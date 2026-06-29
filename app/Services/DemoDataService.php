<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\FollowupMessage;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataService
{
    public function __construct(
        private readonly DefaultBusinessSeederService $defaults,
        private readonly EstimateCalculator $estimateCalculator,
        private readonly InvoiceCalculator $invoiceCalculator,
        private readonly NumberGenerator $numbers,
    ) {
    }

    public function reset(): array
    {
        return DB::transaction(function () {
            $this->deleteExistingDemo();

            $owner = User::create([
                'name' => 'Sam Smith',
                'email' => 'demo@tradeloop.test',
                'phone' => '(555) 201-8833',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            $staff = User::create([
                'name' => 'Taylor Crew',
                'email' => 'staff@tradeloop.test',
                'phone' => '(555) 201-8844',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            $business = Business::create([
                'name' => 'Smith Home Services',
                'trade_type' => 'General Handyman',
                'phone' => '(555) 201-8833',
                'email' => 'hello@smithhomeservices.test',
                'website' => 'https://smithhomeservices.test',
                'city' => 'Columbus',
                'state' => 'OH',
                'timezone' => 'America/New_York',
                'google_review_url' => 'https://example.com/google-review',
                'facebook_review_url' => 'https://example.com/facebook-review',
                'default_tax_rate' => 7.5,
                'default_invoice_terms' => 'Payment due within 14 days.',
            ]);

            $business->users()->attach($owner->id, ['role' => 'owner', 'is_active' => true]);
            $business->users()->attach($staff->id, ['role' => 'field_staff', 'is_active' => true]);
            AppSetting::create(['business_id' => $business->id, 'key' => 'is_demo', 'value' => true]);

            $this->defaults->seed($business);
            $customers = $this->seedCustomers($business);
            $this->seedEstimatesInvoicesJobs($business, $customers);
            $this->seedFollowups($business);

            return [
                'businesses' => 1,
                'users' => 2,
                'customers' => $business->customers()->count(),
                'estimates' => $business->estimates()->count(),
                'invoices' => $business->invoices()->count(),
                'jobs' => $business->jobs()->count(),
                'followups' => $business->followupMessages()->count(),
            ];
        });
    }

    private function deleteExistingDemo(): void
    {
        $demoBusinessIds = Business::query()
            ->where('email', 'hello@smithhomeservices.test')
            ->orWhereHas('users', fn ($query) => $query->whereIn('email', ['demo@tradeloop.test', 'staff@tradeloop.test']))
            ->pluck('id');

        Business::query()->whereIn('id', $demoBusinessIds)->delete();
        User::query()->whereIn('email', ['demo@tradeloop.test', 'staff@tradeloop.test'])->delete();
    }

    private function seedCustomers(Business $business): array
    {
        $names = [
            ['Avery', 'Johnson', null, 'avery@example.test', '(555) 210-1001', true, true],
            ['Morgan', 'Lee', null, 'morgan@example.test', '(555) 210-1002', true, true],
            ['Casey', 'Patel', null, 'casey@example.test', '(555) 210-1003', true, true],
            ['Jordan', 'Miller', null, 'jordan@example.test', '(555) 210-1004', true, true],
            ['Riley', 'Garcia', null, 'riley@example.test', '(555) 210-1005', true, false],
            ['Jamie', 'Brown', null, null, '(555) 210-1006', true, false],
            ['Robin', 'Wilson', null, 'robin@example.test', null, false, true],
            ['Quinn', 'Davis', null, 'quinn@example.test', '(555) 210-1008', false, true],
            ['Drew', 'Martinez', null, 'drew@example.test', '(555) 210-1009', true, true],
            ['Skyler', 'Anderson', null, 'skyler@example.test', '(555) 210-1010', true, true],
        ];

        while (count($names) < 25) {
            $i = count($names) + 1;
            $names[] = ["Homeowner {$i}", 'Demo', null, "homeowner{$i}@example.test", "(555) 210-10{$i}", $i % 4 !== 0, $i % 5 !== 0];
        }

        return collect($names)->map(function (array $row, int $index) use ($business) {
            return Customer::create([
                'business_id' => $business->id,
                'first_name' => $row[0],
                'last_name' => $row[1],
                'company_name' => $row[2],
                'email' => $row[3],
                'phone' => $row[4],
                'address_line_1' => (100 + $index).' Maple Street',
                'city' => 'Columbus',
                'state' => 'OH',
                'zip' => '43215',
                'sms_consent' => $row[5],
                'email_consent' => $row[6],
                'sms_opted_out_at' => $index === 7 ? now()->subDays(20) : null,
                'notes' => $index % 3 === 0 ? 'Prefers weekday appointments.' : null,
            ]);
        })->all();
    }

    private function seedEstimatesInvoicesJobs(Business $business, array $customers): void
    {
        $services = $business->serviceTypes()->whereIn('name', ['General Handyman', 'Deck Repair', 'Gutters', 'Pressure Washing', 'Painting', 'Driveway Work'])->get()->values();

        for ($i = 0; $i < 12; $i++) {
            $customer = $customers[$i];
            $service = $services[$i % $services->count()];
            $status = ['accepted', 'sent', 'declined', 'accepted', 'draft', 'accepted', 'accepted', 'sent', 'accepted', 'declined', 'accepted', 'accepted'][$i];

            $estimate = Estimate::create([
                'business_id' => $business->id,
                'customer_id' => $customer->id,
                'service_type_id' => $service->id,
                'estimate_number' => $this->numbers->estimateNumber($business),
                'status' => $status,
                'expires_at' => now()->addDays(15 + $i)->toDateString(),
                'sent_at' => in_array($status, ['sent', 'accepted', 'declined'], true) ? now()->subDays(20 - $i) : null,
                'accepted_at' => $status === 'accepted' ? now()->subDays(12 - min($i, 10)) : null,
                'declined_at' => $status === 'declined' ? now()->subDays(7) : null,
                'tax_rate' => $business->default_tax_rate,
            ]);

            $this->estimateCalculator->sync($estimate, [
                ['description' => $service->name.' labor', 'quantity' => 1, 'unit_price_cents' => $service->default_price_cents],
                ['description' => 'Materials and supplies', 'quantity' => 1, 'unit_price_cents' => 12500 + ($i * 1000)],
            ], $i % 4 === 0 ? 50 : 0, $business->default_tax_rate);
        }

        $accepted = $business->estimates()->where('status', 'accepted')->with(['customer', 'serviceType', 'items'])->get();
        $team = $business->users()->get();
        $fieldStaff = $team->first(fn (User $user) => $user->roleForBusiness($business) === 'field_staff');

        foreach ($accepted->take(max(0, $accepted->count() - 1)) as $index => $estimate) {
            $job = Job::create([
                'business_id' => $business->id,
                'customer_id' => $estimate->customer_id,
                'estimate_id' => $estimate->id,
                'service_type_id' => $estimate->service_type_id,
                'quoted_total_cents' => $estimate->total_cents,
                'assigned_user_id' => $index % 2 === 0 ? $fieldStaff?->id : null,
                'title' => $estimate->serviceType->name.' for '.$estimate->customer->display_name,
                'status' => $index < 7 ? 'completed' : 'scheduled',
                'scheduled_date' => now()->subDays(18 - $index)->toDateString(),
                'started_by_user_id' => $index < 7 ? $fieldStaff?->id : null,
                'completed_by_user_id' => $index < 7 ? $fieldStaff?->id : null,
                'completed_at' => $index < 7 ? now()->subDays(10 - $index) : null,
                'followups_scheduled_at' => $index < 7 ? now()->subDays(9 - $index) : null,
                'job_address' => $estimate->customer->full_address,
            ]);

            $invoice = Invoice::create([
                'business_id' => $business->id,
                'customer_id' => $estimate->customer_id,
                'estimate_id' => $estimate->id,
                'job_id' => $job->id,
                'invoice_number' => $this->numbers->invoiceNumber($business),
                'status' => 'sent',
                'due_date' => now()->addDays(14 - ($index * 8))->toDateString(),
                'tax_rate' => $business->default_tax_rate,
            ]);

            $this->invoiceCalculator->sync($invoice, $estimate->items->map(fn ($item) => [
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price_cents' => $item->unit_price_cents,
            ])->all(), $estimate->discount_cents / 100, $estimate->tax_rate);

            $job->forceFill(['invoice_id' => $invoice->id])->save();

            if ($index < 4) {
                Payment::create([
                    'business_id' => $business->id,
                    'invoice_id' => $invoice->id,
                    'recorded_by_user_id' => $team->first()?->id,
                    'amount_cents' => $invoice->total_cents,
                    'payment_method' => ['cash', 'check', 'credit_card', 'bank_transfer'][$index % 4],
                    'payment_date' => now()->subDays(7 - $index)->toDateString(),
                ]);
                $this->invoiceCalculator->recalculatePayments($invoice);
            } elseif ($index < 6) {
                Payment::create([
                    'business_id' => $business->id,
                    'invoice_id' => $invoice->id,
                    'recorded_by_user_id' => $team->first()?->id,
                    'amount_cents' => (int) round($invoice->total_cents / 2),
                    'payment_method' => 'check',
                    'payment_date' => now()->subDays(4)->toDateString(),
                ]);
                if ($index === 4) {
                    Payment::create([
                        'business_id' => $business->id,
                        'invoice_id' => $invoice->id,
                        'recorded_by_user_id' => $team->first()?->id,
                        'amount_cents' => (int) round($invoice->total_cents / 4),
                        'payment_method' => 'cash',
                        'payment_date' => now()->subDays(2)->toDateString(),
                        'notes' => 'Second split payment in demo data.',
                    ]);
                }
                $this->invoiceCalculator->recalculatePayments($invoice);
            }

            if ($index === 1) {
                $invoice->sendEvents()->create([
                    'business_id' => $business->id,
                    'user_id' => $team->first()?->id,
                    'recipient' => $invoice->customer->email,
                    'subject' => 'Invoice '.$invoice->invoice_number.' from '.$business->name,
                    'body' => 'Simulated invoice email created by demo seed data.',
                    'status' => 'simulated_sent',
                    'sent_at' => now()->subDays(3),
                ]);
                $invoice->forceFill(['sent_at' => now()->subDays(3)])->save();
            }
        }

        $directService = $services->first();
        $directAssigned = Job::create([
            'business_id' => $business->id,
            'customer_id' => $customers[15]->id,
            'service_type_id' => $directService->id,
            'assigned_user_id' => $fieldStaff?->id,
            'quoted_total_cents' => 42500,
            'title' => 'Direct handyman repair',
            'status' => 'scheduled',
            'scheduled_date' => now()->addDays(3)->toDateString(),
            'job_address' => $customers[15]->full_address,
            'notes' => 'Created directly without an estimate.',
        ]);

        Job::create([
            'business_id' => $business->id,
            'customer_id' => $customers[16]->id,
            'service_type_id' => $directService->id,
            'quoted_total_cents' => 0,
            'title' => 'Unassigned punch list visit',
            'status' => 'scheduled',
            'scheduled_date' => now()->addDays(6)->toDateString(),
            'job_address' => $customers[16]->full_address,
            'notes' => 'Unassigned direct job for demo filtering.',
        ]);

        for ($i = 0; $i < 3; $i++) {
            $customer = $customers[12 + $i];
            $invoice = Invoice::create([
                'business_id' => $business->id,
                'customer_id' => $customer->id,
                'invoice_number' => $this->numbers->invoiceNumber($business),
                'status' => 'sent',
                'due_date' => now()->subDays(5 + $i * 20)->toDateString(),
                'tax_rate' => $business->default_tax_rate,
            ]);

            $this->invoiceCalculator->sync($invoice, [
                ['description' => 'Small repair visit', 'quantity' => 1, 'unit_price_cents' => 32500 + ($i * 7500)],
            ], 0, $business->default_tax_rate);
        }

        $directInvoice = Invoice::create([
            'business_id' => $business->id,
            'customer_id' => $directAssigned->customer_id,
            'job_id' => $directAssigned->id,
            'invoice_number' => $this->numbers->invoiceNumber($business),
            'status' => 'draft',
            'due_date' => now()->addDays(14)->toDateString(),
            'tax_rate' => $business->default_tax_rate,
        ]);
        $this->invoiceCalculator->sync($directInvoice, [['description' => $directAssigned->title, 'quantity' => 1, 'unit_price_cents' => $directAssigned->quoted_total_cents]], 0, $business->default_tax_rate);
        $directAssigned->forceFill(['invoice_id' => $directInvoice->id])->save();
    }

    private function seedFollowups(Business $business): void
    {
        $jobs = $business->jobs()->where('status', 'completed')->with(['customer', 'serviceType'])->get();
        $templates = $business->followupTemplates()->get();

        foreach ($jobs as $jobIndex => $job) {
            foreach (['thank_you', 'review_request', 'repeat_service', 'seasonal_reminder'] as $purposeIndex => $purpose) {
                $channel = $purpose === 'review_request' ? 'email' : 'sms';
                $template = $templates->first(fn ($template) => $template->purpose === $purpose && $template->channel === $channel)
                    ?: $templates->first(fn ($template) => $template->purpose === $purpose);

                if (! $template) {
                    continue;
                }

                $status = $purposeIndex === 0 && $jobIndex < 5 ? 'simulated_sent' : 'scheduled';
                $scheduledAt = $purposeIndex < 2
                    ? now()->subDays(6 - $jobIndex + $purposeIndex)
                    : now()->addDays(20 + ($jobIndex * 7) + ($purposeIndex * 10));

                if (($jobIndex + $purposeIndex) % 7 === 0) {
                    $status = 'skipped';
                }

                $message = FollowupMessage::create([
                    'business_id' => $business->id,
                    'customer_id' => $job->customer_id,
                    'job_id' => $job->id,
                    'template_id' => $template->id,
                    'channel' => $channel,
                    'purpose' => $purpose,
                    'status' => $status,
                    'scheduled_at' => $scheduledAt,
                    'sent_at' => $status === 'simulated_sent' ? $scheduledAt->copy()->addMinute() : null,
                    'recipient' => $channel === 'sms' ? $job->customer->phone : $job->customer->email,
                    'subject' => $template->subject,
                    'body' => str_replace(['{{customer_first_name}}', '{{business_name}}', '{{service_name}}'], [$job->customer->first_name, $business->name, $job->serviceType->name], $template->body),
                    'skip_reason' => $status === 'skipped' ? 'Missing consent or recipient in demo data' : null,
                ]);

                $message->events()->create([
                    'business_id' => $business->id,
                    'event_type' => $status === 'simulated_sent' ? 'simulated_sent' : ($status === 'skipped' ? 'skipped' : 'created'),
                    'event_data' => ['demo_seed' => true],
                    'created_at' => now(),
                ]);
            }
        }

        $customer = $business->customers()->whereNotNull('phone')->first();
        FollowupMessage::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'created_by_user_id' => $business->users()->first()?->id,
            'is_manual' => true,
            'channel' => 'sms',
            'purpose' => 'sales_follow_up',
            'status' => 'scheduled',
            'scheduled_at' => now()->addDays(2),
            'recipient' => $customer->phone,
            'body' => 'Hi '.$customer->first_name.', just following up to see if you want to get on the schedule.',
        ]);
    }
}
