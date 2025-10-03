@php
    use Illuminate\Support\Str;

    $editing = isset($courier);
    $clientOptions = $clients ?? collect();
    $defaultClientId = old('client_id', $courier->client_id ?? ($defaultClientId ?? null));
    $selectedUserId = old('user_id', $courier->user_id ?? null);
    $availableUsers = $availableUsers ?? collect();
    $availableUsersMap = collect($availableUsersMap ?? []);
    $availableZones = $availableZones ?? collect();
    $availableZonesMap = collect($availableZonesMap ?? []);
    $selectedZoneId = old('zone_id', $courier->zone_id ?? null);
    $vehicleTypes = $vehicleTypes ?? [];
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
                <small class="text-muted d-block mt-1">{{ __('El listado de usuarios se limita al cliente seleccionado.') }}</small>
            </div>
        </div>
    @endif

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="user_id" class="form-label">{{ __('Usuario asociado') }} <span class="text-danger">*</span></label>
            <select
                id="user_id"
                name="user_id"
                class="form-select @error('user_id') is-invalid @enderror"
                data-users-map='@json($availableUsersMap)'
                data-default-client-id="{{ $defaultClientId }}"
                required
            >
                <option value="">{{ __('Selecciona un usuario') }}</option>
                @foreach ($availableUsers as $userOption)
                    <option value="{{ $userOption->id }}" {{ (string) $selectedUserId === (string) $userOption->id ? 'selected' : '' }}>
                        {{ $userOption->name }} — {{ $userOption->email }}
                    </option>
                @endforeach
            </select>
            @error('user_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if ($availableUsers->isEmpty())
                <small class="text-muted d-block mt-1">{{ __('No hay usuarios disponibles. Crea primero el usuario y asígnale el cliente correspondiente.') }}</small>
            @endif
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="zone_id" class="form-label">{{ __('Zona principal') }}</label>
            <select
                id="zone_id"
                name="zone_id"
                class="form-select @error('zone_id') is-invalid @enderror"
                data-zones-map='@json($availableZonesMap)'
                data-default-client-id="{{ $defaultClientId }}"
            >
                <option value="">{{ __('Selecciona una zona (opcional)') }}</option>
                @foreach ($availableZones as $zoneOption)
                    <option value="{{ $zoneOption->id }}" {{ (string) $selectedZoneId === (string) $zoneOption->id ? 'selected' : '' }}>
                        {{ $zoneOption->name }}
                    </option>
                @endforeach
            </select>
            @error('zone_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">{{ __('Define la zona predeterminada para asignaciones y reportes.') }}</small>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="vehicle_type" class="form-label">{{ __('Tipo de vehículo') }} <span class="text-danger">*</span></label>
            <select id="vehicle_type" name="vehicle_type" class="form-select @error('vehicle_type') is-invalid @enderror" required>
                <option value="">{{ __('Selecciona un tipo') }}</option>
                @foreach ($vehicleTypes as $type)
                    <option value="{{ $type }}" {{ old('vehicle_type', $courier->vehicle_type ?? '') === $type ? 'selected' : '' }}>
                        {{ Str::headline($type) }}
                    </option>
                @endforeach
            </select>
            @error('vehicle_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="external_code" class="form-label">{{ __('Código externo') }}</label>
            <input type="text" id="external_code" name="external_code" class="form-control @error('external_code') is-invalid @enderror" value="{{ old('external_code', $courier->external_code ?? '') }}" maxlength="255" autocomplete="off">
            <small class="text-muted">{{ __('Usa el código del proveedor o flota si corresponde.') }}</small>
            @error('external_code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="active" name="active" value="1" {{ old('active', $courier->active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="active">{{ __('Repartidor activo') }}</label>
        </div>
    </div>
</div>
