{{-- resources/views/asesmen/pengajuan/borang-online.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Isi Borang Online - ' . $pengajuan->nomor_pengajuan)

@push('styles'){{-- CKEditor 5 CSS --}}
<link rel="stylesheet" href="{{ asset('assets/css/ckeditor5.css') }}">

<style>
    /* CKEditor custom styling */
    .ck-editor__editable {
        min-height: 300px;
        max-height: 600px;
    }

    .ck-editor__editable_inline {
        border: 2px solid #dee2e6;
        border-radius: 6px;
        padding: 15px;
    }

    .ck-editor__editable:focus {
        border-color: #932136 !important;
        box-shadow: 0 0 0 0.2rem rgba(147, 33, 54, 0.15);
    }

    /* Table in editor */
    .ck-content .table {
        margin: 1rem 0;
    }

    .ck-content .table table {
        border-collapse: collapse;
        width: 100%;
    }

    .ck-content .table td,
    .ck-content .table th {
        border: 1px solid #ddd;
        padding: 8px;
    }

    .ck-content .table th {
        background-color: #f2f2f2;
        font-weight: bold;
        text-align: left;
    }

    /* Rest of existing styles */
    .elemen-card.incomplete {
        border-left: 4px solid #ffc107;
    }

    .elemen-card.complete {
        border-left: 4px solid #28a745;
    }

    .stat-circle {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .circle-content {
        text-align: center;
        color: white;
    }

    .dataset-field {
        margin-bottom: 2rem;
    }

    .dataset-field label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.75rem;
        display: block;
    }

    .form-pengisian-card {
        background: #fff;
        border: 2px solid #932136;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .form-pengisian-header {
        background: #932136;
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 6px 6px 0 0;
        font-weight: 600;
    }

    .form-pengisian-body {
        padding: 1.5rem;
    }

    .save-indicator {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1050;
    }

    .save-indicator.show {
        display: block;
    }

    .kriteria-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem;
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .kriteria-btn {
        color: white !important;
        text-decoration: none;
        display: flex;
        align-items: center;
        width: 100%;
        text-align: left;
    }

    .kriteria-btn:hover {
        color: #f8f9fa !important;
    }

    .elemen-header {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .elemen-btn {
        color: #495057 !important;
        text-decoration: none;
        padding: 0;
    }

    .elemen-btn:hover {
        color: #007bff !important;
    }

    .chevron-icon {
        transition: transform 0.3s ease;
    }

    .elemen-btn:not(.collapsed) .chevron-icon,
    .kriteria-btn:not(.collapsed) .chevron-icon {
        transform: rotate(90deg);
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loading-overlay.show {
        display: flex;
    }

    .btn-floating {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1040;
    }

    .indikator-list .list-group-item {
        border-left: 3px solid #007bff;
        margin-bottom: 0.5rem;
    }

    .alert-permanent {
        border-radius: 0.375rem;
    }

    /* Preview mode */
    .preview-mode {
        border: 2px solid #e9ecef;
        padding: 1.5rem;
        border-radius: 6px;
        background: #f8f9fa;
        min-height: 300px;
    }

    .preview-mode table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
    }

    .preview-mode table td,
    .preview-mode table th {
        border: 1px solid #dee2e6;
        padding: 8px;
    }

    .preview-mode table th {
        background-color: #e9ecef;
        font-weight: 600;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">
                        <i class="bi bi-file-earmark-text"></i> Isi Borang Evaluasi Diri
                    </h3>
                    <p class="text-muted mb-0">{{ $pengajuan->programStudi->name }}</p>
                    <small class="text-muted">
                        Pengajuan: <strong>{{ $pengajuan->nomor_pengajuan }}</strong>
                    </small>
                </div>
                <div class="text-end">
                    <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-primary" id="btnSaveAll">
                        <i class="bi bi-cloud-upload"></i> Simpan Semua
                    </button>
                    <button type="button" class="btn btn-success" id="btnFinalSubmit">
                        <i class="bi bi-send-check"></i> Finalisasi & Submit
                    </button>
                </div>
            </div>

            <!-- Progress Section -->
            <div class="progress-wrapper mt-4">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0">Progress Pengisian</h5>
                            <span class="badge bg-primary fs-6" id="progressPercentage">0%</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-gradient bg-success" role="progressbar" id="progressBar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-white mt-1 d-block">
                            <span id="progressCompleted">0</span> dari
                            <span id="progressTotal">{{ $totalFields }}</span> field sudah diisi
                        </small>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="stat-circle">
                            <div class="circle-content">
                                <h2 class="mb-0" id="progressCount">0/{{ $totalFields }}</h2>
                                <small>Fields</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white">
            <div class="alert alert-info alert-permanent mb-0">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Petunjuk Pengisian:</strong>
                <ol class="mb-0 mt-2">
                    <li>Gunakan <strong>WYSIWYG Editor</strong> untuk menulis dengan format rich text</li>
                    <li>Anda dapat <strong>menambah tabel, list, dan format lainnya</strong> langsung di editor</li>
                    <li>Data akan <strong>otomatis tersimpan</strong> saat Anda mengisi field</li>
                    <li>Klik <strong>"Simpan Semua"</strong> untuk memastikan semua data tersimpan</li>
                    <li>Klik <strong>"Finalisasi & Submit"</strong> setelah semua field terisi</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnToggleAll" data-expanded="false">
                        <i class="bi bi-arrows-expand"></i> Expand All
                    </button>
                </div>
                <div>
                    <span class="badge bg-success me-2">
                        <i class="bi bi-check-circle"></i> <span id="completedCount">0</span> Lengkap
                    </span>
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-clock"></i> <span id="incompleteCount">{{ $totalFields }}</span> Belum Lengkap
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Accordion per Kriteria -->
    <div class="accordion" id="accordionBorang">
        @foreach($kriterias as $kriteria)
        <div class="card mb-3 kriteria-card" data-kriteria-id="{{ $kriteria->id }}">
            <!-- Kriteria Header -->
            <div class="card-header kriteria-header">
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-link kriteria-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-kriteria-{{ $kriteria->id }}">
                        <i class="bi bi-chevron-right me-2 chevron-icon"></i>
                        <strong>{{ $kriteria->kode_kriteria }}:</strong>
                        <span class="ms-2">{{ $kriteria->nama_kriteria }}</span>
                    </button>
                    <span class="badge bg-light text-dark kriteria-progress">
                        0 / {{ $kriteria->elemenStandar->count() }}
                    </span>
                </div>
            </div>

            <!-- Kriteria Body -->
            <div id="collapse-kriteria-{{ $kriteria->id }}" class="accordion-collapse collapse">
                <div class="card-body">
                    <!-- Nested Accordion per Elemen -->
                    <div class="accordion">
                        @foreach($kriteria->elemenStandar as $elemen)
                        @php
                        $totalElemenFields = $elemen->datasetBorang->count() > 0 ? $elemen->datasetBorang->count() : 1;
                        @endphp

                        <div class="card mb-3 elemen-card incomplete" data-elemen-id="{{ $elemen->id }}" data-total-fields="{{ $totalElemenFields }}">
                            <!-- Elemen Header -->
                            <div class="card-header elemen-header">
                                <button class="btn btn-link elemen-btn collapsed w-100" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-elemen-{{ $elemen->id }}">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-chevron-right chevron-icon"></i>
                                            <span class="badge bg-primary">{{ $elemen->kode_elemen }}</span>
                                            <strong>{{ $elemen->pernyataan_elemen }}</strong>
                                        </div>
                                        <span class="badge bg-warning text-dark status-badge">
                                            <i class="bi bi-clock"></i> Belum Lengkap
                                        </span>
                                    </div>
                                </button>
                            </div>

                            <!-- Elemen Body -->
                            <div id="collapse-elemen-{{ $elemen->id }}" class="accordion-collapse collapse">
                                <div class="card-body">
                                    {{-- Indikator --}}
                                    @if($elemen->indikator->count() > 0)
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3">
                                            <i class="bi bi-list-check"></i> Panduan Indikator
                                        </h6>
                                        <div class="list-group indikator-list">
                                            @foreach($elemen->indikator as $idx => $indikator)
                                            <div class="list-group-item">
                                                <div class="d-flex">
                                                    <span class="badge bg-secondary me-3">{{ $idx + 1 }}</span>
                                                    <div>
                                                        <span class="badge bg-danger me-2">{{ $indikator->kode_indikator }}</span>
                                                        <p class="mb-0 mt-2">{!! nl2br(e(str_replace("\r\n", "\n",$indikator->deskripsi_indikator))) !!}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- ✅ WYSIWYG FORM --}}
                                    <div class="form-pengisian-card">
                                        <div class="form-pengisian-header">
                                            <i class="bi bi-pencil-square"></i> Form Pengisian Data dengan WYSIWYG Editor
                                        </div>
                                        <div class="form-pengisian-body">
                                            @if($elemen->datasetBorang->count() > 0)
                                            {{-- Ada template dari dataset --}}
                                            @foreach($elemen->datasetBorang as $dataset)
                                            <div class="dataset-field" data-dataset-id="{{ $dataset->kode }}">
                                                <label>
                                                    {{ $dataset->label_field ?? $dataset->nama }}
                                                    @if($dataset->is_required)
                                                    <span class="text-danger">*</span>
                                                    @endif
                                                </label>

                                                @if($dataset->keterangan)
                                                <small class="text-muted d-block mb-2">
                                                    <i class="bi bi-info-circle"></i> {{ $dataset->keterangan }}
                                                </small>
                                                @endif

                                                {{-- WYSIWYG Editor --}}
                                                <div id="editor_{{ $dataset->id }}" class="wysiwyg-editor" data-dataset-id="{{ $dataset->kode }}" data-template="{{ $dataset->expected_columns ? json_encode($dataset->expected_columns) : '' }}">
                                                    {!! $existingData[$dataset->kode] ?? '' !!}
                                                </div>

                                                <div class="save-status text-muted small mt-2" style="display: none;">
                                                    <i class="bi bi-cloud-check"></i>
                                                    <span class="status-text">Tersimpan</span>
                                                </div>
                                            </div>
                                            @endforeach
                                            @else
                                            {{-- Tidak ada template, isi langsung --}}
                                            <div class="dataset-field" data-dataset-id="content_{{ $elemen->id }}">
                                                <label>
                                                    Uraian {{ $elemen->pernyataan_elemen }}
                                                    <span class="text-danger">*</span>
                                                </label>

                                                {{-- WYSIWYG Editor --}}
                                                <div id="editor_{{ $elemen->id }}" class="wysiwyg-editor" data-dataset-id="content_{{ $elemen->id }}">
                                                    {!! $existingData['content_' . $elemen->id] ?? '' !!}
                                                </div>

                                                <div class="save-status text-muted small mt-2" style="display: none;">
                                                    <i class="bi bi-cloud-check"></i>
                                                    <span class="status-text">Tersimpan</span>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="mt-3 pt-3 border-top">
                                                <small class="text-muted">
                                                    <i class="bi bi-info-circle"></i> Data akan otomatis tersimpan
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Floating Buttons & Indicators -->
    <button type="button" class="btn btn-primary btn-floating" id="btnScrollTop">
        <i class="bi bi-arrow-up"></i>
    </button>

    <div class="save-indicator alert alert-success">
        <i class="bi bi-check-circle me-2"></i>
        <span>Data tersimpan!</span>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay">
    <div class="spinner-border text-light" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<!-- ========================================
     MODAL: LEMBAR PENGESAHAN & SUBMIT
     ======================================== -->
<div class="modal fade" id="modalLembarPengesahan" tabindex="-1">
    <div class="modal-dialog modal-lg modal-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-check"></i> Finalisasi Borang - Lembar Pengesahan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formPengesahan" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Alert Info -->
                    <div class="alert alert-info alert-permanent">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Langkah Terakhir!</strong>
                        <p class="mb-2 mt-2">Isi data pengesahan dan upload scan lembar pengesahan yang telah ditandatangani.</p>
                        <hr>
                        <small>
                            <i class="bi bi-check-circle text-success"></i> Semua field sudah terisi lengkap<br>
                            <i class="bi bi-clock text-warning"></i> Setelah submit, borang tidak bisa diedit lagi
                        </small>
                    </div>

                    <!-- Data Ketua Program Studi -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-person-badge"></i> Data Ketua Program Studi
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">
                                        Nama Ketua Program Studi <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="nama_ketua_prodi" placeholder="Dr. Nama Lengkap, M.T." required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">
                                        NIP Ketua Program Studi <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="nip_ketua_prodi" placeholder="197001011999031001" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">
                                        Nama Wakil Ketua (Opsional)
                                    </label>
                                    <input type="text" class="form-control" name="nama_wakil_ketua" placeholder="Dr. Nama Wakil, M.T.">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">
                                        NIP Wakil Ketua (Opsional)
                                    </label>
                                    <input type="text" class="form-control" name="nip_wakil_ketua" placeholder="197501011999031002">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Pengesahan -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-calendar-check"></i> Data Pengesahan
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">
                                        Tanggal Pengesahan <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" name="tanggal_pengesahan" max="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">
                                        Tempat Pengesahan <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="tempat_pengesahan" placeholder="Kota/Kabupaten" value="{{ $pengajuan->programStudi->university->city ?? '' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Scan Pengesahan -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-file-earmark-pdf"></i> Upload Lembar Pengesahan
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning alert-permanent mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Petunjuk:</strong>
                                <ul class="mb-0 mt-2 small">
                                    <li>Upload scan lembar pengesahan yang sudah ditandatangani oleh Ketua Program Studi</li>
                                    <li>Format file: <strong>PDF</strong></li>
                                    <li>Maksimal ukuran: <strong>5MB</strong></li>
                                    <li>Pastikan file terbaca dengan jelas</li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    File Scan Pengesahan (PDF) <span class="text-danger">*</span>
                                </label>
                                <input type="file" class="form-control" id="scanPengesahan" name="scan_pengesahan" accept=".pdf" required>
                                <div class="invalid-feedback">
                                    Mohon upload file scan pengesahan
                                </div>
                            </div>

                            <!-- File Info Display -->
                            <div id="pengesahanFileInfo" class="alert alert-secondary d-none">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-file-earmark-pdf text-danger"></i>
                                        <strong>File:</strong> <span id="pengesahanFileName">-</span>
                                    </div>
                                    <div>
                                        <small class="text-muted">
                                            Ukuran: <span id="pengesahanFileSize">-</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-chat-left-text"></i> Keterangan Tambahan (Opsional)
                        </label>
                        <textarea class="form-control" name="keterangan" rows="3" placeholder="Catatan atau keterangan tambahan..."></textarea>
                    </div>

                    <!-- Konfirmasi -->
                    <div class="alert alert-danger alert-permanent">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="konfirmasiSubmit" required>
                            <label class="form-check-label" for="konfirmasiSubmit">
                                <strong>Saya menyatakan bahwa:</strong>
                                <ul class="mb-0 mt-2 small">
                                    <li>Seluruh data yang diisi adalah <strong>benar dan akurat</strong></li>
                                    <li>Dokumen pengesahan telah <strong>ditandatangani</strong> oleh pejabat yang berwenang</li>
                                    <li>Saya bertanggung jawab penuh atas <strong>kebenaran data</strong> yang disampaikan</li>
                                </ul>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success" id="btnSubmitPengesahan">
                        <i class="bi bi-send-check"></i> Submit Borang Evaluasi Diri
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================
     MODAL: SUBMIT SUCCESS
     ======================================== -->
<div class="modal fade" id="modalSubmitSuccess" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                </div>
                <h3 class="text-success mb-3">Borang Berhasil Disubmit!</h3>
                <p class="text-muted mb-4">
                    Borang evaluasi diri telah berhasil disubmit dan siap untuk proses selanjutnya.
                </p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <small>
                        <strong>Status:</strong> Menunggu verifikasi dari Desk Evaluator
                    </small>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Detail Pengajuan
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- CKEditor 5 --}}
<script src="{{ asset('assets/js/ckeditor5.umd.js') }}"></script>

<script>
    const {
        ClassicEditor
        , Essentials
        , Bold
        , Italic
        , Font
        , Paragraph
        , Table
        , TableToolbar
        , Heading
        , List
        , Link
        , Alignment
        , Underline
        , Strikethrough
        , Indent
        , IndentBlock
    } = CKEDITOR;

    document.addEventListener('DOMContentLoaded', function() {
        const pengajuanId = "{{ $pengajuan->id }}";
        let saveTimeout;
        const AUTO_SAVE_DELAY = 3000;
        let totalFields = parseInt("{{ $totalFields }}");
        let completedFields = 0;
        const existingData = @json($existingData ? $existingData : []);

        // Store editor instances
        const editorInstances = {};

        // Initialize
        initializeForm();
        initializeWYSIWYGEditors();
        updateProgress();
        initializeSubmitBorang(); // ✅ Add this

        /**
         * Initialize WYSIWYG Editors
         */
        async function initializeWYSIWYGEditors() {
            const editorElements = document.querySelectorAll('.wysiwyg-editor');

            for (const element of editorElements) {
                const datasetId = element.dataset.datasetId;
                const template = element.dataset.template;

                try {
                    const editor = await ClassicEditor.create(element, {
                        licenseKey: 'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3Njc3NDM5OTksImp0aSI6ImViNWYxM2ZjLWViMmEtNDNhZi05OGE2LTI1YjBmMTY4N2RhMyIsInVzYWdlRW5kcG9pbnQiOiJodHRwczovL3Byb3h5LWV2ZW50LmNrZWRpdG9yLmNvbSIsImRpc3RyaWJ1dGlvbkNoYW5uZWwiOlsiY2xvdWQiLCJkcnVwYWwiLCJzaCJdLCJ3aGl0ZUxhYmVsIjp0cnVlLCJsaWNlbnNlVHlwZSI6InRyaWFsIiwiZmVhdHVyZXMiOlsiKiJdLCJ2YyI6ImM2MjE0NTY3In0.rTMaN-qZUvY2yczKBRALkpIws_B92_gsnIJtG2pQvOikQvsYE8JoFXqwA_WxymhPtO7kjMQOV0s8FILQ8W1syA'
                        , plugins: [
                            Essentials, Bold, Italic, Font, Paragraph
                            , Table, TableToolbar, Heading, List, Link
                            , Alignment, Underline, Strikethrough
                            , Indent, IndentBlock
                        ]
                        , toolbar: [
                            'undo', 'redo', '|'
                            , 'heading', '|'
                            , 'bold', 'italic', 'underline', 'strikethrough', '|'
                            , 'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|'
                            , 'alignment', '|'
                            , 'bulletedList', 'numberedList', '|'
                            , 'outdent', 'indent', '|'
                            , 'insertTable', 'link'
                        ]
                        , table: {
                            contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
                        }
                        , heading: {
                            options: [{
                                    model: 'paragraph'
                                    , title: 'Paragraph'
                                    , class: 'ck-heading_paragraph'
                                }
                                , {
                                    model: 'heading1'
                                    , view: 'h1'
                                    , title: 'Heading 1'
                                    , class: 'ck-heading_heading1'
                                }
                                , {
                                    model: 'heading2'
                                    , view: 'h2'
                                    , title: 'Heading 2'
                                    , class: 'ck-heading_heading2'
                                }
                                , {
                                    model: 'heading3'
                                    , view: 'h3'
                                    , title: 'Heading 3'
                                    , class: 'ck-heading_heading3'
                                }
                            ]
                        }
                    });

                    // ✅ Insert template table if exists
                    if (template && template !== '') {
                        const columns = JSON.parse(template);
                        if (columns.length > 0 && !element.innerHTML.trim()) {
                            insertTemplateTable(editor, columns);
                        }
                    }

                    // Store instance
                    editorInstances[datasetId] = editor;

                    // Auto-save on change
                    editor.model.document.on('change:data', () => {
                        clearTimeout(saveTimeout);
                        saveTimeout = setTimeout(() => {
                            autoSaveField(datasetId, editor.getData());
                        }, AUTO_SAVE_DELAY);
                    });

                } catch (error) {
                    console.error('Error initializing editor:', error);
                }
            }
        }

        /**
         * Insert template table into editor
         */
        function insertTemplateTable(editor, columns) {
            const tableData = {
                rows: 2, // Header + 1 data row
                columns: columns.length
            };

            editor.model.change(writer => {
                const table = writer.createElement('table');

                // Create header row
                const headerRow = writer.createElement('tableRow');
                columns.forEach(colName => {
                    const cell = writer.createElement('tableCell');
                    const paragraph = writer.createElement('paragraph');
                    writer.insertText(colName, paragraph);
                    writer.append(paragraph, cell);
                    writer.append(cell, headerRow);
                });
                writer.append(headerRow, table);

                // Create one empty data row
                const dataRow = writer.createElement('tableRow');
                columns.forEach(() => {
                    const cell = writer.createElement('tableCell');
                    const paragraph = writer.createElement('paragraph');
                    writer.append(paragraph, cell);
                    writer.append(cell, dataRow);
                });
                writer.append(dataRow, table);

                // Insert table at the end
                writer.insert(table, editor.model.document.getRoot(), 'end');
            });
        }

        /**
         * Initialize form handlers
         */
        function initializeForm() {
            // Toggle button
            const btnToggle = document.getElementById('btnToggleAll');
            if (btnToggle) {
                btnToggle.addEventListener('click', function() {
                    const isExpanded = this.dataset.expanded === 'true';
                    document.querySelectorAll('.accordion-collapse').forEach(collapse => {
                        const instance = bootstrap.Collapse.getOrCreateInstance(collapse, {
                            toggle: false
                        });
                        isExpanded ? instance.hide() : instance.show();
                    });
                    this.dataset.expanded = (!isExpanded).toString();
                    this.innerHTML = isExpanded ?
                        '<i class="bi bi-arrows-expand"></i> Expand All' :
                        '<i class="bi bi-arrows-collapse"></i> Collapse All';
                });
            }

            // Save All button
            const btnSaveAll = document.getElementById('btnSaveAll')
            if (btnSaveAll) btnSaveAll.addEventListener('click', saveAllData);

            // Scroll button
            const btnScrollTop = document.getElementById('btnScrollTop');
            if (btnScrollTop) {
                window.addEventListener('scroll', () => {
                    btnScrollTop.style.display = window.pageYOffset > 300 ? 'flex' : 'none';
                });
                btnScrollTop.addEventListener('click', () => {
                    window.scrollTo({
                        top: 0
                        , behavior: 'smooth'
                    });
                });
            }
        }

        /**
         * ✅ Load data from PHP variable
         */
        function loadExistingDataFromVar() {
            if (!existingData || Object.keys(existingData).length === 0) {
                return;
            }

            Object.entries(existingData).forEach(([datasetId, value]) => {
                const fields = document.querySelectorAll(`[data-dataset-id="${datasetId}"]`);
                fields.forEach(field => {
                    if (field.tagName !== 'DIV' && field.type !== 'file') {
                        field.value = value;
                        markFieldComplete(field);
                    }
                });
            });

            updateProgress();
        }

        /**
         * Add table row dynamically
         */
        window.addTableRow = function(datasetId, columns) {
            const tbody = document.getElementById(`tableBody_${datasetId}`);
            const firstRow = tbody.querySelector('tr');
            if (!firstRow) return;

            const newRow = firstRow.cloneNode(true);

            // Clear all inputs
            newRow.querySelectorAll('input, textarea, select').forEach(input => {
                input.value = '';
            });

            tbody.appendChild(newRow);
            showSaveIndicator('Baris baru ditambahkan');
        };

        /**
         * Auto-save field
         */
        async function autoSaveField(datasetId, content) {
            if (!content || content.trim() === '<p>&nbsp;</p>' || content.trim() === '') {
                return;
            }

            const datasetField = document.querySelector(`[data-dataset-id="${datasetId}"]`);
            const saveStatus = datasetField ? datasetField.closest('.dataset-field').querySelector('.save-status') : null;

            try {
                const response = await fetch(`/pengajuan/${pengajuanId}/borang-online/save-field`, {
                    method: 'POST'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'Accept': 'application/json'
                        , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                    , body: JSON.stringify({
                        dataset_id: datasetId
                        , value: content
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showSaveStatus(saveStatus, 'success');
                    markFieldComplete(datasetField.closest('.dataset-field'));
                    updateProgress();
                    showSaveIndicator();
                }
            } catch (error) {
                console.error('Auto-save error:', error);
                showSaveStatus(saveStatus, 'error');
            }
        }

        /**
         * Mark field as complete
         */
        function markFieldComplete(field) {
            if (field && !field.classList.contains('field-completed')) {
                field.classList.add('field-completed');
            }
        }

        /**
         * Update progress
         */
        function updateProgress() {
            completedFields = document.querySelectorAll('.dataset-field.field-completed').length;
            const percentage = totalFields > 0 ? Math.round((completedFields / totalFields) * 100) : 0;

            document.getElementById('progressBar').style.width = percentage + '%';
            document.getElementById('progressPercentage').textContent = percentage + '%';
            document.getElementById('progressCompleted').textContent = completedFields;
            document.getElementById('progressCount').textContent = `${completedFields}/${totalFields}`;
            document.getElementById('completedCount').textContent = completedFields;
            document.getElementById('incompleteCount').textContent = totalFields - completedFields;

            updateKriteriaProgress();
        }

        /**
         * Update kriteria progress
         */
        function updateKriteriaProgress() {
            document.querySelectorAll('.kriteria-card').forEach(card => {
                const elemenCards = card.querySelectorAll('.elemen-card');
                const totalElemen = elemenCards.length;
                let completedElemen = 0;

                elemenCards.forEach(elemen => {
                    const completed = elemen.querySelectorAll('.dataset-field.field-completed').length;
                    const total = parseInt(elemen.dataset.totalFields) || 0;

                    if (completed === total && total > 0) {
                        completedElemen++;
                        elemen.classList.remove('incomplete');
                        elemen.classList.add('complete');
                        const badge = elemen.querySelector('.status-badge');
                        if (badge) {
                            badge.className = 'badge bg-success status-badge';
                            badge.innerHTML = '<i class="bi bi-check-circle"></i> Lengkap';
                        }
                    }
                });

                const progressBadge = card.querySelector('.kriteria-progress');
                if (progressBadge) {
                    progressBadge.textContent = `${completedElemen} / ${totalElemen}`;
                    progressBadge.className = completedElemen === totalElemen && totalElemen > 0 ?
                        'badge bg-success text-white kriteria-progress' :
                        completedElemen > 0 ?
                        'badge bg-warning text-dark kriteria-progress' :
                        'badge bg-light text-dark kriteria-progress';
                }
            });
        }

        /**
         * Save all data
         */
        async function saveAllData() {
            const confirmed = await Swal.fire({
                icon: 'question'
                , title: 'Simpan Semua Data?'
                , showCancelButton: true
                , confirmButtonText: 'Ya, Simpan'
            });

            if (!confirmed.isConfirmed) return;

            showLoading();

            const allData = {};

            // Collect all editor data
            Object.entries(editorInstances).forEach(([datasetId, editor]) => {
                const content = editor.getData();
                if (content && content.trim() !== '<p>&nbsp;</p>') {
                    allData[datasetId] = content;
                }
            });

            try {
                const response = await fetch(`/pengajuan/${pengajuanId}/borang-online/save`, {
                    method: 'POST'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'Accept': 'application/json'
                        , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                    , body: JSON.stringify({
                        data: allData
                    })
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success'
                        , title: 'Berhasil!'
                        , text: `${result.saved_count} data berhasil disimpan`
                    });

                    // Mark all as completed
                    Object.keys(editorInstances).forEach(datasetId => {
                        let dataDatasetId = document.querySelector(`[data-dataset-id="${datasetId}"]`)
                        const field = dataDatasetId ? dataDatasetId.closest('.dataset-field') : null;
                        if (field) markFieldComplete(field);
                    });

                    updateProgress();
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error'
                    , title: 'Error'
                    , text: error.message
                });
            } finally {
                hideLoading();
            }
        }

        /**
         * Save elemen data
         */
        async function saveElemenData(form) {
            const elemenId = form.dataset.elemenId;
            const formData = new FormData(form);
            const data = {};

            formData.forEach((value, key) => {
                if (key.startsWith('field_') || key.startsWith('desc_')) {
                    const dataId = key;
                    data[dataId] = value;
                }
            });

            showLoading();

            try {
                const response = await fetch(`/pengajuan/${pengajuanId}/borang-online/save`, {
                    method: 'POST'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'Accept': 'application/json'
                        , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                    , body: JSON.stringify({
                        id_elemen: elemenId
                        , data: data
                    })
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success'
                        , title: 'Berhasil!'
                        , text: 'Data elemen berhasil disimpan'
                        , timer: 2000
                        , showConfirmButton: false
                    });

                    form.querySelectorAll('.field-input').forEach(field => {
                        if (field.value.trim()) {
                            markFieldComplete(field);
                        }
                    });

                    updateProgress();
                } else {
                    throw new Error(result.message || 'Gagal menyimpan');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error'
                    , title: 'Error'
                    , text: error.message
                });
            } finally {
                hideLoading();
            }
        }

        /**
         * Helper functions
         */
        function showSaveStatus(element, type) {
            if (!element) return;
            element.style.display = 'block';
            const text = element.querySelector('.status-text');
            if (type === 'success') {
                element.className = 'save-status text-success small mt-2';
                text.textContent = 'Tersimpan ' + new Date().toLocaleTimeString('id-ID');
            } else {
                element.className = 'save-status text-danger small mt-2';
                text.textContent = 'Gagal menyimpan';
            }
        }

        /**
         * Show save indicator (floating)
         */
        function showSaveIndicator(message = 'Data tersimpan!') {
            const indicator = document.querySelector('.save-indicator');
            if (indicator) {
                indicator.querySelector('span').textContent = message;
                indicator.classList.add('show');
                setTimeout(() => {
                    indicator.classList.remove('show');
                }, 2000);
            }
        }

        /**
         * Loading overlay
         */
        function showLoading() {
            document.querySelector('.loading-overlay').classList.add('show');
        }

        function hideLoading() {
            document.querySelector('.loading-overlay').classList.remove('show');
        }

        /**
         * ============================================
         * SUBMIT BORANG WITH LEMBAR PENGESAHAN
         * ============================================
         */

        /**
         * Initialize submit borang handlers
         */
        function initializeSubmitBorang() {
            // Finalize & Submit button
            const btnFinalSubmit = document.getElementById('btnFinalSubmit');
            if (btnFinalSubmit) {
                btnFinalSubmit.addEventListener('click', showLembarPengesahanModal);
            }

            // File input handler
            const scanInput = document.getElementById('scanPengesahan');
            if (scanInput) {
                scanInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    const fileInfo = document.getElementById('pengesahanFileInfo');
                    const fileName = document.getElementById('pengesahanFileName');
                    const fileSize = document.getElementById('pengesahanFileSize');

                    if (file) {
                        // Validate PDF
                        if (file.type !== 'application/pdf') {
                            Swal.fire({
                                icon: 'error'
                                , title: 'File Tidak Valid'
                                , text: 'Hanya file PDF yang diperbolehkan!'
                            });
                            scanInput.value = '';
                            fileInfo.classList.add('d-none');
                            return;
                        }

                        // Validate size (5MB)
                        if (file.size > 5 * 1024 * 1024) {
                            Swal.fire({
                                icon: 'error'
                                , title: 'File Terlalu Besar'
                                , text: 'Maksimal ukuran file 5MB!'
                            });
                            scanInput.value = '';
                            fileInfo.classList.add('d-none');
                            return;
                        }

                        // Show file info
                        fileName.textContent = file.name;
                        fileSize.textContent = formatFileSize(file.size);
                        fileInfo.classList.remove('d-none');
                        scanInput.classList.remove('is-invalid');
                    } else {
                        fileInfo.classList.add('d-none');
                    }
                });
            }

            // Form submit handler
            const formPengesahan = document.getElementById('formPengesahan');
            if (formPengesahan) {
                formPengesahan.addEventListener('submit', submitBorangFinal);
            }
        }

        /**
         * Show lembar pengesahan modal (check completion first)
         */
        async function showLembarPengesahanModal() {
            // Check if all fields completed
            const completed = parseInt(document.getElementById('progressCompleted').textContent);
            const total = parseInt(document.getElementById('progressTotal').textContent);

            if (completed < total) {
                await Swal.fire({
                    icon: 'warning'
                    , title: 'Borang Belum Lengkap'
                    , html: `
                    <p>Anda baru mengisi <strong>${completed} dari ${total}</strong> field.</p>
                    <p class="text-danger">Lengkapi semua field terlebih dahulu!</p>
                `
                    , confirmButtonText: 'OK'
                });
                return;
            }

            // Show confirmation
            const confirmed = await Swal.fire({
                icon: 'question'
                , title: 'Siap untuk Finalisasi?'
                , html: `
                <div class="text-start">
                    <p>Anda akan melanjutkan ke tahap finalisasi borang.</p>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i>
                        <strong>${completed} field</strong> sudah terisi lengkap
                    </div>
                    <p class="mb-0">Lanjutkan ke langkah pengesahan?</p>
                </div>
            `
                , showCancelButton: true
                , confirmButtonText: '<i class="bi bi-arrow-right"></i> Lanjutkan'
                , cancelButtonText: 'Batal'
                , confirmButtonColor: '#28a745'
                , reverseButtons: true
            });

            if (!confirmed.isConfirmed) return;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('modalLembarPengesahan'));
            modal.show();
        }

        /**
         * Submit borang final with pengesahan
         */
        async function submitBorangFinal(e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);

            // Validate
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            // Final confirmation
            const finalConfirm = await Swal.fire({
                icon: 'warning'
                , title: 'Konfirmasi Terakhir'
                , html: `
                <p><strong>Anda akan men-submit borang evaluasi diri.</strong></p>
                <p class="text-danger">Setelah di-submit, borang tidak dapat diedit lagi!</p>
                <p>Pastikan semua data sudah benar.</p>
            `
                , showCancelButton: true
                , confirmButtonText: 'Ya, Submit Sekarang'
                , cancelButtonText: 'Periksa Lagi'
                , confirmButtonColor: '#28a745'
                , cancelButtonColor: '#6c757d'
                , reverseButtons: true
            });

            if (!finalConfirm.isConfirmed) return;

            // Disable button
            const btnSubmit = document.getElementById('btnSubmitPengesahan');
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

            showLoading();

            try {
                const response = await fetch(`/pengajuan/${pengajuanId}/borang-online/submit`, {
                    method: 'POST'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                    }
                    , body: formData
                });

                const data = await response.json();

                hideLoading();

                if (data.success) {
                    // Close pengesahan modal
                    const modalPengesahan = bootstrap.Modal.getInstance(document.getElementById('modalLembarPengesahan'));
                    if (modalPengesahan) modalPengesahan.hide();

                    // Show success modal
                    const modalSuccess = new bootstrap.Modal(document.getElementById('modalSubmitSuccess'));
                    modalSuccess.show();

                } else {
                    throw new Error(data.message || 'Gagal submit borang');
                }

            } catch (error) {
                hideLoading();

                // Re-enable button
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-send-check"></i> Submit Borang Evaluasi Diri';

                Swal.fire({
                    icon: 'error'
                    , title: 'Gagal Submit'
                    , text: error.message
                    , confirmButtonColor: '#d33'
                });
            }
        }

        /**
         * Format file size
         */
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    });

</script>
@endpush
