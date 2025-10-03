@php
    $pageConfigs = ['myLayout' => 'blank'];
    $customizerHidden = 'customizer-hide';
    $templateName = config('variables.templateName', config('app.name', 'GiGi Routing'));
@endphp

@extends('layouts/blankLayout')

@section('title', __('Log in'))

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/@form-validation/form-validation.scss',
    ])
@endsection

@section('page-style')
    @vite([
        'resources/assets/vendor/scss/pages/page-auth.scss',
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/@form-validation/popular.js',
        'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
        'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    ])
@endsection

@section('page-script')
    @vite([
        'resources/assets/js/pages-auth.js',
    ])
@endsection

@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-6">
                <div class="card">
                    <div class="card-body">
                        <div class="app-brand justify-content-center mb-6">
                            <a href="{{ url('/') }}" class="app-brand-link">
                                <span class="app-brand-logo demo">@include('_partials.macros')</span>
                                <span class="app-brand-text demo text-heading fw-bold">{{ $templateName }}</span>
                            </a>
                        </div>

                        <h4 class="mb-1">{{ __('Welcome to :app!', ['app' => $templateName]) }} 👋</h4>
                        <p class="mb-6">{{ __('Sign in to continue managing your routes and operations.') }}</p>

                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form id="formAuthentication" class="mb-4" method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-6 form-control-validation">
                                <label for="email" class="form-label">{{ __('Email') }}</label>
                                <input
                                    type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="{{ __('Enter your email') }}"
                                    required
                                    autofocus
                                    autocomplete="username"
                                />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-6 form-password-toggle form-control-validation">
                                <label class="form-label" for="password">{{ __('Password') }}</label>
                                <div class="input-group input-group-merge">
                                    <input
                                        type="password"
                                        id="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        name="password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        aria-describedby="password"
                                        required
                                        autocomplete="current-password"
                                    />
                                    <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="my-8">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="form-check mb-0 ms-2">
                                        <input class="form-check-input" type="checkbox" id="remember-me" name="remember" {{ old('remember') ? 'checked' : '' }} />
                                        <label class="form-check-label" for="remember-me"> {{ __('Remember me') }} </label>
                                    </div>
                                    @if (\Illuminate\Support\Facades\Route::has('password.request'))
                                        <a href="{{ route('password.request') }}">
                                            <p class="mb-0">{{ __('Forgot your password?') }}</p>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-6">
                                <button class="btn btn-primary d-grid w-100" type="submit">{{ __('Log in') }}</button>
                            </div>
                        </form>

                        <p class="text-center mb-0">{{ __('Account creation is handled by your administrator.') }}</p>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
