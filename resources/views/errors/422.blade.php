@extends('errors::custom-illustration')

@section('title', 'Validasi Belum Dapat Dilakukan')
@section('code', '422')

@section('illustration', 'https://demos.creative-tim.com/soft-ui-dashboard-pro/assets/img/illustrations/rocket-white.png')

@section('message')
@php
$defaultMessage = 'Validasi penilaian belum dapat dilakukan. Pastikan semua persyaratan terpenuhi sebelum melanjutkan.';
$message = $exception ? $exception->getMessage() : $defaultMessage;
@endphp

<div class="error-message-422">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    {{ $message }}
</div>

@if(request()->has('pending_count'))
<div class="mt-4">
    <div class="alert alert-warning">
        <h6 class="alert-heading">
            <i class="bi bi-clock-history"></i> Informasi Tambahan
        </h6>
        <p class="mb-0">
            Terdapat <strong>{{ request()->get('pending_count') }}</strong> asesor yang belum menyelesaikan penilaian.
            Silakan tunggu hingga semua asesor submit penilaian mereka.
        </p>
    </div>
</div>
@endif
@endsection

@section('actions')
<a href="{{ route('ak.validasi.index') }}" class="btn btn-primary">
    <i class="bi bi-arrow-left"></i>
    <span>Kembali ke Dashboard Validasi</span>
</a>

<button onclick="window.location.reload()" class="btn btn-secondary">
    <i class="bi bi-arrow-clockwise"></i>
    <span>Refresh Halaman</span>
</button>

@auth
<a href="{{ route('dashboard') }}" class="btn-outline-secondary">
    <i class="bi bi-speedometer2"></i>
    <span>Dashboard Utama</span>
</a>
@endauth
@endsection

@section('meta')
<div class="info-box mt-4">
    <h6 class="mb-3"><i class="bi bi-info-circle"></i> Persyaratan Validasi:</h6>
    <ul class="requirements-list">
        <li>
            <i class="bi bi-check-circle text-muted"></i>
            Minimal <strong>2 asesor</strong> harus di-assign untuk asesmen
        </li>
        <li>
            <i class="bi bi-check-circle text-muted"></i>
            Semua asesor harus <strong>menerima penawaran</strong> (accepted)
        </li>
        <li>
            <i class="bi bi-check-circle text-muted"></i>
            Semua asesor harus <strong>submit penilaian</strong> mereka
        </li>
        <li>
            <i class="bi bi-check-circle text-muted"></i>
            Anda harus memiliki <strong>akses sebagai validator</strong>
        </li>
    </ul>
</div>

<div class="help-text mt-4">
    <small class="text-muted">
        <i class="bi bi-question-circle"></i>
        Jika Anda memerlukan bantuan atau ada kendala, silakan hubungi Sekretariat LAMDEPILAR.
    </small>
</div>
@endsection

@push('styles')
<style>
    .error-message-422 {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border-left: 5px solid #ffc107;
        padding: 1.5rem;
        border-radius: 12px;
        display: inline-block;
        color: #856404;
        font-size: 1.1rem;
        font-weight: 500;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.2);
        max-width: 600px;
        margin: 0 auto;
    }

    .error-message-422 i {
        font-size: 1.3rem;
        vertical-align: middle;
    }

    .info-box {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        border: 2px solid #e9ecef;
    }

    .info-box h6 {
        color: #495057;
        font-weight: 600;
    }

    .requirements-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }

    .requirements-list li {
        padding: 0.75rem 0;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .requirements-list li:last-child {
        border-bottom: none;
    }

    .requirements-list li i {
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .help-text {
        padding: 1rem;
        background: #e7f3ff;
        border-left: 3px solid #2196f3;
        border-radius: 6px;
    }

    .btn-outline-secondary {
        border: 2px solid #6c757d;
        color: #6c757d;
        background: transparent;
        padding: 0.5rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-outline-secondary:hover {
        background: #6c757d;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    }

    .alert-warning {
        border-left: 4px solid #ffc107;
        background: #fff9e6;
        border-radius: 8px;
    }

    .alert-heading {
        color: #856404;
        margin-bottom: 0.5rem;
    }

</style>
@endpush

@push('scripts')
<script>
    // Auto refresh setiap 30 detik untuk cek status terbaru
    let countdown = 30;
    let countdownInterval;

    function startCountdown() {
        const refreshBtn = document.querySelector('button[onclick*="reload"]');
        if (!refreshBtn) return;

        const originalText = refreshBtn.innerHTML;

        countdownInterval = setInterval(() => {
            countdown--;
            refreshBtn.innerHTML = `
                <i class="bi bi-arrow-clockwise"></i>
                <span>Auto Refresh (${countdown}s)</span>
            `;

            if (countdown <= 0) {
                clearInterval(countdownInterval);
                window.location.reload();
            }
        }, 1000);
    }

    // Start countdown on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Show toast notification
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning'
                , title: 'Validasi Belum Dapat Dilakukan'
                , text: 'Halaman akan otomatis refresh setiap 30 detik untuk cek status terbaru.'
                , toast: true
                , position: 'top-end'
                , showConfirmButton: false
                , timer: 5000
                , timerProgressBar: true
            , });
        }

        // Start auto refresh countdown
        startCountdown();
    });

</script>
@endpush
