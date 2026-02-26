{{-- resources/views/upps/penerimaan-dokumen/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pengiriman Dokumen')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penerimaan-dokumen') }}">Pengiriman Dokumen</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-file-earmark-text"></i> Detail Pengiriman Dokumen
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.penerimaan-dokumen') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
            \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM,
            \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
            //\App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            //\App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
            //\App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
            //\App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            //\App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            ]; // ini contoh, bisa dinamis dari config/db/request

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI)
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-upload"></i>
                <strong>Pembayaran telah diverifikasi, silakan kirim dokumen akreditasi</strong>
                <br>
                Mohon segera lakukan pengiriman dokumen agar proses akreditasi dapat dilanjutkan.

                <!-- Flex container responsive -->
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between mt-3 gap-2">
                    <!-- Kiri: Upload -->
                    <a href="{{ route('upps.penerimaan-dokumen.upload.form', $pengajuan->id) }}" class="btn btn-info w-sm-100 w-md-auto">
                        <i class="bi bi-upload"></i> Upload File Dokumen
                    </a>

                    <!-- Tengah: ATAU -->
                    <span class="fw-bold text-center my-2 my-md-0">ATAU</span>

                    <!-- Kanan: Buka Halaman -->
                    <a href="{{ route('pengajuan.borang-online', $pengajuan->id) }}" class="btn btn-success w-sm-100 w-md-auto">
                        <i class="bi bi-pencil-square"></i> Buka Halaman Pengisian LED & LKPS
                    </a>
                </div>
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-send"></i>
                <strong>Dokumen telah diupload dan dikirim</strong>
                <br>
                Menunggu penerimaan dokumen dari LAMDEPILAR
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Dokumen telah diterima oleh LAMDEPILAR</strong>
                <br>
                Dokumen siap untuk tahap selanjutnya
                {{-- Mohon menunggu proses validasi dokumen selesai dilakukan. --}}
                {{-- Diterima pada {{ $pengajuan->tanggal_draft_borang?->locale('id')->translatedFormat('d M Y H:i') ?? '-' }} --}}
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-ui-checks"></i>
                <strong>Dokumen telah diupload</strong>
                <br>
                Dokumen siap untuk tahap selanjutnya
                {{-- Menunggu proses validasi --}}
            </div>
            @endif

            <!-- Informasi Pengiriman Dokumen -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pengiriman Dokumen
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Tanggal Dokumen Dikirim</th>
                            <td>
                                : {{ $pengajuan->tanggal_draft_borang
                                    ? $pengajuan->tanggal_draft_borang->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Pengiriman Dokumen</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('draft_borang', 'upps') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Dokumen yang Diupload -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-files"></i> Dokumen yang Telah Dikirim
                    </h5>
                </div>

                <div class="card-body">
                    @php
                    $docCards = [
                    'led' => [
                    'label' => 'Laporan Evaluasi Diri (LED)',
                    'icon' => 'file-word',
                    'color' => 'info',
                    ],
                    'suplemen' => [
                    'label' => 'Suplemen LED',
                    'icon' => 'file-earmark-pdf',
                    'color' => 'warning',
                    ],
                    'lkps' => [
                    'label' => 'Laporan Kinerja Program Studi (LKPS)',
                    'icon' => 'file-excel',
                    'color' => 'success',
                    ],
                    'pengesahan' => [
                    'label' => 'Lembar Pengesahan Dokumen',
                    'icon' => 'file-earmark-pdf',
                    'color' => 'danger',
                    ],
                    ];
                    @endphp

                    <div class="row">
                        @foreach($docCards as $key => $cfg)
                        {{-- @continue($key === 'suplemen' && !$needSuplemen) --}}

                        @php $doc = $uploadedDocuments[$key] ?? null; @endphp

                        <div class="col-md-6 mb-3">
                            <div class="card {{ $doc ? 'border-success' : 'border-danger' }}">
                                <div class="card-body d-flex gap-3">
                                    <div>
                                        <i class="bi bi-{{ $cfg['icon'] }} text-{{ $cfg['color'] }}" style="font-size:34px;"></i>
                                    </div>

                                    <div class="flex-grow-1">
                                        <div class="fw-bold">{{ $cfg['label'] }}</div>

                                        @if($doc)
                                        <small class="text-muted text-wrap mt-1">
                                            {{ $doc->original_filename ?? '-' }}<br>
                                        </small>

                                        @if($doc->path_file || $doc->template_link)
                                        <a href="{{ $doc->download_url }}" class="btn btn-sm btn-success mt-2" target="_blank">
                                            <i class="bi bi-eye"></i> Lihat File
                                        </a>
                                        @endif
                                        @else
                                        <span class="badge bg-danger mt-2">Belum Diupload</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Riwayat Status -->
            @php
            $filterStatuses = [
            \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM,
            \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
            ];

            $logs = $pengajuan->statusLog
            ->whereIn('status_to', $filterStatuses)
            ->sortBy('changed_at')
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
        </div>
    </div>
</div>
@endsection
