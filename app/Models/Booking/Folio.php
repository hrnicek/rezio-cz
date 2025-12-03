<?php

namespace App\Models\Booking;

use App\Models\CRM\Customer;
use App\Models\Finance\Payment;
use App\States\Folio\FolioState;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStates\HasStates;

class Folio extends Model
{
    use HasFactory, HasStates, HasUuids, SoftDeletes;

    protected $table = 'folios';

    protected $fillable = [
        'booking_id', 'customer_id', 'name', 'status',
        'total_amount', 'currency',
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'status' => FolioState::class,
    ];

    // --- RELATIONS ---

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Booking\Booking, \App\Models\Booking\Folio>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // Kdo platí toto konkrétní folio (může být jiné než hlavní customer)
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\CRM\Customer, \App\Models\Booking\Folio>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Booking\BookingItem, \App\Models\Booking\Folio>
     */
    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class, 'folio_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Finance\Payment, \App\Models\Booking\Folio>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'folio_id');
    }

    public function recalculateTotal(): void
    {
        $this->update([
            'total_amount' => $this->items()->sum('total_price_amount'),
        ]);
    }
}
