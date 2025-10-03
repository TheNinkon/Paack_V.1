@php
    $selectedProvider = old('provider_id', $parcel->provider_id);
    $selectedBarcode = old('provider_barcode_id', $parcel->provider_barcode_id);
    $selectedCourier = old('courier_id', $parcel->courier_id);
@endphp

<div class="row g-4" data-provider-barcode-scope>
    <div class="col-12 col-md-6">
        <label class="form-label" for="provider_id">{{ __('Proveedor') }}</label>
        <select class="form-select" name="provider_id" id="provider_id">
            <option value="">{{ __('Sin proveedor') }}</option>
            @foreach ($providers as $provider)
                <option value="{{ $provider->id }}" @selected((string) $selectedProvider === (string) $provider->id)>
                    {{ $provider->name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">{{ __('Opcional, referencia al operador que entrega el bulto.') }}</small>
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label" for="provider_barcode_id">{{ __('Patrón de código (regex)') }}</label>
        <select class="form-select" name="provider_barcode_id" id="provider_barcode_id">
            <option value="">{{ __('Sin patrón asociado') }}</option>
            @foreach ($providers as $provider)
                @foreach ($provider->barcodes as $barcode)
                    <option
                        value="{{ $barcode->id }}"
                        data-provider="{{ $provider->id }}"
                        @selected((string) $selectedBarcode === (string) $barcode->id)
                    >
                        {{ $barcode->label }}
                    </option>
                @endforeach
            @endforeach
        </select>
        <small class="text-muted">{{ __('Selecciona el patrón aplicable para validar escaneos futuros.') }}</small>
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label" for="courier_id">{{ __('Courier asignado') }}</label>
        <select class="form-select" name="courier_id" id="courier_id">
            <option value="">{{ __('Sin asignar') }}</option>
            @foreach ($couriers as $courier)
                <option value="{{ $courier->id }}" @selected((string) $selectedCourier === (string) $courier->id)>
                    {{ $courier->user?->name ?? __('Courier #:id', ['id' => $courier->id]) }}
                    @if ($courier->vehicle_type)
                        — {{ \Illuminate\Support\Str::headline($courier->vehicle_type) }}
                    @endif
                    @if ($courier->external_code)
                        ({{ $courier->external_code }})
                    @endif
                    @unless ($courier->active)
                        · {{ __('Inactivo') }}
                    @endunless
                </option>
            @endforeach
        </select>
        <small class="text-muted">{{ __('Al asignar un courier, el bulto pasará a estado "Asignado" si aún estaba pendiente.') }}</small>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label" for="stop_code">{{ __('Código de parada') }}</label>
        <input type="text" class="form-control" id="stop_code" name="stop_code" value="{{ old('stop_code', $parcel->stop_code) }}" maxlength="191">
    </div>

    <div class="col-12">
        <label class="form-label" for="address_line">{{ __('Dirección (línea principal)') }}</label>
        <input type="text" class="form-control" id="address_line" name="address_line" value="{{ old('address_line', $parcel->address_line) }}" maxlength="255">
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label" for="city">{{ __('Ciudad') }}</label>
        <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $parcel->city) }}" maxlength="120">
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label" for="state">{{ __('Provincia / Estado') }}</label>
        <input type="text" class="form-control" id="state" name="state" value="{{ old('state', $parcel->state) }}" maxlength="120">
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label" for="postal_code">{{ __('Código postal') }}</label>
        <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code', $parcel->postal_code) }}" maxlength="30">
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label">{{ __('Latitud geocodificada') }}</label>
        <input type="hidden" name="latitude" id="parcel-latitude" value="{{ old('latitude', $parcel->latitude) }}">
        <input type="text" class="form-control" value="{{ $parcel->latitude !== null ? number_format($parcel->latitude, 7) : '' }}" readonly placeholder="—" data-latitude-display>
        <small class="text-muted">{{ __('Se recalcula automáticamente al guardar la dirección.') }}</small>
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label">{{ __('Longitud geocodificada') }}</label>
        <input type="hidden" name="longitude" id="parcel-longitude" value="{{ old('longitude', $parcel->longitude) }}">
        <input type="text" class="form-control" value="{{ $parcel->longitude !== null ? number_format($parcel->longitude, 7) : '' }}" readonly placeholder="—" data-longitude-display>
    </div>

    <div class="col-12">
        <label class="form-label">{{ __('Dirección normalizada') }}</label>
        <input type="hidden" name="formatted_address" id="parcel-formatted-address" value="{{ old('formatted_address', $parcel->formatted_address) }}">
        <input type="text" class="form-control" value="{{ $parcel->formatted_address }}" readonly placeholder="—" data-formatted-address-display>
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label" for="liquidation_code">{{ __('Código de liquidación') }}</label>
        <input type="text" class="form-control" id="liquidation_code" name="liquidation_code" value="{{ old('liquidation_code', $parcel->liquidation_code) }}" maxlength="120">
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label" for="liquidation_reference">{{ __('Referencia de liquidación') }}</label>
        <input type="text" class="form-control" id="liquidation_reference" name="liquidation_reference" value="{{ old('liquidation_reference', $parcel->liquidation_reference) }}" maxlength="150">
    </div>
</div>
