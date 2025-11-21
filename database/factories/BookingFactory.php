<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+3 months');
        $endDate = (clone $startDate)->modify('+' . fake()->numberBetween(1, 7) . ' days');

        return [
            'property_id' => \App\Models\Property::factory(),
            'user_id' => \App\Models\User::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'guest_info' => [
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'phone' => fake()->phoneNumber(),
            ],
            'total_price' => fake()->randomFloat(2, 100, 1000),
            'status' => fake()->randomElement(['pending', 'confirmed', 'cancelled', 'paid']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
