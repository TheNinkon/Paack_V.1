@php
    use Illuminate\Support\Str;
    $routePrefix = $routePrefix ?? 'admin.users.';
@endphp

@extends('layouts/layoutMaster')

@section('title', __('Usuarios'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-6">
            <div>
                <h4 class="mb-1">{{ __('Gestión de usuarios') }}</h4>
                <p class="text-muted mb-0">{{ __('Crea y administra cuentas internas por cliente y asigna roles específicos.') }}</p>
                @if ($currentClient)
                    <span class="badge bg-label-primary mt-2">
                        <i class="ti tabler-building me-1"></i>
                        {{ __('Operando para: :client', ['client' => $currentClient->name]) }}
                    </span>
                @endif
            </div>
            <div class="d-flex flex-column flex-sm-row gap-3">
                <select class="form-select" id="users-filter-status" aria-label="{{ __('Filtrar por estado') }}">
                    <option value="">{{ __('Todos los estados') }}</option>
                    <option value="active">{{ __('Activos') }}</option>
                    <option value="inactive">{{ __('Inactivos') }}</option>
                </select>
                <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasUser" aria-controls="offcanvasUser">
                    <i class="ti tabler-user-plus me-1"></i>
                    {{ __('Nuevo usuario') }}
                </button>
            </div>
        </div>

        <div class="row g-6 mb-6">
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">{{ __('Usuarios totales') }}</span>
                                <h4 class="mb-1">{{ $stats['total'] }}</h4>
                                <small class="text-muted">{{ __('Registros en el contexto actual') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="icon-base ti tabler-users"></i>
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
                                <small class="text-muted">{{ __('Con acceso habilitado') }}</small>
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
                                <small class="text-muted">{{ __('Suspendidos o dados de baja') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="icon-base ti tabler-archive"></i>
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
                                <span class="text-heading">{{ __('Verificados') }}</span>
                                <h4 class="mb-1">{{ $stats['verified'] }}</h4>
                                <small class="text-muted">{{ __('Cuentas con correo validado') }}</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="icon-base ti tabler-mail"></i>
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
                    @case('user-created')
                        {{ __('Usuario creado correctamente.') }}
                        @break
                    @case('user-updated')
                        {{ __('Usuario actualizado correctamente.') }}
                        @break
                    @case('user-deleted')
                        {{ __('Usuario eliminado correctamente.') }}
                        @break
                    @default
                        {{ session('status') }}
                @endswitch
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">{{ __('Listado de usuarios') }}</h5>
            </div>
            <div class="card-datatable table-responsive">
                <table class="table table-hover mb-0" id="users-table">
                    <thead>
                        <tr>
                            <th>{{ __('Nombre') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th class="d-none d-lg-table-cell">{{ __('Cliente') }}</th>
                            <th>{{ __('Roles') }}</th>
                            <th>{{ __('Estado') }}</th>
                            <th class="text-end">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr data-status="{{ $user->is_active ? 'active' : 'inactive' }}">
                                <td>
                                    <span class="fw-medium d-block">{{ $user->name }}</span>
                                    <small class="text-muted">ID: {{ $user->id }}</small>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td class="d-none d-lg-table-cell">{{ $user->client?->name ?? __('Global') }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($user->roles as $role)
                                            <span class="badge bg-label-primary text-capitalize">{{ str_replace('_', ' ', $role->name) }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $user->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">
                                        {{ $user->is_active ? __('Activo') : __('Inactivo') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route($routePrefix . 'edit', $user) }}" class="btn btn-sm btn-icon btn-label-primary" title="{{ __('Editar') }}">
                                            <i class="ti tabler-edit"></i>
                                        </a>
                                        <form action="{{ route($routePrefix . 'destroy', $user) }}" method="POST" onsubmit="return confirm('{{ __('¿Deseas eliminar este usuario?') }}');">
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
                                <td colspan="6" class="text-center py-6 text-muted">{{ __('No hay usuarios registrados en este contexto.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="card-footer d-flex justify-content-end">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <div
        class="offcanvas offcanvas-end"
        tabindex="-1"
        id="offcanvasUser"
        aria-labelledby="offcanvasUserLabel"
        data-auto-show="{{ session('openCreateUser') || ($errors->any() && old('form_origin') === 'index_create') ? 'true' : 'false' }}"
    >
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasUserLabel" class="offcanvas-title">{{ __('Registrar usuario') }}</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Cerrar') }}"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form action="{{ route($routePrefix . 'store') }}" method="POST" id="create-user-form">
                @csrf
                <input type="hidden" name="form_origin" value="index_create">
                @include('Admin.Users.partials.form-fields', ['routePrefix' => $routePrefix])
                <div class="d-flex justify-content-end gap-3 mt-6">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy me-1"></i>
                        {{ __('Crear usuario') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
    @vite('resources/assets/js/admin/users/index.js')
@endsection
