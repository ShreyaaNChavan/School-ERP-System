<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roleAssignments(): HasMany
    {
        return $this->hasMany(UserRoleAssignment::class);
    }

    // Link the authenticated user to their student profile.
    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class, 'user_id');
    }

    // Link the authenticated user to their parent / guardian profile.
    public function guardian(): HasOne
    {
        return $this->hasOne(Guardian::class, 'user_id');
    }

    public function hasPermission(string $permissionCode): bool
    {
        return $this->roleAssignments()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->whereHas('role', function ($query) use ($permissionCode) {
                $query->where('is_active', true)
                    ->whereHas('permissions', function ($permissionQuery) use ($permissionCode) {
                        $permissionQuery->where('code', $permissionCode);
                    });
            })
            ->exists();
    }
}
