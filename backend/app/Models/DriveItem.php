<?php

namespace App\Models;

use App\Enums\DriveItemType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'parent_id',
    'type',
    'name',
    'disk',
    'storage_path',
    'mime_type',
    'extension',
    'size_bytes',
    'checksum_sha256',
    'preview_status',
    'preview_path',
    'is_favorite',
    'metadata',
    'last_opened_at',
])]
class DriveItem extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => DriveItemType::class,
            'size_bytes' => 'integer',
            'is_favorite' => 'boolean',
            'metadata' => 'array',
            'last_opened_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<DriveItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<DriveItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany<ShareLink, $this>
     */
    public function shareLinks(): HasMany
    {
        return $this->hasMany(ShareLink::class);
    }

    public function isFolder(): bool
    {
        return $this->type === DriveItemType::Folder;
    }
}
