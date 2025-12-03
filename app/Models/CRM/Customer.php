<?php

namespace App\Models\CRM;

use App\Models\Booking\Booking;
use App\Models\Booking\Folio;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $first_name
 * @property string $last_name
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $billing_name
 * @property string $ico
 * @property string $dic
 * @property string $billing_street
 * @property string $billing_city
 * @property string $billing_zip
 * @property string $billing_country
 */
class Customer extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    // Musíme definovat, protože model je v podsložce
    protected $table = 'customers';

    protected $fillable = [
        'email', 'phone', 'is_company',
        'first_name', 'last_name', 'company_name', 'ico', 'dic', 'has_vat',
        'billing_street', 'billing_city', 'billing_zip', 'billing_country',
        'internal_notes', 'is_registered',
    ];

    protected $casts = [
        'is_company' => 'boolean',
        'has_vat' => 'boolean',
        'is_registered' => 'boolean',
    ];

    protected static function newFactory()
    {
        return CustomerFactory::new();
    }

    // --- RELATIONS ---

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    public function folios(): HasMany
    {
        return $this->hasMany(Folio::class, 'customer_id');
    }

    protected function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Helper pro získání správného jména na fakturu
    protected function getBillingNameAttribute(): string
    {
        return $this->is_company ? ($this->company_name ?? $this->name) : $this->name;
    }
}
