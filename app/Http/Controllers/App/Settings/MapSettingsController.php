<?php

namespace App\Http\Controllers\App\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\Settings\UpdateMapSettingsRequest;
use App\Support\ClientContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MapSettingsController extends Controller
{
    public function edit(ClientContext $clientContext): View
    {
        $client = $clientContext->client();

        abort_unless($client, 404);

        return view('App.Settings.maps', [
            'client' => $client,
        ]);
    }

    public function update(UpdateMapSettingsRequest $request, ClientContext $clientContext): RedirectResponse
    {
        $client = $clientContext->client();

        abort_unless($client, 404);

        $payload = $request->validated();

        if ($payload['google_maps_api_key'] === '') {
            $payload['google_maps_api_key'] = null;
        }

        $client->update($payload);

        return redirect()
            ->route('app.settings.maps.edit')
            ->with('status', 'maps-settings-updated');
    }
}
