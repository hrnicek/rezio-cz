<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        return [
            'user_id' => \App\Models\User::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(6),
            'address' => $this->faker->address(),
            'description' => $this->faker->sentence(),
            'price_per_night' => $this->faker->numberBetween(50, 500),
            'widget_token' => Str::random(32),
        ];
    }
}
