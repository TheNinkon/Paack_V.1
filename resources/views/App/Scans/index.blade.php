@extends('layouts/layoutMaster')

@section('title', __('Prerrecepción'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="mb-6">
            <h4 class="mb-1">{{ __('Escaneo de bultos') }}</h4>
            <p class="text-muted mb-0">{{ __('Escanea o introduce manualmente el código del bulto para detectar automáticamente el proveedor asociado.') }}</p>
        </div>

        <div id="scan-feedback-container">
            @if ($feedback)
                <div class="alert alert-{{ $feedback['status'] === 'matched' ? 'success' : 'warning' }} alert-dismissible" role="alert" data-scan-feedback="{{ $feedback['status'] }}">
                    <div class="d-flex">
                        <i class="ti {{ $feedback['status'] === 'matched' ? 'tabler-checkbox' : 'tabler-alert-triangle' }} me-2 mt-1"></i>
                        <div>
                            <strong>{{ $feedback['code'] }}</strong>
                            @if ($feedback['status'] === 'matched')
                                <span class="d-block">{{ __('Proveedor detectado: :provider', ['provider' => $feedback['provider_name'] ?? __('Desconocido')]) }}</span>
                                @if ($feedback['pattern_label'])
                                    <small class="text-muted">{{ __('Patrón: :label', ['label' => $feedback['pattern_label']]) }}</small>
                                @endif
                            @else
                                <span class="d-block">{{ __('No se encontró un patrón asociado. Revisa los patrones configurados para este cliente.') }}</span>
                            @endif
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <div class="row g-6 mb-6">
            @can('scan.create')
                <div class="col-12 col-lg-5">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Nuevo escaneo') }}</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('app.scans.store') }}" method="POST" id="scan-form">
                                @csrf
                                <div class="mb-4">
                                    <label for="code" class="form-label">{{ __('Código del bulto') }}</label>
                                    <input
                                        type="text"
                                        class="form-control @error('code') is-invalid @enderror"
                                        id="code"
                                        name="code"
                                        value="{{ old('code') }}"
                                        required
                                        placeholder="{{ __('Escanea o escribe el código y pulsa Enter') }}"
                                        autocomplete="off"
                                    >
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ __('El foco permanecerá en este campo para facilitar el escaneo continuo.') }}</small>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti tabler-barcode me-1"></i>{{ __('Registrar') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan
            <div class="col-12 col-lg-{{ auth()->user()->can('scan.create') ? '7' : '12' }}">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">{{ __('Últimos escaneos') }}</h5>
                            <small class="text-muted">{{ __('Se muestran los 50 escaneos más recientes.') }}</small>
                        </div>
                        <span class="badge bg-label-primary">{{ $recentScans->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="scans-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Código') }}</th>
                                        <th>{{ __('Proveedor detectado') }}</th>
                                        <th>{{ __('Parada') }}</th>
                                        <th>{{ __('Dirección') }}</th>
                                        <th>{{ __('Patrón') }}</th>
                                        <th>{{ __('Resultado') }}</th>
                                        <th>{{ __('Fecha') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentScans as $scan)
                                        <tr class="{{ $feedback && $feedback['scan_id'] === $scan->id ? 'table-active' : '' }}">
                                            <td class="fw-medium">
                                                {{ $scan->code }}
                                                <div class="text-muted small">
                                                    @if ($scan->creator)
                                                        <span>{{ __('Por :user', ['user' => $scan->creator->name]) }}</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <a class="btn btn-sm btn-label-primary mt-2" href="{{ route('app.parcels.show', ['code' => $scan->code]) }}">
                                                        <i class="ti tabler-clock me-1"></i>{{ __('Historial') }}
                                                    </a>
                                                </div>
                                            </td>
                                            <td>{{ $scan->provider?->name ?? __('No detectado') }}</td>
                                            <td>{{ $scan->parcel?->stop_code ?? '—' }}</td>
                                            <td>
                                                @if ($scan->parcel?->address_line)
                                                    <span class="d-block">{{ $scan->parcel->address_line }}</span>
                                                    <small class="text-muted">{{ trim(collect([$scan->parcel->city, $scan->parcel->state, $scan->parcel->postal_code])->filter()->join(', ')) }}</small>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $scan->providerBarcode?->label ?? '—' }}</td>
                                            <td>
                                                <span class="badge {{ $scan->is_valid ? 'bg-label-success' : 'bg-label-warning' }}">
                                                    {{ $scan->is_valid ? __('Válido') : __('Sin coincidencia') }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $scan->created_at->diffForHumans() }}</small>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">{{ __('Todavía no se ha registrado ningún escaneo.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        window.routes = Object.assign(window.routes || {}, {
            parcelHistory: @json(route('app.parcels.show', ['code' => '__CODE__'])),
        });

        window.translations = Object.assign(window.translations || {}, {
            scanning: @json(__('Escaneando...')),
            scanProvider: @json(__('Proveedor detectado')),
            scanPattern: @json(__('Patrón')),
            scanNoPattern: @json(__('No se encontró un patrón asociado.')),
            scanError: @json(__('Error al registrar el escaneo')), 
            scanProviderUnknown: @json(__('No detectado')),
            scanBy: @json(__('Por :user')),
            history: @json(__('Historial')),
            justNow: @json(__('Hace un momento')),
            valid: @json(__('Válido')),
            noMatch: @json(__('Sin coincidencia')),
        });
    </script>
    @vite('resources/assets/js/app/scans/index.js')
@endsection
