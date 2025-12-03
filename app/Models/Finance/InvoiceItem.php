<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'invoice_items';

    // Všechny ceny jsou finální snapshoty (integers)
    protected $fillable = [
        'invoice_id',
        'name',
        'quantity',
        'unit_price_amount',
        'total_price_amount',
        'tax_amount',
        'tax_rate',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_amount' => \App\Casts\MoneyCast::class,
        'total_price_amount' => \App\Casts\MoneyCast::class,
        'tax_amount' => \App\Casts\MoneyCast::class,
        'tax_rate' => 'integer', // např. 2100 = 21.00%
    ];

    // --- RELATIONS ---

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // --- HELPERS ---

    /**
     * Vrátí sazbu DPH jako float (např. 21.0)
     */
    protected function getTaxRatePercentageAttribute(): float
    {
        return $this->tax_rate / 100;
    }
}
