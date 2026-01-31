{{-- resources/views/upps/penerimaan-dokumen/upload.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Upload Dokumen')

@push('styles')
<style>
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

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penerimaan-dokumen') }}">Penerimaan Dokumen</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penerimaan-dokumen.show', $pengajuan->id) }}">Detail</a></li>
            <li class="breadcrumb-item active">Upload Dokumen</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-upload"></i> Upload Dokumen Akreditasi
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.penerimaan-dokumen.show', $pengajuan->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Info Permohonan -->
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Permohonan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th width="45%">Nomor Permohonan</th>
                                    <td>: <strong>{{ $pengajuan->nomor_pengajuan }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Program Studi</th>
                                    <td>: {{ $pengajuan->studyProgram->name }}</td>
                                </tr>
                                <tr>
                                    <th>Universitas</th>
                                    <td>: {{ $pengajuan->studyProgram->university->name }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th width="45%">Jenis Permohonan</th>
                                    <td>: {{ $pengajuan->jenis_akreditasi_label }}</td>
                                </tr>
                                <tr>
                                    <th>Tahun Akreditasi</th>
                                    <td>: {{ $pengajuan->tahun_akreditasi }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form Alert -->
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED)
            <div class="alert alert-warning alert-permanent">
                <h5><i class="bi bi-exclamation-triangle"></i> Permintaan Revisi Dokumen</h5>
                <p class="mb-0">
                    Validator meminta revisi pada dokumen yang telah diupload. Silakan perbaiki dokumen sesuai catatan validator dan upload ulang.
                </p>
            </div>
            @endif

            <!-- Upload Form -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cloud-upload"></i> Form Upload Dokumen
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('upps.penerimaan-dokumen.upload', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" id="formUploadDokumen">
                        @csrf

                        <!-- File Dokumen with Drag & Drop -->
                        <div class="mb-3">
                            <label class="form-label">
                                File Dokumen Akreditasi <span class="text-danger">*</span>
                            </label>

                            <!-- Upload Area -->
                            <div class="upload-area" id="uploadArea">
                                <i class="bi bi-cloud-upload" style="font-size: 48px; color: #6c757d;"></i>
                                <p class="mb-2"><strong>Klik atau drag & drop file di sini</strong></p>
                                <p class="text-muted small mb-2">Format: PDF (Maksimal 10MB)</p>
                                <input type="file" id="file_dokumen" name="file_dokumen" class="d-none" accept=".pdf" required>
                                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('file_dokumen').click()">
                                    <i class="bi bi-folder2-open"></i> Pilih File
                                </button>
                            </div>

                            <!-- File Preview -->
                            <div id="filePreview" class="file-preview-card d-none mt-3">
                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 48px;"></i>
                                <p class="mb-1 mt-2"><strong id="fileName"></strong></p>
                                <p class="text-muted small mb-2" id="fileSize"></p>
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile()">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('file_dokumen').click()">
                                        <i class="bi bi-arrow-repeat"></i> Ganti File
                                    </button>
                                </div>
                            </div>

                            @error('file_dokumen')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Catatan Upload -->
                        <div class="mb-3">
                            <label for="catatan_upload" class="form-label">
                                Catatan Upload (Opsional)
                            </label>
                            <textarea class="form-control @error('catatan_upload') is-invalid @enderror" id="catatan_upload" name="catatan_upload" rows="3" placeholder="Tambahkan catatan jika diperlukan...">{{ old('catatan_upload') }}</textarea>
                            @error('catatan_upload')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Important Note -->
                        <div class="alert alert-info alert-permanent">
                            <h6><i class="bi bi-info-circle"></i> Informasi Penting</h6>
                            <ul class="mb-0">
                                <li>Pastikan dokumen yang diupload telah sesuai dengan template yang diberikan</li>
                                <li>Dokumen harus dalam format PDF dengan ukuran maksimal 10MB</li>
                                <li>Periksa kelengkapan data sebelum mengupload</li>
                                <li>Setelah upload, dokumen akan diperiksa oleh tim LAMDEPILAR</li>
                                <li>Anda akan mendapat notifikasi hasil pemeriksaan melalui email</li>
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
        </div>
    </div>
</div>

@push('scripts')
<script>
    // File Upload Handling
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('file_dokumen');
    const filePreview = document.getElementById('filePreview');
    const btnSubmit = document.getElementById('btnSubmit');

    // Click to upload
    uploadArea.addEventListener('click', (e) => {
        if (e.target !== uploadArea && e.target.closest('.btn')) return;
        fileInput.click();
    });

    // File selected
    fileInput.addEventListener('change', (e) => {
        handleFileSelect(e.target.files[0]);
    });

    // Drag & Drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');

        const file = e.dataTransfer.files[0];
        if (file && file.name.toLowerCase().endsWith('.pdf')) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelect(file);
        } else {
            alert('File harus berformat PDF!');
        }
    });

    function handleFileSelect(file) {
        if (!file) return;

        // Validate file type
        if (!file.name.toLowerCase().endsWith('.pdf')) {
            alert('File harus berformat PDF!');
            return;
        }

        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('Ukuran file maksimal 10MB!');
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

    // Form submit with loading state
    document.getElementById('formUploadDokumen').addEventListener('submit', function() {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';
    });

</script>
@endpush
@endsection
