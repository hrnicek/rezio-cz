<?php

namespace Database\Seeders;

use App\Models\BlackoutDate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BlackoutDateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now()->startOfDay();

        $items = [
            [
                'start_date' => $now->copy()->addMonth()->startOfMonth()->addDays(14)->toDateString(),
                'end_date' => $now->copy()->addMonth()->startOfMonth()->addDays(20)->toDateString(),
                'reason' => 'Údržba',
            ],
            [
                'start_date' => $now->copy()->addMonths(2)->startOfMonth()->addDays(4)->toDateString(),
                'end_date' => $now->copy()->addMonths(2)->startOfMonth()->addDays(6)->toDateString(),
                'reason' => 'Soukromá akce',
            ],
            [
                'start_date' => $now->copy()->addMonths(3)->startOfMonth()->addDays(25)->toDateString(),
                'end_date' => $now->copy()->addMonths(3)->startOfMonth()->addDays(27)->toDateString(),
                'reason' => 'Servis',
            ],
            [
                'start_date' => $now->copy()->addMonths(6)->startOfMonth()->toDateString(),
                'end_date' => $now->copy()->addMonths(6)->startOfMonth()->addDays(2)->toDateString(),
                'reason' => 'Dovolená',
            ],
            [
                'start_date' => $now->copy()->addYear()->startOfYear()->addDays(9)->toDateString(),
                'end_date' => $now->copy()->addYear()->startOfYear()->addDays(12)->toDateString(),
                'reason' => 'Inventura',
            ],
        ];

        foreach ($items as $data) {
            BlackoutDate::query()->create($data);
        }
    }
}
