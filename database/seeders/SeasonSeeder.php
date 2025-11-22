<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        $properties = \App\Models\Property::all();
        foreach ($properties as $property) {
            $hasDefault = $property->seasons()->where('is_default', true)->exists();
            if (! $hasDefault) {
                $property->seasons()->create([
                    'name' => 'Default',
                    'price' => $property->price_per_night,
                    'is_default' => true,
                ]);
            }
        }
    }
}

