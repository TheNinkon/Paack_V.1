@extends('layouts/layoutMaster')

@section('title', __('Nuevo cliente'))

@section('page-style')
    @vite('resources/assets/scss/admin/clients/index.scss')
@endsection

@section('page-script')
    @vite('resources/assets/js/admin/clients/index.js')
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="mb-6">
            <h4 class="mb-1">{{ __('Crear cliente') }}</h4>
            <p class="text-muted mb-0">{{ __('Define los datos básicos del operador para habilitar la gestión multi-cliente.') }}</p>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Datos generales') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.clients.store') }}" method="POST">
                    @csrf
                    @include('Admin.Clients.partials.form-fields')

                    <div class="d-flex justify-content-end gap-3 mt-6">
                        <a href="{{ route('admin.clients.index') }}" class="btn btn-label-secondary">{{ __('Cancelar') }}</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti tabler-device-floppy me-1"></i>
                            {{ __('Crear cliente') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
