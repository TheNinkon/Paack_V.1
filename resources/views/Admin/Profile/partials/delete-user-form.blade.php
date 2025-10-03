@php
    $userDeletionErrors = $errors->userDeletion ?? null;
@endphp

<div class="card">
    <div class="card-header border-bottom">
        <h5 class="mb-1 text-danger">{{ __('Delete Account') }}</h5>
        <p class="mb-0 text-muted">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}</p>
    </div>
    <div class="card-body">
        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalConfirmUserDeletion">
            <i class="ti tabler-trash me-1"></i>{{ __('Delete Account') }}
        </button>
    </div>
</div>

<div class="modal fade" id="modalConfirmUserDeletion" tabindex="-1" aria-labelledby="modalConfirmUserDeletionLabel" aria-hidden="true" data-has-errors="{{ $userDeletionErrors && $userDeletionErrors->isNotEmpty() ? 'true' : 'false' }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmUserDeletionLabel">{{ __('Confirm account deletion') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <div class="modal-body">
                    <p class="mb-4">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}</p>

                    <div class="mb-3">
                        <label for="delete_account_password" class="form-label">{{ __('Password') }}</label>
                        <input
                            type="password"
                            id="delete_account_password"
                            name="password"
                            class="form-control @if ($userDeletionErrors && $userDeletionErrors->has('password')) is-invalid @endif"
                            placeholder="{{ __('Enter your password') }}"
                            autofocus
                        >
                        @if ($userDeletionErrors && $userDeletionErrors->has('password'))
                            <div class="invalid-feedback">{{ $userDeletionErrors->first('password') }}</div>
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('Delete Account') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
