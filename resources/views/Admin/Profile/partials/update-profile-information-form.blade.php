<div class="card h-100">
    <div class="card-header">
        <h5 class="mb-1">{{ __('Profile Information') }}</h5>
        <p class="mb-0 text-muted">{{ __("Update your account's profile information and email address.") }}</p>
    </div>
    <div class="card-body">
        <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-none">
            @csrf
        </form>

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti tabler-check me-2"></i>{{ __('Profile updated successfully.') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="post" action="{{ route('profile.update') }}" class="row g-6">
            @csrf
            @method('patch')

            <div class="col-12 col-md-6">
                <label for="name" class="form-label">{{ __('Name') }}</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name) }}"
                    required
                    autocomplete="name"
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $user->email) }}"
                    required
                    autocomplete="username"
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="alert alert-warning mt-4" role="alert">
                        <div class="d-flex">
                            <i class="ti tabler-alert-triangle me-2"></i>
                            <div>
                                <p class="mb-2">{{ __('Your email address is unverified.') }}</p>
                                <button form="send-verification" class="btn btn-sm btn-outline-warning">
                                    {{ __('Click here to re-send the verification email.') }}
                                </button>

                                @if (session('status') === 'verification-link-sent')
                                    <span class="d-block mt-2 text-success">{{ __('A new verification link has been sent to your email address.') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
            </div>
        </form>
    </div>
</div>
