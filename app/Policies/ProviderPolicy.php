<?php

namespace App\Policies;

use App\Models\Provider;
use App\Models\User;

class ProviderPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    protected function sameClient(User $user, Provider $provider): bool
    {
        return $user->client_id !== null && $user->client_id === $provider->client_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('providers.manage');
    }

    public function view(User $user, Provider $provider): bool
    {
        return $user->can('providers.manage') && $this->sameClient($user, $provider);
    }

    public function create(User $user): bool
    {
        return $user->can('providers.manage');
    }

    public function update(User $user, Provider $provider): bool
    {
        return $this->view($user, $provider);
    }

    public function delete(User $user, Provider $provider): bool
    {
        return $this->view($user, $provider);
    }
}
