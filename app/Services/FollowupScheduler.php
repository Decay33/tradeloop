<?php

namespace App\Services;

use App\Models\FollowupMessage;
use App\Models\Job;
use App\Models\Customer;
use Carbon\CarbonInterface;

class FollowupScheduler
{
    public function __construct(
        private readonly TemplateRenderer $renderer,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function scheduleForCompletedJob(Job $job, ?array $messages = null, ?CarbonInterface $completedAt = null, bool $scheduleFollowups = true): int
    {
        $job->loadMissing(['business', 'customer', 'serviceType']);

        if ($job->followups_scheduled_at) {
            return 0;
        }

        $job->completed_at = $completedAt ?: $job->completed_at ?: now();

        if (! $scheduleFollowups) {
            $job->forceFill([
                'status' => 'completed',
                'completed_at' => $job->completed_at,
                'followups_scheduled_at' => now(),
            ])->save();

            $this->auditLogger->log('followups_skipped_on_completion', $job->business_id, $job);

            return 0;
        }

        $messages ??= $this->previewForCompletedJob($job, $job->completed_at);

        $count = 0;

        foreach ($messages as $row) {
            [$status, $skipReason] = $this->deliveryStatus($job, $row['channel']);

            $message = FollowupMessage::create([
                'business_id' => $job->business_id,
                'customer_id' => $job->customer_id,
                'job_id' => $job->id,
                'template_id' => $row['template_id'] ?? null,
                'channel' => $row['channel'],
                'purpose' => $row['purpose'],
                'status' => $status,
                'scheduled_at' => $row['scheduled_at'],
                'recipient' => $row['recipient'] ?? ($row['channel'] === 'sms' ? $job->customer->phone : $job->customer->email),
                'subject' => $row['subject'] ?? null,
                'body' => $row['body'],
                'skip_reason' => $skipReason,
            ]);

            $message->events()->create([
                'business_id' => $job->business_id,
                'event_type' => 'created',
                'event_data' => ['source' => 'job_completion_review'],
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

    public function previewForCompletedJob(Job $job, ?CarbonInterface $completedAt = null): array
    {
        $job->loadMissing(['business', 'customer', 'serviceType']);
        $job->completed_at = $completedAt ?: $job->completed_at ?: now();

        return $job->business->followupRules()
            ->with('template')
            ->where('service_type_id', $job->service_type_id)
            ->where('trigger_event', 'job_completed')
            ->where('is_active', true)
            ->orderBy('delay_amount')
            ->get()
            ->map(function ($rule) use ($job) {
                $scheduledAt = $this->scheduledAt($job, $rule->delay_amount, $rule->delay_unit);
                [$status, $skipReason] = $this->deliveryStatus($job, $rule->channel);

                return [
                    'template_id' => $rule->template_id,
                    'rule_id' => $rule->id,
                    'channel' => $rule->channel,
                    'purpose' => $rule->purpose,
                    'status' => $status,
                    'scheduled_at' => $scheduledAt->format('Y-m-d\TH:i'),
                    'recipient' => $rule->channel === 'sms' ? $job->customer->phone : $job->customer->email,
                    'subject' => $this->renderer->render($rule->template?->subject, $job->business, $job->customer, $job),
                    'body' => $this->renderer->render($rule->template?->body, $job->business, $job->customer, $job),
                    'skip_reason' => $skipReason,
                ];
            })
            ->values()
            ->all();
    }

    public function manualMessageStatus(Customer $customer, string $channel): array
    {
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
        return $this->manualMessageStatus($job->customer, $channel);
    }
}
