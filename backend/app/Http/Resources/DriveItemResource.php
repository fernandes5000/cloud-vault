<?php

namespace App\Http\Resources;

use App\Enums\DriveItemType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriveItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isFile = $this->type === DriveItemType::File;
        $appUrl = rtrim((string) config('app.url'), '/');

        return [
            'id' => $this->id,
            'type' => $this->type?->value,
            'name' => $this->name,
            'parentId' => $this->parent_id,
            'mimeType' => $this->mime_type,
            'extension' => $this->extension,
            'sizeBytes' => $this->size_bytes,
            'isFavorite' => $this->is_favorite,
            'previewStatus' => $this->preview_status,
            'metadata' => $this->metadata ?? [],
            'lastOpenedAt' => $this->last_opened_at?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'downloadUrl' => $isFile
                ? $appUrl . route('api.v1.drive.download', ['driveItem' => $this->id], false)
                : null,
            'previewUrl' => $isFile
                ? $appUrl . route('api.v1.drive.preview', ['driveItem' => $this->id], false)
                : null,
        ];
    }
}