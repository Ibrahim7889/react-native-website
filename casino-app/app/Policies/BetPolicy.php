<?php

namespace App\Policies;

use App\Models\Bet;
use App\Models\User;

class BetPolicy
{
    public function create(User $user): bool
    {
        return $user->role === User::ROLE_USER;
    }

    public function view(User $user, Bet $bet): bool
    {
        return $bet->user_id === $user->id;
    }
}
