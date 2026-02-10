@extends('layouts.template.app')

@section('title', 'Detail Pelaporan AL - ' . $pengajuan->nomor_pengajuan)

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.pelaporan-al') }}">Monitor Pelaporan AL</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-file-earmark-text"></i> Detail Pelaporan AL
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('de.pelaporan-al') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
            ];

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp

            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI)
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-clock-history"></i>
                <strong>Menunggu Pelaporan</strong><br>
                Asesmen lapangan telah selesai. Validator perlu melaporkan hasil visitasi untuk melanjutkan proses ke tahap berikutnya
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Pelaporan AL Selesai</strong><br>
                Hasil asesmen lapangan telah dilaporkan pada {{ $statusPelaporan['reported_at'] ? \Carbon\Carbon::parse($statusPelaporan['reported_at'])->format('d M Y H:i') : '-' }}
            </div>
            @endif

            <!-- Dokumen Laporan AL -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-pdf"></i> Dokumen Laporan AL
                    </h5>
                    @if($statusPelaporan['has_laporan'])
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
                    @if($laporanDocuments->count() > 0)
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
                                @foreach($laporanDocuments as $index => $doc)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                        <strong>{{ $doc->title ?? $doc->original_name }}</strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $doc->size ? number_format($doc->size / 1024, 2) : '-' }} KB</small>
                                    </td>
                                    <td>
                                        <small>{{ $doc->uploadedBy->name ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $doc->uploaded_at?->format('d M Y H:i') ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ asset('storage/' . $doc->path) }}" target="_blank" class="btn btn-sm btn-success" title="Lihat File">
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
                        <small class="text-muted">Validator perlu mengupload laporan hasil AL</small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informasi Pelaporan AL -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pelaporan AL
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        @if($pengajuan->asesmen?->asesmenLapangan)
                        <tr>
                            <th>Tanggal AL Selesai</th>
                            <td>
                                : {{ $pengajuan->asesmen->asesmenLapangan->tanggal_selesai
                                    ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenLapangan->tanggal_selesai)->format('d M Y')
                                    : '-' }}
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th>Tanggal Dilaporkan</th>
                            <td>
                                : {{ $statusPelaporan['reported_at']
                                    ? \Carbon\Carbon::parse($statusPelaporan['reported_at'])->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Pelaporan AL</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('pelaporan_al', 'de', 'label_long_for') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Validator Info -->
            <div class="card my-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-check"></i> Validator yang Ditugaskan
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $validators = $pengajuan->asesmen?->asesmenUserRoles->filter(function($aur) {
                    return $aur->role_selected->name === 'validator';
                    }) ?? collect();
                    @endphp

                    @if($validators->count() > 0)
                    <div class="row">
                        @foreach($validators as $validator)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="flex-shrink-0">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="bi bi-person-check fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>{{ $validator->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $validator->user->email }}</small>
                                    <br>
                                    <span class="badge bg-success mt-1">{{ $validator->role_selected->alias }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-person-x" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada validator yang ditugaskan</p>
                    </div>
                    @endif
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
                    \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
                    \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
                    ];

                    $logs = $pengajuan->statusLog
                    ->whereIn('status_to', $filterStatuses)
                    ->sortBy('changed_at');
                    @endphp

                    @if($logs->count() > 0)
                    <div class="timeline">
                        @foreach($logs as $log)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    @php
                                    $iconColor = match($log->status_to) {
                                    \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN
                                    => 'text-success',
                                    \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI
                                    => 'text-warning',
                                    default => 'text-secondary',
                                    };
                                    @endphp
                                    <i class="bi bi-circle-fill {{ $iconColor }}" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label_long_for']['de'] ?? $log->status_to }}
                                    </strong>
                                    <br>
                                    <small class="text-muted">{{ $log->changed_at->format('d M Y H:i') }}</small>
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

            <!-- Status Pelaporan -->
            {{-- <div class="card mb-4">
                <div class="card-header bg-{{ $statusPelaporan['is_reported'] ? 'success' : 'warning' }} text-white">
            <h5 class="mb-0">
                <i class="bi bi-{{ $statusPelaporan['is_reported'] ? 'check-circle' : 'clock' }}"></i> Status Pelaporan
            </h5>
        </div>
        <div class="card-body">
            @if($statusPelaporan['is_reported'])
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-check-circle-fill text-success me-2" style="font-size: 1.5rem;"></i>
                <div>
                    <strong>Laporan Telah Dikirim</strong>
                    <br>
                    <small class="text-muted">
                        Dilaporkan: {{ \Carbon\Carbon::parse($statusPelaporan['reported_at'])->format('d M Y H:i') }}
                    </small>
                </div>
            </div>
            @if($statusPelaporan['has_laporan'])
            <div class="alert alert-success alert-permanent mb-0 mt-3">
                <small>
                    <i class="bi bi-file-earmark-check"></i>
                    {{ $laporanDocuments->count() }} dokumen laporan tersedia
                </small>
            </div>
            @endif
            @else
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-clock text-warning me-2" style="font-size: 1.5rem;"></i>
                <div>
                    <strong>Menunggu Pelaporan</strong>
                    <br>
                    <small class="text-muted">Validator belum mengirim laporan</small>
                </div>
            </div>
            @endif
        </div>
    </div> --}}

</div>
</div>
</div>
@endsection
