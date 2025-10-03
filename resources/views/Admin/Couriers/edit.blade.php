@php
    $routePrefix = $routePrefix ?? 'admin.couriers.';
@endphp

@extends('layouts/layoutMaster')

@section('title', __('Editar repartidor'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="mb-6">
            <h4 class="mb-1">{{ __('Editar repartidor: :courier', ['courier' => $courier->user?->name ?? $courier->id]) }}</h4>
            <p class="text-muted mb-0">{{ __('Actualiza los datos operativos del repartidor, su vehículo y estado de actividad.') }}</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible" role="alert">
                @switch(session('status'))
                    @case('courier-updated')
                        {{ __('Repartidor actualizado correctamente.') }}
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Datos del repartidor') }}</h5>
                <a href="{{ route($routePrefix . 'index') }}" class="btn btn-label-secondary btn-sm">
                    <i class="ti tabler-arrow-back-up me-1"></i>
                    {{ __('Volver al listado') }}
                </a>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 text-muted small">
                    <div>
                        <div><i class="ti tabler-id me-1"></i>{{ __('ID') }}: {{ $courier->id }}</div>
                        @if ($courier->creator)
                            <div><i class="ti tabler-user-check me-1"></i>{{ __('Creado por :user', ['user' => $courier->creator->name]) }}</div>
                        @endif
                        @if ($courier->updater)
                            <div><i class="ti tabler-history me-1"></i>{{ __('Actualizado :time', ['time' => $courier->updated_at?->diffForHumans() ?? __('N/A')]) }}</div>
                        @endif
                    </div>
                    <span class="badge {{ $courier->active ? 'bg-label-success' : 'bg-label-secondary' }}">
                        {{ $courier->active ? __('Activo') : __('Inactivo') }}
                    </span>
                </div>
                <form action="{{ route($routePrefix . 'update', $courier) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('Admin.Couriers.partials.form-fields', [
                        'clients' => $clients,
                        'availableUsers' => $availableUsers,
                        'vehicleTypes' => $vehicleTypes,
                        'availableUsersMap' => $availableUsersMap,
                        'availableZones' => $availableZones,
                        'availableZonesMap' => $availableZonesMap,
                    ])

                    <div class="d-flex justify-content-end gap-3 mt-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti tabler-device-floppy me-1"></i>
                            {{ __('Guardar cambios') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
