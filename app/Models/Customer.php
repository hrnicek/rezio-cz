<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
    ];

    protected $appends = [
        'name',
    ];

    public function name(): Attribute
    {
        return Attribute::make(
            get: fn(): string => $this->first_name . ' ' . $this->last_name
        );
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
