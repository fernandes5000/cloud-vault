<?php

namespace App\Jobs;

use App\Models\UserNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateInAppNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly int $userId,
        private readonly string $type,
        private readonly string $title,
        private readonly string $body,
        private readonly array $data = [],
    ) {
    }

    public function handle(): void
    {
        UserNotification::create([
            'user_id' => $this->userId,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
        ]);
    }
}
