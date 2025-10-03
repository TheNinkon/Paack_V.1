@php
    $routePrefix = $routePrefix ?? 'admin.users.';
@endphp

@extends('layouts/layoutMaster')

@section('title', __('Editar usuario'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="mb-6">
            <h4 class="mb-1">{{ __('Editar usuario: :user', ['user' => $user->name]) }}</h4>
            <p class="text-muted mb-0">{{ __('Actualiza la información de contacto, permisos y estado del usuario.') }}</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ __('Usuario actualizado correctamente.') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Datos del usuario') }}</h5>
                <a href="{{ route($routePrefix . 'index') }}" class="btn btn-label-secondary btn-sm">
                    <i class="ti tabler-arrow-back-up me-1"></i>
                    {{ __('Volver al listado') }}
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route($routePrefix . 'update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('Admin.Users.partials.form-fields', ['routePrefix' => $routePrefix])

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
