<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UploadSessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'folderId' => $this->target_folder_id,
            'name' => $this->original_name,
            'status' => $this->status?->value,
            'totalChunks' => $this->total_chunks,
            'uploadedChunks' => $this->uploaded_chunks,
            'totalSizeBytes' => $this->total_size_bytes,
            'expiresAt' => $this->expires_at?->toIso8601String(),
            'completedDriveItemId' => $this->completed_drive_item_id,
        ];
    }
}
