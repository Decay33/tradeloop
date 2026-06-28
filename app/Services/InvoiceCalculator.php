<?php

namespace App\Services;

use App\Models\Invoice;
use App\Support\Money;

class InvoiceCalculator
{
    public function __construct(private readonly LineItemCalculator $lineItems)
    {
    }

    public function sync(Invoice $invoice, array $items, mixed $discount, mixed $taxRate): Invoice
    {
        $normalized = $this->lineItems->normalize($items);
        $totals = $this->lineItems->totals($normalized, Money::fromInput($discount), (float) $taxRate);

        $invoice->items()->delete();

        foreach ($normalized as $item) {
            $invoice->items()->create([
                'business_id' => $invoice->business_id,
                ...$item,
            ]);
        }

        $paid = (int) $invoice->payments()->sum('amount_cents');

        $invoice->forceFill([
            ...$totals,
            'amount_paid_cents' => $paid,
            'balance_due_cents' => max(0, $totals['total_cents'] - $paid),
        ])->save();

        return $invoice->refresh();
    }

    public function recalculatePayments(Invoice $invoice): Invoice
    {
        $paid = (int) $invoice->payments()->sum('amount_cents');
        $balance = max(0, $invoice->total_cents - $paid);
        $status = $invoice->status;
        $paidAt = $invoice->paid_at;

        if ($status !== 'void') {
            if ($balance === 0 && $invoice->total_cents > 0) {
                $status = 'paid';
                $paidAt ??= now();
            } elseif ($paid > 0) {
                $status = 'partially_paid';
                $paidAt = null;
            }
        }

        $invoice->forceFill([
            'amount_paid_cents' => $paid,
            'balance_due_cents' => $balance,
            'status' => $status,
            'paid_at' => $paidAt,
        ])->save();

        return $invoice->refresh();
    }
}
