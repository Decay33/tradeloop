<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Estimate;
use App\Models\FollowupMessage;
use App\Models\Job;
use App\Models\User;
use App\Services\CurrentBusinessResolver;
use Illuminate\Database\Eloquent\Model;

class BusinessOwnedPolicy
{
    public function view(User $user, Model $model): bool
    {
        return $this->owns($user, $model) && $this->canRead($user, $model);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->owns($user, $model) && $this->canWrite($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->owns($user, $model) && in_array($this->role($user), ['owner', 'manager'], true);
    }

    private function owns(User $user, Model $model): bool
    {
        $businessId = app(CurrentBusinessResolver::class)->id($user);

        return $businessId && isset($model->business_id) && (int) $model->business_id === (int) $businessId;
    }

    private function canRead(User $user, Model $model): bool
    {
        $role = $this->role($user);

        if (in_array($role, ['owner', 'manager'], true)) {
            return true;
        }

        return $role === 'staff' && $model instanceof Customer
            || $role === 'staff' && $model instanceof Job
            || $role === 'staff' && $model instanceof Estimate
            || $role === 'staff' && $model instanceof FollowupMessage;
    }

    private function canWrite(User $user, Model $model): bool
    {
        $role = $this->role($user);

        if (in_array($role, ['owner', 'manager'], true)) {
            return true;
        }

        return $role === 'staff' && ($model instanceof Customer || $model instanceof Job);
    }

    private function role(User $user): ?string
    {
        return app(CurrentBusinessResolver::class)->role($user);
    }
}
