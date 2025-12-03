<?php

namespace App\Services;

use App\Actions\Booking\CalculateDominantSeasonAction;
use App\Actions\Booking\ValidateAvailabilityAction;
use App\Data\PriceBreakdown;
use App\Enums\BookingItemType;
use App\Enums\ServicePriceType;
use App\Events\Booking\BookingCreated;
use App\Exceptions\Booking\BookingValidationException;
use App\Exceptions\Booking\DatesUnavailableException;
use App\Exceptions\Booking\ServiceCalculationException;
use App\Models\Booking\Booking;
use App\Models\Booking\Folio;
use App\Models\Configuration\Service;
use App\Models\CRM\Customer;
use App\Services\BookingPriceCalculator;
use App\Services\BookingRetryService;
use App\Services\BookingRules;
use App\States\Booking\Pending;
use App\States\Folio\Open;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class BookingService
{
    public function __construct(
        private BookingPriceCalculator $priceCalculator,
        private CalculateDominantSeasonAction $seasonCalculator,
        private ValidateAvailabilityAction $availabilityValidator,
        private BookingRules $bookingRules,
        private BookingRetryService $retryService
    ) {
        // Register default rules
        $this->bookingRules
            ->addRule(new \App\Rules\Booking\MinStayRule)
            ->addRule(new \App\Rules\Booking\MinPersonsRule)
            ->addRule(new \App\Rules\Booking\FullSeasonBookingRule);
    }

    /**
     * Create a new booking with comprehensive validation and error handling
     *
     * @throws DatesUnavailableException
     * @throws BookingValidationException
     * @throws ServiceCalculationException
     */
    public function createBooking(array $data): Booking
    {
        // Pre-validate input data and normalize
        $data = $this->validateBookingData($data);

        // Parse and validate dates
        $dates = $this->parseAndValidateDates($data);

        // Use retry mechanism for better stability
        return $this->retryService->executeWithRetry(
            function () use ($data, $dates) {
                return $this->executeBookingCreation($data, $dates);
            },
            [
                'property_id' => $data['property_id'],
                'customer_email' => $data['customer']['email'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
            ]
        );
    }

    private function executeBookingCreation(array $data, array $dates): Booking
    {
        return DB::transaction(function () use ($data, $dates) {
            try {
                // 1. Create or find customer (with deduplication)
                $customer = $this->findOrCreateCustomer($data['customer']);

                // 2. Check availability with database lock to prevent race conditions
                $this->ensureAvailability($data['property_id'], $dates['start'], $dates['end']);

                // 3. Calculate dominant season
                $season = $this->seasonCalculator->execute($dates['start'], $dates['end']);

                // 4. Validate business rules
                $this->validateBusinessRules($dates['start'], $dates['end'], $season, $data['guests_count'] ?? 1);

                // 5. Calculate pricing
                $priceBreakdown = $this->calculatePricing($data['property_id'], $dates['start'], $dates['end'], $data['addons'] ?? []);

                // 6. Create booking with unique code
                $booking = $this->createBookingRecord($data, $customer, $season, $priceBreakdown, $dates);

                // 7. Create folio and booking items
                $this->createFolioAndItems($booking, $customer, $priceBreakdown, $data['addons'] ?? []);

                // 8. Dispatch domain events
                event(new BookingCreated($booking));

                return $booking->load(['customer', 'folios.items']);

            } catch (Throwable $e) {
                // Log the error with context for debugging
                logger()->error('Booking creation failed', [
                    'error' => $e->getMessage(),
                    'data' => $data,
                    'trace' => $e->getTraceAsString()
                ]);

                throw $e;
            }
        });
    }

    private function validateBookingData(array $data): array
    {
        $required = ['property_id', 'start_date', 'end_date', 'customer'];
        $missing = array_diff($required, array_keys($data));

        if (!empty($missing)) {
            throw new BookingValidationException(
                'Missing required fields: ' . implode(', ', $missing),
                ['missing_fields' => $missing]
            );
        }

        // More robust property_id validation (handles both integers and UUIDs)
        $propertyId = $data['property_id'];
        if ($propertyId === null || $propertyId === '') {
            throw new BookingValidationException('Invalid property_id: cannot be null or empty', [
                'property_id' => $propertyId,
                'property_id_type' => gettype($propertyId)
            ]);
        }

        // For UUID properties, just validate it's a non-empty string
        if (is_string($propertyId) && !is_numeric($propertyId)) {
            if (strlen($propertyId) < 10) { // Basic UUID length check
                throw new BookingValidationException('Invalid property_id: string too short', [
                    'property_id' => $propertyId,
                    'property_id_length' => strlen($propertyId)
                ]);
            }
        } elseif (is_numeric($propertyId) || is_int($propertyId)) {
            $propertyId = (int) $propertyId;
            if ($propertyId <= 0) {
                throw new BookingValidationException('Invalid property_id: must be greater than 0', [
                    'property_id' => $propertyId
                ]);
            }
        } else {
            throw new BookingValidationException('Invalid property_id: must be a valid identifier', [
                'property_id' => $propertyId,
                'property_id_type' => gettype($propertyId)
            ]);
        }

        return $data;
    }

    private function parseAndValidateDates(array $data): array
    {
        try {
            $checkin = config('booking.checkin_time', '14:00');
            $checkout = config('booking.checkout_time', '10:00');
            $timezone = config('booking.timezone', 'Europe/Prague');

            $start = Carbon::createFromFormat('Y-m-d H:i', $data['start_date'] . ' ' . $checkin, $timezone);
            $end = Carbon::createFromFormat('Y-m-d H:i', $data['end_date'] . ' ' . $checkout, $timezone);

            if ($end->lte($start)) {
                throw new BookingValidationException('End date must be after start date');
            }

            // Check minimum lead time
            $minLeadDays = (int) config('booking.min_lead_days', 1);
            $earliest = now()->timezone($timezone)->startOfDay()->addDays($minLeadDays)->setTimeFromTimeString($checkin);

            if ($start->lt($earliest)) {
                throw new BookingValidationException('Booking must be made at least ' . $minLeadDays . ' day(s) in advance');
            }

            return ['start' => $start, 'end' => $end];
        } catch (Throwable $e) {
            throw new BookingValidationException('Invalid date format or values', [
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function findOrCreateCustomer(array $customerData): Customer
    {
        // Try to find existing customer by email first
        $customer = Customer::where('email', $customerData['email'])->first();

        if ($customer) {
            // Update customer data if needed
            $customer->update(array_filter([
                'first_name' => $customerData['first_name'] ?? $customer->first_name,
                'last_name' => $customerData['last_name'] ?? $customer->last_name,
                'phone' => $customerData['phone'] ?? $customer->phone,
                'is_company' => $customerData['is_company'] ?? $customer->is_company,
                'company_name' => $customerData['company_name'] ?? $customer->company_name,
                'ico' => $customerData['ico'] ?? $customer->ico,
                'dic' => $customerData['dic'] ?? $customer->dic,
                'has_vat' => $customerData['has_vat'] ?? $customer->has_vat,
                'billing_street' => $customerData['billing_street'] ?? $customer->billing_street,
                'billing_city' => $customerData['billing_city'] ?? $customer->billing_city,
                'billing_zip' => $customerData['billing_zip'] ?? $customer->billing_zip,
                'billing_country' => $customerData['billing_country'] ?? $customer->billing_country,
            ]));

            return $customer;
        }

        // Create new customer
        return Customer::create([
            'email' => $customerData['email'],
            'first_name' => $customerData['first_name'] ?? '',
            'last_name' => $customerData['last_name'] ?? '',
            'phone' => $customerData['phone'],
            'is_company' => $customerData['is_company'] ?? false,
            'company_name' => $customerData['company_name'] ?? null,
            'ico' => $customerData['ico'] ?? null,
            'dic' => $customerData['dic'] ?? null,
            'has_vat' => $customerData['has_vat'] ?? false,
            'billing_street' => $customerData['billing_street'] ?? null,
            'billing_city' => $customerData['billing_city'] ?? null,
            'billing_zip' => $customerData['billing_zip'] ?? null,
            'billing_country' => $customerData['billing_country'] ?? 'CZ',
        ]);
    }

    private function ensureAvailability(string|int $propertyId, Carbon $startDate, Carbon $endDate): void
    {
        $availability = $this->availabilityValidator->execute($startDate, $endDate);

        if (!$availability['available']) {
            throw new DatesUnavailableException([
                'property_id' => $propertyId,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'has_overlapping_bookings' => $availability['has_overlapping_bookings'],
                'has_blackout_dates' => $availability['has_blackout_dates'],
            ]);
        }
    }

    private function validateBusinessRules(Carbon $startDate, Carbon $endDate, $season, int $guestsCount): void
    {
        try {
            $this->bookingRules->validate($startDate, $endDate, $season, $guestsCount);
        } catch (Throwable $e) {
            throw new BookingValidationException(
                'Business rule validation failed: ' . $e->getMessage(),
                ['season' => $season?->id, 'guests_count' => $guestsCount]
            );
        }
    }

    private function calculatePricing(string|int $propertyId, Carbon $startDate, Carbon $endDate, array $addons): PriceBreakdown
    {
        try {
            return $this->priceCalculator->calculate($propertyId, $startDate, $endDate, $addons);
        } catch (Throwable $e) {
            throw new ServiceCalculationException('Failed to calculate booking price', [
                'property_id' => $propertyId,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'addons_count' => count($addons),
            ]);
        }
    }

    private function createBookingRecord(array $data, Customer $customer, $season, PriceBreakdown $priceBreakdown, array $dates): Booking
    {
        // Generate unique booking code
        $code = $this->generateUniqueBookingCode();

        return Booking::create([
            'property_id' => $data['property_id'],
            'customer_id' => $customer->id,
            'season_id' => $season?->id,
            'code' => $code,
            'status' => Pending::class,
            'check_in_date' => $dates['start'],
            'check_out_date' => $dates['end'],
            'total_price_amount' => $priceBreakdown->total,
            'currency' => config('booking.currency', 'CZK'),
            'notes' => $data['notes'] ?? null,
            'guests_count' => $data['guests_count'] ?? 1,
        ]);
    }

    private function generateUniqueBookingCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Booking::where('code', $code)->exists());

        return $code;
    }

    private function createFolioAndItems(Booking $booking, Customer $customer, PriceBreakdown $priceBreakdown, array $addons): void
    {
        // Create main folio
        $folio = $booking->folios()->create([
            'customer_id' => $customer->id,
            'name' => 'Hlavní účet',
            'status' => Open::class,
            'total_amount' => $priceBreakdown->total,
            'currency' => config('booking.currency', 'CZK'),
        ]);

        // Add accommodation item
        if ($priceBreakdown->accommodation > 0) {
            $folio->items()->create([
                'booking_id' => $booking->id,
                'type' => BookingItemType::Night,
                'name' => 'Ubytování',
                'quantity' => 1,
                'unit_price_amount' => $priceBreakdown->accommodation,
                'total_price_amount' => $priceBreakdown->accommodation,
                'currency' => config('booking.currency', 'CZK'),
            ]);
        }

        // Add service items
        foreach ($priceBreakdown->serviceDetails as $serviceDetail) {
            $folio->items()->create([
                'booking_id' => $booking->id,
                'type' => BookingItemType::Service,
                'name' => $serviceDetail['name'],
                'quantity' => $serviceDetail['quantity'],
                'unit_price_amount' => $serviceDetail['price_per_unit'],
                'total_price_amount' => $serviceDetail['line_total'],
                'currency' => config('booking.currency', 'CZK'),
            ]);
        }
    }
}