<?php

namespace App\Policies;

use App\Models\ShareLink;
use App\Models\User;

class ShareLinkPolicy
{
    public function view(User $user, ShareLink $shareLink): bool
    {
        return $user->isAdmin() || $shareLink->created_by === $user->id;
    }

    public function delete(User $user, ShareLink $shareLink): bool
    {
        return $this->view($user, $shareLink);
    }
}
