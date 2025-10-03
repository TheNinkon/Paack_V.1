@php
    $editing = isset($provider);
    $clientOptions = $clients ?? collect();
    $defaultClientId = old('client_id', $provider->client_id ?? ($defaultClientId ?? null));
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
            <label for="name" class="form-label">{{ __('Nombre comercial') }} <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $provider->name ?? '') }}" required autocomplete="off">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="slug" class="form-label">{{ __('Slug interno') }}</label>
            <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $provider->slug ?? '') }}" autocomplete="off">
            <small class="text-muted">{{ __('Se utilizará para identificadores y rutas internas.') }}</small>
            @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <div class="mb-6 form-control-validation">
            <label for="notes" class="form-label">{{ __('Notas internas') }}</label>
            <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" placeholder="{{ __('Información adicional sobre el transportista') }}">{{ old('notes', $provider->notes ?? '') }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="active" name="active" value="1" {{ old('active', $provider->active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="active">{{ __('Proveedor activo') }}</label>
        </div>
    </div>
</div>
