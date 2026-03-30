<?php

namespace App\Jobs;

use App\Models\DriveItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateDriveItemPreviewJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $driveItemId,
    ) {
    }

    public function handle(): void
    {
        $item = DriveItem::query()->find($this->driveItemId);

        if (! $item) {
            return;
        }

        $previewStatus = match (true) {
            str_starts_with((string) $item->mime_type, 'image/') => 'ready',
            str_starts_with((string) $item->mime_type, 'video/') => 'ready',
            $item->mime_type === 'application/pdf' => 'ready',
            default => 'unsupported',
        };

        $item->forceFill(['preview_status' => $previewStatus])->save();
    }
}
