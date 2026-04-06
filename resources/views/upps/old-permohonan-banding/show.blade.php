{{-- resources/views/upps/permohonan-banding/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Permohonan Banding')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.permohonan-banding') }}">Permohonan Banding</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-arrow-repeat"></i> Detail Permohonan Banding
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.permohonan-banding') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN)
            <div class="alert alert-info alert-permanent border-start border-2 border-info">
                <div class="d-flex align-items-start">
                    <i class="bi bi-hourglass-split fs-1 me-3 text-info"></i>
                    <div class="flex-grow-1">
                        <h5 class="mb-2 fw-bold">
                            <i class="bi bi-arrow-repeat"></i> Permohonan Banding Diajukan
                        </h5>
                        <p class="mb-0">
                            Permohonan banding telah diajukan pada
                            <strong>{{ $pengajuan->tanggal_banding->locale('id')->translatedFormat('d M Y H:i') }}</strong>.
                            Menunggu proses pelaksanaan banding dari LAMDEPILAR.
                        </p>
                    </div>
                </div>
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN)
            <div class="alert alert-warning alert-permanent border-start border-2 border-warning">
                <div class="d-flex align-items-start">
                    <i class="bi bi-clock-history fs-1 me-3 text-warning"></i>
                    <div class="flex-grow-1">
                        <h5 class="mb-2 fw-bold">
                            <i class="bi bi-play-circle"></i> Pelaksanaan Banding Sedang Berlangsung
                        </h5>
                        <p class="mb-0">
                            Pelaksanaan banding dimulai pada
                            <strong>{{ $pengajuan->tanggal_pelaksanaan_banding ? $pengajuan->tanggal_pelaksanaan_banding->locale('id')->translatedFormat('d M Y H:i') : '-' }}</strong>.
                            Proses banding sedang dilakukan oleh LAMDEPILAR.
                        </p>
                    </div>
                </div>
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN)
            <div class="alert alert-success alert-permanent border-start border-2 border-success">
                <div class="d-flex align-items-start">
                    <i class="bi bi-check-circle-fill fs-1 me-3 text-success"></i>
                    <div class="flex-grow-1">
                        <h5 class="mb-2 fw-bold">
                            <i class="bi bi-check-circle-fill"></i> Hasil Banding Telah Dilaporkan
                        </h5>
                        <p class="mb-0">
                            Hasil pelaksanaan banding telah dilaporkan pada
                            <strong>{{ $pengajuan->tanggal_pelaporan_banding ? $pengajuan->tanggal_pelaporan_banding->locale('id')->translatedFormat('d M Y H:i') : '-' }}</strong>.
                            Proses dilanjutkan ke penetapan hasil akhir akreditasi.
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
                            <th>Peringkat Hasil Awal</th>
                            <td>
                                : @if($pengajuan->peringkat_hasil)
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
                            </td>
                        </tr>
                        <tr>
                            <th>Nilai Akhir Awal</th>
                            <td>: <strong>{{ $pengajuan->nilai_akhir ?? '-' }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Detail Banding -->
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-repeat"></i> Detail Permohonan Banding
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Tanggal Pengajuan Banding</th>
                            <td>
                                : {{ $pengajuan->tanggal_banding
                                    ? $pengajuan->tanggal_banding->locale('id')->translatedFormat('d M Y H:i')
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
                            <th>Status Banding</th>
                            <td>
                                : <span class="badge {{ $pengajuan->status_badge_class }}">
                                    {{ $pengajuan->status_label }}
                                </span>
                            </td>
                        </tr>

                        @if($pengajuan->tanggal_banding && $pengajuan->tanggal_pelaksanaan_banding)
                        <tr>
                            <th>Durasi Proses</th>
                            <td>
                                : {{ $pengajuan->tanggal_banding->diffInDays($pengajuan->tanggal_pelaksanaan_banding) }} hari
                                <small class="text-muted">(dari pengajuan hingga pelaksanaan)</small>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Dokumen Banding -->
            <div class="card">
                <div class="card-header bg-info text-white">
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
                                    Diupload: {{ $dokumen->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small>
                                <br>
                                <span class="badge bg-info">Dokumen Banding</span>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $dokumen->id) }}" class="btn btn-info btn-md">
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
            <!-- Progress Card -->
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-diagram-3"></i> Progres Banding
                    </h6>
                </div>
                <div class="card-body">
                    @php
                    $steps = [
                    ['status' => 'completed', 'label' => 'Banding Diajukan', 'date' => $pengajuan->tanggal_banding],
                    ['status' => $pengajuan->tanggal_pelaksanaan_banding ? 'completed' : 'pending', 'label' => 'Banding Dilaksanakan', 'date' => $pengajuan->tanggal_pelaksanaan_banding],
                    ['status' => $pengajuan->tanggal_pelaporan_banding ? 'completed' : 'pending', 'label' => 'Banding Dilaporkan', 'date' => $pengajuan->tanggal_pelaporan_banding],
                    ];
                    @endphp

                    <div class="progress-steps">
                        @foreach($steps as $index => $step)
                        <div class="step-item mb-3">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    @if($step['status'] === 'completed')
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-check-lg"></i>
                                    </div>
                                    @else
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        {{ $index + 1 }}
                                    </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong class="{{ $step['status'] === 'completed' ? 'text-success' : 'text-muted' }}">
                                        {{ $step['label'] }}
                                    </strong>
                                    @if($step['date'])
                                    <br>
                                    <small class="text-muted">
                                        {{ $step['date']->locale('id')->translatedFormat('d M Y H:i') }}
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
            <div class="card-header bg-info text-white">
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
                \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
                ];

                $logs = $pengajuan->statusLog
                ->whereIn('status_to', $filterStatuses)
                ->unique('status_to')
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
                                \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN
                                => 'text-success',
                                \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
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
                    <i class="bi bi-info-circle"></i> Informasi Banding
                </h6>
            </div>
            <div class="card-body">
                <p class="small mb-2">
                    <strong>Apa itu Banding?</strong>
                </p>
                <p class="small text-muted mb-3">
                    Banding adalah proses pengajuan keberatan terhadap hasil akreditasi yang
                    diajukan selama masa sanggah. LAMDEPILAR akan meninjau kembali hasil
                    akreditasi sesuai dengan alasan banding yang diajukan.
                </p>

                <p class="small mb-2">
                    <strong>Tahapan Proses Banding:</strong>
                </p>
                <ol class="small mb-3 ps-3 text-muted">
                    <li>Pengajuan banding selama masa sanggah</li>
                    <li>Peninjauan oleh LAMDEPILAR</li>
                    <li>Pelaksanaan banding (jika diperlukan)</li>
                    <li>Pelaporan hasil banding</li>
                    <li>Penetapan hasil akhir akreditasi</li>
                </ol>

                <hr>

                <p class="small text-muted mb-0">
                    <i class="bi bi-exclamation-circle"></i>
                    Hasil banding akan menjadi hasil akhir akreditasi yang ditetapkan oleh LAMDEPILAR.
                </p>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
