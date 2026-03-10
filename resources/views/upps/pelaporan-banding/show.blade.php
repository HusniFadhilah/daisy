{{-- resources/views/upps/pelaporan-banding/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pelaporan Banding')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pelaporan-banding') }}">Pelaporan Banding</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-file-earmark-ruled"></i> Detail Pelaporan Hasil Banding
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.pelaporan-banding') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN)
            <div class="alert alert-success alert-permanent border-start border-2 border-success">
                <div class="d-flex align-items-start">
                    <i class="bi bi-check-circle-fill fs-1 me-3 text-success"></i>
                    <div class="flex-grow-1">
                        <h5 class="mb-2 fw-bold">
                            <i class="bi bi-check-circle-fill"></i> Hasil Banding Telah Dilaporkan
                        </h5>
                        <p class="mb-0">
                            Hasil pelaksanaan banding telah dilaporkan pada
                            <strong>{{ $pengajuan->tanggal_pelaporan_banding->locale('id')->translatedFormat('d M Y H:i') }}</strong>.
                            Proses dilanjutkan ke penetapan hasil akhir akreditasi.
                        </p>
                    </div>
                </div>
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN)
            <div class="alert alert-info alert-permanent border-start border-2 border-info">
                <div class="d-flex align-items-start">
                    <i class="bi bi-award-fill fs-1 me-3 text-info"></i>
                    <div class="flex-grow-1">
                        <h5 class="mb-2 fw-bold">
                            <i class="bi bi-award"></i> Hasil Akhir Telah Ditetapkan
                        </h5>
                        <p class="mb-0">
                            Hasil akhir akreditasi telah ditetapkan pada
                            <strong>{{ $pengajuan->tanggal_penetapan ? $pengajuan->tanggal_penetapan->locale('id')->translatedFormat('d M Y H:i') : '-' }}</strong>.
                        </p>
                    </div>
                </div>
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_SELESAI)
            <div class="alert alert-success alert-permanent border-start border-2 border-success">
                <div class="d-flex align-items-start">
                    <i class="bi bi-patch-check-fill fs-1 me-3 text-success"></i>
                    <div class="flex-grow-1">
                        <h5 class="mb-2 fw-bold">
                            <i class="bi bi-patch-check"></i> Proses Akreditasi Selesai
                        </h5>
                        <p class="mb-0">
                            Seluruh proses akreditasi telah selesai. Hasil akhir akreditasi telah ditetapkan dan diumumkan.
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

            <!-- Timeline Banding -->
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Timeline Proses Banding
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Tanggal Pengajuan Banding</th>
                            <td>
                                : {{ $pengajuan->tanggal_permohonan_banding
                                    ? $pengajuan->tanggal_permohonan_banding->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pelaksanaan Banding</th>
                            <td>
                                : {{ $pengajuan->tanggal_pelaksanaan_banding
                                    ? $pengajuan->tanggal_pelaksanaan_banding->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pelaporan Banding</th>
                            <td>
                                : {{ $pengajuan->tanggal_pelaporan_banding
                                    ? $pengajuan->tanggal_pelaporan_banding->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Penetapan Hasil</th>
                            <td>
                                : {{ $pengajuan->tanggal_penetapan
                                    ? $pengajuan->tanggal_penetapan->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>

                        @if($pengajuan->tanggal_permohonan_banding && $pengajuan->tanggal_pelaporan_banding)
                        <tr>
                            <th>Total Durasi Banding</th>
                            <td>
                                : {{ $pengajuan->tanggal_permohonan_banding->diffInDays($pengajuan->tanggal_pelaporan_banding) }} hari
                                <small class="text-muted">(dari pengajuan hingga pelaporan)</small>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Hasil Peringkat -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-trophy"></i> Perbandingan Hasil Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-secondary">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Hasil Sebelum Banding</h6>
                                </div>
                                <div class="card-body text-center">
                                    @if($pengajuan->peringkat_hasil)
                                    @php
                                    $badgeClass = match($pengajuan->peringkat_hasil) {
                                    'Unggul' => 'bg-warning text-dark',
                                    'Baik Sekali' => 'bg-success',
                                    'Baik' => 'bg-info',
                                    'Tidak Terakreditasi' => 'bg-danger',
                                    default => 'bg-secondary',
                                    };
                                    @endphp
                                    <div class="mb-3">
                                        <i class="bi bi-trophy text-muted" style="font-size: 48px;"></i>
                                    </div>
                                    <h4 class="mb-2">{{ $pengajuan->peringkat_hasil }}</h4>
                                    @if($pengajuan->nilai_akhir)
                                    <p class="mb-0">
                                        Nilai: <strong>{{ $pengajuan->nilai_akhir }}</strong>
                                    </p>
                                    @endif
                                    @else
                                    <p class="text-muted">Data tidak tersedia</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">Hasil Setelah Banding</h6>
                                </div>
                                <div class="card-body text-center">
                                    @if($pengajuan->peringkat_hasil_banding)
                                    @php
                                    $badgeClassBanding = match($pengajuan->peringkat_hasil_banding) {
                                    'Unggul' => 'bg-warning text-dark',
                                    'Baik Sekali' => 'bg-success',
                                    'Baik' => 'bg-info',
                                    'Tidak Terakreditasi' => 'bg-danger',
                                    default => 'bg-secondary',
                                    };
                                    @endphp
                                    <div class="mb-3">
                                        <i class="bi bi-trophy-fill text-success" style="font-size: 48px;"></i>
                                    </div>
                                    <h4 class="mb-2">{{ $pengajuan->peringkat_hasil_banding }}</h4>
                                    @if($pengajuan->nilai_akhir_banding)
                                    <p class="mb-0">
                                        Nilai: <strong>{{ $pengajuan->nilai_akhir_banding }}</strong>
                                    </p>
                                    @endif

                                    @if($pengajuan->peringkat_hasil !== $pengajuan->peringkat_hasil_banding)
                                    <div class="mt-3">
                                        <span class="badge bg-success">
                                            <i class="bi bi-arrow-up-circle"></i> Peringkat Berubah
                                        </span>
                                    </div>
                                    @endif
                                    @else
                                    <div class="mb-3">
                                        <i class="bi bi-hourglass-split text-muted" style="font-size: 48px;"></i>
                                    </div>
                                    <p class="text-muted">Menunggu hasil banding</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dokumen Hasil Banding -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> Dokumen Hasil Banding
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $dokumenHasilBanding = $pengajuan->dokumen
                    ->where('jenis_dokumen', 'hasil_banding')
                    ->where('is_latest', true);
                    @endphp

                    @if($dokumenHasilBanding->count() > 0)
                    @foreach($dokumenHasilBanding as $dokumen)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded mb-2">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $dokumen->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($dokumen->file_size / 1024, 2) }} KB •
                                    Diupload: {{ $dokumen->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small>
                                <br>
                                <span class="badge bg-success">Hasil Banding</span>
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
                        <p class="text-muted mt-2 mb-0">Dokumen hasil banding belum tersedia</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ringkasan Hasil -->
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-clipboard-check"></i> Ringkasan Pelaporan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Status Pelaporan</label>
                        <p class="mb-0">
                            <span class="badge {{ $pengajuan->status_badge_class }} fs-6">
                                {{ $pengajuan->status_label }}
                            </span>
                        </p>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small">Tanggal Pelaporan</label>
                        <p class="fw-bold mb-0">
                            {{ $pengajuan->tanggal_pelaporan_banding
                                ? $pengajuan->tanggal_pelaporan_banding->locale('id')->translatedFormat('d M Y H:i')
                                : '-' }}
                        </p>
                    </div>

                    @if($pengajuan->tanggal_penetapan)
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">Tanggal Penetapan</label>
                        <p class="fw-bold mb-0">
                            {{ $pengajuan->tanggal_penetapan->locale('id')->translatedFormat('d M Y H:i') }}
                        </p>
                    </div>
                    @endif

                    @if($pengajuan->peringkat_hasil_banding)
                    <hr>
                    <div>
                        <label class="text-muted small">Hasil Akhir</label>
                        <p class="mb-0">
                            <span class="badge bg-success fs-6">
                                {{ $pengajuan->peringkat_hasil_banding }}
                            </span>
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
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
                                    @php
                                    $iconColor = match($log->status_to) {
                                    \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
                                    => 'text-success',
                                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN
                                    => 'text-warning',
                                    default => 'text-info',
                                    };
                                    @endphp
                                    <i class="bi bi-circle-fill {{ $iconColor }}" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label'] ?? $log->status_to }}
                                    </strong>
                                    <br>
                                    <small class="text-muted">{{ $log->created_at->locale('id')->translatedFormat('d M Y H:i') }}</small>

                                    {{-- @if($log->keterangan)
                                    <br>
                                    <small class="text-muted fst-italic">{{ $log->keterangan }}</small>
                                    @endif --}}
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
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Tahap Selanjutnya
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Setelah Pelaporan Banding:</strong>
                    </p>
                    <ol class="small mb-0 ps-3 text-muted">
                        <li>Hasil banding dilaporkan (selesai)</li>
                        <li>Penetapan hasil akhir akreditasi</li>
                        <li>Pengumuman hasil akreditasi</li>
                        <li>Pelaporan hasil kepada pemangku kepentingan</li>
                        <li>Penyimpanan Arsip Akreditasi</li>
                        <li>Proses akreditasi selesai</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
