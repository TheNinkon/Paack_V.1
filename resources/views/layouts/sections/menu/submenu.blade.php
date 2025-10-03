@php
use Illuminate\Support\Facades\Route;
@endphp

<ul class="menu-sub">
  @if (!empty($menu))
    @foreach ($menu as $submenu)

    {{-- active menu method --}}
    @php
      $activeClass = null;
      $active = $configData["layout"] === 'vertical' ? 'active open':'active';
      $currentRouteName =  Route::currentRouteName();

      $submenuSlug = $submenu['slug'] ?? null;
      $submenuChildren = $submenu['submenu'] ?? [];

      if ($submenuSlug && $currentRouteName === $submenuSlug) {
          $activeClass = 'active';
      }
      elseif (!empty($submenuChildren)) {
        if (gettype($submenuSlug) === 'array') {
          foreach($submenuSlug as $slug){
            if (str_contains($currentRouteName,$slug) and strpos($currentRouteName,$slug) === 0) {
                $activeClass = $active;
            }
          }
        }
        else{
          if ($submenuSlug && str_contains($currentRouteName,$submenuSlug) and strpos($currentRouteName,$submenuSlug) === 0) {
            $activeClass = $active;
          }
        }
      }
    @endphp

      <li class="menu-item {{$activeClass}}">
        <a href="{{ $submenu['url'] ?? 'javascript:void(0)' }}" class="{{ !empty($submenuChildren) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (!empty($submenu['target'])) target="_blank" @endif>
          @if (!empty($submenu['icon']))
          <i class="{{ $submenu['icon'] }}"></i>
          @endif
          <div>{{ isset($submenu['name']) ? __($submenu['name']) : '' }}</div>
          @isset($submenu['badge'])
            <div class="badge bg-{{ $submenu['badge'][0] }} rounded-pill ms-auto">{{ $submenu['badge'][1] }}</div>
          @endisset
        </a>

        {{-- submenu --}}
        @if (!empty($submenuChildren))
          @include('layouts.sections.menu.submenu',['menu' => $submenuChildren])
        @endif
      </li>
    @endforeach
  @endif
</ul>
