<?php

namespace App\Policies;

use App\Models\Scan;
use App\Models\User;

class ScanPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('scan.view') || $user->can('scan.create');
    }

    public function view(User $user, Scan $scan): bool
    {
        return ($user->can('scan.view') || $user->can('scan.create'))
            && ($user->client_id === null || $user->client_id === $scan->client_id);
    }

    public function create(User $user): bool
    {
        return $user->can('scan.create');
    }
}
