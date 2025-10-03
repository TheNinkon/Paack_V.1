<?php

namespace App\Http\Controllers\Admin\Couriers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Couriers\StoreCourierRequest;
use App\Http\Requests\Admin\Couriers\UpdateCourierRequest;
use App\Models\Client;
use App\Models\Courier;
use App\Models\User;
use App\Models\Zone;
use App\Support\ClientContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CouriersController extends Controller
{
    protected ClientContext $clientContext;

    protected string $routePrefix = 'admin.couriers.';

    public function __construct(ClientContext $clientContext)
    {
        $this->clientContext = $clientContext;
        $this->authorizeResource(Courier::class, 'courier');
    }

    public function index(): View
    {
        $couriers = $this->paginatedCouriers();

        $statsBase = $this->filteredCouriersQuery();
        $stats = [
            'total' => (clone $statsBase)->count(),
            'active' => (clone $statsBase)->where('active', true)->count(),
            'inactive' => (clone $statsBase)->where('active', false)->count(),
        ];

        $vehicleBreakdown = Courier::VEHICLE_TYPES;
        $vehicleStats = collect($vehicleBreakdown)->mapWithKeys(function (string $type) use ($statsBase) {
            return [$type => (clone $statsBase)->where('vehicle_type', $type)->count()];
        });

        $defaultClientId = $this->defaultClientId();

        $availableUsers = $this->availableUsers($defaultClientId);
        $availableZones = $this->availableZones($defaultClientId);

        return view('Admin.Couriers.index', [
            'couriers' => $couriers,
            'stats' => $stats,
            'vehicleStats' => $vehicleStats,
            'currentClient' => $this->clientContext->client(),
            'clients' => $this->clientOptions(),
            'defaultClientId' => $defaultClientId,
            'availableUsers' => $availableUsers,
            'availableUsersMap' => $this->availableUsersMap($defaultClientId),
            'availableZones' => $availableZones,
            'availableZonesMap' => $this->availableZonesMap($defaultClientId),
            'vehicleTypes' => Courier::VEHICLE_TYPES,
            'routePrefix' => $this->routePrefix,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route($this->routeName('index'))->with('openCreateCourier', true);
    }

    public function store(StoreCourierRequest $request): RedirectResponse
    {
        $courier = Courier::create($request->validated());

        $courier->loadMissing('user');
        if ($courier->user && ! $courier->user->hasRole('courier')) {
            $courier->user->assignRole('courier');
        }

        return redirect()
            ->route($this->routeName('index'))
            ->with('status', 'courier-created');
    }

    public function edit(Courier $courier): View
    {
        $courier->load('user', 'client', 'creator', 'updater', 'zone');

        return view('Admin.Couriers.edit', [
            'courier' => $courier,
            'clients' => $this->clientOptions(),
            'defaultClientId' => $courier->client_id,
            'availableUsers' => $this->availableUsers($courier->client_id, $courier),
            'availableUsersMap' => $this->availableUsersMap($courier->client_id, $courier),
            'availableZones' => $this->availableZones($courier->client_id),
            'availableZonesMap' => $this->availableZonesMap($courier->client_id, $courier),
            'vehicleTypes' => Courier::VEHICLE_TYPES,
            'routePrefix' => $this->routePrefix,
        ]);
    }

    public function update(UpdateCourierRequest $request, Courier $courier): RedirectResponse
    {
        $originalUserId = $courier->user_id;
        $courier->update($request->validated());

        $courier->load('user');

        if ($courier->user && ! $courier->user->hasRole('courier')) {
            $courier->user->assignRole('courier');
        }

        if ($originalUserId && $originalUserId !== $courier->user_id) {
            $this->removeCourierRoleIfUnused($originalUserId);
        }

        return redirect()
            ->route($this->routeName('edit'), $courier)
            ->with('status', 'courier-updated');
    }

    public function destroy(Courier $courier): RedirectResponse
    {
        $userId = $courier->user_id;
        $courier->delete();

        if ($userId) {
            $this->removeCourierRoleIfUnused($userId);
        }

        return redirect()
            ->route($this->routeName('index'))
            ->with('status', 'courier-deleted');
    }

    protected function paginatedCouriers(): LengthAwarePaginator
    {
        return $this->filteredCouriersQuery()
            ->with(['user', 'client', 'creator', 'updater', 'zone'])
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();
    }

    protected function filteredCouriersQuery(): Builder
    {
        $query = Courier::query();
        $authUser = auth()->user();
        $contextClientId = $this->clientContext->clientId();

        if ($contextClientId) {
            $query->where('client_id', $contextClientId);
        } elseif ($authUser && ! $authUser->hasRole('super_admin') && $authUser->client_id) {
            $query->where('client_id', $authUser->client_id);
        }

        return $query;
    }

    protected function clientOptions()
    {
        $authUser = auth()->user();

        if ($authUser && $authUser->hasRole('super_admin')) {
            return Client::orderBy('name')->get(['id', 'name']);
        }

        return collect();
    }

    protected function defaultClientId(?int $fallback = null): ?int
    {
        return $this->clientContext->clientId()
            ?? $fallback
            ?? auth()->user()?->client_id;
    }

    protected function availableUsers(?int $clientId, ?Courier $includeCourier = null)
    {
        if (! $clientId) {
            return collect();
        }

        $assignedUserIds = Courier::query()
            ->where('client_id', $clientId)
            ->when($includeCourier, fn ($query) => $query->whereKeyNot($includeCourier->getKey()))
            ->pluck('user_id')
            ->filter()
            ->all();

        $query = User::query()
            ->where('client_id', $clientId)
            ->orderBy('name');

        if (! empty($assignedUserIds)) {
            $query->where(function ($inner) use ($assignedUserIds, $includeCourier) {
                $inner->whereNotIn('id', $assignedUserIds);

                if ($includeCourier) {
                    $inner->orWhere('id', $includeCourier->user_id);
                }
            });
        }

        return $query->get(['id', 'name', 'email']);
    }

    protected function availableUsersMap(?int $primaryClientId = null, ?Courier $includeCourier = null)
    {
        $clientIds = $this->clientOptions()->pluck('id')->all();

        if (empty($clientIds)) {
            $clientIds = array_filter([
                $this->defaultClientId($primaryClientId),
                auth()->user()?->client_id,
            ]);
        }

        if ($primaryClientId) {
            array_unshift($clientIds, $primaryClientId);
        }

        $clientIds = array_values(array_unique(array_filter($clientIds)));

        $map = collect();

        foreach ($clientIds as $clientId) {
            $map->put($clientId, $this->availableUsers($clientId, $includeCourier)->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])->values());
        }

        return $map;
    }

    protected function availableZones(?int $clientId)
    {
        if (! $clientId) {
            return collect();
        }

        return Zone::query()
            ->where('client_id', $clientId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    protected function availableZonesMap(?int $primaryClientId = null, ?Courier $includeCourier = null)
    {
        $clientIds = $this->clientOptions()->pluck('id')->all();

        if (empty($clientIds)) {
            $clientIds = array_filter([
                $this->defaultClientId($primaryClientId),
                auth()->user()?->client_id,
            ]);
        }

        if ($primaryClientId) {
            array_unshift($clientIds, $primaryClientId);
        }

        $clientIds = array_values(array_unique(array_filter($clientIds)));

        $map = collect();

        foreach ($clientIds as $clientId) {
            $zones = $this->availableZones($clientId);

            if ($includeCourier && $includeCourier->zone && $includeCourier->zone->client_id === $clientId && $zones->where('id', $includeCourier->zone_id)->isEmpty()) {
                $zones->push($includeCourier->zone);
            }

            $map->put($clientId, $zones->map(fn ($zone) => [
                'id' => $zone->id,
                'name' => $zone->name,
            ])->values());
        }

        return $map;
    }

    protected function removeCourierRoleIfUnused(int $userId): void
    {
        $user = User::find($userId);

        if (! $user) {
            return;
        }

        $stillAssigned = Courier::where('user_id', $userId)->exists();

        if (! $stillAssigned && $user->hasRole('courier')) {
            $user->removeRole('courier');
        }
    }

    protected function routeName(string $suffix): string
    {
        return $this->routePrefix . $suffix;
    }
}
