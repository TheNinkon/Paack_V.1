@php
    $providerRoutePrefix = $providerRoutePrefix ?? 'admin.providers.';
@endphp

@extends('layouts/layoutMaster')

@section('title', __('Patrones de códigos'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-6">
            <div>
                <h4 class="mb-1">{{ __('Patrones de códigos por proveedor') }}</h4>
                <p class="text-muted mb-0">{{ __('Lista consolidada de expresiones regulares activas para facilitar auditorías rápidas.') }}</p>
            </div>
            <a class="btn btn-label-primary" href="{{ route($providerRoutePrefix . 'index') }}">
                <i class="ti tabler-arrow-back-up me-1"></i>
                {{ __('Volver a proveedores') }}
            </a>
        </div>

        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">{{ __('Patrones configurados') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Proveedor') }}</th>
                                <th>{{ __('Cliente') }}</th>
                                <th>{{ __('Etiqueta') }}</th>
                                <th>{{ __('Expresión regular') }}</th>
                                <th>{{ __('Ejemplo') }}</th>
                                <th>{{ __('Prioridad') }}</th>
                                <th>{{ __('Estado') }}</th>
                                <th class="text-end">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($barcodes as $barcode)
                                <tr>
                                    <td class="fw-medium">{{ $barcode->provider?->name ?? '—' }}</td>
                                    <td>{{ $barcode->provider?->client?->name ?? '—' }}</td>
                                    <td>
                                        <span class="d-block">{{ $barcode->label }}</span>
                                        <small class="text-muted d-block">
                                            <i class="ti tabler-user-check me-1"></i>
                                            {{ $barcode->creator?->name ?? __('N/A') }}
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="ti tabler-history me-1"></i>
                                            {{ $barcode->updated_at?->diffForHumans() ?? __('N/A') }}
                                        </small>
                                    </td>
                                    <td><code>{{ $barcode->pattern_regex }}</code></td>
                                    <td>{{ $barcode->sample_code ?: '—' }}</td>
                                    <td>{{ $barcode->priority }}</td>
                                    <td>
                                        <span class="badge {{ $barcode->active ? 'bg-label-success' : 'bg-label-secondary' }}">
                                            {{ $barcode->active ? __('Activo') : __('Inactivo') }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if ($barcode->provider)
                                            <a href="{{ route($providerRoutePrefix . 'edit', $barcode->provider) }}" class="btn btn-sm btn-label-primary">
                                                <i class="ti tabler-edit me-1"></i>{{ __('Gestionar') }}
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-6 text-muted">
                                        {{ __('Todavía no se han configurado patrones en los proveedores disponibles.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($barcodes->hasPages())
                <div class="card-footer d-flex justify-content-end">
                    {{ $barcodes->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
