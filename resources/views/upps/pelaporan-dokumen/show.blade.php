{{-- resources/views/upps/pelaporan-dokumen/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pelaporan Validasi Dokumen')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pelaporan-dokumen') }}">Pelaporan Validasi Dokumen</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-file-earmark-diff"></i> Detail Pelaporan Validasi Dokumen
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.pelaporan-dokumen') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
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
                Menunggu pelaporan hasil validasi dokumen oleh LAMDEPILAR
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Pelaporan Validasi Dokumen telah Dilaksanakan</strong><br>
                Sekretariat LAMDEPILAR menyampaikan laporan tentang validasi dokumen dan menyatakan dokumen akreditasi memasuki tahap asesmen kecukupan. Dan menyatakan bahwa:
                <ol>
                    <li>
                        Sekretariat LAMDEPILAR akan menugaskan asesor untuk melakukan penilaian AK
                    </li>
                    <li>
                        Program Studi diharapkan dapat mengikuti proses selanjutnya
                    </li>
                </ol>
            </div>
            @endif

            <!-- Informasi Pelaporan -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pelaporan Validasi Dokumen
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width:40%">Tanggal Validasi Dokumen</th>
                            <td>
                                : {{ $pengajuan->tanggal_validasi_borang_selesai
                                    ? $pengajuan->tanggal_validasi_borang_selesai->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pelaporan Validasi Dokumen</th>
                            <td>
                                : {{ $pengajuan->tanggal_pelaporan_validasi_borang
                                    ? $pengajuan->tanggal_pelaporan_validasi_borang->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Pelaporan Validasi Dokumen</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('pelaporan_dokumen', 'upps','label_long_for') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Timeline Pelaporan -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                    \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                    ];

                    $logs = $pengajuan->statusLog
                    ->whereIn('status_to', $filterStatuses)
                    ->sortBy('created_at')
                    ->unique('status_to')
                    ->values();
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
                                    \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
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
                    <p class="text-muted text-center mb-0">Belum ada riwayat pelaporan</p>
                    @endif
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Proses Pelaporan Validasi Dokumen:</strong>
                    </p>
                    <ol class="small mb-0 ps-3">
                        <li>Dokumen selesai divalidasi</li>
                        <li>LAMDEPILAR melaporkan hasil validasi</li>
                        <li>Sistem menugaskan asesor untuk Asesmen Kecukupan</li>
                        <li>Asesor melakukan penilaian kecukupan dokumen</li>
                        <li>Hasil asesmen kecukupan dilaporkan</li>
                        <li>Proses dilanjutkan ke tahap berikutnya</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
