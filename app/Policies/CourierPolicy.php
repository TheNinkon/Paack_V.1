<?php

namespace App\Policies;

use App\Models\Courier;
use App\Models\User;

class CourierPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    protected function sameClient(User $user, Courier $courier): bool
    {
        return $user->client_id !== null && $user->client_id === $courier->client_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('couriers.manage')
            || $user->hasRole('support')
            || $user->hasRole('zone_manager');
    }

    public function view(User $user, Courier $courier): bool
    {
        return ($user->can('couriers.manage')
                || $user->hasRole('support')
                || $user->hasRole('zone_manager'))
            && $this->sameClient($user, $courier);
    }

    public function create(User $user): bool
    {
        return $user->can('couriers.manage');
    }

    public function update(User $user, Courier $courier): bool
    {
        return $user->can('couriers.manage') && $this->sameClient($user, $courier);
    }

    public function delete(User $user, Courier $courier): bool
    {
        return $user->can('couriers.manage') && $this->sameClient($user, $courier);
    }
}
