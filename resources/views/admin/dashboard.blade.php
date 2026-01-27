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
            <h2>Selamat Datang Kembali, {{ $authUser->name }}! 👋</h2>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <p class="mb-0">Role Anda adalah <span class="badge bg-primary">{{ $authUser->role_alias }}</span></p>

                @if($authUser->id_university && $authUser->university)
                <span class="text-muted">|</span>
                <p class="mb-0">
                    <i class="bi bi-building me-1"></i>
                    <strong>{{ $authUser->university->name }}</strong>
                </p>
                @endif
            </div>
        </div>
    </section>

    <!-- Stats Grid -->
    @if(in_array($authUser->role_selected,['super_admin','asesi']))
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="stat-card warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Pengingat Masa Akreditasi</div>
                        <div class="stat-value">{{ $stats['penawaran'] ?? 0 }}</div>
                        <small>PS yang perlu diingatkan tentang masa akreditasi berakhir dalam 7 bulan dari sekarang</small>
                    </div>
                    <div class="stat-icon">📨</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="stat-card info">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Penerimaan Dokumen Akreditasi Prodi</div>
                        <div class="stat-value">{{ $stats['penerimaan_dokumen'] ?? 0 }}</div>
                        <small>Jumlah dokumen LED+Suplemen, dan LKPS yang telah diterima dari prodi</small>
                    </div>
                    <div class="stat-icon"><i class="bi bi-file-earmark-check text-white"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Penawaran Menunggu</div>
                        <div class="stat-value">{{ $stats['penawaran_menunggu'] ?? 0 }}</div>
                        <small>Validator/Asesor yang telah ditugaskan, tetapi statusnya masih menunggu diterima</small>
                    </div>
                    <div class="stat-icon"><i class="bi bi-hourglass-split text-white"></i></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="stat-card success">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Penugasan Aktif</div>
                        <div class="stat-value">{{ $stats['penugasan_aktif'] ?? 0 }}</div>
                        <small>Validator/Asesor yang telah ditugaskan, dan sedang melaksanakan asesmen</small>
                    </div>
                    <div class="stat-icon">✅</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="stat-card warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Proses AK Berlangsung</div>
                        <div class="stat-value">{{ $stats['proses_ak'] ?? 0 }}</div>
                        <small>Jumlah Asesmen Kecukupan yang sedang berlangsung</small>
                    </div>
                    <div class="stat-icon">⏳</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="stat-card primary">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Proses AL Berlangsung</div>
                        <div class="stat-value">{{ $stats['proses_al'] ?? 0 }}</div>
                        <small>Jumlah Asesmen Lapangan yang sedang berlangsung</small>
                    </div>
                    <div class="stat-icon">⏳</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="stat-card info">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Total Selesai Tahun Ini</div>
                        <div class="stat-value">{{ $stats['total_selesai'] ?? 0 }}</div>
                        <small>Jumlah Permohonan Akreditasi yang selesai di tahun ini</small>
                    </div>
                    <div class="stat-icon">🎯</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(in_array($authUser->role_selected,['validator','asesor']))
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Penawaran Menunggu</div>
                        <div class="stat-value">{{ $stats['penawaran'] ?? 0 }}</div>
                    </div>
                    <div class="stat-icon">📨</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="stat-card info">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Penugasan Aktif</div>
                        <div class="stat-value">{{ $stats['penugasan_aktif'] ?? 0 }}</div>
                    </div>
                    <div class="stat-icon">⏳</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="stat-card success">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Penugasan Selesai</div>
                        <div class="stat-value">{{ $stats['penugasan_selesai'] ?? 0 }}</div>
                    </div>
                    <div class="stat-icon">✅</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(in_array($authUser->role_selected,['admin_prodi','admin_univ']))
    @include('admin.upps-dashboard')
    @endif

    @if(in_array($authUser->role_selected,['keuangan_lamdepilar']))
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Pembayaran Perlu Diverifikasi</div>
                        <div class="stat-value">{{ $stats['perlu_diverifikasi'] ?? 0 }}</div>
                    </div>
                    <div class="stat-icon">⏳</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="stat-card success">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Total Pembayaran Selesai Diverifikasi</div>
                        <div class="stat-value">{{ $stats['selesai_diverifikasi'] ?? 0 }}</div>
                    </div>
                    <div class="stat-icon">✅</div>
                </div>
            </div>
        </div>
    </div>
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
