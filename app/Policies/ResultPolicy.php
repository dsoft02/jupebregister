<?php

namespace App\Policies;

use App\Models\Result;
use App\Models\User;

class ResultPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('results.view');
    }

    public function view(User $user, Result $result): bool
    {
        return $user->can('results.view');
    }

    public function enter(User $user, Result $result = null): bool
    {
        return $user->can('results.enter');
    }

    public function update(User $user, Result $result): bool
    {
        return $user->can('results.enter');
    }

    public function publish(User $user, Result $result): bool
    {
        return $user->can('results.publish');
    }

    public function delete(User $user, Result $result): bool
    {
        return $user->can('results.enter');
    }
}
