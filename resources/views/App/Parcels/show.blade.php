@php use Illuminate\Support\Str; @endphp

@extends('layouts/layoutMaster')

@section('title', __('Historial del bulto :code', ['code' => $parcel->code]))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div>
                <h4 class="mb-1">{{ __('Historial del bulto') }}</h4>
                <p class="text-muted mb-0">{{ __('Consulta los eventos registrados para este código de bulto.') }}</p>
            </div>
            <a href="{{ url()->previous() }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-back-up me-1"></i>{{ __('Volver') }}
            </a>
        </div>

        <div class="card mb-6">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Resumen') }}</h5>
            </div>
            <div class="card-body">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <div class="col">
                        <h6 class="text-muted">{{ __('Código') }}</h6>
                        <p class="mb-0 fw-medium">{{ $parcel->code }}</p>
                    </div>
                    <div class="col">
                        <h6 class="text-muted">{{ __('Proveedor detectado') }}</h6>
                        <p class="mb-0">{{ $parcel->provider?->name ?? __('No detectado') }}</p>
                    </div>
                    <div class="col">
                        <h6 class="text-muted">{{ __('Patrón') }}</h6>
                        <p class="mb-0">{{ $parcel->providerBarcode?->label ?? '—' }}</p>
                    </div>
                    <div class="col">
                        <h6 class="text-muted">{{ __('Parada') }}</h6>
                        <p class="mb-0">{{ $parcel->stop_code ?? '—' }}</p>
                    </div>
                    <div class="col">
                        <h6 class="text-muted">{{ __('Liquidación') }}</h6>
                        <p class="mb-0">{{ $parcel->liquidation_reference ?? $parcel->liquidation_code ?? '—' }}</p>
                    </div>
                    <div class="col">
                        <h6 class="text-muted">{{ __('Dirección') }}</h6>
                        @if ($parcel->address_line)
                            <p class="mb-0">{{ $parcel->address_line }}</p>
                            <small class="text-muted">{{ trim(collect([$parcel->city, $parcel->state, $parcel->postal_code])->filter()->join(', ')) }}</small>
                        @else
                            <p class="mb-0 text-muted">—</p>
                        @endif
                    </div>
                    <div class="col">
                        <h6 class="text-muted">{{ __('Último escaneo') }}</h6>
                        @if ($lastScan)
                            <p class="mb-0">{{ $lastScan->creator?->name ?? __('Sistema') }}</p>
                            <small class="text-muted">{{ $lastScan->created_at->format('d/m/Y H:i') }}</small>
                        @else
                    <p class="mb-0 text-muted">{{ __('Sin escaneos registrados') }}</p>
                @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Eventos registrados') }}</h5>
                <span class="badge bg-label-primary">{{ $events->count() }}</span>
            </div>
            <div class="card-body">
                @if ($events->isEmpty())
                    <p class="text-muted mb-0">{{ __('Todavía no hay eventos adicionales asociados a este bulto.') }}</p>
                @else
                    <ul class="timeline timeline-advance mb-0">
                        @foreach ($events as $event)
                            <li class="timeline-item">
                                <span class="timeline-point {{ $event->event_type === 'scan_unmatched' ? 'timeline-point-warning' : 'timeline-point-success' }}">
                                    <i class="ti tabler-circle"></i>
                                </span>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        <h6 class="mb-1 text-capitalize">{{ Str::headline($event->event_type) }}</h6>
                                        <span class="text-muted">{{ $event->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <p class="mb-2">{{ $event->description ?? __('Sin descripción adicional') }}</p>
                                    @if ($event->scan && $event->scan->creator)
                                        <p class="mb-2 small text-muted">{{ __('Registrado por :user', ['user' => $event->scan->creator->name]) }}</p>
                                    @endif
                                    @if (!empty($event->payload))
                                        <div class="bg-light rounded p-2">
                                            <pre class="mb-0 small">{{ json_encode($event->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
@endsection
