<?php

namespace App\Services;

use App\Models\Business;
use App\Models\FollowupMessage;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class ReportService
{
    public function summary(Business $business, ?Carbon $start = null, ?Carbon $end = null): array
    {
        $start ??= now()->startOfMonth();
        $end ??= now()->endOfMonth();

        $payments = $this->paymentsInRange($business, $start, $end);
        $completedJobs = $business->jobs()->where('status', 'completed')->whereBetween('completed_at', [$start, $end]);

        $revenue = (int) (clone $payments)->sum('amount_cents');
        $completedCount = (clone $completedJobs)->count();

        return [
            'revenue_this_month' => (int) $this->paymentsInRange($business, now()->startOfMonth(), now()->endOfMonth())->sum('amount_cents'),
            'open_estimate_value' => (int) $business->estimates()->whereIn('status', ['draft', 'sent'])->sum('total_cents'),
            'accepted_estimate_value' => (int) $business->estimates()->where('status', 'accepted')->sum('total_cents'),
            'unpaid_invoices' => (int) $business->invoices()->where('balance_due_cents', '>', 0)->whereNotIn('status', ['paid', 'void'])->sum('balance_due_cents'),
            'overdue_invoices' => (int) $business->invoices()
                ->where('balance_due_cents', '>', 0)
                ->whereNotIn('status', ['paid', 'void'])
                ->whereDate('due_date', '<', now()->toDateString())
                ->sum('balance_due_cents'),
            'jobs_completed' => $completedCount,
            'average_job_value' => $completedCount > 0 ? (int) round($revenue / $completedCount) : 0,
            'review_requests_sent' => $business->followupMessages()
                ->where('purpose', 'review_request')
                ->whereIn('status', ['simulated_sent', 'sent'])
                ->whereBetween('sent_at', [$start, $end])
                ->count(),
            'followups_due_today' => $business->followupMessages()
                ->where('status', 'scheduled')
                ->whereBetween('scheduled_at', [now()->startOfDay(), now()->endOfDay()])
                ->count(),
            'estimate_win_rate' => $this->winRate($business),
            'repeat_revenue_opportunity' => $this->repeatOpportunity($business)['estimated_cents'],
        ];
    }

    public function full(Business $business, ?Carbon $start = null, ?Carbon $end = null): array
    {
        $start ??= now()->startOfMonth();
        $end ??= now()->endOfMonth();

        return [
            'metrics' => $this->summary($business, $start, $end),
            'range' => ['start' => $start->toDateString(), 'end' => $end->toDateString()],
            'daily_snapshot' => $this->dailySnapshot($business, $start, $end),
            'sales_pipeline' => $this->salesPipeline($business),
            'job_activity' => $this->jobActivity($business, $start, $end),
            'collections' => $this->collections($business, $start, $end),
            'followup_activity' => $this->followupActivity($business, $start, $end),
            'service_breakdown' => $this->serviceBreakdown($business, $start, $end),
            'revenue_by_month' => $this->revenueByMonth($business),
            'revenue_by_service_type' => $this->revenueByServiceType($business, $start, $end),
            'invoice_aging' => $this->invoiceAging($business),
            'repeat_opportunity' => $this->repeatOpportunity($business),
        ];
    }

    private function winRate(Business $business): int
    {
        $accepted = $business->estimates()->where('status', 'accepted')->count();
        $declined = $business->estimates()->where('status', 'declined')->count();
        $denominator = $accepted + $declined;

        return $denominator > 0 ? (int) round(($accepted / $denominator) * 100) : 0;
    }

    private function revenueByMonth(Business $business): array
    {
        return Payment::query()
            ->forBusiness($business)
            ->orderBy('payment_date')
            ->get()
            ->groupBy(fn (Payment $payment) => $payment->payment_date->format('Y-m'))
            ->map(fn (Collection $payments, string $month) => [
                'month' => $month,
                'total_cents' => (int) $payments->sum('amount_cents'),
            ])
            ->values()
            ->all();
    }

    private function revenueByServiceType(Business $business, ?Carbon $start = null, ?Carbon $end = null): array
    {
        $payments = Payment::query()
            ->forBusiness($business)
            ->with('invoice.job.serviceType', 'invoice.estimate.serviceType')
            ->when($start && $end, fn ($query) => $query->whereDate('payment_date', '>=', $start->toDateString())->whereDate('payment_date', '<=', $end->toDateString()))
            ->get();

        return $payments
            ->groupBy(function (Payment $payment) {
                return $payment->invoice?->job?->serviceType?->name
                    ?: $payment->invoice?->estimate?->serviceType?->name
                    ?: 'Uncategorized';
            })
            ->map(fn (Collection $payments, string $name) => [
                'service_type' => $name,
                'total_cents' => (int) $payments->sum('amount_cents'),
            ])
            ->values()
            ->all();
    }

    private function invoiceAging(Business $business): array
    {
        $buckets = [
            'Current' => 0,
            '1-30 days overdue' => 0,
            '31-60 days overdue' => 0,
            '61-90 days overdue' => 0,
            '90+ days overdue' => 0,
        ];

        Invoice::query()
            ->forBusiness($business)
            ->where('balance_due_cents', '>', 0)
            ->whereNotIn('status', ['paid', 'void'])
            ->get()
            ->each(function (Invoice $invoice) use (&$buckets) {
                $days = $invoice->due_date ? $invoice->due_date->diffInDays(now(), false) : 0;
                $bucket = match (true) {
                    $days <= 0 => 'Current',
                    $days <= 30 => '1-30 days overdue',
                    $days <= 60 => '31-60 days overdue',
                    $days <= 90 => '61-90 days overdue',
                    default => '90+ days overdue',
                };

                $buckets[$bucket] += $invoice->balance_due_cents;
            });

        return collect($buckets)->map(fn (int $cents, string $bucket) => [
            'bucket' => $bucket,
            'total_cents' => $cents,
        ])->values()->all();
    }

    private function repeatOpportunity(Business $business): array
    {
        $messages = FollowupMessage::query()
            ->forBusiness($business)
            ->with('customer', 'job.serviceType')
            ->where('status', 'scheduled')
            ->whereIn('purpose', ['repeat_service', 'seasonal_reminder', 'warranty_check'])
            ->whereBetween('scheduled_at', [now(), now()->addDays(90)])
            ->orderBy('scheduled_at')
            ->get();

        $businessAverage = max(0, (int) round((Payment::query()->forBusiness($business)->sum('amount_cents') ?: 0) / max(1, $business->jobs()->where('status', 'completed')->count())));
        $estimated = 0;

        $top = $messages->take(8)->map(function (FollowupMessage $message) use (&$estimated, $businessAverage) {
            $service = $message->job?->serviceType;
            $value = $service?->default_price_cents ?: $businessAverage;
            $estimated += $value;

            return [
                'customer' => $message->customer?->display_name,
                'service_type' => $service?->name ?: 'Uncategorized',
                'scheduled_at' => $message->scheduled_at?->toDateString(),
                'estimated_cents' => $value,
            ];
        })->values()->all();

        if ($messages->count() > count($top)) {
            $estimated += ($messages->count() - count($top)) * $businessAverage;
        }

        return [
            'customers_due' => $messages->pluck('customer_id')->unique()->count(),
            'estimated_cents' => $estimated,
            'top' => $top,
        ];
    }

    private function dailySnapshot(Business $business, Carbon $start, Carbon $end): array
    {
        return collect(CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay()))
            ->map(function (Carbon $date) use ($business) {
                $dayStart = $date->copy()->startOfDay();
                $dayEnd = $date->copy()->endOfDay();

                return [
                    'date' => $date->toDateString(),
                    'estimates_created' => $business->estimates()->whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                    'estimate_value_cents' => (int) $business->estimates()->whereBetween('created_at', [$dayStart, $dayEnd])->sum('total_cents'),
                    'jobs_created' => $business->jobs()->whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                    'jobs_completed' => $business->jobs()->whereBetween('completed_at', [$dayStart, $dayEnd])->count(),
                    'invoices_created' => $business->invoices()->whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                    'payments_collected_cents' => (int) Payment::query()->forBusiness($business)->whereDate('payment_date', $date->toDateString())->sum('amount_cents'),
                    'followups_sent' => $business->followupMessages()->whereIn('status', ['sent', 'simulated_sent'])->whereBetween('sent_at', [$dayStart, $dayEnd])->count(),
                ];
            })
            ->values()
            ->all();
    }

    private function salesPipeline(Business $business): array
    {
        $accepted = $business->estimates()->where('status', 'accepted');

        return [
            'open_estimate_value_cents' => (int) $business->estimates()->whereIn('status', ['draft', 'sent'])->sum('total_cents'),
            'accepted_estimate_value_cents' => (int) (clone $accepted)->sum('total_cents'),
            'estimates_needing_followup' => $business->estimates()->where('status', 'sent')->whereDate('sent_at', '<=', now()->subDays(7)->toDateString())->doesntHave('job')->count(),
            'accepted_without_jobs' => (clone $accepted)->doesntHave('job')->count(),
            'estimate_win_rate' => $this->winRate($business),
        ];
    }

    private function jobActivity(Business $business, Carbon $start, Carbon $end): array
    {
        return [
            'jobs_created' => $business->jobs()->whereBetween('created_at', [$start, $end])->count(),
            'jobs_scheduled' => $business->jobs()->where('status', 'scheduled')->whereBetween('scheduled_date', [$start->toDateString(), $end->toDateString()])->count(),
            'jobs_started' => $business->jobs()->whereBetween('started_at', [$start, $end])->count(),
            'jobs_completed' => $business->jobs()->whereBetween('completed_at', [$start, $end])->count(),
            'jobs_canceled' => $business->jobs()->whereBetween('canceled_at', [$start, $end])->count(),
            'jobs_with_no_invoice' => $business->jobs()->whereNull('invoice_id')->count(),
            'by_assigned_user' => $business->jobs()->with('assignedUser')->get()->groupBy(fn ($job) => $job->assignedUser?->name ?: 'Unassigned')->map(fn (Collection $jobs, string $name) => [
                'name' => $name,
                'count' => $jobs->count(),
            ])->values()->all(),
        ];
    }

    private function collections(Business $business, Carbon $start, Carbon $end): array
    {
        return [
            'payments_collected_cents' => (int) $this->paymentsInRange($business, $start, $end)->sum('amount_cents'),
            'unpaid_invoices_cents' => (int) $business->invoices()->where('balance_due_cents', '>', 0)->whereNotIn('status', ['paid', 'void'])->sum('balance_due_cents'),
            'past_due_invoices_cents' => (int) $business->invoices()->where('balance_due_cents', '>', 0)->whereDate('due_date', '<', now()->toDateString())->sum('balance_due_cents'),
            'overdue_30_cents' => (int) $business->invoices()->where('balance_due_cents', '>', 0)->whereDate('due_date', '<=', now()->subDays(30)->toDateString())->sum('balance_due_cents'),
            'overdue_60_cents' => (int) $business->invoices()->where('balance_due_cents', '>', 0)->whereDate('due_date', '<=', now()->subDays(60)->toDateString())->sum('balance_due_cents'),
            'invoice_aging' => $this->invoiceAging($business),
        ];
    }

    private function followupActivity(Business $business, Carbon $start, Carbon $end): array
    {
        return [
            'scheduled' => $business->followupMessages()->where('status', 'scheduled')->whereBetween('scheduled_at', [$start, $end])->count(),
            'sent' => $business->followupMessages()->whereIn('status', ['sent', 'simulated_sent'])->whereBetween('sent_at', [$start, $end])->count(),
            'skipped' => $business->followupMessages()->where('status', 'skipped')->whereBetween('created_at', [$start, $end])->count(),
            'review_requests_sent' => $business->followupMessages()->where('purpose', 'review_request')->whereIn('status', ['sent', 'simulated_sent'])->whereBetween('sent_at', [$start, $end])->count(),
            'sales_followups_due' => $business->followupMessages()->where('purpose', 'sales_follow_up')->where('status', 'scheduled')->whereBetween('scheduled_at', [$start, $end])->count(),
            'repeat_followups_due' => $business->followupMessages()->whereIn('purpose', ['repeat_service', 'seasonal_reminder', 'warranty_check'])->where('status', 'scheduled')->whereBetween('scheduled_at', [$start, $end])->count(),
        ];
    }

    private function serviceBreakdown(Business $business, Carbon $start, Carbon $end): array
    {
        $revenue = collect($this->revenueByServiceType($business, $start, $end))->keyBy('service_type');

        return $business->serviceTypes()->with(['jobs' => fn ($query) => $query->whereBetween('created_at', [$start, $end])])->get()
            ->map(function ($service) use ($business, $revenue) {
                $invoices = $business->invoices()->whereHas('job', fn ($query) => $query->where('service_type_id', $service->id))->get();
                $invoiceCount = $invoices->count();

                return [
                    'service_type' => $service->name,
                    'revenue_cents' => $revenue[$service->name]['total_cents'] ?? 0,
                    'jobs_count' => $service->jobs->count(),
                    'average_invoice_cents' => $invoiceCount > 0 ? (int) round($invoices->sum('total_cents') / $invoiceCount) : 0,
                    'repeat_opportunity_cents' => $service->default_price_cents * $business->followupMessages()->whereHas('job', fn ($query) => $query->where('service_type_id', $service->id))->where('status', 'scheduled')->whereIn('purpose', ['repeat_service', 'seasonal_reminder', 'warranty_check'])->count(),
                ];
            })
            ->values()
            ->all();
    }

    private function paymentsInRange(Business $business, Carbon $start, Carbon $end)
    {
        return Payment::query()
            ->forBusiness($business)
            ->whereDate('payment_date', '>=', $start->toDateString())
            ->whereDate('payment_date', '<=', $end->toDateString());
    }
}
