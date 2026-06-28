<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_can_be_created_updated_searched_and_soft_deleted(): void
    {
        [$user, $business] = $this->account();

        $this->actingAs($user)->post('/customers', [
            'first_name' => 'Avery',
            'last_name' => 'Johnson',
            'email' => 'avery@example.test',
            'phone' => '(555) 100-2000',
            'sms_consent' => true,
            'email_consent' => false,
            'notes' => 'Call before arrival.',
        ])->assertRedirect();

        $customer = Customer::first();
        $this->assertSame($business->id, $customer->business_id);
        $this->assertTrue($customer->sms_consent);
        $this->assertFalse($customer->email_consent);

        $this->actingAs($user)->put("/customers/{$customer->id}", [
            'first_name' => 'Avery',
            'last_name' => 'Smith',
            'email' => 'avery@example.test',
            'phone' => '(555) 100-2000',
            'sms_consent' => false,
            'email_consent' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'last_name' => 'Smith', 'sms_consent' => false, 'email_consent' => true]);

        $this->actingAs($user)->get('/customers?search=Smith')->assertOk();

        $this->actingAs($user)->delete("/customers/{$customer->id}")->assertRedirect();
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    private function account(): array
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        return [$user, $business];
    }
}
