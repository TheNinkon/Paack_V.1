<?php

namespace App\Http\Requests\Admin\Clients;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('clients.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'active' => $this->boolean('active'),
            'create_admin' => $this->boolean('create_admin'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'cif' => ['nullable', 'string', 'max:50', Rule::unique('clients', 'cif')],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'active' => ['boolean'],
            'create_admin' => ['boolean'],
            'admin_name' => ['nullable', 'string', 'max:255'],
            'admin_email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'admin_phone' => ['nullable', 'string', 'max:50'],
            'admin_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->boolean('create_admin')) {
                return;
            }

            foreach (['admin_name', 'admin_email', 'admin_password'] as $field) {
                if (! $this->filled($field)) {
                    $validator->errors()->add($field, __('Este campo es obligatorio.'));
                }
            }
        });
    }
}
