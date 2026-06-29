<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class)->withPivot('role', 'permissions', 'is_active')->withTimestamps();
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function roleForBusiness(Business|int $business): ?string
    {
        $businessId = $business instanceof Business ? $business->id : $business;
        $loaded = $this->relationLoaded('businesses')
            ? $this->businesses->firstWhere('id', $businessId)
            : $this->businesses()->whereKey($businessId)->first();

        return $loaded?->pivot?->role;
    }

    public function isActiveForBusiness(Business|int $business): bool
    {
        $pivot = $this->businessPivot($business);

        return $pivot ? (bool) ($pivot->is_active ?? true) : false;
    }

    public function permissionsForBusiness(Business|int $business): array
    {
        $pivot = $this->businessPivot($business);
        $role = $pivot?->role;

        if ($role === 'owner') {
            return self::allPermissions();
        }

        $defaults = match ($role) {
            'manager' => array_values(array_diff(self::allPermissions(), ['manage_team'])),
            'field_staff', 'staff' => ['view_dashboard', 'manage_customers', 'create_jobs', 'start_jobs', 'complete_jobs', 'manage_followups'],
            default => [],
        };

        $stored = $pivot?->permissions ? json_decode((string) $pivot->permissions, true) : null;

        return is_array($stored) && $stored !== [] ? $stored : $defaults;
    }

    public function hasBusinessPermission(Business|int $business, string $permission): bool
    {
        if (! $this->isActiveForBusiness($business)) {
            return false;
        }

        return in_array($permission, $this->permissionsForBusiness($business), true);
    }

    public static function allPermissions(): array
    {
        return [
            'view_dashboard',
            'manage_customers',
            'create_estimates',
            'manage_estimates',
            'create_jobs',
            'start_jobs',
            'complete_jobs',
            'manage_invoices',
            'record_payments',
            'manage_followups',
            'view_reports',
            'manage_settings',
            'manage_team',
        ];
    }

    private function businessPivot(Business|int $business)
    {
        $businessId = $business instanceof Business ? $business->id : $business;
        $loaded = $this->relationLoaded('businesses')
            ? $this->businesses->firstWhere('id', $businessId)
            : $this->businesses()->whereKey($businessId)->first();

        return $loaded?->pivot;
    }
}
