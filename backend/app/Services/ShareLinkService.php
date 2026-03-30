<?php

namespace App\Services;

use App\DataTransferObjects\Share\CreateShareLinkData;
use App\Enums\SharePermission;
use App\Enums\ShareVisibility;
use App\Models\DriveItem;
use App\Models\ShareLink;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShareLinkService
{
    public function __construct(
        private readonly DriveItemService $driveItemService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function create(User $user, CreateShareLinkData $data): ShareLink
    {
        $item = $this->driveItemService->findOwned($user, $data->driveItemId);

        if ($data->visibility === ShareVisibility::Private
            && ! $data->recipientUserId
            && ! $data->recipientEmail) {
            throw ValidationException::withMessages([
                'recipient_email' => __('share.private_recipient_required'),
            ]);
        }

        $shareLink = ShareLink::create([
            'drive_item_id' => $item->id,
            'created_by' => $user->id,
            'token' => Str::random(40),
            'visibility' => $data->visibility,
            'permission' => $data->permission,
            'recipient_user_id' => $data->recipientUserId,
            'recipient_email' => $data->recipientEmail,
            'requires_password' => filled($data->password),
            'password_hash' => filled($data->password) ? Hash::make($data->password) : null,
            'expires_at' => $data->expiresAt,
            'max_downloads' => $data->maxDownloads,
            'is_active' => true,
        ]);

        $this->auditLogService->record($user, 'share.created', $shareLink, [
            'drive_item_id' => $item->id,
            'visibility' => $shareLink->visibility->value,
        ]);

        return $shareLink;
    }

    public function resolve(string $token, ?User $viewer = null, ?string $password = null): ShareLink
    {
        /** @var ShareLink $shareLink */
        $shareLink = ShareLink::query()
            ->with('driveItem')
            ->where('token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        if ($shareLink->expires_at && $shareLink->expires_at->isPast()) {
            abort(410, __('share.link_expired'));
        }

        if ($shareLink->max_downloads && $shareLink->download_count >= $shareLink->max_downloads) {
            abort(410, __('share.max_downloads_reached'));
        }

        if ($shareLink->visibility === ShareVisibility::Private) {
            if (! $viewer) {
                abort(403, __('share.private_access_denied'));
            }

            if ($shareLink->recipient_user_id && $shareLink->recipient_user_id !== $viewer->id) {
                abort(403, __('share.private_access_denied'));
            }

            if ($shareLink->recipient_email && $shareLink->recipient_email !== $viewer->email) {
                abort(403, __('share.private_access_denied'));
            }
        }

        if ($shareLink->requires_password && ! Hash::check((string) $password, (string) $shareLink->password_hash)) {
            throw ValidationException::withMessages([
                'password' => __('share.password_invalid'),
            ]);
        }

        return $shareLink;
    }

    public function registerAccess(ShareLink $shareLink, bool $downloaded = false): ShareLink
    {
        $shareLink->forceFill([
            'last_accessed_at' => now(),
            'download_count' => $downloaded ? $shareLink->download_count + 1 : $shareLink->download_count,
        ])->save();

        return $shareLink->refresh();
    }

    public function assertDownloadAllowed(ShareLink $shareLink): void
    {
        if ($shareLink->permission !== SharePermission::Download) {
            abort(403, __('share.download_not_allowed'));
        }
    }

    public function listForItem(User $user, DriveItem $item)
    {
        $this->driveItemService->findOwned($user, $item->id);

        return $item->shareLinks()->latest()->get();
    }
}
