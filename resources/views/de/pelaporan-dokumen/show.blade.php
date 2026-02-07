@extends('layouts.template.app')

@section('title', 'Detail Pelaporan Validasi Dokumen')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.pelaporan-dokumen') }}">Pelaporan Validasi Dokumen</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-file-earmark-text"></i> Detail Pelaporan Validasi Dokumen
            </h5>
            <small class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('de.pelaporan-dokumen') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Informasi & Dokumen -->
        <div class="col-lg-8 mb-4">

            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
            ]; // ini contoh, bisa dinamis dari config/db/request

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Pelaporan Validasi Dokumen</strong><br>
                Menunggu pelaporan hasil validasi dokumen
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Pelaporan Validasi Dokumen telah Dilaksanakan</strong><br>
                Tahap selanjutnya adalah penugasan asesor AK
            </div>
            @endif

            <!-- Laporan Validasi Dokumen -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-pdf"></i> Laporan Validasi Dokumen (LKLED)
                    </h5>
                </div>
                <div class="card-body">
                    @if($laporanValidasi)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 32px;"></i>
                            <div>
                                <strong>{{ $laporanValidasi->title }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $laporanValidasi->original_name }}
                                    ({{ number_format($laporanValidasi->size / 1024, 2) }} KB)
                                </small>
                                <br>
                                <small class="text-muted">
                                    Diupload: {{ $laporanValidasi->uploaded_at->format('d M Y H:i') }}
                                    @if($laporanValidasi->uploaded_by)
                                    | Oleh: {{ $laporanValidasi->uploadedBy->name ?? '-' }}
                                    @endif
                                </small>
                                @if($laporanValidasi->version > 1)
                                <br>
                                <small class="badge bg-info mt-1">Versi {{ $laporanValidasi->version }}</small>
                                @endif
                            </div>
                        </div>
                        <div>
                            <a href="{{ Storage::url($laporanValidasi->path) }}" target="_blank" class="btn btn-primary btn-sm">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2">Validator belum mengupload laporan validasi dokumen</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informasi Program Studi -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Pelaporan Validasi Dokumen</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        {{-- <tr>
                            <th style="width:40%">Program Studi</th>
                            <td>: {{ $pengajuan->studyProgram->name }}</td>
                        </tr>
                        <tr>
                            <th>Universitas</th>
                            <td>: {{ $pengajuan->studyProgram->university->name }}</td>
                        </tr> --}}
                        <tr>
                            <th style="width:40%">Tanggal Pelaporan Validasi Dokumen</th>
                            <td>
                                : {{ $pengajuan->tanggal_pelaporan_validasi_borang
                                    ? $pengajuan->tanggal_pelaporan_validasi_borang->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Pelaporan Validasi Dokumen</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('pelaporan_dokumen') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Informasi Validator -->
            @if($validatorAssignment)
            <div class="card mt-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Informasi Validator</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width:40%">Nama Validator</th>
                            <td>: {{ $validatorAssignment->user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>: {{ $validatorAssignment->user->email }}</td>
                        </tr>
                        <tr>
                            <th>Status Penawaran</th>
                            <td>: <span class="badge bg-success">{{ ucfirst($validatorAssignment->status_penawaran) }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Status & Timeline -->
        <div class="col-lg-4">
            <!-- Status Pelaporan -->
            <div class="card mb-4">
                <div class="card-header bg-{{ $statusPelaporan['class'] }} text-white">
                    <h5 class="mb-0">Status Pelaporan</h5>
                </div>
                <div class="card-body text-center py-4">
                    <i class="bi bi-{{ $statusPelaporan['icon'] }}" style="font-size: 3rem; color: var(--bs-{{ $statusPelaporan['class'] }});"></i>
                    <h5 class="mt-3 mb-1">{{ $statusPelaporan['label'] }}</h5>
                    <small class="text-muted">{{ $statusPelaporan['description'] }}</small>
                </div>
            </div>

            @php
            $filterStatuses = [
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
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
@endsection
