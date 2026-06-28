<?php

namespace App\Http\Controllers;

use App\Services\CurrentBusinessResolver;
use App\Services\ReportService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(ReportService $reports): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();

        return Inertia::render('Dashboard/Index', [
            'metrics' => $reports->summary($business),
            'recentCustomers' => $business->customers()->latest()->limit(5)->get(),
            'recentJobs' => $business->jobs()->with('customer', 'serviceType')->latest()->limit(5)->get(),
            'upcomingFollowups' => $business->followupMessages()->with('customer', 'job.serviceType')->where('status', 'scheduled')->orderBy('scheduled_at')->limit(6)->get(),
            'unpaidInvoices' => $business->invoices()->with('customer')->where('balance_due_cents', '>', 0)->whereNotIn('status', ['paid', 'void'])->orderBy('due_date')->limit(6)->get(),
        ]);
    }
}
