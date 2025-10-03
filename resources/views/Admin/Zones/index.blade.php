@php
    use Illuminate\Support\Str;
    $routePrefix = $routePrefix ?? 'admin.zones.';
@endphp

@extends('layouts/layoutMaster')

@section('title', __('Zonas'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-6">
            <div>
                <h4 class="mb-1">{{ __('Gestión de zonas') }}</h4>
                <p class="text-muted mb-0">{{ __('Administra áreas operativas, códigos internos y notas por cliente.') }}</p>
                @if ($currentClient)
                    <span class="badge bg-label-primary mt-2">
                        <i class="ti tabler-building me-1"></i>
                        {{ __('Operando para: :client', ['client' => $currentClient->name]) }}
                    </span>
                @endif
            </div>
            <div class="d-flex flex-column flex-sm-row gap-3">
                <select class="form-select" id="zones-filter-status" aria-label="{{ __('Filtrar por estado') }}">
                    <option value="">{{ __('Todos los estados') }}</option>
                    <option value="active">{{ __('Activas') }}</option>
                    <option value="inactive">{{ __('Inactivas') }}</option>
                </select>
                <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasZone" aria-controls="offcanvasZone">
                    <i class="ti tabler-map-pin me-1"></i>
                    {{ __('Nueva zona') }}
                </button>
            </div>
        </div>

        <div class="row g-6 mb-6">
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">{{ __('Total zonas') }}</span>
                                <h4 class="mb-1">{{ $stats['total'] }}</h4>
                                <small class="text-muted">{{ __('Registros en el contexto actual') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="icon-base ti tabler-map"></i>
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
                                <span class="text-heading">{{ __('Activas') }}</span>
                                <h4 class="mb-1">{{ $stats['active'] }}</h4>
                                <small class="text-muted">{{ __('Disponibles para asignaciones') }}</small>
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
                                <span class="text-heading">{{ __('Inactivas') }}</span>
                                <h4 class="mb-1">{{ $stats['inactive'] }}</h4>
                                <small class="text-muted">{{ __('Ocultas en flujos operativos') }}</small>
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
                                <span class="text-heading">{{ __('Con código') }}</span>
                                <h4 class="mb-1">{{ $stats['with_code'] }}</h4>
                                <small class="text-muted">{{ __('Zonas con identificador interno') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="icon-base ti tabler-tag"></i>
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
                    @case('zone-created')
                        {{ __('Zona creada correctamente.') }}
                        @break
                    @case('zone-updated')
                        {{ __('Zona actualizada correctamente.') }}
                        @break
                    @case('zone-deleted')
                        {{ __('Zona eliminada correctamente.') }}
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
                <h5 class="card-title mb-0">{{ __('Listado de zonas') }}</h5>
            </div>
            <div class="card-datatable table-responsive">
                <table class="table table-hover mb-0" id="zones-table">
                    <thead>
                        <tr>
                            <th>{{ __('Zona') }}</th>
                            <th class="d-none d-lg-table-cell">{{ __('Cliente') }}</th>
                            <th>{{ __('Código') }}</th>
                            <th>{{ __('Estado') }}</th>
                            <th class="text-end">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($zones as $zone)
                            <tr data-status="{{ $zone->active ? 'active' : 'inactive' }}">
                                <td>
                                    <span class="fw-medium d-block">{{ $zone->name }}</span>
                                    @if ($zone->notes)
                                        <small class="text-muted">{{ Str::limit($zone->notes, 80) }}</small>
                                    @endif
                                    @if ($zone->creator)
                                        <small class="text-muted d-block">
                                            <i class="ti tabler-user-check me-1"></i>
                                            {{ __('Creado por :user', ['user' => $zone->creator->name]) }}
                                        </small>
                                    @endif
                                    <small class="text-muted d-block">
                                        <i class="ti tabler-history me-1"></i>
                                        {{ __('Actualizado :time', ['time' => $zone->updated_at?->diffForHumans() ?? __('N/A')]) }}
                                    </small>
                                </td>
                                <td class="d-none d-lg-table-cell">{{ $zone->client?->name ?? '—' }}</td>
                                <td>
                                    @if ($zone->code)
                                        <code>{{ $zone->code }}</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $zone->active ? 'bg-label-success' : 'bg-label-secondary' }}">
                                        {{ $zone->active ? __('Activa') : __('Inactiva') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route($routePrefix . 'edit', $zone) }}" class="btn btn-sm btn-icon btn-label-primary" title="{{ __('Editar') }}">
                                            <i class="ti tabler-edit"></i>
                                        </a>
                                        <form action="{{ route($routePrefix . 'destroy', $zone) }}" method="POST" onsubmit="return confirm('{{ __('¿Deseas eliminar esta zona?') }}');">
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
                                <td colspan="5" class="text-center py-6 text-muted">{{ __('No hay zonas configuradas en este contexto.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($zones->hasPages())
                <div class="card-footer d-flex justify-content-end">
                    {{ $zones->links() }}
                </div>
            @endif
        </div>
    </div>

    <div
        class="offcanvas offcanvas-end"
        tabindex="-1"
        id="offcanvasZone"
        aria-labelledby="offcanvasZoneLabel"
        data-auto-show="{{ session('openCreateZone') || ($errors->any() && old('form_origin') === 'index_create_zone') ? 'true' : 'false' }}"
    >
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasZoneLabel" class="offcanvas-title">{{ __('Registrar zona') }}</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Cerrar') }}"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form action="{{ route($routePrefix . 'store') }}" method="POST" id="create-zone-form">
                @csrf
                <input type="hidden" name="form_origin" value="index_create_zone">
                @include('Admin.Zones.partials.form-fields', ['routePrefix' => $routePrefix])
                <div class="d-flex justify-content-end gap-3 mt-6">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy me-1"></i>
                        {{ __('Crear zona') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
    @vite('resources/assets/js/admin/zones/index.js')
@endsection
