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
            <div class="d-flex justify-content-between align-items-center">
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

    {{-- ROW 1: Daftar Dokumen (FULL WIDTH) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Dokumen yang Diupload PS</h5>

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
                                            <div>
                                                {{ $dokumen->file_size_formatted ?? '-' }}
                                                @if($dokumen->created_at)
                                                • {{ $dokumen->created_at->format('d M Y H:i') }}
                                                @endif
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
                                        <span class="badge bg-danger mt-1">Belum Diupload oleh PS</span>
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
                                                {{ $uploadedDocuments['suplemen']->file_size_formatted ?? '-' }}
                                                @if($uploadedDocuments['suplemen']->created_at)
                                                • {{ $uploadedDocuments['suplemen']->created_at->format('d M Y') }}
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

    {{-- ROW 2: Validator / Tugaskan Validator --}}
    <div class="row mb-4">
        <div class="col-md-12">
            @if($canAssignValidator)

            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-check"></i>
                            Validasi Dokumen
                        </h5>

                        @if($canAssignValidator)
                        <a href="{{ route('de.penerimaan-dokumen.assign-validator.form', $pengajuan->id) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-person-plus"></i>
                            {{ $currentValidator ? 'Tugaskan Ulang Validator' : 'Tugaskan Validator' }}
                        </a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @if($currentValidator)
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="avatar-circle mx-auto mb-2" style="width: 80px; height: 80px; font-size: 2rem;">
                                    {{ substr($currentValidator->user->name, 0, 1) }}
                                </div>
                                <h6 class="fw-bold">{{ $currentValidator->user->name }}</h6>
                                <small class="text-muted">{{ $currentValidator->user->email }}</small>
                            </div>
                        </div>

                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Role</label>
                                    <p class="fw-bold mb-0">
                                        <span class="badge bg-success">
                                            {{ $currentValidator->role->alias ?? 'Validator' }}
                                        </span>
                                    </p>
                                </div>

                                <div class="col-md-4 mb-3">
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
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Status Pekerjaan</label>
                                    <p class="mb-0">
                                        <span class="badge bg-info">
                                            {{ $currentValidator->status_label ?? 'Belum Mulai' }}
                                        </span>
                                    </p>
                                </div>
                                @endif

                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Ditugaskan</label>
                                    <p class="mb-0">
                                        @if($currentValidator->created_at)
                                        {{ $currentValidator->created_at->format('d M Y H:i') }}
                                        @else
                                        -
                                        @endif
                                    </p>
                                </div>

                                @if($currentValidator->responded_at)
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Respon</label>
                                    <p class="mb-0">{{ $currentValidator->responded_at->format('d M Y H:i') }}</p>
                                </div>
                                @endif

                                @if($currentValidator->status_penawaran === 'accepted' && $currentValidator->approved_at)
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Selesai Review</label>
                                    <p class="mb-0">{{ $currentValidator->approved_at->format('d M Y H:i') }}</p>
                                </div>
                                @endif
                            </div>

                            <div class="d-flex gap-2 mt-3">
                                @if($currentValidator->status_penawaran === 'pending')
                                <span class="badge bg-warning">
                                    <i class="bi bi-hourglass-split"></i>
                                    Menunggu validator menerima penawaran
                                </span>
                                @elseif($currentValidator->status_penawaran === 'rejected')
                                <a href="{{ route('de.penerimaan-dokumen.assign-validator.form', $pengajuan->id) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-arrow-repeat"></i>
                                    Tugaskan Validator Baru
                                </a>
                                @elseif($currentValidator->status_penawaran === 'accepted')
                                @if($currentValidator->borangValidation)
                                <a href="{{ route('validator.borang.show', $currentValidator->id) }}" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="bi bi-eye"></i>
                                    Lihat Progress Validasi
                                </a>
                                @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-person-x" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3 mb-3">
                            Belum ada validator yang ditugaskan untuk review dokumen
                        </p>

                        @if($canAssignValidator)
                        <a href="{{ route('de.penerimaan-dokumen.assign-validator.form', $pengajuan->id) }}" class="btn btn-primary">
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

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8 mb-4">
            <!-- Informasi Pengajuan -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Penerimaan Dokumen</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Nomor Permohonan Akreditasi</th>
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
                            <th>Akreditasi Kedaluwarsa</th>
                            <td>: {{ $pengajuan->studyProgram->days_left ? $pengajuan->studyProgram->days_left.' hari lagi': '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Permohonan</th>
                            <td>: {{ $pengajuan->jenis_akreditasi_label ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Pemohon</th>
                            <td>: {{ $pengajuan->pengaju->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status Penerimaan Dokumen</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('borang_final','de','label_long_for') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            @php
            $filterStatuses = [
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
            ];

            $logs = $pengajuan->statusLog
            ->whereIn('status_to', $filterStatuses)
            ->sortBy('changed_at');
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
        </div>
    </div>
</div>
@endsection
