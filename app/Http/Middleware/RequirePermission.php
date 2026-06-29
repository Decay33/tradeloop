<?php

namespace App\Http\Middleware;

use App\Services\CurrentBusinessResolver;
use Closure;
use Illuminate\Http\Request;

class RequirePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        $business = app(CurrentBusinessResolver::class)->resolve($request->user());

        abort_unless($request->user() && $business, 403);

        foreach ($permissions as $permission) {
            if ($request->user()->hasBusinessPermission($business, $permission)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
