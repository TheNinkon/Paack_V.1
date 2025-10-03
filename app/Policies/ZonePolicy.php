<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Zone;

class ZonePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    protected function sameClient(User $user, Zone $zone): bool
    {
        return $user->client_id !== null && $user->client_id === $zone->client_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('zones.manage');
    }

    public function view(User $user, Zone $zone): bool
    {
        return ($user->can('zones.manage') || $user->can('couriers.manage') || $user->can('scan.view'))
            && $this->sameClient($user, $zone);
    }

    public function create(User $user): bool
    {
        return $user->can('zones.manage');
    }

    public function update(User $user, Zone $zone): bool
    {
        return $user->can('zones.manage') && $this->sameClient($user, $zone);
    }

    public function delete(User $user, Zone $zone): bool
    {
        return $user->can('zones.manage') && $this->sameClient($user, $zone);
    }
}
