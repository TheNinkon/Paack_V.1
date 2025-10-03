<?php

namespace App\Http\Controllers\Admin\Providers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Providers\StoreProviderRequest;
use App\Http\Requests\Admin\Providers\UpdateProviderRequest;
use App\Models\Client;
use App\Models\Provider;
use App\Support\ClientContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProvidersController extends Controller
{
    protected ClientContext $clientContext;
    protected string $routePrefix = 'admin.providers.';
    protected string $barcodeStoreRoutePrefix = 'admin.providers.barcodes.';
    protected string $barcodeManageRoutePrefix = 'admin.barcodes.';

    public function __construct(ClientContext $clientContext)
    {
        $this->clientContext = $clientContext;
        $this->authorizeResource(Provider::class, 'provider');
    }

    public function index(): View
    {
        $providers = $this->filteredProvidersQuery()
            ->with(['client', 'creator', 'updater'])
            ->withCount(['barcodes as barcodes_active_count' => fn ($query) => $query->where('active', true)])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $baseQuery = $this->filteredProvidersQuery();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('active', true)->count(),
            'inactive' => (clone $baseQuery)->where('active', false)->count(),
            'with_patterns' => (clone $baseQuery)->whereHas('barcodes')->count(),
        ];

        return view('Admin.Providers.index', [
            'providers' => $providers,
            'stats' => $stats,
            'currentClient' => $this->clientContext->client(),
            'routePrefix' => $this->routePrefix,
            'barcodeStoreRoutePrefix' => $this->barcodeStoreRoutePrefix,
            'barcodeManageRoutePrefix' => $this->barcodeManageRoutePrefix,
        ]);
    }

    public function create(): View
    {
        return view('Admin.Providers.create', [
            'clients' => $this->clientOptions(),
            'defaultClientId' => $this->defaultClientId(),
            'routePrefix' => $this->routePrefix,
        ]);
    }

    public function store(StoreProviderRequest $request): RedirectResponse
    {
        Provider::create($request->validated());

        return redirect()
            ->route($this->routeName('index'))
            ->with('status', 'provider-created');
    }

    public function edit(Provider $provider): View
    {
        $provider->load([
            'client',
            'creator',
            'updater',
            'barcodes' => fn ($query) => $query->with(['creator', 'updater'])->orderBy('priority'),
        ]);

        return view('Admin.Providers.edit', [
            'provider' => $provider,
            'clients' => $this->clientOptions(),
            'defaultClientId' => $provider->client_id,
            'routePrefix' => $this->routePrefix,
            'barcodeStoreRoutePrefix' => $this->barcodeStoreRoutePrefix,
            'barcodeManageRoutePrefix' => $this->barcodeManageRoutePrefix,
        ]);
    }

    public function update(UpdateProviderRequest $request, Provider $provider): RedirectResponse
    {
        $provider->update($request->validated());

        return redirect()
            ->route($this->routeName('edit'), $provider)
            ->with('status', 'provider-updated');
    }

    public function destroy(Provider $provider): RedirectResponse
    {
        $provider->delete();

        return redirect()
            ->route($this->routeName('index'))
            ->with('status', 'provider-deleted');
    }

    protected function clientOptions()
    {
        $user = auth()->user();

        if (! $user || (! $user->hasRole('super_admin') && $user->client_id)) {
            return collect();
        }

        return Client::orderBy('name')->get(['id', 'name']);
    }

    protected function defaultClientId(): ?int
    {
        return $this->clientContext->clientId() ?? auth()->user()?->client_id;
    }

    protected function filteredProvidersQuery()
    {
        $query = Provider::query();
        $authUser = auth()->user();
        $contextClientId = $this->clientContext->clientId();

        if ($contextClientId) {
            $query->where('client_id', $contextClientId);
        } elseif ($authUser && ! $authUser->hasRole('super_admin') && $authUser->client_id) {
            $query->where('client_id', $authUser->client_id);
        }

        return $query;
    }

    protected function routeName(string $suffix): string
    {
        return $this->routePrefix . $suffix;
    }

    protected function barcodeRouteName(string $suffix): string
    {
        return $this->barcodeStoreRoutePrefix . $suffix;
    }
}
