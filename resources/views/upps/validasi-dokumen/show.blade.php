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
            <div class="alert alert-info alert-permanent">
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
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Dokumen memerlukan revisi</strong>
                <br>
                LAMDEPILAR telah memberikan catatan perbaikan yang perlu dilakukan.<br>
                Mohon memeriksa catatan validator dan lakukan perbaikan pada dokumen<br>
                kemudian upload ulang dokumen yang telah diperbaiki.
                <div class="mt-2">
                    <a href="{{ route('upps.penerimaan-dokumen.upload', $pengajuan->id) }}" class="btn btn-warning btn-sm">
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
                                    ? $pengajuan->tanggal_validasi_borang_assigned->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Validasi Selesai</th>
                            <td>
                                : {{ $pengajuan->tanggal_validasi_borang_selesai
                                    ? $pengajuan->tanggal_validasi_borang_selesai->locale('id')->translatedFormat('d M Y H:i')
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

            @include('upps.validasi-dokumen.validation-summary')
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
                    <p class="text-muted text-center mb-0">Belum ada riwayat validasi</p>
                    @endif
                </div>
            </div>

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
