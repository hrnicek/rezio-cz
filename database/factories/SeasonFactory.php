<?php

namespace Database\Factories;

use App\Models\Configuration\Season;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Configuration\Season>
 */
class SeasonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'name' => $this->faker->word(),
            'start_month_day' => '01-01', // Legacy fields, but required by migration if not nullable? Migration says they are string, not nullable.
            'end_month_day' => '12-31',
            'min_stay' => 1,
            'is_default' => false,
            'price' => $this->faker->numberBetween(100, 500),
            'start_date' => null,
            'end_date' => null,
            'is_fixed_range' => false,
            'check_in_days' => null,
        ];
    }
}
