<?php

namespace App\Http\Controllers\Admin\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Clients\StoreClientRequest;
use App\Http\Requests\Admin\Clients\UpdateClientRequest;
use App\Models\Client;
use App\Models\User;
use App\Support\ClientContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClientsController extends Controller
{
    protected ClientContext $clientContext;

    public function __construct(ClientContext $clientContext)
    {
        $this->clientContext = $clientContext;
        $this->authorizeResource(Client::class, 'client');
    }

    public function index(): View
    {
        $clientId = $this->clientContext->clientId();
        $clientsQuery = Client::with(['creator', 'updater'])->orderBy('name');

        if ($clientId) {
            $clientsQuery->where('id', $clientId);
        }

        $clients = $clientsQuery->paginate(12);

        if ($clientId) {
            $client = $this->clientContext->client();
            $stats = [
                'total' => $clients->total(),
                'active' => $client && $client->active ? 1 : 0,
                'inactive' => $client && ! $client->active ? 1 : 0,
                'with_contact' => $client && ($client->contact_email || $client->contact_phone) ? 1 : 0,
            ];
        } else {
            $stats = [
                'total' => Client::count(),
                'active' => Client::where('active', true)->count(),
                'inactive' => Client::where('active', false)->count(),
                'with_contact' => Client::where(function ($query) {
                    $query->whereNotNull('contact_email')
                        ->orWhereNotNull('contact_phone');
                })->count(),
            ];
        }

        return view('Admin.Clients.index', compact('clients', 'stats'));
    }

    public function create(): View
    {
        return view('Admin.Clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $clientData = $request->safe()->only([
            'name',
            'cif',
            'contact_name',
            'contact_email',
            'contact_phone',
            'active',
        ]);

        $status = 'client-created';

        DB::transaction(function () use ($request, $clientData, &$status) {
            $client = Client::create($clientData);

            if ($request->boolean('create_admin')) {
                $user = User::create([
                    'name' => $request->input('admin_name'),
                    'email' => $request->input('admin_email'),
                    'phone' => $request->input('admin_phone'),
                    'password' => Hash::make($request->input('admin_password')),
                    'client_id' => $client->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'remember_token' => Str::random(10),
                ]);

                $user->assignRole('client_admin');

                $status = 'client-created-with-admin';
            }
        });

        return redirect()
            ->route('admin.clients.index')
            ->with('status', $status);
    }

    public function edit(Client $client): View
    {
        $client->load(['creator', 'updater']);

        return view('Admin.Clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return redirect()
            ->route('admin.clients.index')
            ->with('status', 'client-updated');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()
            ->route('admin.clients.index')
            ->with('status', 'client-deleted');
    }
}
