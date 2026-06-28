<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\FollowupRule;
use App\Models\FollowupTemplate;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FollowupRule> */
class FollowupRuleFactory extends Factory
{
    protected $model = FollowupRule::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'service_type_id' => ServiceType::factory(),
            'template_id' => FollowupTemplate::factory(),
            'trigger_event' => 'job_completed',
            'delay_amount' => 1,
            'delay_unit' => 'days',
            'channel' => 'sms',
            'purpose' => 'thank_you',
            'is_active' => true,
        ];
    }
}
