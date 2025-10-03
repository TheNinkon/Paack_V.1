@php use Illuminate\Support\Str; @endphp

@extends('layouts/layoutMaster')

@section('title', __('Registro de actividad'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-6">
            <div>
                <h4 class="mb-1">{{ __('Registro de actividad') }}</h4>
                <p class="text-muted mb-0">{{ __('Audita los cambios recientes realizados en clientes, usuarios y catálogos.') }}</p>
            </div>
        </div>

        <div class="card mb-6">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Filtros') }}</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-4 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="filter-log-name">{{ __('Módulo') }}</label>
                        <select id="filter-log-name" name="log_name" class="form-select">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach ($logNames as $logName)
                                <option value="{{ $logName }}" {{ ($filters['log_name'] ?? '') === $logName ? 'selected' : '' }}>
                                    {{ Str::headline($logName) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="filter-subject">{{ __('Entidad') }}</label>
                        <select id="filter-subject" name="subject_type" class="form-select">
                            <option value="">{{ __('Todas') }}</option>
                            @foreach ($subjectTypes as $class => $label)
                                <option value="{{ $class }}" {{ ($filters['subject_type'] ?? '') === $class ? 'selected' : '' }}>
                                    {{ __($label) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="filter-causer">{{ __('Usuario') }}</label>
                        <select id="filter-causer" name="causer_id" class="form-select">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach ($causers as $causer)
                                <option value="{{ $causer->id }}" {{ ($filters['causer_id'] ?? '') == $causer->id ? 'selected' : '' }}>
                                    {{ $causer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="filter-event">{{ __('Evento') }}</label>
                        <select id="filter-event" name="event" class="form-select">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach (['created', 'updated', 'deleted'] as $event)
                                <option value="{{ $event }}" {{ ($filters['event'] ?? '') === $event ? 'selected' : '' }}>
                                    {{ Str::headline($event) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="filter-from">{{ __('Desde') }}</label>
                        <input type="date" id="filter-from" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control" />
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="filter-to">{{ __('Hasta') }}</label>
                        <input type="date" id="filter-to" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control" />
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="ti tabler-filter me-1"></i>{{ __('Aplicar') }}</button>
                        <a class="btn btn-label-secondary" href="{{ route('admin.activity.index') }}">{{ __('Limpiar') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">{{ __('Actividad reciente') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Fecha') }}</th>
                                <th>{{ __('Evento') }}</th>
                                <th>{{ __('Entidad') }}</th>
                                <th>{{ __('Descripción') }}</th>
                                <th>{{ __('Usuario') }}</th>
                                <th>{{ __('Cambios') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="text-nowrap">
                                        <span class="fw-medium d-block">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                        <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-primary text-capitalize">{{ $log->event ?? __('N/D') }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-medium d-block">
                                            {{ __($knownSubjects[$log->subject_type] ?? class_basename($log->subject_type)) }}
                                        </span>
                                        <small class="text-muted">ID: {{ $log->subject_id ?? '—' }}</small>
                                    </td>
                                    <td>{{ $log->description ?? '—' }}</td>
                                    <td>
                                        @if ($log->causer)
                                            <span class="fw-medium d-block">{{ $log->causer->name }}</span>
                                            <small class="text-muted">{{ $log->causer->email }}</small>
                                        @else
                                            <span class="text-muted">{{ __('Sistema') }}</span>
                                        @endif
                                    </td>
                                    <td style="min-width: 240px;">
                                        @php $properties = $log->properties ?? []; @endphp
                                        @if (empty($properties))
                                            <span class="text-muted">{{ __('Sin cambios registrados') }}</span>
                                        @else
                                            <ul class="list-unstyled small mb-0">
                                                @foreach ($properties as $key => $value)
                                                    <li>
                                                        <span class="fw-medium">{{ Str::headline($key) }}:</span>
                                                        @if (is_array($value) && array_key_exists('old', $value) && array_key_exists('new', $value))
                                                            <span class="text-muted">{{ __(':old → :new', ['old' => $value['old'] ?? '—', 'new' => $value['new'] ?? '—']) }}</span>
                                                        @else
                                                            <span class="text-muted">{{ is_scalar($value) ? $value : json_encode($value) }}</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-muted">{{ __('No hay registros de actividad con los filtros seleccionados.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($logs->hasPages())
                <div class="card-footer d-flex justify-content-end">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
