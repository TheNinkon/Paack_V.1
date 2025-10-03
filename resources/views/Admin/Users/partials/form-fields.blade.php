@php
    $editing = isset($user);
    $clientOptions = $clients ?? collect();
    $rolesOptions = $roles ?? collect();
    $selectedRoles = collect(old('roles', $editing ? $user->roles->pluck('name')->all() : []));
    $defaultClient = old('client_id', $editing ? $user->client_id : ($defaultClientId ?? null));
@endphp

<div class="row g-6">
    @if ($clientOptions->isNotEmpty())
        <div class="col-12 col-md-6">
            <div class="mb-6 form-control-validation">
                <label for="client_id" class="form-label">{{ __('Cliente') }} {{ $rolesOptions->contains('super_admin') ? '' : '*' }}</label>
                <select id="client_id" name="client_id" class="form-select @error('client_id') is-invalid @enderror" {{ $rolesOptions->contains('super_admin') ? '' : 'required' }}>
                    <option value="">{{ __('Selecciona un cliente') }}</option>
                    @foreach ($clientOptions as $client)
                        <option value="{{ $client->id }}" {{ (string) $defaultClient === (string) $client->id ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
                @error('client_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    @else
        @if ($defaultClient)
            <input type="hidden" name="client_id" value="{{ $defaultClient }}">
        @endif
    @endif

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="name" class="form-label">{{ __('Nombre completo') }} <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name ?? '') }}" required autocomplete="off">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="email" class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}" required autocomplete="off">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="phone" class="form-label">{{ __('Teléfono') }}</label>
            <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone ?? '') }}" autocomplete="off">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="roles" class="form-label">{{ __('Roles') }} <span class="text-danger">*</span></label>
            <select id="roles" name="roles[]" class="form-select @error('roles') is-invalid @enderror" multiple required>
                @foreach ($rolesOptions as $role)
                    <option value="{{ $role }}" {{ $selectedRoles->contains($role) ? 'selected' : '' }}>{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $role)) }}</option>
                @endforeach
            </select>
            <small class="text-muted">{{ __('Selecciona uno o varios roles según las responsabilidades del usuario.') }}</small>
            @error('roles')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="password" class="form-label">{{ $editing ? __('Nueva contraseña') : __('Contraseña') }} {{ $editing ? '' : '*' }}</label>
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ $editing ? '' : 'required' }} autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="password_confirmation" class="form-label">{{ $editing ? __('Confirmar nueva contraseña') : __('Confirmar contraseña') }} {{ $editing ? '' : '*' }}</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" {{ $editing ? '' : 'required' }} autocomplete="new-password">
        </div>
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">{{ __('Usuario activo') }}</label>
        </div>
    </div>
</div>
