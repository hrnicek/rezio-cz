<?php

namespace Database\Factories;

use App\Models\Booking\Booking;
use App\Models\CRM\Customer;
use App\Models\Property;
use App\States\Booking\Pending;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 week', '+3 months');
        $end = (clone $start)->modify('+'.fake()->numberBetween(1, 14).' days');

        return [
            'code' => fake()->uuid(),
            'property_id' => Property::factory(),
            'customer_id' => Customer::factory(),
            'check_in_date' => $start->format('Y-m-d'),
            'check_out_date' => $end->format('Y-m-d'),
            'total_price_amount' => fake()->numberBetween(10000, 100000), // Cents
            'currency' => 'CZK',
            'status' => Pending::class,
            'notes' => fake()->sentence(),
            'reminders_sent_at' => null,
            'arrival_time_estimate' => null,
            'departure_time_estimate' => null,
            'checked_in_at' => null,
            'checked_out_at' => null,
        ];
    }
}
