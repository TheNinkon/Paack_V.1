@php use Illuminate\Support\Str; @endphp

<form action="{{ route('app.parcels.update', $parcel) }}" method="POST" data-edit-form data-google-maps-key="{{ $mapsApiKey ?? '' }}">
    @csrf
    @method('PATCH')

    <div class="alert alert-danger d-none" role="alert" data-form-errors></div>
    <div class="alert alert-success d-none" role="alert" data-form-success></div>

    <div class="mb-4">
        <p class="text-muted text-uppercase small mb-1">{{ __('Código del bulto') }}</p>
        <div class="d-flex align-items-center gap-2">
            <span class="fw-semibold fs-5">{{ $parcel->code }}</span>
            <span class="badge bg-label-{{ ($parcel->status === 'returned') ? 'danger' : 'primary' }}">{{ __(Str::headline($parcel->status ?? 'pending')) }}</span>
        </div>
        @if ($parcel->created_at)
            <small class="text-muted">{{ __('Creado :time', ['time' => $parcel->created_at->diffForHumans()]) }}</small>
        @endif
    </div>

    @include('App.Parcels.partials.edit-form-fields')

    <div class="d-flex justify-content-end gap-3 mt-4">
        <button type="button" class="btn btn-label-secondary" data-modal-dismiss>{{ __('Cancelar') }}</button>
        <button type="submit" class="btn btn-primary" data-submit>
            <span data-submit-label>{{ __('Guardar cambios') }}</span>
        </button>
    </div>
</form>
