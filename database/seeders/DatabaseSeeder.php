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
        $this->command->info('🌱 Seeding database with test data...');

        // 1. Create admin user
        $this->command->info('Creating admin user...');
        $admin = \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'hrncir@zondy.cz',
            'password' => bcrypt('password'),
        ]);

        // 2. Create properties for admin
        $this->command->info('Creating properties...');
        $property1 = \App\Models\Property::create([
            'user_id' => $admin->id,
            'name' => 'Chata u Lipna',
            'slug' => 'chata-u-lipna',
            'description' => 'Krásná chata s výhledem na Lipno',
            'widget_token' => \Illuminate\Support\Str::random(32),
            'price_per_night' => 2500,
        ]);

        $property2 = \App\Models\Property::create([
            'user_id' => $admin->id,
            'name' => 'Apartmán Praha',
            'slug' => 'apartman-praha',
            'description' => 'Moderní apartmán v centru Prahy',
            'widget_token' => \Illuminate\Support\Str::random(32),
            'price_per_night' => 3500,
        ]);

        // 3. Attach properties to admin user (many-to-many)
        $this->command->info('Attaching properties to admin...');
        $admin->properties()->attach([$property1->id, $property2->id]);

        // 4. Assign property roles
        $this->command->info('Assigning property roles...');
        $admin->assignPropertyRole('owner', $property1);
        $admin->assignPropertyRole('owner', $property2);

        // 5. Set current property
        $admin->update(['current_property_id' => $property1->id]);

        // 6. Seed seasons
        $this->command->info('Seeding seasons...');
        $this->call(SeasonSeeder::class);

        // 7. Seed services
        $this->command->info('Seeding services...');
        $this->call(ServiceSeeder::class);

        // 8. Create customers and bookings for property 1
        $this->command->info('Creating customers and bookings for Property 1...');
        for ($i = 0; $i < 5; $i++) {
            $customer = \App\Models\Customer::factory()->create();

            $startDate = now()->addDays(rand(1, 60));
            $endDate = (clone $startDate)->addDays(rand(2, 7));

            $booking = \App\Models\Booking::create([
                'property_id' => $property1->id,
                'user_id' => $admin->id,
                'customer_id' => $customer->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'date_start' => $startDate->setTime(14, 0),
                'date_end' => $endDate->setTime(10, 0),
                'status' => fake()->randomElement(['pending', 'confirmed', 'paid']),
                'total_price' => rand(5000, 15000),
                'notes' => fake()->optional()->sentence(),
            ]);

            // Attach random services to booking
            $services = \App\Models\Service::inRandomOrder()->limit(rand(0, 3))->get();
            foreach ($services as $service) {
                $booking->services()->attach($service->id, [
                    'quantity' => rand(1, 3),
                    'price_total' => $service->price * rand(1, 3),
                ]);
            }
        }

        // 9. Create bookings for property 2
        $this->command->info('Creating customers and bookings for Property 2...');
        for ($i = 0; $i < 3; $i++) {
            $customer = \App\Models\Customer::factory()->create();

            $startDate = now()->addDays(rand(1, 60));
            $endDate = (clone $startDate)->addDays(rand(2, 7));

            \App\Models\Booking::create([
                'property_id' => $property2->id,
                'user_id' => $admin->id,
                'customer_id' => $customer->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'date_start' => $startDate->setTime(14, 0),
                'date_end' => $endDate->setTime(10, 0),
                'status' => fake()->randomElement(['pending', 'confirmed', 'paid']),
                'total_price' => rand(7000, 20000),
                'notes' => fake()->optional()->sentence(),
            ]);
        }

        // 10. Create additional test user with limited access
        $this->command->info('Creating staff user...');
        $staff = \App\Models\User::factory()->create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
        ]);

        // Attach only property 1 to staff
        $staff->properties()->attach($property1->id);
        $staff->assignPropertyRole('manager', $property1);
        $staff->update(['current_property_id' => $property1->id]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->newLine();
        $this->command->info('📧 Admin Login: hrncir@zondy.cz / password');
        $this->command->info('📧 Staff Login: staff@example.com / password');
        $this->command->info('🏠 Properties: ' . \App\Models\Property::count());
        $this->command->info('📅 Bookings: ' . \App\Models\Booking::count());
        $this->command->info('👥 Customers: ' . \App\Models\Customer::count());
    }
}
