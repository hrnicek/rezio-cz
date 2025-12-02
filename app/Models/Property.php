<?php

namespace App\Models;

use App\Models\Booking\Booking;
use App\Models\Configuration\BillingSetting;
use App\Models\Configuration\BlockDate;
use App\Models\Configuration\Season;
use App\Models\Configuration\Service;
use App\Models\Finance\Invoice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Property extends Model
{
    use HasFactory;

    protected $table = 'properties';

    protected $fillable = [
        'name', 'short_name', 'slug', 'address', 'description', 'image',
        'default_check_in_time', 'default_check_out_time',
    ];

    protected $casts = [
        // Time cast usually returns a Carbon instance or string 'H:i:s'
        // In Laravel 11+, 'immutable_time' or just string is common.
        // Let's stick to standard behavior or string for simplicity unless user specified.
    ];

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
