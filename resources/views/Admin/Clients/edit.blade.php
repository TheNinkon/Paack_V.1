@extends('layouts/layoutMaster')

@section('title', __('Editar cliente'))

@section('page-style')
    @vite('resources/assets/scss/admin/clients/index.scss')
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="mb-6">
            <h4 class="mb-1">{{ __('Editar cliente') }}</h4>
            <p class="text-muted mb-0">{{ __('Actualiza la información general y de contacto del cliente.') }}</p>
        </div>

          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">{{ $client->name }}</h5>
                    <small class="text-muted d-block">{{ __('ID') }}: {{ $client->id }}</small>
                    @if ($client->creator)
                        <small class="text-muted d-block">
                            <i class="ti tabler-user-check me-1"></i>
                            {{ __('Creado por :user', ['user' => $client->creator->name]) }}
                        </small>
                    @endif
                    @if ($client->updater)
                        <small class="text-muted d-block">
                            <i class="ti tabler-history me-1"></i>
                            {{ __('Última actualización :time', ['time' => $client->updated_at?->diffForHumans() ?? __('N/A')]) }}
                        </small>
                    @endif
                </div>
                <span class="badge {{ $client->active ? 'bg-label-success' : 'bg-label-secondary' }}">
                    {{ $client->active ? __('Activo') : __('Inactivo') }}
                </span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.clients.update', $client) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('Admin.Clients.partials.form-fields', ['client' => $client])

                    <div class="d-flex justify-content-end gap-3 mt-6">
                        <a href="{{ route('admin.clients.index') }}" class="btn btn-label-secondary">{{ __('Cancelar') }}</a>
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
