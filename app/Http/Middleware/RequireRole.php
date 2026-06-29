<?php

namespace App\Http\Middleware;

use App\Services\CurrentBusinessResolver;
use Closure;
use Illuminate\Http\Request;

class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $role = app(CurrentBusinessResolver::class)->role($request->user());
        $business = app(CurrentBusinessResolver::class)->resolve($request->user());

        abort_unless($role && $business && $request->user()?->isActiveForBusiness($business) && in_array($role, $roles, true), 403);

        return $next($request);
    }
}
