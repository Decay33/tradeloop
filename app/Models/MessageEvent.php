<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Database\Factories\MessageEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageEvent extends Model
{
    /** @use HasFactory<MessageEventFactory> */
    use BelongsToBusiness, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'business_id',
        'followup_message_id',
        'event_type',
        'event_data',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'event_data' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function followupMessage(): BelongsTo
    {
        return $this->belongsTo(FollowupMessage::class);
    }
}
