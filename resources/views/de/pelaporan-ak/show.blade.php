@extends('layouts.template.app')

@section('title', 'Detail Pelaporan AK - ' . $pengajuan->nomor_pengajuan)

@section('content')
    <div class="container-fluid py-3">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('de.pelaporan-ak') }}">Monitor Pelaporan AK</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>

        <!-- Header -->
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
            <div>
                <h5 class="mb-1">
                    <i class="bi bi-file-earmark-bar-graph"></i> Detail Pelaporan AK
                </h5>
                <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
            </div>
            <a href="{{ route('de.pelaporan-ak') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8 mb-4">
                @php
                    $allowed = [
                        \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
                        \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
                    ];

                    $log = $pengajuan->latestRelevantStatusLog($allowed);
                @endphp

                <!-- Status Alert -->
                @if ($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI)
                    <div class="alert alert-warning alert-permanent">
                        <i class="bi bi-clock-history"></i>
                        <strong>Menunggu Pelaporan</strong><br>
                        Validasi AK telah selesai. Validator perlu melaporkan hasil asesmen kecukupan untuk melanjutkan
                        proses ke tahap berikutnya
                    </div>
                @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN)
                    <div class="alert alert-success alert-permanent">
                        <i class="bi bi-check-circle"></i>
                        <strong>Pelaporan AK Selesai</strong><br>
                        Hasil asesmen kecukupan telah dilaporkan pada
                        {{ $statusPelaporan['reported_at'] ? \Carbon\Carbon::parse($statusPelaporan['reported_at'])->locale('id')->translatedFormat('d M Y H:i') : '-' }}
                    </div>
                @endif

                <!-- Dokumen Laporan AK -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-pdf"></i> Dokumen Laporan AK
                        </h5>
                        @if ($statusPelaporan['has_laporan'])
                            <span class="badge bg-white text-primary">
                                <i class="bi bi-check-circle"></i> {{ $laporanDocuments->count() }} file
                            </span>
                        @else
                            <span class="badge bg-white text-danger">
                                <i class="bi bi-x-circle"></i> Belum ada laporan
                            </span>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        @if ($laporanDocuments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="40%">Nama File</th>
                                            <th width="15%">Ukuran</th>
                                            <th width="20%">Diupload Oleh</th>
                                            <th width="15%">Tanggal</th>
                                            <th width="5%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($laporanDocuments as $index => $doc)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                                    <strong>{{ $doc->title ?? $doc->original_name }}</strong>
                                                </td>
                                                <td>
                                                    <small
                                                        class="text-muted">{{ $doc->size ? number_format($doc->size / 1024, 2) : '-' }}
                                                        KB</small>
                                                </td>
                                                <td>
                                                    <small>{{ $doc->uploadedBy->name ?? '-' }}</small>
                                                </td>
                                                <td>
                                                    <small
                                                        class="text-muted">{{ $doc->uploaded_at?->locale('id')->translatedFormat('d M Y H:i') ?? '-' }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('storage.laporan-validasi-ak.preview', ['pengajuan' => $pengajuan->id, 'filename' => basename($doc->path)]) }}"
                                                        target="_blank" class="btn btn-sm btn-success" title="Lihat File">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-file-earmark-x" style="font-size: 4rem; color: #dee2e6;"></i>
                                <p class="text-muted mt-3 mb-1">Belum ada dokumen laporan yang diupload</p>
                                <small class="text-muted">Validator perlu mengupload laporan hasil AK</small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Informasi Pelaporan AK -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle"></i> Informasi Pelaporan AK
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th>Tanggal Validasi Selesai</th>
                                <td>
                                    :
                                    {{ $pengajuan->tanggal_ak_selesai
                                        ? $pengajuan->tanggal_ak_selesai->locale('id')->translatedFormat('d M Y H:i')
                                        : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Dilaporkan</th>
                                <td>
                                    :
                                    {{ $statusPelaporan['reported_at']
                                        ? \Carbon\Carbon::parse($statusPelaporan['reported_at'])->locale('id')->translatedFormat('d M Y H:i')
                                        : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Status Pelaporan AK</th>
                                <td>: {!! $pengajuan->getCustomBadgeLastStatus('pelaporan_ak', 'de', 'label_long_for') !!}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Riwayat Status -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> Riwayat Status
                        </h5>
                    </div>
                    <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                        @php
                            $filterStatuses = [
                                \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
                                \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
                            ];

                            $logs = $pengajuan->statusLog
                                ->whereIn('status_to', $filterStatuses)
                                ->sortBy('created_at')
                                ->unique('status_to')
                                ->values();
                        @endphp

                        @if ($logs->count() > 0)
                            <div class="timeline">
                                @foreach ($logs as $log)
                                    <div class="timeline-item mb-3">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                @php
                                                    $iconColor = match ($log->status_to) {
                                                        \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN
                                                            => 'text-success',
                                                        \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI
                                                            => 'text-warning',
                                                        default => 'text-secondary',
                                                    };
                                                @endphp
                                                <i class="bi bi-circle-fill {{ $iconColor }}"
                                                    style="font-size: 8px;"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <strong>
                                                    {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label_long_for']['de'] ?? $log->status_to }}
                                                </strong>
                                                <br>
                                                <small
                                                    class="text-muted">{{ $log->created_at->locale('id')->translatedFormat('d M Y H:i') }}</small>

                                                {{-- @if ($log->keterangan)
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
            </div>
        </div>
    </div>
@endsection
