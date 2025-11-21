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
        // Default Season (Mimo sezónu) - rok 2025
        Season::create([
            'name' => 'MIMO SEZÓNU',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'is_fixed_range' => false,
            'is_default' => true,
            'min_stay' => 1,
            'check_in_days' => null,
            'price' => 2500.00, // Default price for off-season
        ]);

        // Léto (June 15 - August 31)
        Season::create([
            'name' => 'LÉTO',
            'start_date' => '2025-06-15',
            'end_date' => '2025-08-31',
            'is_fixed_range' => false,
            'is_default' => false,
            'min_stay' => 5,
            'check_in_days' => [6], // Saturday only
            'price' => 5500.00, // Summer season price
        ]);

        // Zima (December 20 - March 15)
        Season::create([
            'name' => 'ZIMA',
            'start_date' => '2025-12-20',
            'end_date' => '2026-03-15',
            'is_fixed_range' => false,
            'is_default' => false,
            'min_stay' => 5,
            'check_in_days' => [6], // Saturday only
            'price' => 5000.00, // Winter season price
        ]);

        // Silvestr (Fixed range: December 28 - January 2)
        Season::create([
            'name' => 'SILVESTR',
            'start_date' => '2025-12-28',
            'end_date' => '2026-01-02',
            'is_fixed_range' => true,
            'is_default' => false,
            'min_stay' => 5,
            'check_in_days' => [6], // Saturday only
            'price' => 8000.00, // New Year's Eve premium price
        ]);
    }
}
