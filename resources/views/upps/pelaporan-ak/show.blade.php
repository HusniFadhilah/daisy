{{-- resources/views/upps/pelaporan-ak/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pelaporan Hasil AK')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pelaporan-ak') }}">Pelaporan Hasil AK</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-file-earmark-check"></i> Detail Pelaporan Hasil Asesmen Kecukupan
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.pelaporan-ak') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Hasil Asesmen Kecukupan telah selesai divalidasi</strong>
                <br>
                Penilaian telah selesai dan tervalidasi, menunggu pelaporan hasil ke tahap berikutnya
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-patch-check"></i>
                <strong>Hasil Asesmen Kecukupan telah dilaporkan</strong>
                <br>
                Hasil telah dilaporkan oleh LAMDEPILAR dan proses dilanjutkan ke tahap berikutnya
            </div>
            @endif

            <!-- Informasi Pelaporan -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pelaporan Hasil AK
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
                            <th>Asesor AK</th>
                            <td>
                                @if($pengajuan->asesmen->asesorAK)
                                : {{ $pengajuan->asesmen->asesorAK()->name }}
                                <br>
                                <small class="text-muted">{{ $pengajuan->asesmen->asesorAK()->email }}</small>
                                @else
                                : <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Penilaian Selesai</th>
                            <td>
                                : {{ $pengajuan->tanggal_ak_selesai
                                    ? $pengajuan->tanggal_ak_selesai->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Hasil Dilaporkan</th>
                            <td>
                                : {{ $pengajuan->tanggal_ak_dilaporkan
                                    ? $pengajuan->tanggal_ak_dilaporkan->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Pelaporan</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('asesmen_kecukupan', 'upps') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Surat Tugas Asesor AK -->
            @php
            $suratTugas = $pengajuan->dokumen
            ->where('jenis_dokumen', 'surat_tugas_asesor_ak')
            ->where('is_latest', true)
            ->first();
            @endphp

            @if($suratTugas)
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> Surat Tugas Asesor AK
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border mb-3">
                        <i class="bi bi-info-circle"></i>
                        <strong>Informasi:</strong> Surat tugas resmi penugasan asesor untuk Asesmen Kecukupan dari LAMDEPILAR.
                    </div>

                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $suratTugas->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($suratTugas->file_size / 1024, 2) }} KB
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> Diupload: {{ $suratTugas->created_at->format('d M Y H:i') }}
                                </small>
                                <br>
                                <span class="badge bg-warning text-dark">Surat Tugas Resmi</span>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $suratTugas->id) }}" class="btn btn-warning btn-md">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Informasi Hasil Penilaian -->
            @if($pengajuan->asesmen->asesorAK)
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check"></i> Informasi Hasil Penilaian
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted small">Asesor Penilai</label>
                            <p class="fw-bold mb-3">{{ $pengajuan->asesmen->asesorAK()->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Tanggal Penilaian Selesai</label>
                            <p class="fw-bold mb-3">
                                {{ $pengajuan->tanggal_ak_selesai
                                        ? $pengajuan->tanggal_ak_selesai->format('d M Y H:i')
                                        : '-' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Durasi Penilaian</label>
                            <p class="fw-bold mb-3">
                                @if($pengajuan->tanggal_ak_mulai && $pengajuan->tanggal_ak_selesai)
                                {{ $pengajuan->tanggal_ak_mulai->diffInDays($pengajuan->tanggal_ak_selesai) }} hari
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Status Hasil</label>
                            <p class="mb-3">
                                @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI)
                                <span class="badge bg-success">Tervalidasi</span>
                                @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN)
                                <span class="badge bg-info">Dilaporkan</span>
                                @else
                                <span class="badge bg-secondary">-</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI)
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle"></i>
                        <strong>Status:</strong> Hasil penilaian telah divalidasi dan siap dilaporkan
                    </div>
                    @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN)
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-send-check"></i>
                        <strong>Status:</strong> Hasil penilaian telah dilaporkan dan proses dilanjutkan ke tahap berikutnya
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Dokumen yang Dinilai -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-pdf"></i> Dokumen yang Dinilai
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $dokumenList = $pengajuan->dokumen
                    ->whereIn('jenis_dokumen', ['draft_borang', 'borang_final'])
                    ->where('is_latest', true);
                    @endphp

                    @if($dokumenList->count() > 0)
                    @foreach($dokumenList as $dokumen)
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
                                <span class="badge bg-success">Versi {{ $dokumen->versi }}</span>
                                @if($dokumen->jenis_dokumen === 'borang_final')
                                <span class="badge bg-primary">Final</span>
                                @else
                                <span class="badge bg-info">Draft</span>
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
                        <p class="text-muted mt-2 mb-0">Tidak ada dokumen yang dinilai</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Timeline Pelaporan -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
                    ];

                    $logs = $pengajuan->statusLog
                    ->whereIn('status_to', $filterStatuses)
                    ->sortByDesc('changed_at');
                    @endphp

                    @if($logs->count() > 0)
                    <div class="timeline">
                        @foreach($logs as $log)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    @php
                                    $iconColor = match($log->status_to) {
                                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                                    \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
                                    \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN
                                    => 'text-success',
                                    \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                                    \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION
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
                                    <small class="text-muted">{{ $log->changed_at->format('d M Y H:i') }}</small>

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
                    <p class="text-muted text-center mb-0">Belum ada riwayat pelaporan</p>
                    @endif
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pelaporan Hasil AK
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Proses Pelaporan Hasil AK:</strong>
                    </p>
                    <ol class="small mb-0 ps-3">
                        <li>Asesor menyelesaikan penilaian kecukupan</li>
                        <li>Hasil penilaian divalidasi oleh tim LAMDEPILAR</li>
                        <li>Validasi hasil selesai dan dinyatakan valid</li>
                        <li>LAMDEPILAR melaporkan hasil penilaian</li>
                        <li>Proses dilanjutkan ke tahap berikutnya</li>
                        <li>Program studi dapat mengakses hasil laporan</li>
                    </ol>

                    <hr>

                    <p class="small mb-2">
                        <strong>Catatan:</strong>
                    </p>
                    <p class="small text-muted mb-0">
                        Pelaporan hasil Asesmen Kecukupan merupakan tahap akhir dari proses penilaian kecukupan dokumen sebelum dilanjutkan ke tahap Asesmen Lapangan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
