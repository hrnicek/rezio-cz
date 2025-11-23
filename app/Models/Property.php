<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        // 'user_id', // Removed - properties now belong to tenants
        'name',
        'slug',
        'address',
        'description',
        'price_per_night',
        'widget_token',
    ];

    // Removed user() relationship - properties now belong to tenants
    // public function user(): BelongsTo { ... }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function emailTemplates(): HasMany
    {
        return $this->hasMany(EmailTemplate::class);
    }
}
