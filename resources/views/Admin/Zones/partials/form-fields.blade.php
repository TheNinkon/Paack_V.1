@php
    $editing = isset($zone);
    $clientOptions = $clients ?? collect();
    $defaultClientId = old('client_id', $zone->client_id ?? ($defaultClientId ?? null));
@endphp

<div class="row g-6">
    @if ($clientOptions->isNotEmpty())
        <div class="col-12 col-md-6">
            <div class="mb-6 form-control-validation">
                <label for="client_id" class="form-label">{{ __('Cliente') }} <span class="text-danger">*</span></label>
                <select id="client_id" name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                    <option value="">{{ __('Selecciona un cliente') }}</option>
                    @foreach ($clientOptions as $client)
                        <option value="{{ $client->id }}" {{ (string) $defaultClientId === (string) $client->id ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
                @error('client_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    @endif

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="name" class="form-label">{{ __('Nombre de la zona') }} <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $zone->name ?? '') }}" required autocomplete="off">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="code" class="form-label">{{ __('Código interno') }}</label>
            <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $zone->code ?? '') }}" maxlength="10" autocomplete="off">
            <small class="text-muted">{{ __('Utiliza un identificador corto (ej. GRO, FIG).') }}</small>
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <div class="mb-6 form-control-validation">
            <label for="notes" class="form-label">{{ __('Notas internas') }}</label>
            <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" placeholder="{{ __('Información adicional sobre cobertura o detalles operativos') }}">{{ old('notes', $zone->notes ?? '') }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="active" name="active" value="1" {{ old('active', $zone->active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="active">{{ __('Zona activa') }}</label>
        </div>
    </div>
</div>
