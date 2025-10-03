@php
    $routePrefix = $routePrefix ?? 'admin.providers.';
@endphp

@extends('layouts/layoutMaster')

@section('title', __('Nuevo proveedor'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="mb-6">
            <h4 class="mb-1">{{ __('Crear proveedor') }}</h4>
            <p class="text-muted mb-0">{{ __('Registra un nuevo transportista para el cliente seleccionado.') }}</p>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Datos del proveedor') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route($routePrefix . 'store') }}" method="POST">
                    @csrf
                    @include('Admin.Providers.partials.form-fields', ['routePrefix' => $routePrefix])

                    <div class="d-flex justify-content-end gap-3 mt-6">
                        <a href="{{ route($routePrefix . 'index') }}" class="btn btn-label-secondary">{{ __('Cancelar') }}</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti tabler-device-floppy me-1"></i>
                            {{ __('Crear proveedor') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
