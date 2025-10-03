<?php

namespace App\Http\Requests\Admin\Providers;

use App\Models\Provider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('providers.manage') ?? false;
    }

    public function rules(): array
    {
        /** @var Provider $provider */
        $provider = $this->route('provider');
        $providerId = $provider instanceof Provider ? $provider->id : $provider;
        $clientId = $provider instanceof Provider ? $provider->client_id : $this->input('client_id');

        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('providers', 'slug')
                    ->ignore($providerId)
                    ->where(fn ($query) => $query->where('client_id', $clientId)),
            ],
            'notes' => ['nullable', 'string'],
            'active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        /** @var Provider|null $provider */
        $provider = $this->route('provider');

        $this->merge([
            'active' => $this->boolean('active'),
            'client_id' => $provider?->client_id ?? $this->input('client_id'),
        ]);

        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => str($this->input('name'))->slug()->toString(),
            ]);
        }
    }
}
