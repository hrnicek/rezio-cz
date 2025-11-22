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
        $start = fake()->dateTimeBetween('+1 week', '+3 months');
        $end = (clone $start)->modify('+' . fake()->numberBetween(1, 14) . ' days');

        // Create a customer for this booking
        $customer = \App\Models\Customer::factory()->create();

        return [
            'property_id' => \App\Models\Property::factory(),
            'user_id' => \App\Models\User::factory(),
            'customer_id' => $customer->id,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'date_start' => $start,
            'date_end' => $end,
            'total_price' => fake()->randomFloat(2, 100, 1000),
            'currency' => 'CZK',
            'exchange_rate' => 1.0000,
            'status' => fake()->randomElement(['pending', 'confirmed', 'paid', 'cancelled']),
        ];
    }
}
