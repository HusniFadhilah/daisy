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
            <h2>Selamat Datang Kembali, {{ $authUser->name ?? 'Dr. Eng. Maryono' }}! 👋</h2>
            {{-- <p class="mb-0">Anda memiliki {{ $penawaranBaru ?? 2 }} penawaran baru, dan {{ $penugasanAktif ?? 1 }} tugas aktif. Mari kita selesaikan tugas ini dengan senyum 😊</p> --}}
            <p class="mb-0">Role Anda adalah {{ $authUser->role_alias }}</p>
        </div>
    </section>

    <!-- Stats Grid -->
    {{-- <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Penawaran Menunggu</div>
                        <div class="stat-value">{{ $stats['penawaran'] ?? 2 }}
</div>
<div class="stat-change">↑ {{ $stats['penawaran'] ?? 2 }} penawaran baru</div>
</div>
<div class="stat-icon">📨</div>
</div>
<div class="progress">
    <div class="progress-bar" role="progressbar" style="width: 40%" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>

<div class="col-12 col-md-6 col-lg-4 col-xl-3">
    <div class="stat-card success">
        <div class="stat-header">
            <div>
                <div class="stat-title">Penugasan Aktif</div>
                <div class="stat-value">{{ $stats['penugasan_aktif'] ?? 1 }}</div>
                <div class="stat-change">Progress {{ $stats['progress'] ?? 65 }}%</div>
            </div>
            <div class="stat-icon">✅</div>
        </div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stats['progress'] ?? 65 }}%" aria-valuenow="{{ $stats['progress'] ?? 65 }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 col-lg-4 col-xl-3">
    <div class="stat-card warning">
        <div class="stat-header">
            <div>
                <div class="stat-title">Proses AK Berlangsung</div>
                <div class="stat-value">{{ $stats['proses_ak'] ?? 1 }}</div>
                <div class="stat-change">Deadline {{ $stats['deadline_days'] ?? 12 }} hari lagi</div>
            </div>
            <div class="stat-icon">⏳</div>
        </div>
        <div class="progress">
            <div class="progress-bar bg-warning" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 col-lg-4 col-xl-3">
    <div class="stat-card info">
        <div class="stat-header">
            <div>
                <div class="stat-title">Total Selesai Tahun Ini</div>
                <div class="stat-value">{{ $stats['total_selesai'] ?? 24 }}</div>
                <div class="stat-change">↑ {{ $stats['persentase_kenaikan'] ?? 12 }}% dari tahun lalu</div>
            </div>
            <div class="stat-icon">🎯</div>
        </div>
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
</div>
</div> --}}

{{-- <!-- Additional Stats -->
<div class="row g-4">
    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Total Program Studi Dinilai</div>
                    <div class="stat-value">{{ $additionalStats['total_prodi'] ?? 156 }}
</div>
<div class="stat-change">↑ Sepanjang karir</div>
</div>
<div class="stat-icon">🎓</div>
</div>
</div>
</div>

<div class="col-12 col-md-6 col-lg-4 col-xl-3">
    <div class="stat-card success">
        <div class="stat-header">
            <div>
                <div class="stat-title">Tingkat Akurasi</div>
                <div class="stat-value">{{ $additionalStats['akurasi'] ?? '98.5' }}%</div>
                <div class="stat-change">↑ Sangat baik</div>
            </div>
            <div class="stat-icon">⭐</div>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 col-lg-4 col-xl-3">
    <div class="stat-card info">
        <div class="stat-header">
            <div>
                <div class="stat-title">Waktu Rata-rata AK</div>
                <div class="stat-value" style="font-size: 28px;">{{ $additionalStats['waktu_rata'] ?? '5.2' }} hari</div>
                <div class="stat-change">↓ Lebih cepat {{ $additionalStats['peningkatan'] ?? 15 }}%</div>
            </div>
            <div class="stat-icon">⚡</div>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 col-lg-4 col-xl-3">
    <div class="stat-card warning">
        <div class="stat-header">
            <div>
                <div class="stat-title">Pesan Belum Dibaca</div>
                <div class="stat-value">{{ $additionalStats['unread_messages'] ?? 3 }}</div>
                <div class="stat-change">Butuh perhatian</div>
            </div>
            <div class="stat-icon">📬</div>
        </div>
    </div>
</div>
</div> --}}

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
                    <a href="{{ route('change.password.first') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-check-circle me-2"></i>Ya, Ganti Sekarang
                    </a>
                    <form action="{{ route('change.password.skip') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-lg w-100">
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
