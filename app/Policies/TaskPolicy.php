<?php

namespace App\Policies;

use App\Models\User;

class TaskPolicy
{
    public function create(User $user): bool
    {
        return $user->role->name === 'manager';
    }

}