<?php

use App\Models\Property;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Iterate through all properties and ensure they have a default season
        Property::chunk(100, function ($properties) {
            foreach ($properties as $property) {
                if (!$property->seasons()->where('is_default', true)->exists()) {
                    $property->seasons()->create([
                        'name' => 'Výchozí sezóna',
                        'is_default' => true,
                        'price' => $property->price_per_night ?? 0,
                        'min_stay' => 1,
                        'priority' => 0,
                        // Default season doesn't need dates
                    ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No safe way to reverse this without deleting potentially valid user data
    }
};
