<?php

namespace App\Http\Controllers\App\Parcels;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\Parcels\StoreParcelBatchRequest;
use App\Http\Requests\App\Parcels\UpdateParcelRequest;
use App\Models\Courier;
use App\Models\Parcel;
use App\Models\Provider;
use App\Services\Geocoding\GoogleMapsGeocoder;
use App\Services\ParcelEventRecorder;
use App\Support\ClientContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ParcelsController extends Controller
{
    public function __construct(
        private readonly ParcelEventRecorder $eventRecorder,
        private readonly GoogleMapsGeocoder $geocoder,
        private readonly ClientContext $clientContext
    ) {
        $this->middleware('can:scan.view');
    }

    public function index(): View
    {
        $this->authorize('viewAny', Parcel::class);

        $baseQuery = Parcel::query();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'with_provider' => (clone $baseQuery)->whereNotNull('provider_id')->count(),
            'without_provider' => (clone $baseQuery)->whereNull('provider_id')->count(),
            'scanned_today' => (clone $baseQuery)->whereHas('scans', function ($query) {
                $query->whereDate('created_at', Carbon::today());
            })->count(),
        ];

        $parcels = Parcel::with([
                'provider',
                'courier.user',
                'latestScan.creator',
                'latestScan.provider',
            ])
            ->withCount('scans')
            ->latest()
            ->limit(500)
            ->get();

        $providers = Provider::orderBy('name')->get(['id', 'name']);

        $couriers = Courier::query()
            ->with('user:id,name')
            ->get(['id', 'client_id', 'user_id', 'vehicle_type', 'external_code', 'active'])
            ->sortBy(fn ($courier) => mb_strtolower($courier->user?->name ?? ''))
            ->values();

        return view('App.Parcels.index', [
            'parcels' => $parcels,
            'providers' => $providers,
            'couriers' => $couriers,
            'stats' => $stats,
            'latestLimit' => 500,
        ]);
    }

    public function edit(Request $request, Parcel $parcel): View|JsonResponse
    {
        $this->authorize('update', $parcel);

        $providers = Provider::query()
            ->with(['barcodes' => fn ($query) => $query->select(['id', 'provider_id', 'label'])->orderBy('priority')->orderBy('label')])
            ->orderBy('name')
            ->get(['id', 'name']);

        $couriers = Courier::query()
            ->with('user:id,name')
            ->where('client_id', $parcel->client_id)
            ->get(['id', 'client_id', 'user_id', 'vehicle_type', 'external_code', 'active'])
            ->sortBy(fn ($courier) => mb_strtolower($courier->user?->name ?? ''))
            ->values();

        $parcel->load(['provider', 'providerBarcode', 'courier.user', 'client']);

        $client = $parcel->client ?? $this->clientContext->client();
        $mapsApiKey = $client?->google_maps_api_key;

        if ($request->expectsJson()) {
            return response()->json([
                'title' => __('Editar bulto :code', ['code' => $parcel->code]),
                'html' => view('App.Parcels.partials.edit-modal-content', [
                    'parcel' => $parcel,
                    'providers' => $providers,
                    'couriers' => $couriers,
                    'mapsApiKey' => $mapsApiKey,
                ])->render(),
            ]);
        }

        return view('App.Parcels.edit', [
            'parcel' => $parcel,
            'providers' => $providers,
            'couriers' => $couriers,
            'mapsApiKey' => $mapsApiKey,
        ]);
    }

    public function store(StoreParcelBatchRequest $request): RedirectResponse
    {
        $this->authorize('create', Parcel::class);

        $lines = collect(preg_split('/\r\n|\r|\n/', $request->validated()['codes']))
            ->map(fn ($line) => trim($line))
            ->filter();

        $created = [];
        $skipped = [];

        foreach ($lines as $code) {
            if (Parcel::where('code', $code)->exists()) {
                $skipped[] = $code;
                continue;
            }

            $parcel = Parcel::create([
                'code' => $code,
                'status' => 'pending',
            ]);

            $this->eventRecorder->record($parcel, 'parcel_manual_created', [
                'description' => __('Bulto ingresado manualmente desde carga masiva'),
            ]);

            $created[] = $code;
        }

        return redirect()
            ->route('app.parcels.index')
            ->with('status', 'parcel-created')
            ->with('parcel-created-codes', $created)
            ->with('parcel-skipped-codes', $skipped)
            ->with('parcel-modal-flash', true);
    }

    public function update(UpdateParcelRequest $request, Parcel $parcel): RedirectResponse
    {
        $this->authorize('update', $parcel);

        $parcel->loadMissing('client');

        $data = collect($request->validated())
            ->map(fn ($value) => $value === '' ? null : $value)
            ->all();

        foreach (['latitude', 'longitude'] as $coordinate) {
            if (array_key_exists($coordinate, $data)) {
                $data[$coordinate] = $data[$coordinate] !== null ? (float) $data[$coordinate] : null;
            }
        }

        if (array_key_exists('provider_id', $data) && $data['provider_id'] === null) {
            $data['provider_id'] = null;
        }

        if (array_key_exists('provider_barcode_id', $data) && $data['provider_barcode_id'] === null) {
            $data['provider_barcode_id'] = null;
        }

        if (array_key_exists('courier_id', $data)) {
            $courierId = $data['courier_id'] ? (int) $data['courier_id'] : null;
            $data['courier_id'] = $courierId;

            if ($courierId) {
                if ($parcel->courier_id !== $courierId || ! $parcel->assigned_at) {
                    $data['assigned_at'] = now();
                }
            } else {
                $data['assigned_at'] = null;
            }

            if ($courierId && $parcel->status === 'pending') {
                $data['status'] = 'assigned';
            } elseif (! $courierId && $parcel->courier_id && $parcel->status === 'assigned') {
                $data['status'] = 'pending';
            }
        }

        $manualCoordinatesProvided = isset($data['latitude'], $data['longitude'])
            && $data['latitude'] !== null
            && $data['longitude'] !== null;

        $shouldGeocode = ! $manualCoordinatesProvided && $this->shouldGeocode($data, $parcel);

        $original = $parcel->getOriginal();
        $parcel->fill($data);

        if (array_key_exists('client_id', $data) && $parcel->relationLoaded('client')) {
            if ($parcel->client?->id !== $parcel->client_id) {
                $parcel->unsetRelation('client');
                $parcel->load('client');
            }
        }

        if ($manualCoordinatesProvided) {
            $parcel->latitude = $data['latitude'];
            $parcel->longitude = $data['longitude'];

            if (array_key_exists('formatted_address', $data) && $data['formatted_address']) {
                $parcel->formatted_address = $data['formatted_address'];
            }
        } elseif ($shouldGeocode || ($parcel->latitude === null || $parcel->longitude === null)) {
            $this->applyGeocoding($parcel, $shouldGeocode);
        }

        $dirty = $parcel->getDirty();

        if (empty($dirty)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'unchanged',
                    'message' => __('No se detectaron cambios para guardar.'),
                ]);
            }

            return redirect()
                ->route('app.parcels.edit', $parcel)
                ->with('status', 'parcel-unchanged');
        }

        $changes = collect($dirty)->mapWithKeys(fn ($value, $field) => [
            $field => [
                'old' => $original[$field] ?? null,
                'new' => $value,
            ],
        ]);

        $parcel->save();

        if ($changes->has('courier_id')) {
            $newCourierId = $parcel->courier_id;
            $eventKey = $newCourierId ? 'parcel_assigned_to_courier' : 'parcel_unassigned_from_courier';

            $this->eventRecorder->record($parcel, $eventKey, [
                'description' => $newCourierId
                    ? __('Bulto asignado al courier :courier', ['courier' => optional(optional($parcel->courier)->user)->name ?? __('ID :id', ['id' => $parcel->courier_id])])
                    : __('Asignación retirada del courier anterior'),
                'payload' => [
                    'courier_id' => $parcel->courier_id,
                    'courier_name' => optional(optional($parcel->courier)->user)->name,
                    'assigned_at' => optional($parcel->assigned_at)->toIso8601String(),
                ],
            ]);
        }

        $this->eventRecorder->record($parcel, 'parcel_manual_updated', [
            'description' => __('Bulto actualizado manualmente desde la pantalla de edición.'),
            'payload' => ['changes' => $changes->toArray()],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => __('Los datos del bulto se actualizaron correctamente.'),
            ]);
        }

        return redirect()
            ->route('app.parcels.edit', $parcel)
            ->with('status', 'parcel-updated');
    }

    protected function shouldGeocode(array $data, Parcel $parcel): bool
    {
        foreach (['address_line', 'city', 'state', 'postal_code'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== ($parcel->getOriginal($field) ?? $parcel->{$field})) {
                return true;
            }
        }

        return false;
    }

    protected function applyGeocoding(Parcel $parcel, bool $forceReset = false): void
    {
        $address = $this->formatAddressForGeocoding($parcel);

        if ($address === '') {
            if ($forceReset) {
                $parcel->latitude = null;
                $parcel->longitude = null;
                $parcel->formatted_address = null;
            }

            return;
        }

        $client = $this->clientContext->client() ?? $parcel->client;

        if (! $client) {
            if ($forceReset) {
                $parcel->latitude = null;
                $parcel->longitude = null;
                $parcel->formatted_address = null;
            }

            return;
        }

        $result = $this->geocoder->geocode($client, $address);

        if (! $result) {
            if ($forceReset) {
                $parcel->latitude = null;
                $parcel->longitude = null;
                $parcel->formatted_address = null;
            }

            return;
        }

        $parcel->latitude = $result['latitude'];
        $parcel->longitude = $result['longitude'];

        if (! empty($result['formatted_address'])) {
            $parcel->formatted_address = $result['formatted_address'];
        }
    }

    protected function formatAddressForGeocoding(Parcel $parcel): string
    {
        return collect([
            $parcel->address_line,
            $parcel->city,
            $parcel->state,
            $parcel->postal_code,
        ])->filter()->implode(', ');
    }
}
