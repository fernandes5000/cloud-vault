<?php

namespace App\Policies;

use App\Models\DriveItem;
use App\Models\User;

class DriveItemPolicy
{
    public function view(User $user, DriveItem $driveItem): bool
    {
        return $user->isAdmin() || $driveItem->user_id === $user->id;
    }

    public function update(User $user, DriveItem $driveItem): bool
    {
        return $this->view($user, $driveItem);
    }

    public function delete(User $user, DriveItem $driveItem): bool
    {
        return $this->view($user, $driveItem);
    }

    public function restore(User $user, DriveItem $driveItem): bool
    {
        return $this->view($user, $driveItem);
    }

    public function share(User $user, DriveItem $driveItem): bool
    {
        return $this->view($user, $driveItem);
    }
}
