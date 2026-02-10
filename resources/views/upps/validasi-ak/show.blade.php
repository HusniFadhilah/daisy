{{-- resources/views/upps/validasi-ak/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Validasi AK')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.validasi-ak') }}">Validasi AK</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-clipboard-check"></i> Detail Validasi AK
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.validasi-ak') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
            \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
            ]; // ini contoh, bisa dinamis dari config/db/request

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Proses Validasi AK</strong><br>
                Permohonan akreditasi program studi memasuki tahap validasi penilaian AK dan Sekretariat telah menugaskan validator
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Proses Validasi AK</strong><br>
                Permohonan akreditasi program studi memasuki tahap validasi penilaian AK dan Sekretariat telah menugaskan validator
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Proses Validasi AK</strong><br>
                Permohonan akreditasi program studi memasuki tahap validasi penilaian AK dan Sekretariat telah menugaskan validator
            </div>
            @endif

            <!-- Informasi Validasi AK -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Validasi AK
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Tanggal Penilaian Dimulai</th>
                            <td>
                                : {{ $pengajuan->tanggal_ak_mulai
                                    ? $pengajuan->tanggal_ak_mulai->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Validasi AK</th>
                            <td>
                                : {{ $pengajuan->tanggal_validasi_ak
                                    ? $pengajuan->tanggal_validasi_ak->format('d M Y H:i')
                                    : '-' }}
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
                            <th>Status Validasi AK</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('validasi_ak', 'upps', 'label_long_for') !!}</td>
                        </tr>
                    </table>
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
                    \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
                    ];

                    $logs = $pengajuan->statusLog
                    ->whereIn('status_to', $filterStatuses)
                    ->sortBy('changed_at')
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
                                    \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
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
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Validasi AK
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Proses Validasi AK:</strong>
                    </p>
                    <ol class="small mb-0 ps-3">
                        <li>Asesor ditugaskan untuk Asesmen Kecukupan</li>
                        <li>Asesor memulai penilaian kecukupan dokumen</li>
                        <li>Asesor menyelesaikan penilaian</li>
                        <li>Hasil penilaian divalidasi oleh tim LAMDEPILAR</li>
                        <li>Hasil validasi selesai dan dinyatakan valid</li>
                        <li>Hasil dilaporkan dan proses dilanjutkan</li>
                    </ol>

                    <hr>

                    <p class="small mb-2">
                        <strong>Catatan:</strong>
                    </p>
                    <p class="small text-muted mb-0">
                        Validasi AK memastikan bahwa penilaian yang dilakukan asesor telah sesuai dengan standar dan prosedur yang ditetapkan LAMDEPILAR.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
