<?php

namespace App\Http\Requests\Admin\Zones;

use App\Support\ClientContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('zones.manage') ?? false;
    }

    public function rules(): array
    {
        $clientId = $this->input('client_id');

        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('zones', 'code')->where(fn ($query) => $query->where('client_id', $clientId)),
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
            'client_id' => $clientId,
            'active' => $this->boolean('active'),
        ]);
    }
}
