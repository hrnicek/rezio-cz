<?php

namespace App\Actions\Booking;

use App\Models\Booking;
use App\Models\CRM\Customer;
use App\Models\Configuration\Service;
use App\Rules\Booking\CheckInDayRule;
use App\Rules\Booking\MinStayRule;
use App\Services\BookingPriceCalculator;
use App\Services\BookingRules;
use App\States\Booking\Pending;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateBookingAction
{
    public function __construct(
        private BookingPriceCalculator $priceCalculator,
        private CalculateDominantSeasonAction $seasonCalculator,
        private ValidateAvailabilityAction $availabilityValidator,
        private BookingRules $bookingRules
    ) {
        // Register default rules
        $this->bookingRules
            ->addRule(new MinStayRule)
            ->addRule(new CheckInDayRule);
    }

    public function execute(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $propertyId = (int) ($data['property_id'] ?? 0);
            if ($propertyId === 0) {
                throw new \InvalidArgumentException('property_id is required');
            }
            // 1. Always create a new customer (no deduplication)
            $customer = Customer::query()->create([
                'email' => $data['customer']['email'],
                'first_name' => $data['customer']['first_name'] ?? '',
                'last_name' => $data['customer']['last_name'] ?? '',
                'phone' => $data['customer']['phone'],
            ]);

            // 2. Parse dates (with default check-in / check-out times)
            $checkin = config('booking.checkin_time', '14:00');
            $checkout = config('booking.checkout_time', '10:00');
            $startDate = Carbon::createFromFormat('Y-m-d H:i', $data['start_date'].' '.$checkin);
            $endDate = Carbon::createFromFormat('Y-m-d H:i', $data['end_date'].' '.$checkout);

            // 3. Validate availability
            $availability = $this->availabilityValidator->execute($startDate, $endDate);
            if (! $availability['available']) {
                throw new \Exception('Selected dates are not available.');
            }

            // 5. Determine dominant season (moved up for validation)
            $season = $this->seasonCalculator->execute($startDate, $endDate);

            // Validate business rules
            $this->bookingRules->validate($startDate, $endDate, $season);

            // 4. Calculate price (seasonal overlay)
            $priceBreakdown = $this->priceCalculator->calculate(
                $propertyId,
                $startDate,
                $endDate,
                $data['addons'] ?? []
            );

            // 6. Create booking
            $booking = Booking::create([
                'property_id' => $propertyId,
                'customer_id' => $customer->id,
                'season_id' => $season?->id,
                'check_in_date' => $data['start_date'],
                'check_out_date' => $data['end_date'],
                'total_price_amount' => $priceBreakdown->total,
                'status' => Pending::class,
            ]);

            // 7. Attach services
            foreach ($data['addons'] ?? [] as $selection) {
                $service = Service::find($selection['extra_id'] ?? $selection['service_id'] ?? null);
                $quantity = (int) ($selection['quantity'] ?? 0);

                if (! $service || ! $service->is_active || $quantity <= 0) {
                    continue;
                }

                $nights = max(1, $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()));
                $lineTotal = $service->price_type === 'per_day'
                    ? $quantity * $nights * (float) $service->price
                    : $quantity * (float) $service->price;

                $booking->services()->attach($service->id, [
                    'quantity' => $quantity,
                    'price_total' => $lineTotal,
                ]);
            }

            return $booking->load(['customer', 'services']);
        });
    }
}
