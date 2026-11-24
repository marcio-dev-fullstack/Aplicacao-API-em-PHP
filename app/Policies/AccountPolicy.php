<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function view(User $user, Account $account): bool
    {
        return $user->id === $account->user_id;
    }

    public function transferFrom(User $user, Account $account): bool
    {
        return $user->id === $account->user_id && $account->is_active;
    }
}
