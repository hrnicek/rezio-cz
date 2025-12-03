<?php

namespace App\Models;

use App\Models\Booking\Booking;
use App\Models\Finance\Invoice;
use App\Models\Configuration\Season;
use App\Models\Configuration\Service;
use App\Models\Configuration\BlockDate;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Models\Configuration\BillingSetting;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'properties';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name', 'short_name', 'slug', 'address', 'description', 'image',
        'default_check_in_time', 'default_check_out_time',
    ];

    protected $casts = [
        // Time cast usually returns a Carbon instance or string 'H:i:s'
        // In Laravel 11+, 'immutable_time' or just string is common.
        // Let's stick to standard behavior or string for simplicity unless user specified.
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    // --- RELATIONS ---

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }

    public function blockDates(): HasMany
    {
        return $this->hasMany(BlockDate::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function billingSetting(): HasOne
    {
        return $this->hasOne(BillingSetting::class);
    }
}
