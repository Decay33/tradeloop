<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\FollowupMessage;
use App\Models\FollowupRule;
use App\Models\FollowupTemplate;
use App\Models\Invoice;
use App\Models\InvoiceSendEvent;
use App\Models\Job;
use App\Models\Payment;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TradeLoopWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_estimate_calculates_and_converts_without_duplicates(): void
    {
        [$user, $business, $customer, $service] = $this->setupBusiness();

        $this->actingAs($user)->post('/estimates', [
            'customer_id' => $customer->id,
            'service_type_id' => $service->id,
            'discount' => 50,
            'tax_rate' => 10,
            'items' => [
                ['description' => 'Labor', 'quantity' => 2, 'unit_price' => 100],
                ['description' => 'Materials', 'quantity' => 1, 'unit_price' => 50],
            ],
        ])->assertRedirect();

        $estimate = Estimate::with('items')->first();
        $this->assertSame(25000, $estimate->subtotal_cents);
        $this->assertSame(5000, $estimate->discount_cents);
        $this->assertSame(2000, $estimate->tax_cents);
        $this->assertSame(22000, $estimate->total_cents);

        $this->actingAs($user)->post("/estimates/{$estimate->id}/mark-sent")->assertRedirect();
        $this->actingAs($user)->post("/estimates/{$estimate->id}/accept")->assertRedirect();
        $this->actingAs($user)->post("/estimates/{$estimate->id}/create-job-and-invoice")->assertRedirect();
        $this->actingAs($user)->post("/estimates/{$estimate->id}/create-job-and-invoice")->assertRedirect();

        $this->assertSame(1, Job::where('estimate_id', $estimate->id)->count());
        $this->assertSame(1, Invoice::where('estimate_id', $estimate->id)->count());
        $this->assertSame(2, Invoice::first()->items()->count());
        $this->assertSame($estimate->total_cents, Job::where('estimate_id', $estimate->id)->first()->quoted_total_cents);

        [, $otherBusiness] = $this->account();
        $otherCustomer = Customer::factory()->for($otherBusiness)->create();
        $this->actingAs($user)->post('/estimates', [
            'customer_id' => $otherCustomer->id,
            'service_type_id' => $service->id,
            'items' => [['description' => 'Bad', 'quantity' => 1, 'unit_price' => 10]],
        ])->assertSessionHasErrors('customer_id');
    }

    public function test_direct_job_creation_and_reviewed_job_only_estimate_conversion(): void
    {
        [$user, $business, $customer, $service] = $this->setupBusiness();

        $this->actingAs($user)->post('/jobs', [
            'customer_id' => $customer->id,
            'service_type_id' => $service->id,
            'assigned_user_id' => $user->id,
            'title' => 'Direct repair visit',
            'scheduled_date' => now()->addDay()->toDateString(),
            'quoted_price' => 425,
            'job_address' => '123 Direct Lane',
        ])->assertRedirect();

        $directJob = Job::where('title', 'Direct repair visit')->first();
        $this->assertNull($directJob->estimate_id);
        $this->assertSame(42500, $directJob->quoted_total_cents);
        $this->assertSame($user->id, $directJob->assigned_user_id);

        $estimate = Estimate::factory()->for($business)->create([
            'customer_id' => $customer->id,
            'service_type_id' => $service->id,
            'status' => 'accepted',
            'total_cents' => 90000,
        ]);

        $this->actingAs($user)->get("/estimates/{$estimate->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Estimates/Show')
                ->has('teamMembers')
            );

        $this->actingAs($user)->post("/estimates/{$estimate->id}/create-job-and-invoice", [
            'job_title' => 'Reviewed job only',
            'create_invoice' => false,
        ])->assertRedirect();

        $this->assertDatabaseHas('jobs', ['estimate_id' => $estimate->id, 'title' => 'Reviewed job only', 'quoted_total_cents' => 90000]);
        $this->assertDatabaseMissing('invoices', ['estimate_id' => $estimate->id]);
    }

    public function test_estimate_create_can_preselect_customer_from_detail_action(): void
    {
        [$user, , $customer] = $this->setupBusiness();

        $this->actingAs($user)
            ->get("/estimates/create?customer_id={$customer->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Estimates/Create')
                ->where('selectedCustomerId', $customer->id)
                ->has('customers')
            );
    }

    public function test_invoice_payments_update_balance_and_block_invalid_payments(): void
    {
        [$user, $business, $customer] = $this->setupBusiness();
        $invoice = Invoice::factory()->for($business)->create(['customer_id' => $customer->id, 'total_cents' => 10000, 'balance_due_cents' => 10000]);

        $this->actingAs($user)->post("/invoices/{$invoice->id}/payments", [
            'amount' => 40,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'amount_paid_cents' => 4000, 'balance_due_cents' => 6000, 'status' => 'partially_paid']);

        $this->actingAs($user)->post("/invoices/{$invoice->id}/payments", [
            'amount' => 70,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ])->assertSessionHasErrors('amount');

        $this->actingAs($user)->post("/invoices/{$invoice->id}/payments", [
            'amount' => 60,
            'payment_method' => 'check',
            'payment_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'balance_due_cents' => 0, 'status' => 'paid']);
        $this->assertSame(2, $invoice->payments()->count());

        $this->actingAs($user)->post("/invoices/{$invoice->id}/void")->assertRedirect();
        $this->actingAs($user)->post("/invoices/{$invoice->id}/payments", [
            'amount' => 1,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ])->assertSessionHasErrors('invoice');
    }

    public function test_invoice_email_send_is_simulated_and_manual_followups_can_link_records(): void
    {
        [$user, $business, $customer, $service] = $this->setupBusiness();
        $invoice = Invoice::factory()->for($business)->create(['customer_id' => $customer->id, 'status' => 'draft']);
        $estimate = Estimate::factory()->for($business)->create(['customer_id' => $customer->id, 'service_type_id' => $service->id]);

        $this->actingAs($user)->post("/invoices/{$invoice->id}/send-email")->assertRedirect();
        $this->assertDatabaseHas('invoice_send_events', [
            'business_id' => $business->id,
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'status' => 'simulated_sent',
        ]);
        $this->assertNotNull($invoice->refresh()->sent_at);
        $this->assertSame('sent', $invoice->status);

        $this->actingAs($user)->post('/follow-ups', [
            'customer_id' => $customer->id,
            'channel' => 'sms',
            'purpose' => 'sales_follow_up',
            'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'body' => 'Checking in on your project.',
        ])->assertRedirect();

        $this->assertDatabaseHas('followup_messages', [
            'customer_id' => $customer->id,
            'job_id' => null,
            'is_manual' => true,
            'purpose' => 'sales_follow_up',
            'status' => 'scheduled',
        ]);

        $this->actingAs($user)->post('/follow-ups', [
            'customer_id' => $customer->id,
            'estimate_id' => $estimate->id,
            'channel' => 'email',
            'purpose' => 'sales_follow_up',
            'scheduled_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'subject' => 'Estimate follow-up',
            'body' => 'Following up on the estimate.',
        ])->assertRedirect();

        $this->assertDatabaseHas('followup_messages', [
            'customer_id' => $customer->id,
            'estimate_id' => $estimate->id,
            'is_manual' => true,
        ]);
    }

    public function test_job_completion_schedules_followups_once_and_respects_consent(): void
    {
        [$user, $business, $customer, $service] = $this->setupBusiness();
        $template = FollowupTemplate::factory()->for($business)->create(['channel' => 'sms', 'purpose' => 'thank_you']);
        FollowupRule::factory()->for($business)->create(['service_type_id' => $service->id, 'template_id' => $template->id, 'channel' => 'sms', 'purpose' => 'thank_you', 'delay_amount' => 0]);
        $job = Job::factory()->for($business)->create(['customer_id' => $customer->id, 'service_type_id' => $service->id]);

        $this->actingAs($user)->post("/jobs/{$job->id}/complete")->assertRedirect();
        $this->assertSame(1, FollowupMessage::where('job_id', $job->id)->count());
        $this->actingAs($user)->post("/jobs/{$job->id}/complete")->assertRedirect();
        $this->assertSame(1, FollowupMessage::where('job_id', $job->id)->count());

        $message = FollowupMessage::first();
        $this->assertSame('scheduled', $message->status);

        $this->artisan('followups:process-due')->assertExitCode(0);
        $this->assertDatabaseHas('followup_messages', ['id' => $message->id, 'status' => 'simulated_sent']);
        $this->assertDatabaseHas('message_events', ['followup_message_id' => $message->id, 'event_type' => 'simulated_sent']);

        $optedOut = Customer::factory()->for($business)->create(['sms_consent' => false]);
        $job = Job::factory()->for($business)->create(['customer_id' => $optedOut->id, 'service_type_id' => $service->id]);
        $this->actingAs($user)->post("/jobs/{$job->id}/complete")->assertRedirect();
        $this->assertDatabaseHas('followup_messages', ['job_id' => $job->id, 'status' => 'skipped', 'skip_reason' => 'SMS consent not granted']);
    }

    public function test_job_completion_review_supports_edited_dates_and_no_followups(): void
    {
        [$user, $business, $customer, $service] = $this->setupBusiness();
        $template = FollowupTemplate::factory()->for($business)->create(['channel' => 'sms', 'purpose' => 'thank_you']);
        FollowupRule::factory()->for($business)->create(['service_type_id' => $service->id, 'template_id' => $template->id, 'channel' => 'sms', 'purpose' => 'thank_you', 'delay_amount' => 1]);
        $job = Job::factory()->for($business)->create(['customer_id' => $customer->id, 'service_type_id' => $service->id]);
        $editedDate = now()->addDays(5)->format('Y-m-d\TH:i');

        $this->actingAs($user)->post("/jobs/{$job->id}/complete", [
            'completed_at' => now()->format('Y-m-d\TH:i'),
            'schedule_followups' => true,
            'followups' => [[
                'template_id' => $template->id,
                'channel' => 'sms',
                'purpose' => 'thank_you',
                'scheduled_at' => $editedDate,
                'recipient' => $customer->phone,
                'body' => 'Edited follow-up body.',
            ]],
        ])->assertRedirect();

        $this->assertDatabaseHas('followup_messages', [
            'job_id' => $job->id,
            'body' => 'Edited follow-up body.',
        ]);
        $this->assertSame($editedDate, FollowupMessage::where('job_id', $job->id)->first()->scheduled_at->format('Y-m-d\TH:i'));

        $jobWithoutFollowups = Job::factory()->for($business)->create(['customer_id' => $customer->id, 'service_type_id' => $service->id]);
        $this->actingAs($user)->post("/jobs/{$jobWithoutFollowups->id}/complete", [
            'schedule_followups' => false,
        ])->assertRedirect();

        $this->assertSame(0, FollowupMessage::where('job_id', $jobWithoutFollowups->id)->count());
        $this->assertNotNull($jobWithoutFollowups->refresh()->followups_scheduled_at);
    }

    public function test_followup_send_now_cancel_reschedule_and_reports(): void
    {
        [$user, $business, $customer, $service] = $this->setupBusiness();
        $invoice = Invoice::factory()->for($business)->create(['customer_id' => $customer->id, 'total_cents' => 20000, 'balance_due_cents' => 0, 'status' => 'paid']);
        Payment::factory()->for($business)->create(['invoice_id' => $invoice->id, 'amount_cents' => 20000, 'payment_date' => now()->toDateString()]);
        $job = Job::factory()->for($business)->create(['customer_id' => $customer->id, 'service_type_id' => $service->id, 'invoice_id' => $invoice->id, 'status' => 'completed', 'completed_at' => now()]);
        $template = FollowupTemplate::factory()->for($business)->create(['purpose' => 'review_request', 'channel' => 'email']);
        $message = FollowupMessage::factory()->for($business)->create(['customer_id' => $customer->id, 'job_id' => $job->id, 'template_id' => $template->id, 'channel' => 'email', 'purpose' => 'review_request', 'scheduled_at' => now()]);

        $this->actingAs($user)->post("/follow-ups/{$message->id}/send-now")->assertRedirect();
        $this->assertDatabaseHas('followup_messages', ['id' => $message->id, 'status' => 'simulated_sent']);

        $message = FollowupMessage::factory()->for($business)->create(['customer_id' => $customer->id, 'job_id' => $job->id, 'template_id' => $template->id]);
        $this->actingAs($user)->post("/follow-ups/{$message->id}/reschedule", ['scheduled_at' => now()->addWeek()->format('Y-m-d\TH:i')])->assertRedirect();
        $this->actingAs($user)->post("/follow-ups/{$message->id}/cancel")->assertRedirect();
        $this->assertDatabaseHas('followup_messages', ['id' => $message->id, 'status' => 'canceled']);

        $summary = app(ReportService::class)->summary($business);
        $this->assertSame(20000, $summary['revenue_this_month']);
        $this->assertGreaterThanOrEqual(1, $summary['review_requests_sent']);
    }

    public function test_smart_filters_and_report_date_ranges(): void
    {
        [$user, $business, $customer, $service] = $this->setupBusiness();
        Estimate::factory()->for($business)->create(['customer_id' => $customer->id, 'service_type_id' => $service->id, 'status' => 'sent', 'sent_at' => now()->subDays(10)]);
        $accepted = Estimate::factory()->for($business)->create(['customer_id' => $customer->id, 'service_type_id' => $service->id, 'status' => 'accepted']);
        Job::factory()->for($business)->create(['customer_id' => $customer->id, 'service_type_id' => $service->id, 'estimate_id' => $accepted->id]);
        Invoice::factory()->for($business)->create(['customer_id' => $customer->id, 'balance_due_cents' => 50000, 'due_date' => now()->subDays(35)]);
        $paidInvoice = Invoice::factory()->for($business)->create(['customer_id' => $customer->id, 'total_cents' => 30000, 'balance_due_cents' => 0, 'status' => 'paid']);
        Payment::factory()->create(['business_id' => $business->id, 'invoice_id' => $paidInvoice->id, 'amount_cents' => 30000, 'payment_date' => now()->subDay()->toDateString()]);

        $this->actingAs($user)->get('/estimates?filter=needs_follow_up')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('estimates.data', 1));

        $this->actingAs($user)->get('/invoices?filter=30_overdue')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('invoices.data', 1));

        $this->actingAs($user)->get('/reports?range=yesterday')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('range', 'yesterday')
                ->where('report.collections.payments_collected_cents', 30000)
                ->has('report.daily_snapshot')
            );
    }

    public function test_team_member_creation_and_field_staff_permissions(): void
    {
        [$owner, $business, $customer, $service] = $this->setupBusiness();

        $this->actingAs($owner)->post('/settings/team', [
            'name' => 'Field Worker',
            'email' => 'field@example.test',
            'phone' => '(555) 222-1000',
            'role' => 'field_staff',
            'temporary_password' => 'password123',
            'is_active' => true,
        ])->assertRedirect();

        $field = User::where('email', 'field@example.test')->first();
        $job = Job::factory()->for($business)->create(['customer_id' => $customer->id, 'service_type_id' => $service->id, 'assigned_user_id' => $field->id]);

        $this->actingAs($field)->get('/jobs')->assertOk();
        $this->actingAs($field)->post("/jobs/{$job->id}/complete", ['schedule_followups' => false])->assertRedirect();
        $this->actingAs($field)->get('/reports')->assertForbidden();
        $this->actingAs($field)->get('/settings')->assertForbidden();
    }

    private function setupBusiness(): array
    {
        [$user, $business] = $this->account();
        $customer = Customer::factory()->for($business)->create();
        $service = ServiceType::factory()->for($business)->create(['default_price_cents' => 100000]);

        return [$user, $business, $customer, $service];
    }

    private function account(string $role = 'owner'): array
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $business->users()->attach($user->id, ['role' => $role]);

        return [$user, $business];
    }
}
