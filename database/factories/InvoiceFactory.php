<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Invoice> */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'customer_id' => Customer::factory(),
            'invoice_number' => 'INV-'.fake()->unique()->numberBetween(1001, 9999),
            'status' => 'sent',
            'subtotal_cents' => 100000,
            'discount_cents' => 0,
            'tax_rate' => 0,
            'tax_cents' => 0,
            'total_cents' => 100000,
            'amount_paid_cents' => 0,
            'balance_due_cents' => 100000,
            'due_date' => now()->addDays(14),
        ];
    }
}
