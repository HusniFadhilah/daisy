{{-- resources/views/upps/penugasan-ak/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Penugasan Asesor AK')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penugasan-ak') }}">Penugasan Asesor AK</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-person-check"></i> Detail Penugasan Asesor AK
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.penugasan-ak') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
            \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            ]; // ini contoh, bisa dinamis dari config/db/request

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Penugasan Asesor AK</strong><br>
                Sekretariat sedang menugaskan asesor untuk melakukan penilaian AK
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Penugasan Asesor AK</strong><br>
                Sekretariat telah menugaskan asesor untuk melakukan penilaian AK
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Penugasan Asesor AK</strong><br>
                Sekretariat telah menugaskan asesor untuk melakukan penilaian AK
            </div>
            @endif

            <!-- Informasi Penugasan -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Penugasan Asesor AK
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Tanggal Penugasan Asesor AK</th>
                            <td>
                                : {{ $pengajuan->tanggal_penugasan_asesor_ak
                                    ? $pengajuan->tanggal_penugasan_asesor_ak->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Mulai AK</th>
                            <td>
                                : {{ $pengajuan->asesmen?->asesmenKecukupan?->tanggal_mulai
                                    ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenKecukupan->tanggal_mulai)->locale('id')->translatedFormat('d M Y')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal AK Selesai</th>
                            <td>
                                : {{ $pengajuan->asesmen?->asesmenKecukupan?->tanggal_selesai
                                    ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenKecukupan->tanggal_selesai)->locale('id')->translatedFormat('d M Y')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Penugasan Asesor AK</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('penugasan_asesor_ak', 'upps', 'label_long_for') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Informasi Asesor -->
            @if($pengajuan->asesmen && $pengajuan->asesmen->asesorAK->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge"></i> Informasi Asesor AK
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3">
                        @foreach($pengajuan->asesmen->asesorAK as $asesor)
                        <li class="mb-3">
                            <strong>{{ $asesor->user->name }}</strong>
                            @if($asesor->urutan_asesor)
                            <span class="badge bg-secondary">#{{ $asesor->urutan_asesor }}</span>
                            @endif
                            <br>

                            @if($asesor->user->email)
                            <small class="text-muted text-wrap">{{ $asesor->user->email }}</small><br>
                            @endif
                            @if($asesor->user->phone)
                            <small class="text-muted text-wrap">{{ $asesor->user->phone }}</small><br>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Timeline Penugasan -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
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
                                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                                    => 'text-success',
                                    \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                                    => 'text-success',
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
                    <p class="text-muted text-center mb-0">Belum ada riwayat penugasan</p>
                    @endif
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Asesmen Kecukupan
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Tahapan Asesmen Kecukupan:</strong>
                    </p>
                    <ol class="small mb-0 ps-3">
                        <li>Pelaporan validasi dokumen telah dikirim</li>
                        <li>LAMDEPILAR menugaskan asesor AK</li>
                        <li>Surat tugas diterbitkan dan dikirim</li>
                        <li>Asesor memulai proses asesmen kecukupan</li>
                        <li>Asesor melakukan penilaian kecukupan dari dokumen akreditasi</li>
                        <li>Hasil asesmen kecukupan divalidasi oleh tim</li>
                        <li>Pelaporan hasil asesmen kecukupan dibuat</li>
                        <li>Proses dilanjutkan ke tahap berikutnya</li>
                    </ol>

                    <hr>

                    <p class="small mb-2">
                        <strong>Catatan:</strong>
                    </p>
                    <p class="small text-muted mb-0">
                        Asesmen Kecukupan adalah penilaian awal terhadap kelengkapan dan kesesuaian dokumen akreditasi sebelum dilanjutkan ke tahap asesmen lapangan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
