<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Estimate;
use App\Models\EstimateItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EstimateItem> */
class EstimateItemFactory extends Factory
{
    protected $model = EstimateItem::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'estimate_id' => Estimate::factory(),
            'description' => fake()->sentence(3),
            'quantity' => 1,
            'unit_price_cents' => 100000,
            'line_total_cents' => 100000,
            'sort_order' => 1,
        ];
    }
}
