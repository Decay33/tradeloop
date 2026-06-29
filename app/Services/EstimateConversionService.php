<?php

namespace App\Services;

use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EstimateConversionService
{
    public function __construct(
        private readonly NumberGenerator $numbers,
        private readonly InvoiceCalculator $invoiceCalculator,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function convert(Estimate $estimate, array $data = []): array
    {
        $estimate->loadMissing(['business', 'customer', 'serviceType', 'items', 'job', 'invoice']);

        if ($estimate->status !== 'accepted') {
            throw ValidationException::withMessages(['estimate' => 'Only accepted estimates can be converted.']);
        }

        if ($estimate->job || $estimate->invoice) {
            return [
                'job' => $estimate->job,
                'invoice' => $estimate->invoice,
                'created' => false,
            ];
        }

        return DB::transaction(function () use ($estimate, $data) {
            $title = $data['job_title'] ?? $estimate->serviceType->name.' for '.$estimate->customer->display_name;
            $createInvoice = array_key_exists('create_invoice', $data) ? (bool) $data['create_invoice'] : true;

            $job = Job::create([
                'business_id' => $estimate->business_id,
                'customer_id' => $estimate->customer_id,
                'estimate_id' => $estimate->id,
                'service_type_id' => $estimate->service_type_id,
                'quoted_total_cents' => $estimate->total_cents,
                'assigned_user_id' => $data['assigned_user_id'] ?? null,
                'title' => $title,
                'status' => 'scheduled',
                'scheduled_date' => $data['scheduled_date'] ?? null,
                'job_address' => $data['job_address'] ?? $estimate->customer->full_address,
                'notes' => $data['job_notes'] ?? null,
            ]);

            $invoice = null;

            if ($createInvoice) {
                $invoice = Invoice::create([
                    'business_id' => $estimate->business_id,
                    'customer_id' => $estimate->customer_id,
                    'estimate_id' => $estimate->id,
                    'job_id' => $job->id,
                    'invoice_number' => $this->numbers->invoiceNumber($estimate->business),
                    'status' => 'draft',
                    'discount_cents' => $estimate->discount_cents,
                    'tax_rate' => $data['invoice_tax_rate'] ?? $estimate->tax_rate,
                    'due_date' => $data['invoice_due_date'] ?? now()->addDays(14)->toDateString(),
                ]);

                $items = $data['invoice_items'] ?? $estimate->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price_cents' => $item->unit_price_cents,
                ])->all();

                $this->invoiceCalculator->sync($invoice, $items, $data['invoice_discount'] ?? ($estimate->discount_cents / 100), $data['invoice_tax_rate'] ?? $estimate->tax_rate);

                $job->forceFill(['invoice_id' => $invoice->id])->save();
                $this->auditLogger->log('invoice_created', $estimate->business_id, $invoice);
            }

            $this->auditLogger->log('job_created', $estimate->business_id, $job);

            return [
                'job' => $job->refresh(),
                'invoice' => $invoice?->refresh(),
                'created' => true,
            ];
        });
    }
}
