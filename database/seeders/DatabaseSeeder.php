<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Booking;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Property;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user1 = User::create([
            'name' => 'Jakub Hrnčíř',
            'email' => 'hrncir@zondy.cz',
            'password' => bcrypt('password')
        ]);

        Property::factory(2)->create();
        Booking::factory(12)->create();
    }
}
