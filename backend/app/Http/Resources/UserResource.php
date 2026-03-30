<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role?->value,
            'preferredLocale' => $this->preferred_locale,
            'timezone' => $this->timezone,
            'emailVerifiedAt' => $this->email_verified_at?->toIso8601String(),
            'storage' => [
                'quotaBytes' => $this->storage_quota_bytes,
                'usedBytes' => $this->used_storage_bytes,
                'freeBytes' => max(0, $this->storage_quota_bytes - $this->used_storage_bytes),
            ],
            'lastSeenAt' => $this->last_seen_at?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
