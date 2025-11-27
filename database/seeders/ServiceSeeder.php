<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first property in the current tenant context
        $property = Property::first();

        if (! $property) {
            $this->command->warn('No property found, skipping service seeding');

            return;
        }

        $items = [
            [
                'property_id' => $property->id,
                'name' => 'Pes',
                'price_type' => 'per_day',
                'price' => 350,
                'max_quantity' => 10,
                'is_active' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Postýlka',
                'price_type' => 'per_stay',
                'price' => 500,
                'max_quantity' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($items as $data) {
            Service::query()->updateOrCreate(
                [
                    'name' => $data['name'],
                    'property_id' => $data['property_id'],
                ],
                $data,
            );
        }
    }
}
