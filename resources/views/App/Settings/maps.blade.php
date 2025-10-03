@extends('layouts/layoutMaster')

@section('title', __('Configuración de mapas'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div>
                <h4 class="mb-1">{{ __('Configuración de mapas') }}</h4>
                <p class="text-muted mb-0">
                    {{ __('Introduce tu clave de Google Maps Platform para habilitar geocodificación y rutas en la app del repartidor.') }}
                </p>
            </div>
        </div>

        @if (session('status') === 'maps-settings-updated')
            <div class="alert alert-success alert-dismissible" role="alert">
                <i class="ti tabler-check me-2"></i>{{ __('La configuración se guardó correctamente.') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Cerrar') }}"></button>
            </div>
        @endif

        <div class="alert alert-info" role="alert">
            <div class="d-flex align-items-start gap-3">
                <i class="ti tabler-info-circle fs-4"></i>
                <div>
                    <p class="mb-1 fw-semibold">{{ __('Requisitos de Google Maps Platform') }}</p>
                    <ul class="mb-0 ps-3">
                        <li>{{ __('Habilita las APIs de Geocoding y Maps JavaScript en tu proyecto.') }}</li>
                        <li>{{ __('Restringe la clave para dominios y apps de tu organización cuando esté listo.') }}</li>
                        <li>{{ __('Los cargos de Google se facturan directamente a tu cuenta de Maps Platform.') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <form action="{{ route('app.settings.maps.update') }}" method="POST" class="card-body">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label" for="google_maps_api_key">{{ __('Clave de Google Maps API') }}</label>
                            <input
                                type="text"
                                id="google_maps_api_key"
                                name="google_maps_api_key"
                                class="form-control @error('google_maps_api_key') is-invalid @enderror"
                                value="{{ old('google_maps_api_key', $client->google_maps_api_key) }}"
                                placeholder="AIza..."
                                autocomplete="off"
                            >
                            @error('google_maps_api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <small class="text-muted">{{ __('Déjalo vacío para usar la clave configurada por el sistema (si existe).') }}</small>
                            @enderror
                        </div>

                        <div class="d-flex gap-3 justify-content-end">
                            <a href="https://console.cloud.google.com/google/maps-apis" target="_blank" rel="noopener" class="btn btn-label-secondary">
                                {{ __('Abrir consola de Google Maps') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti tabler-device-floppy me-1"></i>{{ __('Guardar cambios') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
