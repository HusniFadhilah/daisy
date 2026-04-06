{{-- resources/views/upps/penyampaian-template/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Formulir dan Templat Dokumen')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penyampaian-template') }}">Formulir dan Templat Dokumen</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-file-earmark"></i> Detail Formulir dan Templat Dokumen
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.penyampaian-template') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
            \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
            ]; // ini contoh, bisa dinamis dari config/db/request

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM)
            <div class="alert alert-warning alert-permanent mb-4">
                <i class="bi bi-hourglass-split"></i>
                <strong>Menunggu formulir dan templat dokumen</strong>
                <br>
                Templat belum dikirim oleh LAMDEPILAR
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM)
            <div class="alert alert-success alert-permanent mb-4">
                <i class="bi bi-check-circle"></i>
                <strong>Formulir dan templat dokumen telah diterima</strong>
                <br>
                Diterima pada {{ $pengajuan->tanggal_template_led_dikirim->locale('id')->translatedFormat('d M Y H:i') }}
            </div>
            @endif

            <!-- Pending Requests Alert -->
            @if($pendingRequests->count() > 0)
            <div class="alert alert-info alert-permanent mb-4">
                <h6><i class="bi bi-info-circle"></i> Permintaan Upload Ulang Pending</h6>
                <p class="mb-2">Anda memiliki {{ $pendingRequests->count() }} permintaan upload ulang yang sedang diproses:</p>
                <ul class="mb-0">
                    @foreach($pendingRequests as $notif)
                    @php
                    $data = json_decode($notif->data, true);
                    @endphp
                    <li>
                        <strong>{{ $data['jenis_dokumen_label'] ?? 'Dokumen' }}</strong>
                        <br>
                        <small class="text-muted">
                            Dikirim: {{ \Carbon\Carbon::parse($data['requested_at'])->locale('id')->translatedFormat('d M Y H:i') }}
                        </small>
                        <br>
                        <small>Alasan: {{ $data['alasan_request'] ?? '-' }}</small>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Formulir Pembayaran -->
            @php
            $formulirPembayaran = $pengajuan->dokumen
            ->where('jenis_dokumen', 'template_formulir_pembayaran')
            ->where('is_latest', true)
            ->first();
            @endphp

            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-text"></i> Formulir Pembayaran
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    @if($formulirPembayaran)
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center rounded gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $formulirPembayaran->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($formulirPembayaran->file_size / 1024, 2) }} KB •
                                    Diupload: {{ $formulirPembayaran->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small>
                                <br>
                                <span class="badge bg-info">Versi {{ $formulirPembayaran->versi }}</span>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penyampaian-template.download', [$pengajuan->id, 'template_formulir_pembayaran']) }}" class="btn btn-success btn-md">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Formulir pembayaran belum dikirim oleh LAMDEPILAR</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Templat Dokumen -->
            @php
            $templateLed = $pengajuan->dokumen
            ->where('jenis_dokumen', 'borang_template')
            ->where('is_latest', true)
            ->first();
            @endphp

            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-text"></i> Templat Dokumen Akreditasi
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    @if($templateLed)
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center rounded gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $templateLed->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($templateLed->file_size / 1024, 2) }} KB •
                                    Diupload: {{ $templateLed->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small>
                                <br>
                                <span class="badge bg-info">Versi {{ $templateLed->versi }}</span>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penyampaian-template.download', [$pengajuan->id, 'borang_template']) }}" class="btn btn-success btn-md">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Templat dokumen belum dikirim oleh LAMDEPILAR</p>
                    </div>
                    @endif
                </div>
            </div>

            @php
            $hasAnyTemplate = ($formulirPembayaran || $templateLed);
            @endphp

            @if($hasAnyTemplate)
            <div class="alert alert-info alert-permanent d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h6 class="mb-1">
                        <i class="bi bi-info-circle"></i> Informasi Pengiriman Ulang Dokumen
                    </h6>
                    <div class="small">
                        Apabila Program Studi Anda membutuhkan <strong>pengiriman ulang</strong> formulir pembayaran atau templat dokumen, silakan klik tombol <strong>Pengiriman Ulang</strong> untuk mengajukan permintaan pengiriman ulang formulir dan templat dokumen ke LAMDEPILAR.
                    </div>
                </div>
                <div class="text-nowrap">
                    <a href="{{ route('upps.penyampaian-template.request.form', [$pengajuan->id]) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-arrow-repeat"></i> Pengiriman Ulang
                    </a>
                </div>
            </div>
            @endif

            <!-- Informasi Penyampaian -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Formulir dan Templat Dokumen
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Tanggal Templat Dikirim</th>
                            <td>
                                : {{ $pengajuan->tanggal_template_led_dikirim
                                    ? $pengajuan->tanggal_template_led_dikirim->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Formulir dan Templat Dokumen</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('borang_template','upps') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Riwayat Status -->
            @php
            $filterStatuses = [
            \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
            ];

            $logs = $pengajuan->statusLog
            ->whereIn('status_to', $filterStatuses)
            ->unique('status_to')
            ->sortBy('changed_at');
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
