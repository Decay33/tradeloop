<?php

namespace App\Services;

use App\Models\FollowupMessage;
use App\Models\Job;

class FollowupScheduler
{
    public function __construct(
        private readonly TemplateRenderer $renderer,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function scheduleForCompletedJob(Job $job): int
    {
        $job->loadMissing(['business', 'customer', 'serviceType']);

        if ($job->followups_scheduled_at) {
            return 0;
        }

        if (! $job->completed_at) {
            $job->completed_at = now();
        }

        $rules = $job->business->followupRules()
            ->with('template')
            ->where('service_type_id', $job->service_type_id)
            ->where('trigger_event', 'job_completed')
            ->where('is_active', true)
            ->orderBy('delay_amount')
            ->get();

        $count = 0;

        foreach ($rules as $rule) {
            $scheduledAt = $this->scheduledAt($job, $rule->delay_amount, $rule->delay_unit);
            $recipient = $rule->channel === 'sms' ? $job->customer->phone : $job->customer->email;
            [$status, $skipReason] = $this->deliveryStatus($job, $rule->channel);

            $message = FollowupMessage::create([
                'business_id' => $job->business_id,
                'customer_id' => $job->customer_id,
                'job_id' => $job->id,
                'template_id' => $rule->template_id,
                'channel' => $rule->channel,
                'purpose' => $rule->purpose,
                'status' => $status,
                'scheduled_at' => $scheduledAt,
                'recipient' => $recipient,
                'subject' => $this->renderer->render($rule->template?->subject, $job->business, $job->customer, $job),
                'body' => $this->renderer->render($rule->template?->body, $job->business, $job->customer, $job),
                'skip_reason' => $skipReason,
            ]);

            $message->events()->create([
                'business_id' => $job->business_id,
                'event_type' => 'created',
                'event_data' => ['rule_id' => $rule->id],
                'created_at' => now(),
            ]);

            if ($status === 'skipped') {
                $message->events()->create([
                    'business_id' => $job->business_id,
                    'event_type' => 'skipped',
                    'event_data' => ['reason' => $skipReason],
                    'created_at' => now(),
                ]);
            }

            $count++;
        }

        $job->forceFill([
            'status' => 'completed',
            'completed_at' => $job->completed_at,
            'followups_scheduled_at' => now(),
        ])->save();

        $this->auditLogger->log('followups_scheduled', $job->business_id, $job, ['messages' => $count]);

        return $count;
    }

    private function scheduledAt(Job $job, int $amount, string $unit)
    {
        $base = $job->completed_at ?: now();

        return match ($unit) {
            'weeks' => $base->copy()->addWeeks($amount),
            'months' => $base->copy()->addMonthsNoOverflow($amount),
            default => $base->copy()->addDays($amount),
        };
    }

    private function deliveryStatus(Job $job, string $channel): array
    {
        $customer = $job->customer;

        if ($channel === 'sms') {
            if (! $customer->phone) {
                return ['skipped', 'Missing phone number'];
            }

            if (! $customer->sms_consent) {
                return ['skipped', 'SMS consent not granted'];
            }

            if ($customer->sms_opted_out_at) {
                return ['skipped', 'Customer opted out of SMS'];
            }
        }

        if ($channel === 'email') {
            if (! $customer->email) {
                return ['skipped', 'Missing email address'];
            }

            if (! $customer->email_consent) {
                return ['skipped', 'Email consent not granted'];
            }

            if ($customer->email_opted_out_at) {
                return ['skipped', 'Customer opted out of email'];
            }
        }

        return ['scheduled', null];
    }
}
