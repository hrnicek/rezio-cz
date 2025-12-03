<?php

namespace App\Models\Finance;

use App\Enums\InvoiceType;
use App\Models\Booking\Booking;
use App\Models\Booking\Folio;
use App\States\Invoice\InvoiceState;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStates\HasStates;

class Invoice extends Model
{
    use HasFactory, HasStates, HasUuids, SoftDeletes;

    protected $table = 'invoices';

    protected $fillable = [
        'booking_id', 'folio_id', 'payment_id', 'type',
        'number', 'variable_symbol',
        'issued_date', 'due_date', 'tax_date',
        'supplier_name', 'supplier_ico', 'supplier_dic', 'supplier_address',
        'customer_name', 'customer_ico', 'customer_dic', 'customer_address',
        'total_price_amount', 'total_net_amount', 'total_tax_amount', 'currency',
        'status', 'notes',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'due_date' => 'date',
        'tax_date' => 'date',
        'total_price_amount' => \App\Casts\MoneyCast::class,
        'total_net_amount' => \App\Casts\MoneyCast::class,
        'total_tax_amount' => \App\Casts\MoneyCast::class,
        'status' => InvoiceState::class,
        'type' => InvoiceType::class,
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
