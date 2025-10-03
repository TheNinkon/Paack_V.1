<?php

namespace App\Http\Requests\App\Parcels;

use Illuminate\Foundation\Http\FormRequest;

class StoreParcelBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('scan.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'codes' => ['required', 'string'],
        ];
    }
}
