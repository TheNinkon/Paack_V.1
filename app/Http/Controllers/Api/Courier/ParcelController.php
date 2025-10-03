<?php

namespace App\Http\Controllers\Api\Courier;

use App\Http\Controllers\Controller;
use App\Http\Resources\ParcelResource;
use App\Models\Parcel;
use App\Services\ParcelEventRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParcelController extends Controller
{
    public function __construct(private readonly ParcelEventRecorder $eventRecorder)
    {
        //
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('viewAny', Parcel::class);

        $perPage = (int) min($request->integer('per_page', 50), 100);

        $courier = $user?->courier;

        $query = Parcel::query()
            ->with(['provider', 'courier.user', 'latestScan.creator'])
            ->withCount('scans')
            ->orderByDesc('updated_at');

        if ($user && $user->hasRole('courier')) {
            if (! $courier || ! $courier->active) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('courier_id', $courier->id);
            }
        }

        if ($status = $request->input('status')) {
            $statuses = array_filter(explode(',', $status));
            if ($statuses) {
                $query->whereIn('status', $statuses);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('code', 'like', "%{$search}%")
                    ->orWhere('stop_code', 'like', "%{$search}%")
                    ->orWhere('address_line', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('today_only')) {
            $query->whereDate('updated_at', today());
        }

        $parcels = $query->paginate($perPage);

        return ParcelResource::collection($parcels)->additional([
            'meta' => [
                'filters' => [
                    'status' => $status ? array_values(array_filter(explode(',', $status))) : [],
                    'search' => $search,
                    'today_only' => $request->boolean('today_only'),
                ],
                'per_page' => $perPage,
            ],
        ])->response();
    }

    public function storeEvent(Request $request, Parcel $parcel): JsonResponse
    {
        $this->authorize('update', $parcel);

        $courier = $request->user()?->courier;
        if ($request->user()?->hasRole('courier')) {
            if (! $courier || ! $courier->active || (int) $parcel->courier_id !== (int) $courier->id) {
                abort(403, __('No tienes acceso a este bulto.'));
            }
        }

        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'assigned', 'out_for_delivery', 'delivered', 'incident', 'returned'])],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $originalStatus = $parcel->status;

        if ($originalStatus !== $data['status']) {
            $parcel->status = $data['status'];
            $parcel->save();
        }

        $eventType = $originalStatus !== $data['status'] ? 'parcel_status_updated' : 'parcel_status_confirmed';

        $this->eventRecorder->record($parcel, $eventType, [
            'description' => $data['comment'] ?? null,
            'payload' => [
                'status' => $data['status'],
                'previous_status' => $originalStatus,
                'by' => $request->user()->only(['id', 'name']),
            ],
        ]);

        return response()->json([
            'message' => __('Estado actualizado correctamente.'),
            'parcel' => new ParcelResource($parcel->fresh(['provider', 'courier.user', 'latestScan.creator']))
        ]);
    }
}
