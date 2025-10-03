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

    $eventStyles = [
        'parcel_created' => ['point' => 'success', 'icon' => 'ti tabler-package-import'],
        'parcel_manual_created' => ['point' => 'primary', 'icon' => 'ti tabler-square-rounded-plus'],
        'parcel_manual_updated' => ['point' => 'info', 'icon' => 'ti tabler-edit'],
        'parcel_assigned_to_courier' => ['point' => 'info', 'icon' => 'ti tabler-user-check'],
        'parcel_unassigned_from_courier' => ['point' => 'warning', 'icon' => 'ti tabler-user-off'],
        'parcel_import_created' => ['point' => 'info', 'icon' => 'ti tabler-cloud-upload'],
        'parcel_import_updated' => ['point' => 'info', 'icon' => 'ti tabler-refresh'],
        'parcel_import_synced' => ['point' => 'secondary', 'icon' => 'ti tabler-arrows-diagonal-minimize'],
        'parcel_returned' => ['point' => 'danger', 'icon' => 'ti tabler-square-rounded-x'],
        'scan_matched' => ['point' => 'success', 'icon' => 'ti tabler-barcode'],
        'scan_unmatched' => ['point' => 'warning', 'icon' => 'ti tabler-alert-triangle'],
    ];

    $status = $parcel->status ?? 'pending';
    $statusColor = $statusColors[$status] ?? 'secondary';
    $statusLabel = __(Str::headline($status));
    $scansCount = $parcel->scans_count ?? $parcel->scans->count();
    $addressLines = collect([$parcel->address_line, trim(collect([$parcel->city, $parcel->state, $parcel->postal_code])->filter()->join(', '))])->filter();
    $recentScans = $parcel->scans->take(5);
@endphp

<div class="offcanvas-header border-bottom py-4">
    <div class="d-flex flex-column">
        <span class="text-uppercase text-muted small fw-semibold mb-1">{{ __('Bulto') }}</span>
        <h5 class="offcanvas-title mb-2">{{ $parcel->code }}</h5>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge bg-label-{{ $statusColor }}">{{ $statusLabel }}</span>
            <span class="badge bg-label-primary">
                {{ trans_choice('{0}Sin escaneos|{1}:count escaneo|[2,*]:count escaneos', $scansCount, ['count' => $scansCount]) }}
            </span>
            @if ($parcel->created_at)
                <small class="text-muted">{{ __('Creado :time', ['time' => $parcel->created_at->diffForHumans()]) }}</small>
            @endif
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        @can('update', $parcel)
            <a href="{{ route('app.parcels.edit', $parcel) }}" class="btn btn-sm btn-label-info">
                <i class="ti tabler-pencil"></i>
            </a>
        @endcan
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Cerrar') }}"></button>
    </div>
</div>

<div class="offcanvas-body px-0">
    <section class="px-6 py-5 border-bottom bg-body-tertiary">
        <div class="row gy-4 gx-4">
            <div class="col-12 col-lg-6">
                <div class="d-flex align-items-start gap-3">
                    <span class="avatar avatar-sm rounded bg-label-primary shadow-sm">
                        <i class="ti tabler-truck-delivery"></i>
                    </span>
                    <div>
                        <p class="mb-1 text-muted text-uppercase small fw-semibold">{{ __('Proveedor') }}</p>
                        <p class="mb-0 fw-semibold">{{ $parcel->provider?->name ?? __('Sin proveedor') }}</p>
                        @if ($parcel->providerBarcode)
                            <small class="text-muted">{{ __('Patrón: :label', ['label' => $parcel->providerBarcode->label]) }}</small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="d-flex align-items-start gap-3">
                    <span class="avatar avatar-sm rounded bg-label-info shadow-sm">
                        <i class="ti tabler-id-badge"></i>
                    </span>
                    <div>
                        <p class="mb-1 text-muted text-uppercase small fw-semibold">{{ __('Courier asignado') }}</p>
                        @if ($parcel->courier)
                            <p class="mb-0 fw-semibold">{{ $parcel->courier->user?->name ?? __('Courier #:id', ['id' => $parcel->courier->id]) }}</p>
                            <small class="text-muted d-block">{{ __('Tipo: :type', ['type' => $parcel->courier->vehicle_type ? __(Str::headline($parcel->courier->vehicle_type)) : __('Sin definir')]) }}</small>
                            @if ($parcel->assigned_at)
                                <small class="text-muted">{{ __('Asignado :time', ['time' => $parcel->assigned_at->diffForHumans()]) }}</small>
                            @endif
                        @else
                            <p class="mb-0 text-muted">{{ __('Sin asignar') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <p class="mb-1 text-muted text-uppercase small fw-semibold">{{ __('Parada') }}</p>
                <p class="mb-0">{{ $parcel->stop_code ?? '—' }}</p>
            </div>
            <div class="col-6 col-lg-3">
                <p class="mb-1 text-muted text-uppercase small fw-semibold">{{ __('Liquidación') }}</p>
                <p class="mb-0">{{ $parcel->liquidation_reference ?? $parcel->liquidation_code ?? '—' }}</p>
            </div>
            <div class="col-12">
                <p class="mb-1 text-muted text-uppercase small fw-semibold">{{ __('Dirección') }}</p>
                @if ($addressLines->isNotEmpty())
                    @foreach ($addressLines as $line)
                        <p class="mb-0">{{ $line }}</p>
                    @endforeach
                @else
                    <p class="mb-0 text-muted">—</p>
                @endif
                @if ($parcel->formatted_address)
                    <p class="mb-0 text-muted mt-2">{{ __('Normalizada: :address', ['address' => $parcel->formatted_address]) }}</p>
                @endif
                @if ($parcel->latitude && $parcel->longitude)
                    <p class="mb-0 text-muted mt-1">{{ __('Coordenadas: :lat, :lng', ['lat' => number_format($parcel->latitude, 7), 'lng' => number_format($parcel->longitude, 7)]) }}</p>
                @endif
            </div>
        </div>
    </section>

    <section class="px-6 py-5 border-bottom">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">{{ __('Escaneos recientes') }}</h6>
            @if ($parcel->latestScan)
                <small class="text-muted">{{ __('Último: :time', ['time' => $parcel->latestScan->created_at->diffForHumans()]) }}</small>
            @endif
        </div>
        @if ($recentScans->isEmpty())
            <p class="text-muted mb-0">{{ __('Todavía no se ha registrado ningún escaneo.') }}</p>
        @else
            <div class="list-group list-group-flush rounded border">
                @foreach ($recentScans as $scan)
                    <div class="list-group-item d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <span class="fw-semibold d-block">{{ $scan->created_at->format('d/m/Y H:i') }}</span>
                            <small class="text-muted d-block">{{ __('Por :user', ['user' => $scan->creator?->name ?? __('Sistema')]) }}</small>
                            @if ($scan->provider)
                                <small class="text-muted">{{ __('Proveedor: :provider', ['provider' => $scan->provider->name]) }}</small>
                            @endif
                        </div>
                        <span class="badge {{ $scan->is_valid ? 'bg-label-success' : 'bg-label-warning' }}">
                            {{ $scan->is_valid ? __('Válido') : __('Sin coincidencia') }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="px-6 py-5" style="max-height: 40vh; overflow-y: auto;">
        <h6 class="mb-3">{{ __('Eventos recientes') }}</h6>
        @if ($parcel->events->isEmpty())
            <p class="text-muted mb-0">{{ __('Todavía no hay eventos registrados para este bulto.') }}</p>
        @else
            <ul class="timeline timeline-advance mb-0">
                @foreach ($parcel->events as $event)
                    @php
                        $styles = $eventStyles[$event->event_type] ?? ['point' => 'primary', 'icon' => 'ti tabler-dot'];
                    @endphp
                    <li class="timeline-item">
                        <span class="timeline-point timeline-point-{{ $styles['point'] }}">
                            <i class="{{ $styles['icon'] }}"></i>
                        </span>
                        <div class="timeline-event">
                            <div class="timeline-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-capitalize">{{ Str::headline($event->event_type) }}</h6>
                                <span class="text-muted small">{{ $event->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="mb-2">{{ $event->description ?? __('Sin descripción adicional') }}</p>
                            @if ($event->scan && $event->scan->creator)
                                <p class="mb-2 small text-muted">{{ __('Registrado por :user', ['user' => $event->scan->creator->name]) }}</p>
                            @endif
                            @if (! empty($event->payload))
                                <div class="bg-light rounded p-2">
                                    <pre class="mb-0 small">{{ json_encode($event->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
