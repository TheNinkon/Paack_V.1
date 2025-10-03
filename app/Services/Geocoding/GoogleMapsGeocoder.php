<?php

namespace App\Services\Geocoding;

use App\Models\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleMapsGeocoder
{
    /**
     * @return array{latitude: float, longitude: float, formatted_address?: string}|null
     */
    public function geocode(Client $client, string $address): ?array
    {
        $apiKey = trim((string) $client->google_maps_api_key);

        if ($apiKey === '' || Str::of($address)->trim()->isEmpty()) {
            return null;
        }

        $response = Http::timeout(10)
            ->acceptJson()
            ->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'key' => $apiKey,
                'address' => $address,
                'language' => app()->getLocale(),
            ]);

        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json();

        if (($payload['status'] ?? null) !== 'OK') {
            return null;
        }

        $result = $payload['results'][0] ?? null;

        if (! $result || empty($result['geometry']['location'])) {
            return null;
        }

        $location = $result['geometry']['location'];

        return [
            'latitude' => (float) ($location['lat'] ?? 0.0),
            'longitude' => (float) ($location['lng'] ?? 0.0),
            'formatted_address' => $result['formatted_address'] ?? null,
        ];
    }
}
