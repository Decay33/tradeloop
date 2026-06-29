<?php

namespace App\Http\Controllers;

use App\Http\Requests\FollowupRescheduleRequest;
use App\Http\Requests\ManualFollowupRequest;
use App\Models\Customer;
use App\Models\FollowupMessage;
use App\Services\AuditLogger;
use App\Services\CurrentBusinessResolver;
use App\Services\DemoMessageSender;
use App\Services\FollowupScheduler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class FollowupController extends Controller
{
    public function index(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $filter = request('filter', request('status') ?: 'due_today');

        return Inertia::render('FollowUps/Index', [
            'filters' => ['filter' => $filter, 'purpose' => request('purpose')],
            'messages' => $business->followupMessages()
                ->with('customer', 'job.serviceType', 'estimate')
                ->tap(fn ($query) => $this->applySmartFilter($query, $filter))
                ->when(request('purpose'), fn ($query, $purpose) => $query->where('purpose', $purpose))
                ->orderByRaw("case when status = 'scheduled' then 0 else 1 end")
                ->orderBy('scheduled_at')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(FollowupMessage $followupMessage): Response
    {
        $this->authorize('view', $followupMessage);

        return Inertia::render('FollowUps/Show', [
            'message' => $followupMessage->load('customer', 'estimate', 'job.serviceType', 'createdBy', 'events'),
        ]);
    }

    public function create(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $selectedCustomerId = request()->integer('customer_id') ?: null;
        $selectedEstimateId = request()->integer('estimate_id') ?: null;
        $selectedJobId = request()->integer('job_id') ?: null;

        $estimate = $selectedEstimateId ? $business->estimates()->whereKey($selectedEstimateId)->first() : null;
        $job = $selectedJobId ? $business->jobs()->whereKey($selectedJobId)->first() : null;

        if ($job) {
            $selectedCustomerId = $job->customer_id;
        } elseif ($estimate) {
            $selectedCustomerId = $estimate->customer_id;
        } elseif ($selectedCustomerId && ! $business->customers()->whereKey($selectedCustomerId)->exists()) {
            $selectedCustomerId = null;
        }

        return Inertia::render('FollowUps/Create', [
            'customers' => $business->customers()->orderBy('first_name')->get(),
            'estimates' => $business->estimates()->with('customer')->latest()->limit(100)->get(),
            'jobs' => $business->jobs()->with('customer')->latest()->limit(100)->get(),
            'selectedCustomerId' => $selectedCustomerId,
            'selectedEstimateId' => $estimate?->id,
            'selectedJobId' => $job?->id,
        ]);
    }

    public function store(ManualFollowupRequest $request, FollowupScheduler $scheduler, AuditLogger $auditLogger)
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $customer = $business->customers()->whereKey($request->integer('customer_id'))->firstOrFail();
        $estimate = $request->integer('estimate_id') ? $business->estimates()->whereKey($request->integer('estimate_id'))->firstOrFail() : null;
        $job = $request->integer('job_id') ? $business->jobs()->whereKey($request->integer('job_id'))->firstOrFail() : null;

        if (($estimate && (int) $estimate->customer_id !== (int) $customer->id) || ($job && (int) $job->customer_id !== (int) $customer->id)) {
            throw ValidationException::withMessages(['customer_id' => 'Selected records must belong to the same customer.']);
        }

        [$status, $skipReason] = $scheduler->manualMessageStatus($customer, $request->input('channel'));

        $message = FollowupMessage::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'estimate_id' => $estimate?->id,
            'job_id' => $job?->id,
            'created_by_user_id' => Auth::id(),
            'is_manual' => true,
            'channel' => $request->input('channel'),
            'purpose' => $request->input('purpose'),
            'status' => $status,
            'scheduled_at' => $request->date('scheduled_at'),
            'recipient' => $request->input('channel') === 'sms' ? $customer->phone : $customer->email,
            'subject' => $request->input('subject'),
            'body' => $request->input('body'),
            'skip_reason' => $skipReason,
        ]);

        $message->events()->create([
            'business_id' => $business->id,
            'event_type' => $status === 'skipped' ? 'skipped' : 'created',
            'event_data' => ['manual' => true, 'reason' => $skipReason],
            'created_at' => now(),
        ]);
        $auditLogger->log('manual_followup_created', $business, $message);

        return redirect()->route('follow-ups.show', $message)->with('success', $status === 'skipped' ? 'Follow-up saved as skipped because consent or contact info is missing.' : 'Follow-up scheduled.');
    }

    public function sendNow(FollowupMessage $followupMessage, DemoMessageSender $sender)
    {
        $this->authorize('update', $followupMessage);
        $sender->send($followupMessage);

        return back()->with('success', 'Follow-up simulated.');
    }

    public function cancel(FollowupMessage $followupMessage, AuditLogger $auditLogger)
    {
        $this->authorize('update', $followupMessage);
        $followupMessage->forceFill([
            'status' => 'canceled',
            'canceled_at' => now(),
        ])->save();
        $followupMessage->events()->create([
            'business_id' => $followupMessage->business_id,
            'event_type' => 'canceled',
            'created_at' => now(),
        ]);
        $auditLogger->log('message_canceled', $followupMessage->business_id, $followupMessage);

        return back()->with('success', 'Follow-up canceled.');
    }

    public function reschedule(FollowupRescheduleRequest $request, FollowupMessage $followupMessage, AuditLogger $auditLogger)
    {
        $this->authorize('update', $followupMessage);
        $followupMessage->forceFill([
            'status' => 'scheduled',
            'scheduled_at' => $request->date('scheduled_at'),
            'canceled_at' => null,
            'sent_at' => null,
        ])->save();
        $auditLogger->log('message_rescheduled', $followupMessage->business_id, $followupMessage);

        return back()->with('success', 'Follow-up rescheduled.');
    }

    private function applySmartFilter($query, ?string $filter): void
    {
        match ($filter) {
            'due_today' => $query->where('status', 'scheduled')->whereBetween('scheduled_at', [now()->startOfDay(), now()->endOfDay()]),
            'upcoming' => $query->where('status', 'scheduled')->where('scheduled_at', '>', now()->endOfDay()),
            'sales_follow_ups' => $query->where('purpose', 'sales_follow_up'),
            'job_follow_ups' => $query->whereNotNull('job_id')->where('is_manual', false),
            'sent' => $query->whereIn('status', ['sent', 'simulated_sent']),
            'skipped' => $query->where('status', 'skipped'),
            'canceled' => $query->where('status', 'canceled'),
            'all' => null,
            default => null,
        };
    }
}
