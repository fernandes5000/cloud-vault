<?php

namespace App\Models;

use App\Enums\SharePermission;
use App\Enums\ShareVisibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'drive_item_id',
    'created_by',
    'token',
    'visibility',
    'permission',
    'recipient_user_id',
    'recipient_email',
    'requires_password',
    'password_hash',
    'expires_at',
    'last_accessed_at',
    'download_count',
    'max_downloads',
    'is_active',
])]
class ShareLink extends Model
{
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'visibility' => ShareVisibility::class,
            'permission' => SharePermission::class,
            'requires_password' => 'boolean',
            'is_active' => 'boolean',
            'download_count' => 'integer',
            'max_downloads' => 'integer',
            'expires_at' => 'datetime',
            'last_accessed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<DriveItem, $this>
     */
    public function driveItem(): BelongsTo
    {
        return $this->belongsTo(DriveItem::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
