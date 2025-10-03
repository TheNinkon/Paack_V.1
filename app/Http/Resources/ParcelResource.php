<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Parcel
 */
class ParcelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'provider' => $this->provider ? [
                'id' => $this->provider->id,
                'name' => $this->provider->name,
            ] : null,
            'courier_id' => $this->courier_id,
            'courier' => $this->courier ? [
                'id' => $this->courier->id,
                'name' => $this->courier->user?->name,
                'vehicle_type' => $this->courier->vehicle_type,
            ] : null,
            'assigned_at' => optional($this->assigned_at)->toIso8601String(),
            'stop_code' => $this->stop_code,
            'address_line' => $this->address_line,
            'formatted_address' => $this->formatted_address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'liquidation_code' => $this->liquidation_code,
            'liquidation_reference' => $this->liquidation_reference,
            'latest_scan_at' => optional($this->latestScan?->created_at)->toIso8601String(),
            'latest_scan_by' => $this->latestScan?->creator?->only(['id', 'name']),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
