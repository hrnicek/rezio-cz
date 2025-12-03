<?php

namespace App\Models\Communication;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'type',
        'subject',
        'title',
        'content',
        'trigger_reference',
        'trigger_offset_days',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Find a template for a specific property, falling back to the virtual default.
     */
    public static function findForProperty(string $type, ?int $propertyId): ?self
    {
        if ($propertyId) {
            $template = self::query()->where('type', $type)
                ->where('property_id', $propertyId)
                ->first();

            if ($template) {
                return $template;
            }
        }

        // Fallback to virtual default
        $defaultData = \App\Services\EmailTemplates\DefaultTemplates::get($type);

        if ($defaultData) {
            return self::query()->make($defaultData);
        }

        return null;
    }
}
