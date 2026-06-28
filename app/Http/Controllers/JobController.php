<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobRequest;
use App\Models\Job;
use App\Services\AuditLogger;
use App\Services\CurrentBusinessResolver;
use App\Services\FollowupScheduler;
use Inertia\Inertia;
use Inertia\Response;

class JobController extends Controller
{
    public function index(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();

        return Inertia::render('Jobs/Index', [
            'filters' => ['status' => request('status')],
            'jobs' => $business->jobs()
                ->with('customer', 'serviceType', 'invoice')
                ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
                ->latest()
                ->paginate(12)
                ->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();

        return Inertia::render('Jobs/Create', [
            'customers' => $business->customers()->orderBy('first_name')->get(),
            'serviceTypes' => $business->serviceTypes()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(JobRequest $request, AuditLogger $auditLogger)
    {
        $business = app(CurrentBusinessResolver::class)->resolve();

        $job = $business->jobs()->create([
            ...$request->validated(),
            'status' => $request->input('status', 'scheduled'),
        ]);

        $auditLogger->log('job_created', $business, $job);

        return redirect()->route('jobs.show', $job)->with('success', 'Job created.');
    }

    public function show(Job $job): Response
    {
        $this->authorize('view', $job);

        return Inertia::render('Jobs/Show', [
            'job' => $job->load('customer', 'serviceType', 'estimate', 'invoice', 'followupMessages'),
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
        ]);
    }

    public function update(JobRequest $request, Job $job)
    {
        $this->authorize('update', $job);
        $job->update($request->validated());

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
        ])->save();
        $auditLogger->log('job_started', $job->business_id, $job);

        return back()->with('success', 'Job started.');
    }

    public function complete(Job $job, FollowupScheduler $scheduler, AuditLogger $auditLogger)
    {
        $this->authorize('update', $job);
        abort_if($job->status === 'canceled', 422, 'Canceled jobs cannot be completed.');

        $job->forceFill([
            'status' => 'completed',
            'completed_at' => $job->completed_at ?: now(),
        ])->save();

        $scheduler->scheduleForCompletedJob($job->refresh());
        $auditLogger->log('job_completed', $job->business_id, $job);

        return back()->with('success', 'Job completed and follow-ups scheduled.');
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
}
