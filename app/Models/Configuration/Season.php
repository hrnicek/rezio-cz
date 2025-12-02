<?php

namespace App\Models\Configuration;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Season extends Model
{
    use HasFactory;

    protected $table = 'seasons';

    protected $fillable = [
        'property_id',
        'name',
        'start_date',
        'end_date',
        'is_fixed_range',
        'priority',
        'min_stay',
        'check_in_days',
        'is_default',
        'price_amount',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_fixed_range' => 'boolean',
        'is_default' => 'boolean',
        'check_in_days' => 'array',
        'price_amount' => \App\Casts\MoneyCast::class,
        'min_stay' => 'integer',
        'priority' => 'integer',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function matchesDate(\Carbon\Carbon $date): bool
    {
        if ($this->is_default) {
            return false; // Default seasons don't match specific dates
        }

        if (! $this->start_date || ! $this->end_date) {
            return false;
        }

        return $date->between($this->start_date, $this->end_date);
    }
}
