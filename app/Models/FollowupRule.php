<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Database\Factories\FollowupRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowupRule extends Model
{
    /** @use HasFactory<FollowupRuleFactory> */
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'service_type_id',
        'template_id',
        'trigger_event',
        'delay_amount',
        'delay_unit',
        'channel',
        'purpose',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(FollowupTemplate::class, 'template_id');
    }
}
