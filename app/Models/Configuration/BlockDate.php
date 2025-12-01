<?php

namespace App\Models\Configuration;

use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockDate extends Model
{
    use HasFactory;

    // Mapujeme na tabulku z migrace (v modelu se jmenuje BlockDate, v DB blackout_dates)
    protected $table = 'blackout_dates';

    // Nemáme UUID, protože toto je interní "Inventory" tabulka s BigInt ID

    protected $fillable = [
        'property_id',
        'start_date',
        'end_date',
        'reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // --- RELATIONS ---

    public function property(): BelongsTo
    {
        // Předpokládáme, že Property model je v App\Models namespace
        return $this->belongsTo(Property::class);
    }

    // --- SCOPES (Byznys logika) ---

    /**
     * Scope: Najdi blokace, které kolidují s poptávaným termínem.
     * Logika překryvu intervalů: (StartA < EndB) && (EndA > StartB)
     */
    public function scopeOverlaps(Builder $query, $checkIn, $checkOut): Builder
    {
        return $query->where(function ($q) use ($checkIn, $checkOut) {
            $q->where('start_date', '<', $checkOut)
                ->where('end_date', '>', $checkIn);
        });
    }

    /**
     * Scope: Pouze aktivní blokace (dnešní a budoucí)
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('end_date', '>=', now()->startOfDay());
    }

    /**
     * Scope: Pro konkrétní property (pomocná metoda)
     */
    public function scopeForProperty(Builder $query, $propertyId): Builder
    {
        return $query->where('property_id', $propertyId);
    }

    // --- ACCESSORS ---

    /**
     * Počet dní blokace
     */
    public function getDaysCountAttribute(): int
    {
        // Včetně počátečního dne, nebo rozdíl? Záleží na logice, obvykle diffInDays
        return $this->start_date->diffInDays($this->end_date);
    }
}
