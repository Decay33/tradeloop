<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Business> */
class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'trade_type' => 'General Handyman',
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'website' => 'https://example.test',
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip' => fake()->postcode(),
            'timezone' => 'America/New_York',
            'google_review_url' => 'https://example.test/review',
            'default_tax_rate' => 7.5,
            'default_invoice_terms' => 'Payment due within 14 days.',
        ];
    }
}
