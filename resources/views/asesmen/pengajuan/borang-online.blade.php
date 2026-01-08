{{-- resources/views/asesmen/pengajuan/borang-online.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Isi Lembar Evaluasi Diri - ' . $pengajuan->studyProgram->name)

@push('styles')
{{-- CKEditor 5 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/tinymce@8.3.1/skins/ui/oxide/content.min.css" rel="stylesheet">
{{-- <link rel="stylesheet" href="{{ asset('assets/css/ckeditor5.css') }}"> --}}

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

    /* Existing styles */
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

    .import-export-section {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
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
                    <h3 class="mb-1">Lembar Evaluasi Diri</h3>
                    <p class="text-muted mb-0">
                        {{ $pengajuan->studyProgram->name }} - {{ $pengajuan->nomor_pengajuan }}
                    </p>
                </div>
                <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            {{-- ✅ Progress Section with Server Data --}}
            <div class="progress-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-3">Progress Pengisian Borang</h5>

                        @php
                        // ✅ Use field percentage for main progress bar
                        $fieldPercentage = $progressData['field_percentage'];
                        $elemenPercentage = $progressData['elemen_percentage'];
                        $barColor = $fieldPercentage === 100 ? 'bg-success' :
                        ($fieldPercentage >= 75 ? 'bg-info' :
                        ($fieldPercentage >= 50 ? 'bg-warning' : 'bg-danger'));
                        @endphp

                        {{-- Main Progress Bar (Field-based) --}}
                        <div class="mb-2">
                            <small class="text-white-70">Progress Elemen</small>
                            <div class="progress" style="height: 20px; background-color: rgba(255,255,255,0.3);">
                                <div class="progress-bar bg-light" role="progressbar" id="progressBarElemen" style="width: {{ $elemenPercentage }}%" data-initial-value="{{ $elemenPercentage }}">
                                    <span id="progressPercentageElemen" style="font-weight: bold; font-size: 0.875rem;">{{ $elemenPercentage }}%</span>
                                </div>
                            </div>
                        </div>

                        {{-- Secondary Progress Bar (Elemen-based) --}}
                        <div class="mb-3">
                            <small class="text-white-70">Progress Bagian</small>
                            <div class="progress" style="height: 25px; background-color: rgba(255,255,255,0.3);">
                                <div class="progress-bar {{ $barColor }}" role="progressbar" id="progressBarFields" style="width: {{ $fieldPercentage }}%" data-initial-value="{{ $fieldPercentage }}">
                                    <span id="progressPercentageFields" style="font-weight: bold;">{{ $fieldPercentage }}%</span>
                                </div>
                            </div>
                        </div>

                        <div class="progress-stats">
                            <div class="stat-item" title="Jumlah bagian/field yang sudah diisi dari total bagian">
                                <span class="stat-number" id="progressCompletedFields">{{ $progressData['filled_fields'] }}</span>
                                <span class="stat-label">Bagian Terisi</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="progressRemainingFields">{{ $progressData['remaining_fields'] }}</span>
                                <span class="stat-label">Bagian Tersisa</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="progressTotalFields">{{ $progressData['total_fields'] }}</span>
                                <span class="stat-label">Total Bagian</span>
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
                        <div class="mt-3 text-white">
                            <small id="progressCountFields">{{ $progressData['filled_fields'] }}/{{ $progressData['total_fields'] }} Bagian</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="import-export-section">
                <h6 class="mb-3"><i class="bi bi-file-earmark-arrow-up-fill"></i> Download / Upload Borang</h6>

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

        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-success" id="btnFinalize">
                        <i class="bi bi-check-circle"></i> Finalisasi & Submit Borang
                    </button>
                    <small class="d-block text-muted mt-1">
                        <i class="bi bi-info-circle"></i> Pastikan semua bagian sudah diisi sebelum finalisasi
                    </small>
                </div>
                <div class="btn-group">
                    {{-- ✅ Reset Button --}}
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

    {{-- Alert Info --}}
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Petunjuk:</strong>
        <ol class="mb-0 mt-2">
            <li>Isi <strong>deskripsi/narasi</strong> untuk setiap elemen standar</li>
            <li>Isi <strong>tabel HTML</strong> menggunakan editor (klik pada tabel untuk mengedit)</li>
            <li>Klik tombol <strong>"Simpan"</strong> setelah mengisi setiap bagian</li>
            <li>Atau gunakan fitur <strong>Upload DOCX</strong> untuk mengisi dari file</li>
            <li>Klik <strong>"Finalisasi & Submit"</strong> setelah semua bagian terisi</li>
        </ol>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    {{-- ✅ Accordion per Kriteria with Server Progress --}}
    <div class="accordion" id="accordionKriteria">
        @foreach($kriterias as $kriteria)
        @php
        $kProgress = $progressData['kriteria_progress'][$kriteria->id] ?? [
        'total_fields' => 0,
        'filled_fields' => 0,
        'total_elemen' => 0,
        'completed_elemen' => 0,
        'percentage' => 0,
        ];

        $badgeClass = $kProgress['percentage'] === 100 ? 'bg-success' :
        ($kProgress['percentage'] >= 50 ? 'bg-warning' : 'bg-secondary');
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
                        <span class="badge {{ $kProgress['completed_elemen'] === $kProgress['total_elemen'] ? 'bg-success' : 'bg-info' }} kriteria-progress-elemen" title="Elemen yang sudah lengkap 100%" data-kriteria-id="{{ $kriteria->id }}" title="Elemen Lengkap">
                            <i class="bi bi-check-square"></i>
                            {{ $kProgress['completed_elemen'] }}/{{ $kProgress['total_elemen'] }}
                        </span>

                        {{-- Field Progress --}}
                        <span class="badge {{ $badgeClass }} kriteria-progress-fields" title="Total bagian/field yang terisi" data-kriteria-id="{{ $kriteria->id }}" title="Bagian Terisi" data-initial-filled="{{ $kProgress['filled_fields'] }}" data-initial-total="{{ $kProgress['total_fields'] }}">
                            <i class="bi bi-list-check"></i>
                            {{ $kProgress['filled_fields'] }}/{{ $kProgress['total_fields'] }}
                        </span>

                        <small class="text-white">{{ $kProgress['percentage'] }}%</small>
                        <button type="button" class="btn btn-sm btn-light" onclick="toggleKriteriaAccordion({{ $kriteria->id }})" title="Expand/Collapse Semua Pernyataan Standar">
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
                        $eProgress = $progressData['elemen_progress'][$elemen->id] ?? [
                        'total' => 1,
                        'filled' => 0,
                        'percentage' => 0,
                        'is_complete' => false
                        ];

                        $hasData = $eProgress['is_complete'];
                        $elemenBadgeClass = $hasData ? 'bg-success' : 'bg-warning text-dark';
                        @endphp

                        <div class="card mb-3 elemen-card @if($hasData) has-data @endif" data-elemen-id="{{ $elemen->id }}" data-initial-filled="{{ $eProgress['filled'] }}" data-initial-total="{{ $eProgress['total'] }}">
                            {{-- Elemen Header --}}
                            <div class="card-header elemen-header" id="heading-elemen-{{ $elemen->id }}">
                                <div class="d-md-flex justify-content-between align-items-center">
                                    <button class="btn btn-link elemen-btn collapsed d-flex flex-column flex-md-row align-items-start align-items-md-center w-100 gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-elemen-{{ $elemen->id }}" aria-expanded="false" aria-controls="collapse-elemen-{{ $elemen->id }}">
                                        <div>
                                            <span class="badge bg-primary me-2">{{ $elemen->kode_elemen }}</span>
                                            <strong>{{ $elemen->pernyataan_elemen }}</strong>
                                        </div>
                                        <div class="ms-auto d-flex align-items-center gap-2">
                                            <small class="text-muted elemen-progress-text">
                                                {{ $eProgress['filled'] }}/{{ $eProgress['total'] }}
                                            </small>
                                            <span class="badge {{ $elemenBadgeClass }} elemen-status-badge">
                                                @if($hasData)
                                                <i class="bi bi-check-circle"></i> Lengkap
                                                @else
                                                <i class="bi bi-clock"></i> {{ $eProgress['percentage'] }}%
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
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-pencil-square"></i> Deskripsi/Narasi Elemen
                                        </label>
                                        <textarea class="form-control auto-save-field" name="desc_{{ $elemen->id }}" data-field-id="desc_{{ $elemen->id }}" data-field-type="description" rows="6" placeholder="Tuliskan deskripsi/narasi untuk {{ $elemen->kode_elemen }} di sini...">{{ $existingData["desc_{$elemen->id}"] ?? '' }}</textarea>
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i>
                                            <span class="char-count">{{ strlen($existingData["desc_{$elemen->id}"] ?? '') }}</span> karakter
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

                                    {{-- Dataset Fields --}}
                                    @if($elemen->datasetBorang->count() > 0)
                                    <div class="border-top pt-3">
                                        <h6 class="mb-3">
                                            <i class="bi bi-table"></i> Data Pendukung
                                        </h6>

                                        @foreach($elemen->datasetBorang as $dataset)
                                        <div class="dataset-field-wrapper">
                                            <label class="form-label fw-semibold">
                                                {{ $dataset->nama }}
                                                @if($dataset->is_required)
                                                <span class="text-danger">*</span>
                                                @endif
                                            </label>

                                            @if($dataset->deskripsi)
                                            <small class="d-block text-muted mb-2">{{ $dataset->deskripsi }}</small>
                                            @endif

                                            @if($dataset->tipe_field === 'table')
                                            {{-- CKEditor for Table --}}
                                            {{-- <div class="wysiwyg-editor" data-dataset-id="{{ $dataset->kode }}" data-field-type="table" data-template="{{ $dataset->expected_columns ? json_encode($dataset->expected_columns) : '' }}">
                                            {!! $existingData[$dataset->kode] ?? $dataset->template_html ?? '' !!}
                                        </div> --}}
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
                                        {{-- Simple Textarea --}}
                                        <textarea class="form-control auto-save-field" name="{{ $dataset->kode }}" data-field-id="{{ $dataset->kode }}" data-field-type="textarea" rows="4" placeholder="{{ $dataset->placeholder }}" @if($dataset->is_required) required @endif>{{ $existingData[$dataset->kode] ?? '' }}</textarea>

                                        <div class="save-status text-muted mt-1">
                                            <i class="bi bi-cloud-check"></i>
                                            <span class="status-text">Belum ada perubahan</span>
                                        </div>

                                        @elseif($dataset->tipe_field === 'number')
                                        <input type="number" class="form-control auto-save-field" name="{{ $dataset->kode }}" data-field-id="{{ $dataset->kode }}" data-field-type="number" placeholder="{{ $dataset->placeholder }}" value="{{ $existingData[$dataset->kode] ?? '' }}" @if($dataset->is_required) required @endif>

                                        <div class="save-status text-muted mt-1">
                                            <i class="bi bi-cloud-check"></i>
                                            <span class="status-text">Belum ada perubahan</span>
                                        </div>

                                        @elseif($dataset->tipe_field === 'date')
                                        <input type="date" class="form-control auto-save-field" name="{{ $dataset->kode }}" data-field-id="{{ $dataset->kode }}" data-field-type="date" value="{{ $existingData[$dataset->kode] ?? '' }}" @if($dataset->is_required) required @endif>

                                        <div class="save-status text-muted mt-1">
                                            <i class="bi bi-cloud-check"></i>
                                            <span class="status-text">Belum ada perubahan</span>
                                        </div>

                                        @elseif($dataset->tipe_field === 'file')
                                        <input type="file" class="form-control file-upload-field" name="{{ $dataset->kode }}" data-field-id="{{ $dataset->kode }}" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                        @if(isset($existingData[$dataset->kode]))
                                        <small class="text-success">
                                            <i class="bi bi-check-circle"></i> File tersimpan
                                        </small>
                                        @endif

                                        @else
                                        <input type="text" class="form-control auto-save-field @error($dataset->kode) is-invalid @enderror" name="{{ $dataset->kode }}" data-field-id="{{ $dataset->kode }}" data-field-type="text" placeholder="{{ $dataset->placeholder }}" value="{{ $existingData[$dataset->kode] ?? '' }}" @if($dataset->is_required) required @endif>

                                        <div class="save-status text-muted mt-1">
                                            <i class="bi bi-cloud-check"></i>
                                            <span class="status-text">Belum ada perubahan</span>
                                        </div>
                                        @endif

                                        @if($dataset->keterangan)
                                        <small class="text-muted d-block mt-1">
                                            <i class="bi bi-lightbulb"></i> {{ $dataset->keterangan }}
                                        </small>
                                        @endif
                                        @error($dataset->kode)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
                        <input type="file" class="form-control @error('docx_file') is-invalid @enderror" name="docx_file" accept=".docx,.doc" required>
                        <small class="text-muted">
                            Format: .docx atau .doc (Max 10MB)
                        </small>
                        @error('docx_file')
                        <span class="invalid-feedback" role="alert">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Perhatian:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Data yang ada akan ditimpa dengan data dari file</li>
                            <li>Pastikan format file sesuai template</li>
                            <li>Proses upload dan pembacaan data memerlukan waktu beberapa saat</li>
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

{{-- Loading Overlay --}}
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
@endsection

@push('scripts')
{{-- CKEditor 5 --}}
<script src="https://cdn.jsdelivr.net/npm/tinymce@8.3.1/tinymce.min.js"></script>
{{-- <script src="{{ asset('assets/js/ckeditor5.umd.js') }}"></script> --}}

<script>
    //const { ClassicEditor , Essentials , Bold , Italic , Font , Paragraph , Table , TableToolbar , Heading , List , Link , Alignment , Underline , Strikethrough , Indent , IndentBlock } = CKEDITOR;
    //const licenseKeyCKEDITOR = 'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3Njc3NDM5OTksImp0aSI6ImViNWYxM2ZjLWViMmEtNDNhZi05OGE2LTI1YjBmMTY4N2RhMyIsInVzYWdlRW5kcG9pbnQiOiJodHRwczovL3Byb3h5LWV2ZW50LmNrZWRpdG9yLmNvbSIsImRpc3RyaWJ1dGlvbkNoYW5uZWwiOlsiY2xvdWQiLCJkcnVwYWwiLCJzaCJdLCJ3aGl0ZUxhYmVsIjp0cnVlLCJsaWNlbnNlVHlwZSI6InRyaWFsIiwiZmVhdHVyZXMiOlsiKiJdLCJ2YyI6ImM2MjE0NTY3In0.rTMaN-qZUvY2yczKBRALkpIws_B92_gsnIJtG2pQvOikQvsYE8JoFXqwA_WxymhPtO7kjMQOV0s8FILQ8W1syA'

    document.addEventListener('DOMContentLoaded', function() {
        const pengajuanId = "{{ $pengajuan->id }}";

        // ✅ Load server-side progress
        const initialProgress = @json($progressData);
        const totalFields = initialProgress.total_fields;

        let saveTimeout;
        let importStatusInterval = null;
        const AUTO_SAVE_DELAY = 2000;
        const editorInstances = {};

        // Initialize
        initializeAutoSave();
        initializeWYSIWYGEditors();
        initializeFileUpload();
        initializeToggleButton();
        initializeFinalize();
        initializeResetBorang();
        initializeImportExport();

        function initializeWYSIWYGEditors() {
            const editorElements = document.querySelectorAll('textarea.tinymce-editor');

            editorElements.forEach((el) => {
                const datasetId = el.dataset.datasetId;

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
                    , content_style: `
        table { border-collapse: collapse; width: 100%; }
        td, th { border: 1px solid #ddd; padding: 8px; }
        th { background: #f2f2f2; font-weight: bold; }
      `
                    , setup: function(editor) {
                        editor.on('init', function() {
                            // Jika kosong / terlalu pendek, isi template dari expected_columns
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
                                        console.error('Template JSON parse error:', datasetId, e);
                                    }
                                }
                            }

                            // simpan instance
                            editorInstances[datasetId] = editor;
                        });
                    }
                });
            });

            setTimeout(updateProgress, 1000);
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

        /**
         * ✅ Save editor button handler
         */
        document.querySelectorAll('.btn-save-editor').forEach(btn => {
            btn.addEventListener('click', async function() {
                const datasetId = this.dataset.datasetId;
                const editor = editorInstances[datasetId];

                if (!editor) {
                    Swal.fire('Error', 'Editor belum siap', 'error');
                    return;
                }

                //const content = editor.getData(); // CKEditor 5
                const content = editor.getContent(); // TinyMCE
                const wrapper = this.closest('.dataset-field-wrapper');
                const statusElement = wrapper.querySelector('.status-text');

                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

                try {
                    await autoSaveField(datasetId, content, statusElement);
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-check-circle"></i> Tersimpan';
                    setTimeout(() => {
                        this.innerHTML = '<i class="bi bi-save"></i> Simpan';
                    }, 2000);
                } catch (error) {
                    console.log(error)
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-save"></i> Simpan';
                    Swal.fire('Error', 'Gagal menyimpan: ' + error.message, 'error');
                }
            });
        });

        /**
         * ✅ Auto-save field
         */
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

        /**
         * Auto-save text fields
         */
        function initializeAutoSave() {
            document.querySelectorAll('.auto-save-field').forEach(field => {
                field.addEventListener('input', function() {
                    clearTimeout(saveTimeout);
                    const wrapper = field.closest('.mb-3, .mb-4, .dataset-field-wrapper');
                    const statusElement = wrapper ? wrapper.querySelector('.status-text') : null;

                    if (statusElement) {
                        statusElement.textContent = 'Menyimpan...';
                        statusElement.className = 'status-text text-warning';
                    }

                    saveTimeout = setTimeout(() => saveField(this), AUTO_SAVE_DELAY);
                });

                // Character count
                if (field.tagName === 'TEXTAREA') {
                    field.addEventListener('input', function() {
                        const wrapper = this.closest('.mb-3, .mb-4');
                        const charCount = wrapper ? wrapper.querySelector('.char-count') : null;
                        if (charCount) {
                            charCount.textContent = this.value.length;
                        }
                    });
                }
            });
        }

        async function saveField(field) {
            const fieldId = field.dataset.fieldId;
            const value = field.value;
            const wrapper = field.closest('.mb-3, .mb-4, .dataset-field-wrapper');
            const statusElement = wrapper ? wrapper.querySelector('.status-text') : null;

            await autoSaveField(fieldId, value, statusElement);
        }

        /**
         * File upload
         */
        function initializeFileUpload() {
            document.querySelectorAll('.file-upload-field').forEach(input => {
                input.addEventListener('change', async function() {
                    const file = this.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('dataset_id', this.dataset.fieldId);

                    showLoading();

                    try {
                        const response = await fetch(`/pengajuan/${pengajuanId}/borang-online/upload`, {
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
                            Swal.fire({
                                icon: 'success'
                                , title: 'File Terupload!'
                                , text: data.message
                                , timer: 2000
                                , showConfirmButton: false
                            });
                            updateProgress();
                        } else {
                            throw new Error(data.message || 'Upload gagal');
                        }

                    } catch (error) {
                        hideLoading();
                        Swal.fire('Error', error.message, 'error');
                    }
                });
            });
        }

        /**
         * ✅ Import/Export handlers
         */
        function initializeImportExport() {
            document.getElementById('btnImportDocx').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('modalImportDocx'));
                modal.show();
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
                        showImportProgress(data.import_id);
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

        function showImportProgress(importId) {
            Swal.fire({
                title: 'Mengupload Borang...'
                , html: `
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             id="importProgressBar" role="progressbar" style="width: 0%">
                            <span id="importProgressText">0%</span>
                        </div>
                    </div>
                    <p><strong>Sections:</strong> <span id="importSections">0 / 0</span></p>
                    <p><strong>Tables:</strong> <span id="importTables">0 / 0</span></p>
                    <small class="text-muted">Proses upload dan pembacaan data...</small>
                `
                , allowOutsideClick: false
                , showConfirmButton: false
                , didOpen: () => {
                    importStatusInterval = setInterval(() => checkImportStatus(importId), 2000);
                }
            });
        }

        async function checkImportStatus(importId) {
            try {
                const response = await fetch('/pengajuan/' + pengajuanId + '/borang/import-status/' + importId, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    const progress = data.progress;
                    const percentage = Math.round((progress.tables.percentage + progress.sections.percentage) / 2);

                    document.getElementById('importProgressBar').style.width = percentage + '%';
                    document.getElementById('importProgressText').textContent = percentage + '%';
                    document.getElementById('importSections').textContent =
                        progress.sections.parsed + ' / ' + progress.sections.total;
                    document.getElementById('importTables').textContent =
                        progress.tables.parsed + ' / ' + progress.tables.total;

                    if (data.status === 'completed') {
                        clearInterval(importStatusInterval);
                        Swal.fire({
                            icon: 'success'
                            , title: 'Proses Upload Berhasil!'
                            , html: `
                                <p><strong>${progress.sections.parsed} elemen</strong> berhasil diupload</p>
                                <p><strong>${progress.tables.parsed} tabel</strong> data terisi</p>
                                <p class="text-muted mt-2">Halaman akan dimuat ulang...</p>
                            `
                            , timer: 3000
                            , showConfirmButton: false
                            , allowOutsideClick: false
                        }).then(() => {
                            window.location.href = window.location.href + '?refreshed=' + Date.now();
                        });

                    } else if (data.status === 'failed') {
                        clearInterval(importStatusInterval);
                        Swal.fire('Error', 'Import gagal: ' + (data && data.errors && data.errors[0] && data.errors[0].message || 'Unknown'), 'error');
                    }
                }

            } catch (error) {
                clearInterval(importStatusInterval);
                console.error('Check status error:', error);
            }
        }

        /**
         * ✅ Update progress - Client side fallback
         */
        function updateProgress() {
            let filledFieldsCount = 0;
            let totalFieldsCount = 0;
            let completedElemenCount = 0;
            let totalElemenCount = 0;

            // Process each elemen card
            document.querySelectorAll('.elemen-card').forEach(card => {
                totalElemenCount++;

                let elemenFilledCount = 0;
                let elemenTotalCount = 0;

                // ✅ 1. Count DESCRIPTION field (always 1 per elemen)
                const descField = card.querySelector('textarea[data-field-type="description"]');
                if (descField) {
                    elemenTotalCount++;
                    totalFieldsCount++;

                    const descValue = descField.value.trim();
                    if (descValue && descValue.length > 0) {
                        elemenFilledCount++;
                        filledFieldsCount++;
                    }
                }

                // ✅ 2. Count TABLE fields only (match server-side)
                card.querySelectorAll('textarea.tinymce-editor[data-field-type="table"]').forEach(editorEl => {
                    const datasetId = editorEl.dataset.datasetId;
                    elemenTotalCount++;
                    totalFieldsCount++;

                    if (editorInstances[datasetId]) {
                        //const content = editorInstances[datasetId].getData().trim();
                        const content = editorInstances[datasetId].getContent().trim();

                        // Check if has real data (not just template)
                        const hasRealData = content.length > 0 &&
                            !content.includes('&nbsp;</td></tr></tbody></table>') &&
                            !isEmptyTable(content);

                        if (hasRealData) {
                            elemenFilledCount++;
                            filledFieldsCount++;
                        }
                    }
                });

                // Check if elemen is complete
                const isElemenComplete = (elemenFilledCount === elemenTotalCount && elemenTotalCount > 0);
                if (isElemenComplete) {
                    completedElemenCount++;
                    card.classList.add('has-data');
                } else {
                    card.classList.remove('has-data');
                }

                // Update elemen badge
                updateElemenBadge(card, elemenFilledCount, elemenTotalCount, isElemenComplete);
            });

            // Calculate percentages
            const fieldPercentage = totalFieldsCount > 0 ? Math.round((filledFieldsCount / totalFieldsCount) * 100) : 0;
            const elemenPercentage = totalElemenCount > 0 ? Math.round((completedElemenCount / totalElemenCount) * 100) : 0;

            // Update UI
            updateProgressUI(filledFieldsCount, totalFieldsCount, fieldPercentage, completedElemenCount, totalElemenCount, elemenPercentage);

            // Update kriteria progress
            updateKriteriaProgress();
        }

        /**
         * ✅ Check if table is empty (only has template)
         */
        function isEmptyTable(htmlContent) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlContent, 'text/html');
            const cells = doc.querySelectorAll('td');

            let nonEmptyCells = 0;
            let totalCells = 0;

            cells.forEach(cell => {
                totalCells++;

                let text = cell.textContent.trim();
                text = text.replace(/\s+/g, '');
                text = text.replace(/\u00A0/g, ''); // Remove &nbsp;

                // Skip if it's just a number (row numbering: 1, 2, 3)
                if (/^\d+$/.test(text) && parseInt(text) <= 10) {
                    return;
                }

                if (text && text !== '') {
                    nonEmptyCells++;
                }
            });

            // ✅ Need at least 3 cells with real data
            return nonEmptyCells < 3;
        }

        /**
         * ✅ Update elemen badge
         */
        function updateElemenBadge(card, filled, total, isComplete) {
            const progressText = card.querySelector('.elemen-progress-text');
            const statusBadge = card.querySelector('.elemen-status-badge');

            if (progressText) {
                progressText.textContent = `${filled}/${total}`;
            }

            if (statusBadge) {
                if (isComplete) {
                    statusBadge.className = 'badge bg-success elemen-status-badge';
                    statusBadge.innerHTML = '<i class="bi bi-check-circle"></i> Lengkap';
                } else {
                    const percentage = total > 0 ? Math.round((filled / total) * 100) : 0;
                    statusBadge.className = 'badge bg-warning text-dark elemen-status-badge';
                    statusBadge.innerHTML = `<i class="bi bi-clock"></i> ${percentage}%`;
                }
            }
        }

        /**
         * ✅ Update progress UI
         */
        function updateProgressUI(filledFields, totalFields, fieldPercentage, completedElemen, totalElemen, elemenPercentage) {
            // Field Progress Bar
            document.getElementById('progressBarFields').style.width = fieldPercentage + '%';
            document.getElementById('progressPercentageFields').textContent = fieldPercentage + '%';
            document.getElementById('progressCompletedFields').textContent = filledFields;
            document.getElementById('progressRemainingFields').textContent = totalFields - filledFields;
            document.getElementById('progressTotalFields').textContent = totalFields;
            document.getElementById('progressCountFields').textContent = filledFields + '/' + totalFields + ' Bagian';

            // Elemen Progress Bar
            document.getElementById('progressBarElemen').style.width = elemenPercentage + '%';
            document.getElementById('progressPercentageElemen').textContent = elemenPercentage + '%';
            document.getElementById('progressCountElemen').textContent = completedElemen + '/' + totalElemen;

            // Update field progress bar color
            const progressBarFields = document.getElementById('progressBarFields');
            if (fieldPercentage === 100) {
                progressBarFields.className = 'progress-bar bg-success';
            } else if (fieldPercentage >= 75) {
                progressBarFields.className = 'progress-bar bg-info';
            } else if (fieldPercentage >= 50) {
                progressBarFields.className = 'progress-bar bg-warning';
            } else {
                progressBarFields.className = 'progress-bar bg-danger';
            }
        }

        /**
         * ✅ Update kriteria progress - Match server logic
         */
        function updateKriteriaProgress() {
            document.querySelectorAll('.kriteria-card').forEach(kriteriaCard => {
                const elemenCards = kriteriaCard.querySelectorAll('.elemen-card');

                let totalFields = 0;
                let filledFields = 0;
                let totalElemen = elemenCards.length;
                let completedElemen = 0;

                elemenCards.forEach(card => {
                    let elemenFilled = 0;
                    let elemenTotal = 0;
                    let elemenTotalCount = 0;
                    let totalFieldsCount = 0;

                    // Count description
                    const descField = card.querySelector('textarea[data-field-type="description"]');
                    if (descField) {
                        elemenTotal++;
                        const descValue = descField.value.trim();
                        if (descValue && descValue.length > 20) {
                            elemenFilled++;
                        }
                    }

                    // Count table fields only
                    card.querySelectorAll('textarea.tinymce-editor[data-field-type="table"]').forEach(editorEl => {
                        const datasetId = editorEl.dataset.datasetId;
                        elemenTotalCount++;
                        totalFieldsCount++;

                        const editor = editorInstances[datasetId];
                        if (editor) {
                            const content = editor.getContent({
                                format: 'html'
                            }).trim();
                            const hasRealData = content.length > 0 && !isEmptyTable(content);
                            if (hasRealData) {
                                elemenFilled++;
                                filledFields++;
                            }
                        }
                    });

                    totalFields += elemenTotal;
                    filledFields += elemenFilled;

                    // Check if elemen complete
                    if (elemenFilled === elemenTotal && elemenTotal > 0) {
                        completedElemen++;
                    }
                });

                // Update elemen badge
                const elemenBadge = kriteriaCard.querySelector('.kriteria-progress-elemen');
                if (elemenBadge) {
                    elemenBadge.innerHTML = `<i class="bi bi-check-square"></i> ${completedElemen}/${totalElemen}`;
                    elemenBadge.className = completedElemen === totalElemen && totalElemen > 0 ?
                        'badge bg-success kriteria-progress-elemen' :
                        'badge bg-info kriteria-progress-elemen';
                }

                // Update fields badge
                const fieldsBadge = kriteriaCard.querySelector('.kriteria-progress-fields');
                if (fieldsBadge) {
                    fieldsBadge.innerHTML = `<i class="bi bi-list-check"></i> ${filledFields}/${totalFields}`;

                    const percentage = totalFields > 0 ? Math.round((filledFields / totalFields) * 100) : 0;
                    if (percentage === 100) {
                        fieldsBadge.className = 'badge bg-success kriteria-progress-fields';
                    } else if (percentage >= 50) {
                        fieldsBadge.className = 'badge bg-warning kriteria-progress-fields';
                    } else {
                        fieldsBadge.className = 'badge bg-secondary kriteria-progress-fields';
                    }
                }
            });
        }

        function initializeToggleButton() {
            const btnToggle = document.getElementById('btnToggleAll');
            let isExpanded = false;

            btnToggle.addEventListener('click', function() {
                document.querySelectorAll('.accordion-collapse').forEach(accordion => {
                    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(accordion, {
                        toggle: false
                    });
                    isExpanded ? bsCollapse.hide() : bsCollapse.show();
                });

                isExpanded = !isExpanded;
                this.innerHTML = isExpanded ?
                    '<i class="bi bi-arrows-collapse"></i> Collapse All' :
                    '<i class="bi bi-arrows-expand"></i> Expand All';
            });
        }

        /**
         * ✅ Initialize reset borang
         */
        function initializeResetBorang() {
            const btnReset = document.getElementById('btnResetBorang');
            if (!btnReset) return;

            btnReset.addEventListener('click', async function() {
                try {
                    // Get stats first
                    const statsResponse = await fetch(`/pengajuan/${pengajuanId}/borang/stats`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const statsData = await statsResponse.json();

                    if (!statsData.success) {
                        throw new Error(statsData.message);
                    }

                    if (!statsData.stats.can_reset) {
                        Swal.fire({
                            icon: 'error'
                            , title: 'Tidak Dapat Reset'
                            , text: 'Status pengajuan tidak memungkinkan untuk reset borang.'
                        });
                        return;
                    }

                    // Show confirmation with stats
                    const result = await Swal.fire({
                        icon: 'warning'
                        , title: 'Reset Borang?'
                        , html: `
                    <div class="text-start">
                        <p class="mb-3"><strong>PERHATIAN!</strong> Tindakan ini akan menghapus:</p>
                        <ul class="text-danger">
                            <li><strong>${statsData.stats.total_data}</strong> data yang sudah diisi</li>
                            <li><strong>${statsData.stats.total_imports}</strong> riwayat import</li>
                            ${statsData.stats.draft_borang ? `<li>Draft LED: ${statsData.stats.draft_borang.filename}</li>` : ''}
                        </ul>
                        <p class="mt-3 text-muted">Anda harus mengisi ulang dari awal.</p>
                        <p class="mt-3"><strong>Ketik "RESET" untuk konfirmasi:</strong></p>
                    </div>
                `
                        , input: 'text'
                        , inputPlaceholder: 'Ketik RESET'
                        , showCancelButton: true
                        , confirmButtonColor: '#dc3545'
                        , cancelButtonColor: '#6c757d'
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

                    // Perform reset
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
                            , html: `
                        <p>Berhasil menghapus:</p>
                        <ul>
                            <li>${data.data.deleted_data} data entries</li>
                            <li>${data.data.deleted_imports} import records</li>
                        </ul>
                        <p class="text-muted mt-2">Halaman akan dimuat ulang...</p>
                    `
                            , timer: 3000
                            , showConfirmButton: false
                        });

                        // Reload page
                        window.location.href = window.location.href + '?reset=1&t=' + Date.now();
                    } else {
                        throw new Error(data.message || 'Reset gagal');
                    }

                } catch (error) {
                    hideLoading();
                    Swal.fire({
                        icon: 'error'
                        , title: 'Error'
                        , text: error.message || 'Terjadi kesalahan saat reset borang'
                    });
                }
            });
        }

        function initializeFinalize() {
            document.getElementById('btnFinalize').addEventListener('click', async function() {
                // Count filled
                let filledCount = 0;
                document.querySelectorAll('.auto-save-field').forEach(field => {
                    if (field.value.trim() !== '') filledCount++;
                });
                Object.keys(editorInstances).forEach(id => {
                    const content = editorInstances[id].getContent({
                        format: 'html'
                    }).trim();
                    if (content !== '' && !isEmptyTable(content)) filledCount++;
                });

                if (filledCount < totalFields) {
                    Swal.fire({
                        icon: 'warning'
                        , title: 'Borang Belum Lengkap'
                        , html: `<p>Anda baru mengisi <strong>${filledCount} dari ${totalFields}</strong> bagian.</p>`
                        , confirmButtonColor: '#932136'
                    });
                    return;
                }

                const confirmed = await Swal.fire({
                    icon: 'question'
                    , title: 'Konfirmasi Finalisasi'
                    , html: '<p><strong>Submit lembar evaluasi diri?</strong></p>'
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

        /**
         * Toggle Kriteria Accordion
         */
        window.toggleKriteriaAccordion = function(kriteriaId, btn) {
            const kriteriaCollapse = document.getElementById(`collapse-kriteria-${kriteriaId}`);
            if (!kriteriaCollapse) return;

            const kriteriaInstance = bootstrap.Collapse.getOrCreateInstance(
                kriteriaCollapse, {
                    toggle: false
                }
            );

            const isOpen = kriteriaCollapse.classList.contains('show');

            // 1️⃣ Toggle kriteria
            isOpen ? kriteriaInstance.hide() : kriteriaInstance.show();

            // 2️⃣ Toggle semua elemen di dalam kriteria
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
    });

</script>
@endpush
