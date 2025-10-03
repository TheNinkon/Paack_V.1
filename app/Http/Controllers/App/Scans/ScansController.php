<?php

namespace App\Http\Controllers\App\Scans;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\Scans\StoreScanRequest;
use App\Models\Parcel;
use App\Models\ProviderBarcode;
use App\Models\Scan;
use App\Services\BarcodeMatcher;
use App\Services\ParcelEventRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScansController extends Controller
{
    public function __construct(
        private readonly BarcodeMatcher $matcher,
        private readonly ParcelEventRecorder $eventRecorder
    )
    {
        $this->middleware('can:scan.create')->only('store');
    }

    public function index(): View
    {
        $this->authorize('viewAny', Scan::class);

        $recentScans = Scan::with(['provider', 'providerBarcode', 'creator', 'parcel'])
            ->latest()
            ->limit(50)
            ->get();

        $feedback = session('scan_feedback');

        return view('App.Scans.index', [
            'recentScans' => $recentScans,
            'feedback' => $feedback,
        ]);
    }

    public function store(StoreScanRequest $request)
    {
        $this->authorize('create', Scan::class);

        $code = trim($request->input('code'));
        $barcode = $this->matcher->match($code);

        $parcel = Parcel::firstWhere('code', $code);
        $isNewParcel = false;

        if (! $parcel) {
            $parcel = Parcel::create([
                'code' => $code,
                'provider_id' => $barcode?->provider_id,
                'provider_barcode_id' => $barcode?->id,
            ]);
            $isNewParcel = true;
        } elseif ($barcode) {
            $parcel->fill([
                'provider_id' => $parcel->provider_id ?: $barcode->provider_id,
                'provider_barcode_id' => $parcel->provider_barcode_id ?: $barcode->id,
            ])->save();
        }

        $scan = Scan::create([
            'parcel_id' => $parcel->id,
            'code' => $code,
            'provider_id' => $barcode?->provider_id,
            'provider_barcode_id' => $barcode?->id,
            'is_valid' => (bool) $barcode,
            'context' => $this->buildContext($barcode),
        ]);

        if ($isNewParcel) {
            $this->eventRecorder->record($parcel, 'parcel_created', [
                'scan' => $scan,
                'description' => __('Bulto creado desde el primer escaneo'),
                'payload' => [
                    'provider_id' => $barcode?->provider_id,
                    'provider_barcode_id' => $barcode?->id,
                ],
            ]);
        }

        $this->eventRecorder->record($parcel, $barcode ? 'scan_matched' : 'scan_unmatched', [
            'scan' => $scan,
            'description' => $barcode
                ? __('Coincidencia con :provider', ['provider' => $barcode->provider?->name ?? 'N/A'])
                : __('Escaneo sin coincidencia'),
            'payload' => [
                'provider_id' => $barcode?->provider_id,
                'provider_barcode_id' => $barcode?->id,
                'pattern_label' => $barcode?->label,
            ],
        ]);

        $feedback = [
            'status' => $barcode ? 'matched' : 'unmatched',
            'scan_id' => $scan->id,
            'code' => $code,
            'provider_name' => $barcode?->provider?->name,
            'pattern_label' => $barcode?->label,
            'parcel_status' => $parcel->status,
        ];

        $alerts = [];

        if (! $barcode) {
            $alerts[] = [
                'type' => 'warning',
                'message' => __('No se encontró un patrón asociado para este código.'),
            ];
        }

        if ($parcel->wasRecentlyCreated) {
            $alerts[] = [
                'type' => 'info',
                'message' => __('Se creó el bulto en el sistema.'),
            ];
        }

        if ($request->expectsJson()) {
            return response()->json([
                'feedback' => $feedback,
                'scan' => $scan->load(['provider', 'providerBarcode', 'creator']),
                'parcel' => $parcel->fresh(['provider', 'providerBarcode'])->loadCount('scans'),
                'alerts' => $alerts,
            ]);
        }

        return redirect()
            ->route('app.scans.index')
            ->with('scan_feedback', $feedback)
            ->with('scan_alerts', $alerts);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function buildContext(?ProviderBarcode $barcode): ?array
    {
        if (! $barcode) {
            return null;
        }

        return [
            'pattern' => $barcode->pattern_regex,
            'label' => $barcode->label,
            'priority' => $barcode->priority,
        ];
    }
}
