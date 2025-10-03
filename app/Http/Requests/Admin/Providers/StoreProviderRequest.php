<?php

namespace App\Http\Requests\Admin\Providers;

use App\Support\ClientContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('providers.manage') ?? false;
    }

    public function rules(): array
    {
        $clientId = $this->input('client_id');

        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('providers', 'slug')->where(fn ($query) => $query->where('client_id', $clientId)),
            ],
            'notes' => ['nullable', 'string'],
            'active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $clientContextId = app(ClientContext::class)->clientId();
        $userClientId = $this->user()?->client_id;

        $clientId = $this->input('client_id');

        if (! $clientId) {
            $clientId = $clientContextId ?? $userClientId;
        }

        $this->merge([
            'active' => $this->boolean('active'),
            'client_id' => $clientId,
        ]);

        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => str($this->input('name'))->slug()->toString(),
            ]);
        }
    }
}
