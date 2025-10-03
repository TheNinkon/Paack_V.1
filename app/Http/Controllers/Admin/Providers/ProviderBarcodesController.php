<?php

namespace App\Http\Controllers\Admin\Providers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Providers\StoreProviderBarcodeRequest;
use App\Http\Requests\Admin\Providers\UpdateProviderBarcodeRequest;
use App\Models\Provider;
use App\Models\ProviderBarcode;
use App\Support\ClientContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProviderBarcodesController extends Controller
{
    protected ClientContext $clientContext;
    protected string $providerRoutePrefix = 'admin.providers.';

    public function __construct(ClientContext $clientContext)
    {
        $this->clientContext = $clientContext;
    }

    public function index(): View
    {
        $barcodes = ProviderBarcode::with([
                'creator',
                'updater',
                'provider' => fn ($query) => $query->with(['client', 'creator', 'updater']),
            ])
            ->whereHas('provider', function ($query) {
                $authUser = auth()->user();
                $contextClientId = $this->clientContext->clientId();

                if ($contextClientId) {
                    $query->where('client_id', $contextClientId);
                } elseif ($authUser && ! $authUser->hasRole('super_admin') && $authUser->client_id) {
                    $query->where('client_id', $authUser->client_id);
                }
            })
            ->orderBy('priority')
            ->orderBy('label')
            ->paginate(15)
            ->withQueryString();

        return view('Admin.Providers.Barcodes.index', [
            'barcodes' => $barcodes,
            'providerRoutePrefix' => $this->providerRoutePrefix,
        ]);
    }

    public function store(StoreProviderBarcodeRequest $request, Provider $provider): RedirectResponse
    {
        $provider->barcodes()->create($request->validated());

        return redirect()
            ->route($this->providerRouteName('edit'), $provider)
            ->with('status', 'barcode-created');
    }

    public function update(UpdateProviderBarcodeRequest $request, ProviderBarcode $barcode): RedirectResponse
    {
        $barcode->update($request->validated());

        $provider = $barcode->provider;

        return redirect()
            ->route($this->providerRouteName('edit'), $provider)
            ->with('status', 'barcode-updated');
    }

    public function destroy(ProviderBarcode $barcode): RedirectResponse
    {
        $provider = $barcode->provider;
        $barcode->delete();

        return redirect()
            ->route($this->providerRouteName('edit'), $provider)
            ->with('status', 'barcode-deleted');
    }

    protected function providerRouteName(string $suffix): string
    {
        return $this->providerRoutePrefix . $suffix;
    }
}
