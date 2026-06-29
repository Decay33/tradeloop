<?php

namespace App\Http\Controllers;

use App\Services\CurrentBusinessResolver;
use App\Services\ReportService;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __invoke(ReportService $reports): Response
    {
        [$start, $end] = $this->range(request('range', 'this_month'));
        $business = app(CurrentBusinessResolver::class)->resolve();

        return Inertia::render('Reports/Index', [
            'range' => request('range', 'this_month'),
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'report' => $reports->full($business, $start, $end),
        ]);
    }

    private function range(string $range): array
    {
        return match ($range) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'last_week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [Carbon::parse(request('start', now()->startOfMonth())), Carbon::parse(request('end', now()->endOfMonth()))],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}
