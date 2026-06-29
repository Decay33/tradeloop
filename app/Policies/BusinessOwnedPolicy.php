<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Estimate;
use App\Models\FollowupMessage;
use App\Models\Invoice;
use App\Models\InvoiceSendEvent;
use App\Models\Job;
use App\Models\Payment;
use App\Models\ServiceType;
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
        if ($this->can($user, 'manage_settings')) {
            return true;
        }

        return match (true) {
            $model instanceof Customer => $this->can($user, 'manage_customers'),
            $model instanceof Job => $this->can($user, 'create_jobs') || $this->can($user, 'start_jobs') || $this->can($user, 'complete_jobs'),
            $model instanceof Estimate => $this->can($user, 'manage_estimates') || $this->can($user, 'create_estimates'),
            $model instanceof Invoice => $this->can($user, 'manage_invoices'),
            $model instanceof Payment => $this->can($user, 'record_payments') || $this->can($user, 'manage_invoices'),
            $model instanceof FollowupMessage => $this->can($user, 'manage_followups'),
            $model instanceof ServiceType => $this->can($user, 'manage_settings'),
            $model instanceof InvoiceSendEvent => $this->can($user, 'manage_invoices'),
            default => false,
        };
    }

    private function canWrite(User $user, Model $model): bool
    {
        return match (true) {
            $model instanceof Customer => $this->can($user, 'manage_customers'),
            $model instanceof Job => $this->can($user, 'create_jobs') || $this->can($user, 'start_jobs') || $this->can($user, 'complete_jobs'),
            $model instanceof Estimate => $this->can($user, 'manage_estimates'),
            $model instanceof Invoice => $this->can($user, 'manage_invoices'),
            $model instanceof Payment => $this->can($user, 'record_payments') || $this->can($user, 'manage_invoices'),
            $model instanceof FollowupMessage => $this->can($user, 'manage_followups'),
            $model instanceof ServiceType => $this->can($user, 'manage_settings'),
            $model instanceof InvoiceSendEvent => $this->can($user, 'manage_invoices'),
            default => false,
        };
    }

    private function role(User $user): ?string
    {
        return app(CurrentBusinessResolver::class)->role($user);
    }

    private function can(User $user, string $permission): bool
    {
        $business = app(CurrentBusinessResolver::class)->resolve($user);

        return $business ? $user->hasBusinessPermission($business, $permission) : false;
    }
}
