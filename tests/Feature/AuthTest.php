<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Services\DemoDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_and_log_out(): void
    {
        [$user] = $this->account();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_invalid_login_is_rejected_and_dashboard_requires_auth(): void
    {
        [$user] = $this->account();

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');

        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_demo_login_works_only_when_demo_mode_is_enabled(): void
    {
        app(DemoDataService::class)->reset();

        $this->post('/demo-login')->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        auth()->logout();
        config(['tradeloop.demo_mode' => false]);

        $this->post('/demo-login')->assertNotFound();
    }

    private function account(string $role = 'owner'): array
    {
        $user = User::factory()->create();
        $business = Business::factory()->create();
        $business->users()->attach($user->id, ['role' => $role]);

        return [$user, $business];
    }
}
