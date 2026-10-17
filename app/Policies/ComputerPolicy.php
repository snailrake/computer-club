<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Computer;
use App\Models\User;

class ComputerPolicy
{
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Computer $computer): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Computer $computer): bool
    {
        return $user->isAdmin();
    }
}
