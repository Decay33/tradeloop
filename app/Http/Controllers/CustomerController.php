<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Services\AuditLogger;
use App\Services\CurrentBusinessResolver;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $search = request('search');

        return Inertia::render('Customers/Index', [
            'filters' => ['search' => $search],
            'customers' => $business->customers()
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(12)
                ->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Customers/Create');
    }

    public function store(CustomerRequest $request, AuditLogger $auditLogger)
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $customer = $business->customers()->create([
            ...$request->validated(),
            'sms_consent' => $request->boolean('sms_consent'),
            'email_consent' => $request->boolean('email_consent'),
        ]);

        $auditLogger->log('customer_created', $business, $customer);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer created.');
    }

    public function show(Customer $customer): Response
    {
        $this->authorize('view', $customer);

        return Inertia::render('Customers/Show', [
            'customer' => $customer->load([
                'estimates.serviceType',
                'invoices.payments',
                'jobs.serviceType',
                'followupMessages',
            ]),
        ]);
    }

    public function edit(Customer $customer): Response
    {
        $this->authorize('update', $customer);

        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(CustomerRequest $request, Customer $customer, AuditLogger $auditLogger)
    {
        $this->authorize('update', $customer);
        $customer->update([
            ...$request->validated(),
            'sms_consent' => $request->boolean('sms_consent'),
            'email_consent' => $request->boolean('email_consent'),
        ]);

        $auditLogger->log('customer_updated', $customer->business_id, $customer);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer, AuditLogger $auditLogger)
    {
        $this->authorize('delete', $customer);
        $customer->delete();
        $auditLogger->log('customer_deleted', $customer->business_id, $customer);

        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }
}
