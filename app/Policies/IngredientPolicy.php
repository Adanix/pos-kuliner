<?php

namespace App\Policies;

use App\Models\User;

class IngredientPolicy
{
    public function manage(User $user): bool
    {
        return $user->is_active && $user->isOwner();
    }
}
