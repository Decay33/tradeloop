<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\FollowupTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FollowupTemplate> */
class FollowupTemplateFactory extends Factory
{
    protected $model = FollowupTemplate::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => fake()->words(3, true),
            'channel' => 'sms',
            'purpose' => 'thank_you',
            'subject' => null,
            'body' => 'Hi {{customer_first_name}}, thanks for choosing {{business_name}}.',
            'is_default' => true,
        ];
    }
}
