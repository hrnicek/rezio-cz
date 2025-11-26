<?php

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
        $properties = \App\Models\Property::all();

        foreach ($properties as $property) {
            // Create a widget for each existing property
            \App\Models\Widget::create([
                'property_id' => $property->id,
                // UUID will be auto-generated in the model's boot method
                'branding_config' => [
                    'primary_color' => '#3B82F6',
                    'secondary_color' => '#10B981',
                    'font_family' => 'Inter, sans-serif',
                ],
                'allowed_domains' => [], // Empty = allow all origins (permissive mode)
                'is_active' => true,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete all widgets (cascade will handle this if properties are deleted)
        \App\Models\Widget::truncate();
    }
};
