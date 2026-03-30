<?php

namespace App\Models;

use App\Enums\UploadSessionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id',
    'user_id',
    'target_folder_id',
    'original_name',
    'disk',
    'temp_directory',
    'total_size_bytes',
    'total_chunks',
    'uploaded_chunks',
    'mime_type',
    'checksum_sha256',
    'status',
    'last_chunk_at',
    'completed_drive_item_id',
    'metadata',
    'expires_at',
])]
class UploadSession extends Model
{
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'status' => UploadSessionStatus::class,
            'metadata' => 'array',
            'total_size_bytes' => 'integer',
            'total_chunks' => 'integer',
            'uploaded_chunks' => 'integer',
            'last_chunk_at' => 'datetime',
            'expires_at' => 'datetime',
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
    public function folder(): BelongsTo
    {
        return $this->belongsTo(DriveItem::class, 'target_folder_id');
    }

    /**
     * @return BelongsTo<DriveItem, $this>
     */
    public function completedDriveItem(): BelongsTo
    {
        return $this->belongsTo(DriveItem::class, 'completed_drive_item_id');
    }
}
