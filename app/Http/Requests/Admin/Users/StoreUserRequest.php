<?php

namespace App\Http\Requests\Admin\Users;

use App\Support\ClientContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Spatie\Permission\Models\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') ?? false;
    }

    public function rules(): array
    {
        $allowedRoles = $this->allowedRoles();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in($allowedRoles)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $clientIdInput = $this->input('client_id');
        $clientId = $clientIdInput !== null && $clientIdInput !== '' ? (int) $clientIdInput : null;

        $contextClient = app(ClientContext::class)->clientId();
        $authUser = $this->user();

        if ($clientId === null) {
            if ($contextClient) {
                $clientId = $contextClient;
            } elseif ($authUser && ! $authUser->hasRole('super_admin')) {
                $clientId = $authUser->client_id;
            }
        }

        $this->merge([
            'client_id' => $clientId,
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $clientId = $this->input('client_id');
            $roles = $this->input('roles', []);

            $hasSuperAdmin = in_array('super_admin', $roles, true);

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
