{{-- resources/views/asesmen/pengajuan/borang-online.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Isi Laporan Evaluasi Diri - ' . $pengajuan->studyProgram->name)

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tinymce@8.3.1/skins/ui/oxide/content.min.css" rel="stylesheet">

<style>
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

    .elemen-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    .elemen-card.has-data {
        border-left-color: #28a745;
    }

    .elemen-card:not(.has-data) {
        border-left-color: #ffc107;
    }

    .save-status {
        font-size: 0.875rem;
        font-style: italic;
    }

    .char-count {
        font-weight: 600;
        color: #932136;
    }

    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .loading-overlay.show {
        display: flex;
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

    .dataset-field-wrapper {
        margin-bottom: 2rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .editor-actions {
        margin-top: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .progress-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .progress-stats {
        display: flex;
        justify-content: space-around;
        margin-top: 1rem;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        display: block;
    }

    .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    /* ✅ File Upload Card */
    .upload-card {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s;
        background: #fff;
    }

    .upload-card.clickable {
        cursor: pointer;
    }

    .upload-card.clickable:hover {
        border-color: #932136;
        background: #f8f9fa;
    }

    .upload-card.has-file {
        border-color: #28a745;
        border-style: solid;
        background: #f0fff4;
    }

    .upload-icon {
        font-size: 3rem;
        color: #6c757d;
        margin-bottom: 1rem;
    }

    .upload-card.has-file .upload-icon {
        color: #28a745;
    }

    /* ✅ Front Matter & Suplemen Accordion */
    .front-matter-card {
        border-left: 4px solid #17a2b8;
    }

    .suplemen-card {
        border-left: 4px solid #6f42c1;
    }

    .front-matter-card .card-header,
    .suplemen-card .card-header {
        background: #f8f9fa;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    {{-- Header Card --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Laporan Evaluasi Diri</h3>
                    <p class="text-muted mb-0">
                        Prodi {{ $pengajuan->studyProgram->name }} - Nomor: {{ $pengajuan->nomor_pengajuan }}
                    </p>
                </div>
                <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            {{-- ✅ Progress Section - ELEMEN ONLY --}}
            <div class="progress-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-3">Progress Pengisian Elemen</h5>

                        @php
                        $elemenPercentage = $progressData['elemen_percentage'];
                        $barColor = $elemenPercentage === 100 ? 'bg-success' :
                        ($elemenPercentage >= 75 ? 'bg-info' :
                        ($elemenPercentage >= 50 ? 'bg-warning' : 'bg-danger'));
                        @endphp

                        {{-- Main Progress Bar (Elemen-based only) --}}
                        <div class="mb-3">
                            <div class="progress" style="height: 30px; background-color: rgba(255,255,255,0.3);">
                                <div class="progress-bar {{ $barColor }}" role="progressbar" id="progressBarElemen" style="width: {{ $elemenPercentage }}%" data-initial-value="{{ $elemenPercentage }}">
                                    <span id="progressPercentageElemen" style="font-weight: bold; font-size: 1rem;">{{ $elemenPercentage }}%</span>
                                </div>
                            </div>
                        </div>

                        <div class="progress-stats">
                            <div class="stat-item">
                                <span class="stat-number" id="progressCompletedElemen">{{ $progressData['completed_elemen'] }}</span>
                                <span class="stat-label">Elemen Lengkap</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="progressRemainingElemen">{{ $progressData['total_elemen'] - $progressData['completed_elemen'] }}</span>
                                <span class="stat-label">Elemen Tersisa</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="progressTotalElemen">{{ $progressData['total_elemen'] }}</span>
                                <span class="stat-label">Total Elemen</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 text-center">
                        <div class="stat-circle">
                            <div class="circle-content">
                                <h2 class="mb-0" id="progressCountElemen">{{ $progressData['completed_elemen'] }}/{{ $progressData['total_elemen'] }}</h2>
                                <small>Elemen Lengkap</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-success" id="btnFinalize">
                        <i class="bi bi-check-circle"></i> Finalisasi & Submit LED+Suplemen dan LKPS
                    </button>
                    <small class="d-block text-muted mt-1">
                        <i class="bi bi-info-circle"></i> Pastikan semua elemen sudah diisi sebelum finalisasi
                    </small>
                </div>
                <div class="btn-group">
                    @if(in_array($pengajuan->status, ['borang_dikirim', 'draft_borang_diterima', 'borang_online_selesai', 'review_kesiapan_belum_siap']))
                    <button type="button" class="btn btn-outline-danger" id="btnResetBorang">
                        <i class="bi bi-arrow-clockwise"></i> Reset Borang
                    </button>
                    @endif

                    <button type="button" class="btn btn-outline-primary" id="btnToggleAll">
                        <i class="bi bi-arrows-expand"></i> Expand All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4" id="validationCard">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-clipboard-check"></i> Hasil Validasi Borang
            </h5>
            <span class="badge bg-secondary" id="validationBadge">Memuat...</span>
        </div>
        <div class="card-body">
            <div id="validationLoading" class="text-muted">
                <span class="spinner-border spinner-border-sm me-2"></span> Mengambil data validasi...
            </div>

            <div id="validationContent" class="d-none">
                <div class="mb-2">
                    <small class="text-muted">Validator</small>
                    <div class="fw-semibold" id="validatorName">-</div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="p-2 bg-light rounded">
                            <div class="small text-muted">LED</div>
                            <div class="fw-bold" id="valLedCount">-</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-light rounded">
                            <div class="small text-muted">Suplemen</div>
                            <div class="fw-bold" id="valSuplemenCount">-</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-light rounded">
                            <div class="small text-muted">LKPS</div>
                            <div class="fw-bold" id="valLkpsCount">-</div>
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <small class="text-muted">Total Progress</small>
                    <div class="progress">
                        <div class="progress-bar" id="valTotalBar" style="width:0%"></div>
                    </div>
                    <div class="small text-muted mt-1">
                        <span id="valTotalText">0%</span> • terakhir update <span id="valUpdatedAt">-</span>
                    </div>
                </div>

                <hr>

                <div class="mb-2">
                    <small class="text-muted">Catatan Validator (Keseluruhan)</small>
                    <div class="border rounded p-2 bg-white" id="valNoteAll">-</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Catatan LED</small>
                    <div class="border rounded p-2 bg-white" id="valNoteLed">-</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Catatan Suplemen</small>
                    <div class="border rounded p-2 bg-white" id="valNoteSuplemen">-</div>
                </div>
                <div class="mb-0">
                    <small class="text-muted">Catatan LKPS</small>
                    <div class="border rounded p-2 bg-white" id="valNoteLkps">-</div>
                </div>
            </div>

            <div id="validationEmpty" class="d-none text-muted">
                Belum ada hasil validasi.
            </div>

            <div id="validationError" class="d-none alert alert-danger alert-permanent">
                Gagal memuat hasil validasi.
            </div>
        </div>
    </div>

    {{-- ✅ Upload Files Section --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-cloud-upload"></i> Upload File Laporan Evaluasi Diri dan LKPS
            </h5>
        </div>
        <div class="card-body">

            {{-- 2. Laporan Evaluasi Diri (Data Kualitatif) --}}
            <div class="row g-0 align-items-stretch">
                <div class="col-lg-8">
                    <div class="upload-card h-100 {{ isset($uploadedFiles['kualitatif']) && $uploadedFiles['kualitatif'] ? 'has-file' : '' }}">
                        <div class="upload-icon">
                            <i class="bi {{ isset($uploadedFiles['kualitatif']) && $uploadedFiles['kualitatif'] ? 'bi-file-earmark-check' : 'bi-file-word' }}"></i>
                        </div>
                        <h6 class="fw-bold">Laporan Evaluasi Diri</h6>

                        @if(isset($uploadedFiles['kualitatif']) && $uploadedFiles['kualitatif'])
                        <p class="text-success mb-2">
                            <i class="bi bi-check-circle"></i> {{ $uploadedFiles['kualitatif']->original_filename }}
                        </p>
                        <small class="text-muted d-block mb-3">{{ $uploadedFiles['kualitatif']->created_at->diffForHumans() }}</small>
                        @else
                        <p class="text-muted small mb-3">File DOCX berisi deskripsi/narasi</p>
                        @endif
                        <div class="action-buttons">
                            {{-- Download Template --}}
                            <a href="{{ route('pengajuan.borang.download-template', $pengajuan->id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-download"></i> Download Template DOCX
                            </a>

                            {{-- Import DOCX --}}
                            <button type="button" class="btn btn-primary btn-sm" id="btnImportDocx">
                                <i class="bi bi-file-earmark-arrow-up"></i> Upload dari DOCX
                            </button>

                            {{-- Export DOCX --}}
                            <a href="{{ route('pengajuan.borang.export-docx', $pengajuan->id) }}" class="btn btn-success btn-sm">
                                <i class="bi bi-file-earmark-arrow-down"></i> Download ke DOCX
                            </a>
                        </div>

                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle"></i>
                            Download template, isi offline, lalu upload kembali. Atau isi online dan download hasilnya.
                        </small>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="upload-card h-100 clickable {{ isset($uploadedFiles['suplemen']) && $uploadedFiles['suplemen'] ? 'has-file' : '' }}" onclick="triggerUploadSuplemen()">
                        <div class="upload-icon">
                            <i class="bi {{ isset($uploadedFiles['suplemen']) && $uploadedFiles['suplemen'] ? 'bi-file-earmark-check' : 'bi-file-earmark-pdf' }}"></i>
                        </div>
                        <h6 class="fw-bold">Suplemen LED</h6>
                        @if(isset($uploadedFiles['suplemen']) && $uploadedFiles['suplemen'])
                        <p class="text-success mb-2">
                            <i class="bi bi-check-circle"></i> {{ $uploadedFiles['suplemen']->original_filename }}
                        </p>
                        <small class="text-muted">{{ $uploadedFiles['suplemen']->created_at->diffForHumans() }}</small>
                        @else
                        <p class="text-muted small mb-2">PDF sesuai template</p>
                        <button type="button" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-upload"></i> Upload PDF
                        </button>
                        @endif
                    </div>
                    <input type="file" id="inputSuplemen" class="d-none" accept=".pdf" onchange="handleUploadSuplemen(event)">
                </div>
            </div>

            <div class="row g-0 align-items-stretch">
                {{-- 1. Lembar Pengesahan --}}
                <div class="col-md-6">
                    <div class="upload-card h-100 clickable {{ isset($uploadedFiles['pengesahan']) && $uploadedFiles['pengesahan'] ? 'has-file' : '' }}" onclick="triggerUploadPengesahan()">
                        <div class="upload-icon">
                            <i class="bi {{ isset($uploadedFiles['pengesahan']) && $uploadedFiles['pengesahan'] ? 'bi-file-earmark-check' : 'bi-file-earmark-pdf' }}"></i>
                        </div>
                        <h6 class="fw-bold">Lembar Pengesahan</h6>
                        @if(isset($uploadedFiles['pengesahan']) && $uploadedFiles['pengesahan'])
                        <p class="text-success mb-2">
                            <i class="bi bi-check-circle"></i> {{ $uploadedFiles['pengesahan']->original_filename }}
                        </p>
                        <small class="text-muted">{{ $uploadedFiles['pengesahan']->created_at->diffForHumans() }}</small>
                        @else
                        <p class="text-muted small mb-2">PDF yang sudah ditandatangani dan distempel</p>
                        <button type="button" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-upload"></i> Upload PDF
                        </button>
                        @endif
                    </div>
                    <input type="file" id="inputPengesahan" class="d-none" accept=".pdf" onchange="handleUploadPengesahan(event)">
                </div>
                {{-- 3. LKPS (Data Kuantitatif) --}}
                <div class="col-md-6">
                    <div class="upload-card h-100 clickable {{ isset($uploadedFiles['kuantitatif']) && $uploadedFiles['kuantitatif'] ? 'has-file' : '' }}" onclick="triggerUploadKuantitatif()">
                        <div class="upload-icon">
                            <i class="bi {{ isset($uploadedFiles['kuantitatif']) && $uploadedFiles['kuantitatif'] ? 'bi-file-earmark-check' : 'bi-file-excel' }}"></i>
                        </div>
                        <h6 class="fw-bold">LKPS</h6>
                        @if(isset($uploadedFiles['kuantitatif']) && $uploadedFiles['kuantitatif'])
                        <p class="text-success mb-2">
                            <i class="bi bi-check-circle"></i> {{ $uploadedFiles['kuantitatif']->original_filename }}
                        </p>
                        <small class="text-muted">{{ $uploadedFiles['kuantitatif']->created_at->diffForHumans() }}</small>
                        @else
                        <p class="text-muted small mb-2">File Excel berisi data tabel</p>
                        <button type="button" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-upload"></i> Upload Excel
                        </button>
                        @endif
                    </div>
                    <input type="file" id="inputKuantitatif" class="d-none" accept=".xlsx,.xls" onchange="handleUploadKuantitatif(event)">
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Alert Gabungan (Petunjuk + Catatan) --}}
    <div class="alert alert-info alert-dismissible alert-permanent fade show" role="alert">
        <div class="row">
            <div class="col-md-6">
                <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Petunjuk Pengisian:</h6>
                <ol class="mb-0 small">
                    <li>Isi <strong>Kata Pengantar</strong> dan <strong>Ringkasan</strong> di bagian atas</li>
                    <li>Isi <strong>deskripsi/narasi</strong> untuk setiap elemen standar (D.1 - R.6)</li>
                    <li>Isi <strong>tabel HTML</strong> menggunakan editor (klik pada tabel untuk mengedit)</li>
                    <li>Isi <strong>Suplemen</strong> di bagian bawah sesuai jenjang program studi</li>
                    <li>Data tersimpan otomatis setelah 2 detik tidak ada perubahan</li>
                    <li>Klik <strong>"Finalisasi & Submit"</strong> setelah semua terisi</li>
                </ol>
            </div>
            <div class="col-md-6 border-start">
                <h6 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Catatan Penting:</h6>
                <ul class="mb-0 small">
                    <li><strong>Lembar Pengesahan:</strong> Upload PDF yang sudah ditandatangani pimpinan</li>
                    <li><strong>Laporan Evaluasi Diri:</strong> Upload DOCX dengan deskripsi setiap elemen - akan diproses otomatis</li>
                    <li><strong>LKPS:</strong> Upload Excel dengan sheet terpisah untuk setiap tabel - hanya tersimpan sebagai file</li>
                    <li><strong>Alternatif:</strong> Download template, isi offline, lalu upload kembali</li>
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    {{-- ✅ ACCORDION 1: Kata Pengantar --}}
    <div class="card mb-3 front-matter-card">
        <div class="card-header" id="headingKataPengantar">
            <button class="btn btn-link w-100 text-start collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKataPengantar" aria-expanded="false" aria-controls="collapseKataPengantar">
                <i class="bi bi-chevron-right me-2 chevron-icon"></i>
                <strong><i class="bi bi-pen"></i> Kata Pengantar</strong>
                <span class="badge bg-info float-end">Front Matter</span>
            </button>
        </div>
        <div id="collapseKataPengantar" class="accordion-collapse collapse" aria-labelledby="headingKataPengantar">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label fw-semibold mb-0">
                        <i class="bi bi-pencil-square"></i> Kata Pengantar (maksimal 500 kata)
                    </label>

                    <button type="button" class="btn btn-sm btn-primary btn-save-field" data-target-field="kata_pengantar">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
                <textarea class="form-control auto-save-field" name="kata_pengantar" data-field-id="kata_pengantar" data-field-type="front_matter" rows="12" placeholder="Tuliskan kata pengantar laporan evaluasi diri di sini...">{{ $existingData['kata_pengantar'] ?? '' }}</textarea>
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i>
                    <span class="char-count">
                        {{ str_word_count(trim(preg_replace('/\s+/', ' ', strip_tags($existingData['kata_pengantar'] ?? '')))) }}
                    </span>/maksimal 500 kata
                </small>
                <div class="save-status text-muted mt-1">
                    <i class="bi bi-cloud-check"></i>
                    <span class="status-text">
                        @if(isset($existingData['kata_pengantar']) && strlen($existingData['kata_pengantar']) > 20)
                        Tersimpan
                        @else
                        Belum ada perubahan
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ ACCORDION 2: Ringkasan --}}
    <div class="card mb-3 front-matter-card">
        <div class="card-header" id="headingRingkasan">
            <button class="btn btn-link w-100 text-start collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRingkasan" aria-expanded="false" aria-controls="collapseRingkasan">
                <i class="bi bi-chevron-right me-2 chevron-icon"></i>
                <strong><i class="bi bi-file-text"></i> Ringkasan Laporan</strong>
                <span class="badge bg-info float-end">Front Matter</span>
            </button>
        </div>
        <div id="collapseRingkasan" class="accordion-collapse collapse" aria-labelledby="headingRingkasan">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label fw-semibold mb-0">
                        <i class="bi bi-pencil-square"></i> Ringkasan Laporan (maksimal 1000 kata)
                    </label>

                    <button type="button" class="btn btn-sm btn-primary btn-save-field" data-target-field="ringkasan">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
                <textarea class="form-control auto-save-field" name="ringkasan" data-field-id="ringkasan" data-field-type="front_matter" rows="15" placeholder="Tuliskan ringkasan laporan evaluasi diri di sini...">{{ $existingData['ringkasan'] ?? '' }}</textarea>
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i>
                    <span class="char-count">
                        {{ str_word_count(trim(preg_replace('/\s+/', ' ', strip_tags($existingData['ringkasan'] ?? '')))) }}
                    </span>/maksimal 1000 kata
                </small>
                <div class="save-status text-muted mt-1">
                    <i class="bi bi-cloud-check"></i>
                    <span class="status-text">
                        @if(isset($existingData['ringkasan']) && strlen($existingData['ringkasan']) > 20)
                        Tersimpan
                        @else
                        Belum ada perubahan
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ ACCORDION 3-9: Kriteria (D, E, P, I, L, A, R) --}}
    <div class="accordion" id="accordionKriteria">
        @foreach($kriterias as $kriteria)
        @php
        $kProgress = $progressData['kriteria_progress'][$kriteria->id] ?? [
        'total_elemen' => 0,
        'completed_elemen' => 0,
        'percentage' => 0,
        ];
        @endphp

        <div class="card mb-3 kriteria-card" data-kriteria-id="{{ $kriteria->id }}">
            <div class="card-header kriteria-header" id="heading-kriteria-{{ $kriteria->id }}">
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-link kriteria-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-kriteria-{{ $kriteria->id }}" aria-expanded="false" aria-controls="collapse-kriteria-{{ $kriteria->id }}">
                        <i class="bi bi-chevron-right me-2 chevron-icon"></i>
                        <strong>{{ $kriteria->kode_kriteria }}:</strong> {{ $kriteria->nama_kriteria }}
                    </button>
                    <div class="d-flex gap-2 align-items-center">
                        {{-- Elemen Progress --}}
                        <span class="badge {{ $kProgress['completed_elemen'] === $kProgress['total_elemen'] ? 'bg-success' : 'bg-info' }} kriteria-progress-elemen" data-kriteria-id="{{ $kriteria->id }}" title="Elemen Lengkap">
                            <i class="bi bi-check-square"></i>
                            {{ $kProgress['completed_elemen'] }}/{{ $kProgress['total_elemen'] }}
                        </span>

                        <small class="text-white">{{ $kProgress['percentage'] }}%</small>
                        <button type="button" class="btn btn-sm btn-light" onclick="toggleKriteriaAccordion({{ $kriteria->id }})" title="Expand/Collapse Semua Elemen">
                            <i class="bi bi-arrows-expand"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Kriteria Body --}}
            <div id="collapse-kriteria-{{ $kriteria->id }}" class="accordion-collapse collapse" data-bs-parent="#accordionKriteria">
                <div class="card-body">
                    {{-- Nested Accordion per Elemen --}}
                    <div class="accordion" id="accordionElemen-{{ $kriteria->id }}">
                        @foreach($kriteria->elemenStandar as $elemen)
                        @php
                        $eProgress = $progressData['elemen_progress'][$elemen->id] ?? ['is_complete' => false];
                        $hasData = $eProgress['is_complete'];
                        $elemenBadgeClass = $hasData ? 'bg-success' : 'bg-warning text-dark';
                        @endphp

                        <div class="card mb-3 elemen-card @if($hasData) has-data @endif" data-elemen-id="{{ $elemen->id }}">
                            {{-- Elemen Header --}}
                            <div class="card-header elemen-header" id="heading-elemen-{{ $elemen->id }}">
                                <div class="d-md-flex justify-content-between align-items-center">
                                    <button class="btn btn-link elemen-btn collapsed d-flex flex-column flex-md-row align-items-start align-items-md-center w-100 gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-elemen-{{ $elemen->id }}" aria-expanded="false" aria-controls="collapse-elemen-{{ $elemen->id }}">
                                        <div>
                                            <span class="badge bg-primary me-2">{{ $elemen->kode_elemen }}</span>
                                            <strong>{{ $elemen->pernyataan_elemen }}</strong>
                                        </div>
                                        <div class="ms-auto d-flex align-items-center gap-2">
                                            <span class="badge {{ $elemenBadgeClass }} elemen-status-badge">
                                                @if($hasData)
                                                <i class="bi bi-check-circle"></i> Lengkap
                                                @else
                                                <i class="bi bi-clock"></i> Belum Lengkap
                                                @endif
                                            </span>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            {{-- Elemen Body --}}
                            <div id="collapse-elemen-{{ $elemen->id }}" class="accordion-collapse collapse elemen-collapse" aria-labelledby="heading-elemen-{{ $elemen->id }}" data-bs-parent="#accordionElemen-{{ $kriteria->id }}">
                                <div class="card-body">
                                    {{-- Deskripsi Narasi --}}
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label class="form-label fw-semibold mb-0">
                                                <i class="bi bi-pencil-square"></i> Deskripsi/Narasi Elemen (maksimal 1000 kata)
                                            </label>

                                            <button type="button" class="btn btn-sm btn-primary btn-save-field" data-target-field="desc_{{ $elemen->id }}">
                                                <i class="bi bi-save"></i> Simpan
                                            </button>
                                        </div>
                                        <textarea class="form-control auto-save-field tinymce-editor" name="desc_{{ $elemen->id }}" data-field-id="desc_{{ $elemen->id }}" data-field-type="description" rows="15" placeholder="Tuliskan deskripsi/narasi untuk {{ $elemen->kode_elemen }} di sini (maksimal 1000 kata)...">{{ $existingData["desc_{$elemen->id}"] ?? '' }}</textarea>
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i>
                                            <span class="char-count">{{ str_word_count(strip_tags($existingData["desc_{$elemen->id}"] ?? '')) }}</span>/maksimal 1000 kata
                                        </small>
                                        <div class="save-status text-muted mt-1">
                                            <i class="bi bi-cloud-check"></i>
                                            <span class="status-text">
                                                @if(isset($existingData["desc_{$elemen->id}"]) && strlen($existingData["desc_{$elemen->id}"]) > 20)
                                                Tersimpan
                                                @else
                                                Belum ada perubahan
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Dataset Fields (If Any) --}}
                                    @if($elemen->datasetBorang->count() > 0)
                                    <div>
                                        @foreach($elemen->datasetBorang as $dataset)
                                        @if(str_ends_with($dataset->kode, '.DESC'))
                                        @continue
                                        @endif
                                        <div class="dataset-field-wrapper">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label fw-semibold mb-0">
                                                    {{ $dataset->nama }}
                                                    @if($dataset->is_required) <span class="text-danger">*</span> @endif
                                                </label>

                                                <button type="button" class="btn btn-sm btn-primary btn-save-field" data-target-field="{{ $dataset->kode }}">
                                                    <i class="bi bi-save"></i> Simpan
                                                </button>
                                            </div>

                                            @if($dataset->deskripsi)
                                            <small class="d-block text-muted mb-2">{{ $dataset->deskripsi }}</small>
                                            @endif

                                            @if($dataset->tipe_field === 'table')
                                            <textarea id="editor_{{ \Illuminate\Support\Str::slug($dataset->kode, '_') }}" class="tinymce-editor" data-dataset-id="{{ $dataset->kode }}" data-field-type="table" data-template="{{ $dataset->expected_columns ? e(json_encode($dataset->expected_columns)) : '' }}">{!! $existingData[$dataset->kode] ?? $dataset->template_html ?? '' !!}</textarea>
                                            <div class="editor-actions">
                                                <small class="text-muted">
                                                    <i class="bi bi-info-circle"></i> Gunakan toolbar editor untuk mengedit tabel
                                                </small>
                                                <button type="button" class="btn btn-sm btn-primary btn-save-editor" data-dataset-id="{{ $dataset->kode }}">
                                                    <i class="bi bi-save"></i> Simpan
                                                </button>
                                            </div>

                                            <div class="save-status text-muted mt-1">
                                                <i class="bi bi-cloud-check"></i>
                                                <span class="status-text">
                                                    @if(isset($existingData[$dataset->kode]))
                                                    Tersimpan
                                                    @else
                                                    Belum ada data
                                                    @endif
                                                </span>
                                            </div>

                                            @elseif($dataset->tipe_field === 'narasi' || $dataset->tipe_field === 'textarea')
                                            <textarea class="form-control auto-save-field" name="{{ $dataset->kode }}" data-field-id="{{ $dataset->kode }}" data-field-type="textarea" rows="4" placeholder="{{ $dataset->placeholder }}" @if($dataset->is_required) required @endif>{{ $existingData[$dataset->kode] ?? '' }}</textarea>

                                            <div class="save-status text-muted mt-1">
                                                <i class="bi bi-cloud-check"></i>
                                                <span class="status-text">Belum ada perubahan</span>
                                            </div>

                                            @else
                                            <input type="text" class="form-control auto-save-field" name="{{ $dataset->kode }}" data-field-id="{{ $dataset->kode }}" data-field-type="text" placeholder="{{ $dataset->placeholder }}" value="{{ $existingData[$dataset->kode] ?? '' }}" @if($dataset->is_required) required @endif>

                                            <div class="save-status text-muted mt-1">
                                                <i class="bi bi-cloud-check"></i>
                                                <span class="status-text">Belum ada perubahan</span>
                                            </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
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

    {{-- ✅ ACCORDION 10: Suplemen --}}
    {{-- <div class="card mb-3 suplemen-card">
        <div class="card-header" id="headingSuplemen">
            <button class="btn btn-link w-100 text-start collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSuplemen" aria-expanded="false" aria-controls="collapseSuplemen">
                <i class="bi bi-chevron-right me-2 chevron-icon"></i>
                <strong><i class="bi bi-plus-square"></i> Suplemen Program Studi</strong>
                <span class="badge bg-secondary float-end">Opsional</span>
            </button>
        </div>
        <div id="collapseSuplemen" class="accordion-collapse collapse" aria-labelledby="headingSuplemen">
            <div class="card-body">
                <div class="alert alert-info alert-permanent mb-3">
                    <i class="bi bi-info-circle"></i>
                    Isi bagian suplemen sesuai dengan jenjang program studi Anda ({{ $pengajuan->studyProgram->degreeLevel->alias }})
</div>

<label class="form-label fw-semibold">
    <i class="bi bi-pencil-square"></i> Konten Suplemen
</label>
<textarea class="form-control auto-save-field" name="suplemen" data-field-id="suplemen" data-field-type="suplemen" rows="20" placeholder="Tuliskan konten suplemen sesuai jenjang program studi di sini...">{{ $existingData['suplemen'] ?? '' }}</textarea>
<small class="text-muted">
    <i class="bi bi-info-circle"></i>
    Contoh: Capaian Pembelajaran Lulusan (CPL), Susunan Materi Pembelajaran, Beban Belajar, dll.
</small>
<div class="save-status text-muted mt-1">
    <i class="bi bi-cloud-check"></i>
    <span class="status-text">
        @if(isset($existingData['suplemen']) && strlen($existingData['suplemen']) > 20)
        Tersimpan
        @else
        Belum ada perubahan
        @endif
    </span>
</div>
</div>
</div>
</div> --}}
</div>

{{-- Loading Overlay --}}
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

{{-- Modal Import DOCX --}}
<div class="modal fade" id="modalImportDocx" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark-arrow-up"></i> Upload Borang dari DOCX</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formImportDocx" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Pilih File DOCX</label>
                        <input type="file" class="form-control" name="docx_file" accept=".docx,.doc" required>
                        <small class="text-muted">
                            Format: .docx atau .doc (Max 10MB)
                        </small>
                    </div>

                    <div class="alert alert-warning alert-permanent">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Perhatian:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Data yang ada akan ditimpa dengan data dari file</li>
                            <li>Pastikan format file sesuai template</li>
                        </ul>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSubmitImport">
                    <i class="bi bi-upload"></i> Upload Sekarang
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@8.3.1/tinymce.min.js"></script>
@php
$pengajuanId = $pengajuan->id;
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pengajuanId = "{{ $pengajuan->id }}";
        const initialProgress = @json($progressData);

        let saveTimeout, progressTimeout;
        const AUTO_SAVE_DELAY = 2000;
        const AUTOSAVE_ENABLED = false;
        const editorInstances = {};

        // Initialize
        initializeAutoSave();
        initializeWYSIWYGEditors();
        initializeToggleButton();
        initializeFinalize();
        initializeResetBorang();
        initializeChevronIcons();
        initializeImportExport();
        initializeManualSaveButtons();
        fetchValidationSummary();

        // ✅ Initial progress update
        updateProgress();

        function initializeChevronIcons() {
            document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(button => {
                button.addEventListener('click', function() {
                    const icon = this.querySelector('.chevron-icon');
                    if (icon) {
                        icon.classList.toggle('bi-chevron-right');
                        icon.classList.toggle('bi-chevron-down');
                    }
                });
            });
        }

        function initializeWYSIWYGEditors() {
            const editorElements = document.querySelectorAll('textarea.tinymce-editor');
            let initCount = 0;
            const totalEditors = editorElements.length;

            editorElements.forEach(function(el) {
                const key = el.dataset.fieldId || el.dataset.datasetId;

                tinymce.init({
                    license_key: 'gpl'
                    , target: el
                    , menubar: false
                    , height: 550
                    , plugins: 'table lists link code'
                    , toolbar: [
                        'undo redo | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist |'
                        , 'table | link | code'
                    ].join(' ')
                    , content_style: 'table { border-collapse: collapse; width: 100%; } td, th { border: 1px solid #ddd; padding: 8px; } th { background: #f2f2f2; font-weight: bold; }'
                    , setup: function(editor) {
                        editor.on('init', function() {
                            const existing = editor.getContent({
                                format: 'html'
                            }).trim();
                            if (!existing || existing.length < 50) {
                                const templateColumns = el.dataset.template;
                                if (templateColumns) {
                                    try {
                                        const columns = JSON.parse(templateColumns);
                                        if (columns && columns.length > 0) {
                                            editor.setContent(buildTemplateTable(columns));
                                        }
                                    } catch (e) {
                                        console.error('Template JSON parse error:', key, e);
                                    }
                                }
                            }
                            editorInstances[key] = editor;

                            // ✅ Count initialized editors
                            initCount++;

                            // ✅ Update progress when ALL editors are ready
                            if (initCount === totalEditors) {
                                setTimeout(function() {
                                    updateProgress();
                                }, 500);
                            }
                        });

                        editor.on('input change keyup', function() {
                            if (!AUTOSAVE_ENABLED) return;

                            clearTimeout(saveTimeout);

                            // key editor (desc_xxx atau dataset kode tabel)
                            const key = el.dataset.fieldId || el.dataset.datasetId;

                            // cari status element (wrapper sama seperti autosave textarea biasa)
                            const wrapper = el.closest('.mb-3, .mb-4, .dataset-field-wrapper, .card-body');
                            const statusElement = wrapper ? wrapper.querySelector('.status-text') : null;

                            if (statusElement) {
                                statusElement.textContent = 'Menyimpan...';
                                statusElement.className = 'status-text text-warning';
                            }

                            // update word count (lihat bagian #2)
                            const counterEl = wrapper ? wrapper.querySelector('.char-count') : null;
                            if (counterEl) {
                                const plainText = editor.getContent({
                                    format: 'text'
                                });
                                counterEl.textContent = countWords(plainText);
                            }

                            saveTimeout = setTimeout(function() {
                                const value = editor.getContent(); // simpan html (biar format tidak hilang)
                                autoSaveField(key, value, statusElement);
                            }, AUTO_SAVE_DELAY);
                        });
                    }
                });
            });
        }

        function countWords(text) {
            if (!text) return 0;
            const cleaned = text.replace(/\u00A0/g, ' ').trim(); // handle &nbsp;
            if (!cleaned) return 0;
            return cleaned.split(/\s+/).filter(Boolean).length;
        }

        function buildTemplateTable(columns) {
            let html = '<table style="width:100%;border-collapse:collapse;"><thead><tr>';
            columns.forEach(col => {
                html += `<th style="border:1px solid #ddd;padding:8px;background-color:#f2f2f2;">${escapeHtml(col)}</th>`;
            });
            html += '</tr></thead><tbody>';

            for (let i = 0; i < 3; i++) {
                html += '<tr>';
                columns.forEach(() => {
                    html += '<td style="border:1px solid #ddd;padding:8px;">&nbsp;</td>';
                });
                html += '</tr>';
            }

            html += '</tbody></table>';
            return html;
        }

        function escapeHtml(str) {
            return String(str)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function initializeManualSaveButtons() {
            document.querySelectorAll('.btn-save-field').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const fieldId = this.dataset.targetField;
                    const field = findFieldById(fieldId);

                    // cari wrapper status (konsisten dengan yang lain)
                    const wrapper = field ? field.closest('.mb-3, .mb-4, .dataset-field-wrapper, .card-body') : null;
                    const statusElement = wrapper ? wrapper.querySelector('.status-text') : null;

                    const originalHtml = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

                    try {
                        await manualSaveByFieldId(fieldId, statusElement, this);
                        this.innerHTML = '<i class="bi bi-check-circle"></i> Tersimpan';
                        setTimeout(() => {
                            this.innerHTML = originalHtml;
                        }, 1500);
                    } catch (e) {
                        console.error(e);
                        this.innerHTML = originalHtml;
                        Swal.fire('Error', e.message || 'Gagal menyimpan', 'error');
                    } finally {
                        this.disabled = false;
                    }
                });
            });
        }

        async function autoSaveField(fieldId, value, statusElement) {
            if (statusElement) {
                statusElement.textContent = 'Menyimpan...';
                statusElement.className = 'status-text text-warning';
            }

            try {
                const response = await fetch(`/pengajuan/${pengajuanId}/borang-online/save-field`, {
                    method: 'POST'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                    }
                    , body: JSON.stringify({
                        dataset_id: fieldId
                        , value: value
                    })
                });

                const data = await response.json();

                if (data.success) {
                    if (statusElement) {
                        const now = new Date();
                        statusElement.textContent = `Tersimpan pada ${now.toLocaleTimeString('id-ID')}`;
                        statusElement.className = 'status-text text-success';
                    }
                    // ✅ Update progress setelah save berhasil
                    updateProgress();
                } else {
                    throw new Error(data.message || 'Gagal menyimpan');
                }

            } catch (error) {
                console.error('Save error:', error);
                if (statusElement) {
                    statusElement.textContent = 'Gagal menyimpan!';
                    statusElement.className = 'status-text text-danger';
                }
                throw error;
            }
        }

        function findFieldById(fieldId) {
            // cari input/textarea yang punya data-field-id
            return document.querySelector(`[data-field-id="${CSS.escape(fieldId)}"]`);
        }

        async function manualSaveByFieldId(fieldId, statusElement = null, buttonEl = null) {
            // kalau TinyMCE
            if (editorInstances[fieldId]) {
                const html = editorInstances[fieldId].getContent();
                await autoSaveField(fieldId, html, statusElement);
                return;
            }

            const field = findFieldById(fieldId);
            if (!field) throw new Error(`Field tidak ditemukan: ${fieldId}`);

            await autoSaveField(fieldId, field.value, statusElement);
        }

        function initializeAutoSave() {
            // kalau autosave dimatikan, jangan pasang listener input sama sekali
            if (!AUTOSAVE_ENABLED) return;

            document.querySelectorAll('.auto-save-field').forEach(field => {
                field.addEventListener('input', function() {
                    clearTimeout(saveTimeout);

                    const wrapper = field.closest('.mb-3, .mb-4, .dataset-field-wrapper, .card-body');
                    const statusElement = wrapper ? wrapper.querySelector('.status-text') : null;

                    if (statusElement) {
                        statusElement.textContent = 'Menyimpan...';
                        statusElement.className = 'status-text text-warning';
                    }

                    // update word count (textarea saja)
                    if (field.tagName === 'TEXTAREA') {
                        const charCount = wrapper ? wrapper.querySelector('.char-count') : null;
                        if (charCount) charCount.textContent = countWords(field.value);
                    }

                    saveTimeout = setTimeout(() => saveField(field), AUTO_SAVE_DELAY);
                });
            });
        }

        async function saveField(field) {
            const fieldId = field.dataset.fieldId;
            const value = field.value;
            const wrapper = field.closest('.mb-3, .mb-4, .dataset-field-wrapper, .card-body');
            const statusElement = wrapper ? wrapper.querySelector('.status-text') : null;

            await autoSaveField(fieldId, value, statusElement);
        }

        function updateProgress() {
            let completedElemenCount = 0;
            let totalElemenCount = 0;

            document.querySelectorAll('.elemen-card').forEach(function(card) {
                totalElemenCount++;

                // ✅ Check deskripsi field - FIXED untuk TinyMCE
                const descField = card.querySelector('textarea[data-field-type="description"]');
                let hasDescription = false;

                if (descField) {
                    const descFieldId = descField.dataset.fieldId;
                    let descValue = '';

                    // ✅ Check if it's a TinyMCE editor
                    if (editorInstances[descFieldId]) {
                        descValue = editorInstances[descFieldId].getContent({
                            format: 'text'
                        }).trim();
                    } else {
                        descValue = descField.value.trim();
                    }

                    hasDescription = (descValue && descValue.length > 0);
                }

                // ✅ Check table fields (jika ada)
                const tableFields = card.querySelectorAll('textarea[data-field-type="table"]');
                let hasAllTables = true;
                let tableCount = tableFields.length;

                if (tableCount > 0) {
                    tableFields.forEach(function(tableField) {
                        const datasetId = tableField.dataset.datasetId;
                        const editor = editorInstances[datasetId];

                        if (editor) {
                            const content = editor.getContent();
                            const hasData = hasTableData(content);

                            if (!hasData) {
                                hasAllTables = false;
                            }
                        } else {
                            // Fallback: check textarea value
                            const content = tableField.value;
                            if (!hasTableData(content)) {
                                hasAllTables = false;
                            }
                        }
                    });
                }

                // ✅ Elemen complete jika:
                // 1. Ada description (mandatory)
                // 2. Semua table terisi (jika ada table)
                const isElemenComplete = hasDescription && (tableCount === 0 || hasAllTables);

                if (isElemenComplete) {
                    completedElemenCount++;
                    card.classList.add('has-data');
                } else {
                    card.classList.remove('has-data');
                }

                updateElemenBadge(card, isElemenComplete);
            });

            const elemenPercentage = totalElemenCount > 0 ? Math.round((completedElemenCount / totalElemenCount) * 100) : 0;

            updateProgressUI(completedElemenCount, totalElemenCount, elemenPercentage);
            updateKriteriaProgress();
        }

        // ✅ Helper: Check if table has real data
        function hasTableData(tableHtml) {
            if (!tableHtml || tableHtml.length < 100) return false;
            if (tableHtml.indexOf('<table') === -1) return false;

            // Extract td contents
            const tdRegex = /<td[^>]*>(.*?)<\/td>/gi;
            const matches = tableHtml.match(tdRegex);

            if (!matches || matches.length === 0) return false;

            let nonEmptyCount = 0;

            matches.forEach(match => {
                // Remove tags
                let content = match.replace(/<[^>]+>/g, '');
                // Decode HTML entities
                content = content.replace(/&nbsp;/g, '').replace(/&\w+;/g, '');
                // Trim
                content = content.trim();

                // Skip numbering (1, 2, 3, etc)
                if (content && !/^\d+$/.test(content)) {
                    nonEmptyCount++;
                }
            });

            // Has data if at least 3 meaningful cells
            return nonEmptyCount >= 3;
        }

        function updateElemenBadge(card, isComplete) {
            const statusBadge = card.querySelector('.elemen-status-badge');

            if (statusBadge) {
                if (isComplete) {
                    statusBadge.className = 'badge bg-success elemen-status-badge';
                    statusBadge.innerHTML = '<i class="bi bi-check-circle"></i> Lengkap';
                } else {
                    statusBadge.className = 'badge bg-warning text-dark elemen-status-badge';
                    statusBadge.innerHTML = '<i class="bi bi-clock"></i> Belum Lengkap';
                }
            }
        }

        function updateProgressUI(completedElemen, totalElemen, elemenPercentage) {
            document.getElementById('progressBarElemen').style.width = elemenPercentage + '%';
            document.getElementById('progressPercentageElemen').textContent = elemenPercentage + '%';
            document.getElementById('progressCompletedElemen').textContent = completedElemen;
            document.getElementById('progressRemainingElemen').textContent = totalElemen - completedElemen;
            document.getElementById('progressTotalElemen').textContent = totalElemen;
            document.getElementById('progressCountElemen').textContent = completedElemen + '/' + totalElemen;

            const progressBarElemen = document.getElementById('progressBarElemen');
            if (elemenPercentage === 100) {
                progressBarElemen.className = 'progress-bar bg-success';
            } else if (elemenPercentage >= 75) {
                progressBarElemen.className = 'progress-bar bg-info';
            } else if (elemenPercentage >= 50) {
                progressBarElemen.className = 'progress-bar bg-warning';
            } else {
                progressBarElemen.className = 'progress-bar bg-danger';
            }
        }

        function updateKriteriaProgress() {
            document.querySelectorAll('.kriteria-card').forEach(function(kriteriaCard) {
                const elemenCards = kriteriaCard.querySelectorAll('.elemen-card');

                let totalElemen = elemenCards.length;
                let completedElemen = 0;

                elemenCards.forEach(function(card) {
                    // ✅ Re-check elemen completion - FIXED untuk TinyMCE
                    const descField = card.querySelector('textarea[data-field-type="description"]');
                    let hasDescription = false;

                    if (descField) {
                        const descFieldId = descField.dataset.fieldId;
                        let descValue = '';

                        // ✅ Check if it's a TinyMCE editor
                        if (editorInstances[descFieldId]) {
                            descValue = editorInstances[descFieldId].getContent({
                                format: 'text'
                            }).trim();
                        } else {
                            descValue = descField.value.trim();
                        }

                        hasDescription = (descValue && descValue.length > 0);
                    }

                    const tableFields = card.querySelectorAll('textarea[data-field-type="table"]');
                    let hasAllTables = true;

                    if (tableFields.length > 0) {
                        tableFields.forEach(function(tableField) {
                            const datasetId = tableField.dataset.datasetId;
                            const editor = editorInstances[datasetId];

                            if (editor) {
                                const content = editor.getContent();
                                if (!hasTableData(content)) {
                                    hasAllTables = false;
                                }
                            } else {
                                if (!hasTableData(tableField.value)) {
                                    hasAllTables = false;
                                }
                            }
                        });
                    }

                    const isComplete = hasDescription && (tableFields.length === 0 || hasAllTables);
                    if (isComplete) {
                        completedElemen++;
                    }
                });

                const elemenBadge = kriteriaCard.querySelector('.kriteria-progress-elemen');
                if (elemenBadge) {
                    elemenBadge.innerHTML = '<i class="bi bi-check-square"></i> ' + completedElemen + '/' + totalElemen;
                    elemenBadge.className = completedElemen === totalElemen && totalElemen > 0 ?
                        'badge bg-success kriteria-progress-elemen' :
                        'badge bg-info kriteria-progress-elemen';
                }

                // ✅ Update percentage text
                const percentageText = kriteriaCard.querySelector('.text-white');
                if (percentageText && totalElemen > 0) {
                    const percentage = Math.round((completedElemen / totalElemen) * 100);
                    percentageText.textContent = percentage + '%';
                }
            });
        }

        function initializeToggleButton() {
            const btnToggle = document.getElementById('btnToggleAll');
            let isExpanded = false;

            btnToggle.addEventListener('click', function() {
                // ✅ Toggle ALL accordions (Front Matter + Kriteria + Suplemen)
                document.querySelectorAll('.accordion-collapse').forEach(accordion => {
                    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(accordion, {
                        toggle: false
                    });
                    isExpanded ? bsCollapse.hide() : bsCollapse.show();
                });

                // ✅ Update chevron icons
                document.querySelectorAll('.chevron-icon').forEach(icon => {
                    if (isExpanded) {
                        icon.classList.remove('bi-chevron-down');
                        icon.classList.add('bi-chevron-right');
                    } else {
                        icon.classList.remove('bi-chevron-right');
                        icon.classList.add('bi-chevron-down');
                    }
                });

                isExpanded = !isExpanded;
                this.innerHTML = isExpanded ?
                    '<i class="bi bi-arrows-collapse"></i> Collapse All' :
                    '<i class="bi bi-arrows-expand"></i> Expand All';
            });
        }

        function initializeResetBorang() {
            const btnReset = document.getElementById('btnResetBorang');
            if (!btnReset) return;

            btnReset.addEventListener('click', async function() {
                const result = await Swal.fire({
                    icon: 'warning'
                    , title: 'Reset Borang?'
                    , html: '<p>Semua data yang sudah diisi akan dihapus. Ketik <strong>RESET</strong> untuk konfirmasi:</p>'
                    , input: 'text'
                    , inputPlaceholder: 'Ketik RESET'
                    , showCancelButton: true
                    , confirmButtonColor: '#dc3545'
                    , confirmButtonText: 'Ya, Reset!'
                    , cancelButtonText: 'Batal'
                    , inputValidator: (value) => {
                        if (value !== 'RESET') {
                            return 'Ketik "RESET" dengan benar!'
                        }
                    }
                });

                if (!result.isConfirmed) return;

                showLoading();

                try {
                    const response = await fetch(`/pengajuan/${pengajuanId}/borang/reset`, {
                        method: 'POST'
                        , headers: {
                            'Content-Type': 'application/json'
                            , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            , 'Accept': 'application/json'
                        }
                        , body: JSON.stringify({
                            confirm: result.value
                        })
                    });

                    const data = await response.json();
                    hideLoading();

                    if (data.success) {
                        await Swal.fire({
                            icon: 'success'
                            , title: 'Borang Direset!'
                            , text: 'Halaman akan dimuat ulang...'
                            , timer: 2000
                            , showConfirmButton: false
                        });

                        window.location.href = window.location.href + '?reset=1&t=' + Date.now();
                    } else {
                        throw new Error(data.message || 'Reset gagal');
                    }

                } catch (error) {
                    hideLoading();
                    Swal.fire('Error', error.message, 'error');
                }
            });
        }

        function initializeFinalize() {
            document.getElementById('btnFinalize').addEventListener('click', async function() {
                const confirmed = await Swal.fire({
                    icon: 'question'
                    , title: 'Konfirmasi Finalisasi'
                    , html: '<p><strong>Submit laporan evaluasi diri?</strong></p>'
                    , showCancelButton: true
                    , confirmButtonText: 'Ya, Finalisasi'
                    , cancelButtonText: 'Batal'
                    , confirmButtonColor: '#28a745'
                    , reverseButtons: true
                });

                if (!confirmed.isConfirmed) return;

                showLoading();

                try {
                    const response = await fetch(`/pengajuan/${pengajuanId}/borang-online/submit`, {
                        method: 'POST'
                        , headers: {
                            'Content-Type': 'application/json'
                            , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            , 'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    hideLoading();

                    if (data.success) {
                        await Swal.fire({
                            icon: 'success'
                            , title: 'Finalisasi Berhasil!'
                            , text: data.message
                            , confirmButtonColor: '#28a745'
                        });
                        window.location.href = '{{ route("pengajuan.show", $pengajuan->id) }}';
                    } else {
                        throw new Error(data.message || 'Finalisasi gagal');
                    }

                } catch (error) {
                    hideLoading();
                    Swal.fire('Error', error.message, 'error');
                }
            });
        }

        window.toggleKriteriaAccordion = function(kriteriaId) {
            const kriteriaCollapse = document.getElementById(`collapse-kriteria-${kriteriaId}`);
            if (!kriteriaCollapse) return;

            const kriteriaInstance = bootstrap.Collapse.getOrCreateInstance(kriteriaCollapse, {
                toggle: false
            });
            const isOpen = kriteriaCollapse.classList.contains('show');

            isOpen ? kriteriaInstance.hide() : kriteriaInstance.show();

            const elemenCollapses = kriteriaCollapse.querySelectorAll('.elemen-collapse');
            elemenCollapses.forEach(el => {
                const instance = bootstrap.Collapse.getOrCreateInstance(el, {
                    toggle: false
                });
                isOpen ? instance.hide() : instance.show();
            });
        };

        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('show');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('show');
        }

        // ✅ Upload Handlers
        window.triggerUploadPengesahan = () => document.getElementById('inputPengesahan').click();
        window.triggerUploadSuplemen = () => document.getElementById('inputSuplemen').click();
        window.triggerUploadKuantitatif = () => document.getElementById('inputKuantitatif').click();

        window.handleUploadPengesahan = async (event) => {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.name.endsWith('.pdf')) {
                Swal.fire('Error', 'File harus berformat PDF', 'error');
                return;
            }

            await uploadFile(file, 'pengesahan', '{{ route("pengajuan.upload-pengesahan", $pengajuan->id) }}');
        };
        window.handleUploadSuplemen = async (event) => {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.name.endsWith('.pdf')) {
                Swal.fire('Error', 'File harus berformat PDF', 'error');
                return;
            }

            await uploadFile(file, 'file_suplemen', '{{ route("pengajuan.upload-suplemen", $pengajuan->id) }}');
        };

        window.handleUploadKuantitatif = async (event) => {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.name.match(/\.(xlsx|xls)$/)) {
                Swal.fire('Error', 'File harus berformat Excel', 'error');
                return;
            }

            const result = await Swal.fire({
                icon: 'question'
                , title: 'Upload Data Kuantitatif?'
                , html: `
                    <p>File: <strong>${file.name}</strong></p>
                    <p>Ukuran: <strong>${formatFileSize(file.size)}</strong></p>
                    <p class="text-muted mt-2">File akan disimpan (tidak diproses otomatis)</p>
                `
                , showCancelButton: true
                , confirmButtonText: 'Ya, Upload'
                , cancelButtonText: 'Batal'
            });

            if (!result.isConfirmed) return;

            await uploadFile(file, 'file_kuantitatif', '{{ route("pengajuan.upload-kuantitatif", $pengajuan->id) }}');
        };

        async function uploadFile(file, fieldName, url) {
            const formData = new FormData();
            formData.append(fieldName, file);

            showLoading();

            try {
                const response = await fetch(url, {
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
                    Swal.fire('Success', 'File berhasil diupload!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    throw new Error(data.message || 'Upload gagal');
                }
            } catch (error) {
                hideLoading();
                Swal.fire('Error', error.message, 'error');
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        function initializeImportExport() {
            document.getElementById('btnImportDocx').addEventListener('click', function() {
                const modal = showModalById('modalImportDocx');
            });

            document.getElementById('btnSubmitImport').addEventListener('click', async function() {
                const form = document.getElementById('formImportDocx');
                const formData = new FormData(form);
                const fileInput = form.querySelector('input[type="file"]');

                if (!fileInput.files.length) {
                    Swal.fire('Error', 'Pilih file DOCX terlebih dahulu', 'error');
                    return;
                }

                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengupload...';

                try {
                    const response = await fetch('/pengajuan/' + pengajuanId + '/borang/import-docx', {
                        method: 'POST'
                        , headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            , 'Accept': 'application/json'
                        }
                        , body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalImportDocx')).hide();
                        Swal.fire('Success', 'File berhasil diupload!', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        throw new Error(data.message || 'Import gagal');
                    }

                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-upload"></i> Upload Sekarang';
                }
            });
        }

        async function fetchValidationSummary() {
            const url = @json(route('pengajuan.validation-summary', $pengajuanId));

            const elLoading = document.getElementById('validationLoading');
            const elContent = document.getElementById('validationContent');
            const elEmpty = document.getElementById('validationEmpty');
            const elError = document.getElementById('validationError');
            const elBadge = document.getElementById('validationBadge');

            // reset state
            elLoading.classList.remove('d-none');
            elContent.classList.add('d-none');
            elEmpty.classList.add('d-none');
            elError.classList.add('d-none');
            elBadge.className = 'badge bg-secondary';
            elBadge.textContent = 'Memuat...';

            try {
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.message || 'Request gagal');

                elLoading.classList.add('d-none');

                if (!data.has_validation) {
                    elEmpty.classList.remove('d-none');
                    elBadge.className = 'badge bg-secondary';
                    elBadge.textContent = 'Belum ada';
                    return;
                }

                // render
                const v = data.validation;
                const counts = v.counts;

                document.getElementById('validatorName').textContent = data.validator ? data.validator.name : '-';

                document.getElementById('valLedCount').textContent = `${counts.led.reviewed}/${counts.led.total}`;
                document.getElementById('valSuplemenCount').textContent = `${counts.suplemen.reviewed}/${counts.suplemen.total}`;
                document.getElementById('valLkpsCount').textContent = `${counts.lkps.reviewed}/${counts.lkps.total}`;

                const pct = counts.total.percentage ? counts.total.percentage : 0;
                const bar = document.getElementById('valTotalBar');
                bar.style.width = pct + '%';
                document.getElementById('valTotalText').textContent = `${pct}%`;
                document.getElementById('valUpdatedAt').textContent = v.updated_at ? v.updated_at : '-';

                // notes
                document.getElementById('valNoteAll').textContent = (v.notes.catatan_validator || '-');
                document.getElementById('valNoteLed').textContent = (v.notes.catatan_led || '-');
                document.getElementById('valNoteSuplemen').textContent = (v.notes.catatan_suplemen || '-');
                document.getElementById('valNoteLkps').textContent = (v.notes.catatan_lkps || '-');

                // badge status: final_action dan completeness
                // final_action: approve|revision|null
                if (!v.final_action) {
                    elBadge.className = 'badge bg-info';
                    elBadge.textContent = v.is_complete ? 'Lengkap (Belum Submit)' : 'Belum Lengkap';
                } else if (v.final_action === 'approve') {
                    elBadge.className = 'badge bg-success';
                    elBadge.textContent = 'Disubmit: Approve';
                } else if (v.final_action === 'revision') {
                    elBadge.className = 'badge bg-warning text-dark';
                    elBadge.textContent = 'Disubmit: Revisi';
                } else {
                    elBadge.className = 'badge bg-secondary';
                    elBadge.textContent = 'Status';
                }

                elContent.classList.remove('d-none');

            } catch (err) {
                console.error(err);
                elLoading.classList.add('d-none');
                elError.classList.remove('d-none');
                elBadge.className = 'badge bg-danger';
                elBadge.textContent = 'Error';
            }
        }
    });

</script>
@endpush
