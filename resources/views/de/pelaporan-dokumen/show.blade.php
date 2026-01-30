@extends('layouts.template.app')

@section('title', 'Detail Monitoring Pelaporan Dokumen')

@push('styles')
<style>
    .info-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .info-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }

    .status-success {
        background: #28a745;
    }

    .status-warning {
        background: #ffc107;
    }

    .status-danger {
        background: #dc3545;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -24px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: white;
        border: 3px solid;
        z-index: 1;
    }

    .timeline-marker.success {
        border-color: #28a745;
    }

    .timeline-marker.warning {
        border-color: #ffc107;
    }

    .timeline-marker.danger {
        border-color: #dc3545;
    }

    .file-preview {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .file-preview:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }

    .file-icon {
        font-size: 3rem;
        color: #dc3545;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('de.pelaporan-dokumen') }}">
                    <i class="bi bi-arrow-left"></i> Monitoring Pelaporan Dokumen
                </a>
            </li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-text"></i> Detail Monitoring Pelaporan Dokumen
            </h4>
            <p class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
        </div>
        <div>
            <span class="badge bg-{{ $statusPelaporan['class'] }}" style="font-size: 14px; padding: 8px 16px;">
                <i class="bi bi-{{ $statusPelaporan['icon'] }}"></i> {{ $statusPelaporan['label'] }}
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Info Cards -->
        <div class="col-lg-4 mb-4">
            <!-- Program Studi Info -->
            <div class="card info-card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Program Studi
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="40%" class="text-muted">Program Studi</td>
                            <td><strong>{{ $pengajuan->studyProgram->name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jenjang</td>
                            <td>{{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Universitas</td>
                            <td>{{ $pengajuan->studyProgram->university->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nomor Permohonan</td>
                            <td><code>{{ $pengajuan->nomor_pengajuan }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status Permohonan</td>
                            <td>
                                <span class="badge bg-info text-wrap">
                                    {{ $pengajuan->status_label }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Validator Info -->
            @if($validatorAssignment)
            <div class="card info-card mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-person-check"></i> Informasi Validator
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-circle bg-success text-white me-3" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold;">
                            {{ strtoupper(substr($validatorAssignment->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="fw-bold">{{ $validatorAssignment->user->name }}</div>
                            <small class="text-muted">{{ $validatorAssignment->user->email }}</small>
                        </div>
                    </div>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="50%" class="text-muted">Role</td>
                            <td><span class="badge bg-success">{{ $validatorAssignment->role_selected->name ?? '-' }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status Penawaran</td>
                            <td><span class="badge bg-success">{{ ucfirst($validatorAssignment->status_penawaran) }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif

            <!-- Status Pelaporan Card -->
            <div class="card info-card">
                <div class="card-header bg-{{ $statusPelaporan['class'] }} text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-{{ $statusPelaporan['icon'] }}"></i> Status Pelaporan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center py-3">
                        <i class="bi bi-{{ $statusPelaporan['icon'] }}" style="font-size: 3rem; color: var(--bs-{{ $statusPelaporan['class'] }});"></i>
                        <h5 class="mt-3">{{ $statusPelaporan['label'] }}</h5>
                        <p class="text-muted mb-0">{{ $statusPelaporan['description'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Laporan & Timeline -->
        <div class="col-lg-8">
            <!-- Laporan Validasi Section -->
            <div class="card info-card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-file-earmark-pdf"></i> Laporan Validasi Dokumen (LKLED)
                    </h6>
                </div>
                <div class="card-body">
                    @if($laporanValidasi)
                    <div class="file-preview">
                        <i class="bi bi-file-earmark-pdf file-icon"></i>
                        <h6 class="mt-3">{{ $laporanValidasi->title }}</h6>
                        <p class="text-muted mb-3">
                            <small>
                                <i class="bi bi-file-earmark"></i> {{ $laporanValidasi->original_name }}<br>
                                <i class="bi bi-hdd"></i> {{ number_format($laporanValidasi->size / 1024, 2) }} KB<br>
                                <i class="bi bi-calendar"></i> Diupload: {{ $laporanValidasi->uploaded_at->format('d F Y, H:i') }}<br>
                                @if($laporanValidasi->uploaded_by)
                                <i class="bi bi-person"></i> Oleh: {{ $laporanValidasi->uploadedBy->name ?? '-' }}
                                @endif
                            </small>
                        </p>
                        <a href="{{ Storage::url($laporanValidasi->path) }}" target="_blank" class="btn btn-danger">
                            <i class="bi bi-download"></i> Download Laporan
                        </a>
                    </div>

                    @if($laporanValidasi->version > 1)
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle"></i> Ini adalah versi {{ $laporanValidasi->version }} dari laporan validasi.
                    </div>
                    @endif
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-x" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="text-muted mt-3 mb-0">Validator belum mengupload laporan validasi dokumen</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Timeline History -->
            <div class="card info-card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h6>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @if($pengajuan->statusLog && $pengajuan->statusLog->count() > 0)
                    <div class="timeline">
                        @foreach($pengajuan->statusLog->take(10) as $log)
                        @php
                        $isRelevant = in_array($log->status_to, [
                        \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                        \App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
                        \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                        ]);

                        if ($log->status_to === \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN) {
                        $markerClass = 'success';
                        } elseif ($isRelevant) {
                        $markerClass = 'warning';
                        } else {
                        $markerClass = 'danger';
                        }
                        @endphp
                        <div class="timeline-item">
                            <div class="timeline-marker {{ $markerClass }}"></div>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-{{ $markerClass }} mb-1">
                                                {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label_for']['de'] ?? $log->status_to }}
                                            </span>
                                            <p class="mb-0 small text-muted">
                                                {{ $log->keterangan }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-person"></i> {{ $log->changedBy->name ?? 'System' }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> {{ $log->changed_at->format('d M Y, H:i') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-clock-history" style="font-size: 2rem; color: #dee2e6;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada riwayat status</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
