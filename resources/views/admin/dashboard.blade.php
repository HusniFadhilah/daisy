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
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Penawaran Menunggu</div>
                        <div class="stat-value">{{ $stats['penawaran'] ?? 0 }}
                        </div>
                        <div class="stat-change">↑ {{ $stats['penawaran'] ?? 0 }} penawaran baru</div>
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
                        <div class="stat-value">{{ $stats['penugasan_aktif'] ?? 0 }}</div>
                        <div class="stat-change">Progress {{ $stats['progress'] ?? 0 }}%</div>
                    </div>
                    <div class="stat-icon">✅</div>
                </div>
                <div class="progress">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stats['progress'] ?? 0 }}%" aria-valuenow="{{ $stats['progress'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="stat-card warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Proses AK Berlangsung</div>
                        <div class="stat-value">{{ $stats['proses_ak'] ?? 0 }}</div>
                        <div class="stat-change">Deadline {{ $stats['deadline_days'] ?? 0 }} hari lagi</div>
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
                        <div class="stat-value">{{ $stats['total_selesai'] ?? 0 }}</div>
                        <div class="stat-change">↑ {{ $stats['persentase_kenaikan'] ?? 0 }}% dari tahun lalu</div>
                    </div>
                    <div class="stat-icon">🎯</div>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>

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
@endsection
