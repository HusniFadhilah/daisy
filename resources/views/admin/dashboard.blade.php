@php
$authUser = auth()->user();
@endphp
@extends('layouts.template.app')

@section('title', 'Dashboard ' . $authUser->role_alias . ' - DAISY')

@section('content')
<div class="container-fluid py-3">
    <!-- Welcome Section -->
    <section class="welcome-section">
        <div class="welcome-content">
            <h2>Selamat Datang Kembali 👋</h2>
            <p class="mb-0">Anda bersama Sistem Informasi Akreditasi LAMDEPILAR (DAISY)</p>
        </div>
    </section>

    <!-- Stats Grid -->
    @if(in_array($authUser->role_selected,['super_admin','asesi']))
    @include('admin.admin-dashboard')
    @endif

    @if(in_array($authUser->role_selected,['asesor']))
    @include('admin.asesor-dashboard')
    @endif

    @if(in_array($authUser->role_selected,['validator']))
    @include('admin.validator-dashboard')
    @endif

    @if(in_array($authUser->role_selected,['admin_prodi','admin_univ']))
    @include('admin.upps-dashboard')
    @endif

    @if(in_array($authUser->role_selected,['keuangan_lamdepilar']))
    @include('admin.keuangan-dashboard')
    @endif
</div>

<!-- Modal Notifikasi Ganti Password -->
@if(auth()->check() && auth()->user()->must_change_password)
<div class="modal fade" id="changePasswordModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-key fa-3x text-warning"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-3">Ganti Password?</h4>
                <p class="text-muted mb-4">
                    Untuk keamanan akun Anda, disarankan untuk mengganti password default.<br>
                    Apakah Anda ingin mengganti password sekarang?
                </p>
                <div class="d-grid gap-2">
                    <a href="{{ route('change.password.first') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-check-circle me-2"></i>Ya, Ganti Sekarang
                    </a>
                    <form action="{{ route('change.password.skip') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-times-circle me-2"></i>Tidak, Nanti Saja
                        </button>
                    </form>
                </div>
                <p class="text-muted small mt-3 mb-0">
                    <i class="fas fa-info-circle me-1"></i>Anda dapat mengganti password kapan saja dari menu profil
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var changePasswordModal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
        changePasswordModal.show();
    });

</script>
@endif
@endsection
