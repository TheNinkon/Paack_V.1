@extends('layouts/layoutMaster')

@php
    use Illuminate\Support\Str;
    $statusColors = [
        'pending' => 'secondary',
        'assigned' => 'info',
        'out_for_delivery' => 'primary',
        'delivered' => 'success',
        'incident' => 'warning',
        'returned' => 'danger',
    ];
@endphp

@section('title', __('Bultos'))

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/moment/moment.js',
        'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/flatpickr/flatpickr.js',
    ])
@endsection

@section('page-script')
    @if (session('parcel-modal-flash'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const offcanvasEl = document.getElementById('parcel-create-offcanvas');
                if (offcanvasEl && window.bootstrap?.Offcanvas) {
                    const instance = window.bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                    instance.show();
                }
            });
        </script>
    @endif
    <script>
        window.routes = Object.assign(window.routes || {}, {
            parcelsCheck: @json(route('app.parcels.check')),
        });

        window.translations = Object.assign(window.translations || {}, {
            listPending: @json(__('Pendiente de guardar')),
            listDuplicate: @json(__('Ya existe en el sistema')),
            listLocalDuplicate: @json(__('Ya se añadió en la lista')),
            currentStatus: @json(__('Estado actual')),
            markedReturned: @json(__('Bulto marcado como retirado.')),
            markingError: @json(__('No se pudo actualizar el estado.')),
            markReturnedButton: @json(__('Marcar como retirado')),
            marking: @json(__('Actualizando…')),
            returnedLabel: @json(__('Retornado')),
            saving: @json(__('Guardando…')),
            parcelUpdated: @json(__('Los datos del bulto se actualizaron correctamente.')),
            parcelUpdateError: @json(__('No se pudo actualizar el bulto.')),
        });
    </script>
    @vite('resources/assets/js/app/parcels/index.js')
@endsection

@section('content')
    <div class="row g-6 mb-6">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">{{ __('Total bultos') }}</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ number_format($stats['total']) }}</h4>
                            </div>
                            <small class="text-muted">{{ __('Registros en el contexto actual') }}</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="icon-base ti tabler-packages icon-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">{{ __('Con proveedor detectado') }}</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ number_format($stats['with_provider']) }}</h4>
                                <p class="text-success mb-0">{{ $stats['total'] > 0 ? round(($stats['with_provider'] / max($stats['total'], 1)) * 100, 1) : 0 }}%</p>
                            </div>
                            <small class="text-muted">{{ __('Bultos vinculados a un patrón') }}</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="icon-base ti tabler-barcode icon-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">{{ __('Sin proveedor') }}</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ number_format($stats['without_provider']) }}</h4>
                                <p class="text-danger mb-0">{{ $stats['total'] > 0 ? round(($stats['without_provider'] / max($stats['total'], 1)) * 100, 1) : 0 }}%</p>
                            </div>
                            <small class="text-muted">{{ __('Revisar patrones pendientes') }}</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="icon-base ti tabler-alert-triangle icon-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">{{ __('Escaneados hoy') }}</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ number_format($stats['scanned_today']) }}</h4>
                            </div>
                            <small class="text-muted">{{ __('Últimas 24 horas') }}</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="icon-base ti tabler-clock-play icon-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
                <div>
                    <h5 class="mb-1">{{ __('Listado de bultos') }}</h5>
                    <small class="text-muted">{{ __('Se muestran los :limit registros más recientes.', ['limit' => $latestLimit]) }}</small>
                </div>
                <div class="d-flex flex-column flex-md-row gap-3">
                    <div class="w-100 w-md-50">
                        <label class="form-label mb-2" for="parcel-provider-filter">{{ __('Proveedor') }}</label>
                        <select id="parcel-provider-filter" class="form-select select2" data-placeholder="{{ __('Todos los proveedores') }}">
                            <option value="">{{ __('Todos los proveedores') }}</option>
                            @foreach ($providers as $provider)
                                <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                            @endforeach
                            <option value="_none">{{ __('Sin proveedor') }}</option>
                        </select>
                    </div>
                    <div class="w-100 w-md-50">
                        <label class="form-label mb-2" for="parcel-courier-filter">{{ __('Courier') }}</label>
                        <select id="parcel-courier-filter" class="form-select select2" data-placeholder="{{ __('Todos los couriers') }}">
                            <option value="">{{ __('Todos los couriers') }}</option>
                            @foreach ($couriers as $courier)
                                <option value="{{ $courier->id }}">{{ $courier->user?->name ?? __('Courier #:id', ['id' => $courier->id]) }}</option>
                            @endforeach
                            <option value="_none">{{ __('Sin asignar') }}</option>
                        </select>
                    </div>
                    <div class="w-100 w-md-50">
                        <label class="form-label mb-2" for="parcel-status-filter">{{ __('Estado') }}</label>
                        <select id="parcel-status-filter" class="form-select select2" data-placeholder="{{ __('Todos los estados') }}">
                            <option value="">{{ __('Todos los estados') }}</option>
                            @foreach ($statusColors as $statusKey => $color)
                                <option value="{{ $statusKey }}">{{ __(Str::headline($statusKey)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-100">
                        <label class="form-label mb-2" for="parcel-date-filter">{{ __('Fecha (creación)') }}</label>
                        <input type="text" id="parcel-date-filter" class="form-control" placeholder="{{ __('Rango de fechas') }}">
                    </div>
                    @can('create', \App\Models\Parcel::class)
                        <div class="ms-md-auto d-flex align-items-end">
                            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#parcel-create-offcanvas" aria-controls="parcel-create-offcanvas">
                                <i class="ti tabler-plus me-1"></i>{{ __('Registrar códigos') }}
                            </button>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="table" id="parcels-table">
                <thead class="border-top">
                    <tr>
                        <th>{{ __('Código') }}</th>
                        <th>{{ __('Proveedor') }}</th>
                        <th>{{ __('Courier') }}</th>
                        <th>{{ __('Estado') }}</th>
                        <th>{{ __('Parada') }}</th>
                        <th>{{ __('Dirección') }}</th>
                        <th>{{ __('Último escaneo') }}</th>
                        <th>{{ __('Creado') }}</th>
                        <th class="text-end">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($parcels as $parcel)
                        @php
                            $latestScan = $parcel->latestScan;
                            $status = $parcel->status ?? 'pending';
                            $statusLabel = __(Str::headline($status));
                            $statusColor = $statusColors[$status] ?? 'secondary';
                        @endphp
                        <tr
                            data-provider-id="{{ $parcel->provider_id ?? '_none' }}"
                            data-courier-id="{{ $parcel->courier_id ?? '_none' }}"
                            data-status="{{ $status }}"
                            data-created="{{ $parcel->created_at?->format('Y-m-d') }}"
                            data-lat="{{ $parcel->latitude }}"
                            data-lng="{{ $parcel->longitude }}"
                            data-parcel-id="{{ $parcel->id }}"
                        >
                            <td class="fw-medium">
                                {{ $parcel->code }}
                                <div class="text-muted small">{{ __('Escaneos: :count', ['count' => $parcel->scans_count]) }}</div>
                            </td>
                            <td>
                                {{ $parcel->provider?->name ?? __('Sin proveedor') }}
                            </td>
                            <td>
                                @if ($parcel->courier)
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium">{{ $parcel->courier->user?->name ?? __('Courier #:id', ['id' => $parcel->courier->id]) }}</span>
                                        <small class="text-muted">{{ $parcel->courier->vehicle_type ? __(Str::headline($parcel->courier->vehicle_type)) : __('Sin tipo definido') }}</small>
                                        @if ($parcel->assigned_at)
                                            <small class="text-muted">{{ __('Asignado :time', ['time' => $parcel->assigned_at->diffForHumans()]) }}</small>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-label-{{ $statusColor }} status-badge" data-status-badge>{{ $statusLabel }}</span>
                            </td>
                            <td>
                                {{ $parcel->stop_code ?? '—' }}
                            </td>
                            <td>
                                @if ($parcel->address_line)
                                    <span class="d-block">{{ $parcel->address_line }}</span>
                                    <small class="text-muted">{{ trim(collect([$parcel->city, $parcel->state, $parcel->postal_code])->filter()->join(', ')) }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td data-order="{{ $latestScan?->created_at?->timestamp ?? 0 }}">
                                @if ($latestScan)
                                    <div class="d-flex flex-column">
                                        <span>{{ $latestScan->created_at->diffForHumans() }}</span>
                                        <small class="text-muted">{{ __('Por :user', ['user' => $latestScan->creator?->name ?? __('Sistema')]) }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td data-order="{{ $parcel->created_at?->timestamp ?? 0 }}">
                                <div class="d-flex flex-column">
                                    <span>{{ $parcel->created_at?->format('d/m/Y H:i') ?? '—' }}</span>
                                    @if ($parcel->updated_at)
                                        <small class="text-muted">{{ __('Actualizado :time', ['time' => $parcel->updated_at->diffForHumans()]) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-label-primary parcel-detail-trigger"
                                        data-summary-url="{{ route('app.parcels.summary', ['code' => $parcel->code]) }}"
                                        data-full-url="{{ route('app.parcels.show', ['code' => $parcel->code]) }}"
                                    >
                                        <i class="ti tabler-zoom-in me-1"></i>{{ __('Detalle') }}
                                    </button>
                                    @can('update', $parcel)
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-label-info parcel-edit-trigger"
                                            data-edit-url="{{ route('app.parcels.edit', $parcel) }}"
                                            data-row-selector="tr[data-parcel-id='{{ $parcel->id }}']"
                                            data-parcel-code="{{ $parcel->code }}"
                                        >
                                            <i class="ti tabler-pencil me-1"></i>{{ __('Editar') }}
                                        </button>
                                    @endcan
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-label-secondary parcel-tag-trigger"
                                        data-code="{{ $parcel->code }}"
                                        data-kill-url="{{ route('app.parcels.kill', $parcel) }}"
                                        data-status-label="{{ $statusLabel }}"
                                        data-row-selector="tr[data-parcel-id='{{ $parcel->id }}']"
                                    >
                                        <i class="ti tabler-eye me-1"></i>{{ __('Etiqueta') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="parcel-offcanvas" aria-labelledby="parcel-offcanvas-label">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="parcel-offcanvas-label">{{ __('Detalle del bulto') }}</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Cerrar') }}"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column justify-content-center align-items-center" data-parcel-offcanvas-body>
            <div class="text-center">
                <span class="spinner-border text-primary mb-3" role="status"></span>
                <p class="text-muted mb-0">{{ __('Cargando información del bulto…') }}</p>
            </div>
        </div>
        <div class="offcanvas-footer border-top p-4 text-end">
            <a href="#" class="btn btn-outline-primary" target="_blank" rel="noopener" data-parcel-offcanvas-open-full>{{ __('Abrir en página completa') }}</a>
        </div>
    </div>

    @php
        $createdCodes = collect(session('parcel-created-codes', []));
        $skippedCodes = collect(session('parcel-skipped-codes', []));
    @endphp

    @can('create', \App\Models\Parcel::class)
        <div
            class="offcanvas offcanvas-end offcanvas-lg"
            tabindex="-1"
            id="parcel-create-offcanvas"
            aria-labelledby="parcel-create-offcanvas-label"
            style="--bs-offcanvas-width: 520px; --bs-offcanvas-bg: var(--bs-body-bg); --bs-offcanvas-color: var(--bs-body-color);"
        >
            <div class="offcanvas-header border-bottom">
                <div>
                    <h5 class="offcanvas-title" id="parcel-create-offcanvas-label">{{ __('Registrar códigos manualmente') }}</h5>
                    <small class="text-muted">{{ __('Pega o escribe los códigos de bulto (uno por línea). Los duplicados se omiten automáticamente.') }}</small>
                </div>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Cerrar') }}"></button>
            </div>
            <div class="offcanvas-body">
                <form action="{{ route('app.parcels.store') }}" method="POST" class="row g-4">
                    @csrf
                    <div class="col-12">
                        <label class="form-label" for="parcel-code-input">{{ __('Código del bulto') }}</label>
                        <div class="input-group">
                            <input
                                type="text"
                                id="parcel-code-input"
                                class="form-control"
                                autocomplete="off"
                                placeholder="{{ __('Escanea y pulsa Enter, o pega varios códigos a la vez') }}"
                            >
                            <button class="btn btn-outline-primary" type="button" id="parcel-code-add">
                                <i class="ti tabler-plus"></i>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1">{{ __('Las lecturas duplicadas se marcarán automáticamente.') }}</small>
                    </div>
                    <div class="col-12">
                        <textarea id="parcel-codes-hidden" name="codes" class="d-none" required>{{ old('codes') }}</textarea>
                        <div class="d-flex flex-wrap gap-2 mb-3 align-items-center" id="parcel-codes-stats">
                            <span class="badge bg-label-primary">{{ __('Pendientes') }}: <span data-count-pending>0</span></span>
                            <span class="badge bg-label-warning">{{ __('Duplicados') }}: <span data-count-duplicate>0</span></span>
                            <span class="badge bg-label-secondary">{{ __('Total') }}: <span data-count-total>0</span></span>
                            <button type="button" class="btn btn-sm btn-label-secondary ms-auto" id="parcel-codes-clear">
                                <i class="ti tabler-trash me-1"></i>{{ __('Limpiar lista') }}
                            </button>
                        </div>
                        <div class="border rounded overflow-auto" style="max-height: 240px;">
                            <ul class="list-group list-group-flush" id="parcel-codes-list"></ul>
                        </div>
                    </div>

                    @if ($createdCodes->isNotEmpty() || $skippedCodes->isNotEmpty())
                        <div class="col-12">
                            <hr class="my-4">
                            @if ($createdCodes->isNotEmpty())
                                <div class="alert alert-success" role="alert">
                                    <div class="d-flex align-items-start">
                                        <i class="ti tabler-check me-2 mt-1"></i>
                                        <div>
                                            <strong>{{ __('Bultos registrados en la última carga') }}</strong>
                                            <ul class="mb-0 ms-3">
                                                @foreach ($createdCodes as $code)
                                                    <li>{{ $code }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($skippedCodes->isNotEmpty())
                                <div class="alert alert-warning" role="alert">
                                    <div class="d-flex align-items-start">
                                        <i class="ti tabler-alert-triangle me-2 mt-1"></i>
                                        <div>
                                            <strong>{{ __('Duplicados omitidos en la última carga') }}</strong>
                                            <ul class="mb-0 ms-3">
                                                @foreach ($skippedCodes as $code)
                                                    <li>{{ $code }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                    <div class="col-12 d-flex justify-content-end gap-3">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('Cancelar') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti tabler-device-floppy me-1"></i>{{ __('Registrar códigos') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    <div class="modal fade" id="parcel-tag-modal" tabindex="-1" aria-labelledby="parcel-tag-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="parcel-tag-modal-label">{{ __('Etiqueta rápida') }}</h5>
                        <small class="text-muted" data-parcel-tag-status></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Cerrar') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <svg id="parcel-tag-barcode" class="img-fluid"></svg>
                    </div>
                    <p class="text-center fw-bold fs-4" data-parcel-tag-code></p>
                    <p class="text-center text-muted">{{ __('Escanea este código con el láser o cámara para dar salida al paquete.') }}</p>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted small" data-parcel-tag-feedback></div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cerrar') }}</button>
                        <button type="button" class="btn btn-danger" data-parcel-kill>{{ __('Marcar como retirado') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="parcel-edit-modal" tabindex="-1" aria-labelledby="parcel-edit-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="parcel-edit-modal-label">{{ __('Editar bulto') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Cerrar') }}"></button>
                </div>
                <div class="modal-body" data-edit-body>
                    <div class="text-center my-4">
                        <span class="spinner-border text-primary mb-3" role="status"></span>
                        <p class="text-muted mb-0">{{ __('Cargando datos del bulto…') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
