<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceRequest;
use App\Http\Requests\PaymentRequest;
use App\Models\Invoice;
use App\Services\AuditLogger;
use App\Services\CurrentBusinessResolver;
use App\Services\InvoiceEmailService;
use App\Services\InvoiceCalculator;
use App\Services\InvoicePaymentService;
use App\Services\NumberGenerator;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $filter = request('filter', request('status'));

        return Inertia::render('Invoices/Index', [
            'invoices' => $business->invoices()
                ->with('customer', 'job.serviceType')
                ->tap(fn ($query) => $this->applySmartFilter($query, $filter))
                ->latest()
                ->paginate(12)
                ->withQueryString()
                ->through(fn (Invoice $invoice) => [
                    ...$invoice->toArray(),
                    'urgency' => $this->urgency($invoice),
                ]),
            'filters' => ['filter' => $filter],
        ]);
    }

    public function create(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $selectedCustomerId = request()->integer('customer_id') ?: null;
        $selectedJobId = request()->integer('job_id') ?: null;
        $selectedJob = $selectedJobId ? $business->jobs()->with('customer', 'serviceType')->whereKey($selectedJobId)->first() : null;

        if ($selectedJob) {
            $selectedCustomerId = $selectedJob->customer_id;
        } elseif ($selectedCustomerId && ! $business->customers()->whereKey($selectedCustomerId)->exists()) {
            $selectedCustomerId = null;
        }

        return Inertia::render('Invoices/Create', [
            'customers' => $business->customers()->orderBy('first_name')->get(),
            'jobs' => $business->jobs()->with('customer', 'serviceType')->whereNull('invoice_id')->latest()->get(),
            'defaultTaxRate' => $business->default_tax_rate,
            'selectedCustomerId' => $selectedCustomerId,
            'selectedJobId' => $selectedJob?->id,
            'defaultItems' => $selectedJob ? [[
                'description' => $selectedJob->title,
                'quantity' => 1,
                'unit_price' => ($selectedJob->quoted_total_cents ?? $selectedJob->serviceType?->default_price_cents ?? 0) / 100,
            ]] : null,
        ]);
    }

    public function store(InvoiceRequest $request, InvoiceCalculator $calculator, NumberGenerator $numbers, AuditLogger $auditLogger)
    {
        $business = app(CurrentBusinessResolver::class)->resolve();

        $invoice = DB::transaction(function () use ($request, $business, $calculator, $numbers, $auditLogger) {
            $job = $request->integer('job_id')
                ? $business->jobs()->whereKey($request->integer('job_id'))->firstOrFail()
                : null;

            $invoice = Invoice::create([
                'business_id' => $business->id,
                'customer_id' => $job?->customer_id ?: $request->integer('customer_id'),
                'job_id' => $job?->id,
                'estimate_id' => $job?->estimate_id,
                'invoice_number' => $numbers->invoiceNumber($business),
                'status' => 'draft',
                'due_date' => $request->input('due_date'),
                'tax_rate' => $request->input('tax_rate', $business->default_tax_rate),
            ]);

            $calculator->sync($invoice, $request->input('items', []), $request->input('discount', 0), $request->input('tax_rate', $business->default_tax_rate));
            $job?->forceFill(['invoice_id' => $invoice->id])->save();
            $auditLogger->log('invoice_created', $business, $invoice);

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created.');
    }

    public function show(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice->load('customer', 'items', 'payments.recordedBy', 'sendEvents.user', 'job.serviceType', 'estimate'),
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        $this->authorize('update', $invoice);
        $business = app(CurrentBusinessResolver::class)->resolve();

        return Inertia::render('Invoices/Edit', [
            'invoice' => $invoice->load('items'),
            'customers' => $business->customers()->orderBy('first_name')->get(),
            'jobs' => $business->jobs()->with('customer', 'serviceType')->where(function ($query) use ($invoice) {
                $query->whereNull('invoice_id')->orWhereKey($invoice->job_id);
            })->latest()->get(),
            'defaultTaxRate' => $business->default_tax_rate,
        ]);
    }

    public function update(InvoiceRequest $request, Invoice $invoice, InvoiceCalculator $calculator)
    {
        $this->authorize('update', $invoice);

        $invoice->update([
            'customer_id' => $request->integer('customer_id'),
            'job_id' => $request->input('job_id') ?: null,
            'due_date' => $request->input('due_date'),
            'tax_rate' => $request->input('tax_rate', 0),
        ]);

        $calculator->sync($invoice, $request->input('items', []), $request->input('discount', 0), $request->input('tax_rate', 0));

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted.');
    }

    public function markSent(Invoice $invoice, AuditLogger $auditLogger)
    {
        $this->authorize('update', $invoice);
        $invoice->forceFill(['status' => 'sent', 'sent_at' => now()])->save();
        $auditLogger->log('invoice_sent', $invoice->business_id, $invoice);

        return back()->with('success', 'Invoice marked sent.');
    }

    public function recordPayment(PaymentRequest $request, Invoice $invoice, InvoicePaymentService $payments)
    {
        $this->authorize('update', $invoice);
        $payments->record($invoice, $request->validated());

        return back()->with('success', 'Payment recorded.');
    }

    public function void(Invoice $invoice, AuditLogger $auditLogger)
    {
        $this->authorize('update', $invoice);
        $invoice->forceFill(['status' => 'void'])->save();
        $auditLogger->log('invoice_voided', $invoice->business_id, $invoice);

        return back()->with('success', 'Invoice voided.');
    }

    public function print(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return view('print.invoice', ['invoice' => $invoice->load('business', 'customer', 'items')]);
    }

    public function download(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return response()
            ->view('print.invoice', ['invoice' => $invoice->load('business', 'customer', 'items', 'payments')])
            ->header('Content-Disposition', 'attachment; filename="'.$invoice->invoice_number.'.html"');
    }

    public function sendEmail(Invoice $invoice, InvoiceEmailService $sender)
    {
        $this->authorize('update', $invoice);
        $event = $sender->send($invoice);

        return back()->with('success', $event->status === 'simulated_sent' ? 'Invoice email simulated.' : 'Invoice email skipped because the customer has no email address.');
    }

    private function applySmartFilter($query, ?string $filter): void
    {
        match ($filter) {
            'draft', 'sent', 'partially_paid', 'paid' => $query->where('status', $filter),
            'unpaid' => $query->where('balance_due_cents', '>', 0)->whereNotIn('status', ['paid', 'void']),
            'due_soon' => $query->where('balance_due_cents', '>', 0)->whereBetween('due_date', [now()->toDateString(), now()->addDays(7)->toDateString()]),
            'past_due' => $query->where('balance_due_cents', '>', 0)->whereDate('due_date', '<', now()->toDateString()),
            '30_overdue' => $query->where('balance_due_cents', '>', 0)->whereDate('due_date', '<=', now()->subDays(30)->toDateString()),
            '60_overdue' => $query->where('balance_due_cents', '>', 0)->whereDate('due_date', '<=', now()->subDays(60)->toDateString()),
            default => null,
        };
    }

    private function urgency(Invoice $invoice): ?string
    {
        if ($invoice->balance_due_cents <= 0 || ! $invoice->due_date || in_array($invoice->status, ['paid', 'void'], true)) {
            return null;
        }

        $days = $invoice->due_date->diffInDays(now(), false);

        return match (true) {
            $days >= 60 => 'critical',
            $days >= 30 => 'very_overdue',
            $days > 0 => 'past_due',
            $days >= -7 => 'due_soon',
            default => null,
        };
    }
}
