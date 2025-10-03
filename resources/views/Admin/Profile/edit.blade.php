@php
    $pageConfigs = ['myLayout' => 'contentNavbar'];
@endphp

@extends('layouts/layoutMaster')

@section('title', __('Profile Settings'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-6">
            <div class="col-12 col-xl-8">
                @include('Admin.Profile.partials.update-profile-information-form')
            </div>
            <div class="col-12 col-xl-4">
                @include('Admin.Profile.partials.update-password-form')
            </div>
        </div>

        <div class="row g-6 mt-0">
            <div class="col-12">
                @include('Admin.Profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deletionModal = document.getElementById('modalConfirmUserDeletion');

            if (!deletionModal) {
                return;
            }

            const hasErrors = deletionModal.getAttribute('data-has-errors') === 'true';

            if (hasErrors && window.bootstrap) {
                const modalInstance = window.bootstrap.Modal.getOrCreateInstance(deletionModal);
                modalInstance.show();
            }
        });
    </script>
@endsection
