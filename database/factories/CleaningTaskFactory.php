<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CleaningTask>
 */
class CleaningTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'property_id' => Property::factory(),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'completed_at' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the cleaning task is completed.
     */
    public function completed(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
