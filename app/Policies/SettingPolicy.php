<?php

namespace App\Policies;

use App\Models\User;

class SettingPolicy
{
    public function manage(User $user): bool
    {
        return $user->is_active && $user->isOwner();
    }
}
