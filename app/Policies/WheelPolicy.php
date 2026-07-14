<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wheel;

class WheelPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Wheel $wheel): bool
    {
        return $user->id === $wheel->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Wheel $wheel): bool
    {
        return $user->id === $wheel->user_id;
    }

    public function delete(User $user, Wheel $wheel): bool
    {
        return $user->id === $wheel->user_id;
    }

    public function restore(User $user, Wheel $wheel): bool
    {
        return $user->id === $wheel->user_id;
    }

    public function forceDelete(User $user, Wheel $wheel): bool
    {
        return $user->id === $wheel->user_id;
    }
}
