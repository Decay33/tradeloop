<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CurrentBusinessResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'demoMode' => config('tradeloop.demo_mode'),
        ]);
    }

    public function store(LoginRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $request->user()->forceFill(['last_login_at' => now()])->save();
        $business = app(CurrentBusinessResolver::class)->resolve($request->user());
        $auditLogger->log('login', $business?->id, $request->user());

        return redirect()->intended(route('dashboard'));
    }

    public function demo(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless(config('tradeloop.demo_mode'), 404);

        $user = User::where('email', 'demo@tradeloop.test')->first();

        if (! $user) {
            $user = User::create([
                'name' => 'Demo Owner',
                'email' => 'demo@tradeloop.test',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        $business = app(CurrentBusinessResolver::class)->resolve($user);
        $auditLogger->log('login', $business?->id, $user, ['demo' => true]);

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
