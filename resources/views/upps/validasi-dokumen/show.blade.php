{{-- resources/views/upps/validasi-dokumen/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Validasi Dokumen')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.validasi-dokumen') }}">Validasi Dokumen</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-clipboard-check"></i> Detail Validasi Dokumen
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.validasi-dokumen') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            ]; // ini contoh, bisa dinamis dari config/db/request

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if(in_array($log?->status_to,[\App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,\App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION]))
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Dokumen sedang divalidasi</strong><br>
                Mohon menunggu hasil validasi dokumen oleh LAMDEPILAR
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Dokumen telah divalidasi</strong><br>
                Menunggu pelaporan hasil validasi oleh LAMDEPILAR ke tahap berikutnya
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Dokumen memerlukan revisi</strong>
                <br>
                Validator telah memberikan catatan perbaikan yang perlu dilakukan.
                <div class="mt-2">
                    <a href="{{ route('upps.penerimaan-dokumen.show', $pengajuan->id) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-arrow-right"></i> Lihat Dokumen & Upload Revisi
                    </a>
                </div>
            </div>
            @endif

            <!-- Informasi Validasi -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Validasi Dokumen
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Tanggal Penugasan Validator</th>
                            <td>
                                : {{ $pengajuan->tanggal_validasi_borang_assigned
                                    ? $pengajuan->tanggal_validasi_borang_assigned->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Validasi Selesai</th>
                            <td>
                                : {{ $pengajuan->tanggal_validasi_borang_selesai
                                    ? $pengajuan->tanggal_validasi_borang_selesai->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Validasi Dokumen</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('validasi_dokumen', 'upps') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Catatan Validator -->
            @if($pengajuan->borangValidation)
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-chat-left-text"></i> Catatan dari Validator
                    </h5>
                </div>
                <div class="card-body">
                    @if($pengajuan->borangValidation->catatan_validator)
                    <div class="alert alert-light border">
                        <h6 class="fw-bold mb-2">
                            <i class="bi bi-person-badge"></i>
                            {{ $pengajuan->borangValidation->validator->name ?? 'Validator' }}
                        </h6>
                        <p class="mb-0">{{ $pengajuan->borangValidation->catatan_validator }}</p>
                        @if($pengajuan->borangValidation->updated_at)
                        <hr class="my-2">
                        <small class="text-muted">
                            <i class="bi bi-clock"></i>
                            {{ $pengajuan->borangValidation->updated_at->format('d M Y H:i') }}
                        </small>
                        @endif
                    </div>
                    @else
                    <p class="text-muted mb-0 text-center py-3">
                        <i class="bi bi-chat-left-text" style="font-size: 32px; color: #ddd;"></i>
                        <br>
                        Belum ada catatan dari validator
                    </p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Dokumen yang Divalidasi -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-pdf"></i> Dokumen yang Divalidasi
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
                        <p class="text-muted mt-2 mb-0">Belum ada dokumen yang divalidasi</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Timeline Validasi -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                    \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                    \App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
                    \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                    \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                    \App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
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
                                    \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                                    \App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA
                                    => 'text-success',
                                    \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED
                                    => 'text-danger',
                                    \App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION
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
                    <p class="text-muted text-center mb-0">Belum ada riwayat validasi</p>
                    @endif
                </div>
            </div>

            <!-- Info Card -->
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED)
            <div class="card mt-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="bi bi-exclamation-triangle"></i> Perhatian
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Dokumen memerlukan revisi.</strong>
                    </p>
                    <p class="text-muted small mb-3">
                        Silakan periksa catatan validator dan lakukan perbaikan pada dokumen,
                        kemudian upload ulang dokumen yang telah diperbaiki.
                    </p>
                    <a href="{{ route('upps.penerimaan-dokumen.show', $pengajuan->id) }}" class="btn btn-warning btn-sm w-100">
                        <i class="bi bi-arrow-right"></i> Upload Dokumen Revisi
                    </a>
                </div>
            </div>
            @endif

            <!-- Help Card -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-question-circle"></i> Informasi
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Proses Validasi Dokumen:</strong>
                    </p>
                    <ol class="small mb-0 ps-3">
                        <li>Dokumen diterima LAMDEPILAR</li>
                        <li>LAMDEPILAR menugaskan validator</li>
                        <li>Selanjutnya, Validator memeriksa kelengkapan dokumen</li>
                        <li>Jika terdapat hal di dokumen yang perlu diperbaiki, validator akan memberikan catatan revisi tersebut</li>
                        <li>Jika telah sesuai (tidak ada permintaan revisi), dokumen akan selesai divalidasi</li>
                        <li>Proses dilanjutkan ke tahap berikutnya</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
