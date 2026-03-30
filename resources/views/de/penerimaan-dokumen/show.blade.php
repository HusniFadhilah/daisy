{{-- resources/views/de/penerimaan-dokumen/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Penerimaan Dokumen - ' . $pengajuan->nomor_pengajuan)

@push('styles')
<style>
    .document-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .document-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }

    .document-card.uploaded {
        border-color: #28a745;
        background: #f8fff9;
    }

    .document-card.missing {
        border-color: #dc3545;
        background: #fff8f8;
    }

    .doc-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .doc-item {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: .65rem .75rem;
        margin-bottom: .5rem;
        transition: .2s ease;
        background: #fff;
    }

    .doc-item:hover {
        border-color: #667eea;
        box-shadow: 0 2px 8px rgba(102, 126, 234, .12);
    }

    .doc-item.uploaded {
        border-color: #28a745;
        background: #f8fff9;
    }

    .doc-item.missing {
        border-color: #dc3545;
        background: #fff8f8;
    }

    .doc-ico {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex: 0 0 34px;
    }

    .doc-title {
        font-size: .92rem;
        margin: 0;
        line-height: 1.25;
    }

    .doc-meta {
        font-size: .78rem;
        color: #6c757d;
    }

    .doc-progress .progress {
        height: 12px;
        border-radius: 999px;
    }

    .doc-progress .progress-bar {
        font-size: .70rem;
        line-height: 12px;
    }

    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #932136, #870820);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                <div>
                    <h4 class="mb-1">
                        <i class="bi bi-file-earmark-text"></i> Detail Penerimaan Dokumen
                    </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('de.penerimaan-dokumen') }}">Penerimaan Dokumen</a>
                            </li>
                            <li class="breadcrumb-item active">{{ $pengajuan->nomor_pengajuan }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('de.penerimaan-dokumen') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    @php
    $allowed = [
    \App\Models\PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
    \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM,
    \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
    \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
    ]; // ini contoh, bisa dinamis dari config/db/request

    $log = $pengajuan->latestRelevantStatusLog($allowed);
    @endphp
    <!-- Status Alert -->
    @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI)
    <div class="alert alert-info alert-permanent mb-4">
        <i class="bi bi-hourglass-split"></i>
        <strong>Menunggu Penerimaan Dokumen</strong>
        <br>
        Program studi sedang dalam proses mengirim dokumen
    </div>
    @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM)
    <div class="alert alert-info alert-permanent mb-4">
        <i class="bi bi-send"></i>
        <strong>Dokumen telah dikirim</strong>
        <br>
        Mohon download file yang telah diupload oleh program studi sebagai berikut
    </div>
    @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA)
    <div class="alert alert-success alert-permanent mb-4">
        <i class="bi bi-check-circle"></i>
        <strong>Dokumen telah diterima</strong>
        <br>
        Dokumen siap untuk tahap selanjutnya yaitu penugasan validator
        {{-- Mohon menunggu proses validasi dokumen selesai dilakukan. --}}
        {{-- Diterima pada {{ $pengajuan->tanggal_draft_borang?->locale('id')->translatedFormat('d M Y H:i') ?? '-' }} --}}
    </div>
    @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI)
    <div class="alert alert-success alert-permanent mb-4">
        <i class="bi bi-ui-checks"></i>
        <strong>Dokumen telah dikirim</strong>
        <br>
        Dokumen siap untuk tahap selanjutnya yaitu penugasan validator
        {{-- Menunggu proses validasi --}}
    </div>
    @endif

    {{-- ROW 2: Validator / Tugaskan Validator --}}
    <div class="row">
        <div class="col-md-12">
            @if($canAssignValidator)

            <div class="card border-primary mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-check"></i>
                            Validasi Dokumen
                        </h5>

                        @if($canAssignValidator)
                        <a href="{{ route('de.penerimaan-dokumen.assign-validator', $pengajuan->id) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-person-plus"></i>
                            {{ $currentValidator ? 'Tugaskan Ulang Validator' : 'Tugaskan Validator' }}
                        </a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @if($currentValidator)
                    <div class="row align-items-start">
                        {{-- Validator Info --}}
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="avatar-circle mx-auto mb-2" style="width: 80px; height: 80px; font-size: 2rem;">
                                    {{ substr($currentValidator->user->name, 0, 1) }}
                                </div>
                                <h6 class="fw-bold text-wrap">{{ $currentValidator->user->name }}</h6>
                                <small class="text-muted text-wrap">{{ $currentValidator->user->email }}</small>
                            </div>
                        </div>

                        {{-- Validator Status --}}
                        <div class="col-md-5">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Role</label>
                                    <p class="fw-bold mb-0">
                                        <span class="badge bg-success">
                                            {{ $currentValidator->role->alias ?? 'Validator' }}
                                        </span>
                                    </p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Status Penawaran</label>
                                    <p class="mb-0">
                                        @php
                                        $penawaranBadge = match($currentValidator->status_penawaran) {
                                        'accepted' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Diterima'],
                                        'rejected' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Ditolak'],
                                        'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Menunggu'],
                                        default => ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Unknown'],
                                        };
                                        @endphp
                                        <span class="badge bg-{{ $penawaranBadge['class'] }}">
                                            <i class="bi bi-{{ $penawaranBadge['icon'] }}"></i>
                                            {{ $penawaranBadge['text'] }}
                                        </span>
                                    </p>
                                </div>

                                @if($currentValidator->status_penawaran === 'accepted')
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Status Pekerjaan</label>
                                    <p class="mb-0">
                                        <span class="badge bg-info">
                                            {{ $currentValidator->status_label ?? 'Belum Mulai' }}
                                        </span>
                                    </p>
                                </div>
                                @endif

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Ditugaskan</label>
                                    <p class="mb-0">
                                        @if($currentValidator->created_at)
                                        {{ $currentValidator->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                        @else
                                        -
                                        @endif
                                    </p>
                                </div>

                                @if($currentValidator->responded_at)
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Respon</label>
                                    <p class="mb-0">{{ $currentValidator->responded_at->locale('id')->translatedFormat('d M Y H:i') }}</p>
                                </div>
                                @endif

                                @if($currentValidator->status_penawaran === 'accepted' && $currentValidator->approved_at)
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Selesai Review</label>
                                    <p class="mb-0">{{ $currentValidator->approved_at->locale('id')->translatedFormat('d M Y H:i') }}</p>
                                </div>
                                @endif
                            </div>

                            <div class="d-flex gap-2 mt-2">
                                @if($currentValidator->status_penawaran === 'pending')
                                <span class="badge bg-warning">
                                    <i class="bi bi-hourglass-split"></i>
                                    Menunggu validator menerima penawaran
                                </span>
                                @elseif($currentValidator->status_penawaran === 'rejected')
                                <a href="{{ route('de.penerimaan-dokumen.assign-validator', $pengajuan->id) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-arrow-repeat"></i>
                                    Tugaskan Validator Baru
                                </a>
                                @elseif($currentValidator->status_penawaran === 'accepted')
                                @if($currentValidator->borangValidation)
                                <a href="{{ route('validator.borang.show', $currentValidator->id) }}" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="bi bi-eye"></i>
                                    Lihat Progres Validasi
                                </a>
                                @endif
                                @endif
                            </div>
                        </div>

                        {{-- ✅ NEW: Surat Tugas Validator --}}
                        <div class="col-md-4">
                            @php
                            $suratTugas = $pengajuan->dokumen
                            ->where('jenis_dokumen', 'surat_tugas_validator_dokumen')
                            ->where('is_latest', true)
                            ->first();
                            @endphp

                            <div class="card border-secondary h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="bi bi-file-earmark-text"></i> Surat Tugas
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($suratTugas)
                                    <div class="d-flex align-items-start gap-2 mb-3">
                                        <div class="doc-ico bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <p class="doc-title fw-semibold mb-0">
                                                {{ Str::limit($suratTugas->original_filename, 30) }}
                                            </p>
                                            <div class="doc-meta">
                                                @if($suratTugas->file_size)
                                                <div>{{ number_format($suratTugas->file_size / 1024, 2) }} KB</div>
                                                @endif
                                                <div>
                                                    <span class="badge bg-info">Versi {{ $suratTugas->versi }}</span>
                                                </div>
                                                <div>
                                                    <small>Dibuat: {{ $suratTugas->created_at->locale('id')->translatedFormat('d M Y H:i') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('de.penerimaan-dokumen.download-surat-tugas-validator', $pengajuan->id) }}" class="btn btn-success btn-sm w-100" target="_blank">
                                            <i class="bi bi-eye"></i> Lihat Surat Tugas
                                        </a>
                                        <button type="button" class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalUploadSuratTugas">
                                            <i class="bi bi-upload"></i> Upload Ulang
                                        </button>
                                    </div>

                                    {{-- @if($suratTugas->keterangan)
                                    <div class="mt-3 pt-3 border-top">
                                        <small class="text-muted fst-italic">
                                            <i class="bi bi-info-circle"></i>
                                            {{ $suratTugas->keterangan }}
                                    </small>
                                </div>
                                @endif --}}
                                @else
                                <div class="text-center py-3">
                                    <i class="bi bi-file-earmark-x" style="font-size: 2rem; color: #ddd;"></i>
                                    <p class="text-muted mt-2 mb-3 small">
                                        Surat tugas belum tersedia
                                    </p>
                                    <button type="button" class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalUploadSuratTugas">
                                        <i class="bi bi-upload"></i> Upload Surat Tugas
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @else
                {{-- No Validator Assigned --}}
                <div class="text-center">
                    <i class="bi bi-person-x" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-3 mb-3">
                        Belum ada validator yang ditugaskan untuk review dokumen
                    </p>

                    @if($canAssignValidator)
                    <a href="{{ route('de.penerimaan-dokumen.assign-validator', $pengajuan->id) }}" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i>
                        Tugaskan Validator Sekarang
                    </a>
                    @else
                    <p class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Dokumen harus lengkap terlebih dahulu sebelum menugaskan validator
                    </p>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ROW 1: Daftar Dokumen (FULL WIDTH) --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Dokumen yang Diupload oleh PS</h5>

                    @if($docCompleteness['is_complete'])
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle"></i> Lengkap
                    </span>
                    @else
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-exclamation-triangle"></i> Belum Lengkap
                    </span>
                    @endif
                </div>

                {{-- Progress menyatu di header --}}
                <div class="doc-progress mt-2">
                    <div class="d-flex justify-content-between small">
                        <span>Kelengkapan Dokumen</span>
                        <span class="fw-semibold">{{ $docCompleteness['percentage'] }}%</span>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-bar {{ $docCompleteness['is_complete'] ? 'bg-success' : 'bg-warning' }}" style="width: {{ $docCompleteness['percentage'] }}%" role="progressbar" aria-valuenow="{{ $docCompleteness['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    @foreach($docCompleteness['details'] as $jenis => $detail)
                    @php
                    $dokumen = $uploadedDocuments[$jenis] ?? null;
                    @endphp

                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="doc-item {{ $detail['uploaded'] ? 'uploaded' : 'missing' }}">
                            <div class="d-flex align-items-start gap-2">
                                <div class="doc-ico {{ $detail['uploaded'] ? 'bg-success' : 'bg-danger' }} bg-opacity-10 text-{{ $detail['uploaded'] ? 'success' : 'danger' }}">
                                    <i class="bi bi-{{ $detail['uploaded'] ? 'check-lg' : 'x-lg' }}"></i>
                                </div>

                                <div class="flex-grow-1 min-w-0">
                                    <p class="doc-title fw-semibold">{{ $detail['label'] }}</p>

                                    @if($dokumen)
                                    <div class="doc-meta">
                                        <div class="text-wrap text-truncate" title="{{ $dokumen->original_filename ?? '-' }}">
                                            {{ Str::limit($dokumen->original_filename ?? '-', 40) }}
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        @if($dokumen->path_file || $dokumen->template_link)
                                        <a href="{{ $dokumen->download_url }}" class="btn btn-sm btn-success" target="_blank">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                        @endif
                                    </div>
                                    @else
                                    <span class="badge bg-danger mt-1">Belum Diupload</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    {{-- Dokumen Suplemen (jika ada tapi tidak masuk di details) --}}
                    @if(!empty($uploadedDocuments['suplemen']) && empty($docCompleteness['details']['suplemen']))
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="doc-item uploaded">
                            <div class="d-flex align-items-start gap-2">
                                <div class="doc-ico bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-plus-lg"></i>
                                </div>

                                <div class="flex-grow-1">
                                    <p class="doc-title fw-semibold mb-0">Dokumen Suplemen</p>
                                    <div class="doc-meta">
                                        <div class="text-wrap text-truncate" title="{{ $uploadedDocuments['suplemen']->original_filename }}">
                                            {{ Str::limit($uploadedDocuments['suplemen']->original_filename, 40) }}
                                        </div>
                                        <div>
                                            @if($uploadedDocuments['suplemen']->created_at)
                                            {{ $uploadedDocuments['suplemen']->created_at->locale('id')->translatedFormat('d M Y') }}
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <a href="{{ $uploadedDocuments['suplemen']->download_url }}" class="btn btn-sm btn-info" target="_blank">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column -->
    <div class="col-lg-8 mb-4">
        <!-- Informasi Pengajuan -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle"></i> Informasi Penerimaan Dokumen
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>Tanggal Dokumen Diupload</th>
                        <td>
                            : {{ $pengajuan->tanggal_draft_borang
                                    ? $pengajuan->tanggal_draft_borang->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Status Pengiriman Dokumen</th>
                        <td>: {!! $pengajuan->getCustomBadgeLastStatus('draft_borang', 'de') !!}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        @php
        $filterStatuses = [
        \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM,
        \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
        \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
        ];

        $logs = $pengajuan->statusLog
        ->whereIn('status_to', $filterStatuses)
        ->sortBy('changed_at')
        ->unique('status_to')
        ->values();
        @endphp

        <!-- Status Log -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history"></i> Riwayat Status
                </h5>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                @if($logs->count() > 0)
                <div class="timeline">
                    @foreach($logs as $log)
                    <div class="timeline-item mb-3">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-circle-fill text-primary" style="font-size: 8px;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>
                                    {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label_long_for']['de'] ?? $log->status_to }}
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
    </div>
</div>
</div>

@if($currentValidator)
<div class="modal fade" id="modalUploadSuratTugas" tabindex="-1" aria-labelledby="modalUploadSuratTugasLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalUploadSuratTugasLabel">
                    <i class="bi bi-upload"></i> Upload Surat Tugas Validator Dokumen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('de.penerimaan-dokumen.upload-surat-tugas-validator', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" id="formUploadSuratTugas">
                @csrf
                <div class="modal-body">
                    @php
                    $suratTugas = $pengajuan->dokumen
                    ->where('jenis_dokumen', 'surat_tugas_validator_dokumen')
                    ->where('is_latest', true)
                    ->first();
                    @endphp

                    @if($suratTugas)
                    <div class="alert alert-info alert-permanent mb-3">
                        <i class="bi bi-info-circle"></i>
                        <strong>File Saat Ini:</strong><br>
                        {{ $suratTugas->original_filename }}<br>
                        <small>Versi {{ $suratTugas->versi }} • {{ $suratTugas->created_at->locale('id')->translatedFormat('d M Y H:i') }}</small>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            File Surat Tugas (PDF) <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file_surat_tugas" id="file_surat_tugas_upload" class="form-control @error('file_surat_tugas') is-invalid @enderror" accept=".pdf" required>
                        <small class="text-muted">
                            Format: PDF | Maksimal: 5MB
                        </small>
                        <div id="suratTugasUploadPreview" class="mt-2"></div>
                        @error('file_surat_tugas')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-warning alert-permanent">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Perhatian:</strong>
                        @if($suratTugas)
                        Surat tugas yang diupload akan menggantikan surat tugas sebelumnya (Versi {{ $suratTugas->versi }}).
                        Surat tugas lama akan tetap tersimpan sebagai riwayat.
                        @else
                        Upload surat tugas untuk validator <strong>{{ $currentValidator->user->name }}</strong>.
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitUpload">
                        <i class="bi bi-upload"></i> Upload Surat Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let fileInput = document.getElementById('file_surat_tugas_upload');
        let preview = document.getElementById('suratTugasUploadPreview');
        if (fileInput && preview) {
            fileInput.addEventListener('change', function(e) {
                preview.innerHTML = '';
                if (!e.target.files || !e.target.files.length) return;
                let file = e.target.files[0];
                let size = file.size / 1024 / 1024;
                if (file.type !== 'application/pdf') {
                    preview.innerHTML = '<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-x-circle"></i> File harus berformat PDF<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                    e.target.value = '';
                    return;
                }
                if (size > 5) {
                    preview.innerHTML = '<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-x-circle"></i> Ukuran file terlalu besar (' + size.toFixed(2) + ' MB). Maksimal 5 MB<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                    e.target.value = '';
                    return;
                }
                preview.innerHTML = '<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle"></i> <strong>' + file.name + '</strong> (' + size.toFixed(2) + ' MB)<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            });
        }
        let form = document.getElementById('formUploadSuratTugas');
        let btn = document.getElementById('btnSubmitUpload');
        if (form && btn) {
            form.addEventListener('submit', function() {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
            });
        }
    });

</script>
@endpush
