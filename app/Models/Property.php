<?php

namespace App\Models; 

use App\Models\Booking\Booking;
use App\Models\Finance\Invoice;
use App\Models\Configuration\Season;     
use App\Models\Configuration\Service;    
use App\Models\Configuration\BlockDate;  
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory;

    protected $table = 'properties';

    protected $fillable = [
        'name', 'slug', 'address', 'description', 'image'
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
}