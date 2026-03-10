{{-- resources/views/upps/penerimaan-dokumen/upload.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Kirim Dokumen')

@push('styles')
<style>
    .upload-area {
        border: 2px dashed #ced4da;
        border-radius: 8px;
        padding: 22px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: #fff;
    }

    .upload-area.has-file {
        border-color: #28a745;
    }

    .file-preview-card.has-file {
        border-color: #28a745;
    }

    .upload-area:hover {
        border-color: #0d6efd;
        background: #f8f9fa;
    }

    .upload-area.dragover {
        border-color: #28a745;
        background: #d4edda;
    }

    .file-preview-card {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 14px;
    }

    .file-preview-card.has-file {
        border-color: #28a745;
        background: #d4edda;
    }

    .file-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .file-meta .left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 200px;
    }

    /* Loading overlay */
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
        flex-direction: column;
        gap: 16px;
    }

    .loading-overlay.show {
        display: flex;
    }

    .loading-overlay .loading-text {
        color: #fff;
        font-size: 1rem;
        font-weight: 500;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penerimaan-dokumen') }}">Pengiriman Dokumen</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penerimaan-dokumen.show', $pengajuan->id) }}">Detail</a></li>
            <li class="breadcrumb-item active">Kirim Dokumen</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-upload"></i> Pengiriman Dokumen Akreditasi
            </h5>
            <small class="text-muted">{{ $canUploadDokumen ? 'Silahkan lakukan pengiriman dokumen akreditasi' : 'Lihat dokumen akreditasi yang telah Anda kirim' }}</small>
        </div>
        <a href="{{ route('upps.penerimaan-dokumen.show', $pengajuan->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Upload Form Alert -->
            @if(!$canUploadDokumen)
            <div class="alert alert-info alert-permanent mb-4">
                <h5 class="mb-1">
                    <i class="bi bi-eye"></i> Mode <i>Preview</i> Dokumen
                </h5>
                <p class="mb-0">
                    Status permohonan saat ini hanya memungkinkan <strong><i>preview</i> dokumen</strong>.<br>
                    Upload dokumen akan tersedia ketika status sudah sesuai.
                </p>
                <div class="small text-muted mt-1">
                    Status saat ini: {!! $pengajuan->getCustomBadgeLastStatus('draft_borang', 'upps') !!}
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-files"></i> Dokumen yang Telah Dikirim
                    </h5>
                </div>

                <div class="card-body">
                    @php
                    $docCards = [
                    'led' => ['label' => 'Laporan Evaluasi Diri (LED)', 'icon' => 'file-word', 'color' => 'info'],
                    'suplemen' => ['label' => 'Suplemen LED', 'icon' => 'file-earmark-pdf', 'color' => 'warning'],
                    'lkps' => ['label' => 'Laporan Kinerja Program Studi (LKPS)', 'icon' => 'file-excel', 'color' => 'success'],
                    'pengesahan' => ['label' => 'Lembar Pengesahan Dokumen', 'icon' => 'file-earmark-pdf', 'color' => 'danger'],
                    ];
                    @endphp

                    <div class="row">
                        @foreach($docCards as $key => $cfg)
                        @php $doc = isset($uploadedDocuments[$key]) ? $uploadedDocuments[$key] : null; @endphp
                        <div class="col-md-6 mb-3">
                            <div class="card {{ $doc ? 'border-success' : 'border-danger' }}">
                                <div class="card-body d-flex gap-3">
                                    <div>
                                        <i class="bi bi-{{ $cfg['icon'] }} text-{{ $cfg['color'] }}" style="font-size:34px;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">{{ $cfg['label'] }}</div>
                                        @if($doc)
                                        <small class="text-muted text-wrap mt-1">
                                            {{ isset($doc->original_filename) ? $doc->original_filename : '-' }}<br>
                                            @if($doc->created_at) {{ $doc->created_at->locale('id')->translatedFormat('d M Y H:i') }}<br> @endif
                                        </small>
                                        @if($doc->path_file || $doc->template_link)
                                        <a href="{{ $doc->download_url }}" class="btn btn-sm btn-success mt-2" target="_blank">
                                            <i class="bi bi-eye"></i> Lihat File
                                        </a>
                                        @endif
                                        @else
                                        <span class="badge {{ $needSuplemen ? 'bg-danger' : 'bg-secondary' }} mt-2">Belum Diupload</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED)
            <div class="alert alert-warning alert-permanent">
                <h5><i class="bi bi-exclamation-triangle"></i> Permintaan Revisi Dokumen</h5>
                <p class="mb-0">
                    Validator meminta revisi pada dokumen yang telah diupload. Silakan perbaiki dokumen sesuai catatan validator dan upload ulang.
                </p>
            </div>
            @endif

            <!-- Upload Form -->
            @if($canUploadDokumen)
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cloud-upload"></i> Form Upload Dokumen
                    </h5>
                </div>

                <div class="card-body">
                    {{--
                        Form ini TIDAK di-submit biasa.
                        JS akan:
                          1. Upload LED  → POST /permohonan-akreditasi/{id}/borang/import-docx  → poll status
                          2. Upload LKPS → POST /permohonan-akreditasi/{id}/borang/import-lkps  → poll status
                          3. Upload Suplemen & Pengesahan → POST route upps.penerimaan-dokumen.upload
                    --}}
                    <form id="formUploadDokumen" enctype="multipart/form-data">
                        @csrf

                        {{-- ===================== LED ===================== --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Laporan Evaluasi Diri (LED) <span class="text-danger">*</span>
                            </label>
                            <small class="d-block text-muted mb-2">
                                <i class="bi bi-info-circle"></i>
                                File DOCX akan dibaca otomatis ke elemen penilaian.
                            </small>

                            <div class="upload-area {{ $uploadedDocuments['led'] ? 'dragover border-success bg-light' : '' }}" id="uploadAreaLed">
                                <i class="bi bi-file-word text-info" style="font-size: 44px;"></i>
                                <p class="mb-1"><strong>Silahkan upload file LED di sini</strong></p>
                                <p class="text-muted small mb-2">Format: DOCX/DOC • Maksimal 10MB</p>
                                <input type="file" id="file_led" name="file_led" class="visually-hidden-input" accept=".docx,.doc">
                                <button type="button" class="btn btn-outline-info btn-sm" id="btnPickLed">
                                    <i class="bi bi-folder2-open"></i> Pilih File LED
                                </button>
                            </div>
                            <div id="previewLed" class="file-preview-card {{ $uploadedDocuments['led'] ? 'has-file' : 'd-none' }} mt-2">
                                <div class="file-meta">
                                    <div class="left">
                                        <i class="bi bi-file-word text-info" style="font-size: 26px;"></i>
                                        <div>
                                            <div class="fw-bold" id="ledName">
                                                {{ $uploadedDocuments['led']->original_filename ?? '-' }}
                                            </div>
                                            <div class="text-muted small" id="ledSize">
                                                @if($uploadedDocuments['led'])
                                                Uploaded {{ $uploadedDocuments['led']->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="right d-flex gap-2">
                                        @if($uploadedDocuments['led'])
                                        <a href="{{ $uploadedDocuments['led']->download_url }}" class="btn btn-sm btn-outline-success" target="_blank">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveLed">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnChangeLed">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===================== SUPLEMEN ===================== --}}
                        <div class="mb-4">
                            @php
                            $suplemenDoc = $uploadedDocuments['suplemen'] ?? null;
                            @endphp
                            <label class="form-label fw-semibold">
                                Suplemen LED
                                @if($needSuplemen)<span class="text-danger">*</span>@endif
                            </label>
                            <div class="upload-area {{ $suplemenDoc ? 'has-file' : '' }}" id="uploadAreaSuplemen">
                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size:44px;"></i>
                                @if($suplemenDoc)
                                <p class="mb-1 text-success"><strong>File suplemen sudah diupload</strong></p>
                                <p class="text-muted small mb-2">
                                    {{ $suplemenDoc->original_filename }}
                                </p>
                                @else
                                <p class="mb-1"><strong>Silahkan upload file Suplemen di sini</strong></p>
                                <p class="text-muted small mb-2">Format: PDF • Maksimal 10MB</p>
                                @endif
                                <input type="file" id="file_suplemen" name="file_suplemen" class="visually-hidden-input" accept=".pdf">
                                <button type="button" class="btn btn-outline-success btn-sm" id="btnPickSuplemen">
                                    <i class="bi bi-folder2-open"></i>
                                    {{ $suplemenDoc ? 'Ganti File Suplemen' : 'Pilih File Suplemen' }}
                                </button>
                            </div>

                            <div id="previewSuplemen" class="file-preview-card {{ $suplemenDoc ? 'has-file' : 'd-none' }} mt-2">
                                <div class="file-meta">
                                    <div class="left">
                                        <i class="bi bi-file-earmark-pdf text-success" style="font-size:26px;"></i>
                                        <div>
                                            <div class="fw-bold" id="suplemenName">
                                                {{ $suplemenDoc->original_filename ?? '-' }}
                                            </div>
                                            <div class="text-muted small" id="suplemenSize">
                                                @if($suplemenDoc)
                                                Uploaded {{ $suplemenDoc->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="right d-flex gap-2">
                                        @if($suplemenDoc)
                                        <a href="{{ $suplemenDoc->download_url }}" target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveSuplemen">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnChangeSuplemen">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===================== LKPS ===================== --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Laporan Kinerja Program Studi (LKPS) <span class="text-danger">*</span>
                            </label>
                            <small class="d-block text-muted mb-2">
                                <i class="bi bi-info-circle"></i>
                                File Excel akan dibaca otomatis per sheet/tabel.
                            </small>

                            <div class="upload-area {{ $uploadedDocuments['lkps'] ? 'has-file' : '' }}" id="uploadAreaLkps">
                                <i class="bi bi-file-excel text-success" style="font-size: 44px;"></i>
                                <p class="mb-1"><strong>Silahkan upload file LKPS di sini</strong></p>
                                <p class="text-muted small mb-2">Format: XLSX/XLS • Maksimal 10MB</p>
                                <input type="file" id="file_lkps" name="file_lkps" class="visually-hidden-input" accept=".xlsx,.xls">
                                <button type="button" class="btn btn-outline-success btn-sm" id="btnPickLkps">
                                    <i class="bi bi-folder2-open"></i> Pilih File LKPS
                                </button>
                            </div>

                            <div id="previewLkps" class="file-preview-card {{ $uploadedDocuments['lkps'] ? 'has-file' : 'd-none' }} mt-2">
                                <div class="file-meta">
                                    <div class="left">
                                        <i class="bi bi-file-excel text-success" style="font-size: 26px;"></i>
                                        <div>
                                            <div class="fw-bold" id="lkpsName">
                                                {{ $uploadedDocuments['lkps']->original_filename ?? '-' }}
                                            </div>
                                            <div class="text-muted small">
                                                @if($uploadedDocuments['lkps'])
                                                Uploaded {{ $uploadedDocuments['lkps']->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="right d-flex gap-2">
                                        @if($uploadedDocuments['lkps'])
                                        <a href="{{ $uploadedDocuments['lkps']->download_url }}" class="btn btn-sm btn-outline-success" target="_blank">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveLkps">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnChangeLkps">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===================== LEMBAR PENGESAHAN ===================== --}}
                        <div class="mb-4">
                            @php
                            $pengesahanDoc = $uploadedDocuments['pengesahan'] ?? null;
                            @endphp
                            <label class="form-label fw-semibold">
                                Lembar Pengesahan Dokumen <span class="text-danger">*</span>
                            </label>
                            <div class="upload-area {{ $pengesahanDoc ? 'has-file' : '' }}" id="uploadAreaPengesahan">
                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size:44px;"></i>
                                @if($pengesahanDoc)
                                <p class="mb-1 text-success"><strong>File pengesahan sudah diupload</strong></p>
                                <p class="text-muted small mb-2">
                                    {{ $pengesahanDoc->original_filename }}
                                </p>
                                @else
                                <p class="mb-1"><strong>Silahkan upload file Lembar Pengesahan di sini</strong></p>
                                <p class="text-muted small mb-2">Format: PDF • Maksimal 10MB</p>
                                @endif
                                <input type="file" id="file_pengesahan" name="file_pengesahan" class="visually-hidden-input" accept=".pdf">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnPickPengesahan">
                                    <i class="bi bi-folder2-open"></i>
                                    {{ $pengesahanDoc ? 'Ganti File Pengesahan' : 'Pilih File Pengesahan' }}
                                </button>
                            </div>

                            <div id="previewPengesahan" class="file-preview-card {{ $pengesahanDoc ? 'has-file' : 'd-none' }} mt-2">
                                <div class="file-meta">
                                    <div class="left">
                                        <i class="bi bi-file-earmark-pdf text-danger" style="font-size:26px;"></i>
                                        <div>
                                            <div class="fw-bold" id="pengesahanName">
                                                {{ $pengesahanDoc->original_filename ?? '-' }}
                                            </div>
                                            <div class="text-muted small" id="pengesahanSize">
                                                @if($pengesahanDoc)
                                                Uploaded {{ $pengesahanDoc->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="right d-flex gap-2">
                                        @if($pengesahanDoc)
                                        <a href="{{ $pengesahanDoc->download_url }}" target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemovePengesahan">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnChangePengesahan">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Catatan --}}
                        <div class="mb-3">
                            <label for="catatan_upload" class="form-label">Catatan Upload (Opsional)</label>
                            <textarea class="form-control" id="catatan_upload" name="catatan_upload" rows="3" placeholder="Tambahkan catatan jika diperlukan...">{{ old('catatan_upload') }}</textarea>
                        </div>

                        <!-- Important Note -->
                        <div class="alert alert-info alert-permanent">
                            <h6><i class="bi bi-info-circle"></i> Informasi Penting</h6>
                            <ul class="mb-0">
                                <li>Mohon memastikan dokumen yang diupload sudah sesuai templat yang diberikan.</li>
                                <li><strong>LED</strong>: format DOCX/DOC — akan <strong>diproses otomatis</strong> ke elemen penilaian.</li>
                                <li><strong>LKPS</strong>: format XLSX/XLS — akan <strong>diproses otomatis</strong> per sheet/tabel.</li>
                                @if($needSuplemen)
                                <li><strong>Suplemen</strong>: PDF (wajib untuk jenis akreditasi <strong>menuju unggul</strong>).</li>
                                @else
                                <li><strong>Suplemen</strong>: tidak wajib untuk jenis akreditasi ini.</li>
                                @endif
                                <li><strong>Lembar Pengesahan</strong>: format PDF.</li>
                            </ul>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-md" id="btnSubmit" disabled>
                                <i class="bi bi-upload"></i> Upload Dokumen
                            </button>
                            <a href="{{ route('upps.penerimaan-dokumen.show', $pengajuan->id) }}" class="btn btn-secondary btn-md">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Info Permohonan -->
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Permohonan Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="fw-bold">Jenis Dokumen Diperlukan</div>
                        <ul class="mb-0 ps-3">
                            <li>Laporan Evaluasi Diri (LED)</li>
                            @if($needSuplemen)
                            <li>Suplemen: PDF (wajib untuk jenis akreditasi menuju unggul).</li>
                            @else
                            <li>Suplemen: tidak wajib untuk jenis akreditasi ini.</li>
                            @endif
                            <li>Laporan Kinerja Program Studi (LKPS)</li>
                            <li>Lembar Pengesahan Dokumen</li>
                        </ul>
                    </div>

                    <!-- Progress upload realtime -->
                    <div id="uploadProgressBox" class="d-none mt-3">
                        <div class="fw-bold mb-2"><i class="bi bi-hourglass-split"></i> Progres Upload</div>
                        <ul class="list-group list-group-flush small" id="uploadProgressList">
                            <li class="list-group-item px-0 py-1" id="progLed">
                                <i class="bi bi-circle text-muted me-1"></i> LED (DOCX)
                            </li>
                            <li class="list-group-item px-0 py-1" id="progLkps">
                                <i class="bi bi-circle text-muted me-1"></i> LKPS (Excel)
                            </li>
                            <li class="list-group-item px-0 py-1" id="progSuplemen">
                                <i class="bi bi-circle text-muted me-1"></i> Suplemen (PDF)
                            </li>
                            <li class="list-group-item px-0 py-1" id="progPengesahan">
                                <i class="bi bi-circle text-muted me-1"></i> Lembar Pengesahan (PDF)
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-light" role="status" style="width:3rem;height:3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="loading-text" id="loadingText">Memproses...</div>
</div>
@endsection

@push('scripts')
@php
$pengajuanId = $pengajuan->id;
@endphp
<script>
    (function() {
        const NEED_SUPLEMEN = @json($needSuplemen);
        const PENGAJUAN_ID = @json($pengajuanId);
        const CSRF = '{{ csrf_token() }}';

        // Route untuk upload Suplemen + Pengesahan (biasa, tanpa import)
        const URL_UPLOAD = '{{ route("upps.penerimaan-dokumen.upload", $pengajuan->id) }}';

        // Route import LED (sama seperti di borang-online)
        const URL_IMPORT_DOCX = `/permohonan-akreditasi/${PENGAJUAN_ID}/borang/import-docx`;
        const URL_IMPORT_DOCX_STATUS = (id) => `/permohonan-akreditasi/${PENGAJUAN_ID}/borang/import-status/${id}`;

        // Route import LKPS (sama seperti di borang-online)
        const URL_IMPORT_LKPS = `/permohonan-akreditasi/${PENGAJUAN_ID}/borang/import-lkps`;
        const URL_IMPORT_LKPS_STATUS = (id) => `/permohonan-akreditasi/${PENGAJUAN_ID}/borang/import-lkps/${id}/status`;

        // Redirect setelah semua selesai
        const URL_REDIRECT = '{{ route("upps.penerimaan-dokumen.show", $pengajuan->id) }}';
        const EXISTING_FILES = {
            led: @json(!empty($uploadedDocuments['led']))
            , lkps: @json(!empty($uploadedDocuments['lkps']))
            , suplemen: @json(!empty($uploadedDocuments['suplemen']))
            , pengesahan: @json(!empty($uploadedDocuments['pengesahan']))
        };

        // ===================== helpers =====================
        function on(el, event, fn) {
            if (el) el.addEventListener(event, fn);
        }

        function formatFileSize(bytes) {
            if (!bytes) return '0 Bytes';
            const k = 1024
                , sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
        }

        function makeFileList(file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            return dt.files;
        }

        function bindDragDrop(areaEl, onFile) {
            if (!areaEl) return;
            areaEl.addEventListener('dragover', (e) => {
                e.preventDefault();
                areaEl.classList.add('dragover');
            });
            areaEl.addEventListener('dragleave', () => areaEl.classList.remove('dragover'));
            areaEl.addEventListener('drop', (e) => {
                e.preventDefault();
                areaEl.classList.remove('dragover');
                const file = e.dataTransfer.files && e.dataTransfer.files[0] ? e.dataTransfer.files[0] : null;
                onFile(file);
            });
        }

        function showLoading(text = 'Memproses...') {
            document.getElementById('loadingText').textContent = text;
            document.getElementById('loadingOverlay').classList.add('show');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('show');
        }

        // Progress item helpers
        function progPending(id, label) {
            const el = document.getElementById(id);
            if (el) el.innerHTML = `<i class="bi bi-circle text-muted me-1"></i> ${label}`;
        }

        function progLoading(id, label) {
            const el = document.getElementById(id);
            if (el) el.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> ${label}`;
        }

        function progDone(id, label) {
            const el = document.getElementById(id);
            if (el) el.innerHTML = `<i class="bi bi-check-circle-fill text-success me-1"></i> ${label}`;
        }

        function progError(id, label) {
            const el = document.getElementById(id);
            if (el) el.innerHTML = `<i class="bi bi-x-circle-fill text-danger me-1"></i> ${label}`;
        }

        // ===================== file widgets =====================
        function makeWidget(cfg) {
            const {
                jenis
                , areaId
                , inputId
                , previewId
                , nameId
                , sizeId
                , btnPickId
                , btnChangeId
                , btnRemoveId
                , accept
                , validate
                , onReady
            } = cfg;

            const area = document.getElementById(areaId);
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            const nameEl = document.getElementById(nameId);
            const sizeEl = document.getElementById(sizeId);

            function handle(file) {
                if (!file) return;
                const err = validate(file);
                if (err) {
                    Swal.fire('Perhatian', err, 'error');
                    remove();
                    return;
                }

                if (nameEl) nameEl.textContent = file.name;
                if (sizeEl) sizeEl.textContent = formatFileSize(file.size);
                if (preview) {
                    preview.classList.remove('d-none');
                    preview.classList.add('has-file');
                }
                if (onReady) onReady();
            }

            function remove() {
                if (input) input.value = '';
                if (preview) {
                    preview.classList.add('d-none');
                    preview.classList.remove('has-file');
                }
                if (nameEl) nameEl.textContent = '-';
                if (sizeEl) sizeEl.textContent = '-';
                if (onReady) onReady();
            }

            on(document.getElementById(btnPickId), 'click', () => input && input.click());
            on(document.getElementById(btnChangeId), 'click', () => input && input.click());
            on(document.getElementById(btnRemoveId), 'click', () => {
                const jenis = cfg.jenis;
                deleteUploadedFile(jenis, remove);
            });

            on(area, 'click', (e) => {
                if (e.target.closest('button')) return;
                if (input) input.click();
            });
            on(input, 'change', () => handle(input.files[0]));

            bindDragDrop(area, (file) => {
                if (!file || !input) return;
                input.files = makeFileList(file);
                handle(file);
            });

            return {
                input
                , remove
            };
        }

        // ===================== build widgets =====================
        makeWidget({
            jenis: 'led'
            , areaId: 'uploadAreaLed'
            , inputId: 'file_led'
            , previewId: 'previewLed'
            , nameId: 'ledName'
            , sizeId: 'ledSize'
            , btnPickId: 'btnPickLed'
            , btnChangeId: 'btnChangeLed'
            , btnRemoveId: 'btnRemoveLed'
            , accept: '.docx,.doc'
            , validate(f) {
                if (!(f.name.toLowerCase().endsWith('.docx') || f.name.toLowerCase().endsWith('.doc'))) return 'LED harus berformat DOCX/DOC!';
                if (f.size > 10 * 1024 * 1024) return 'Ukuran file LED maksimal 10MB!';
                return null;
            }
            , onReady: checkReady
        , });

        makeWidget({
            jenis: 'suplemen'
            , areaId: 'uploadAreaSuplemen'
            , inputId: 'file_suplemen'
            , previewId: 'previewSuplemen'
            , nameId: 'suplemenName'
            , sizeId: 'suplemenSize'
            , btnPickId: 'btnPickSuplemen'
            , btnChangeId: 'btnChangeSuplemen'
            , btnRemoveId: 'btnRemoveSuplemen'
            , accept: '.pdf'
            , validate(f) {
                if (!f.name.toLowerCase().endsWith('.pdf')) return 'Suplemen harus berformat PDF!';
                if (f.size > 10 * 1024 * 1024) return 'Ukuran file Suplemen maksimal 10MB!';
                return null;
            }
            , onReady: checkReady
        , });

        makeWidget({
            jenis: 'lkps'
            , areaId: 'uploadAreaLkps'
            , inputId: 'file_lkps'
            , previewId: 'previewLkps'
            , nameId: 'lkpsName'
            , sizeId: 'lkpsSize'
            , btnPickId: 'btnPickLkps'
            , btnChangeId: 'btnChangeLkps'
            , btnRemoveId: 'btnRemoveLkps'
            , accept: '.xlsx,.xls'
            , validate(f) {
                const n = f.name.toLowerCase();
                if (!(n.endsWith('.xlsx') || n.endsWith('.xls'))) return 'LKPS harus berformat XLSX/XLS!';
                if (f.size > 10 * 1024 * 1024) return 'Ukuran file LKPS maksimal 10MB!';
                return null;
            }
            , onReady: checkReady
        , });

        makeWidget({
            jenis: 'pengesahan'
            , areaId: 'uploadAreaPengesahan'
            , inputId: 'file_pengesahan'
            , previewId: 'previewPengesahan'
            , nameId: 'pengesahanName'
            , sizeId: 'pengesahanSize'
            , btnPickId: 'btnPickPengesahan'
            , btnChangeId: 'btnChangePengesahan'
            , btnRemoveId: 'btnRemovePengesahan'
            , accept: '.pdf'
            , validate(f) {
                if (!f.name.toLowerCase().endsWith('.pdf')) return 'Lembar Pengesahan harus berformat PDF!';
                if (f.size > 10 * 1024 * 1024) return 'Ukuran file Pengesahan maksimal 10MB!';
                return null;
            }
            , onReady: checkReady
        , });

        // ===================== readiness check =====================
        function getInput(id) {
            return document.getElementById(id);
        }

        function checkReady() {
            const fileLed = document.getElementById('file_led');
            const hasNewLed = !!(fileLed && fileLed.files && fileLed.files.length > 0);
            const okLed = hasNewLed || EXISTING_FILES.led;

            const fileLkps = document.getElementById('file_lkps');
            const hasNewLkps = !!(fileLkps && fileLkps.files && fileLkps.files.length > 0);
            const okLkps = hasNewLkps || EXISTING_FILES.lkps;

            const filePengesahan = document.getElementById('file_pengesahan');
            const hasNewPengesahan = !!(filePengesahan && filePengesahan.files && filePengesahan.files.length > 0);
            const okPengesahan = hasNewPengesahan || EXISTING_FILES.pengesahan;

            const fileSuplemen = document.getElementById('file_suplemen');
            const hasNewSuplemen = !!(fileSuplemen && fileSuplemen.files && fileSuplemen.files.length > 0);
            const okSuplemen = NEED_SUPLEMEN ? (hasNewSuplemen || EXISTING_FILES.suplemen) : true;

            document.getElementById('btnSubmit').disabled = !(okLed && okLkps && okPengesahan && okSuplemen);
        }

        checkReady();

        // ===================== polling helpers =====================
        async function sleep(ms) {
            return new Promise(r => setTimeout(r, ms));
        }

        /**
         * Poll import-docx status.
         * Returns 'completed' | 'failed' | 'timeout'
         */
        async function pollDocxStatus(importId, {
            maxMs = 120000
            , interval = 2000
        } = {}) {
            const start = Date.now();
            while (Date.now() - start < maxMs) {
                await sleep(interval);
                try {
                    const res = await fetch(URL_IMPORT_DOCX_STATUS(importId), {
                        headers: {
                            Accept: 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (!data || !data.success) continue;

                    const st = data.status || (data.data && data.data.status) || (data.import && data.import.status);
                    if (!st) continue;

                    if (st === 'completed') return 'completed';
                    if (st === 'failed') return 'failed';
                } catch {
                    /* retry */
                }
            }
            return 'timeout';
        }

        /**
         * Poll import-lkps status.
         * Returns 'completed' | 'failed' | 'timeout'
         */
        async function pollLkpsStatus(importId, {
            maxMs = 120000
            , interval = 2000
        } = {}) {
            const start = Date.now();
            while (Date.now() - start < maxMs) {
                await sleep(interval);
                try {
                    const res = await fetch(URL_IMPORT_LKPS_STATUS(importId), {
                        headers: {
                            Accept: 'application/json'
                        }
                    });
                    const data = await res.json();
                    const st = data && data.summary && data.summary.status;
                    if (!st) continue;

                    if (st === 'completed') return 'completed';
                    if (st === 'failed') return 'failed';
                } catch {
                    /* retry */
                }
            }
            return 'timeout';
        }

        // ===================== main submit handler =====================
        on(document.getElementById('btnSubmit'), 'click', async function() {
            const fileLed = getInput('file_led');
            const fileLkps = getInput('file_lkps');
            const fileSuplemen = getInput('file_suplemen');
            const filePengesahan = getInput('file_pengesahan');
            const catatanEl = document.getElementById('catatan_upload');
            const catatan = catatanEl ? catatanEl.value : '';

            // Show progress sidebar
            document.getElementById('uploadProgressBox').classList.remove('d-none');
            progPending('progLed', 'LED (DOCX)');
            progPending('progLkps', 'LKPS (Excel)');
            progPending('progSuplemen', 'Suplemen (PDF)');
            progPending('progPengesahan', 'Lembar Pengesahan (PDF)');

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';

            // ── 1. Upload & import LED ────────────────────────────────────────
            showLoading('Mengupload LED (DOCX)...');
            progLoading('progLed', 'LED (DOCX) — Mengupload...');

            // ── 1. Upload & import LED ────────────────────────────────────────
            let ledOk = false;
            const hasNewLed = !!(fileLed && fileLed.files && fileLed.files.length > 0);

            if (hasNewLed) {
                showLoading('Mengupload LED (DOCX)...');
                progLoading('progLed', 'LED (DOCX) — Mengupload...');

                try {
                    const fd = new FormData();
                    fd.append('_token', CSRF);
                    fd.append('docx_file', fileLed.files[0]);

                    const res = await fetch(URL_IMPORT_DOCX, {
                        method: 'POST'
                        , headers: {
                            Accept: 'application/json'
                        }
                        , body: fd
                    });
                    const data = await res.json();

                    if (!data.success) throw new Error(data.message || 'Upload LED gagal');

                    const importId = data.data ? data.data.import_id : null;
                    if (importId) {
                        showLoading('Memproses LED (DOCX)...');
                        progLoading('progLed', 'LED (DOCX) — Memproses...');

                        const st = await pollDocxStatus(importId);
                        if (st === 'completed') {
                            progDone('progLed', 'LED (DOCX) — Selesai');
                            ledOk = true;
                            EXISTING_FILES.led = true;
                        } else if (st === 'timeout') {
                            progDone('progLed', 'LED (DOCX) — Masih diproses (timeout)');
                            ledOk = true;
                            EXISTING_FILES.led = true;
                        } else {
                            throw new Error('Proses pembacaan data LED gagal.');
                        }
                    } else {
                        progDone('progLed', 'LED (DOCX) — Selesai');
                        ledOk = true;
                        EXISTING_FILES.led = true;
                    }
                } catch (e) {
                    progError('progLed', `LED — ${e.message}`);
                    hideLoading();
                    await Swal.fire('Gagal Upload LED', e.message, 'error');
                    resetBtn(this);
                    return;
                }
            } else if (EXISTING_FILES.led) {
                progDone('progLed', 'LED (DOCX) — Sudah ada');
                ledOk = true;
            } else {
                progError('progLed', 'LED — Belum ada file');
                hideLoading();
                await Swal.fire('Gagal Upload LED', 'File LED belum dipilih.', 'error');
                resetBtn(this);
                return;
            }

            // ── 2. Upload & import LKPS ──────────────────────────────────────
            showLoading('Mengupload LKPS (Excel)...');
            progLoading('progLkps', 'LKPS (Excel) — Mengupload...');

            // ── 2. Upload & import LKPS ──────────────────────────────────────
            let lkpsOk = false;
            const hasNewLkps = !!(fileLkps && fileLkps.files && fileLkps.files.length > 0);

            if (hasNewLkps) {
                showLoading('Mengupload LKPS (Excel)...');
                progLoading('progLkps', 'LKPS (Excel) — Mengupload...');

                try {
                    const fd = new FormData();
                    fd.append('_token', CSRF);
                    fd.append('file', fileLkps.files[0]);

                    const res = await fetch(URL_IMPORT_LKPS, {
                        method: 'POST'
                        , headers: {
                            Accept: 'application/json'
                        }
                        , body: fd
                    });
                    const data = await res.json();

                    if (!data.success) throw new Error(data.message || 'Upload LKPS gagal');

                    const importId = data.borang_import_id;
                    if (importId) {
                        showLoading('Memproses LKPS (Excel)...');
                        progLoading('progLkps', 'LKPS (Excel) — Memproses...');

                        const st = await pollLkpsStatus(importId);
                        if (st === 'completed') {
                            progDone('progLkps', 'LKPS (Excel) — Selesai');
                            lkpsOk = true;
                            EXISTING_FILES.lkps = true;
                        } else if (st === 'timeout') {
                            progDone('progLkps', 'LKPS (Excel) — Masih diproses (timeout)');
                            lkpsOk = true;
                            EXISTING_FILES.lkps = true;
                        } else {
                            throw new Error('Proses pembacaan data LKPS gagal.');
                        }
                    } else {
                        progDone('progLkps', 'LKPS (Excel) — Selesai');
                        lkpsOk = true;
                        EXISTING_FILES.lkps = true;
                    }
                } catch (e) {
                    progError('progLkps', `LKPS — ${e.message}`);
                    hideLoading();
                    await Swal.fire('Gagal Upload LKPS', e.message, 'error');
                    resetBtn(this);
                    return;
                }
            } else if (EXISTING_FILES.lkps) {
                progDone('progLkps', 'LKPS (Excel) — Sudah ada');
                lkpsOk = true;
            } else {
                progError('progLkps', 'LKPS — Belum ada file');
                hideLoading();
                await Swal.fire('Gagal Upload LKPS', 'File LKPS belum dipilih.', 'error');
                resetBtn(this);
                return;
            }

            // ── 3. Upload Suplemen + Pengesahan (jika ada file baru) ──────────
            const hasNewSuplemen = !!(fileSuplemen && fileSuplemen.files && fileSuplemen.files.length > 0);
            const hasNewPengesahan = !!(filePengesahan && filePengesahan.files && filePengesahan.files.length > 0);

            const suplemenReady = NEED_SUPLEMEN ? (hasNewSuplemen || EXISTING_FILES.suplemen) : true;
            const pengesahanReady = hasNewPengesahan || EXISTING_FILES.pengesahan;

            if (!suplemenReady || !pengesahanReady) {
                hideLoading();
                await Swal.fire(
                    'Dokumen Belum Lengkap'
                    , 'Suplemen dan/atau Lembar Pengesahan belum tersedia.'
                    , 'error'
                );
                resetBtn(this);
                return;
            }

            if (hasNewSuplemen || hasNewPengesahan) {
                showLoading('Mengupload Suplemen & Pengesahan...');
                if (NEED_SUPLEMEN) progLoading('progSuplemen', 'Suplemen (PDF) — Mengupload...');
                progLoading('progPengesahan', 'Lembar Pengesahan (PDF) — Mengupload...');

                try {
                    const fd = new FormData();
                    fd.append('_token', CSRF);
                    fd.append('_method', 'POST');
                    fd.append('catatan_upload', catatan);

                    if (hasNewSuplemen) fd.append('file_suplemen', fileSuplemen.files[0]);
                    if (hasNewPengesahan) fd.append('file_pengesahan', filePengesahan.files[0]);

                    const res = await fetch(URL_UPLOAD, {
                        method: 'POST'
                        , headers: {
                            Accept: 'application/json'
                        }
                        , body: fd
                    });
                    const data = await res.json();

                    if (!data.success) throw new Error(data.message || 'Upload Suplemen/Pengesahan gagal');

                    if (NEED_SUPLEMEN) {
                        progDone('progSuplemen', hasNewSuplemen ? 'Suplemen (PDF) — Selesai' : 'Suplemen (PDF) — Sudah ada');
                    }
                    progDone('progPengesahan', hasNewPengesahan ? 'Lembar Pengesahan (PDF) — Selesai' : 'Lembar Pengesahan (PDF) — Sudah ada');

                    if (hasNewSuplemen) EXISTING_FILES.suplemen = true;
                    if (hasNewPengesahan) EXISTING_FILES.pengesahan = true;

                } catch (e) {
                    progError('progSuplemen', `Suplemen — ${e.message}`);
                    progError('progPengesahan', `Pengesahan — ${e.message}`);
                    hideLoading();
                    await Swal.fire('Gagal Upload', e.message, 'error');
                    resetBtn(this);
                    return;
                }
            } else {
                if (NEED_SUPLEMEN) progDone('progSuplemen', 'Suplemen (PDF) — Sudah ada');
                progDone('progPengesahan', 'Lembar Pengesahan (PDF) — Sudah ada');
            }

            // ── 4. Selesai — redirect ────────────────────────────────────────
            hideLoading();

            if (typeof Swal !== 'undefined') {
                await Swal.fire({
                    icon: 'success'
                    , title: 'Upload Selesai!'
                    , text: 'Semua dokumen berhasil diupload dan diproses. Anda akan diarahkan ke halaman detail.'
                    , timer: 2500
                    , showConfirmButton: false
                , });
            }

            window.location.href = URL_REDIRECT;
        });

        function resetBtn(btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-upload"></i> Upload Dokumen';
            checkReady();
        }

        function mapJenisToExistingKey(jenis) {
            return {
                led: 'led'
                , lkps: 'lkps'
                , suplemen: 'suplemen'
                , pengesahan: 'pengesahan'
                , kualitatif: 'led'
                , kuantitatif: 'lkps'
            , } [jenis] || jenis;
        }

        async function deleteUploadedFile(jenis, removeUiCallback) {
            const confirm = await Swal.fire({
                icon: 'warning'
                , title: 'Hapus Dokumen?'
                , html: `Dokumen <b>${jenis.toUpperCase()}</b> akan dihapus dari sistem.<br>Data yang telah disimpan juga akan ikut dihapus.`
                , showCancelButton: true
                , confirmButtonText: 'Ya, Hapus'
                , cancelButtonText: 'Batal'
                , confirmButtonColor: '#d33'
            });

            if (!confirm.isConfirmed) return;

            try {
                showLoading('Menghapus dokumen...');

                const res = await fetch(`/permohonan-akreditasi/${PENGAJUAN_ID}/borang-online/uploaded-file/${jenis}`, {
                    method: 'DELETE'
                    , headers: {
                        'X-CSRF-TOKEN': CSRF
                        , 'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                hideLoading();

                if (!data.success) throw new Error(data.message || 'Gagal menghapus dokumen');

                const existingKey = mapJenisToExistingKey(jenis);
                if (existingKey && Object.prototype.hasOwnProperty.call(EXISTING_FILES, existingKey)) {
                    EXISTING_FILES[existingKey] = false;
                }

                if (removeUiCallback) removeUiCallback();
                checkReady();

                Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil'
                    , text: 'Dokumen berhasil dihapus'
                    , timer: 1500
                    , showConfirmButton: false
                });
            } catch (e) {
                hideLoading();
                Swal.fire('Gagal Menghapus', e.message, 'error');
            }
        }
    })();

</script>
@endpush
