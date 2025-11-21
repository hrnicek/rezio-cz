<?php

namespace Database\Seeders;

use App\Models\Extra;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Pes',
                'price_type' => 'per_day',
                'price' => 350,
                'max_quantity' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Postýlka',
                'price_type' => 'per_stay',
                'price' => 500,
                'max_quantity' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($items as $data) {
            Service::query()->updateOrCreate(
                ['name' => $data['name']],
                $data,
            );
        }
    }
}
