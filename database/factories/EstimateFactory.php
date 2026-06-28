<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Estimate> */
class EstimateFactory extends Factory
{
    protected $model = Estimate::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'customer_id' => Customer::factory(),
            'service_type_id' => ServiceType::factory(),
            'estimate_number' => 'EST-'.fake()->unique()->numberBetween(1001, 9999),
            'status' => 'draft',
            'subtotal_cents' => 100000,
            'discount_cents' => 0,
            'tax_rate' => 0,
            'tax_cents' => 0,
            'total_cents' => 100000,
            'expires_at' => now()->addDays(30),
        ];
    }
}
