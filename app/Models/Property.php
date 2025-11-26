<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'address',
        'description',
        'price_per_night',
    ];

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

    public function widget(): HasOne
    {
        return $this->hasOne(Widget::class)->withDefault([
        'settings' => [
            'primary_color' => '#3b82f6',
            'font_family' => 'Roboto, sans-serif',
            'layout' => 'standard',
        ],
        'allowed_domains' => [], 
        'is_active' => true,
    ]);
    }
}
