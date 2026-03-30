<?php

namespace App\DataTransferObjects\Share;

use App\Enums\SharePermission;
use App\Enums\ShareVisibility;
use Carbon\CarbonImmutable;

readonly class CreateShareLinkData
{
    public function __construct(
        public string $driveItemId,
        public ShareVisibility $visibility,
        public SharePermission $permission,
        public ?int $recipientUserId,
        public ?string $recipientEmail,
        public ?CarbonImmutable $expiresAt,
        public ?string $password,
        public ?int $maxDownloads,
    ) {
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self(
            driveItemId: $attributes['drive_item_id'],
            visibility: ShareVisibility::from($attributes['visibility']),
            permission: SharePermission::from($attributes['permission']),
            recipientUserId: $attributes['recipient_user_id'] ?? null,
            recipientEmail: $attributes['recipient_email'] ?? null,
            expiresAt: isset($attributes['expires_at']) ? CarbonImmutable::parse($attributes['expires_at']) : null,
            password: $attributes['password'] ?? null,
            maxDownloads: $attributes['max_downloads'] ?? null,
        );
    }
}
