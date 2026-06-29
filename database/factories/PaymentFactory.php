<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'invoice_id' => Invoice::factory(),
            'recorded_by_user_id' => null,
            'amount_cents' => 50000,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ];
    }
}
