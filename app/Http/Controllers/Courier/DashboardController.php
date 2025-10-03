<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Support\ClientContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Parcel::class);

        $user = $request->user();
        $courier = $user?->loadMissing('courier.client')->courier;

        $contextClient = app(ClientContext::class)->client();
        $client = $courier?->client ?? $user?->loadMissing('client')->client ?? $contextClient;
        $allowedStatuses = ['pending', 'assigned', 'out_for_delivery', 'delivered', 'incident', 'returned'];
        $statusFilter = $this->normalizeStatusFilter($request->input('status'), $allowedStatuses);
        $search = trim((string) $request->input('search'));

        $canScan = $this->hasBarcodeDetectorSupport();

        $activeParcels = collect();
        $completedParcels = collect();
        $statusCounts = collect();

        if ($courier && $courier->active) {
            $activeParcels = Parcel::query()
                ->with(['provider'])
                ->where('courier_id', $courier->id)
                ->whereIn('status', $statusFilter ?: ['assigned', 'out_for_delivery'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($builder) use ($search) {
                        $builder->where('code', 'like', "%{$search}%")
                            ->orWhere('stop_code', 'like', "%{$search}%")
                            ->orWhere('address_line', 'like', "%{$search}%");
                    });
                })
                ->latest('updated_at')
                ->take(50)
                ->get();

            $completedParcels = Parcel::query()
                ->with(['provider'])
                ->where('courier_id', $courier->id)
                ->whereIn('status', ['delivered', 'incident', 'returned'])
                ->latest('updated_at')
                ->take(20)
                ->get();

            $statusCounts = Parcel::query()
                ->selectRaw('status, COUNT(*) as total')
                ->where('courier_id', $courier->id)
                ->whereIn('status', ['assigned', 'out_for_delivery', 'delivered', 'incident'])
                ->groupBy('status')
                ->pluck('total', 'status');
        }

        $filters = [
            'status' => $statusFilter,
            'search' => $search,
        ];

        $mapsApiKey = $client ? trim((string) $client->google_maps_api_key) : null;

        $meta = [
            'canUseBarcodeDetector' => $canScan,
            'mapsApiKey' => $mapsApiKey,
        ];

        $initialState = [
            'filters' => $filters,
            'meta' => array_merge($meta, [
                'courier_id' => $courier?->id,
                'courier_active' => (bool) ($courier?->active),
            ]),
            'routes' => [
                'dashboard' => route('courier.dashboard'),
                'logout' => route('logout'),
            ],
            'parcels' => [
                'active' => $activeParcels->map(fn ($parcel) => [
                    'id' => $parcel->id,
                    'code' => $parcel->code,
                    'status' => $parcel->status,
                    'stop_code' => $parcel->stop_code,
                    'address_line' => $parcel->address_line,
                    'city' => $parcel->city,
                    'provider' => $parcel->provider?->name,
                    'latitude' => $parcel->latitude,
                    'longitude' => $parcel->longitude,
                    'formatted_address' => $parcel->formatted_address,
                ])->values()->all(),
                'completed' => $completedParcels->map(fn ($parcel) => [
                    'id' => $parcel->id,
                    'code' => $parcel->code,
                    'status' => $parcel->status,
                    'address_line' => $parcel->address_line,
                    'latitude' => $parcel->latitude,
                    'longitude' => $parcel->longitude,
                    'formatted_address' => $parcel->formatted_address,
                ])->values()->all(),
            ],
        ];

        return view('courier.dashboard', [
            'courier' => $courier,
            'activeParcels' => $activeParcels,
            'completedParcels' => $completedParcels,
            'filters' => $filters,
            'counts' => [
                'assigned' => (int) $statusCounts->get('assigned', 0),
                'out_for_delivery' => (int) $statusCounts->get('out_for_delivery', 0),
                'delivered' => (int) $statusCounts->get('delivered', 0),
                'incident' => (int) $statusCounts->get('incident', 0),
            ],
            'meta' => $meta,
            'initialState' => $initialState,
        ]);
    }


    /**
     * @param  array<int, string>|string|null  $input
     * @param  array<int, string>  $allowed
     * @return array<int, string>
     */
    protected function normalizeStatusFilter($input, array $allowed): array
    {
        $raw = collect(Arr::wrap($input))->flatMap(function ($value) {
            if (is_string($value)) {
                return array_filter(explode(',', $value));
            }

            return [];
        })->map(fn ($value) => strtolower(trim($value)))->all();

        $filtered = array_values(array_unique(array_intersect($raw, $allowed)));

        return $filtered;
    }

    protected function hasBarcodeDetectorSupport(): bool
    {
        $userAgent = request()->header('User-Agent', '');

        return str_contains($userAgent, 'Chrome') || str_contains($userAgent, 'Edg') || str_contains($userAgent, 'Firefox');
    }
}
