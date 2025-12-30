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

    .timeline-item.completed::before {
        background: #28a745;
        box-shadow: 0 0 0 2px #28a745;
    }

    .action-card {
        border-left: 4px solid #0d6efd;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .processing-stats {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 15px;
    }

    .processing-stats .stat-item {
        text-align: center;
        padding: 10px;
    }

    .processing-stats .stat-item h4 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .file-preview-card {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
    }

    .file-preview-card.has-file {
        border-color: #28a745;
        background: #d4edda;
    }

    .upload-area {
        border: 2px dashed #ced4da;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .upload-area:hover {
        border-color: #0d6efd;
        background: #f8f9fa;
    }

    .upload-area.dragover {
        border-color: #28a745;
        background: #d4edda;
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
                {{ $pengajuan->studyProgram->name }} - {{ $pengajuan->tahun_akreditasi }}
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
            <!-- ACTION: Upload/Isi Draft Borang -->
            @if(in_array($pengajuan->status, ['borang_dikirim', 'review_kesiapan_belum_siap']))
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-exclamation-circle text-warning"></i>
                        Aksi Diperlukan: Lengkapi Draft Borang
                    </h5>
                    <p class="mb-3">
                        Anda dapat melengkapi borang dengan 2 cara:
                    </p>

                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs mb-3" id="borangTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-panel" type="button">
                                <i class="bi bi-upload"></i> Upload DOCX
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="online-tab" data-bs-toggle="tab" data-bs-target="#online-panel" type="button">
                                <i class="bi bi-pencil-square"></i> Isi Online
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="borangTabContent">
                        <!-- Upload DOCX Panel -->
                        <div class="tab-pane fade show active" id="upload-panel">
                            <div class="alert alert-info alert-permanent">
                                <i class="bi bi-info-circle"></i>
                                <strong>Format yang diterima:</strong> DOCX (Microsoft Word)
                                <ul class="mb-0 mt-2">
                                    <li>Bagian diawali dengan kode (contoh: <code>D.1 Legalitas Program</code>)</li>
                                    <li>Tabel didahului marker <strong>"Mohon isi di sini"</strong></li>
                                    <li>Header tabel format: <code>Tabel E.1.1 - Judul Tabel</code></li>
                                </ul>
                            </div>

                            <div class="alert alert-success alert-permanent mb-3">
                                <i class="bi bi-download"></i>
                                <strong>Belum punya template?</strong> Download template borang resmi.
                                <br>
                                <a href="{{ route('pengajuan.template.download') }}" class="btn btn-sm btn-success mt-2">
                                    <i class="bi bi-download"></i> Download Template Borang DOCX
                                </a>
                            </div>

                            <form id="formUploadBorang" enctype="multipart/form-data">
                                @csrf
                                <!-- Upload Area -->
                                <div class="upload-area mb-3" id="uploadArea">
                                    <i class="bi bi-cloud-upload fs-1 text-muted"></i>
                                    <p class="mb-2"><strong>Klik atau drag & drop file di sini</strong></p>
                                    <p class="text-muted small mb-2">Format: DOCX | Max: 10 MB</p>
                                    <input type="file" id="inputDraftBorang" name="draft_borang" class="d-none" accept=".docx">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('inputDraftBorang').click()">
                                        <i class="bi bi-folder2-open"></i> Pilih File
                                    </button>
                                </div>

                                <!-- File Preview -->
                                <div id="filePreview" class="file-preview-card d-none mb-3">
                                    <i class="bi bi-file-earmark-word fs-1 text-success"></i>
                                    <p class="mb-1 mt-2"><strong id="fileName"></strong></p>
                                    <p class="text-muted small mb-2" id="fileSize"></p>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile()">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('inputDraftBorang').click()">
                                            <i class="bi bi-arrow-repeat"></i> Ganti File
                                        </button>
                                    </div>
                                </div>

                                <!-- Keterangan -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Keterangan (Opsional)</label>
                                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan terkait draft borang"></textarea>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary" id="btnSubmitUpload" disabled>
                                        <i class="bi bi-upload"></i> Upload Draft Borang
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Isi Online Panel -->
                        <div class="tab-pane fade" id="online-panel">
                            <div class="alert alert-info alert-permanent">
                                <i class="bi bi-info-circle"></i>
                                <strong>Isi borang langsung di web</strong><br>
                                Sistem akan memandu Anda mengisi setiap bagian borang secara terstruktur.
                            </div>

                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <i class="bi bi-pencil-square fs-1 text-primary mb-3"></i>
                                    <h5>Form Isian Borang Online</h5>
                                    <p class="text-muted mb-3">
                                        Isi lembar evaluasi diri secara langsung dengan form yang terstruktur
                                    </p>
                                    <a href="{{ route('pengajuan.borang-online', $pengajuan->id) }}" class="btn btn-primary">
                                        <i class="bi bi-pencil-square"></i> Mulai Mengisi Borang Online
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- SECTION: Proses & Preview Borang -->
            @if(in_array($pengajuan->status, ['draft_borang_diterima', 'borang_online_selesai']))
            @include('asesmen.pengajuan.components.modal-upload')
            @endif

            <!-- 🆕 MODAL UPLOAD ULANG -->
            <div class="modal fade" id="modalUploadUlang" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title">
                                <i class="bi bi-arrow-repeat"></i> Upload Ulang Draft Borang
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning alert-permanent">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Perhatian:</strong> Dokumen lama akan diganti dengan dokumen baru.
                                Versi akan bertambah secara otomatis.
                            </div>

                            @if($draftBorang ?? false)
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-2">Dokumen Saat Ini:</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td width="120"><i class="bi bi-file-word text-primary"></i> File:</td>
                                            <td><strong>{{ $draftBorang->original_filename ?? '-' }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td><i class="bi bi-hdd text-info"></i> Ukuran:</td>
                                            <td>{{ $draftBorang->file_size_formatted ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><i class="bi bi-clock text-warning"></i> Upload:</td>
                                            <td>{{ $draftBorang->created_at->format('d M Y H:i') ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><i class="bi bi-tag text-secondary"></i> Versi:</td>
                                            <td>v{{ $draftBorang->versi ?? '1' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            @endif

                            <form id="formUploadUlang" enctype="multipart/form-data">
                                @csrf
                                <!-- Upload Area -->
                                <div class="upload-area-modal mb-3" id="uploadAreaModal">
                                    <i class="bi bi-cloud-upload fs-1 text-muted"></i>
                                    <p class="mb-2"><strong>Klik atau drag & drop file baru di sini</strong></p>
                                    <p class="text-muted small mb-2">Format: DOCX | Max: 10 MB</p>
                                    <input type="file" id="inputDraftBorangUlang" name="draft_borang" class="d-none" accept=".docx">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('inputDraftBorangUlang').click()">
                                        <i class="bi bi-folder2-open"></i> Pilih File Baru
                                    </button>
                                </div>

                                <!-- File Preview -->
                                <div id="filePreviewModal" class="file-preview-card d-none mb-3">
                                    <i class="bi bi-file-earmark-word fs-1 text-success"></i>
                                    <p class="mb-1 mt-2"><strong id="fileNameModal"></strong></p>
                                    <p class="text-muted small mb-2" id="fileSizeModal"></p>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFileModal()">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('inputDraftBorangUlang').click()">
                                            <i class="bi bi-arrow-repeat"></i> Ganti File
                                        </button>
                                    </div>
                                </div>

                                <!-- Keterangan -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        Alasan Upload Ulang <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Revisi data mahasiswa tahun 2023, Perbaikan tabel E.1.1" required></textarea>
                                    <small class="text-muted">Jelaskan perubahan yang dilakukan</small>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x"></i> Batal
                            </button>
                            <button type="button" class="btn btn-warning" onclick="submitUploadUlang()" id="btnSubmitUlang" disabled>
                                <i class="bi bi-upload"></i> Upload Versi Baru
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTION: Upload Pembayaran -->
            @if($pengajuan->status === 'menunggu_pembayaran')
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-credit-card text-success"></i>
                        Aksi Diperlukan: Upload Bukti Pembayaran
                    </h5>

                    @if($pengajuan->pembayaran)
                    <div class="alert alert-info alert-permanent">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Invoice:</strong> {{ $pengajuan->pembayaran->nomor_invoice }}
                            </div>
                            <div class="col-md-6">
                                <strong>Jumlah:</strong> Rp {{ number_format($pengajuan->pembayaran->jumlah_pembayaran, 0, ',', '.') }}
                            </div>
                            <div class="col-md-6">
                                <strong>Jatuh Tempo:</strong> {{ $pengajuan->pembayaran->tanggal_jatuh_tempo->format('d M Y') }}
                            </div>
                        </div>
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

            <!-- ACTION: Upload Borang Final -->
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
                        <strong>Perhatian:</strong> Pastikan borang final sudah lengkap dan tidak ada revisi.
                    </div>

                    <form action="{{ route('pengajuan.upload-final', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Borang Final (DOCX)</label>
                                <input type="file" name="borang_final" class="form-control" accept=".docx" required>
                                <small class="text-muted">Format: DOCX | Max: 10 MB</small>
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
                        {{-- <div class="col-md-6 mb-3">
                            <label class="text-muted small">DE</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->deskEvaluator->name ?? 'Belum ditugaskan' }}</p>
                    </div> --}}
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
                @foreach($pengajuan->reviewKesiapan->sortByDesc('tanggal_review') as $review)
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
@push('scripts')
<script>
    // File Upload Handling
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('inputDraftBorang');
    const filePreview = document.getElementById('filePreview');
    const btnSubmit = document.getElementById('btnSubmitUpload');
    const formUpload = document.getElementById('formUploadBorang');
    const btnResetBorangShow = document.getElementById('btnResetBorangShow');

    if (btnResetBorangShow) btnResetBorangShow.addEventListener('click', function() {
        window.location.href = '{{ route("pengajuan.borang-online", $pengajuan->id) }}#reset';
    });

    if (fileInput) {
        // Click to upload
        if (uploadArea) uploadArea.addEventListener('click', (e) => {
            if (e.target !== uploadArea && e.target.closest('.btn')) return;
            fileInput.click();
        });

        // File selected
        fileInput.addEventListener('change', (e) => {
            handleFileSelect(e.target.files[0]);
        });

        // Drag & Drop
        if (uploadArea) uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        if (uploadArea) uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        if (uploadArea) uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');

            const file = e.dataTransfer.files[0];
            if (file && file.name.endsWith('.docx')) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect(file);
            } else {
                alert('Hanya file DOCX yang diperbolehkan!');
            }
        });
    }

    function handleFileSelect(file) {
        if (!file) return;

        // Validate file
        if (!file.name.endsWith('.docx')) {
            alert('Hanya file DOCX yang diperbolehkan!');
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert('Ukuran file maksimal 10 MB!');
            return;
        }

        // Show preview
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatFileSize(file.size);

        uploadArea.classList.add('d-none');
        filePreview.classList.remove('d-none');
        filePreview.classList.add('has-file');
        btnSubmit.disabled = false;
    }

    function removeFile() {
        fileInput.value = '';
        uploadArea.classList.remove('d-none');
        filePreview.classList.add('d-none');
        filePreview.classList.remove('has-file');
        btnSubmit.disabled = true;
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Form Submit
    if (formUpload) formUpload.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!fileInput.files[0]) {
            alert('Pilih file terlebih dahulu!');
            return;
        }

        const formData = new FormData(formUpload);
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';

        try {
            const response = await fetch('{{ route("pengajuan.upload-draft", $pengajuan->id) }}', {
                method: 'POST'
                , body: formData
                , headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (data.success || response.ok) {
                alert('✅ Draft borang berhasil diupload!');
                window.location.reload();
            } else {
                alert('❌ Upload gagal: ' + (data.message || 'Terjadi kesalahan'));
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-upload"></i> Upload Draft Borang';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('❌ Terjadi kesalahans: ' + error.message);
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-upload"></i> Upload Draft Borang';
        }
    });

    // Process Borang
    async function processBorang(pengajuanId) {
        const btn = document.getElementById('btnProcessBorang');
        const resultDiv = document.getElementById('processResult');

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sedang memproses...';
        }

        if (resultDiv) {
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = `
            <div class="alert alert-info alert-permanent">
                <div class="d-flex align-items-center">
                    <div class="spinner-border text-primary me-3" role="status"></div>
                    <div>
                        <strong>Memproses dokumen DOCX...</strong><br>
                        <small>Sedang melakukan pembacaan data dari borang. Proses ini memerlukan 10-30 detik.</small>
                    </div>
                </div>
            </div>
        `;
        }

        try {
            const response = await fetch(`/pengajuan/${pengajuanId}/process-borang`, {
                method: 'POST'
                , headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    , 'Accept': 'application/json'
                , }
            });

            const data = await response.json();

            if (data.success) {
                if (resultDiv) {
                    resultDiv.innerHTML = `
                    <div class="alert alert-success alert-permanent">
                        <i class="bi bi-check-circle"></i>
                        <strong>Pembacaan data berhasil!</strong><br>
                        <ul class="mb-0 mt-2">
                            <li>Total Bagian: <strong>${data.data.total_sections}</strong></li>
                            <li>Total Tabel: <strong>${data.data.total_tables}</strong></li>
                            <li>Berhasil Diproses: <strong>${data.data.parsed_tables}/${data.data.total_tables}</strong></li>
                            <li>Kelengkapan: <strong>${data.data.completion}%</strong></li>
                        </ul>
                    </div>
                `;
                }

                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                if (resultDiv) {
                    resultDiv.innerHTML = `
                    <div class="alert alert-danger alert-permanent">
                        <i class="bi bi-x-circle"></i>
                        <strong>Pembacaan data gagal:</strong><br>
                        ${data.message}
                    </div>
                `;
                }
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-gear"></i> Proses & Validasi Borang';
                }
            }
        } catch (error) {
            console.error('Error:', error);
            if (resultDiv) {
                resultDiv.innerHTML = `
                <div class="alert alert-danger alert-permanent">
                    <i class="bi bi-x-circle"></i>
                    <strong>Terjadi kesalahan:</strong><br>
                    ${error.message}
                </div>
            `;
            }
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-gear"></i> Proses & Validasi Borang';
            }
        }
    }

</script>
@endpush
@endsection
