@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', __('Dashboard'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xxl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-primary"><i class="ti tabler-building"></i></span>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1">{{ __('Clientes activos') }}</h5>
                                <h2 class="mb-0 display-6">0</h2>
                                <small class="text-muted">{{ __('Próximamente: conteo real desde base de datos') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xxl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-success"><i class="ti tabler-users"></i></span>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1">{{ __('Usuarios totales') }}</h5>
                                <h2 class="mb-0 display-6">{{ auth()->user() ? '1' : '0' }}</h2>
                                <small class="text-muted">{{ __('Incluye super admin y usuarios del cliente') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xxl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-warning"><i class="ti tabler-truck-delivery"></i></span>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1">{{ __('Proveedores configurados') }}</h5>
                                <h2 class="mb-0 display-6">0</h2>
                                <small class="text-muted">{{ __('Agrega proveedores desde el módulo correspondiente') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xxl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-info"><i class="ti tabler-barcode"></i></span>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1">{{ __('Patrones de códigos') }}</h5>
                                <h2 class="mb-0 display-6">0</h2>
                                <small class="text-muted">{{ __('Semilla inicial prevista para CTT / SEUR / GLS') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">{{ __('Bienvenido, :name', ['name' => auth()->user()->name ?? 'Super Admin']) }}</h5>
                    <small class="text-muted">{{ __('Este tablero se personalizará a medida que avancemos con los módulos multi-cliente.') }}</small>
                </div>
                <button type="button" class="btn btn-primary" disabled>
                    <i class="ti tabler-plus me-1"></i>{{ __('Crear cliente') }}
                </button>
            </div>
            <div class="card-body">
                <p class="mb-4">
                    {{ __('GiGi Routing está listo para comenzar con la fase de acceso y roles. A continuación te mostramos las próximas tareas sugeridas:') }}
                </p>
                <ul class="list-unstyled mb-0">
                    <li class="mb-3 d-flex align-items-start">
                        <span class="badge bg-label-primary rounded-pill me-3"><i class="ti tabler-shield-check"></i></span>
                        <div>
                            <h6 class="mb-1">{{ __('Configurar clientes y usuarios') }}</h6>
                            <p class="text-muted mb-0">{{ __('Habilitaremos el CRUD para clientes y usuarios internos en la siguiente iteración.') }}</p>
                        </div>
                    </li>
                    <li class="mb-3 d-flex align-items-start">
                        <span class="badge bg-label-success rounded-pill me-3"><i class="ti tabler-map-pin"></i></span>
                        <div>
                            <h6 class="mb-1">{{ __('Definir zonas y repartidores') }}</h6>
                            <p class="text-muted mb-0">{{ __('El siguiente paso será crear la estructura de zonas y asignar repartidores por cliente.') }}</p>
                        </div>
                    </li>
                    <li class="d-flex align-items-start">
                        <span class="badge bg-label-warning rounded-pill me-3"><i class="ti tabler-barcode"></i></span>
                        <div>
                            <h6 class="mb-1">{{ __('Configurar patrones de códigos de bulto') }}</h6>
                            <p class="text-muted mb-0">{{ __('Prepararemos el módulo para cargar regex y validarlos con códigos de ejemplo.') }}</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection
