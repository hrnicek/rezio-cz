<?php

namespace App\Models\Booking;

use App\Enums\BookingItemType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    use HasFactory, HasUuids;

    // Zde je důležité mapování na tabulku s prefixem, i když model se jmenuje čistě
    protected $table = 'booking_items';

    protected $fillable = [
        'booking_id', 'folio_id',
        'type', 'name', 'quantity',
        'unit_price_amount', 'total_price_amount',
        'net_amount', 'tax_amount', 'tax_rate',
    ];

    protected $casts = [
        'unit_price_amount' => 'integer',
        'total_price_amount' => 'integer',
        'net_amount' => 'integer',
        'tax_amount' => 'integer',
        'quantity' => 'integer',
        'type' => BookingItemType::class,
    ];

    // --- RELATIONS ---

    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
