<?php

namespace App\Http\Requests\Admin\Users;

use App\Models\User;
use App\Support\ClientContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Spatie\Permission\Models\Role;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->route('user');

        return $this->user()?->can('users.manage') && $user !== null;
    }

    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');
        $allowedRoles = $this->allowedRoles();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in($allowedRoles)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $clientIdInput = $this->input('client_id');
        $clientId = $clientIdInput !== null && $clientIdInput !== '' ? (int) $clientIdInput : null;

        /** @var User $target */
        $target = $this->route('user');
        $contextClient = app(ClientContext::class)->clientId();
        $authUser = $this->user();

        if ($clientId === null) {
            if ($contextClient) {
                $clientId = $contextClient;
            } elseif ($target && $target->client_id !== null) {
                $clientId = $target->client_id;
            } elseif ($authUser && ! $authUser->hasRole('super_admin')) {
                $clientId = $authUser->client_id;
            }
        }

        $this->merge([
            'client_id' => $clientId,
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : ($target->is_active ?? true),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        /** @var User $target */
        $target = $this->route('user');

        $validator->after(function (Validator $validator) use ($target) {
            $clientId = $this->input('client_id');
            $roles = $this->input('roles', []);
            $hasSuperAdmin = in_array('super_admin', $roles, true);

            if ($target->client_id === null) {
                // Mantener usuarios globales como super admin.
                if ($clientId !== null) {
                    $validator->errors()->add('client_id', __('Los usuarios globales no pueden vincularse a un cliente.'));
                }
                if (! $hasSuperAdmin) {
                    $validator->errors()->add('roles', __('Los usuarios globales deben conservar el rol super_admin.'));
                }
                return;
            }

            if ($clientId === null && ! $hasSuperAdmin) {
                $validator->errors()->add('client_id', __('Debes seleccionar un cliente para los roles elegidos.'));
            }

            if ($clientId !== null && $hasSuperAdmin) {
                $validator->errors()->add('roles', __('El rol super_admin solo está disponible para usuarios globales.'));
            }
        });
    }

    protected function allowedRoles(): array
    {
        $roles = Role::query()->where('guard_name', 'web')->pluck('name')->all();

        $authUser = $this->user();
        if ($authUser && ! $authUser->hasRole('super_admin')) {
            $roles = array_values(array_diff($roles, ['super_admin']));
        }

        return $roles;
    }
}
