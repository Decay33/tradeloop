<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\FollowupMessage;
use App\Models\MessageEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MessageEvent> */
class MessageEventFactory extends Factory
{
    protected $model = MessageEvent::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'followup_message_id' => FollowupMessage::factory(),
            'event_type' => 'created',
            'event_data' => ['factory' => true],
            'created_at' => now(),
        ];
    }
}
