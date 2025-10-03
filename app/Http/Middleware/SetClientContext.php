<?php

namespace App\Http\Middleware;

use App\Models\Client;
use App\Support\ClientContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class SetClientContext
{
    protected ClientContext $clientContext;

    public function __construct(ClientContext $clientContext)
    {
        $this->clientContext = $clientContext;
    }

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $selectedClientId = $request->session()->get('selected_client_id');
        $clientForContext = null;

        if ($user && $user->client_id) {
            $clientForContext = Client::find($user->client_id);
            $request->session()->put('selected_client_id', $user->client_id);
        } elseif ($user && method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            if ($selectedClientId) {
                $clientForContext = Client::find($selectedClientId);
                if (! $clientForContext) {
                    $request->session()->forget('selected_client_id');
                }
            }
        }

        if ($clientForContext) {
            $this->clientContext->setClient($clientForContext);
        } else {
            $this->clientContext->reset();
        }

        $availableClients = collect();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            $availableClients = Client::orderBy('name')->get(['id', 'name']);
        }

        View::share('currentClient', $this->clientContext->client());
        View::share('availableClients', $availableClients);

        return $next($request);
    }
}
