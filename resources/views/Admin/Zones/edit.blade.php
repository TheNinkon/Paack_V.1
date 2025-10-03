@php
    $routePrefix = $routePrefix ?? 'admin.zones.';
@endphp

@extends('layouts/layoutMaster')

@section('title', __('Editar zona'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="mb-6">
            <h4 class="mb-1">{{ __('Editar zona: :zone', ['zone' => $zone->name]) }}</h4>
            <p class="text-muted mb-0">{{ __('Actualiza los datos operativos y notas internas de la zona seleccionada.') }}</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible" role="alert">
                @switch(session('status'))
                    @case('zone-updated')
                        {{ __('Zona actualizada correctamente.') }}
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
                <h5 class="mb-0">{{ __('Datos de la zona') }}</h5>
                <a href="{{ route($routePrefix . 'index') }}" class="btn btn-label-secondary btn-sm">
                    <i class="ti tabler-arrow-back-up me-1"></i>
                    {{ __('Volver al listado') }}
                </a>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 text-muted small">
                    <div>
                        <div><i class="ti tabler-id me-1"></i>{{ __('ID') }}: {{ $zone->id }}</div>
                        @if ($zone->creator)
                            <div><i class="ti tabler-user-check me-1"></i>{{ __('Creado por :user', ['user' => $zone->creator->name]) }}</div>
                        @endif
                        @if ($zone->updater)
                            <div><i class="ti tabler-history me-1"></i>{{ __('Actualizado :time', ['time' => $zone->updated_at?->diffForHumans() ?? __('N/A')]) }}</div>
                        @endif
                    </div>
                    <span class="badge {{ $zone->active ? 'bg-label-success' : 'bg-label-secondary' }}">
                        {{ $zone->active ? __('Activa') : __('Inactiva') }}
                    </span>
                </div>
                <form action="{{ route($routePrefix . 'update', $zone) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('Admin.Zones.partials.form-fields')

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
