<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'password',
    'preferred_locale',
    'timezone',
    'role',
    'storage_quota_bytes',
    'used_storage_bytes',
    'last_seen_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
            'storage_quota_bytes' => 'integer',
            'used_storage_bytes' => 'integer',
            'role' => UserRole::class,
        ];
    }

    /**
     * @return HasMany<DriveItem, $this>
     */
    public function driveItems(): HasMany
    {
        return $this->hasMany(DriveItem::class);
    }

    /**
     * @return HasMany<UploadSession, $this>
     */
    public function uploadSessions(): HasMany
    {
        return $this->hasMany(UploadSession::class);
    }

    /**
     * @return HasMany<ShareLink, $this>
     */
    public function createdShares(): HasMany
    {
        return $this->hasMany(ShareLink::class, 'created_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }
}
