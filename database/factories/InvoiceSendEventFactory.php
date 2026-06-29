<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\InvoiceSendEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InvoiceSendEvent> */
class InvoiceSendEventFactory extends Factory
{
    protected $model = InvoiceSendEvent::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'invoice_id' => Invoice::factory(),
            'user_id' => User::factory(),
            'recipient' => fake()->safeEmail(),
            'subject' => 'Invoice ready',
            'body' => 'This is a simulated invoice email.',
            'status' => 'simulated_sent',
            'sent_at' => now(),
        ];
    }
}
