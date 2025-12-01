<?php

namespace App\Models\Configuration;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingSetting extends Model
{
    use HasFactory;

    protected $table = 'billing_settings';

    protected $fillable = [
        'property_id',
        // A) Identity & Tax
        'is_vat_payer',
        'ico',
        'dic',
        'company_name',
        'street',
        'city',
        'zip',
        'country',
        'default_note',
        // B) Banking
        'bank_account',
        'iban',
        'swift',
        'currency',
        'show_bank_account',
        // C) Document Numbering
        'proforma_prefix',
        'proforma_current_sequence',
        'invoice_prefix',
        'invoice_current_sequence',
        'receipt_prefix',
        'receipt_current_sequence',
        // D) Logic
        'due_days',
    ];

    protected $casts = [
        'is_vat_payer' => 'boolean',
        'show_bank_account' => 'boolean',
        'proforma_current_sequence' => 'integer',
        'invoice_current_sequence' => 'integer',
        'receipt_current_sequence' => 'integer',
        'due_days' => 'integer',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
