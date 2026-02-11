{{-- resources/views/upps/penyimpanan-arsip-akreditasi/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Arsip Akreditasi')

@push('styles')
<style>
    .info-card {
        border-left: 4px solid #667eea;
        border-radius: 8px;
    }

    .checklist-item {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .checklist-complete {
        background: #d4edda;
    }

    .checklist-missing {
        background: #f8d7da;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penyimpanan-arsip-akreditasi') }}">Penyimpanan Arsip Akreditasi</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-archive"></i> Detail Penyimpanan Arsip Akreditasi
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.penyimpanan-arsip-akreditasi') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
            \App\Models\PengajuanAkreditasi::STATUS_SELESAI,
            ]; // ini contoh, bisa dinamis dari config/db/request

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN)
            <div class="alert alert-light alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Proses Penyimpanan Arsip</strong><br>
                Seluruh proses akreditasi untuk program studi ini telah disimpan dalam arsip
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_SELESAI)
            <div class="alert alert-light alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Proses Penyimpanan Arsip</strong><br>
                Seluruh proses akreditasi untuk program studi ini telah selesai dan telah disimpan dalam arsip
            </div>
            @endif

            {{-- ✅ Berita Acara Section --}}
            @if($pengajuan->status != \App\Models\PengajuanAkreditasi::STATUS_SELESAI)
            @if(!$beritaAcara)
            {{-- Form Upload --}}
            <div class="card border-start border-warning border-4 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="flex-shrink-0">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">Upload Berita Acara Penyimpanan Arsip</h5>
                            <p class="text-muted mb-0">
                                Anda harus mengupload <strong>Berita Acara Penyimpanan Arsip Akreditasi</strong>
                                sebelum dapat menyimpan arsip.
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('upps.penyimpanan-arsip-akreditasi.upload-berita-acara', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" class="mt-4">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="berita_acara" class="form-label">
                                        File Berita Acara <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" class="form-control @error('berita_acara') is-invalid @enderror" id="berita_acara" name="berita_acara" accept=".pdf" required>
                                    <small class="text-muted">Format: PDF, Maksimal 10MB</small>
                                    @error('berita_acara')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                                    <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="3" placeholder="Catatan tambahan...">{{ old('keterangan') }}</textarea>
                                    @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-outline-warning">
                                <i class="bi bi-cloud-upload"></i> Upload Berita Acara
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @else
            {{-- Display Uploaded File --}}
            <div class="card border-start border-success border-4 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-start flex-grow-1">
                            <div class="flex-shrink-0">
                                <i class="bi bi-file-earmark-check-fill text-success fs-3"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">Berita Acara Penyimpanan Arsip</h5>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-file-pdf text-danger"></i>
                                    {{ $beritaAcara->original_name }}
                                    <span class="badge bg-light text-dark ms-2">
                                        {{ number_format($beritaAcara->size / 1024, 2) }} KB
                                    </span>
                                </p>
                                <small class="text-muted">
                                    Diupload oleh: <strong>{{ $beritaAcara->uploader ? $beritaAcara->uploader->name : '-' }}</strong>
                                    pada {{ $beritaAcara->uploaded_at ? $beritaAcara->uploaded_at->format('d M Y, H:i') : '-' }}
                                </small>
                                @if($beritaAcara->keterangan)
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="bi bi-chat-left-text"></i>
                                        {{ $beritaAcara->keterangan }}
                                    </small>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('upps.penyimpanan-arsip-akreditasi.download-berita-acara', $pengajuan->id) }}" class="btn btn-outline-primary" target="_blank">
                                <i class="bi bi-download"></i> Download
                            </a>
                            @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN)
                            <form action="{{ route('upps.penyimpanan-arsip-akreditasi.delete-berita-acara', $pengajuan->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Hapus berita acara ini? Anda harus upload ulang untuk menyimpan arsip.')">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @else
            {{-- Read-only after selesai --}}
            @if($beritaAcara)
            <div class="card border-start border-info border-4 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-start flex-grow-1">
                            <div class="flex-shrink-0">
                                <i class="bi bi-file-earmark-check-fill text-info fs-3"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">Berita Acara Penyimpanan Arsip</h5>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-file-pdf text-danger"></i>
                                    {{ $beritaAcara->original_name }}
                                </p>
                                <small class="text-muted">
                                    Diupload pada {{ $beritaAcara->uploaded_at ? $beritaAcara->uploaded_at->format('d M Y, H:i') : '-' }}
                                </small>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penyimpanan-arsip-akreditasi.download-berita-acara', $pengajuan->id) }}" class="btn btn-outline-primary" target="_blank">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endif

            <!-- Document Checklist -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-list-check"></i> Kelengkapan Dokumen</h5>
                </div>
                <div class="card-body">
                    @foreach($documentChecklist as $key => $item)
                    <div class="checklist-item {{ $item['exists'] ? 'checklist-complete' : 'checklist-missing' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @if($item['exists'])
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                @else
                                <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                @endif
                                <strong>{{ $item['label'] }}</strong>
                                {{-- @if($item['critical'])
                                <span class="badge bg-danger ms-2">Wajib</span>
                                @endif --}}
                            </div>
                            @if($item['exists'] && $item['dokumen'])
                            <a href="{{ route('pengajuan.dokumen.download', $item['dokumen']->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    @php
                    $missingCritical = array_filter($documentChecklist, fn($item) => $item['critical'] && !$item['exists']);
                    @endphp
                    @if(!empty($missingCritical))
                    <div class="alert alert-danger alert-permanent mt-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Dokumen penting masih kurang!</strong> Lengkapi sebelum menyimpan arsip.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN)
            @if(empty($missingCritical))
            <div class="card mb-4 border-start border-info border-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-archive"></i> Simpan Arsip</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('upps.penyimpanan-arsip-akreditasi.simpan', $pengajuan->id) }}">
                        @csrf
                        <div class="alert alert-light alert-permanent border">
                            <i class="bi bi-info-circle"></i>
                            Arsip akan disimpan dan dapat dilanjutkan ke penyelesaian proses.
                        </div>
                        <div class="mb-3">
                            <textarea name="catatan_penyimpanan" class="form-control" rows="3" placeholder="Catatan penyimpanan (opsional)..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-md w-100 {{ !$canSaveArsip ? 'disabled' : '' }}" {{ !$canSaveArsip ? 'disabled' : '' }}>
                            <i class="bi bi-archive"></i> Simpan Arsip
                        </button>

                        @if(!$canSaveArsip)
                        <div class="mt-2 text-center">
                            <small class="text-danger">
                                <i class="bi bi-exclamation-circle"></i>
                                Upload Berita Acara untuk menyimpan arsip
                            </small>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
            @endif
            @elseif($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN)
            <div class="card mb-4 border-start border-success border-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-check-circle"></i> Selesaikan Proses</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('upps.penyimpanan-arsip-akreditasi.selesaikan', $pengajuan->id) }}">
                        @csrf
                        <div class="alert alert-light alert-permanent border border-warning">
                            <i class="bi bi-info-circle"></i>
                            <strong>Langkah Final!</strong> Setelah diselesaikan, proses akreditasi akan ditandai sebagai selesai.
                        </div>
                        <div class="mb-3">
                            <textarea name="catatan_penyelesaian" class="form-control" rows="3" placeholder="Catatan penyelesaian (opsional)..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-md w-100">
                            <i class="bi bi-check-circle-fill"></i> Selesaikan Proses Akreditasi
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Penyimpanan Arsip Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Tanggal Penyimpanan Arsip Akreditasi</th>
                            <td>
                                : {{ $pengajuan->tanggal_penyimpanan
                                    ? $pengajuan->tanggal_penyimpanan->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Pelaporan AL</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('penyimpanan_arsip', 'de', 'label_long_for','text-dark') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Timeline -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    //\App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                    \App\Models\PengajuanAkreditasi::STATUS_SELESAI,
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
                                    <i class="bi bi-circle-fill text-success" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label'] ?? $log->status_to }}
                                    </strong>
                                    <br>
                                    <small class="text-muted">{{ $log->created_at->format('d M Y H:i') }}</small>

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
