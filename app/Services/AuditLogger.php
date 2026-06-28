<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Business;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public function log(string $action, Business|int|null $business = null, ?Model $entity = null, array $metadata = []): void
    {
        $businessId = $business instanceof Business ? $business->id : $business;

        AuditLog::create([
            'business_id' => $businessId,
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => $entity ? $entity::class : null,
            'entity_id' => $entity?->getKey(),
            'metadata' => $metadata ?: null,
            'created_at' => now(),
        ]);
    }
}
