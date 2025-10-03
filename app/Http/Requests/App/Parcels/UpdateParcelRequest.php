<?php

namespace App\Http\Requests\App\Parcels;

use App\Models\Parcel;
use App\Models\ProviderBarcode;
use App\Support\ClientContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateParcelRequest extends FormRequest
{
    public function authorize(): bool
    {
        $parcel = $this->route('parcel');

        return $parcel instanceof Parcel
            ? ($this->user()?->can('update', $parcel) ?? false)
            : false;
    }

    protected function prepareForValidation(): void
    {
        foreach (['latitude', 'longitude'] as $field) {
            if ($this->filled($field)) {
                $this->merge([$field => str_replace(',', '.', (string) $this->input($field))]);
            } elseif ($this->has($field)) {
                $this->merge([$field => null]);
            }
        }

        if ($this->has('formatted_address')) {
            $this->merge(['formatted_address' => trim((string) $this->input('formatted_address')) ?: null]);
        }
    }

    public function rules(): array
    {
        $clientId = app(ClientContext::class)->clientId();

        $providerRule = Rule::exists('providers', 'id');
        if ($clientId) {
            $providerRule = $providerRule->where(fn ($query) => $query->where('client_id', $clientId));
        }

        $barcodeRule = Rule::exists('provider_barcodes', 'id');
        if ($clientId) {
            $barcodeRule = $barcodeRule->where(fn ($query) => $query->whereIn('provider_id', function ($subQuery) use ($clientId) {
                $subQuery->select('id')->from('providers')->where('client_id', $clientId);
            }));
        }

        $courierRule = Rule::exists('couriers', 'id');
        if ($clientId) {
            $courierRule = $courierRule->where(fn ($query) => $query->where('client_id', $clientId));
        }

        return [
            'provider_id' => ['nullable', 'integer', $providerRule],
            'provider_barcode_id' => ['nullable', 'integer', $barcodeRule],
            'courier_id' => ['nullable', 'integer', $courierRule],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'formatted_address' => ['nullable', 'string', 'max:255'],
            'stop_code' => ['nullable', 'string', 'max:191'],
            'address_line' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'liquidation_code' => ['nullable', 'string', 'max:120'],
            'liquidation_reference' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $providerId = $this->input('provider_id');
            $barcodeId = $this->input('provider_barcode_id');

            if ($barcodeId) {
                $barcode = ProviderBarcode::query()->with('provider:id,client_id')
                    ->find($barcodeId);

                if (! $barcode) {
                    return;
                }

                if ($providerId && (int) $barcode->provider_id !== (int) $providerId) {
                    $validator->errors()->add('provider_barcode_id', __('El patrón seleccionado no pertenece al proveedor elegido.'));
                }
            }
        });
    }
}
