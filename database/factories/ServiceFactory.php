<?php

namespace Database\Factories;

use App\Enums\ServicePriceType;
use App\Models\Configuration\Service;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Configuration\Service>
 */
class ServiceFactory extends Factory
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
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'price_type' => $this->faker->randomElement(ServicePriceType::cases()),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'max_quantity' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
