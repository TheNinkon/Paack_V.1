<?php

namespace App\Http\Controllers\App\Parcels;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParcelSummaryController extends Controller
{
    public function __invoke(Request $request, string $code): JsonResponse
    {
        $parcel = Parcel::with([
                'provider',
                'providerBarcode',
                'courier.user',
                'latestScan.creator',
                'scans' => fn ($query) => $query->latest()->limit(5)->with(['creator', 'provider']),
                'events' => fn ($query) => $query->latest()->limit(10)->with('scan.creator'),
            ])
            ->withCount('scans')
            ->where('code', $code)
            ->firstOrFail();

        $this->authorize('view', $parcel);

        $html = view('App.Parcels.partials.offcanvas-summary', [
            'parcel' => $parcel,
        ])->render();

        return response()->json([
            'html' => $html,
        ]);
    }
}
