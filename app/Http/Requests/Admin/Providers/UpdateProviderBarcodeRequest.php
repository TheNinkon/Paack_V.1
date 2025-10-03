<?php

namespace App\Http\Requests\Admin\Providers;

use App\Models\ProviderBarcode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProviderBarcodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $barcode = $this->route('barcode');

        if (! $user || ! $barcode instanceof ProviderBarcode) {
            return false;
        }

        if (! $user->can('barcodes.manage')) {
            return false;
        }

        $provider = $barcode->provider;

        return $user->hasRole('super_admin') || ($user->client_id !== null && $provider && $user->client_id === $provider->client_id);
    }

    public function rules(): array
    {
        /** @var ProviderBarcode $barcode */
        $barcode = $this->route('barcode');
        $providerId = $barcode->provider_id;

        return [
            'label' => [
                'required',
                'string',
                'max:255',
                Rule::unique('provider_barcodes', 'label')
                    ->ignore($barcode->id)
                    ->where(fn ($query) => $query->where('provider_id', $providerId)),
            ],
            'pattern_regex' => ['required', 'string', 'max:255'],
            'sample_code' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:999'],
            'active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'active' => $this->boolean('active'),
            'priority' => $this->filled('priority') ? (int) $this->input('priority') : 100,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('pattern_regex')) {
                return;
            }

            $pattern = (string) $this->input('pattern_regex');
            $sample = (string) $this->input('sample_code', '');

            if (! $this->isValidRegex($pattern)) {
                $validator->errors()->add('pattern_regex', __('El patrón proporcionado no es una expresión regular válida.'));
                return;
            }

            if ($sample !== '' && ! $this->matchesRegex($pattern, $sample)) {
                $validator->errors()->add('sample_code', __('La muestra no coincide con la expresión regular.'));
            }
        });
    }

    protected function isValidRegex(string $pattern): bool
    {
        $delimited = '/' . str_replace('/', '\/', $pattern) . '/u';

        set_error_handler(static function () {
            // Silencia los avisos de preg_match.
        });
        $result = @preg_match($delimited, '');
        restore_error_handler();

        return $result !== false;
    }

    protected function matchesRegex(string $pattern, string $sample): bool
    {
        $delimited = '/' . str_replace('/', '\/', $pattern) . '/u';

        set_error_handler(static function () {
            // Silencia los avisos de preg_match.
        });
        $result = @preg_match($delimited, $sample);
        restore_error_handler();

        return $result === 1;
    }
}
