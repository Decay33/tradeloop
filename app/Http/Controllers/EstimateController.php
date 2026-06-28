<?php

namespace App\Http\Controllers;

use App\Http\Requests\EstimateRequest;
use App\Models\Estimate;
use App\Services\AuditLogger;
use App\Services\CurrentBusinessResolver;
use App\Services\EstimateCalculator;
use App\Services\EstimateConversionService;
use App\Services\NumberGenerator;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EstimateController extends Controller
{
    public function index(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $status = request('status');
        $search = request('search');

        return Inertia::render('Estimates/Index', [
            'filters' => ['status' => $status, 'search' => $search],
            'estimates' => $business->estimates()
                ->with('customer', 'serviceType')
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('estimate_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($query) => $query->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%"));
                }))
                ->latest()
                ->paginate(12)
                ->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $selectedCustomerId = request()->integer('customer_id') ?: null;

        if ($selectedCustomerId && ! $business->customers()->whereKey($selectedCustomerId)->exists()) {
            $selectedCustomerId = null;
        }

        return Inertia::render('Estimates/Create', [
            'customers' => $business->customers()->orderBy('first_name')->get(),
            'serviceTypes' => $business->serviceTypes()->where('is_active', true)->orderBy('name')->get(),
            'defaultTaxRate' => $business->default_tax_rate,
            'selectedCustomerId' => $selectedCustomerId,
        ]);
    }

    public function store(EstimateRequest $request, EstimateCalculator $calculator, NumberGenerator $numbers, AuditLogger $auditLogger)
    {
        $business = app(CurrentBusinessResolver::class)->resolve();

        $estimate = DB::transaction(function () use ($request, $business, $calculator, $numbers, $auditLogger) {
            $estimate = Estimate::create([
                'business_id' => $business->id,
                'customer_id' => $request->integer('customer_id'),
                'service_type_id' => $request->integer('service_type_id'),
                'estimate_number' => $numbers->estimateNumber($business),
                'status' => 'draft',
                'notes' => $request->input('notes'),
                'terms' => $request->input('terms'),
                'expires_at' => $request->input('expires_at'),
                'tax_rate' => $request->input('tax_rate', $business->default_tax_rate),
            ]);

            $calculator->sync($estimate, $request->input('items', []), $request->input('discount', 0), $request->input('tax_rate', $business->default_tax_rate));
            $auditLogger->log('estimate_created', $business, $estimate);

            return $estimate;
        });

        return redirect()->route('estimates.show', $estimate)->with('success', 'Estimate created.');
    }

    public function show(Estimate $estimate): Response
    {
        $this->authorize('view', $estimate);

        return Inertia::render('Estimates/Show', [
            'estimate' => $estimate->load('customer', 'serviceType', 'items', 'job', 'invoice'),
        ]);
    }

    public function edit(Estimate $estimate): Response
    {
        $this->authorize('update', $estimate);
        $business = app(CurrentBusinessResolver::class)->resolve();

        return Inertia::render('Estimates/Edit', [
            'estimate' => $estimate->load('items'),
            'customers' => $business->customers()->orderBy('first_name')->get(),
            'serviceTypes' => $business->serviceTypes()->where('is_active', true)->orderBy('name')->get(),
            'defaultTaxRate' => $business->default_tax_rate,
        ]);
    }

    public function update(EstimateRequest $request, Estimate $estimate, EstimateCalculator $calculator, AuditLogger $auditLogger)
    {
        $this->authorize('update', $estimate);

        $estimate->update([
            'customer_id' => $request->integer('customer_id'),
            'service_type_id' => $request->integer('service_type_id'),
            'notes' => $request->input('notes'),
            'terms' => $request->input('terms'),
            'expires_at' => $request->input('expires_at'),
            'tax_rate' => $request->input('tax_rate', 0),
        ]);

        $calculator->sync($estimate, $request->input('items', []), $request->input('discount', 0), $request->input('tax_rate', 0));
        $auditLogger->log('estimate_updated', $estimate->business_id, $estimate);

        return redirect()->route('estimates.show', $estimate)->with('success', 'Estimate updated.');
    }

    public function destroy(Estimate $estimate)
    {
        $this->authorize('delete', $estimate);
        $estimate->delete();

        return redirect()->route('estimates.index')->with('success', 'Estimate deleted.');
    }

    public function markSent(Estimate $estimate, AuditLogger $auditLogger)
    {
        $this->authorize('update', $estimate);
        $estimate->forceFill(['status' => 'sent', 'sent_at' => now()])->save();
        $auditLogger->log('estimate_sent', $estimate->business_id, $estimate);

        return back()->with('success', 'Estimate marked sent.');
    }

    public function accept(Estimate $estimate, AuditLogger $auditLogger)
    {
        $this->authorize('update', $estimate);
        $estimate->forceFill(['status' => 'accepted', 'accepted_at' => now()])->save();
        $auditLogger->log('estimate_accepted', $estimate->business_id, $estimate);

        return back()->with('success', 'Estimate accepted.');
    }

    public function decline(Estimate $estimate, AuditLogger $auditLogger)
    {
        $this->authorize('update', $estimate);
        $estimate->forceFill(['status' => 'declined', 'declined_at' => now()])->save();
        $auditLogger->log('estimate_declined', $estimate->business_id, $estimate);

        return back()->with('success', 'Estimate declined.');
    }

    public function convert(Estimate $estimate, EstimateConversionService $conversion)
    {
        $this->authorize('update', $estimate);
        $result = $conversion->convert($estimate);

        return redirect()->route('jobs.show', $result['job'])->with('success', $result['created'] ? 'Job and invoice created.' : 'This estimate was already converted.');
    }

    public function print(Estimate $estimate)
    {
        $this->authorize('view', $estimate);

        return view('print.estimate', ['estimate' => $estimate->load('business', 'customer', 'items')]);
    }
}
