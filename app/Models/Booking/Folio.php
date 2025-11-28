<?php

namespace App\Models\Booking;

use App\Models\CRM\Customer;
use App\Models\Finance\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Folio extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'folios';

    protected $fillable = [
        'booking_id', 'customer_id', 'name', 'status', 
        'total_amount', 'currency'
    ];

    protected $casts = [
        'total_amount' => 'integer',
    ];

    // --- RELATIONS ---

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // Kdo platí toto konkrétní folio (může být jiné než hlavní customer)
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'folio_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'folio_id');
    }

    // --- BUSINESS LOGIC ---

    public function recalculateTotal(): void
    {
        $this->update([
            'total_amount' => $this->items()->sum('total_price_amount')
        ]);
    }
}