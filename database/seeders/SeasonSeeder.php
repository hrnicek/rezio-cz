<?php

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default Season (Mimo sezónu)
        Season::create([
            'name' => 'MIMO SEZÓNU',
            'start_month_day' => '01-01',
            'end_month_day' => '12-31',
            'is_default' => true,
            'min_stay' => 1,
            'price' => 2500,
        ]);

        // Léto (June 15 - August 31)
        Season::create([
            'name' => 'LÉTO',
            'start_month_day' => '06-15',
            'end_month_day' => '08-31',
            'is_default' => false,
            'min_stay' => 5,
            'price' => 5500,
        ]);

        // Zima (December 20 - March 15)
        Season::create([
            'name' => 'ZIMA',
            'start_month_day' => '12-20',
            'end_month_day' => '03-15',
            'is_default' => false,
            'min_stay' => 5,
            'price' => 5000,
        ]);

        // Silvestr (December 28 - January 2)
        Season::create([
            'name' => 'SILVESTR',
            'start_month_day' => '12-28',
            'end_month_day' => '01-02',
            'is_default' => false,
            'min_stay' => 5,
            'price' => 8000,
        ]);
    }
}
