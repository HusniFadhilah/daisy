{{-- resources/views/upps/pelaksanaan-banding/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pelaksanaan Banding')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pelaksanaan-banding') }}">Pelaksanaan Banding</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-play-circle"></i> Detail Pelaksanaan Banding
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.pelaksanaan-banding') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
            \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
            ]; // ini contoh, bisa dinamis dari config/db/request

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Proses Pelaksanaan Banding</strong><br>
                Program Studi yang akan melakukan banding dapat mengajukan permohonan. Klik tombol berikut untuk mengajukan permohonan
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Proses Pelaksanaan Banding</strong><br>
                Permohonan akreditasi program studi memasuki pelaksanaan banding
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
                            <th>Jenis Permohonan</th>
                            <td>: {{ $pengajuan->jenis_akreditasi_label }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Hasil Akreditasi Awal -->
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-megaphone"></i> Hasil Akreditasi Awal (Sebelum Banding)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Peringkat Akreditasi</label>
                            <p class="mb-0">
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
                                <span class="badge {{ $badgeClass }} fs-6">
                                    @if($pengajuan->peringkat_hasil === 'Unggul')
                                    <i class="bi bi-star-fill"></i>
                                    @elseif($pengajuan->peringkat_hasil === 'Baik Sekali')
                                    <i class="bi bi-award-fill"></i>
                                    @elseif($pengajuan->peringkat_hasil === 'Baik')
                                    <i class="bi bi-check-circle-fill"></i>
                                    @endif
                                    {{ $pengajuan->peringkat_hasil }}
                                </span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Nilai Akhir</label>
                            <p class="mb-0">
                                @if($pengajuan->nilai_akhir)
                                <strong class="fs-5 text-primary">{{ $pengajuan->nilai_akhir }}</strong>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small">Tanggal Hasil Disampaikan</label>
                            <p class="mb-0">
                                {{ $pengajuan->tanggal_hasil_akreditasi_dikirim
                                    ? $pengajuan->tanggal_hasil_akreditasi_dikirim->format('d M Y H:i')
                                    : '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Pelaksanaan Banding -->
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-play-circle"></i> Detail Pelaksanaan Banding
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Tanggal Pengajuan Banding</th>
                            <td>
                                : {{ $pengajuan->tanggal_permohonan_banding
                                    ? $pengajuan->tanggal_permohonan_banding->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pelaksanaan</th>
                            <td>
                                : {{ $pengajuan->tanggal_pelaksanaan_banding
                                    ? $pengajuan->tanggal_pelaksanaan_banding->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Pelaksanaan</th>
                            <td>
                                : <span class="badge {{ $pengajuan->status_badge_class }}">
                                    {{ $pengajuan->status_label }}
                                </span>
                            </td>
                        </tr>

                        @if($pengajuan->tanggal_permohonan_banding && $pengajuan->tanggal_pelaksanaan_banding)
                        <tr>
                            <th>Durasi Tunggu</th>
                            <td>
                                : {{ $pengajuan->tanggal_permohonan_banding->diffInDays($pengajuan->tanggal_pelaksanaan_banding) }} hari
                                <small class="text-muted">(dari pengajuan hingga pelaksanaan)</small>
                            </td>
                        </tr>
                        @endif

                        @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN && $pengajuan->tanggal_pelaksanaan_banding)
                        <tr>
                            <th>Durasi Pelaksanaan</th>
                            <td>
                                : {{ $pengajuan->tanggal_pelaksanaan_banding->diffInDays(now()) }} hari
                                <small class="text-muted">(hingga saat ini)</small>
                            </td>
                        </tr>
                        @endif
                    </table>

                    @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN)
                    <hr>
                    <div class="alert alert-light border border-info mb-0">
                        <i class="bi bi-info-circle-fill text-info"></i>
                        <strong>Informasi:</strong> Proses pelaksanaan banding sedang berlangsung.
                        Hasil akan dilaporkan setelah proses pelaksanaan selesai.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Dokumen Banding -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> Dokumen Banding
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $dokumenBanding = $pengajuan->dokumen
                    ->where('jenis_dokumen', 'dokumen_banding')
                    ->where('is_latest', true);
                    @endphp

                    @if($dokumenBanding->count() > 0)
                    @foreach($dokumenBanding as $dokumen)
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
                                <span class="badge bg-success">Dokumen Banding</span>
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
                        <p class="text-muted mt-2 mb-0">Dokumen banding belum tersedia</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Progress Steps -->
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-diagram-3"></i> Progress Pelaksanaan
                    </h6>
                </div>
                <div class="card-body">
                    @php
                    $steps = [
                    [
                    'status' => 'completed',
                    'label' => 'Banding Diajukan',
                    'date' => $pengajuan->tanggal_permohonan_banding,
                    'icon' => 'check-circle-fill',
                    'color' => 'success'
                    ],
                    [
                    'status' => $pengajuan->tanggal_pelaksanaan_banding ? 'completed' : ($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN ? 'current' : 'pending'),
                    'label' => 'Pelaksanaan Banding',
                    'date' => $pengajuan->tanggal_pelaksanaan_banding,
                    'icon' => $pengajuan->tanggal_pelaksanaan_banding ? 'check-circle-fill' : 'hourglass-split',
                    'color' => $pengajuan->tanggal_pelaksanaan_banding ? 'success' : ($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN ? 'warning' : 'secondary')
                    ],
                    [
                    'status' => 'pending',
                    'label' => 'Pelaporan Hasil',
                    'date' => null,
                    'icon' => 'circle',
                    'color' => 'secondary'
                    ],
                    ];
                    @endphp

                    <div class="progress-steps">
                        @foreach($steps as $index => $step)
                        <div class="step-item mb-3">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-{{ $step['color'] }} text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-{{ $step['icon'] }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong class="{{ $step['status'] === 'completed' ? 'text-success' : ($step['status'] === 'current' ? 'text-warning' : 'text-muted') }}">
                                        {{ $step['label'] }}
                                    </strong>
                                    @if($step['date'])
                                    <br>
                                    <small class="text-muted">
                                        {{ $step['date']->format('d M Y H:i') }}
                                    </small>
                                    @endif
                                    @if($step['status'] === 'current')
                                    <br>
                                    <small class="text-warning">
                                        <i class="bi bi-arrow-right"></i> Sedang berlangsung
                                    </small>
                                    @endif
                                </div>
                            </div>
                            @if($index < count($steps) - 1) <div class="ms-3 ps-2 border-start border-2 {{ $step['status'] === 'completed' ? 'border-success' : 'border-secondary' }}" style="height: 20px; margin-left: 20px;">
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history"></i> Riwayat Status
                </h5>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                @php
                $filterStatuses = [
                \App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                \App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
                \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                ];

                $logs = $pengajuan->statusLog
                ->whereIn('status_to', $filterStatuses)
                ->sortBy('changed_at');
                @endphp

                @if($logs->count() > 0)
                <div class="timeline">
                    @foreach($logs as $log)
                    <div class="timeline-item mb-3">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                @php
                                $iconColor = match($log->status_to) {
                                \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN
                                => 'text-success',
                                \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN
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
                                <small class="text-muted">{{ $log->created_at->format('d M Y H:i') }}</small>

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
        <div class="card mt-4 border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle"></i> Informasi Pelaksanaan
                </h6>
            </div>
            <div class="card-body">
                <p class="small mb-2">
                    <strong>Proses Pelaksanaan Banding:</strong>
                </p>
                <ol class="small mb-3 ps-3 text-muted">
                    <li>Permohonan banding diajukan (selesai)</li>
                    <li>LAMDEPILAR meninjau dokumen banding</li>
                    <li>Pelaksanaan banding dilakukan</li>
                    <li>Hasil banding dilaporkan</li>
                    <li>Penetapan hasil akhir akreditasi</li>
                </ol>

                <hr>

                <p class="small text-muted mb-0">
                    <i class="bi bi-exclamation-circle"></i>
                    @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN)
                    Menunggu proses pelaksanaan banding dari LAMDEPILAR. Harap pantau status secara berkala.
                    @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN)
                    Pelaksanaan banding sedang berlangsung. Hasil akan dilaporkan setelah proses selesai.
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
