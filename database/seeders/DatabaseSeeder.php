<?php

namespace Database\Seeders;

use App\Models\Superadmin;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Central superadmin
        Superadmin::query()->create([
            'name' => 'Super Admin',
            'email' => 'hrncir@zondy.cz',
            'password' => bcrypt('password'),
        ]);
    }
}
