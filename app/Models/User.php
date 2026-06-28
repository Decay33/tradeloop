<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        return $this->belongsToMany(Business::class)->withPivot('role')->withTimestamps();
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
}
