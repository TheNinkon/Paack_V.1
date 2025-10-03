@extends('layouts/layoutMaster')

@section('title', __('Clientes'))

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/@form-validation/form-validation.scss',
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/moment/moment.js',
        'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/@form-validation/popular.js',
        'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
        'resources/assets/vendor/libs/@form-validation/auto-focus.js',
        'resources/assets/vendor/libs/cleave-zen/cleave-zen.js',
    ])
@endsection

@section('page-style')
    @vite('resources/assets/scss/admin/clients/index.scss')
@endsection

@section('page-script')
    @vite('resources/assets/js/admin/clients/index.js')
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-6 mb-6">
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">{{ __('Clientes totales') }}</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $stats['total'] }}</h4>
                                </div>
                                <small class="mb-0">{{ __('Registros en el sistema') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="icon-base ti tabler-building icon-26px"></i>
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
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $stats['active'] }}</h4>
                                </div>
                                <small class="mb-0">{{ __('Clientes operativos') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="icon-base ti tabler-activity-heartbeat icon-26px"></i>
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
                                <span class="text-heading">{{ __('En revisión') }}</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $stats['inactive'] }}</h4>
                                </div>
                                <small class="mb-0">{{ __('Clientes inactivos') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-secondary">
                                    <i class="icon-base ti tabler-power icon-26px"></i>
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
                                <span class="text-heading">{{ __('Contactos completos') }}</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $stats['with_contact'] }}</h4>
                                </div>
                                <small class="mb-0">{{ __('Con email o teléfono') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="icon-base ti tabler-address-book icon-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show mb-6" role="alert">
                <i class="ti tabler-check me-2"></i>
                @switch(session('status'))
                    @case('client-created')
                        {{ __('Cliente creado correctamente.') }}
                        @break
                    @case('client-created-with-admin')
                        {{ __('Cliente y administrador inicial creados correctamente.') }}
                        @break
                    @case('client-updated')
                        {{ __('Cliente actualizado correctamente.') }}
                        @break
                    @case('client-deleted')
                        {{ __('Cliente eliminado correctamente.') }}
                        @break
                    @default
                        {{ session('status') }}
                @endswitch
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header border-bottom">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between w-100 gap-3">
                    <div>
                        <h5 class="card-title mb-1">{{ __('Listado de clientes') }}</h5>
                        <small class="text-muted">{{ __('Filtra por estado u operador para realizar ajustes rápidos.') }}</small>
                    </div>
                    <div class="d-flex gap-3">
                        <select class="form-select" id="filter-status">
                            <option value="">{{ __('Todos los estados') }}</option>
                            <option value="active">{{ __('Activos') }}</option>
                            <option value="inactive">{{ __('Inactivos') }}</option>
                        </select>
                        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasClient" aria-controls="offcanvasClient">
                            <i class="ti tabler-plus me-1"></i>{{ __('Nuevo cliente') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-datatable table-responsive">
                <table class="table table-hover mb-0" id="clients-table">
                    <thead class="border-top">
                        <tr>
                            <th>{{ __('Nombre') }}</th>
                            <th>{{ __('CIF') }}</th>
                            <th>{{ __('Contacto') }}</th>
                            <th>{{ __('Estado') }}</th>
                            <th class="text-end">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clients as $client)
                            <tr data-status="{{ $client->active ? 'active' : 'inactive' }}">
                                <td>
                                    <span class="fw-medium d-block">{{ $client->name }}</span>
                                    <small class="text-muted d-block">ID: {{ $client->id }}</small>
                                    @if ($client->creator)
                                        <small class="text-muted d-block">
                                            <i class="ti tabler-user-check me-1"></i>
                                            {{ __('Creado por :user', ['user' => $client->creator->name]) }}
                                        </small>
                                    @endif
                                    <small class="text-muted d-block">
                                        <i class="ti tabler-history me-1"></i>
                                        {{ __('Actualizado :time', ['time' => $client->updated_at?->diffForHumans() ?? __('N/A')]) }}
                                    </small>
                                </td>
                                <td>{{ $client->cif ?: '—' }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        @if ($client->contact_name)
                                            <span class="fw-medium">{{ $client->contact_name }}</span>
                                        @endif
                                        @if ($client->contact_email)
                                            <small class="text-muted">
                                                <i class="ti tabler-mail me-1"></i>{{ $client->contact_email }}
                                            </small>
                                        @endif
                                        @if ($client->contact_phone)
                                            <small class="text-muted">
                                                <i class="ti tabler-phone me-1"></i>{{ $client->contact_phone }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $client->active ? 'bg-label-success' : 'bg-label-secondary' }}">
                                        {{ $client->active ? __('Activo') : __('Inactivo') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a class="btn btn-sm btn-icon btn-outline-primary" href="{{ route('admin.clients.edit', $client) }}">
                                            <i class="ti tabler-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" onsubmit="return confirm('{{ __('¿Eliminar este cliente?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger">
                                                <i class="ti tabler-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-muted">
                                    <i class="ti tabler-database-search me-2"></i>{{ __('Aún no hay clientes registrados.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($clients->hasPages())
                <div class="card-footer py-4">
                    {{ $clients->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasClient" aria-labelledby="offcanvasClientLabel" data-auto-show="{{ $errors->any() ? 'true' : 'false' }}">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasClientLabel" class="offcanvas-title">{{ __('Nuevo cliente') }}</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form action="{{ route('admin.clients.store') }}" method="POST" class="pt-0" id="create-client-form">
                @csrf
                @include('Admin.Clients.partials.form-fields')

                <div class="d-flex justify-content-end gap-3 mt-6">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy me-1"></i>{{ __('Crear cliente') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
