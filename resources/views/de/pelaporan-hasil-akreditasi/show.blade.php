{{-- resources/views/de/pelaporan-hasil-akreditasi/show.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Detail Pelaporan Hasil Akreditasi')

@push('styles')
<style>
    .info-card {
        border-left: 4px solid #667eea;
        border-radius: 8px;
    }

    .result-card {
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .doc-card {
        transition: all 0.3s ease;
    }

    .doc-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.pelaporan-hasil-akreditasi') }}">Pelaporan Hasil</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-megaphone"></i> Detail Pelaporan Hasil Akreditasi</h4>
            <p class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
        </div>
        <div>
            <a href="{{ route('de.pelaporan-hasil-akreditasi') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

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
                            <label class="text-muted small">Universitas</label>
                            <p class="fw-bold">{{ $pengajuan->studyProgram->university->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tanggal Penetapan</label>
                            <p class="fw-bold">{{ $pengajuan->tanggal_penetapan?->format('d F Y') ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Status Pelaporan</label>
                            <p>
                                @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN)
                                <span class="badge bg-success">Sudah Dilaporkan</span>
                                @else
                                <span class="badge bg-warning text-dark">Belum Dilaporkan</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hasil Akreditasi -->
            @php
            $peringkat = $pengajuan->peringkat_final;
            $skor = $pengajuan->skor_final;
            $badgeClass = match($peringkat) {
            'Unggul' => 'success',
            'Baik Sekali' => 'primary',
            'Baik' => 'info',
            default => 'secondary'
            };
            @endphp

            <div class="card result-card mb-4">
                <div class="card-header bg-{{ $badgeClass }} text-white">
                    <h5 class="mb-0"><i class="bi bi-award"></i> Hasil Akreditasi</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded">
                                <h6 class="text-muted small mb-2">Peringkat</h6>
                                <h2 class="mb-0">
                                    <span class="badge bg-{{ $badgeClass }}">{{ $peringkat }}</span>
                                </h2>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded">
                                <h6 class="text-muted small mb-2">Skor Final</h6>
                                <h2 class="mb-0 fw-bold">{{ number_format($skor, 2) }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Forms (if not yet dilaporkan) -->
            @if($pengajuan->status != \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN)
            <div class="row mb-4">
                <!-- Upload Laporan Hasil -->
                <div class="col-md-6 mb-3">
                    <div class="card doc-card h-100">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-file-text"></i> Upload Laporan Hasil</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('de.pelaporan-hasil-akreditasi.upload-laporan', $pengajuan->id) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <input type="file" name="file_laporan" class="form-control form-control-sm" accept=".pdf,.doc,.docx" required>
                                    <small class="text-muted">PDF, DOC, DOCX (Max: 10MB)</small>
                                </div>
                                <div class="mb-3">
                                    <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Keterangan..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-upload"></i> Upload
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Upload Sertifikat -->
                <div class="col-md-6 mb-3">
                    <div class="card doc-card h-100">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="bi bi-patch-check"></i> Upload Sertifikat</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('de.pelaporan-hasil-akreditasi.upload-sertifikat', $pengajuan->id) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <input type="file" name="file_sertifikat" class="form-control form-control-sm" accept=".pdf" required>
                                    <small class="text-muted">PDF (Max: 5MB)</small>
                                </div>
                                <div class="mb-3">
                                    <input type="number" name="masa_berlaku_tahun" class="form-control form-control-sm" placeholder="Masa berlaku (tahun)" min="1" max="10">
                                </div>
                                <button type="submit" class="btn btn-success btn-sm w-100">
                                    <i class="bi bi-upload"></i> Upload
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selesaikan Pelaporan Button -->
            @php
            $hasLaporan = $pengajuan->dokumen->where('jenis_dokumen', 'laporan_hasil')->isNotEmpty();
            $hasSertifikat = $pengajuan->dokumen->where('jenis_dokumen', 'sertifikat')->isNotEmpty();
            @endphp
            @if($hasLaporan || $hasSertifikat)
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-check-circle"></i> Selesaikan Pelaporan</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('de.pelaporan-hasil-akreditasi.selesaikan', $pengajuan->id) }}">
                        @csrf
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Setelah diselesaikan, status akan berubah menjadi "Hasil Dilaporkan" dan siap untuk arsip.
                        </div>
                        <div class="mb-3">
                            <textarea name="catatan_pelaporan" class="form-control" rows="3" placeholder="Catatan pelaporan (opsional)..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning btn-lg w-100">
                            <i class="bi bi-check-circle"></i> Selesaikan Pelaporan
                        </button>
                    </form>
                </div>
            </div>
            @endif
            @endif

            <!-- Dokumen yang sudah diupload -->
            @php
            $laporanHasil = $pengajuan->dokumen->where('jenis_dokumen', 'laporan_hasil')->first();
            $sertifikat = $pengajuan->dokumen->where('jenis_dokumen', 'sertifikat')->first();
            @endphp
            @if($laporanHasil || $sertifikat)
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-files"></i> Dokumen Terupload</h5>
                </div>
                <div class="card-body">
                    @if($laporanHasil)
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded mb-2">
                        <div>
                            <i class="bi bi-file-text text-primary me-2"></i>
                            <strong>Laporan Hasil</strong>
                            <br>
                            <small class="text-muted">{{ $laporanHasil->original_filename }}</small>
                        </div>
                        <a href="{{ route('de.pelaporan-hasil-akreditasi.download', [$pengajuan->id, 'laporan_hasil']) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                    @endif

                    @if($sertifikat)
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                        <div>
                            <i class="bi bi-patch-check text-success me-2"></i>
                            <strong>Sertifikat Akreditasi</strong>
                            <br>
                            <small class="text-muted">{{ $sertifikat->original_filename }}</small>
                        </div>
                        <a href="{{ route('de.pelaporan-hasil-akreditasi.download', [$pengajuan->id, 'sertifikat']) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Timeline -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Timeline</h5>
                </div>
                <div class="card-body">
                    @forelse($pengajuan->statusLog->sortBy('changed_at')->take(10) as $log)
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
