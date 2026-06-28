<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use BelongsToBusiness, HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'first_name',
        'last_name',
        'company_name',
        'email',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip',
        'sms_consent',
        'email_consent',
        'sms_opted_out_at',
        'email_opted_out_at',
        'notes',
    ];

    protected $appends = ['full_name', 'display_name', 'full_address'];

    protected function casts(): array
    {
        return [
            'sms_consent' => 'boolean',
            'email_consent' => 'boolean',
            'sms_opted_out_at' => 'datetime',
            'email_opted_out_at' => 'datetime',
        ];
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(Estimate::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function followupMessages(): HasMany
    {
        return $this->hasMany(FollowupMessage::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->company_name ?: ($this->full_name ?: 'Unnamed Customer');
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address_line_1,
            $this->address_line_2,
            trim(collect([$this->city, $this->state, $this->zip])->filter()->join(', ')),
        ])->filter()->join("\n");
    }
}
