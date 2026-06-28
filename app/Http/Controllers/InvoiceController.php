<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceRequest;
use App\Http\Requests\PaymentRequest;
use App\Models\Invoice;
use App\Services\AuditLogger;
use App\Services\CurrentBusinessResolver;
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

        return Inertia::render('Invoices/Index', [
            'invoices' => $business->invoices()
                ->with('customer', 'job.serviceType')
                ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
                ->latest()
                ->paginate(12)
                ->withQueryString(),
            'filters' => ['status' => request('status')],
        ]);
    }

    public function create(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();

        return Inertia::render('Invoices/Create', [
            'customers' => $business->customers()->orderBy('first_name')->get(),
            'defaultTaxRate' => $business->default_tax_rate,
        ]);
    }

    public function store(InvoiceRequest $request, InvoiceCalculator $calculator, NumberGenerator $numbers, AuditLogger $auditLogger)
    {
        $business = app(CurrentBusinessResolver::class)->resolve();

        $invoice = DB::transaction(function () use ($request, $business, $calculator, $numbers, $auditLogger) {
            $invoice = Invoice::create([
                'business_id' => $business->id,
                'customer_id' => $request->integer('customer_id'),
                'invoice_number' => $numbers->invoiceNumber($business),
                'status' => 'draft',
                'due_date' => $request->input('due_date'),
                'tax_rate' => $request->input('tax_rate', $business->default_tax_rate),
            ]);

            $calculator->sync($invoice, $request->input('items', []), $request->input('discount', 0), $request->input('tax_rate', $business->default_tax_rate));
            $auditLogger->log('invoice_created', $business, $invoice);

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created.');
    }

    public function show(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice->load('customer', 'items', 'payments', 'job.serviceType', 'estimate'),
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        $this->authorize('update', $invoice);
        $business = app(CurrentBusinessResolver::class)->resolve();

        return Inertia::render('Invoices/Edit', [
            'invoice' => $invoice->load('items'),
            'customers' => $business->customers()->orderBy('first_name')->get(),
            'defaultTaxRate' => $business->default_tax_rate,
        ]);
    }

    public function update(InvoiceRequest $request, Invoice $invoice, InvoiceCalculator $calculator)
    {
        $this->authorize('update', $invoice);

        $invoice->update([
            'customer_id' => $request->integer('customer_id'),
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
}
