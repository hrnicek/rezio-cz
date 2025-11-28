<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'image',
    ];

    protected static function booted(): void
    {
        static::created(function (Property $property) {
            $property->seasons()->create([
                'name' => 'Výchozí sezóna',
                'is_default' => true,
                'price' => 0,
                'min_stay' => 1,
                'priority' => 0,
            ]);
        });
    }

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
