<?php

namespace App\Models\CRM;

use App\Models\Booking\Booking;
use App\Models\Booking\Folio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    // Musíme definovat, protože model je v podsložce
    protected $table = 'customers';

    protected $fillable = [
        'email', 'phone', 'is_company',
        'first_name', 'last_name', 'company_name', 'ico', 'dic', 'has_vat',
        'billing_street', 'billing_city', 'billing_zip', 'billing_country',
        'internal_notes', 'is_registered'
    ];

    protected $casts = [
        'is_company' => 'boolean',
        'has_vat' => 'boolean',
        'is_registered' => 'boolean',
    ];

    // --- RELATIONS ---

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    public function folios(): HasMany
    {
        return $this->hasMany(Folio::class, 'customer_id');
    }

    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Helper pro získání správného jména na fakturu
    public function getBillingNameAttribute(): string
    {
        return $this->is_company ? ($this->company_name ?? $this->name) : $this->name;
    }
}