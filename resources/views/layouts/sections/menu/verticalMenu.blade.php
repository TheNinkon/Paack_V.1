@php
use Illuminate\Support\Facades\Route;
$configData = Helper::appClasses();
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu" @foreach ($configData['menuAttributes'] as $attribute=>
  $value)
  {{ $attribute }}="{{ $value }}" @endforeach>

  <!-- ! Hide app brand if navbar-full -->
  @if (!isset($navbarFull))
  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link">
      <span class="app-brand-logo demo">@include('_partials.macros')</span>
      <span class="app-brand-text demo menu-text fw-bold ms-3">{{ config('variables.templateName') }}</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
      <i class="icon-base ti tabler-x d-block d-xl-none"></i>
    </a>
  </div>
  @endif

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    @foreach ($menuData[0]['menu'] ?? [] as $menu)
    {{-- adding active and open class if child is active --}}

    {{-- menu headers --}}
    @if (isset($menu['menuHeader']))
    <li class="menu-header small">
      <span class="menu-header-text">{{ __($menu['menuHeader']) }}</span>
    </li>
    @else
    {{-- active menu method --}}
    @php
    $activeClass = null;
    $currentRouteName = Route::currentRouteName();

    $menuSlug = $menu['slug'] ?? null;
    $submenuItems = $menu['submenu'] ?? [];

    if ($menuSlug && $currentRouteName === $menuSlug) {
    $activeClass = 'active';
    } elseif (!empty($submenuItems)) {
    if (is_array($menuSlug)) {
    foreach ($menuSlug as $slug) {
    if (str_contains($currentRouteName, $slug) and strpos($currentRouteName, $slug) === 0) {
    $activeClass = 'active open';
    }
    }
    } else {
    if (
    str_contains($currentRouteName, (string) $menuSlug) and
    strpos($currentRouteName, (string) $menuSlug) === 0
    ) {
    $activeClass = 'active open';
    }
    }
    }
    @endphp

    {{-- main menu --}}
    <li class="menu-item {{ $activeClass }}">
      <a href="{{ $menu['url'] ?? 'javascript:void(0);' }}"
        class="{{ !empty($submenuItems) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (!empty($menu['target'])) target="_blank" @endif>
        @isset($menu['icon'])
        <i class="{{ $menu['icon'] }}"></i>
        @endisset
        <div>{{ isset($menu['name']) ? __($menu['name']) : '' }}</div>
        @isset($menu['badge'])
        <div class="badge bg-{{ $menu['badge'][0] }} rounded-pill ms-auto">{{ $menu['badge'][1] }}</div>
        @endisset
      </a>

      {{-- submenu --}}
      @if (!empty($submenuItems))
      @include('layouts.sections.menu.submenu', ['menu' => $submenuItems])
      @endif
    </li>
    @endif
    @endforeach
  </ul>

</aside>
