<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ServiceType> */
class ServiceTypeFactory extends Factory
{
    protected $model = ServiceType::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => fake()->randomElement(['General Handyman', 'Painting', 'Pressure Washing']).' '.fake()->unique()->numberBetween(1, 999),
            'category' => 'Home Services',
            'description' => fake()->sentence(),
            'default_price_cents' => fake()->numberBetween(25000, 250000),
            'default_repeat_months' => 12,
            'is_active' => true,
        ];
    }
}
