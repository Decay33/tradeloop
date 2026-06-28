<?php

namespace App\Services;

use App\Models\Business;
use App\Models\FollowupMessage;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    public function summary(Business $business, ?Carbon $start = null, ?Carbon $end = null): array
    {
        $start ??= now()->startOfMonth();
        $end ??= now()->endOfMonth();

        $payments = Payment::query()->forBusiness($business)->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()]);
        $completedJobs = $business->jobs()->where('status', 'completed')->whereBetween('completed_at', [$start, $end]);

        $revenue = (int) (clone $payments)->sum('amount_cents');
        $completedCount = (clone $completedJobs)->count();

        return [
            'revenue_this_month' => (int) Payment::query()
                ->forBusiness($business)
                ->whereBetween('payment_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->sum('amount_cents'),
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
        return [
            'metrics' => $this->summary($business, $start, $end),
            'revenue_by_month' => $this->revenueByMonth($business),
            'revenue_by_service_type' => $this->revenueByServiceType($business),
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

    private function revenueByServiceType(Business $business): array
    {
        $payments = Payment::query()
            ->forBusiness($business)
            ->with('invoice.job.serviceType', 'invoice.estimate.serviceType')
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
}
