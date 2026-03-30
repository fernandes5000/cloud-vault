<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class QuotaService
{
    public function assertCanStore(User $user, int $bytes): void
    {
        if ($user->used_storage_bytes + $bytes > $user->storage_quota_bytes) {
            throw ValidationException::withMessages([
                'total_size_bytes' => __('files.quota_exceeded'),
            ]);
        }
    }

    public function adjustUsage(User $user, int $delta): void
    {
        $user->increment('used_storage_bytes', $delta);
        $user->refresh();
    }
}
