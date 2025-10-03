<?php

namespace App\Http\Controllers\App\Parcels;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Models\ParcelEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParcelHistoryController extends Controller
{
    public function __invoke(Request $request, string $code): View
    {
        $parcel = Parcel::with(['provider', 'providerBarcode'])
            ->where('code', $code)
            ->firstOrFail();

        $this->authorize('view', $parcel);

        $lastScan = $parcel->scans()
            ->with(['provider', 'providerBarcode', 'creator'])
            ->latest()
            ->first();

        $events = ParcelEvent::where('parcel_id', $parcel->id)
            ->with(['scan.creator'])
            ->orderBy('created_at')
            ->get();

        return view('App.Parcels.show', [
            'parcel' => $parcel,
            'lastScan' => $lastScan,
            'events' => $events,
        ]);
    }
}
