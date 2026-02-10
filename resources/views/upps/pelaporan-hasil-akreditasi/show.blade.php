{{-- resources/views/upps/pelaporan-hasil-akreditasi/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pelaporan Hasil Akreditasi')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pelaporan-hasil-akreditasi') }}">Pelaporan Hasil Akreditasi</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-megaphone"></i> Detail Pelaporan Hasil Akreditasi
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.pelaporan-hasil-akreditasi') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @php
    // Peringkat akhir: prioritas hasil banding jika ada
    $peringkatAkhir = $pengajuan->peringkat_hasil_banding ?? $pengajuan->peringkat_hasil;
    $nilaiAkhir = $pengajuan->nilai_akhir_banding ?? $pengajuan->nilai_akhir;

    $badgeClass = match($peringkatAkhir) {
    'Unggul' => 'bg-warning text-dark',
    'Baik Sekali' => 'bg-success',
    'Baik' => 'bg-info',
    'Tidak Terakreditasi' => 'bg-danger',
    default => 'bg-secondary',
    };
    @endphp

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Success Alert -->
            <div class="alert alert-success alert-permanent border-start border-4 border-success">
                <div class="d-flex align-items-start">
                    <i class="bi bi-megaphone-fill fs-1 me-3 text-success"></i>
                    <div class="flex-grow-1">
                        <h5 class="mb-2 fw-bold">
                            <i class="bi bi-check-circle-fill"></i> Hasil Akreditasi Telah Dilaporkan
                        </h5>
                        <p class="mb-2">
                            Hasil akreditasi telah dilaporkan kepada pemangku kepentingan pada
                            <strong>{{ $pengajuan->tanggal_pelaporan_hasil ? $pengajuan->tanggal_pelaporan_hasil->format('d M Y H:i') : '-' }}</strong>.
                        </p>
                        <div class="alert alert-light border border-success mb-0">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Peringkat Akhir:</strong>
                                    <div class="mt-1">
                                        <span class="badge {{ $badgeClass }} fs-6">
                                            @if($peringkatAkhir === 'Unggul')
                                            <i class="bi bi-star-fill"></i>
                                            @elseif($peringkatAkhir === 'Baik Sekali')
                                            <i class="bi bi-award-fill"></i>
                                            @elseif($peringkatAkhir === 'Baik')
                                            <i class="bi bi-check-circle-fill"></i>
                                            @endif
                                            {{ $peringkatAkhir ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <strong>Nilai Akhir:</strong>
                                    <div class="fs-4 fw-bold text-success mt-1">{{ $nilaiAkhir ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completion Alert -->
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_SELESAI)
            <div class="alert alert-info alert-permanent border-start border-4 border-info">
                <div class="d-flex align-items-start">
                    <i class="bi bi-patch-check-fill fs-1 me-3 text-info"></i>
                    <div class="flex-grow-1">
                        <h5 class="mb-2 fw-bold">
                            <i class="bi bi-patch-check"></i> Proses Akreditasi Selesai
                        </h5>
                        <p class="mb-0">
                            Seluruh tahapan proses akreditasi telah selesai. Arsip pelaksanaan akreditasi
                            telah disimpan dan dapat diakses untuk keperluan dokumentasi.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Informasi Permohonan -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Permohonan Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Nomor Permohonan</th>
                            <td>: {{ $pengajuan->nomor_pengajuan }}</td>
                        </tr>
                        <tr>
                            <th>Program Studi</th>
                            <td>: {{ $pengajuan->studyProgram->full_name }}</td>
                        </tr>
                        <tr>
                            <th>Universitas</th>
                            <td>: {{ $pengajuan->studyProgram->university->name }}</td>
                        </tr>
                        <tr>
                            <th>Jenjang</th>
                            <td>: {{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Permohonan</th>
                            <td>: {{ $pengajuan->jenis_akreditasi_label }}</td>
                        </tr>
                        <tr>
                            <th>Tahun Akreditasi</th>
                            <td>: {{ $pengajuan->tahun_akreditasi }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                : <span class="badge {{ $pengajuan->status_badge_class }}">
                                    {{ $pengajuan->status_label }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Hasil Akhir Akreditasi -->
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-trophy"></i> Hasil Akhir Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Peringkat Akreditasi</label>
                            <p class="mb-0">
                                @if($peringkatAkhir)
                                <span class="badge {{ $badgeClass }} fs-5">
                                    @if($peringkatAkhir === 'Unggul')
                                    <i class="bi bi-star-fill"></i>
                                    @elseif($peringkatAkhir === 'Baik Sekali')
                                    <i class="bi bi-award-fill"></i>
                                    @elseif($peringkatAkhir === 'Baik')
                                    <i class="bi bi-check-circle-fill"></i>
                                    @endif
                                    {{ $peringkatAkhir }}
                                </span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Nilai Akhir</label>
                            <p class="mb-0">
                                @if($nilaiAkhir)
                                <span class="fs-3 fw-bold text-success">{{ $nilaiAkhir }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tanggal Penetapan</label>
                            <p class="mb-0">
                                {{ $pengajuan->tanggal_penetapan
                                    ? $pengajuan->tanggal_penetapan->format('d M Y H:i')
                                    : '-' }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tanggal Pengumuman</label>
                            <p class="mb-0">
                                {{ $pengajuan->tanggal_pengumuman
                                    ? $pengajuan->tanggal_pengumuman->format('d M Y H:i')
                                    : '-' }}
                            </p>
                        </div>
                    </div>

                    @if($pengajuan->peringkat_hasil_banding)
                    <hr>
                    <div class="alert alert-info border border-info mb-0">
                        <i class="bi bi-info-circle-fill"></i>
                        <strong>Catatan:</strong> Hasil yang ditampilkan adalah hasil akhir setelah proses banding.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Detail Pelaporan -->
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-check"></i> Detail Pelaporan
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Tanggal Pelaporan Hasil</th>
                            <td>
                                : {{ $pengajuan->tanggal_pelaporan_hasil
                                    ? $pengajuan->tanggal_pelaporan_hasil->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Penyimpanan Arsip</th>
                            <td>
                                : {{ $pengajuan->tanggal_penyimpanan
                                    ? $pengajuan->tanggal_penyimpanan->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>

                        @if($pengajuan->tanggal_pengajuan && $pengajuan->tanggal_pelaporan_hasil)
                        <tr>
                            <th>Total Durasi Proses</th>
                            <td>
                                : {{ $pengajuan->tanggal_pengajuan->diffInDays($pengajuan->tanggal_pelaporan_hasil) }} hari
                                <small class="text-muted">(dari pengajuan hingga pelaporan hasil)</small>
                            </td>
                        </tr>
                        @endif
                    </table>

                    @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_SELESAI)
                    <hr>
                    <div class="alert alert-success border border-success mb-0">
                        <i class="bi bi-check-circle-fill"></i>
                        <strong>Status:</strong> Seluruh proses akreditasi telah selesai dan arsip telah disimpan.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Dokumen Hasil & Laporan -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> Dokumen Hasil & Laporan
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $dokumenHasil = $pengajuan->dokumen
                    ->whereIn('jenis_dokumen', ['sertifikat_akreditasi', 'sk_akreditasi', 'sk_penetapan', 'laporan_hasil'])
                    ->where('is_latest', true);
                    @endphp

                    @if($dokumenHasil->count() > 0)
                    @foreach($dokumenHasil as $dokumen)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded mb-2">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $dokumen->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($dokumen->file_size / 1024, 2) }} KB •
                                    Diupload: {{ $dokumen->created_at->format('d M Y H:i') }}
                                </small>
                                <br>
                                @if($dokumen->jenis_dokumen === 'sertifikat_akreditasi')
                                <span class="badge bg-success">Sertifikat Akreditasi</span>
                                @elseif($dokumen->jenis_dokumen === 'sk_akreditasi')
                                <span class="badge bg-primary">SK Akreditasi</span>
                                @elseif($dokumen->jenis_dokumen === 'sk_penetapan')
                                <span class="badge bg-info">SK Penetapan</span>
                                @elseif($dokumen->jenis_dokumen === 'laporan_hasil')
                                <span class="badge bg-warning text-dark">Laporan Hasil</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $dokumen->id) }}" class="btn btn-success btn-md">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                            </a>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Dokumen hasil & laporan belum tersedia</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ringkasan Hasil Akhir -->
            <div class="card mb-4 border-{{ $badgeClass === 'bg-warning text-dark' ? 'warning' : ($badgeClass === 'bg-success' ? 'success' : 'info') }}">
                <div class="card-header {{ $badgeClass }}">
                    <h6 class="mb-0">
                        <i class="bi bi-award"></i> Ringkasan Hasil Akhir
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-trophy-fill text-{{ $badgeClass === 'bg-warning text-dark' ? 'warning' : ($badgeClass === 'bg-success' ? 'success' : 'info') }}" style="font-size: 64px;"></i>
                    </div>
                    <h3 class="fw-bold mb-2">{{ $peringkatAkhir ?? '-' }}</h3>
                    @if($nilaiAkhir)
                    <p class="mb-3">
                        <span class="text-muted">Nilai Akhir:</span>
                        <br>
                        <span class="fs-3 fw-bold">{{ $nilaiAkhir }}</span>
                    </p>
                    @endif
                    <hr>
                    <div class="mb-2">
                        <small class="text-muted">Tanggal Penetapan:</small>
                        <p class="mb-0 fw-bold">
                            {{ $pengajuan->tanggal_penetapan ? $pengajuan->tanggal_penetapan->format('d M Y') : '-' }}
                        </p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Tanggal Pelaporan:</small>
                        <p class="mb-0 fw-bold">
                            {{ $pengajuan->tanggal_pelaporan_hasil ? $pengajuan->tanggal_pelaporan_hasil->format('d M Y') : '-' }}
                        </p>
                    </div>

                    @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_SELESAI)
                    <hr>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-patch-check-fill"></i>
                        <small><strong>Proses Selesai</strong></small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Statistik Proses -->
            @if($pengajuan->tanggal_pengajuan && $pengajuan->tanggal_pelaporan_hasil)
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up"></i> Statistik Proses
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Total Durasi</label>
                        <div class="display-6 fw-bold text-primary">
                            {{ $pengajuan->tanggal_pengajuan->diffInDays($pengajuan->tanggal_pelaporan_hasil) }}
                        </div>
                        <small class="text-muted">hari</small>
                    </div>

                    <hr>

                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Mulai:</span>
                            <strong>{{ $pengajuan->tanggal_pengajuan->format('d M Y') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Selesai:</span>
                            <strong>{{ $pengajuan->tanggal_pelaporan_hasil->format('d M Y') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Timeline -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                    \App\Models\PengajuanAkreditasi::STATUS_SELESAI,
                    ];

                    $logs = $pengajuan->statusLog
                    ->whereIn('status_to', $filterStatuses)
                    ->sortBy('created_at')
                    ->unique('status_to')
                    ->values();
                    @endphp

                    @if($logs->count() > 0)
                    <div class="timeline">
                        @foreach($logs as $log)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill text-success" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label'] ?? $log->status_to }}
                                    </strong>
                                    <br>
                                    <small class="text-muted">{{ $log->changed_at->format('d M Y H:i') }}</small>

                                    @if($log->keterangan)
                                    <br>
                                    <small class="text-muted fst-italic">{{ $log->keterangan }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted text-center mb-0">Belum ada riwayat</p>
                    @endif
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4 border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-check-circle"></i> Proses Akreditasi
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Tahapan Proses Akreditasi:</strong>
                    </p>
                    <ol class="small mb-3 ps-3 text-muted">
                        <li>Pengajuan permohonan akreditasi</li>
                        <li>Validasi dokumen</li>
                        <li>Asesmen Kecukupan (AK)</li>
                        <li>Asesmen Lapangan (AL)</li>
                        <li>Penyampaian hasil akreditasi</li>
                        <li>Masa sanggah (opsional: banding)</li>
                        <li>Penetapan hasil akreditasi</li>
                        <li>Pengumuman hasil</li>
                        <li>Pelaporan hasil (selesai)</li>
                        <li>Penyimpanan arsip</li>
                    </ol>

                    <hr>

                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle-fill"></i>
                        <strong>Selamat!</strong> Proses akreditasi telah selesai. Hasil akreditasi dapat digunakan
                        untuk keperluan administratif dan publikasi.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
