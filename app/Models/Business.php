<?php

namespace App\Models;

use Database\Factories\BusinessFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    /** @use HasFactory<BusinessFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'trade_type',
        'phone',
        'email',
        'website',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip',
        'timezone',
        'logo_path',
        'google_review_url',
        'facebook_review_url',
        'default_tax_rate',
        'default_invoice_terms',
    ];

    protected function casts(): array
    {
        return [
            'default_tax_rate' => 'decimal:4',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function serviceTypes(): HasMany
    {
        return $this->hasMany(ServiceType::class);
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

    public function followupTemplates(): HasMany
    {
        return $this->hasMany(FollowupTemplate::class);
    }

    public function followupRules(): HasMany
    {
        return $this->hasMany(FollowupRule::class);
    }

    public function followupMessages(): HasMany
    {
        return $this->hasMany(FollowupMessage::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
