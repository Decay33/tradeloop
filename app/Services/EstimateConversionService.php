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

    public function convert(Estimate $estimate): array
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

        return DB::transaction(function () use ($estimate) {
            $title = $estimate->serviceType->name.' for '.$estimate->customer->display_name;

            $job = Job::create([
                'business_id' => $estimate->business_id,
                'customer_id' => $estimate->customer_id,
                'estimate_id' => $estimate->id,
                'service_type_id' => $estimate->service_type_id,
                'title' => $title,
                'status' => 'scheduled',
                'job_address' => $estimate->customer->full_address,
            ]);

            $invoice = Invoice::create([
                'business_id' => $estimate->business_id,
                'customer_id' => $estimate->customer_id,
                'estimate_id' => $estimate->id,
                'job_id' => $job->id,
                'invoice_number' => $this->numbers->invoiceNumber($estimate->business),
                'status' => 'draft',
                'discount_cents' => $estimate->discount_cents,
                'tax_rate' => $estimate->tax_rate,
                'due_date' => now()->addDays(14)->toDateString(),
            ]);

            $items = $estimate->items->map(fn ($item) => [
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price_cents' => $item->unit_price_cents,
            ])->all();

            $this->invoiceCalculator->sync($invoice, $items, $estimate->discount_cents / 100, $estimate->tax_rate);

            $job->forceFill(['invoice_id' => $invoice->id])->save();

            $this->auditLogger->log('job_created', $estimate->business_id, $job);
            $this->auditLogger->log('invoice_created', $estimate->business_id, $invoice);

            return [
                'job' => $job->refresh(),
                'invoice' => $invoice->refresh(),
                'created' => true,
            ];
        });
    }
}
