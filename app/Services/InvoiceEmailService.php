<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceSendEvent;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;

class InvoiceEmailService
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function send(Invoice $invoice): InvoiceSendEvent
    {
        $invoice->loadMissing('business', 'customer');

        $recipient = $invoice->customer?->email;
        $status = $recipient ? 'simulated_sent' : 'skipped';

        $event = $invoice->sendEvents()->create([
            'business_id' => $invoice->business_id,
            'user_id' => Auth::id(),
            'recipient' => $recipient,
            'subject' => 'Invoice '.$invoice->invoice_number.' from '.$invoice->business->name,
            'body' => "Hi {$invoice->customer?->display_name},\n\nYour invoice {$invoice->invoice_number} is ready. Balance due: ".Money::format($invoice->balance_due_cents).".\n\nThis is a simulated demo email.",
            'status' => $status,
            'sent_at' => $status === 'simulated_sent' ? now() : null,
        ]);

        if ($status === 'simulated_sent') {
            $invoice->forceFill([
                'status' => $invoice->status === 'draft' ? 'sent' : $invoice->status,
                'sent_at' => $invoice->sent_at ?: now(),
            ])->save();
        }

        $this->auditLogger->log('invoice_email_'.$status, $invoice->business_id, $invoice, ['event_id' => $event->id]);

        return $event;
    }
}
