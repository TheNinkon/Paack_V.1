@extends('courier.layout')

@php
    $statusStyles = [
        'pending' => 'bg-slate-800 text-slate-200 ring-1 ring-slate-700/60',
        'assigned' => 'bg-indigo-500/15 text-indigo-200 ring-1 ring-indigo-400/40',
        'out_for_delivery' => 'bg-amber-500/15 text-amber-200 ring-1 ring-amber-400/40',
        'delivered' => 'bg-emerald-500/15 text-emerald-200 ring-1 ring-emerald-400/40',
        'incident' => 'bg-rose-500/15 text-rose-200 ring-1 ring-rose-400/40',
        'returned' => 'bg-slate-700 text-slate-100 ring-1 ring-slate-600/60',
    ];
    $hasCourierProfile = isset($courier) && $courier;
    $courierActive = $hasCourierProfile && $courier->active;
@endphp

@section('content')
  <div class="flex h-full flex-1 flex-col">
    <section class="relative flex-1 overflow-hidden">
      <div id="courier-map" class="courier-map h-full w-full" data-google-maps-key="{{ $mapsApiKey ?? '' }}"></div>

      <div class="pointer-events-none absolute inset-0 flex flex-col justify-between">
        <header class="pointer-events-auto flex items-start justify-between px-6 pt-safe-top pb-6">
          <div class="rounded-full bg-slate-950/70 px-4 py-3 shadow-lg shadow-slate-950/30">
            <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('Repartidor') }}</p>
            <p class="text-base font-semibold text-white">{{ auth()->user()?->name }}</p>
          </div>
          <div class="flex items-center gap-3">
            <button id="courier-map-recenter" class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-950/80 text-white shadow-lg shadow-slate-950/40 hover:bg-slate-900">
              <i class="ti tabler-target text-xl"></i>
            </button>
            <button id="courier-refresh" class="flex items-center gap-2 rounded-full bg-slate-950/80 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-slate-950/30 hover:bg-slate-900">
              <i class="ti tabler-refresh"></i>
              {{ __('Actualizar') }}
            </button>
          </div>
        </header>

        <div class="pointer-events-auto px-6 pb-safe-bottom">
          <div class="courier-bottom-sheet collapsed rounded-3xl bg-slate-950/95 ring-1 ring-slate-800/80" data-sheet>
            <button type="button" class="courier-sheet-toggle" data-sheet-toggle aria-expanded="true">
              <i class="ti tabler-chevron-down"></i>
            </button>
            <div class="sheet-header flex items-center justify-between gap-4">
              <div>
                <h2 class="text-lg font-semibold text-white">{{ __('Paradas asignadas') }}</h2>
                <p class="text-sm text-slate-400">{{ trans_choice('{0}Sin paradas|{1}1 parada pendiente|[2,*]:count paradas pendientes', $counts['assigned'] + $counts['out_for_delivery'], ['count' => $counts['assigned'] + $counts['out_for_delivery']]) }}</p>
              </div>
              <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-2xl bg-indigo-500/15 px-3 py-2 text-indigo-200 ring-1 ring-indigo-400/30">
                  <p class="text-xs uppercase tracking-wide">{{ __('Asignados') }}</p>
                  <p class="text-lg font-semibold">{{ $counts['assigned'] }}</p>
                </div>
                <div class="rounded-2xl bg-amber-500/15 px-3 py-2 text-amber-200 ring-1 ring-amber-400/30">
                  <p class="text-xs uppercase tracking-wide">{{ __('En ruta') }}</p>
                  <p class="text-lg font-semibold">{{ $counts['out_for_delivery'] }}</p>
                </div>
              </div>
            </div>
            <div class="sheet-body mt-6 space-y-4" data-sheet-body>
              @if (! $hasCourierProfile)
                <div class="rounded-2xl border border-amber-400/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                  {{ __('Tu usuario aún no está vinculado como courier. Contacta a coordinación.') }}
                </div>
              @elseif (! $courierActive)
                <div class="rounded-2xl border border-rose-400/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
                  {{ __('Tu perfil de courier está inactivo. Solicita la reactivación antes de iniciar ruta.') }}
                </div>
              @elseif (! ($mapsApiKey ?? false))
                <div class="rounded-2xl border border-amber-400/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                  {{ __('No se ha configurado la clave de Google Maps para este cliente. Pide a coordinación que la añada en Configuración → Mapas.') }}
                </div>
              @endif

              <div class="hidden flex-col gap-3 rounded-3xl border border-slate-800 bg-slate-900/80 p-5 text-sm" data-selected-card>
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('Parada seleccionada') }}</p>
                    <p class="mt-1 text-lg font-semibold text-white" data-selected-code>—</p>
                    <p class="mt-2 text-sm text-slate-300" data-selected-address>—</p>
                    <p class="mt-1 text-xs text-slate-500" data-selected-provider></p>
                  </div>
                  <span class="rounded-full px-3 py-1 text-xs font-semibold bg-slate-800 text-slate-200" data-selected-status>—</span>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400">
                  <button class="flex items-center gap-2 rounded-full bg-indigo-500/10 px-4 py-2 font-semibold text-indigo-200 ring-1 ring-indigo-400/30 hover:bg-indigo-500/20" data-selected-action="mark-delivered">
                    <i class="ti tabler-checkbox text-base text-indigo-200"></i>
                    <span>{{ __('Confirmar entrega') }}</span>
                  </button>
                  <button class="flex items-center gap-2 rounded-full bg-amber-500/10 px-4 py-2 font-semibold text-amber-200 ring-1 ring-amber-400/30 hover:bg-amber-500/20" data-selected-action="report-issue">
                    <i class="ti tabler-alert-circle text-base text-amber-200"></i>
                    <span>{{ __('Reportar incidencia') }}</span>
                  </button>
                  <button class="flex items-center gap-2 rounded-full bg-slate-800 px-4 py-2 font-semibold text-slate-200 ring-1 ring-slate-700 hover:bg-slate-700" data-selected-action="open-navigation">
                    <i class="ti tabler-navigation text-base"></i>
                    <span>{{ __('Navegar') }}</span>
                  </button>
                </div>
              </div>

              <div class="max-h-[18rem] space-y-4 overflow-y-auto pr-1" id="courier-active-list">
                @forelse ($activeParcels as $parcel)
                  <article class="relative overflow-hidden rounded-3xl bg-slate-900/80 p-5 ring-1 ring-slate-800" data-code="{{ $parcel->code }}">
                    <div class="flex items-start justify-between gap-3">
                      <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('Parada') }}</p>
                        <p class="mt-1 text-lg font-semibold text-white">{{ $parcel->code }}</p>
                        <p class="mt-2 text-sm text-slate-300">
                          @if ($parcel->formatted_address)
                            {{ $parcel->formatted_address }}
                          @else
                            {{ $parcel->address_line ? $parcel->address_line . ', ' : '' }}{{ $parcel->city }}
                          @endif
                        </p>
                        @if ($parcel->provider)
                          <p class="mt-1 text-xs text-slate-500">{{ $parcel->provider->name }}</p>
                        @endif
                      </div>
                      <div class="flex flex-col items-end gap-2">
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyles[$parcel->status] ?? 'bg-slate-800 text-slate-200' }}">
                          {{ \Illuminate\Support\Str::headline($parcel->status) }}
                        </span>
                        @if ($parcel->stop_code)
                          <span class="rounded-full bg-slate-800/80 px-3 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-300">{{ __('Parada :stop', ['stop' => $parcel->stop_code]) }}</span>
                        @endif
                      </div>
                    </div>
                    <div class="mt-5 flex flex-wrap items-center gap-3 text-xs text-slate-400">
                      <button class="flex items-center gap-2 rounded-full bg-indigo-500/10 px-4 py-2 font-semibold text-indigo-200 ring-1 ring-indigo-400/30 hover:bg-indigo-500/20"
                        data-action="mark-delivered" data-code="{{ $parcel->code }}">
                        <i class="ti tabler-checkbox text-base text-indigo-200"></i>
                        <span>{{ __('Confirmar entrega') }}</span>
                      </button>
                      <button class="flex items-center gap-2 rounded-full bg-amber-500/10 px-4 py-2 font-semibold text-amber-200 ring-1 ring-amber-400/30 hover:bg-amber-500/20"
                        data-action="report-issue" data-code="{{ $parcel->code }}">
                        <i class="ti tabler-alert-circle text-base text-amber-200"></i>
                        <span>{{ __('Reportar incidencia') }}</span>
                      </button>
                      <button class="flex items-center gap-2 rounded-full bg-slate-800 px-4 py-2 font-semibold text-slate-200 ring-1 ring-slate-700 hover:bg-slate-700"
                        data-action="open-navigation" data-lat="{{ $parcel->latitude }}" data-lng="{{ $parcel->longitude }}">
                        <i class="ti tabler-navigation text-base"></i>
                        <span>{{ __('Navegar') }}</span>
                      </button>
                    </div>
                  </article>
                @empty
                  <div class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/70 p-6 text-center text-sm text-slate-400">
                    {{ __('No tienes bultos asignados ahora mismo. Actualiza o contacta a coordinación para recibir tu próxima ruta.') }}
                  </div>
                @endforelse
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="px-6 pb-28 pt-8 text-slate-100">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">{{ __('Últimas entregas') }}</h2>
        <a href="#" class="text-xs font-semibold text-indigo-300 hover:text-indigo-200">{{ __('Ver historial') }}</a>
      </div>
      @if ($completedParcels->isEmpty())
        <p class="mt-4 text-sm text-slate-400">{{ __('Aún no hay entregas confirmadas hoy.') }}</p>
      @else
        <ul class="mt-5 space-y-3" id="courier-completed-list">
          @foreach ($completedParcels as $parcel)
            <li class="flex items-center justify-between rounded-2xl bg-slate-900/70 px-4 py-3 ring-1 ring-slate-800">
              <div>
                <p class="text-sm font-semibold text-slate-100">{{ $parcel->code }}</p>
                <p class="text-xs text-slate-400">{{ $parcel->formatted_address ?? $parcel->address_line ?? __('Sin dirección registrada') }}</p>
              </div>
              <span class="rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-wide {{ $statusStyles[$parcel->status] ?? 'bg-slate-800 text-slate-200' }}">
                {{ \Illuminate\Support\Str::headline($parcel->status) }}
              </span>
            </li>
          @endforeach
        </ul>
      @endif
    </section>

    <nav class="pointer-events-auto fixed inset-x-0 bottom-0 z-20 select-none border-t border-slate-800/80 bg-slate-950/95 px-8 pb-safe-bottom">
      <div class="grid grid-cols-3 items-center gap-2 py-4 text-xs font-semibold text-slate-400">
        <button class="flex flex-col items-center gap-1 text-indigo-300">
          <i class="ti tabler-map-2 text-xl"></i>
          <span>{{ __('Mapa') }}</span>
        </button>
        <button class="flex flex-col items-center gap-1 text-slate-300">
          <i class="ti tabler-list-details text-xl"></i>
          <span>{{ __('Paradas') }}</span>
        </button>
        <a href="{{ route('profile.edit') }}" class="flex flex-col items-center gap-1 hover:text-slate-200">
          <i class="ti tabler-user text-xl"></i>
          <span>{{ __('Cuenta') }}</span>
        </a>
      </div>
    </nav>
  </div>
@endsection

@push('scripts')
  <script>
    window.courierInitialState = @json($initialState);
  </script>
@endpush
