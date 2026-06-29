<?php

namespace App\Services;

use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CurrentBusinessResolver
{
    private ?Business $resolved = null;
    private bool $attempted = false;

    public function resolve(?User $user = null): ?Business
    {
        $user ??= Auth::user();

        if (! $user) {
            return null;
        }

        if ($this->attempted) {
            return $this->resolved;
        }

        $this->attempted = true;
        $selectedId = session('current_business_id');

        if ($selectedId && $this->userCanAccessBusiness($selectedId, $user)) {
            return $this->resolved = $user->businesses()->whereKey($selectedId)->first();
        }

        $business = $user->businesses()->orderBy('businesses.id')->first();

        if ($business) {
            session(['current_business_id' => $business->id]);
        }

        return $this->resolved = $business;
    }

    public function id(?User $user = null): ?int
    {
        return $this->resolve($user)?->id;
    }

    public function role(?User $user = null): ?string
    {
        $user ??= Auth::user();
        $business = $this->resolve($user);

        if (! $user || ! $business) {
            return null;
        }

        return $user->roleForBusiness($business);
    }

    public function userCanAccessBusiness(int|string $businessId, ?User $user = null): bool
    {
        $user ??= Auth::user();

        return $user
            ? $user->businesses()->whereKey($businessId)->wherePivot('is_active', true)->exists()
            : false;
    }

    public function set(Business $business, ?User $user = null): void
    {
        $user ??= Auth::user();

        abort_unless($user && $this->userCanAccessBusiness($business->id, $user), 403);

        session(['current_business_id' => $business->id]);
        $this->resolved = $business;
        $this->attempted = true;
    }
}
