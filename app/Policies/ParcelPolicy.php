<?php

namespace App\Policies;

use App\Models\Parcel;
use App\Models\User;

class ParcelPolicy
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
        return $user->can('scan.view') || $user->can('scan.create') || $user->hasRole('courier');
    }

    public function view(User $user, Parcel $parcel): bool
    {
        if (! ($user->can('scan.view') || $user->can('scan.create') || $user->hasRole('courier'))) {
            return false;
        }

        if ($user->hasRole('courier')) {
            $courier = $user->courier;

            if (! $courier) {
                return false;
            }

            if ((int) $parcel->courier_id !== (int) $courier->id) {
                return false;
            }
        }

        if ($user->client_id === null) {
            return true;
        }

        return $user->client_id === $parcel->client_id;
    }

    public function create(User $user): bool
    {
        return $user->can('scan.create');
    }

    public function update(User $user, Parcel $parcel): bool
    {
        if (! ($user->can('scan.create') || $user->can('scan.view') || $user->hasRole('courier'))) {
            return false;
        }

        if ($user->hasRole('courier')) {
            $courier = $user->courier;

            if (! $courier) {
                return false;
            }

            if ((int) $parcel->courier_id !== (int) $courier->id) {
                return false;
            }
        }

        if ($user->client_id === null) {
            return true;
        }

        return $user->client_id === $parcel->client_id;
    }
}
