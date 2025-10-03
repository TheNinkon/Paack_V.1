@php
    use Illuminate\Support\Str;
    $routePrefix = $routePrefix ?? 'admin.providers.';
    $barcodeStoreRoutePrefix = $barcodeStoreRoutePrefix ?? 'admin.providers.barcodes.';
    $barcodeManageRoutePrefix = $barcodeManageRoutePrefix ?? 'admin.barcodes.';
@endphp

@extends('layouts/layoutMaster')

@section('title', __('Proveedores'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-6">
            <div>
                <h4 class="mb-1">{{ __('Gestión de proveedores') }}</h4>
                <p class="text-muted mb-0">
                    {{ __('Administra transportistas y patrones de códigos asociados a cada cliente.') }}
                </p>
                @if ($currentClient)
                    <span class="badge bg-label-primary mt-2">
                        <i class="ti tabler-building-estate me-1"></i>
                        {{ __('Operando para: :client', ['client' => $currentClient->name]) }}
                    </span>
                @endif
            </div>
            <div class="d-flex flex-column flex-sm-row gap-3">
                <select class="form-select" id="providers-filter-status" aria-label="{{ __('Filtrar por estado') }}">
                    <option value="">{{ __('Todos los estados') }}</option>
                    <option value="active">{{ __('Activos') }}</option>
                    <option value="inactive">{{ __('Inactivos') }}</option>
                </select>
                <a class="btn btn-primary" href="{{ route($routePrefix . 'create') }}">
                    <i class="ti tabler-truck-delivery me-1"></i>
                    {{ __('Añadir proveedor') }}
                </a>
            </div>
        </div>

        <div class="row g-6 mb-6">
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">{{ __('Total proveedores') }}</span>
                                <h4 class="mb-1">{{ $stats['total'] }}</h4>
                                <small class="text-muted">{{ __('Registrados en el contexto actual') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="icon-base ti tabler-truck"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">{{ __('Activos') }}</span>
                                <h4 class="mb-1">{{ $stats['active'] }}</h4>
                                <small class="text-muted">{{ __('Disponibles para operar') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="icon-base ti tabler-power"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">{{ __('Inactivos') }}</span>
                                <h4 class="mb-1">{{ $stats['inactive'] }}</h4>
                                <small class="text-muted">{{ __('Ocultos para uso diario') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-secondary">
                                    <i class="icon-base ti tabler-eye-off"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">{{ __('Con patrones') }}</span>
                                <h4 class="mb-1">{{ $stats['with_patterns'] }}</h4>
                                <small class="text-muted">{{ __('Proveedores con regex configuradas') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="icon-base ti tabler-barcode"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible" role="alert">
                @switch(session('status'))
                    @case('provider-created')
                        {{ __('Proveedor creado correctamente.') }}
                        @break
                    @case('provider-updated')
                        {{ __('Proveedor actualizado correctamente.') }}
                        @break
                    @case('provider-deleted')
                        {{ __('Proveedor eliminado correctamente.') }}
                        @break
                    @default
                        {{ session('status') }}
                @endswitch
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">{{ __('Listado de proveedores') }}</h5>
            </div>
            <div class="card-datatable table-responsive">
                <table class="table table-hover mb-0" id="providers-table">
                    <thead>
                        <tr>
                            <th>{{ __('Proveedor') }}</th>
                            <th class="d-none d-lg-table-cell">{{ __('Cliente') }}</th>
                            <th>{{ __('Slug interno') }}</th>
                            <th>{{ __('Patrones activos') }}</th>
                            <th>{{ __('Estado') }}</th>
                            <th class="text-end">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($providers as $provider)
                            <tr data-status="{{ $provider->active ? 'active' : 'inactive' }}">
                                <td>
                                    <span class="fw-medium d-block">{{ $provider->name }}</span>
                                    @if ($provider->notes)
                                        <small class="text-muted">{{ Str::limit($provider->notes, 80) }}</small>
                                    @endif
                                    @if ($provider->creator)
                                        <small class="text-muted d-block">
                                            <i class="ti tabler-user-check me-1"></i>
                                            {{ __('Creado por :user', ['user' => $provider->creator->name]) }}
                                        </small>
                                    @endif
                                    <small class="text-muted d-block">
                                        <i class="ti tabler-history me-1"></i>
                                        {{ __('Actualizado :time', ['time' => $provider->updated_at?->diffForHumans() ?? __('N/A')]) }}
                                    </small>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    {{ $provider->client?->name ?? '—' }}
                                </td>
                                <td>
                                    <code>{{ $provider->slug }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $provider->barcodes_active_count ? 'info' : 'secondary' }}">
                                        {{ $provider->barcodes_active_count }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $provider->active ? 'bg-label-success' : 'bg-label-warning' }}">
                                        {{ $provider->active ? __('Activo') : __('Inactivo') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route($routePrefix . 'edit', $provider) }}" class="btn btn-sm btn-icon btn-label-primary" title="{{ __('Editar') }}">
                                            <i class="ti tabler-edit"></i>
                                        </a>
                                        <form action="{{ route($routePrefix . 'destroy', $provider) }}" method="POST" onsubmit="return confirm('{{ __('¿Deseas eliminar este proveedor?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-label-danger" title="{{ __('Eliminar') }}">
                                                <i class="ti tabler-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-6 text-muted">
                                    {{ __('No hay proveedores disponibles para este contexto.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($providers->hasPages())
                <div class="card-footer d-flex justify-content-end">
                    {{ $providers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@section('page-script')
    @vite('resources/assets/js/admin/providers/index.js')
@endsection
