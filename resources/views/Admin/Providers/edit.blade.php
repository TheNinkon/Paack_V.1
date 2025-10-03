@php
    $routePrefix = $routePrefix ?? 'admin.providers.';
    $barcodeStoreRoutePrefix = $barcodeStoreRoutePrefix ?? 'admin.providers.barcodes.';
    $barcodeManageRoutePrefix = $barcodeManageRoutePrefix ?? 'admin.barcodes.';
@endphp

@extends('layouts/layoutMaster')

@section('title', __('Editar proveedor'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="mb-6">
            <h4 class="mb-1">{{ __('Editar proveedor: :provider', ['provider' => $provider->name]) }}</h4>
            <p class="text-muted mb-0">{{ __('Actualiza los datos básicos y gestiona los patrones de códigos asociados.') }}</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible" role="alert">
                @switch(session('status'))
                    @case('provider-updated')
                        {{ __('Proveedor actualizado correctamente.') }}
                        @break
                    @case('barcode-created')
                        {{ __('Patrón de código añadido correctamente.') }}
                        @break
                    @case('barcode-updated')
                        {{ __('Patrón de código actualizado correctamente.') }}
                        @break
                    @case('barcode-deleted')
                        {{ __('Patrón eliminado correctamente.') }}
                        @break
                    @default
                        {{ session('status') }}
                @endswitch
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Datos del proveedor') }}</h5>
                <a href="{{ route($routePrefix . 'index') }}" class="btn btn-label-secondary btn-sm">
                    <i class="ti tabler-arrow-back-up me-1"></i>
                    {{ __('Volver al listado') }}
                </a>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div class="text-muted small">
                        <div>
                            <i class="ti tabler-id me-1"></i> {{ __('ID') }}: {{ $provider->id }}
                        </div>
                        @if ($provider->creator)
                            <div>
                                <i class="ti tabler-user-check me-1"></i>
                                {{ __('Creado por :user', ['user' => $provider->creator->name]) }}
                            </div>
                        @endif
                        @if ($provider->updater)
                            <div>
                                <i class="ti tabler-history me-1"></i>
                                {{ __('Actualizado :time', ['time' => $provider->updated_at?->diffForHumans() ?? __('N/A')]) }}
                            </div>
                        @endif
                    </div>
                    <span class="badge {{ $provider->active ? 'bg-label-success' : 'bg-label-secondary' }}">
                        {{ $provider->active ? __('Activo') : __('Inactivo') }}
                    </span>
                </div>
                <form action="{{ route($routePrefix . 'update', $provider) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('Admin.Providers.partials.form-fields')

                    <div class="d-flex justify-content-end gap-3 mt-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti tabler-device-floppy me-1"></i>
                            {{ __('Guardar cambios') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="mb-0">{{ __('Patrones de códigos de bulto') }}</h5>
                    <small class="text-muted">{{ __('Define las expresiones regulares para clasificar códigos escaneados.') }}</small>
                </div>
                <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#newBarcodeForm" aria-expanded="false" aria-controls="newBarcodeForm">
                    <i class="ti tabler-plus me-1"></i>
                    {{ __('Añadir patrón') }}
                </button>
            </div>
            <div class="collapse border-top" id="newBarcodeForm">
                <div class="card-body">
                    <form action="{{ route($barcodeStoreRoutePrefix . 'store', $provider) }}" method="POST" class="row g-4">
                        @csrf
                        <div class="col-12 col-md-4">
                            <label for="barcode_label" class="form-label">{{ __('Etiqueta') }} <span class="text-danger">*</span></label>
                            <input type="text" id="barcode_label" name="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label') }}" required autocomplete="off">
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="barcode_pattern" class="form-label">{{ __('Patrón (regex)') }} <span class="text-danger">*</span></label>
                            <input type="text" id="barcode_pattern" name="pattern_regex" class="form-control @error('pattern_regex') is-invalid @enderror" value="{{ old('pattern_regex') }}" required autocomplete="off" placeholder="{{ __('Ej: ^[A-Z0-9]{10,25}$') }}">
                            @error('pattern_regex')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-2">
                            <label for="barcode_priority" class="form-label">{{ __('Prioridad') }}</label>
                            <input type="number" id="barcode_priority" name="priority" class="form-control @error('priority') is-invalid @enderror" value="{{ old('priority', 100) }}" min="1" max="999">
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-2">
                            <label for="barcode_sample" class="form-label">{{ __('Código ejemplo') }}</label>
                            <input type="text" id="barcode_sample" name="sample_code" class="form-control @error('sample_code') is-invalid @enderror" value="{{ old('sample_code') }}" autocomplete="off">
                            @error('sample_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="barcode_active" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="barcode_active">{{ __('Patrón activo') }}</label>
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti tabler-device-floppy me-1"></i>
                                {{ __('Guardar patrón') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Etiqueta') }}</th>
                                <th>{{ __('Patrón') }}</th>
                                <th>{{ __('Muestra') }}</th>
                                <th>{{ __('Prioridad') }}</th>
                                <th>{{ __('Estado') }}</th>
                                <th class="text-end">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($provider->barcodes as $barcode)
                                <tr>
                                    <td class="fw-medium">
                                        {{ $barcode->label }}
                                        <div class="text-muted small">
                                            @if ($barcode->creator)
                                                <div>
                                                    <i class="ti tabler-user-check me-1"></i>
                                                    {{ __('Creado por :user', ['user' => $barcode->creator->name]) }}
                                                </div>
                                            @endif
                                            <div>
                                                <i class="ti tabler-history me-1"></i>
                                                {{ __('Actualizado :time', ['time' => $barcode->updated_at?->diffForHumans() ?? __('N/A')]) }}
                                            </div>
                                        </div>
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
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-icon btn-label-primary" type="button" data-bs-toggle="collapse" data-bs-target="#barcode-edit-{{ $barcode->id }}" aria-expanded="false" aria-controls="barcode-edit-{{ $barcode->id }}" title="{{ __('Editar') }}">
                                                <i class="ti tabler-edit"></i>
                                            </button>
                                            <form action="{{ route($barcodeManageRoutePrefix . 'destroy', $barcode) }}" method="POST" onsubmit="return confirm('{{ __('¿Eliminar este patrón?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-label-danger" title="{{ __('Eliminar') }}">
                                                    <i class="ti tabler-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="collapse @if ($errors->getBag('updateBarcode_'.$barcode->id) ?? false) show @endif" id="barcode-edit-{{ $barcode->id }}">
                                    <td colspan="6">
                                        <form action="{{ route($barcodeManageRoutePrefix . 'update', $barcode) }}" method="POST" class="row g-4 align-items-end">
                                            @csrf
                                            @method('PUT')
                                            <div class="col-12 col-md-3">
                                                <label class="form-label" for="label-{{ $barcode->id }}">{{ __('Etiqueta') }}</label>
                                                <input type="text" id="label-{{ $barcode->id }}" name="label" class="form-control"
                                                    value="{{ old('label', $barcode->label) }}" required>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label" for="pattern-{{ $barcode->id }}">{{ __('Patrón (regex)') }}</label>
                                                <input type="text" id="pattern-{{ $barcode->id }}" name="pattern_regex" class="form-control"
                                                    value="{{ old('pattern_regex', $barcode->pattern_regex) }}" required>
                                            </div>
                                            <div class="col-12 col-md-2">
                                                <label class="form-label" for="priority-{{ $barcode->id }}">{{ __('Prioridad') }}</label>
                                                <input type="number" id="priority-{{ $barcode->id }}" name="priority" class="form-control"
                                                    value="{{ old('priority', $barcode->priority) }}" min="1" max="999">
                                            </div>
                                            <div class="col-12 col-md-2">
                                                <label class="form-label" for="sample-{{ $barcode->id }}">{{ __('Código ejemplo') }}</label>
                                                <input type="text" id="sample-{{ $barcode->id }}" name="sample_code" class="form-control"
                                                    value="{{ old('sample_code', $barcode->sample_code) }}">
                                            </div>
                                            <div class="col-12 col-md-1">
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" class="form-check-input" id="active-{{ $barcode->id }}" name="active" value="1" {{ old('active', $barcode->active) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="active-{{ $barcode->id }}">{{ __('Activo') }}</label>
                                                </div>
                                            </div>
                                            <div class="col-12 d-flex justify-content-end gap-3">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="ti tabler-device-floppy me-1"></i>
                                                    {{ __('Actualizar patrón') }}
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-muted">
                                        {{ __('Todavía no hay patrones registrados para este proveedor.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
