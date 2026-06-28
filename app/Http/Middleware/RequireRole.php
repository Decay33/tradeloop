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

        abort_unless($role && in_array($role, $roles, true), 403);

        return $next($request);
    }
}
