@extends('layouts.template.app')

@section('title', 'Dashboard User - DAISY LAMDEPILAR')

@section('content')
<!-- Welcome Section -->
<section class="welcome-section">
    <div class="welcome-content">
        <h2>Selamat Datang Kembali, {{ auth()->user()->name }}! 👋</h2>
        <p>
            @if(auth()->user()->role_selected === 'sekretariat' || auth()->user()->role_selected === 'admin_prodi')
            Anda memiliki {{ $prosesAK ?? 0 }} proses akreditasi aktif. Mari selesaikan persiapan dengan baik 😊
            @elseif(auth()->user()->role_selected === 'asesor')
            Anda memiliki {{ $penawaranBaru ?? 0 }} penawaran baru, dan {{ $penugasanAktif ?? 0 }} tugas aktif. Mari kita selesaikan tugas ini dengan senyum 😊
            @elseif(auth()->user()->role_selected === 'validator')
            Anda memiliki {{ $penugasanAktif ?? 0 }} dokumen untuk divalidasi. Mari pastikan kualitas hasil asesmen 😊
            @elseif(auth()->user()->role_selected === 'verifikator')
            Anda memiliki {{ $penugasanAktif ?? 0 }} dokumen untuk validasi final. Mari pastikan keputusan yang tepat 😊
            @else
            Selamat bekerja dan semoga hari Anda menyenangkan 😊
            @endif
        </p>
        <div class="d-flex gap-2 flex-wrap">
            @if(auth()->user()->role_selected === 'asesor')
            @if(Route::has('penawaran.baru'))
            <a href="{{ route('penawaran.baru') }}" class="quick-btn">📨 Lihat Penawaran</a>
            @endif
            @if(Route::has('ak.berkas'))
            <a href="{{ route('ak.berkas') }}" class="quick-btn">📝 Lanjutkan Penilaian</a>
            @endif
            @elseif(auth()->user()->role_selected === 'validator' || auth()->user()->role_selected === 'verifikator')
            @if(Route::has('penugasan.aktif'))
            <a href="{{ route('penugasan.aktif') }}" class="quick-btn">📋 Tugas Aktif</a>
            @endif
            @elseif(auth()->user()->role_selected === 'sekretariat' || auth()->user()->role_selected === 'admin_prodi')
            <a href="{{ route('de.pemetaan.index') }}" class="quick-btn">📄 Kelola Dokumen</a>
            @endif
            @if(Route::has('laporan'))
            <a href="{{ route('laporan') }}" class="quick-btn">📊 Lihat Laporan</a>
            @endif
        </div>
    </div>
</section>

<!-- Stats Grid -->
<div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Penawaran Menunggu</div>
                    <div class="stat-value">{{ $stats['penawaran'] ?? 2 }}</div>
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
</div>

<!-- Content Grid -->
<div class="row g-4 mb-4">
    <!-- Recent Activities -->
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header custom-header">
                <h3>Aktivitas Terkini</h3>
                @if(Route::has('aktivitas'))
                <a href="{{ route('aktivitas') }}" class="card-link">Lihat Semua →</a>
                @endif
            </div>
            <div class="card-body">
                @forelse($recentActivities ?? [] as $activity)
                <div class="activity-item">
                    <div class="activity-icon {{ $activity->type }}">{{ $activity->icon }}</div>
                    <div>
                        <div class="activity-title">{{ $activity->title }}</div>
                        <div class="activity-description">{{ $activity->description }}</div>
                        <div class="activity-time">{{ $activity->time }}</div>
                    </div>
                </div>
                @empty
                <div class="activity-item">
                    <div class="activity-icon primary">📝</div>
                    <div>
                        <div class="activity-title">Hasil AK Berhasil Diunggah</div>
                        <div class="activity-description">
                            Anda telah mengunggah hasil penilaian AK untuk Program Studi S2 Ilmu Lingkungan - Universitas Diponegoro
                        </div>
                        <div class="activity-time">2 jam yang lalu</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon warning">⚠️</div>
                    <div>
                        <div class="activity-title">Terdeteksi Split Nilai</div>
                        <div class="activity-description">
                            Terdapat 3 deskriptor dengan perbedaan penilaian. Silakan lakukan rekonsiliasi dengan partner asesor.
                        </div>
                        <div class="activity-time">5 jam yang lalu</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon success">✉️</div>
                    <div>
                        <div class="activity-title">Penawaran Asesmen Diterima</div>
                        <div class="activity-description">
                            Anda telah menerima penawaran asesmen untuk S1 Teknik Informatika - Universitas Bina Nusantara
                        </div>
                        <div class="activity-time">1 hari yang lalu</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon primary">💬</div>
                    <div>
                        <div class="activity-title">Pesan Baru dari Partner</div>
                        <div class="activity-description">
                            Dr. Paulus mengirim pesan terkait rekonsiliasi nilai pada butir F1|81|8.3.1
                        </div>
                        <div class="activity-time">1 hari yang lalu</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon success">📊</div>
                    <div>
                        <div class="activity-title">Validasi AK Selesai</div>
                        <div class="activity-description">
                            Hasil AK untuk S1 Manajemen - Universitas Pelita Harapan telah divalidasi oleh Dewan Eksekutif
                        </div>
                        <div class="activity-time">2 hari yang lalu</div>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Upcoming Tasks -->
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header custom-header">
                <h3>Tugas Mendatang</h3>
                @if(Route::has('tugas'))
                <a href="{{ route('tugas') }}" class="card-link">Lihat Semua →</a>
                @endif
            </div>
            <div class="card-body">
                @forelse($upcomingTasks ?? [] as $task)
                <div class="task-item">
                    <div class="task-header">
                        <div class="task-title">{{ $task->title }}</div>
                        <span class="task-priority {{ $task->priority }}">{{ ucfirst($task->priority) }}</span>
                    </div>
                    <div class="task-description">{{ $task->description }}</div>
                    <div class="task-meta">
                        <span>⏰ Deadline: {{ $task->deadline }}</span>
                        <span>📍 {{ $task->days_left }} hari lagi</span>
                    </div>
                </div>
                @empty
                <div class="task-item">
                    <div class="task-header">
                        <div class="task-title">Upload Hasil Penilaian AK</div>
                        <span class="task-priority high">Urgent</span>
                    </div>
                    <div class="task-description">
                        S2 Ilmu Lingkungan - Universitas Diponegoro
                    </div>
                    <div class="task-meta">
                        <span>⏰ Deadline: 10 November 2025</span>
                        <span>📍 12 hari lagi</span>
                    </div>
                </div>

                <div class="task-item">
                    <div class="task-header">
                        <div class="task-title">Rekonsiliasi Split Nilai</div>
                        <span class="task-priority high">Urgent</span>
                    </div>
                    <div class="task-description">
                        3 deskriptor perlu didiskusikan dengan Dr. Paulus
                    </div>
                    <div class="task-meta">
                        <span>⏰ Deadline: 11 Jun 2025</span>
                        <span>📍 4 hari lagi</span>
                    </div>
                </div>

                <div class="task-item">
                    <div class="task-header">
                        <div class="task-title">Visitasi AL</div>
                        <span class="task-priority medium">Normal</span>
                    </div>
                    <div class="task-description">
                        Asesmen Lapangan - S2 Ilmu Lingkungan Universitas Diponegoro
                    </div>
                    <div class="task-meta">
                        <span>⏰ Jadwal: 14-20 Jun 2025</span>
                        <span>📍 12 hari lagi</span>
                    </div>
                </div>

                <div class="task-item">
                    <div class="task-header">
                        <div class="task-title">Tanggapi Penawaran Baru</div>
                        <span class="task-priority low">Low</span>
                    </div>
                    <div class="task-description">
                        2 penawaran asesmen menunggu respon
                    </div>
                    <div class="task-meta">
                        <span>⏰ Deadline: 20 Jun 2025</span>
                        <span>📍 18 hari lagi</span>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats -->
<div class="row g-4">
    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Total Program Studi Dinilai</div>
                    <div class="stat-value">{{ $additionalStats['total_prodi'] ?? 156 }}</div>
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
