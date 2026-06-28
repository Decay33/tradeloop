<?php

namespace App\Http\Middleware;

use App\Services\CurrentBusinessResolver;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $resolver = app(CurrentBusinessResolver::class);
        $business = $request->user() ? $resolver->resolve($request->user()) : null;

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()?->only('id', 'name', 'email'),
                'business' => $business?->only('id', 'name', 'trade_type', 'timezone'),
                'role' => $business && $request->user() ? $request->user()->roleForBusiness($business) : null,
            ],
            'demoMode' => config('tradeloop.demo_mode'),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
