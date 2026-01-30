{{-- resources/views/upps/penyampaian-template/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pengiriman Formulir dan Template Dokumen')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penyampaian-template') }}">Pengiriman Formulir dan Template Dokumen</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-file-earmark-arrow-down"></i> Detail Pengiriman Formulir dan Template Dokumen
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
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM)
            <div class="alert alert-warning alert-permanent mb-4">
                <i class="bi bi-hourglass-split"></i>
                <strong>Menunggu pengiriman formulir dan template dokumen dari LAMDEPILAR</strong>
                <br>
                Template belum dikirim oleh LAMDEPILAR
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM)
            <div class="alert alert-success alert-permanent mb-4">
                <i class="bi bi-check-circle"></i>
                <strong>Formulir dan template dokumen telah diterima dari LAMDEPILAR</strong>
                <br>
                Diterima pada {{ $pengajuan->tanggal_template_led_dikirim->format('d M Y H:i') }}
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
                            Dikirim: {{ \Carbon\Carbon::parse($data['requested_at'])->format('d M Y H:i') }}
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
                        @if($formulirPembayaran)
                        <button type="button" class="btn btn-warning btn-sm" onclick="window.location.href='{{ route('upps.penyampaian-template.request.form', [$pengajuan->id, 'formulir_pembayaran']) }}'">
                            <i class="bi bi-arrow-repeat"></i> Minta Upload Ulang
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($formulirPembayaran)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $formulirPembayaran->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($formulirPembayaran->file_size / 1024, 2) }} KB •
                                    Diupload: {{ $formulirPembayaran->created_at->format('d M Y H:i') }}
                                </small>
                                <br>
                                <span class="badge bg-info">Versi {{ $formulirPembayaran->versi }}</span>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penyampaian-template.download', [$pengajuan->id, 'formulir_pembayaran']) }}" class="btn btn-success btn-md">
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

            <!-- Template Dokumen -->
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
                            <i class="bi bi-file-earmark-text"></i> Template Dokumen Akreditasi
                        </h5>
                        @if($templateLed)
                        <button type="button" class="btn btn-warning btn-sm" onclick="window.location.href='{{ route('upps.penyampaian-template.request.form', [$pengajuan->id, 'borang_template']) }}'">
                            <i class="bi bi-arrow-repeat"></i> Minta Upload Ulang
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($templateLed)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $templateLed->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($templateLed->file_size / 1024, 2) }} KB •
                                    Diupload: {{ $templateLed->created_at->format('d M Y H:i') }}
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
                        <p class="text-muted mt-2 mb-0">Template dokumen belum dikirim oleh LAMDEPILAR</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informasi Penyampaian -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pengiriman Formulir dan Template Dokumen
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Nomor Permohonan</th>
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
                            <th>Jenis Permohonan</th>
                            <td>: {{ $pengajuan->jenis_akreditasi_label }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Template Dikirim</th>
                            <td>
                                : {{ $pengajuan->tanggal_template_led_dikirim
                                    ? $pengajuan->tanggal_template_led_dikirim->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Pengiriman Formulir dan Template Dokumen</th>
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
            \App\Models\PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
            \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
            ];

            $logs = $pengajuan->statusLog
            ->whereIn('status_to', $filterStatuses)
            ->sortByDesc('changed_at');
            @endphp

            <div class="card">
                <div class="card-header bg-info text-white">
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
                                    <i class="bi bi-circle-fill text-info" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label'] ?? $log->status_to }}
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
