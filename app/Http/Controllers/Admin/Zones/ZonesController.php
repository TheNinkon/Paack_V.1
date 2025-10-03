<?php

namespace App\Http\Controllers\Admin\Zones;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Zones\StoreZoneRequest;
use App\Http\Requests\Admin\Zones\UpdateZoneRequest;
use App\Models\Client;
use App\Models\Zone;
use App\Support\ClientContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZonesController extends Controller
{
    protected ClientContext $clientContext;

    protected string $routePrefix = 'admin.zones.';

    public function __construct(ClientContext $clientContext)
    {
        $this->clientContext = $clientContext;
        $this->authorizeResource(Zone::class, 'zone');
    }

    public function index(Request $request): View
    {
        $zones = $this->paginatedZones();

        $statsBase = $this->filteredZonesQuery();
        $stats = [
            'total' => (clone $statsBase)->count(),
            'active' => (clone $statsBase)->where('active', true)->count(),
            'inactive' => (clone $statsBase)->where('active', false)->count(),
            'with_code' => (clone $statsBase)->whereNotNull('code')->count(),
        ];

        return view('Admin.Zones.index', [
            'zones' => $zones,
            'stats' => $stats,
            'currentClient' => $this->clientContext->client(),
            'clients' => $this->clientOptions(),
            'defaultClientId' => $this->defaultClientId(),
            'routePrefix' => $this->routePrefix,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route($this->routeName('index'))->with('openCreateZone', true);
    }

    public function store(StoreZoneRequest $request): RedirectResponse
    {
        Zone::create($request->validated());

        return redirect()
            ->route($this->routeName('index'))
            ->with('status', 'zone-created');
    }

    public function edit(Zone $zone): View
    {
        $zone->load(['creator', 'updater']);

        return view('Admin.Zones.edit', [
            'zone' => $zone,
            'clients' => $this->clientOptions(),
            'defaultClientId' => $zone->client_id,
            'routePrefix' => $this->routePrefix,
        ]);
    }

    public function update(UpdateZoneRequest $request, Zone $zone): RedirectResponse
    {
        $zone->update($request->validated());

        return redirect()
            ->route($this->routeName('edit'), $zone)
            ->with('status', 'zone-updated');
    }

    public function destroy(Zone $zone): RedirectResponse
    {
        $zone->delete();

        return redirect()
            ->route($this->routeName('index'))
            ->with('status', 'zone-deleted');
    }

    protected function paginatedZones(): LengthAwarePaginator
    {
        return $this->filteredZonesQuery()
            ->with(['client', 'creator', 'updater'])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();
    }

    protected function filteredZonesQuery(): Builder
    {
        $query = Zone::query();
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

    protected function routeName(string $suffix): string
    {
        return $this->routePrefix . $suffix;
    }
}
