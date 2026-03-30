<?php

namespace App\Services;

use App\DataTransferObjects\Drive\InitiateUploadData;
use App\Enums\DriveItemType;
use App\Enums\UploadSessionStatus;
use App\Jobs\CreateInAppNotificationJob;
use App\Jobs\GenerateDriveItemPreviewJob;
use App\Models\DriveItem;
use App\Models\UploadSession;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChunkUploadService
{
    public function __construct(
        private readonly DriveItemService $driveItemService,
        private readonly QuotaService $quotaService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function initiate(User $user, InitiateUploadData $data): UploadSession
    {
        if ($data->folderId) {
            $folder = $this->driveItemService->findOwned($user, $data->folderId);

            if ($folder->type !== DriveItemType::Folder) {
                throw ValidationException::withMessages([
                    'folder_id' => __('files.parent_must_be_folder'),
                ]);
            }
        }

        if ($data->totalSizeBytes) {
            $this->quotaService->assertCanStore($user, $data->totalSizeBytes);
        }

        $sessionId = (string) Str::ulid();

        $session = new UploadSession([
            'id' => $sessionId,
            'user_id' => $user->id,
            'target_folder_id' => $data->folderId,
            'original_name' => $data->name,
            'disk' => config('filesystems.default'),
            'temp_directory' => sprintf('tmp/uploads/%d/%s', $user->id, $sessionId),
            'total_chunks' => $data->totalChunks,
            'total_size_bytes' => $data->totalSizeBytes,
            'mime_type' => $data->mimeType,
            'checksum_sha256' => $data->checksumSha256,
            'metadata' => $data->metadata,
            'status' => UploadSessionStatus::Pending,
            'expires_at' => now()->addDay(),
        ]);
        $session->save();

        $this->auditLogService->record($user, 'upload.session_started', $session);

        return $session->refresh();
    }

    public function appendChunk(User $user, UploadSession $session, UploadedFile $chunk, int $chunkIndex): UploadSession
    {
        $this->assertSessionOwner($user, $session);

        if ($chunkIndex >= $session->total_chunks) {
            throw ValidationException::withMessages([
                'chunk_index' => __('files.invalid_chunk_index'),
            ]);
        }

        Storage::disk('local')->putFileAs($session->temp_directory, $chunk, 'chunk-'.$chunkIndex);

        $session->forceFill([
            'uploaded_chunks' => $this->countUploadedChunks($session),
            'status' => UploadSessionStatus::Uploading,
            'last_chunk_at' => now(),
        ])->save();

        return $session->refresh();
    }

    public function complete(User $user, UploadSession $session): DriveItem
    {
        $this->assertSessionOwner($user, $session);

        if ($session->uploaded_chunks < $session->total_chunks) {
            throw ValidationException::withMessages([
                'upload_session_id' => __('files.upload_incomplete'),
            ]);
        }

        $assembledPath = storage_path('app/private/'.$session->temp_directory.'/assembled.bin');
        $this->assembleLocalFile($session, $assembledPath);

        $sizeBytes = filesize($assembledPath) ?: 0;
        $this->quotaService->assertCanStore($user, $sizeBytes);

        return DB::transaction(function () use ($user, $session, $assembledPath, $sizeBytes): DriveItem {
            $name = $this->driveItemService->suggestUniqueName($user, $session->target_folder_id, $session->original_name);
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            $storagePath = sprintf(
                'users/%d/%s/%s%s',
                $user->id,
                now()->format('Y/m'),
                Str::ulid()->toBase32(),
                $extension !== '' ? '.'.$extension : ''
            );

            $stream = fopen($assembledPath, 'rb');
            Storage::disk($session->disk)->put($storagePath, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            $item = DriveItem::create([
                'user_id' => $user->id,
                'parent_id' => $session->target_folder_id,
                'type' => DriveItemType::File,
                'name' => $name,
                'disk' => $session->disk,
                'storage_path' => $storagePath,
                'mime_type' => $session->mime_type,
                'extension' => $extension !== '' ? strtolower($extension) : null,
                'size_bytes' => $sizeBytes,
                'checksum_sha256' => $session->checksum_sha256,
                'preview_status' => 'pending',
                'metadata' => $session->metadata ?? [],
                'last_opened_at' => now(),
            ]);

            $session->forceFill([
                'status' => UploadSessionStatus::Completed,
                'completed_drive_item_id' => $item->id,
            ])->save();

            $this->quotaService->adjustUsage($user, $sizeBytes);
            $this->cleanupTempDirectory($session);

            $this->auditLogService->record($user, 'upload.completed', $item, [
                'upload_session_id' => $session->id,
                'size_bytes' => $sizeBytes,
            ]);

            GenerateDriveItemPreviewJob::dispatch($item->id);
            CreateInAppNotificationJob::dispatch(
                userId: $user->id,
                type: 'upload.completed',
                title: __('notifications.upload_completed_title'),
                body: __('notifications.upload_completed_body', ['name' => $item->name]),
                data: ['drive_item_id' => $item->id],
            );

            return $item;
        });
    }

    public function cancel(User $user, UploadSession $session): void
    {
        $this->assertSessionOwner($user, $session);
        $this->cleanupTempDirectory($session);

        $session->forceFill([
            'status' => UploadSessionStatus::Cancelled,
        ])->save();

        $this->auditLogService->record($user, 'upload.cancelled', $session);
    }

    public function pruneExpired(): void
    {
        UploadSession::query()
            ->whereIn('status', [UploadSessionStatus::Pending, UploadSessionStatus::Uploading])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get()
            ->each(function (UploadSession $session): void {
                $this->cleanupTempDirectory($session);
                $session->forceFill(['status' => UploadSessionStatus::Cancelled])->save();
            });
    }

    private function countUploadedChunks(UploadSession $session): int
    {
        return count(Storage::disk('local')->files($session->temp_directory));
    }

    private function assembleLocalFile(UploadSession $session, string $assembledPath): void
    {
        $directory = dirname($assembledPath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $output = fopen($assembledPath, 'wb');

        for ($index = 0; $index < $session->total_chunks; $index++) {
            $relativePath = $session->temp_directory.'/chunk-'.$index;

            if (! Storage::disk('local')->exists($relativePath)) {
                throw ValidationException::withMessages([
                    'upload_session_id' => __('files.missing_chunk', ['index' => $index]),
                ]);
            }

            $input = Storage::disk('local')->readStream($relativePath);
            stream_copy_to_stream($input, $output);

            if (is_resource($input)) {
                fclose($input);
            }
        }

        if (is_resource($output)) {
            fclose($output);
        }
    }

    private function cleanupTempDirectory(UploadSession $session): void
    {
        Storage::disk('local')->deleteDirectory($session->temp_directory);
    }

    private function assertSessionOwner(User $user, UploadSession $session): void
    {
        if ($session->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }
    }
}
