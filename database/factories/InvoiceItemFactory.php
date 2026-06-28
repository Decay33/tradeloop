<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InvoiceItem> */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'invoice_id' => Invoice::factory(),
            'description' => fake()->sentence(3),
            'quantity' => 1,
            'unit_price_cents' => 100000,
            'line_total_cents' => 100000,
            'sort_order' => 1,
        ];
    }
}
