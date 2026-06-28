<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Support\Money;
use Illuminate\Validation\ValidationException;

class InvoicePaymentService
{
    public function __construct(
        private readonly InvoiceCalculator $calculator,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function record(Invoice $invoice, array $data): Payment
    {
        if ($invoice->status === 'void') {
            throw ValidationException::withMessages(['invoice' => 'Void invoices cannot accept payments.']);
        }

        $amount = array_key_exists('amount_cents', $data)
            ? (int) $data['amount_cents']
            : Money::fromInput($data['amount'] ?? 0);

        if ($amount <= 0 || $amount > $invoice->balance_due_cents) {
            throw ValidationException::withMessages(['amount' => 'Payment must be greater than zero and no more than the balance due.']);
        }

        $payment = $invoice->payments()->create([
            'business_id' => $invoice->business_id,
            'amount_cents' => $amount,
            'payment_method' => $data['payment_method'],
            'payment_date' => $data['payment_date'],
            'notes' => $data['notes'] ?? null,
        ]);

        $this->calculator->recalculatePayments($invoice);
        $this->auditLogger->log('payment_recorded', $invoice->business_id, $payment);

        return $payment;
    }
}
