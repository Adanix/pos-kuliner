<?php

namespace App\Policies;

use App\Models\User;

class MenuPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function manage(User $user): bool
    {
        return $user->is_active && $user->isOwner();
    }
}
