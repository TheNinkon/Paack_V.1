@extends('layouts/layoutMaster')

@section('title', __('Panel del cliente'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="mb-6">
            <h4 class="mb-1">{{ __('Panel operativo') }}</h4>
            <p class="text-muted mb-0">
                {{ __('Gestiona tu entorno desde aquí. Revisa usuarios, proveedores, zonas y repartidores vinculados a tu cliente.') }}
            </p>
            @if ($currentClient)
                <span class="badge bg-label-primary mt-3">
                    <i class="ti tabler-building me-1"></i>
                    {{ __('Cliente activo: :client', ['client' => $currentClient->name]) }}
                </span>
            @endif
        </div>

        @if ($modules->isNotEmpty())
            <div class="row g-6 mb-6">
                @foreach ($modules as $module)
                    <div class="col-sm-6 col-xl-3">
                        <a class="card h-100 text-reset text-decoration-none" href="{{ route($module['route']) }}">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h5 class="mb-1">{{ $module['label'] }}</h5>
                                        <p class="text-muted mb-0">{{ $module['description'] }}</p>
                                    </div>
                                    <div class="avatar">
                                        <span class="avatar-initial rounded {{ $module['badge'] }}">
                                            <i class="icon-base {{ $module['icon'] }}"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($modules->isEmpty())
            <div class="alert alert-info d-flex align-items-start mb-6" role="alert">
                <i class="ti tabler-info-circle me-2 mt-1"></i>
                <div>
                    <h6 class="alert-heading mb-1">{{ __('Próximamente más módulos disponibles') }}</h6>
                    <p class="mb-0">{{ __('Tu rol todavía no tiene secciones activas en esta fase. Pronto añadiremos el módulo de escaneo y soporte de incidencias.') }}</p>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="mb-1">{{ __('Próximos pasos sugeridos') }}</h5>
                <small class="text-muted">{{ __('Planifica tu despliegue interno mientras preparamos los módulos siguientes.') }}</small>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3 d-flex align-items-start">
                        <span class="badge bg-label-primary rounded-pill me-3"><i class="ti tabler-lock-check"></i></span>
                        <div>
                            <h6 class="mb-1">{{ __('Revisa permisos por rol') }}</h6>
                            <p class="text-muted mb-0">{{ __('Asegúrate de asignar los roles adecuados a cada usuario operativo.') }}</p>
                        </div>
                    </li>
                    <li class="mb-3 d-flex align-items-start">
                        <span class="badge bg-label-success rounded-pill me-3"><i class="ti tabler-id"></i></span>
                        <div>
                            <h6 class="mb-1">{{ __('Configura proveedores y patrones') }}</h6>
                            <p class="text-muted mb-0">{{ __('Mantén actualizados los regex de códigos para acelerar el escaneo.') }}</p>
                        </div>
                    </li>
                    <li class="d-flex align-items-start">
                        <span class="badge bg-label-warning rounded-pill me-3"><i class="ti tabler-map"></i></span>
                        <div>
                            <h6 class="mb-1">{{ __('Define zonas y repartidores') }}</h6>
                            <p class="text-muted mb-0">{{ __('Prepara la estructura para la pre-asignación de rutas en la siguiente fase.') }}</p>
                        </div>
                    </li>
                    @if ($showScanPreview)
                        <li class="mt-3 d-flex align-items-start">
                            <span class="badge bg-label-info rounded-pill me-3"><i class="ti tabler-barcode"></i></span>
                            <div>
                                <h6 class="mb-1">{{ __('Ensaya el flujo de escaneo') }}</h6>
                                <p class="text-muted mb-0">{{ __('Muy pronto activaremos la pantalla de prerecepción para mozos de almacén.') }}</p>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
@endsection
