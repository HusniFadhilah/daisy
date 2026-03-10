@extends('layouts.template.app')

@section('title', 'Detail Masa Sanggah')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.masa-sanggah') }}">Masa Sanggah</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-hourglass-split"></i> Detail Masa Sanggah
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('de.masa-sanggah') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @php
    $now = now();
    $isAktif = $pengajuan->tanggal_masa_sanggah_mulai && $pengajuan->tanggal_masa_sanggah_selesai
    && $now->between($pengajuan->tanggal_masa_sanggah_mulai, $pengajuan->tanggal_masa_sanggah_selesai);
    $sisaHari = $isAktif ? $now->diffInDays($pengajuan->tanggal_masa_sanggah_selesai, false) : 0;
    $totalDurasi = $pengajuan->tanggal_masa_sanggah_mulai && $pengajuan->tanggal_masa_sanggah_selesai
    ? $pengajuan->tanggal_masa_sanggah_mulai->diffInDays($pengajuan->tanggal_masa_sanggah_selesai)
    : 0;
    @endphp

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @if($isAktif)
            <div class="alert alert-warning alert-permanent border-start border-2 border-warning">
                <div class="d-flex align-items-start">
                    <i class="bi bi-clock-history fs-1 me-3 text-warning"></i>
                    <div class="flex-grow-1">
                        <h5 class="mb-2 fw-bold">
                            <i class="bi bi-exclamation-triangle-fill"></i> Masa Sanggah Sedang Berlangsung
                        </h5>
                        <p class="mb-2">
                            Saat ini dalam periode masa sanggah hasil akreditasi.
                            Jika ada keberatan terhadap hasil, dapat mengajukan banding.
                        </p>
                        {{-- <div class="alert alert-light border border-warning mb-0">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Sisa Waktu:</strong>
                                    <div class="fs-4 fw-bold text-warning">{{ $sisaHari }} hari
                    </div>
                </div>
                <div class="col-md-6">
                    <strong>Berakhir pada:</strong>
                    <div class="fs-6 fw-bold">{{ $pengajuan->tanggal_masa_sanggah_selesai->locale('id')->translatedFormat('d M Y H:i') }}</div>
                </div>
            </div>
        </div> --}}
    </div>
</div>
</div>
@else
<div class="alert alert-success alert-permanent">
    <i class="bi bi-info-circle"></i>
    <strong>Masa Sanggah Telah Selesai</strong><br>
    Periode masa sanggah telah berakhir pada
    <strong>{{ $pengajuan->tanggal_masa_sanggah_selesai->locale('id')->translatedFormat('d M Y H:i') }}</strong>
</div>
@endif

<!-- Detail Masa Sanggah -->
<div class="card mb-4 border-{{ $isAktif ? 'warning' : 'success' }}">
    <div class="card-header bg-{{ $isAktif ? 'warning' : 'success' }} text-{{ $isAktif ? 'dark' : 'white' }}">
        <h5 class="mb-0">
            <i class="bi bi-calendar-range"></i> Detail Masa Sanggah
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="text-muted small">Tanggal Mulai</label>
                <p class="fw-bold mb-0">
                    {{ $pengajuan->tanggal_masa_sanggah_mulai
                                    ? $pengajuan->tanggal_masa_sanggah_mulai->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="text-muted small">Tanggal Selesai</label>
                <p class="fw-bold mb-0">
                    {{ $pengajuan->tanggal_masa_sanggah_selesai
                                    ? $pengajuan->tanggal_masa_sanggah_selesai->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                </p>
            </div>
            {{-- <div class="col-md-6 mb-3">
                            <label class="text-muted small">Durasi Masa Sanggah</label>
                            <p class="fw-bold mb-0">{{ $totalDurasi }} hari</p>
        </div> --}}
        <div class="col-md-6 mb-3">
            <label class="text-muted small">Status</label>
            <p class="mb-0">
                @if($isAktif)
                <span class="badge bg-warning text-dark fs-6">
                    <i class="bi bi-clock"></i> Sedang Berlangsung
                </span>
                @else
                <span class="badge bg-success fs-6">
                    <i class="bi bi-check-circle"></i> Masa Sanggah Selesai
                </span>
                @endif
            </p>
        </div>

        {{-- @if($isAktif)
            <div class="col-12">
                <hr>
                <div class="alert alert-light border border-warning mb-0">
                    <div class="row">
                        <div class="col-md-6 text-center mb-3 mb-md-0">
                            <label class="text-muted small d-block">Sisa Waktu</label>
                            <div class="display-4 fw-bold text-warning">{{ $sisaHari }}
    </div>
    <small class="text-muted">hari tersisa</small>
</div>
<div class="col-md-6">
    <p class="small mb-2"><strong>Progres Waktu:</strong></p>
    @php
    $elapsed = $pengajuan->tanggal_masa_sanggah_mulai->diffInDays($now);
    $progress = $totalDurasi > 0 ? ($elapsed / $totalDurasi) * 100 : 0;
    @endphp
    <div class="progress" style="height: 25px;">
        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ min($progress, 100) }}%">
            {{ number_format(min($progress, 100), 1) }}%
        </div>
    </div>
    <small class="text-muted">{{ $elapsed }} dari {{ $totalDurasi }} hari</small>
</div>
</div>
</div>
</div>
@endif --}}
</div>
</div>
</div>

<!-- Informasi Banding -->
{{-- <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-repeat"></i> Informasi Banding
                    </h5>
                </div>
                <div class="card-body">
                    @if($pengajuan->tanggal_permohonan_banding)
                    <div class="alert alert-primary border border-primary">
                        <i class="bi bi-info-circle-fill"></i>
                        <strong>Banding Telah Diajukan</strong>
                        <br>
                        <small>
                            Tanggal pengajuan: {{ $pengajuan->tanggal_permohonan_banding->locale('id')->translatedFormat('d M Y H:i') }}
</small>
</div>

<table class="table table-borderless mb-0">
    <tr>
        <th width="30%">Status Banding</th>
        <td>: {!! $pengajuan->getCustomBadgeLastStatus('banding', 'de') !!}</td>
    </tr>
    <tr>
        <th>Tanggal Pengajuan</th>
        <td>: {{ $pengajuan->tanggal_permohonan_banding->locale('id')->translatedFormat('d M Y H:i') }}</td>
    </tr>
    @if($pengajuan->tanggal_pelaksanaan_banding)
    <tr>
        <th>Tanggal Pelaksanaan</th>
        <td>: {{ $pengajuan->tanggal_pelaksanaan_banding->locale('id')->translatedFormat('d M Y H:i') }}</td>
    </tr>
    @endif
    @if($pengajuan->tanggal_pelaporan_banding)
    <tr>
        <th>Tanggal Pelaporan</th>
        <td>: {{ $pengajuan->tanggal_pelaporan_banding->locale('id')->translatedFormat('d M Y H:i') }}</td>
    </tr>
    @endif
</table>
@else
<div class="text-center py-4">
    <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
    <p class="text-muted mt-2 mb-0">
        @if($isAktif)
        Belum ada pengajuan banding. Anda masih dapat mengajukan banding selama masa sanggah berlangsung.
        @else
        Tidak ada pengajuan banding pada periode ini.
        @endif
    </p>
</div>
@endif
</div>
</div> --}}
</div>

<!-- Sidebar -->
<div class="col-lg-4">
    <!-- Countdown Card (if active) -->
    {{-- @if($isAktif)
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0">
                <i class="bi bi-alarm"></i> Countdown Masa Sanggah
            </h6>
        </div>
        <div class="card-body text-center">
            <div class="display-1 fw-bold text-warning mb-2">{{ $sisaHari }}</div>
<p class="h5 mb-3">Hari Tersisa</p>
<hr>
<p class="small mb-2">
    <strong>Berakhir pada:</strong>
</p>
<p class="mb-0">
    {{ $pengajuan->tanggal_masa_sanggah_selesai->format('l, d F Y') }}
    <br>
    <strong>{{ $pengajuan->tanggal_masa_sanggah_selesai->format('H:i') }} WIB</strong>
</p>
</div>
</div>
@endif --}}

<!-- Timeline -->
<div class="card">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <i class="bi bi-clock-history"></i> Riwayat Status
        </h5>
    </div>
    <div class="card-body" style="max-height: 600px; overflow-y: auto;">
        @php
        $filterStatuses = [
        \App\Models\PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
        \App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
        \App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
        \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
        \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
        \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
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
                        \App\Models\PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                        \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                        \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN
                        => 'text-success',
                        \App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                        \App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
                        \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN
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
        <p class="text-muted text-center mb-0">Belum ada riwayat</p>
        @endif
    </div>
</div>

<!-- Info Card -->
{{-- <div class="card mt-4 border-info">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="bi bi-info-circle"></i> Informasi Masa Sanggah
            </h6>
        </div>
        <div class="card-body">
            <p class="small mb-2">
                <strong>Apa itu Masa Sanggah?</strong>
            </p>
            <p class="small text-muted mb-3">
                Masa sanggah adalah periode waktu yang diberikan kepada program studi
                untuk mengajukan keberatan (banding) terhadap hasil akreditasi jika merasa
                ada ketidaksesuaian.
            </p>

            <p class="small mb-2">
                <strong>Proses Banding:</strong>
            </p>
            <ol class="small mb-3 ps-3 text-muted">
                <li>Program studi mengajukan banding selama masa sanggah</li>
                <li>LAMDEPILAR meninjau pengajuan banding</li>
                <li>Pelaksanaan banding dilakukan</li>
                <li>Hasil banding dilaporkan</li>
                <li>Hasil akhir ditetapkan dan diumumkan</li>
            </ol>

            <hr>

            <p class="small text-muted mb-0">
                <i class="bi bi-exclamation-circle"></i>
                @if($isAktif)
                <strong>Perhatian:</strong> Anda masih memiliki waktu <strong>{{ $sisaHari }} hari</strong>
untuk mengajukan banding jika diperlukan.
@else
Jika tidak ada banding, hasil akreditasi akan langsung ditetapkan setelah masa sanggah berakhir.
@endif
</p>
</div>
</div> --}}
</div>
</div>
</div>
@endsection
