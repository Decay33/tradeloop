<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Database\Factories\FollowupTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FollowupTemplate extends Model
{
    /** @use HasFactory<FollowupTemplateFactory> */
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'channel',
        'purpose',
        'subject',
        'body',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function rules(): HasMany
    {
        return $this->hasMany(FollowupRule::class, 'template_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(FollowupMessage::class, 'template_id');
    }
}
