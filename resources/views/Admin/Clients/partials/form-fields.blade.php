@php
    $editing = isset($client);
@endphp

<div class="row g-6">
    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="name" class="form-label">{{ __('Nombre') }} <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $client->name ?? '') }}" required autofocus autocomplete="off">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="cif" class="form-label">{{ __('CIF') }}</label>
            <input type="text" id="cif" name="cif" class="form-control @error('cif') is-invalid @enderror" value="{{ old('cif', $client->cif ?? '') }}" autocomplete="off">
            @error('cif')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="contact_name" class="form-label">{{ __('Nombre de contacto') }}</label>
            <input type="text" id="contact_name" name="contact_name" class="form-control @error('contact_name') is-invalid @enderror" value="{{ old('contact_name', $client->contact_name ?? '') }}" autocomplete="off">
            @error('contact_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="contact_email" class="form-label">{{ __('Email de contacto') }}</label>
            <input type="email" id="contact_email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror" value="{{ old('contact_email', $client->contact_email ?? '') }}" autocomplete="off">
            @error('contact_email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-6 form-control-validation">
            <label for="contact_phone" class="form-label">{{ __('Teléfono de contacto') }}</label>
            <input type="text" id="contact_phone" name="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror" value="{{ old('contact_phone', $client->contact_phone ?? '') }}" autocomplete="off">
            @error('contact_phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-6 d-flex align-items-center">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="active" name="active" value="1" {{ old('active', $client->active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="active">{{ __('Cliente activo') }}</label>
            </div>
        </div>
    </div>

    @unless ($editing)
        <div class="col-12">
            <div class="card border shadow-none">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h6 class="mb-1">{{ __('Crear administrador inicial') }}</h6>
                            <p class="text-muted mb-0">{{ __('Opcional: genera el primer usuario con rol Client Admin para este cliente.') }}</p>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox" class="form-check-input" id="create_admin" name="create_admin" value="1" {{ old('create_admin') ? 'checked' : '' }}>
                        </div>
                    </div>

                    <div id="admin-fields" class="row g-4 {{ old('create_admin') ? '' : 'd-none' }}">
                        <div class="col-12 col-md-6">
                            <div class="form-control-validation">
                                <label for="admin_name" class="form-label">{{ __('Nombre completo') }}</label>
                                <input type="text" id="admin_name" name="admin_name" class="form-control @error('admin_name') is-invalid @enderror" value="{{ old('admin_name') }}" autocomplete="off">
                                @error('admin_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-control-validation">
                                <label for="admin_email" class="form-label">{{ __('Email') }}</label>
                                <input type="email" id="admin_email" name="admin_email" class="form-control @error('admin_email') is-invalid @enderror" value="{{ old('admin_email') }}" autocomplete="off">
                                @error('admin_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-control-validation">
                                <label for="admin_phone" class="form-label">{{ __('Teléfono') }}</label>
                                <input type="text" id="admin_phone" name="admin_phone" class="form-control @error('admin_phone') is-invalid @enderror" value="{{ old('admin_phone') }}" autocomplete="off">
                                @error('admin_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-control-validation">
                                <label for="admin_password" class="form-label">{{ __('Contraseña') }}</label>
                                <input type="password" id="admin_password" name="admin_password" class="form-control @error('admin_password') is-invalid @enderror" autocomplete="new-password">
                                @error('admin_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-control-validation">
                                <label for="admin_password_confirmation" class="form-label">{{ __('Confirmar contraseña') }}</label>
                                <input type="password" id="admin_password_confirmation" name="admin_password_confirmation" class="form-control" autocomplete="new-password">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endunless
</div>
