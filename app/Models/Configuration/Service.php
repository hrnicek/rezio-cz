<?php

namespace App\Models\Configuration; // Změna namespace

use App\Models\Property; // Property je nyní v rootu
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'services';

    protected $fillable = [
        'property_id', 'name', 'description', 'price_type',
        'price_amount', 'max_quantity', 'is_active',
    ];

    protected $casts = [
        'price_amount' => \App\Casts\MoneyCast::class,
        'max_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
