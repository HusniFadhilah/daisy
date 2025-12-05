@extends('layouts.template.app')

@section('title', 'Dashboard User - DAISY')

@section('content')
<!-- Welcome Section -->
<section class="welcome-section">
    <div class="welcome-content">
        <h2>Selamat Datang Kembali, {{ auth()->user()->name ?? 'Dr. Eng. Maryono' }}! 👋</h2>
        <p>Anda memiliki {{ $penawaranBaru ?? 2 }} penawaran baru, dan {{ $penugasanAktif ?? 1 }} tugas aktif. Mari kita selesaikan tugas ini dengan senyum 😊</p>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('penawaran.baru') }}" class="quick-btn">📨 Lihat Penawaran</a>
            <a href="{{ route('ak.berkas') }}" class="quick-btn">📝 Lanjutkan Penilaian</a>
            <a href="{{ route('laporan') }}" class="quick-btn">📊 Lihat Laporan</a>
        </div>
    </div>
</section>

<!-- Stats Grid -->
<div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-lg-3">
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

    <div class="col-12 col-md-6 col-lg-3">
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

    <div class="col-12 col-md-6 col-lg-3">
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

    <div class="col-12 col-md-6 col-lg-3">
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
                <a href="{{ route('aktivitas') }}" class="card-link">Lihat Semua →</a>
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
                <a href="{{ route('tugas') }}" class="card-link">Lihat Semua →</a>
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
    <div class="col-12 col-md-6 col-lg-3">
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

    <div class="col-12 col-md-6 col-lg-3">
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

    <div class="col-12 col-md-6 col-lg-3">
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

    <div class="col-12 col-md-6 col-lg-3">
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

@endsection
