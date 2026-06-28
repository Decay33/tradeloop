<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Customer;
use App\Models\FollowupMessage;
use App\Models\FollowupTemplate;
use App\Models\Job;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FollowupMessage> */
class FollowupMessageFactory extends Factory
{
    protected $model = FollowupMessage::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'customer_id' => Customer::factory(),
            'job_id' => Job::factory(),
            'template_id' => FollowupTemplate::factory(),
            'channel' => 'sms',
            'purpose' => 'thank_you',
            'status' => 'scheduled',
            'scheduled_at' => now()->addDay(),
            'recipient' => '(555) 555-1212',
            'body' => 'Thanks for choosing us.',
        ];
    }
}
