<?php

namespace App\Http\Controllers\App\Parcels;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Services\ParcelEventRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ParcelStateController extends Controller
{
    public function kill(Request $request, Parcel $parcel, ParcelEventRecorder $eventRecorder): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $parcel);

        $parcel->update(['status' => 'returned']);

        $eventRecorder->record($parcel, 'parcel_returned', [
            'description' => __('Bulto marcado como retornado (matado) desde el panel'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'parcel' => $parcel->fresh(['provider'])->only(['id', 'code', 'status']),
            ]);
        }

        return redirect()->back()->with('status', 'parcel-returned');
    }
}
