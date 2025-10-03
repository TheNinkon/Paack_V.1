<?php

namespace App\Http\Controllers\App\Parcels;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParcelCheckController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('create', Parcel::class);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $code = trim($validated['code']);
        $exists = Parcel::where('code', $code)->exists();

        return response()->json([
            'code' => $code,
            'status' => $exists ? 'duplicate' : 'pending',
            'exists' => $exists,
        ]);
    }
}
