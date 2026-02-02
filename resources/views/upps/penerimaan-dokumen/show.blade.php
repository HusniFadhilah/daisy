{{-- resources/views/upps/penerimaan-dokumen/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Penerimaan Dokumen')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penerimaan-dokumen') }}">Penerimaan Dokumen</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-file-earmark-text"></i> Detail Penerimaan Dokumen
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
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI)
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
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-send"></i>
                <strong>Dokumen telah diupload dan dikirim</strong>
                <br>
                Menunggu penerimaan dari LAMDEPILAR
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Dokumen telah diterima oleh LAMDEPILAR</strong>
                <br>
                Mohon menunggu proses validasi dokumen selesai dilakukan.
                {{-- Diterima pada {{ $pengajuan->tanggal_draft_borang?->format('d M Y H:i') ?? '-' }} --}}
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-ui-checks"></i>
                <strong>Dokumen telah diupload</strong>
                <br>
                Menunggu proses validasi
            </div>
            @elseif(in_array($pengajuan->status, [
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION
            ]))
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-clock-history"></i>
                <strong>Dokumen sedang dalam proses validasi</strong>
                <br>
                Validator sedang memeriksa kelengkapan dan kesesuaian dokumen
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED)
            <div class="alert alert-danger alert-permanent">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Dokumen memerlukan revisi</strong>
                <br>
                Validator meminta perbaikan pada dokumen yang telah diupload.
                <div class="mt-2">
                    <a href="{{ route('upps.penerimaan-dokumen.upload.form', $pengajuan->id) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-upload"></i> Upload Dokumen Revisi
                    </a>
                </div>
            </div>
            @elseif(in_array($pengajuan->status, [
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA
            ]))
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-patch-check"></i>
                <strong>Dokumen telah divalidasi</strong>
                <br>
                Dokumen memenuhi persyaratan dan siap untuk tahap selanjutnya
            </div>
            @endif

            <!-- Informasi Penerimaan Dokumen -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Penerimaan Dokumen
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
                            <th>Tanggal Dokumen Diupload</th>
                            <td>
                                : {{ $pengajuan->tanggal_draft_borang
                                    ? $pengajuan->tanggal_draft_borang->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Pengiriman Dokumen</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('borang_final', 'upps') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Dokumen yang Diupload -->

        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Riwayat Status -->
            @php
            $filterStatuses = [
            \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM,
            \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            ];

            $logs = $pengajuan->statusLog
            ->whereIn('status_to', $filterStatuses)
            ->sortBy('changed_at');
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
