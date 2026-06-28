<?php

namespace App\Http\Middleware;

use App\Services\CurrentBusinessResolver;
use Closure;
use Illuminate\Http\Request;

class EnsureCurrentBusiness
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()) {
            return $next($request);
        }

        if (app(CurrentBusinessResolver::class)->resolve($request->user())) {
            return $next($request);
        }

        if ($request->routeIs('onboarding.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        return redirect()->route('onboarding.index');
    }
}
