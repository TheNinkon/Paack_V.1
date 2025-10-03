<?php

namespace App\Services;

use App\Models\Parcel;
use App\Models\ParcelEvent;
use App\Models\Scan;

class ParcelEventRecorder
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(Parcel $parcel, string $eventType, array $context = []): ParcelEvent
    {
        $scan = $context['scan'] ?? null;

        return ParcelEvent::create([
            'parcel_id' => $parcel->id,
            'scan_id' => $scan instanceof Scan ? $scan->id : ($context['scan_id'] ?? null),
            'code' => $parcel->code,
            'event_type' => $eventType,
            'description' => $context['description'] ?? null,
            'payload' => $context['payload'] ?? null,
        ]);
    }
}
