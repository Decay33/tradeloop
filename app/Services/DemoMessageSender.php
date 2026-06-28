<?php

namespace App\Services;

use App\Models\FollowupMessage;
use Illuminate\Validation\ValidationException;

class DemoMessageSender
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function send(FollowupMessage $message): FollowupMessage
    {
        $message->loadMissing('customer');

        if ($message->status !== 'scheduled') {
            throw ValidationException::withMessages(['message' => 'Only scheduled follow-ups can be sent.']);
        }

        $skipReason = $this->skipReason($message);

        if ($skipReason) {
            $message->forceFill([
                'status' => 'skipped',
                'skip_reason' => $skipReason,
            ])->save();

            $message->events()->create([
                'business_id' => $message->business_id,
                'event_type' => 'skipped',
                'event_data' => ['reason' => $skipReason],
                'created_at' => now(),
            ]);

            return $message->refresh();
        }

        $message->forceFill([
            'status' => config('tradeloop.demo_mode') ? 'simulated_sent' : 'failed',
            'sent_at' => config('tradeloop.demo_mode') ? now() : null,
            'skip_reason' => config('tradeloop.demo_mode') ? null : 'External sending is not configured for this demo MVP',
        ])->save();

        $eventType = config('tradeloop.demo_mode') ? 'simulated_sent' : 'failed';

        $message->events()->create([
            'business_id' => $message->business_id,
            'event_type' => $eventType,
            'event_data' => ['driver' => 'simulated'],
            'created_at' => now(),
        ]);

        $this->auditLogger->log($eventType === 'simulated_sent' ? 'message_simulated' : 'message_failed', $message->business_id, $message);

        return $message->refresh();
    }

    private function skipReason(FollowupMessage $message): ?string
    {
        $customer = $message->customer;

        if ($message->channel === 'sms') {
            return match (true) {
                ! $customer->phone => 'Missing phone number',
                ! $customer->sms_consent => 'SMS consent not granted',
                (bool) $customer->sms_opted_out_at => 'Customer opted out of SMS',
                default => null,
            };
        }

        if ($message->channel === 'email') {
            return match (true) {
                ! $customer->email => 'Missing email address',
                ! $customer->email_consent => 'Email consent not granted',
                (bool) $customer->email_opted_out_at => 'Customer opted out of email',
                default => null,
            };
        }

        return null;
    }
}
