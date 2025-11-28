<?php

namespace App\Models\Finance;

use App\Enums\InvoiceStatus;
use App\Models\Booking\Booking;
use App\Models\Booking\Folio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'invoices';

    protected $fillable = [
        'booking_id', 'folio_id',
        'number', 'variable_symbol',
        'issued_date', 'due_date', 'tax_date',
        'supplier_name', 'supplier_ico', 'supplier_dic', 'supplier_address',
        'customer_name', 'customer_ico', 'customer_dic', 'customer_address',
        'total_price_amount', 'total_net_amount', 'total_tax_amount', 'currency',
        'status', 'notes'
    ];

    protected $casts = [
        'issued_date' => 'date',
        'due_date' => 'date',
        'tax_date' => 'date',
        'total_price_amount' => 'integer',
        'total_net_amount' => 'integer',
        'total_tax_amount' => 'integer',
        'status' => InvoiceStatus::class,
    ];

    // --- RELATIONS ---

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class);
    }

    // Zde odkazujeme na InvoiceItem, abychom se vyhnuli kolizi v namespace Finance
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}