<?php

namespace App\Http\Requests\Admin\Zones;

use App\Models\Zone;
use App\Support\ClientContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\Zone|null $zone */
        $zone = $this->route('zone');

        return $zone ? $this->user()?->can('update', $zone) ?? false : false;
    }

    public function rules(): array
    {
        /** @var Zone|null $zone */
        $zone = $this->route('zone');
        $zoneId = $zone?->id ?? 0;
        $clientId = $this->input('client_id') ?? $zone?->client_id;

        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('zones', 'code')
                    ->where(fn ($query) => $query->where('client_id', $clientId))
                    ->ignore($zoneId),
            ],
            'notes' => ['nullable', 'string'],
            'active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $clientContextId = app(ClientContext::class)->clientId();
        $userClientId = $this->user()?->client_id;
        /** @var Zone|null $zone */
        $zone = $this->route('zone');

        $clientId = $this->input('client_id') ?? $zone?->client_id;

        if (! $clientId) {
            $clientId = $clientContextId ?? $userClientId;
        }

        $this->merge([
            'client_id' => $clientId,
            'active' => $this->boolean('active', $zone?->active ?? true),
        ]);
    }
}
