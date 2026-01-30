{{-- resources/views/de/penyimpanan-arsip-akreditasi/show.blade.php --}}
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

    .confetti {
        animation: confetti-fall 3s ease-in-out;
    }

    @keyframes confetti-fall {
        0% {
            transform: translateY(-100%) rotate(0deg);
            opacity: 1;
        }

        100% {
            transform: translateY(100vh) rotate(720deg);
            opacity: 0;
        }
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.penyimpanan-arsip-pelaksanaan-akreditasi') }}">Penyimpanan Arsip</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-archive"></i> Detail Arsip Akreditasi</h4>
            <p class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
        </div>
        <div>
            <a href="{{ route('de.penyimpanan-arsip-pelaksanaan-akreditasi') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('de.penyimpanan-arsip-pelaksanaan-akreditasi.download-all', $pengajuan->id) }}" class="btn btn-primary">
                <i class="bi bi-download"></i> Download Semua
            </a>
        </div>
    </div>

    @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_SELESAI)
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="bi bi-check-circle-fill"></i> Proses Akreditasi Selesai!</h5>
        <p class="mb-0">Seluruh proses akreditasi untuk program studi ini telah selesai dengan sempurna. 🎉</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8 mb-4">
            <!-- Basic Info -->
            <div class="card info-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Pengajuan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Program Studi</label>
                            <p class="fw-bold">{{ $pengajuan->studyProgram->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Peringkat Akreditasi</label>
                            <p>
                                @php
                                $badgeClass = match($pengajuan->peringkat_final) {
                                'Unggul' => 'success',
                                'Baik Sekali' => 'primary',
                                'Baik' => 'info',
                                default => 'secondary'
                                };
                                @endphp
                                <span class="badge bg-{{ $badgeClass }} fs-6">
                                    {{ $pengajuan->peringkat_final }} ({{ number_format($pengajuan->skor_final, 2) }})
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tanggal Penetapan</label>
                            <p class="fw-bold">{{ $pengajuan->tanggal_penetapan?->format('d F Y') ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Status Arsip</label>
                            <p>
                                @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_SELESAI)
                                <span class="badge bg-success">Proses Selesai</span>
                                @elseif($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN)
                                <span class="badge bg-info">Arsip Disimpan</span>
                                @else
                                <span class="badge bg-warning text-dark">Belum Diarsipkan</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

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
                                @if($item['critical'])
                                <span class="badge bg-danger ms-2">Wajib</span>
                                @endif
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
                    <div class="alert alert-danger mt-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Dokumen penting masih kurang!</strong> Lengkapi sebelum menyimpan arsip.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN)
            @if(empty($missingCritical))
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-archive"></i> Simpan Arsip</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('de.penyimpanan-arsip-pelaksanaan-akreditasi.simpan', $pengajuan->id) }}">
                        @csrf
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Arsip akan disimpan dan dapat dilanjutkan ke penyelesaian proses.
                        </div>
                        <div class="mb-3">
                            <textarea name="catatan_penyimpanan" class="form-control" rows="3" placeholder="Catatan penyimpanan (opsional)..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-archive"></i> Simpan Arsip
                        </button>
                    </form>
                </div>
            </div>
            @endif
            @elseif($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN)
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-check-circle"></i> Selesaikan Proses</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('de.penyimpanan-arsip-pelaksanaan-akreditasi.selesaikan', $pengajuan->id) }}">
                        @csrf
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i>
                            <strong>Langkah Final!</strong> Setelah diselesaikan, proses akreditasi akan ditandai sebagai selesai.
                        </div>
                        <div class="mb-3">
                            <textarea name="catatan_penyelesaian" class="form-control" rows="3" placeholder="Catatan penyelesaian (opsional)..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="bi bi-check-circle-fill"></i> Selesaikan Proses Akreditasi
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Timeline -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Timeline Lengkap</h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @forelse($pengajuan->statusLog->sortByDesc('changed_at') as $log)
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="d-block">
                            {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label_long_for']['de'] ?? $log->status_to }}
                        </strong>
                        <small class="text-muted">{{ $log->changed_at->format('d M Y H:i') }}</small>
                        @if($log->keterangan)
                        <p class="text-muted small mb-0 mt-1">{{ $log->keterangan }}</p>
                        @endif
                    </div>
                    @empty
                    <p class="text-muted mb-0">Tidak ada riwayat.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
