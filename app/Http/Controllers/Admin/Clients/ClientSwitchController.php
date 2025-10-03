<?php

namespace App\Http\Controllers\Admin\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientSwitchController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ($user->client_id && ! $user->hasRole('super_admin'))) {
            abort(403);
        }

        $validated = $request->validate([
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
        ]);

        $clientId = $validated['client_id'] ?? null;

        if ($clientId) {
            $request->session()->put('selected_client_id', $clientId);
        } else {
            $request->session()->forget('selected_client_id');
        }

        return back()->with('status', 'client-context-updated');
    }
}
