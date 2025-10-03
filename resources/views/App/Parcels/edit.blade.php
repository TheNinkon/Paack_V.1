@extends('layouts/layoutMaster')

@section('title', __('Editar bulto :code', ['code' => $parcel->code]))

@section('page-script')
    @vite('resources/assets/js/app/parcels/index.js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('parcel-edit-form');
            if (window.setupProviderBarcodeFilter && form) {
                window.setupProviderBarcodeFilter(form);
            }
            if (window.setupParcelAddressAutocomplete && form) {
                window.setupParcelAddressAutocomplete(form);
            }
        });
    </script>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div>
                <h4 class="mb-1">{{ __('Editar bulto') }}</h4>
                <p class="text-muted mb-0">{{ __('Actualiza los datos operativos que necesites corregir o completar.') }}</p>
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('app.parcels.index') }}" class="btn btn-label-secondary">
                    <i class="ti tabler-arrow-back-up me-1"></i>{{ __('Volver al listado') }}
                </a>
                <a href="{{ route('app.parcels.show', ['code' => $parcel->code]) }}" class="btn btn-outline-primary">
                    <i class="ti tabler-info-circle me-1"></i>{{ __('Ver historial') }}
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="alert {{ session('status') === 'parcel-updated' ? 'alert-success' : 'alert-info' }} alert-dismissible" role="alert">
                @switch(session('status'))
                    @case('parcel-updated')
                        {{ __('Los datos del bulto se actualizaron correctamente.') }}
                        @break
                    @case('parcel-unchanged')
                        {{ __('No se detectaron cambios para guardar.') }}
                        @break
                    @default
                        {{ session('status') }}
                @endswitch
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Cerrar') }}"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Cerrar') }}"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Información del bulto') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('app.parcels.update', $parcel) }}" method="POST" class="row g-4" id="parcel-edit-form" data-google-maps-key="{{ $mapsApiKey ?? '' }}">
                    @csrf
                    @method('PATCH')

                    <div class="col-12 col-md-6">
                        <label class="form-label text-uppercase text-muted small mb-1">{{ __('Código') }}</label>
                        <p class="fw-semibold fs-5 mb-0">{{ $parcel->code }}</p>
                        <small class="text-muted">{{ __('Creado :time', ['time' => $parcel->created_at?->diffForHumans() ?? __('N/D')]) }}</small>
                    </div>

                    @include('App.Parcels.partials.edit-form-fields')

                    <div class="col-12 d-flex justify-content-end gap-3 mt-4">
                        <a href="{{ route('app.parcels.index') }}" class="btn btn-label-secondary">{{ __('Cancelar') }}</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti tabler-device-floppy me-1"></i>{{ __('Guardar cambios') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
