<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Database\Factories\FollowupMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FollowupMessage extends Model
{
    /** @use HasFactory<FollowupMessageFactory> */
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'customer_id',
        'job_id',
        'template_id',
        'channel',
        'purpose',
        'status',
        'scheduled_at',
        'sent_at',
        'canceled_at',
        'recipient',
        'subject',
        'body',
        'skip_reason',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(FollowupTemplate::class, 'template_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MessageEvent::class);
    }
}
