<?php

namespace App\Http\Requests\App\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMapSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->hasRole('client_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'google_maps_api_key' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('google_maps_api_key')) {
            $this->merge([
                'google_maps_api_key' => trim((string) $this->input('google_maps_api_key')),
            ]);
        }
    }
}
