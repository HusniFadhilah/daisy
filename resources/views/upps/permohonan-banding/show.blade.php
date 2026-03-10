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
                <i class="bi bi-envelope"></i> Detail Permohonan Banding
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
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
            \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
            \App\Models\PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
            \App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
            ]; // ini contoh, bisa dinamis dari config/db/request

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DITERIMA)
            <div class="alert alert-success alert-permanent mb-4">
                <i class="bi bi-check-circle"></i>
                <strong>Permohonan Banding telah diterima oleh LAMDEPILAR</strong>
                <br>
                Diterima pada {{ $pengajuan->tanggal_permohonan_banding->locale('id')->translatedFormat('d M Y H:i') }}
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN)
            <div class="alert alert-warning alert-permanent mb-4">
                <i class="bi bi-hourglass-split"></i>
                <strong>Menunggu tanggapan dari LAMDEPILAR</strong>
                <br>
                Permohonan Banding telah dikirim pada {{ $pengajuan->tanggal_permohonan_banding->locale('id')->translatedFormat('d M Y H:i') }}
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI)
            <div class="alert alert-danger alert-permanent mb-4">
                <i class="bi bi-x-circle"></i>
                <strong>Permohonan Banding telah Ditutup</strong>
                <br>
                Karena masa sanggah telah selesai pada {{ $pengajuan->tanggal_masa_sanggah_selesai->locale('id')->translatedFormat('d M Y H:i') }}
            </div>
            @endif

            <!-- Informasi Permohonan Banding -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Permohonan Banding</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Nomor Permohonan</th>
                            <td>: {{ $pengajuan->nomor_permohonan }}</td>
                        </tr>
                        <tr>
                            <th style="width:45%">Tanggal Permohonan Banding</th>
                            <td>: {{ $pengajuan->tanggal_permohonan_banding
                                    ? $pengajuan->tanggal_permohonan_banding->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Penerimaan Banding</th>
                            <td>:
                                {{ $pengajuan->tanggal_penerimaan_banding
                                    ? $pengajuan->tanggal_penerimaan_banding->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Permohonan Banding</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('permohonan_banding','upps','label_long_for','text-dark') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- File Permohonan Banding -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-pdf"></i> File Permohonan Banding
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $dokumen = $pengajuan->dokumen
                    ->where('jenis_dokumen', 'surat_permohonan_banding')
                    ->where('is_latest', true)
                    ->first();
                    @endphp

                    @if($dokumen)
                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between rounded">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $dokumen->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($dokumen->file_size / 1024, 2) }} KB •
                                    Diupload: {{ $dokumen->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.permohonan-banding.download', $pengajuan->id) }}" class="btn btn-success btn-md mt-2">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada file Permohonan Banding</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Riwayat Status -->
            @php
            $filterStatuses = [
            \App\Models\PengajuanAkreditasi::STATUS_DRAFT,
            \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
            \App\Models\PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
            \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK,
            ];

            $logs = $pengajuan->statusLog
            ->whereIn('status_to', $filterStatuses)
            ->sortBy('created_at')
            ->unique('status_to')
            ->values();
            @endphp

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
                                    <i class="bi bi-circle-fill text-secondary" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label_long_for']['upps'] ?? $log->status_to }}
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
@endsection
