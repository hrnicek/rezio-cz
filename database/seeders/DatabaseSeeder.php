<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Central\Tenant;
use Illuminate\Database\Seeder;

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

        $tenant1 = Tenant::create([
            'id' => 'chata',
            'plan' => 'pro',
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
                'widget_token' => \Illuminate\Support\Str::random(32),
                'price_per_night' => 2500,
            ]);

            // Seed seasons and services
            $this->call(SeasonSeeder::class);
            $this->call(ServiceSeeder::class);

            // Create customers and bookings
            for ($i = 0; $i < 5; $i++) {
                $customer = \App\Models\Customer::factory()->create();

                $startDate = now()->addDays(rand(1, 60));
                $endDate = (clone $startDate)->addDays(rand(2, 7));

                $booking = \App\Models\Booking::create([
                    'property_id' => $property->id,
                    'customer_id' => $customer->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'date_start' => $startDate->setTime(14, 0),
                    'date_end' => $endDate->setTime(10, 0),
                    'status' => fake()->randomElement(['pending', 'confirmed', 'paid']),
                    'total_price' => rand(5000, 15000),
                    'notes' => fake()->optional()->sentence(),
                ]);

                // Attach random services
                $services = \App\Models\Service::inRandomOrder()->limit(rand(0, 3))->get();
                foreach ($services as $service) {
                    $booking->services()->attach($service->id, [
                        'quantity' => rand(1, 3),
                        'price_total' => $service->price * rand(1, 3),
                    ]);
                }
            }
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
