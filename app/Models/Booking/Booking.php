<?php

namespace App\Models\Booking;

use App\Models\CRM\Guest;
use App\Models\CRM\Customer;
use App\Models\Finance\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'bookings';

    protected $fillable = [
        'property_id', 'customer_id', 'code', 'status', 'token',
        'check_in_date', 'check_out_date',
        'total_price_amount', 'currency',
        'notes', 'reminders_sent_at'
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'reminders_sent_at' => 'datetime',
        'total_price_amount' => 'integer',
        // 'status' => BookingStatus::class, // Až budete mít Enum
    ];

    // --- RELATIONS ---

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

    // --- HELPERS ---

    public function getNightsCountAttribute(): int
    {
        return $this->check_in_date->diffInDays($this->check_out_date);
    }
}