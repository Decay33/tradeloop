<?php

namespace App\Http\Controllers;

use App\Http\Requests\FollowupRescheduleRequest;
use App\Models\FollowupMessage;
use App\Services\AuditLogger;
use App\Services\CurrentBusinessResolver;
use App\Services\DemoMessageSender;
use Inertia\Inertia;
use Inertia\Response;

class FollowupController extends Controller
{
    public function index(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();

        return Inertia::render('FollowUps/Index', [
            'filters' => ['status' => request('status'), 'purpose' => request('purpose')],
            'messages' => $business->followupMessages()
                ->with('customer', 'job.serviceType')
                ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
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
            'message' => $followupMessage->load('customer', 'job.serviceType', 'events'),
        ]);
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
}
