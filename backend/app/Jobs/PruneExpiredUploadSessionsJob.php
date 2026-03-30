<?php

namespace App\Jobs;

use App\Services\ChunkUploadService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PruneExpiredUploadSessionsJob implements ShouldQueue
{
    use Queueable;

    public function handle(ChunkUploadService $chunkUploadService): void
    {
        $chunkUploadService->pruneExpired();
    }
}
