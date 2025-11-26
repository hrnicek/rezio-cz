<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Widget extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'uuid',
        'branding_config',
        'allowed_domains',
        'is_active',
    ];

    protected $casts = [
        'branding_config' => 'array',
        'allowed_domains' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Widget $widget) {
            if (empty($widget->uuid)) {
                $widget->uuid = (string) Str::uuid();
            }
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
