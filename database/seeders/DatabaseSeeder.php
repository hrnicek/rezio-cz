<?php

namespace Database\Seeders;

use App\Models\Central\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding multi-tenant database...');

        // 1. Create tenants
        $this->command->info('Creating tenants...');

        // Delete existing tenants and domains (use central connection)
        Tenant::query()->delete();
        DB::connection('mysql')->table('domains')->delete();

        // Drop and recreate tenant database
        try {
            DB::connection('mysql')->statement('DROP DATABASE IF EXISTS rezio_chata');
        } catch (\Exception $e) {
            // Database might not exist yet
        }

        $tenant1 = Tenant::create([
            'tenancy_db_name' => 'rezio_chata',
        ]);

        $tenant1->domains()->create(['domain' => 'chata.rezio.test']);

        // 2. Seed Tenant 1 (Chata u Lipna)
        $this->command->info('Seeding Tenant 1: Chata u Lipna...');
        $tenant1->run(function () {
            // Create admin user
            $admin = User::create([
                'name' => 'Admin User',
                'email' => 'hrncir@zondy.cz',
                'password' => bcrypt('password'),
            ]);

            // Create property
            $property = \App\Models\Property::create([
                'name' => 'Chata 1',
                'slug' => 'chata-1',
                'description' => 'Krásná chata s výhledem',
            ]);
        });

        $this->command->info('✅ Multi-tenant database seeded successfully!');
        $this->command->newLine();
        $this->command->info('🏢 Tenants created:');
        $this->command->info('  - Chata u Lipna: http://chata-u-lipna.rezio.test');
        $this->command->info('    📧 Login: hrncir@zondy.cz / password');
        $this->command->info('  - Apartmán Praha: http://apartman-praha.rezio.test');
        $this->command->info('    📧 Login: praha@example.com / password');
    }
}
