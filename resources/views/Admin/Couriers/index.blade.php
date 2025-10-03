@php
    use Illuminate\Support\Str;
    $routePrefix = $routePrefix ?? 'admin.couriers.';
@endphp

@extends('layouts/layoutMaster')

@section('title', __('Repartidores'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-6">
            <div>
                <h4 class="mb-1">{{ __('Gestión de repartidores') }}</h4>
                <p class="text-muted mb-0">{{ __('Vincula usuarios operativos con tipos de vehículo y códigos externos.') }}</p>
                @if ($currentClient)
                    <span class="badge bg-label-primary mt-2">
                        <i class="ti tabler-building me-1"></i>
                        {{ __('Operando para: :client', ['client' => $currentClient->name]) }}
                    </span>
                @endif
            </div>
            <div class="d-flex flex-column flex-sm-row gap-3">
                <select class="form-select" id="couriers-filter-status" aria-label="{{ __('Filtrar por estado') }}">
                    <option value="">{{ __('Todos los estados') }}</option>
                    <option value="active">{{ __('Activos') }}</option>
                    <option value="inactive">{{ __('Inactivos') }}</option>
                </select>
                <select class="form-select" id="couriers-filter-vehicle" aria-label="{{ __('Filtrar por vehículo') }}">
                    <option value="">{{ __('Todos los vehículos') }}</option>
                    @foreach ($vehicleTypes as $type)
                        <option value="{{ $type }}">{{ Str::headline($type) }}</option>
                    @endforeach
                </select>
                <select class="form-select" id="couriers-filter-zone" aria-label="{{ __('Filtrar por zona') }}">
                    <option value="">{{ __('Todas las zonas') }}</option>
                    @foreach ($availableZones as $zone)
                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCourier" aria-controls="offcanvasCourier">
                    <i class="ti tabler-user-check me-1"></i>
                    {{ __('Nuevo repartidor') }}
                </button>
            </div>
        </div>

        <div class="row g-6 mb-6">
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">{{ __('Total repartidores') }}</span>
                                <h4 class="mb-1">{{ $stats['total'] }}</h4>
                                <small class="text-muted">{{ __('Registros en el contexto actual') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="icon-base ti tabler-motorbike"></i>
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
                                <small class="text-muted">{{ __('Disponibles para rutas') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="icon-base ti tabler-check"></i>
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
                                <small class="text-muted">{{ __('Deshabilitados temporalmente') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-secondary">
                                    <i class="icon-base ti tabler-pause"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <span class="text-heading d-block mb-3">{{ __('Vehículos asignados') }}</span>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($vehicleStats as $vehicle => $count)
                                <span class="badge bg-label-info">
                                    {{ Str::headline($vehicle) }}: {{ $count }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible" role="alert">
                @switch(session('status'))
                    @case('courier-created')
                        {{ __('Repartidor creado correctamente.') }}
                        @break
                    @case('courier-updated')
                        {{ __('Repartidor actualizado correctamente.') }}
                        @break
                    @case('courier-deleted')
                        {{ __('Repartidor eliminado correctamente.') }}
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

        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">{{ __('Listado de repartidores') }}</h5>
            </div>
            <div class="card-datatable table-responsive">
                <table class="table table-hover mb-0" id="couriers-table">
                    <thead>
                        <tr>
                            <th>{{ __('Repartidor') }}</th>
                            <th>{{ __('Contacto') }}</th>
                            <th class="d-none d-lg-table-cell">{{ __('Cliente') }}</th>
                            <th>{{ __('Vehículo') }}</th>
                            <th>{{ __('Zona') }}</th>
                            <th>{{ __('Código externo') }}</th>
                            <th>{{ __('Estado') }}</th>
                            <th class="text-end">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($couriers as $courier)
                            <tr data-status="{{ $courier->active ? 'active' : 'inactive' }}" data-vehicle="{{ $courier->vehicle_type }}" data-zone="{{ $courier->zone_id ?? '' }}">
                                <td>
                                    <span class="fw-medium d-block">{{ $courier->user?->name ?? __('Sin usuario') }}</span>
                                    <small class="text-muted d-block">ID: {{ $courier->id }}</small>
                                    @if ($courier->creator)
                                        <small class="text-muted d-block">
                                            <i class="ti tabler-user-check me-1"></i>
                                            {{ __('Creado por :user', ['user' => $courier->creator->name]) }}
                                        </small>
                                    @endif
                                    <small class="text-muted d-block">
                                        <i class="ti tabler-history me-1"></i>
                                        {{ __('Actualizado :time', ['time' => $courier->updated_at?->diffForHumans() ?? __('N/A')]) }}
                                    </small>
                                </td>
                                <td>
                                    @if ($courier->user)
                                        <span class="d-block">{{ $courier->user->email }}</span>
                                        @if ($courier->user->phone)
                                            <small class="text-muted">{{ $courier->user->phone }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="d-none d-lg-table-cell">{{ $courier->client?->name ?? '—' }}</td>
                                <td>{{ Str::headline($courier->vehicle_type) }}</td>
                                <td>{{ $courier->zone?->name ?? '—' }}</td>
                                <td>{{ $courier->external_code ?: '—' }}</td>
                                <td>
                                    <span class="badge {{ $courier->active ? 'bg-label-success' : 'bg-label-secondary' }}">
                                        {{ $courier->active ? __('Activo') : __('Inactivo') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route($routePrefix . 'edit', $courier) }}" class="btn btn-sm btn-icon btn-label-primary" title="{{ __('Editar') }}">
                                            <i class="ti tabler-edit"></i>
                                        </a>
                                        <form action="{{ route($routePrefix . 'destroy', $courier) }}" method="POST" onsubmit="return confirm('{{ __('¿Deseas eliminar este repartidor?') }}');">
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
                                <td colspan="7" class="text-center py-6 text-muted">{{ __('No hay repartidores registrados en este contexto.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($couriers->hasPages())
                <div class="card-footer d-flex justify-content-end">
                    {{ $couriers->links() }}
                </div>
            @endif
        </div>
    </div>

    <div
        class="offcanvas offcanvas-end"
        tabindex="-1"
        id="offcanvasCourier"
        aria-labelledby="offcanvasCourierLabel"
        data-auto-show="{{ session('openCreateCourier') || ($errors->any() && old('form_origin') === 'index_create_courier') ? 'true' : 'false' }}"
    >
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasCourierLabel" class="offcanvas-title">{{ __('Registrar repartidor') }}</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Cerrar') }}"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form action="{{ route($routePrefix . 'store') }}" method="POST" id="create-courier-form">
                @csrf
                <input type="hidden" name="form_origin" value="index_create_courier">
                @include('Admin.Couriers.partials.form-fields', [
                    'routePrefix' => $routePrefix,
                    'clients' => $clients,
                    'vehicleTypes' => $vehicleTypes,
                    'availableUsers' => $availableUsers,
                    'availableUsersMap' => $availableUsersMap,
                    'availableZones' => $availableZones,
                    'availableZonesMap' => $availableZonesMap,
                ])
                <div class="d-flex justify-content-end gap-3 mt-6">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy me-1"></i>
                        {{ __('Crear repartidor') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
    @vite('resources/assets/js/admin/couriers/index.js')
@endsection
