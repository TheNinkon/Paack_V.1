@php
  use Illuminate\Support\Facades\Route;
  $configData = Helper::appClasses();
@endphp
<!-- Horizontal Menu -->
<aside id="layout-menu" class="layout-menu-horizontal menu-horizontal  menu bg-menu-theme flex-grow-0"
  @foreach ($configData['menuAttributes'] as $attribute => $value)
  {{ $attribute }}="{{ $value }}" @endforeach>
  <div class="{{ $containerNav }} d-flex h-100">
    <ul class="menu-inner">
      @foreach ($menuData[1]['menu'] ?? [] as $menu)
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
                          $activeClass = 'active';
                      }
                  }
              } else {
                  if ($menuSlug && str_contains($currentRouteName, $menuSlug) and strpos($currentRouteName, $menuSlug) === 0) {
                      $activeClass = 'active';
                  }
              }
          }
        @endphp

        {{-- main menu --}}
        <li class="menu-item {{ $activeClass }}">
          <a href="{{ $menu['url'] ?? 'javascript:void(0);' }}"
            class="{{ !empty($submenuItems) ? 'menu-link menu-toggle' : 'menu-link' }}"
            @if (!empty($menu['target'])) target="_blank" @endif>
            @isset($menu['icon'])
              <i class="{{ $menu['icon'] }}"></i>
            @endisset
            <div>{{ isset($menu['name']) ? __($menu['name']) : '' }}</div>
          </a>

          {{-- submenu --}}
          @if (!empty($submenuItems))
            @include('layouts.sections.menu.submenu', ['menu' => $submenuItems])
          @endif
        </li>
      @endforeach
    </ul>
  </div>
</aside>
<!--/ Horizontal Menu -->
