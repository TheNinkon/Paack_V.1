<?php

namespace App\Http\Requests\Admin\Couriers;

use App\Models\Courier;
use App\Support\ClientContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Courier|null $courier */
        $courier = $this->route('courier');

        return $courier ? $this->user()?->can('update', $courier) ?? false : false;
    }

    public function rules(): array
    {
        /** @var Courier|null $courier */
        $courier = $this->route('courier');
        $courierId = $courier?->id ?? 0;
        $clientId = $this->input('client_id') ?? $courier?->client_id;

        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('client_id', $clientId)),
                Rule::unique('couriers', 'user_id')
                    ->where(fn ($query) => $query->where('client_id', $clientId))
                    ->ignore($courierId),
            ],
            'vehicle_type' => ['required', 'string', Rule::in(Courier::VEHICLE_TYPES)],
            'external_code' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
            'zone_id' => [
                'nullable',
                'integer',
                Rule::exists('zones', 'id')
                    ->where(fn ($query) => $query->where('client_id', $clientId)),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        /** @var Courier|null $courier */
        $courier = $this->route('courier');
        $clientContextId = app(ClientContext::class)->clientId();
        $userClientId = $this->user()?->client_id;

        $clientId = $this->input('client_id') ?? $courier?->client_id;

        if (! $clientId) {
            $clientId = $clientContextId ?? $userClientId;
        }

        $this->merge([
            'client_id' => $clientId,
            'active' => $this->boolean('active', $courier?->active ?? true),
        ]);
    }
}
