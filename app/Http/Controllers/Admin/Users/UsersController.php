<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\StoreUserRequest;
use App\Http\Requests\Admin\Users\UpdateUserRequest;
use App\Models\Client;
use App\Models\User;
use App\Support\ClientContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    protected ClientContext $clientContext;
    protected string $routePrefix = 'admin.users.';

    public function __construct(ClientContext $clientContext)
    {
        $this->clientContext = $clientContext;
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request): View
    {
        $query = $this->filteredUsersQuery()->with(['client', 'roles'])->orderBy('name');

        $users = (clone $query)->paginate(12)->withQueryString();

        $statsBase = $this->filteredUsersQuery();
        $stats = [
            'total' => (clone $statsBase)->count(),
            'active' => (clone $statsBase)->where('is_active', true)->count(),
            'inactive' => (clone $statsBase)->where('is_active', false)->count(),
            'verified' => (clone $statsBase)->whereNotNull('email_verified_at')->count(),
        ];

        return view('Admin.Users.index', [
            'users' => $users,
            'stats' => $stats,
            'currentClient' => $this->clientContext->client(),
            'clients' => $this->clientOptions(),
            'roles' => $this->availableRoles(),
            'defaultClientId' => $this->defaultClientId(),
            'routePrefix' => $this->routePrefix,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route($this->routeName('index'))->with('openCreateUser', true);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $roles = $data['roles'];

        unset($data['roles']);

        $user = User::create(array_merge($data, [
            'remember_token' => Str::random(10),
            'email_verified_at' => now(),
        ]));

        $user->syncRoles($roles);

        return redirect()
            ->route($this->routeName('index'))
            ->with('status', 'user-created');
    }

    public function edit(User $user): View
    {
        $user->load('roles');

        return view('Admin.Users.edit', [
            'user' => $user,
            'clients' => $this->clientOptions(),
            'roles' => $this->availableRoles(),
            'defaultClientId' => $this->defaultClientId($user->client_id),
            'routePrefix' => $this->routePrefix,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $roles = $data['roles'];

        unset($data['roles']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->fill($data)->save();

        $user->syncRoles($roles);

        return redirect()
            ->route($this->routeName('edit'), $user)
            ->with('status', 'user-updated');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()
            ->route($this->routeName('index'))
            ->with('status', 'user-deleted');
    }

    protected function filteredUsersQuery()
    {
        $query = User::query();
        $authUser = auth()->user();
        $contextClientId = $this->clientContext->clientId();

        if ($contextClientId) {
            $query->where('client_id', $contextClientId);
        } elseif ($authUser && ! $authUser->hasRole('super_admin') && $authUser->client_id) {
            $query->where('client_id', $authUser->client_id);
        }

        if ($authUser && ! $authUser->hasRole('super_admin')) {
            $query->whereNotNull('client_id');
        }

        return $query;
    }

    protected function clientOptions()
    {
        $authUser = auth()->user();

        if ($authUser && $authUser->hasRole('super_admin')) {
            return Client::orderBy('name')->get(['id', 'name']);
        }

        return collect();
    }

    protected function defaultClientId(?int $fallback = null): ?int
    {
        return $this->clientContext->clientId()
            ?? $fallback
            ?? auth()->user()?->client_id;
    }

    protected function availableRoles()
    {
        $authUser = auth()->user();

        $roles = Role::query()->where('guard_name', 'web')->orderBy('name')->pluck('name');

        if ($authUser && ! $authUser->hasRole('super_admin')) {
            $roles = $roles->reject(fn ($role) => $role === 'super_admin');
        }

        return $roles->values();
    }

    protected function routeName(string $suffix): string
    {
        return $this->routePrefix . $suffix;
    }
}
