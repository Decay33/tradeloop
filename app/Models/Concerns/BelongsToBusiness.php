<?php

namespace App\Models\Concerns;

use App\Models\Business;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBusiness
{
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function scopeForBusiness(Builder $query, Business|int $business): Builder
    {
        $businessId = $business instanceof Business ? $business->id : $business;

        return $query->where($query->getModel()->getTable().'.business_id', $businessId);
    }

    public function belongsToBusiness(Business|int|null $business): bool
    {
        if (! $business) {
            return false;
        }

        $businessId = $business instanceof Business ? $business->id : $business;

        return (int) $this->business_id === (int) $businessId;
    }
}
