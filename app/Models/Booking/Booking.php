<?php

namespace App\Models\Booking;

use App\Models\CRM\Customer;
use App\Models\CRM\Guest;
use App\Models\Finance\Invoice;
use App\Models\Finance\Payment;
use App\States\Booking\BookingState;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\HasStates;

/**
 * @property-read \Illuminate\Database\Eloquent\Relations\HasManyThrough<\App\Models\Booking\BookingItem, \App\Models\Booking\Folio> $items
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

    public function getCheckInTimeAttribute(): string
    {
        // 1. Má host specifický čas příjezdu? (např. Early Check-in)
        if ($this->arrival_time_estimate) {
            return $this->arrival_time_estimate;
        }

        // 2. Pokud ne, vrať standardní čas nemovitosti
        return $this->property->default_check_in_time;
    }

    // Helper pro Frontend: Kdy přesně má nárok na klíče?
    public function getCheckInAtAttribute(): Carbon
    {
        // Spojí Datum (Booking) + Čas (Vypočítaný)
        return Carbon::parse(
            $this->check_in_date->format('Y-m-d').' '.$this->check_in_time
        );
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function property(): BelongsTo
    {
        // Předpoklad: App\Models\Property existuje
        return $this->belongsTo(\App\Models\Property::class);
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

    // Zkratka pro získání všech položek přes všechna folia
    public function items()
    {
        return $this->hasManyThrough(BookingItem::class, Folio::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
