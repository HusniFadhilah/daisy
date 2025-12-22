@extends('layouts.template.app')

@section('title', 'Detail Pengajuan - ' . $pengajuan->nomor_pengajuan)

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e0e0e0;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 5px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #6c757d;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #e0e0e0;
    }

    .timeline-item.active::before {
        background: #0d6efd;
        box-shadow: 0 0 0 2px #0d6efd;
    }

    .timeline-item.completed::before {
        background: #28a745;
        box-shadow: 0 0 0 2px #28a745;
    }

    .action-card {
        border-left: 4px solid #0d6efd;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>
                <i class="bi bi-file-earmark-text"></i>
                {{ $pengajuan->nomor_pengajuan }}
            </h2>
            <p class="text-muted mb-0">
                {{ $pengajuan->studyProgram->name }} -
                {{ $pengajuan->tahun_akreditasi }}
            </p>
        </div>
        <div>
            <span class="badge {{ $pengajuan->status_badge_class }} fs-6">
                {{ $pengajuan->status_label }}
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Current Action Required -->
            @if(in_array($pengajuan->status, ['borang_dikirim', 'review_kesiapan_belum_siap']))
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-exclamation-circle text-warning"></i>
                        Aksi Diperlukan: Upload Draft Borang
                    </h5>
                    <p class="mb-3">
                        Silakan lengkapi borang yang telah dikirimkan oleh DE dan upload sebagai draft untuk direview.
                    </p>

                    <form action="{{ route('pengajuan.upload-draft', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Draft Borang</label>
                                <input type="file" name="draft_borang" class="form-control" accept=".pdf,.xlsx,.xls" required>
                                <small class="text-muted">Format: PDF, XLSX | Max: 10 MB</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan terkait draft borang (opsional)"></textarea>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Upload Draft Borang
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            @if($pengajuan->status === 'menunggu_pembayaran')
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-credit-card text-success"></i>
                        Aksi Diperlukan: Upload Bukti Pembayaran
                    </h5>

                    @if($pengajuan->pembayaran)
                    <div class="alert alert-info alert-permanent">
                        <strong>Invoice:</strong> {{ $pengajuan->pembayaran->nomor_invoice }}<br>
                        <strong>Jumlah:</strong> Rp {{ number_format($pengajuan->pembayaran->jumlah_pembayaran, 0, ',', '.') }}<br>
                        <strong>Jatuh Tempo:</strong> {{ $pengajuan->pembayaran->tanggal_jatuh_tempo->format('d M Y') }}
                    </div>
                    @endif

                    <form action="{{ route('pengajuan.upload-pembayaran', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Pembayaran</label>
                                <input type="date" name="tanggal_pembayaran" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Bukti Pembayaran</label>
                                <input type="file" name="bukti_pembayaran" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted">Format: PDF, JPG, PNG | Max: 5 MB</small>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-upload"></i> Upload Bukti Pembayaran
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            @if($pengajuan->status === 'pembayaran_diterima' && $pengajuan->pembayaran && $pengajuan->pembayaran->status_pembayaran === 'verified')
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-file-check text-primary"></i>
                        Aksi Diperlukan: Upload Borang Final
                    </h5>
                    <p class="mb-3">
                        Pembayaran telah diverifikasi. Silakan upload borang final untuk dilanjutkan ke tahap AK.
                    </p>

                    <div class="alert alert-warning alert-permanent">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Pastikan borang final sudah lengkap dan tidak ada revisi
                        data kuantitatif/kualitatif yang berkaitan dengan proses akreditasi.
                    </div>

                    <form action="{{ route('pengajuan.upload-final', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Borang Final</label>
                                <input type="file" name="borang_final" class="form-control" accept=".pdf,.xlsx" required>
                                <small class="text-muted">Format: PDF, XLSX | Max: 10 MB</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Upload Borang Final
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Informasi Pengajuan -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pengajuan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Program Studi</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->studyProgram->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Jenjang</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->studyProgram->degreeLevel->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tahun Akreditasi</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->tahun_akreditasi }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Jenis Akreditasi</label>
                            <p class="fw-bold mb-0">{{ ucfirst($pengajuan->jenis_akreditasi) }}</p>
                        </div>
                        @if($pengajuan->pengaju)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Pengaju</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->pengaju->name }}</p>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">DE</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->deskEvaluator->name ?? 'Belum ditugaskan' }}</p>
                        </div>
                    </div>

                    @if($pengajuan->catatan_pengaju)
                    <hr>
                    <label class="text-muted small">Catatan Pengaju</label>
                    <p class="mb-0">{{ $pengajuan->catatan_pengaju }}</p>
                    @endif
                </div>
            </div>

            <!-- Review Kesiapan -->
            @if($pengajuan->reviewKesiapan->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check"></i> Hasil Review Kesiapan
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($pengajuan->reviewKesiapan as $review)
                    <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge {{ $review->hasil_review === 'siap' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $review->hasil_review === 'siap' ? 'SIAP' : 'BELUM SIAP' }}
                                </span>
                                <small class="text-muted ms-2">Versi {{ $review->versi_review }}</small>
                            </div>
                            <small class="text-muted">
                                {{ $review->tanggal_review->format('d M Y H:i') }}
                            </small>
                        </div>
                        <p class="mb-2"><strong>Reviewer:</strong> {{ $review->reviewer->name }}</p>
                        <p class="mb-0"><strong>Catatan:</strong></p>
                        <p class="text-muted">{{ $review->catatan_review }}</p>

                        @if($review->checklist_kesiapan)
                        <p class="mb-1"><strong>Checklist:</strong></p>
                        <ul>
                            @foreach($review->checklist_kesiapan as $item)
                            <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Dokumen -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-folder"></i> Dokumen
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($pengajuan->dokumen->groupBy('jenis_dokumen') as $jenis => $docs)
                    <div class="mb-3">
                        <h6 class="fw-bold text-primary">
                            {{ str_replace('_', ' ', ucwords($jenis)) }}
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama File</th>
                                        <th>Versi</th>
                                        <th>Upload Oleh</th>
                                        <th>Tanggal</th>
                                        <th>Ukuran</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($docs as $doc)
                                    <tr>
                                        <td>
                                            {{ $doc->original_filename }}
                                            @if($doc->is_latest)
                                            <span class="badge bg-success">Latest</span>
                                            @endif
                                        </td>
                                        <td>v{{ $doc->versi }}</td>
                                        <td>{{ $doc->uploader->name }}</td>
                                        <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $doc->file_size_formatted }}</td>
                                        <td>
                                            <a href="{{ $doc->download_url }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted mb-0">Belum ada dokumen yang diupload.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Timeline -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Timeline Proses
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item {{ $pengajuan->tanggal_pengingat ? 'completed' : '' }}">
                            <strong>Pengingat Dikirim</strong>
                            @if($pengajuan->tanggal_pengingat)
                            <small class="d-block text-muted">
                                {{ $pengajuan->tanggal_pengingat->format('d M Y H:i') }}
                            </small>
                            @endif
                        </div>

                        <div class="timeline-item {{ $pengajuan->tanggal_surat_permohonan ? 'completed' : '' }}">
                            <strong>Surat Permohonan</strong>
                            @if($pengajuan->tanggal_surat_permohonan)
                            <small class="d-block text-muted">
                                {{ $pengajuan->tanggal_surat_permohonan->format('d M Y H:i') }}
                            </small>
                            @endif
                        </div>

                        <div class="timeline-item {{ $pengajuan->tanggal_borang_dikirim ? 'completed' : '' }}">
                            <strong>Borang Dikirim</strong>
                            @if($pengajuan->tanggal_borang_dikirim)
                            <small class="d-block text-muted">
                                {{ $pengajuan->tanggal_borang_dikirim->format('d M Y H:i') }}
                            </small>
                            @endif
                        </div>

                        <div class="timeline-item {{ $pengajuan->tanggal_draft_borang ? 'completed' : '' }}">
                            <strong>Draft Borang Diterima</strong>
                            @if($pengajuan->tanggal_draft_borang)
                            <small class="d-block text-muted">
                                {{ $pengajuan->tanggal_draft_borang->format('d M Y H:i') }}
                            </small>
                            @endif
                        </div>

                        <div class="timeline-item {{ $pengajuan->tanggal_review_kesiapan ? 'completed' : '' }}">
                            <strong>Review Kesiapan</strong>
                            @if($pengajuan->tanggal_review_kesiapan)
                            <small class="d-block text-muted">
                                {{ $pengajuan->tanggal_review_kesiapan->format('d M Y H:i') }}
                            </small>
                            @endif
                        </div>

                        <div class="timeline-item {{ $pengajuan->tanggal_pembayaran ? 'completed' : '' }}">
                            <strong>Pembayaran</strong>
                            @if($pengajuan->tanggal_pembayaran)
                            <small class="d-block text-muted">
                                {{ $pengajuan->tanggal_pembayaran->format('d M Y H:i') }}
                            </small>
                            @endif
                        </div>

                        <div class="timeline-item {{ $pengajuan->tanggal_borang_final ? 'completed' : '' }}">
                            <strong>Borang Final</strong>
                            @if($pengajuan->tanggal_borang_final)
                            <small class="d-block text-muted">
                                {{ $pengajuan->tanggal_borang_final->format('d M Y H:i') }}
                            </small>
                            @endif
                        </div>

                        <div class="timeline-item {{ $pengajuan->tanggal_lanjut_ak ? 'completed' : '' }}">
                            <strong>Lanjut ke AK</strong>
                            @if($pengajuan->tanggal_lanjut_ak)
                            <small class="d-block text-muted">
                                {{ $pengajuan->tanggal_lanjut_ak->format('d M Y H:i') }}
                            </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Log -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check"></i> Log Aktivitas
                    </h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($pengajuan->statusLog->sortByDesc('changed_at') as $log)
                    <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">
                                {{ $log->changed_at->format('d/m/Y H:i') }}
                            </small>
                            <small class="text-muted">
                                {{ $log->changedBy->name }}
                            </small>
                        </div>
                        <p class="mb-0 small">
                            <span class="badge bg-secondary">{{ str_replace('_', ' ', $log->status_from) }}</span>
                            <i class="bi bi-arrow-right"></i>
                            <span class="badge bg-primary">{{ str_replace('_', ' ', $log->status_to) }}</span>
                        </p>
                        @if($log->keterangan)
                        <small class="text-muted">{{ $log->keterangan }}</small>
                        @endif
                    </div>
                    @empty
                    <p class="text-muted small mb-0">Belum ada aktivitas</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
