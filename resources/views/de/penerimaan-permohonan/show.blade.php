{{-- resources/views/de/penerimaan-permohonan/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Kirim Penerimaan - ' . $pengajuan->nomor_pengajuan)

@push('styles')
<style>
    .upload-zone {
        border: 2px dashed #ddd;
        border-radius: 12px;
        padding: 40px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        background: #f8f9fa;
    }

    .upload-zone:hover {
        border-color: #0d6efd;
        background: #e7f3ff;
    }

    .upload-zone.dragover {
        border-color: #198754;
        background: #d1e7dd;
    }

    .file-preview {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        background: white;
    }

    .info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.penerimaan-permohonan') }}">Penerimaan Permohonan</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-send-check"></i> Kirim Penerimaan Permohonan Akreditasi
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('de.penerimaan-permohonan') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-8 mb-4">
            <!-- Status Surat Penerimaan -->
            @php
            $suratPenerimaan = $pengajuan->dokumen->first();
            @endphp

            @if($suratPenerimaan)
            <!-- Already Sent -->
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-check-circle"></i> Penerimaan Permohonan Akreditasi Telah Terkirim
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success alert-permanent">
                        <i class="bi bi-info-circle"></i>
                        Penerimaan akreditasi telah dikirim pada <strong>{{ $suratPenerimaan->created_at->locale('id')->translatedFormat('d M Y H:i') }}</strong>
                    </div>

                    <div class="file-preview">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center rounded gap-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-pdf text-danger me-3" style="font-size: 48px;"></i>
                                <div>
                                    <strong>{{ $suratPenerimaan->original_filename }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ number_format($suratPenerimaan->file_size / 1024, 2) }} KB
                                    </small>
                                    <br>
                                    @if($suratPenerimaan->keterangan)
                                    <small class="text-info">
                                        <i class="bi bi-info-circle"></i> {{ $suratPenerimaan->keterangan }}
                                    </small>
                                    @endif
                                </div>
                            </div>
                            <div class="btn-group-vertical">
                                <a href="{{ route('de.penerimaan-permohonan.download', $pengajuan->id) }}" class="btn btn-info mb-2">
                                    <i class="bi bi-eye btn-sm"></i> Lihat
                                </a>
                                @if($pengajuan->id_de_assigned == auth()->id())
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete()">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning alert-permanent mt-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Catatan:</strong> Jika ingin mengirim ulang, mohon menghapus file yang ada terlebih dahulu.
                    </div>
                </div>
            </div>
            @else
            <!-- Upload Form -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-upload"></i> Upload File Penerimaan Permohonan Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('de.penerimaan-permohonan.kirim', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" id="formUploadPenerimaan">
                        @csrf

                        <!-- File Upload -->
                        <div class="mb-4">
                            <label for="file_surat_penerimaan" class="form-label required">
                                <i class="bi bi-file-pdf"></i> File Penerimaan Permohonan Akreditasi (PDF)
                            </label>

                            <div class="upload-zone" id="uploadZone">
                                <input type="file" class="d-none @error('file_surat_penerimaan') is-invalid @enderror" id="file_surat_penerimaan" name="file_surat_penerimaan" accept=".pdf" required>

                                <div id="uploadPlaceholder">
                                    <i class="bi bi-cloud-upload" style="font-size: 48px; color: #6c757d;"></i>
                                    <p class="mt-3 mb-1 fw-bold">Silahkan upload file PDF di sini</p>
                                    <p class="text-muted small mb-0">Maksimal 5MB</p>
                                </div>

                                <div id="filePreview" style="display: none;">
                                    <i class="bi bi-file-pdf text-danger" style="font-size: 48px;"></i>
                                    <p class="mt-3 mb-1 fw-bold" id="fileName"></p>
                                    <p class="text-muted small mb-2" id="fileSize"></p>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFile()">
                                        <i class="bi bi-x-circle"></i> Batalkan Upload File
                                    </button>
                                </div>
                            </div>

                            @error('file_surat_penerimaan')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Keterangan -->
                        {{-- <div class="mb-4">
                            <label for="keterangan" class="form-label">
                                <i class="bi bi-chat-left-text"></i> Keterangan
                                <small class="text-muted">(Opsional)</small>
                            </label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="3" placeholder="Tambahkan catatan atau keterangan tambahan jika diperlukan">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                </div> --}}

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('de.penerimaan-permohonan') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary" id="btnSubmit">
                        <i class="bi bi-send"></i> Kirim Penerimaan Akreditasi
                    </button>
                </div>
                </form>
            </div>
        </div>
        @endif

        <div class="card my-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Informasi Penerimaan Permohonan Akreditasi</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th style="width:45%">Tanggal Penerimaan Permohonan</th>
                        <td>: {{ \App\Libraries\Date::tglIndo($pengajuan->tanggal_surat_penerimaan_dikirim) }}</td>
                    </tr>
                    <tr>
                        <th>Status Penerimaan Permohonan</th>
                        <td>: {!! $pengajuan->getCustomBadgeLastStatus('surat_penerimaan_de','de') !!}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Riwayat Status -->
        @php
        $filterStatuses = [
        \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
        \App\Models\PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
        ];

        $logs = $pengajuan->statusLog
        ->whereIn('status_to', $filterStatuses)
        ->sortBy('created_at');
        @endphp

        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history"></i> Riwayat Status
                </h5>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                @if($logs->count() > 0)
                <div class="timeline">
                    @foreach($logs as $log)
                    <div class="timeline-item mb-3">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-circle-fill text-secondary" style="font-size: 8px;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>
                                    {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label_long_for']['de'] ?? $log->status_to }}
                                </strong>
                                <br>
                                <small class="text-muted">{{ $log->created_at->locale('id')->translatedFormat('d M Y H:i') }}</small>

                                {{-- @if($log->keterangan)
                                    <br>
                                    <small class="text-muted fst-italic">{{ $log->keterangan }}</small>
                                @endif --}}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted text-center mb-0">Belum ada riwayat</p>
                @endif
            </div>
        </div>
    </div>
</div>
</div>

<!-- Modal Konfirmasi Delete -->
<div class="modal fade" id="modalConfirmDelete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus file penerimaan permohonan akreditasi ini?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i>
                    File akan dihapus dan Anda perlu mengupload ulang jika diperlukan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="formDelete" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // File upload handling
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('file_surat_penerimaan');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const filePreview = document.getElementById('filePreview');

    // Click to upload
    uploadZone.addEventListener('click', () => {
        if (!fileInput.files.length) {
            fileInput.click();
        }
    });

    // Drag & drop
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });

    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('dragover');
    });

    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');

        const files = e.dataTransfer.files;
        if (files.length) {
            fileInput.files = files;
            handleFileSelect();
        }
    });

    // File input change
    fileInput.addEventListener('change', handleFileSelect);

    function handleFileSelect() {
        const file = fileInput.files[0];

        if (!file) return;

        // Validate file type
        if (file.type !== 'application/pdf') {
            Swal.fire('Perhatian', 'File harus berformat PDF!', 'warning');
            fileInput.value = '';
            return;
        }

        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire('Perhatian', 'Ukuran file maksimal 5MB!', 'warning');
            fileInput.value = '';
            return;
        }

        // Show preview
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = (file.size / 1024).toFixed(2) + ' KB';

        uploadPlaceholder.style.display = 'none';
        filePreview.style.display = 'block';
    }

    function removeFile() {
        fileInput.value = '';
        uploadPlaceholder.style.display = 'block';
        filePreview.style.display = 'none';
    }

    // Form submission
    document.getElementById('formUploadPenerimaan').addEventListener('submit', function(e) {
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
    });

    // Delete confirmation
    function confirmDelete() {
        const modal = new bootstrap.Modal(document.getElementById('modalConfirmDelete'));
        const form = document.getElementById('formDelete');
        form.action = '{{ route("de.penerimaan-permohonan.destroy", $pengajuan->id) }}';
        modal.show();
    }

</script>
@endpush
