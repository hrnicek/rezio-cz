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
        'priority',
        'min_stay',
        'min_persons',
        'is_default',
        'price_amount',
        'is_full_season_booking_only',
        'is_recurring',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_default' => 'boolean',
        'price_amount' => \App\Casts\MoneyCast::class,
        'min_stay' => 'integer',
        'min_persons' => 'integer',
        'priority' => 'integer',
        'is_full_season_booking_only' => 'boolean',
        'is_recurring' => 'boolean',
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

        if ($this->is_recurring) {
            $start = $this->start_date->copy()->year($date->year);
            $end = $this->end_date->copy()->year($date->year);

            // Handle season spanning across new year (e.g. Dec to Jan)
            if ($start->gt($end)) {
                 if ($date->month >= $start->month) {
                     $end->addYear();
                 } else {
                     $start->subYear();
                 }
            }
            
            return $date->between($start, $end);
        }

        return $date->between($this->start_date, $this->end_date);
    }
}
