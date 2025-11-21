<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'name',
        'start_date',
        'end_date',
        'is_fixed_range',
        'is_default',
        'min_stay',
        'check_in_days',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_fixed_range' => 'boolean',
            'is_default' => 'boolean',
            'min_stay' => 'integer',
            'check_in_days' => 'array',
            'price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Season $season) {
            if ($season->is_default) {
                throw new \RuntimeException('Default season cannot be deleted.');
            }
        });

        static::updating(function (Season $season) {
            if ($season->is_default) {
                if ($season->isDirty(['name', 'start_date', 'end_date', 'is_fixed_range'])) {
                    throw new \RuntimeException('Default season metadata cannot be modified.');
                }
            }
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }



    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
