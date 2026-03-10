{{-- resources/views/asesmen/pelaporan/dokumen-show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pelaporan Validasi Dokumen')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('pelaporan.indexDokumen') }}">Pelaporan Validasi Dokumen</a>
            </li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-file-earmark-check"></i> Detail Pelaporan Validasi Dokumen
            </h5>
            @if($pengajuan)
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
            @else
            <small class="text-muted">{{ $assignment->asesmen->code }}</small>
            @endif
        </div>
        <a href="{{ route('pelaporan.indexDokumen') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @php
            $isReported = $pengajuan && !empty($pengajuan->tanggal_pelaporan_validasi_borang);
            $canReport = $pengajuan && $pengajuan->canBeReported('dokumen');
            @endphp

            @if($isReported)
            <div class="alert alert-success alert-permanent mb-4">
                <i class="bi bi-check-circle"></i>
                <strong>Pelaporan Validasi Dokumen telah selesai</strong>
                <br>
                Dilaporkan pada {{ $pengajuan->tanggal_pelaporan_validasi_borang->locale('id')->translatedFormat('d M Y H:i') }}
            </div>
            @elseif($canReport)
            <div class="alert alert-warning alert-permanent mb-4">
                <i class="bi bi-hourglass-split"></i>
                <strong>Menunggu Pelaporan Validasi Dokumen</strong>
                <br>
                Silakan upload dan finalisasi laporan validasi dokumen
            </div>
            @else
            <div class="alert alert-info alert-permanent mb-4">
                <i class="bi bi-arrow-repeat"></i>
                <strong>Proses Validasi Dokumen Sedang Berlangsung</strong>
                <br>
                Pelaporan dapat dilakukan setelah proses validasi selesai
            </div>
            @endif

            <!-- File Laporan Validasi Dokumen -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-pdf"></i> Laporan Kesiapan LED Program Studi (LKLED)
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $dokumen = $assignment->asesmen->documents()
                    ->where('type', 'laporan_validasi_borang')
                    ->where('is_active', true)
                    ->latest('id')
                    ->first();
                    @endphp

                    @if($dokumen)
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center rounded gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $dokumen->title }}</strong>
                                <br>
                                <small class="text-muted">{{ $dokumen->original_name }}</small>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($dokumen->size / 1024, 2) }} KB •
                                    Diupload: {{ $dokumen->uploaded_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small>
                                @if($dokumen->version > 1)
                                <br>
                                <small class="badge bg-info">Versi {{ $dokumen->version }}</small>
                                @endif
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('pelaporan.borang.download', $assignment->id) }}" class="btn btn-success btn-md">
                                <i class="bi bi-eye"></i> Lihat File
                            </a>
                        </div>
                    </div>

                    @if(!$isReported && $canReport)
                    <div class="alert alert-warning alert-permanent">
                        <i class="bi bi-info-circle"></i>
                        <strong>Perhatian:</strong> Setelah yakin dengan laporan yang diupload, jangan lupa untuk <strong>finalisasi pelaporan</strong> agar status permohonan diperbarui.
                    </div>
                    @endif
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada file laporan validasi dokumen</p>
                        @if($canReport)
                        <button type="button" class="btn btn-info mt-3 js-open-pelaporan" data-type="dokumen" data-assignment-id="{{ $assignment->id }}" data-nomor="{{ $pengajuan->nomor_pengajuan ?? $assignment->asesmen->code }}">
                            <i class="bi bi-upload"></i> Upload Laporan
                        </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informasi Asesmen -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Pelaporan Validasi Dokumen</h5>
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
            <!-- Riwayat Status -->
            @if($pengajuan)
            @php
            $filterStatuses = [
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
            ];

            $logs = $pengajuan->statusLog
            ->whereIn('status_to', $filterStatuses)
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
                                    <i class="bi bi-circle-fill text-info" style="font-size: 8px;"></i>
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
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/validasi.js') }}"></script>
@endpush
