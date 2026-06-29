<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\FollowupMessage;
use App\Models\FollowupTemplate;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\Payment;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessIsolationAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_business_direct_ids_are_blocked(): void
    {
        [$userA] = $this->account('owner');
        [, $businessB] = $this->account('owner');
        [$customer, $service, $estimate, $invoice, $job, $message] = $this->recordsFor($businessB);

        $this->actingAs($userA);

        $this->get("/customers/{$customer->id}")->assertForbidden();
        $this->get("/estimates/{$estimate->id}")->assertForbidden();
        $this->get("/invoices/{$invoice->id}")->assertForbidden();
        $this->get("/jobs/{$job->id}")->assertForbidden();
        $this->get("/follow-ups/{$message->id}")->assertForbidden();
        $this->put("/settings/service-types/{$service->id}", [
            'name' => 'Wrong',
            'default_price' => 10,
            'is_active' => true,
        ])->assertForbidden();
    }

    public function test_reports_are_business_scoped(): void
    {
        [$userA, $businessA] = $this->account('owner');
        [, $businessB] = $this->account('owner');
        $invoiceA = $this->invoiceFor($businessA, 10000);
        $invoiceB = $this->invoiceFor($businessB, 90000);

        Payment::factory()->create(['business_id' => $businessA->id, 'invoice_id' => $invoiceA->id, 'amount_cents' => 10000, 'payment_date' => now()->toDateString()]);
        Payment::factory()->create(['business_id' => $businessB->id, 'invoice_id' => $invoiceB->id, 'amount_cents' => 90000, 'payment_date' => now()->toDateString()]);

        $this->actingAs($userA)->get('/reports')->assertOk();

        $summary = app(ReportService::class)->summary($businessA);
        $this->assertSame(10000, $summary['revenue_this_month']);
    }

    public function test_manual_followups_reject_cross_business_records(): void
    {
        [$userA] = $this->account('owner');
        [, $businessB] = $this->account('owner');
        $customerB = Customer::factory()->for($businessB)->create();

        $this->actingAs($userA)->post('/follow-ups', [
            'customer_id' => $customerB->id,
            'channel' => 'sms',
            'purpose' => 'sales_follow_up',
            'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'body' => 'Should not save.',
        ])->assertSessionHasErrors('customer_id');
    }

    public function test_custom_permissions_are_enforced_server_side(): void
    {
        [$owner, $business] = $this->account('owner');

        $this->actingAs($owner)->post('/settings/team', [
            'name' => 'Invoice Only',
            'email' => 'invoice-only@example.test',
            'role' => 'custom',
            'temporary_password' => 'password123',
            'is_active' => true,
            'permissions' => ['manage_invoices'],
        ])->assertRedirect();

        $custom = User::where('email', 'invoice-only@example.test')->first();

        $this->actingAs($custom)->get('/invoices')->assertOk();
        $this->actingAs($custom)->get('/jobs')->assertForbidden();
        $this->actingAs($custom)->get('/reports')->assertForbidden();
    }

    public function test_role_permissions_match_mvp_rules(): void
    {
        [$owner] = $this->account('owner');
        [$manager] = $this->account('manager');
        [$staff] = $this->account('staff');

        $this->actingAs($owner)->get('/settings')->assertOk();
        $this->actingAs($manager)->get('/reports')->assertOk();
        $this->actingAs($staff)->get('/reports')->assertForbidden();
        $this->actingAs($staff)->get('/invoices')->assertForbidden();
        $this->actingAs($staff)->get('/customers')->assertOk();
        $this->actingAs($staff)->get('/jobs')->assertOk();
    }

    private function account(string $role): array
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $business->users()->attach($user->id, ['role' => $role]);

        return [$user, $business];
    }

    private function recordsFor(Business $business): array
    {
        $customer = Customer::factory()->for($business)->create();
        $service = ServiceType::factory()->for($business)->create();
        $estimate = Estimate::factory()->for($business)->create(['customer_id' => $customer->id, 'service_type_id' => $service->id]);
        $invoice = Invoice::factory()->for($business)->create(['customer_id' => $customer->id]);
        $job = Job::factory()->for($business)->create(['customer_id' => $customer->id, 'service_type_id' => $service->id, 'invoice_id' => $invoice->id]);
        $template = FollowupTemplate::factory()->for($business)->create();
        $message = FollowupMessage::factory()->for($business)->create(['customer_id' => $customer->id, 'job_id' => $job->id, 'template_id' => $template->id]);

        return [$customer, $service, $estimate, $invoice, $job, $message];
    }

    private function invoiceFor(Business $business, int $total): Invoice
    {
        $customer = Customer::factory()->for($business)->create();

        return Invoice::factory()->for($business)->create([
            'customer_id' => $customer->id,
            'total_cents' => $total,
            'balance_due_cents' => 0,
            'status' => 'paid',
        ]);
    }
}
