<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareLinkResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $appUrl = rtrim((string) config('app.url'), '/');

        return [
            'id' => $this->id,
            'token' => $this->token,
            'visibility' => $this->visibility?->value,
            'permission' => $this->permission?->value,
            'requiresPassword' => $this->requires_password,
            'recipientUserId' => $this->recipient_user_id,
            'recipientEmail' => $this->recipient_email,
            'expiresAt' => $this->expires_at?->toIso8601String(),
            'lastAccessedAt' => $this->last_accessed_at?->toIso8601String(),
            'downloadCount' => $this->download_count,
            'maxDownloads' => $this->max_downloads,
            'isActive' => $this->is_active,
            'publicUrl' => $appUrl . route('api.v1.public-shares.show', ['token' => $this->token], false),
            'downloadUrl' => $appUrl . route('api.v1.public-shares.download', ['token' => $this->token], false),
        ];
    }
}
