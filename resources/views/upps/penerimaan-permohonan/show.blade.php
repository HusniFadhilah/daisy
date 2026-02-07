{{-- resources/views/upps/penerimaan-permohonan/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Penerimaan Permohonan Akreditasi')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penerimaan-permohonan') }}">Penerimaan Permohonan Akreditasi</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-envelope-check"></i> Detail Penerimaan Permohonan Akreditasi
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.penerimaan-permohonan') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
            \App\Models\PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
            ]; // ini contoh, bisa dinamis dari config/db/request

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA)
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-hourglass-split"></i>
                <strong>Menunggu penerimaan permohonan akreditasi</strong>
                <br>
                Permohonan Akreditasi telah ditanggapi, mohon menunggu penerimaan permohonan akreditasi dari LAMDEPILAR
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Permohonan akreditasi telah diterima</strong>
                <br>
                Permohonan akreditasi Anda telah diterima oleh LAMDEPILAR.<br>Berikut adalah detailnya
            </div>
            @endif

            <!-- Surat Penerimaan dari LAMDEPILAR -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-pdf"></i> Surat Penerimaan dari LAMDEPILAR
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $dokumen = $pengajuan->dokumen
                    ->where('jenis_dokumen', 'surat_penerimaan_de')
                    ->where('is_latest', true)
                    ->first();
                    @endphp

                    @if($dokumen)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $dokumen->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($dokumen->file_size / 1024, 2) }} KB •
                                    Diupload: {{ $dokumen->created_at->format('d M Y H:i') }}
                                </small>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penerimaan-permohonan.download', $pengajuan->id) }}" class="btn btn-success btn-md">
                                <i class="bi bi-file-earmark-pdf"></i> Download
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Surat penerimaan belum dikirim oleh LAMDEPILAR</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informasi Permohonan Akreditasi -->
            <div class="card my-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Penerimaan Permohonan Akreditasi</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width:45%">Tanggal Penerimaan Permohonan</th>
                            <td>: {{ \App\Libraries\Date::tglIndo($pengajuan->tanggal_surat_penerimaan_dikirim) }}</td>
                        </tr>
                        <tr>
                            <th>Status Penerimaan Permohonan</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('surat_penerimaan_de','upps') !!}</td>
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
            \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
            \App\Models\PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
            ];

            $logs = $pengajuan->statusLog
            ->whereIn('status_to', $filterStatuses)
            ->sortBy('created_at');
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
