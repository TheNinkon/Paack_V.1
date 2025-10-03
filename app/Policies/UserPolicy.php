<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    protected function sameClient(User $user, User $subject): bool
    {
        return $user->client_id !== null && $user->client_id === $subject->client_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('users.manage');
    }

    public function view(User $user, User $subject): bool
    {
        if (! $user->can('users.manage')) {
            return false;
        }

        return $this->sameClient($user, $subject);
    }

    public function create(User $user): bool
    {
        return $user->can('users.manage');
    }

    public function update(User $user, User $subject): bool
    {
        if ($user->id === $subject->id) {
            return true;
        }

        return $this->view($user, $subject);
    }

    public function delete(User $user, User $subject): bool
    {
        if ($user->id === $subject->id) {
            return false;
        }

        return $this->view($user, $subject);
    }
}
