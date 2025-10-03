<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0F172A">

    <title>{{ config('app.name', 'Courier') }}</title>

    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" href="{{ asset('icons/icon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/courier.css', 'resources/js/courier.js'])
    @stack('head')
  </head>
  <body class="h-full w-full bg-slate-950 text-slate-100 antialiased">
    <div id="courier-app" class="flex h-full w-full flex-col">
      @yield('content')
    </div>

    @stack('modals')
    @stack('scripts')
  </body>
</html>
