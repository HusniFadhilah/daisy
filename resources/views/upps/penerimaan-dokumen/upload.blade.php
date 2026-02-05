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
    <div class="d-flex justify-content-between align-items-center mb-4">
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
                        <div class="fw-bold">Nomor Permohonan Akreditasi</div>
                        <div>{{ $pengajuan->nomor_pengajuan }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="fw-bold">Jenis Dokumen Diperlukan</div>
                        <ul class="mb-0 ps-3">
                            <li>Laporan Evaluasi Diri (LED)</li>
                            <li>Suplemen LED</li>
                            <li>Laporan Kinerja Program Studi (LKPS)</li>
                            <li>Lembar Pengesahan Dokumen</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Upload Form Alert -->
            @if(!$canUploadDokumen)
            <div class="alert alert-info alert-permanent mb-4">
                <h5 class="mb-1">
                    <i class="bi bi-eye"></i> Mode Preview Dokumen
                </h5>
                <p class="mb-0">
                    Status permohonan saat ini hanya memungkinkan <strong>preview dokumen</strong>.
                    Upload dokumen akan tersedia ketika status sudah sesuai.
                </p>
                <div class="small text-muted mt-1">
                    Status saat ini: <strong>{{ $pengajuan->status_label ?? $pengajuan->status }}</strong>
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
                    'led' => [
                    'label' => 'Laporan Evaluasi Diri (LED)',
                    'icon' => 'file-word',
                    'color' => 'info',
                    ],
                    'suplemen' => [
                    'label' => 'Suplemen LED',
                    'icon' => 'file-earmark-pdf',
                    'color' => 'warning',
                    ],
                    'lkps' => [
                    'label' => 'Laporan Kinerja Program Studi (LKPS)',
                    'icon' => 'file-excel',
                    'color' => 'success',
                    ],
                    'pengesahan' => [
                    'label' => 'Lembar Pengesahan Dokumen',
                    'icon' => 'file-earmark-pdf',
                    'color' => 'danger',
                    ],
                    ];
                    @endphp

                    <div class="row">
                        @foreach($docCards as $key => $cfg)
                        @continue($key === 'suplemen' && !$needSuplemen)

                        @php $doc = $uploadedDocuments[$key] ?? null; @endphp

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
                                            {{ $doc->original_filename ?? '-' }}<br>
                                            {{ $doc->file_size_formatted ?? '-' }}
                                            @if($doc->created_at) • {{ $doc->created_at->format('d M Y H:i') }} @endif
                                        </small>

                                        @if($doc->path_file || $doc->template_link)
                                        <a href="{{ $doc->download_url }}" class="btn btn-sm btn-success mt-2" target="_blank">
                                            <i class="bi bi-eye"></i> Lihat File
                                        </a>
                                        @endif
                                        @else
                                        <span class="badge bg-danger mt-2">Belum Diupload</span>
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
                    <form action="{{ route('upps.penerimaan-dokumen.upload', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" id="formUploadDokumen">
                        @csrf

                        {{-- ===================== LED ===================== --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Laporan Evaluasi Diri (LED) <span class="text-danger">*</span>
                            </label>

                            <div class="upload-area" id="uploadAreaLed">
                                <i class="bi bi-file-word text-info" style="font-size: 44px;"></i>
                                <p class="mb-1"><strong>Silahkan upload file LED di sini</strong></p>
                                <p class="text-muted small mb-2">Format: DOCX/DOC • Maksimal 10MB</p>

                                <input type="file" id="file_led" name="file_led" class="visually-hidden-input" accept=".docx,.doc" required>
                                <button type="button" class="btn btn-outline-info btn-sm" id="btnPickLed">
                                    <i class="bi bi-folder2-open"></i> Pilih File LED
                                </button>
                            </div>

                            <div id="previewLed" class="file-preview-card d-none mt-2">
                                <div class="file-meta">
                                    <div class="left">
                                        <i class="bi bi-file-word text-info" style="font-size: 26px;"></i>
                                        <div>
                                            <div class="fw-bold" id="ledName">-</div>
                                            <div class="text-muted small" id="ledSize">-</div>
                                        </div>
                                    </div>
                                    <div class="right d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveLed">
                                            <i class="bi bi-trash"></i> Batalkan
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnChangeLed">
                                            <i class="bi bi-arrow-repeat"></i> Ganti
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @error('file_led')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ===================== SUPLEMEN ===================== --}}
                        @if($needSuplemen)
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Suplemen LED <span class="text-danger">*</span>
                            </label>

                            <div class="upload-area" id="uploadAreaSuplemen">
                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 44px;"></i>
                                <p class="mb-1"><strong>Silahkan upload file Suplemen di sini</strong></p>
                                <p class="text-muted small mb-2">Format: PDF • Maksimal 10MB</p>

                                <input type="file" id="file_suplemen" name="file_suplemen" class="visually-hidden-input" accept=".pdf" required>
                                <button type="button" class="btn btn-outline-success btn-sm" id="btnPickSuplemen">
                                    <i class="bi bi-folder2-open"></i> Pilih File Suplemen
                                </button>
                            </div>

                            <div id="previewSuplemen" class="file-preview-card d-none mt-2">
                                <div class="file-meta">
                                    <div class="left">
                                        <i class="bi bi-file-earmark-pdf text-success" style="font-size: 26px;"></i>
                                        <div>
                                            <div class="fw-bold" id="suplemenName">-</div>
                                            <div class="text-muted small" id="suplemenSize">-</div>
                                        </div>
                                    </div>
                                    <div class="right d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveSuplemen">
                                            <i class="bi bi-trash"></i> Batalkan
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnChangeSuplemen">
                                            <i class="bi bi-arrow-repeat"></i> Ganti
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @error('file_suplemen')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        {{-- ===================== LKPS ===================== --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Laporan Kinerja Program Studi (LKPS) <span class="text-danger">*</span>
                            </label>

                            <div class="upload-area" id="uploadAreaLkps">
                                <i class="bi bi-file-excel text-success" style="font-size: 44px;"></i>
                                <p class="mb-1"><strong>Silahkan upload file LKPS di sini</strong></p>
                                <p class="text-muted small mb-2">Format: XLSX/XLS • Maksimal 10MB</p>

                                <input type="file" id="file_lkps" name="file_lkps" class="visually-hidden-input" accept=".xlsx,.xls" required>
                                <button type="button" class="btn btn-outline-success btn-sm" id="btnPickLkps">
                                    <i class="bi bi-folder2-open"></i> Pilih File LKPS
                                </button>
                            </div>

                            <div id="previewLkps" class="file-preview-card d-none mt-2">
                                <div class="file-meta">
                                    <div class="left">
                                        <i class="bi bi-file-excel text-success" style="font-size: 26px;"></i>
                                        <div>
                                            <div class="fw-bold" id="lkpsName">-</div>
                                            <div class="text-muted small" id="lkpsSize">-</div>
                                        </div>
                                    </div>
                                    <div class="right d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveLkps">
                                            <i class="bi bi-trash"></i> Batalkan
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnChangeLkps">
                                            <i class="bi bi-arrow-repeat"></i> Ganti
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @error('file_lkps')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ===================== LEMBAR PENGESAHAN ===================== --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Lembar Pengesahan Dokumen <span class="text-danger">*</span>
                            </label>

                            <div class="upload-area" id="uploadAreaPengesahan">
                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 44px;"></i>
                                <p class="mb-1"><strong>Silahkan upload file Lembar Pengesahan di sini</strong></p>
                                <p class="text-muted small mb-2">Format: PDF • Maksimal 10MB</p>

                                <input type="file" id="file_pengesahan" name="file_pengesahan" class="visually-hidden-input" accept=".pdf" required>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnPickPengesahan">
                                    <i class="bi bi-folder2-open"></i> Pilih File Pengesahan
                                </button>
                            </div>

                            <div id="previewPengesahan" class="file-preview-card d-none mt-2">
                                <div class="file-meta">
                                    <div class="left">
                                        <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 26px;"></i>
                                        <div>
                                            <div class="fw-bold" id="pengesahanName">-</div>
                                            <div class="text-muted small" id="pengesahanSize">-</div>
                                        </div>
                                    </div>
                                    <div class="right d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemovePengesahan">
                                            <i class="bi bi-trash"></i> Batalkan
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnChangePengesahan">
                                            <i class="bi bi-arrow-repeat"></i> Ganti
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @error('file_pengesahan')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Catatan --}}
                        <div class="mb-3">
                            <label for="catatan_upload" class="form-label">Catatan Upload (Opsional)</label>
                            <textarea class="form-control @error('catatan_upload') is-invalid @enderror" id="catatan_upload" name="catatan_upload" rows="3" placeholder="Tambahkan catatan jika diperlukan...">{{ old('catatan_upload') }}</textarea>
                            @error('catatan_upload')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Important Note -->
                        <div class="alert alert-info alert-permanent">
                            <h6><i class="bi bi-info-circle"></i> Informasi Penting</h6>
                            <ul class="mb-0">
                                <li>Mohon memastikan dokumen yang diupload, sudah sesuai template yang diberikan.</li>
                                <li><strong>LED</strong>: format file DOCX/DOC, <strong>LKPS</strong>: format file XLSX/XLS (maks 10MB per file).</li>
                                @if($needSuplemen)
                                <li><strong>Suplemen</strong>: PDF (wajib untuk jenis akreditasi <strong>menuju unggul</strong>).</li>
                                @else
                                <li><strong>Suplemen</strong>: tidak wajib untuk jenis akreditasi ini.</li>
                                @endif
                                <li><strong>Lembar Pengesahan Dokumen</strong>: format file PDF.</li>
                            </ul>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-md" id="btnSubmit" disabled>
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
    </div>
</div>

@push('scripts')
<script>
    (function() {
        const NEED_SUPLEMEN = @json($needSuplemen);

        const btnSubmit = document.getElementById('btnSubmit');
        const form = document.getElementById('formUploadDokumen');

        // ===================== helper safe addEventListener =====================
        function on(el, event, handler) {
            if (!el) return;
            el.addEventListener(event, handler);
        }

        // ===================== utils =====================
        function formatFileSize(bytes) {
            if (!bytes) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
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

        // ============================================================
        // LED
        // ============================================================
        const uploadAreaLed = document.getElementById('uploadAreaLed');
        const fileLed = document.getElementById('file_led');
        const previewLed = document.getElementById('previewLed');
        const ledName = document.getElementById('ledName');
        const ledSize = document.getElementById('ledSize');

        const btnPickLed = document.getElementById('btnPickLed');
        const btnChangeLed = document.getElementById('btnChangeLed');
        const btnRemoveLed = document.getElementById('btnRemoveLed');

        on(btnPickLed, 'click', () => fileLed.click());
        on(btnChangeLed, 'click', () => fileLed.click());
        on(btnRemoveLed, 'click', removeLed);

        on(uploadAreaLed, 'click', (e) => {
            if (e.target.closest('button')) return;
            fileLed.click();
        });

        on(fileLed, 'change', () => handleLed(fileLed.files[0]));

        bindDragDrop(uploadAreaLed, (file) => {
            if (!file) return;
            fileLed.files = makeFileList(file);
            handleLed(file);
        });

        function handleLed(file) {
            if (!file) return;

            const name = file.name.toLowerCase();
            if (!(name.endsWith('.docx') || name.endsWith('.doc'))) {
                alert('LED harus berformat DOCX/DOC!');
                removeLed();
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                alert('Ukuran file LED maksimal 10MB!');
                removeLed();
                return;
            }

            ledName.textContent = file.name;
            ledSize.textContent = formatFileSize(file.size);

            previewLed.classList.remove('d-none');
            previewLed.classList.add('has-file');
            checkReady();
        }

        function removeLed() {
            fileLed.value = '';
            previewLed.classList.add('d-none');
            previewLed.classList.remove('has-file');
            ledName.textContent = '-';
            ledSize.textContent = '-';
            checkReady();
        }

        // ============================================================
        // SUPLEMEN (optional render)
        // ============================================================
        const uploadAreaSuplemen = document.getElementById('uploadAreaSuplemen'); // bisa null
        const fileSuplemen = document.getElementById('file_suplemen'); // bisa null
        const previewSuplemen = document.getElementById('previewSuplemen'); // bisa null
        const suplemenName = document.getElementById('suplemenName'); // bisa null
        const suplemenSize = document.getElementById('suplemenSize'); // bisa null

        const btnPickSuplemen = document.getElementById('btnPickSuplemen'); // bisa null
        const btnChangeSuplemen = document.getElementById('btnChangeSuplemen'); // bisa null
        const btnRemoveSuplemen = document.getElementById('btnRemoveSuplemen'); // bisa null

        on(btnPickSuplemen, 'click', () => fileSuplemen && fileSuplemen.click());
        on(btnChangeSuplemen, 'click', () => fileSuplemen && fileSuplemen.click());
        on(btnRemoveSuplemen, 'click', removeSuplemen);

        on(uploadAreaSuplemen, 'click', (e) => {
            if (e.target.closest('button')) return;
            if (fileSuplemen) fileSuplemen.click();
        });

        on(fileSuplemen, 'change', () => handleSuplemen(fileSuplemen.files[0]));

        if (uploadAreaSuplemen && fileSuplemen) {
            bindDragDrop(uploadAreaSuplemen, (file) => {
                if (!file) return;
                fileSuplemen.files = makeFileList(file);
                handleSuplemen(file);
            });
        }

        function handleSuplemen(file) {
            if (!fileSuplemen || !previewSuplemen) return;

            if (!file) {
                checkReady();
                return;
            }

            const name = file.name.toLowerCase();
            if (!name.endsWith('.pdf')) {
                alert('Suplemen harus berformat PDF!');
                removeSuplemen();
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                alert('Ukuran file Suplemen maksimal 10MB!');
                removeSuplemen();
                return;
            }

            suplemenName.textContent = file.name;
            suplemenSize.textContent = formatFileSize(file.size);

            previewSuplemen.classList.remove('d-none');
            previewSuplemen.classList.add('has-file');
            checkReady();
        }

        function removeSuplemen() {
            if (!fileSuplemen || !previewSuplemen) {
                checkReady();
                return;
            }
            fileSuplemen.value = '';
            previewSuplemen.classList.add('d-none');
            previewSuplemen.classList.remove('has-file');
            if (suplemenName) suplemenName.textContent = '-';
            if (suplemenSize) suplemenSize.textContent = '-';
            checkReady();
        }

        // ============================================================
        // LKPS
        // ============================================================
        const uploadAreaLkps = document.getElementById('uploadAreaLkps');
        const fileLkps = document.getElementById('file_lkps');
        const previewLkps = document.getElementById('previewLkps');
        const lkpsName = document.getElementById('lkpsName');
        const lkpsSize = document.getElementById('lkpsSize');

        const btnPickLkps = document.getElementById('btnPickLkps');
        const btnChangeLkps = document.getElementById('btnChangeLkps');
        const btnRemoveLkps = document.getElementById('btnRemoveLkps');

        on(btnPickLkps, 'click', () => fileLkps.click());
        on(btnChangeLkps, 'click', () => fileLkps.click());
        on(btnRemoveLkps, 'click', removeLkps);

        on(uploadAreaLkps, 'click', (e) => {
            if (e.target.closest('button')) return;
            fileLkps.click();
        });

        on(fileLkps, 'change', () => handleLkps(fileLkps.files[0]));

        bindDragDrop(uploadAreaLkps, (file) => {
            if (!file) return;
            fileLkps.files = makeFileList(file);
            handleLkps(file);
        });

        function handleLkps(file) {
            if (!file) return;

            const name = file.name.toLowerCase();
            if (!(name.endsWith('.xlsx') || name.endsWith('.xls'))) {
                alert('LKPS harus berformat XLSX/XLS!');
                removeLkps();
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                alert('Ukuran file LKPS maksimal 10MB!');
                removeLkps();
                return;
            }

            lkpsName.textContent = file.name;
            lkpsSize.textContent = formatFileSize(file.size);

            previewLkps.classList.remove('d-none');
            previewLkps.classList.add('has-file');
            checkReady();
        }

        function removeLkps() {
            fileLkps.value = '';
            previewLkps.classList.add('d-none');
            previewLkps.classList.remove('has-file');
            lkpsName.textContent = '-';
            lkpsSize.textContent = '-';
            checkReady();
        }

        // ============================================================
        // PENGESAHAN (PDF) - WAJIB
        // ============================================================
        const uploadAreaPengesahan = document.getElementById('uploadAreaPengesahan');
        const filePengesahan = document.getElementById('file_pengesahan');
        const previewPengesahan = document.getElementById('previewPengesahan');
        const pengesahanName = document.getElementById('pengesahanName');
        const pengesahanSize = document.getElementById('pengesahanSize');

        const btnPickPengesahan = document.getElementById('btnPickPengesahan');
        const btnChangePengesahan = document.getElementById('btnChangePengesahan');
        const btnRemovePengesahan = document.getElementById('btnRemovePengesahan');

        on(btnPickPengesahan, 'click', () => filePengesahan.click());
        on(btnChangePengesahan, 'click', () => filePengesahan.click());
        on(btnRemovePengesahan, 'click', removePengesahan);

        on(uploadAreaPengesahan, 'click', (e) => {
            if (e.target.closest('button')) return;
            filePengesahan.click();
        });

        on(filePengesahan, 'change', () => handlePengesahan(filePengesahan.files[0]));

        bindDragDrop(uploadAreaPengesahan, (file) => {
            if (!file) return;
            filePengesahan.files = makeFileList(file);
            handlePengesahan(file);
        });

        function handlePengesahan(file) {
            if (!file) return;

            const name = file.name.toLowerCase();
            if (!name.endsWith('.pdf')) {
                alert('Lembar Pengesahan harus berformat PDF!');
                removePengesahan();
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                alert('Ukuran file Lembar Pengesahan maksimal 10MB!');
                removePengesahan();
                return;
            }

            pengesahanName.textContent = file.name;
            pengesahanSize.textContent = formatFileSize(file.size);

            previewPengesahan.classList.remove('d-none');
            previewPengesahan.classList.add('has-file');
            checkReady();
        }

        function removePengesahan() {
            filePengesahan.value = '';
            previewPengesahan.classList.add('d-none');
            previewPengesahan.classList.remove('has-file');
            pengesahanName.textContent = '-';
            pengesahanSize.textContent = '-';
            checkReady();
        }

        // ============================================================
        // readiness: LED + LKPS + Pengesahan + (Suplemen jika wajib)
        // ============================================================
        function checkReady() {
            const okLed = fileLed.files && fileLed.files.length > 0;
            const okLkps = fileLkps.files && fileLkps.files.length > 0;
            const okPengesahan = filePengesahan.files && filePengesahan.files.length > 0;

            let okSuplemen = true;
            if (NEED_SUPLEMEN) {
                okSuplemen = !!(fileSuplemen && fileSuplemen.files && fileSuplemen.files.length > 0);
            }

            btnSubmit.disabled = !(okLed && okLkps && okPengesahan && okSuplemen);
        }

        // initial check
        checkReady();

        // submit loading state
        on(form, 'submit', function() {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';
        });

    })();

</script>
@endpush
@endsection
