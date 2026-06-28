<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\FollowupMessage;
use App\Models\FollowupRule;
use App\Models\FollowupTemplate;
use App\Models\Invoice;
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

        [, $otherBusiness] = $this->account();
        $otherCustomer = Customer::factory()->for($otherBusiness)->create();
        $this->actingAs($user)->post('/estimates', [
            'customer_id' => $otherCustomer->id,
            'service_type_id' => $service->id,
            'items' => [['description' => 'Bad', 'quantity' => 1, 'unit_price' => 10]],
        ])->assertSessionHasErrors('customer_id');
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

        $this->actingAs($user)->post("/invoices/{$invoice->id}/void")->assertRedirect();
        $this->actingAs($user)->post("/invoices/{$invoice->id}/payments", [
            'amount' => 1,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ])->assertSessionHasErrors('invoice');
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
