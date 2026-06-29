<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompleteJobRequest;
use App\Http\Requests\JobRequest;
use App\Models\Job;
use App\Services\AuditLogger;
use App\Services\CurrentBusinessResolver;
use App\Services\FollowupScheduler;
use App\Services\InvoiceCalculator;
use App\Services\NumberGenerator;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class JobController extends Controller
{
    public function index(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $filter = request('filter', request('status'));

        return Inertia::render('Jobs/Index', [
            'filters' => ['filter' => $filter, 'assigned_user_id' => request('assigned_user_id')],
            'jobs' => $business->jobs()
                ->with('customer', 'serviceType', 'invoice', 'assignedUser')
                ->tap(fn ($query) => $this->applySmartFilter($query, $filter))
                ->when(request('assigned_user_id'), fn ($query, $id) => $query->where('assigned_user_id', $id))
                ->latest()
                ->paginate(12)
                ->withQueryString(),
            'teamMembers' => $business->users()->wherePivot('is_active', true)->orderBy('name')->get(['users.id', 'users.name']),
        ]);
    }

    public function create(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $selectedCustomerId = request()->integer('customer_id') ?: null;

        if ($selectedCustomerId && ! $business->customers()->whereKey($selectedCustomerId)->exists()) {
            $selectedCustomerId = null;
        }

        return Inertia::render('Jobs/Create', [
            'customers' => $business->customers()->orderBy('first_name')->get(),
            'serviceTypes' => $business->serviceTypes()->where('is_active', true)->orderBy('name')->get(),
            'teamMembers' => $business->users()->wherePivot('is_active', true)->orderBy('name')->get(['users.id', 'users.name']),
            'selectedCustomerId' => $selectedCustomerId,
        ]);
    }

    public function store(JobRequest $request, AuditLogger $auditLogger)
    {
        $business = app(CurrentBusinessResolver::class)->resolve();

        $data = $request->validated();
        unset($data['quoted_price']);

        $job = $business->jobs()->create([
            ...$data,
            'quoted_total_cents' => $request->filled('quoted_total_cents')
                ? $request->integer('quoted_total_cents')
                : Money::fromInput($request->input('quoted_price')),
            'status' => $request->input('status', 'scheduled'),
        ]);

        $auditLogger->log('job_created', $business, $job);

        return redirect()->route('jobs.show', $job)->with('success', 'Job created.');
    }

    public function show(Job $job, FollowupScheduler $scheduler): Response
    {
        $this->authorize('view', $job);

        return Inertia::render('Jobs/Show', [
            'job' => $job->load('customer', 'serviceType', 'estimate', 'invoice.payments', 'assignedUser', 'startedBy', 'completedBy', 'followupMessages'),
            'completionPreview' => $job->followups_scheduled_at ? [] : $scheduler->previewForCompletedJob($job),
        ]);
    }

    public function edit(Job $job): Response
    {
        $this->authorize('update', $job);
        $business = app(CurrentBusinessResolver::class)->resolve();

        return Inertia::render('Jobs/Edit', [
            'job' => $job,
            'customers' => $business->customers()->orderBy('first_name')->get(),
            'serviceTypes' => $business->serviceTypes()->where('is_active', true)->orderBy('name')->get(),
            'teamMembers' => $business->users()->wherePivot('is_active', true)->orderBy('name')->get(['users.id', 'users.name']),
        ]);
    }

    public function update(JobRequest $request, Job $job)
    {
        $this->authorize('update', $job);
        $data = $request->validated();
        unset($data['quoted_price']);
        $data['quoted_total_cents'] = $request->filled('quoted_total_cents')
            ? $request->integer('quoted_total_cents')
            : Money::fromInput($request->input('quoted_price'));

        $job->update($data);

        return redirect()->route('jobs.show', $job)->with('success', 'Job updated.');
    }

    public function destroy(Job $job)
    {
        $this->authorize('delete', $job);
        $job->delete();

        return redirect()->route('jobs.index')->with('success', 'Job deleted.');
    }

    public function start(Job $job, AuditLogger $auditLogger)
    {
        $this->authorize('update', $job);
        $job->forceFill([
            'status' => 'in_progress',
            'started_at' => $job->started_at ?: now(),
            'started_by_user_id' => $job->started_by_user_id ?: Auth::id(),
        ])->save();
        $auditLogger->log('job_started', $job->business_id, $job);

        return back()->with('success', 'Job started.');
    }

    public function complete(CompleteJobRequest $request, Job $job, FollowupScheduler $scheduler, AuditLogger $auditLogger)
    {
        $this->authorize('update', $job);
        abort_if($job->status === 'canceled', 422, 'Canceled jobs cannot be completed.');

        $job->forceFill(['completed_by_user_id' => $job->completed_by_user_id ?: Auth::id()])->save();

        $scheduleFollowups = $request->has('schedule_followups') ? $request->boolean('schedule_followups') : true;
        $followups = $request->input('followups');

        $scheduler->scheduleForCompletedJob($job->refresh(), $followups, $request->date('completed_at'), $scheduleFollowups);
        $auditLogger->log('job_completed', $job->business_id, $job);

        return back()->with('success', $scheduleFollowups ? 'Job completed and follow-ups scheduled.' : 'Job completed without follow-ups.');
    }

    public function cancel(Job $job, AuditLogger $auditLogger)
    {
        $this->authorize('update', $job);
        $job->forceFill([
            'status' => 'canceled',
            'canceled_at' => $job->canceled_at ?: now(),
        ])->save();
        $auditLogger->log('job_canceled', $job->business_id, $job);

        return back()->with('success', 'Job canceled.');
    }

    public function createInvoice(Job $job, InvoiceCalculator $calculator, NumberGenerator $numbers, AuditLogger $auditLogger)
    {
        $this->authorize('update', $job);
        abort_if($job->invoice_id, 422, 'This job already has an invoice.');

        $invoice = DB::transaction(function () use ($job, $calculator, $numbers, $auditLogger) {
            $job->loadMissing('business', 'serviceType');
            $invoice = $job->business->invoices()->create([
                'customer_id' => $job->customer_id,
                'estimate_id' => $job->estimate_id,
                'job_id' => $job->id,
                'invoice_number' => $numbers->invoiceNumber($job->business),
                'status' => 'draft',
                'due_date' => now()->addDays(14)->toDateString(),
                'tax_rate' => $job->business->default_tax_rate,
            ]);

            $calculator->sync($invoice, [[
                'description' => $job->title,
                'quantity' => 1,
                'unit_price_cents' => $job->quoted_total_cents ?: ($job->serviceType?->default_price_cents ?? 0),
            ]], 0, $job->business->default_tax_rate);

            $job->forceFill(['invoice_id' => $invoice->id])->save();
            $auditLogger->log('invoice_created_from_job', $job->business_id, $invoice);

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created from job.');
    }

    private function applySmartFilter($query, ?string $filter): void
    {
        match ($filter) {
            'scheduled', 'in_progress', 'completed', 'canceled' => $query->where('status', $filter),
            'no_invoice' => $query->whereNull('invoice_id'),
            'unassigned' => $query->whereNull('assigned_user_id'),
            'assigned_to_me' => $query->where('assigned_user_id', Auth::id()),
            default => null,
        };
    }
}
