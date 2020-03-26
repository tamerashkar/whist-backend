<?php

namespace App\Observers;

use App\Player;
use App\User;

class UserObserver
{
    public function created(User $user)
    {
        Player::create([
            'name' => $user->name,
            'user_id' => $user->id
        ]);
    }
}
