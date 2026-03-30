<?php

namespace App\Services;

use App\DataTransferObjects\Drive\CreateFolderData;
use App\Enums\DriveItemType;
use App\Models\DriveItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriveItemService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function findOwned(User $user, string $id, bool $withTrashed = false): DriveItem
    {
        $query = DriveItem::query()->where('user_id', $user->id);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function listItems(User $user, ?string $parentId, string $scope = 'root'): LengthAwarePaginator
    {
        $query = DriveItem::query()->where('user_id', $user->id);

        return match ($scope) {
            'favorites' => $query
                ->whereNull('deleted_at')
                ->where('is_favorite', true)
                ->orderByDesc('updated_at')
                ->paginate(50),
            'recent' => $query
                ->whereNull('deleted_at')
                ->whereNotNull('last_opened_at')
                ->orderByDesc('last_opened_at')
                ->paginate(50),
            'trash' => $query
                ->onlyTrashed()
                ->orderByDesc('deleted_at')
                ->paginate(50),
            default => $query
                ->whereNull('deleted_at')
                ->where('parent_id', $parentId)
                ->orderByRaw("CASE WHEN type = 'folder' THEN 0 ELSE 1 END")
                ->orderBy('name')
                ->paginate(50),
        };
    }

    public function createFolder(User $user, CreateFolderData $data): DriveItem
    {
        $this->assertParentOwnership($user, $data->parentId);
        $this->assertUniqueName($user, $data->parentId, $data->name);

        $item = DriveItem::create([
            'user_id' => $user->id,
            'parent_id' => $data->parentId,
            'type' => DriveItemType::Folder,
            'name' => $data->name,
            'disk' => config('filesystems.default'),
            'metadata' => [],
        ]);

        $this->auditLogService->record($user, 'drive.folder_created', $item);

        return $item;
    }

    public function rename(User $user, DriveItem $item, string $name): DriveItem
    {
        $this->assertUniqueName($user, $item->parent_id, $name, $item->id);

        $item->forceFill(['name' => $name])->save();

        $this->auditLogService->record($user, 'drive.renamed', $item, ['name' => $name]);

        return $item->refresh();
    }

    public function move(User $user, DriveItem $item, ?string $parentId): DriveItem
    {
        $this->assertParentOwnership($user, $parentId);
        $this->assertUniqueName($user, $parentId, $item->name, $item->id);

        $item->forceFill(['parent_id' => $parentId])->save();

        $this->auditLogService->record($user, 'drive.moved', $item, ['parent_id' => $parentId]);

        return $item->refresh();
    }

    public function toggleFavorite(User $user, DriveItem $item, bool $isFavorite): DriveItem
    {
        $item->forceFill(['is_favorite' => $isFavorite])->save();

        $this->auditLogService->record($user, 'drive.favorite_toggled', $item, ['is_favorite' => $isFavorite]);

        return $item->refresh();
    }

    public function trash(User $user, DriveItem $item): void
    {
        $item->delete();
        $this->auditLogService->record($user, 'drive.trashed', $item);
    }

    public function restore(User $user, DriveItem $item): DriveItem
    {
        $this->assertUniqueName($user, $item->parent_id, $item->name, $item->id);
        $item->restore();

        $this->auditLogService->record($user, 'drive.restored', $item);

        return $item->refresh();
    }

    public function touchOpened(User $user, DriveItem $item): void
    {
        $item->forceFill(['last_opened_at' => now()])->save();

        $this->auditLogService->record($user, 'drive.opened', $item);
    }

    public function responseForItem(DriveItem $item, bool $inline = false): StreamedResponse
    {
        $headers = [];

        if ($inline) {
            $headers['Content-Disposition'] = 'inline; filename="'.$item->name.'"';
            return Storage::disk($item->disk)->response($item->storage_path, $item->name, $headers);
        }

        return Storage::disk($item->disk)->download($item->storage_path, $item->name, $headers);
    }

    public function suggestUniqueName(User $user, ?string $parentId, string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $base = pathinfo($originalName, PATHINFO_FILENAME);
        $candidate = $originalName;
        $counter = 1;

        while ($this->nameExists($user, $parentId, $candidate)) {
            $suffix = sprintf('%s (%d)', $base, $counter);
            $candidate = $extension !== '' ? $suffix.'.'.$extension : $suffix;
            $counter++;
        }

        return $candidate;
    }

    private function assertParentOwnership(User $user, ?string $parentId): void
    {
        if (! $parentId) {
            return;
        }

        $parent = $this->findOwned($user, $parentId);

        if ($parent->type !== DriveItemType::Folder) {
            throw ValidationException::withMessages([
                'parent_id' => __('files.parent_must_be_folder'),
            ]);
        }
    }

    private function assertUniqueName(User $user, ?string $parentId, string $name, ?string $ignoreId = null): void
    {
        $query = DriveItem::query()
            ->where('user_id', $user->id)
            ->where('parent_id', $parentId)
            ->where('name', $name)
            ->whereNull('deleted_at');

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'name' => __('files.name_already_exists'),
            ]);
        }
    }

    private function nameExists(User $user, ?string $parentId, string $name): bool
    {
        return DriveItem::query()
            ->where('user_id', $user->id)
            ->where('parent_id', $parentId)
            ->where('name', $name)
            ->whereNull('deleted_at')
            ->exists();
    }
}
