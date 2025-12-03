<?php

namespace App\Models\Booking;

use App\Models\Property;
use App\Models\CRM\Guest;
use App\Models\CRM\Customer;
use Illuminate\Support\Carbon;
use App\Models\Finance\Invoice;
use App\Models\Finance\Payment;
use Spatie\ModelStates\HasStates;
use App\States\Booking\BookingState;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property-read \App\Models\CRM\Customer $customer
 * @property-read \App\Models\Property $property
 * @property-read \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\CRM\Guest, \App\Models\Booking\Booking> $guests
 * @property-read \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Booking\Folio, \App\Models\Booking\Booking> $folios
 * @property-read \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Finance\Invoice, \App\Models\Booking\Booking> $invoices
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking\BookingItem> $items
 * @property-read \Illuminate\Database\Eloquent\Relations\HasManyThrough<\App\Models\Booking\BookingItem, \App\Models\Booking\Folio, \App\Models\Booking\Booking> $items()
 * @property-read \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Finance\Payment, \App\Models\Booking\Booking> $payments
 * @property string $guest_email
 */
class Booking extends Model
{
    use HasFactory, HasStates, HasUuids, SoftDeletes;

    protected $table = 'bookings';

    protected $fillable = [
        'property_id', 'customer_id', 'code', 'status', 'token',
        'check_in_date', 'check_out_date',
        'total_price_amount', 'currency', 'guests_count',
        'notes', 'reminders_sent_at',
        'arrival_time_estimate', 'departure_time_estimate',
        'checked_in_at', 'checked_out_at',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'reminders_sent_at' => 'datetime',
        'total_price_amount' => \App\Casts\MoneyCast::class,
        'status' => BookingState::class,
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    protected $dispatchesEvents = [
        'created' => \App\Events\Booking\BookingCreated::class,
    ];

    protected static function newFactory()
    {
        return BookingFactory::new();
    }

    protected function getCheckInTimeAttribute(): string
    {
        // 1. Má host specifický čas příjezdu? (např. Early Check-in)
        if ($this->arrival_time_estimate) {
            return $this->arrival_time_estimate;
        }

        // 2. Pokud ne, vrať standardní čas nemovitosti
        return $this->property->default_check_in_time;
    }

    protected function getCheckInAtAttribute(): Carbon
    {
        return \Illuminate\Support\Facades\Date::parse(
            $this->check_in_date->format('Y-m-d').' '.$this->check_in_time
        );
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function folios(): HasMany
    {
        return $this->hasMany(Folio::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function items()
    {
        return $this->hasManyThrough(BookingItem::class, Folio::class, 'booking_id', 'folio_id', 'id', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
