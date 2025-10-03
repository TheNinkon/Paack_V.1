@php
    $passwordErrors = $errors->updatePassword ?? null;
@endphp

<div class="card h-100">
    <div class="card-header">
        <h5 class="mb-1">{{ __('Update Password') }}</h5>
        <p class="mb-0 text-muted">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
    </div>
    <div class="card-body">
        @if (session('status') === 'password-updated')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti tabler-check me-2"></i>{{ __('Password updated successfully.') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="post" action="{{ route('password.update') }}" class="row g-4">
            @csrf
            @method('put')

            <div class="col-12">
                <label for="update_password_current_password" class="form-label">{{ __('Current Password') }}</label>
                <input
                    type="password"
                    id="update_password_current_password"
                    name="current_password"
                    class="form-control @if ($passwordErrors && $passwordErrors->has('current_password')) is-invalid @endif"
                    autocomplete="current-password"
                >
                @if ($passwordErrors && $passwordErrors->has('current_password'))
                    <div class="invalid-feedback">{{ $passwordErrors->first('current_password') }}</div>
                @endif
            </div>

            <div class="col-12 col-md-6">
                <label for="update_password_password" class="form-label">{{ __('New Password') }}</label>
                <input
                    type="password"
                    id="update_password_password"
                    name="password"
                    class="form-control @if ($passwordErrors && $passwordErrors->has('password')) is-invalid @endif"
                    autocomplete="new-password"
                >
                @if ($passwordErrors && $passwordErrors->has('password'))
                    <div class="invalid-feedback">{{ $passwordErrors->first('password') }}</div>
                @endif
            </div>

            <div class="col-12 col-md-6">
                <label for="update_password_password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                <input
                    type="password"
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    class="form-control @if ($passwordErrors && $passwordErrors->has('password_confirmation')) is-invalid @endif"
                    autocomplete="new-password"
                >
                @if ($passwordErrors && $passwordErrors->has('password_confirmation'))
                    <div class="invalid-feedback">{{ $passwordErrors->first('password_confirmation') }}</div>
                @endif
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
            </div>
        </form>
    </div>
</div>
