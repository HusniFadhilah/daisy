@push('styles')
<style>
    .upload-area-modal {
        border: 2px dashed #ced4da;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .upload-area-modal:hover {
        border-color: #ffc107;
        background: #fff3cd;
    }

    .upload-area-modal.dragover {
        border-color: #28a745;
        background: #d4edda;
    }

</style>
@endpush

@php
$draftBorang = $pengajuan->dokumen->where('jenis_dokumen', 'draft_borang')->where('is_latest', true)->first();
$latestImport = $pengajuan->latestBorangImport;
@endphp

<div class="card border-success mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <i class="bi bi-file-earmark-code"></i> Proses & Preview Data Borang
        </h5>
    </div>
    <div class="card-body">
        @if($latestImport)
        <!-- Already Processed -->
        <div class="alert alert-success alert-permanent">
            <i class="bi bi-check-circle"></i>
            <strong>Borang Anda sudah diproses!</strong>
            <br>
            <small class="text-muted">
                Terakhir diproses: {{ $latestImport->imported_at->diffForHumans() }}
            </small>
        </div>

        <div class="processing-stats mb-3">
            <div class="row">
                <div class="col-3 stat-item">
                    <h4>{{ $latestImport->total_sections }}</h4>
                    <small>Bagian</small>
                </div>
                <div class="col-3 stat-item">
                    <h4>{{ $latestImport->total_tables }}</h4>
                    <small>Total Tabel</small>
                </div>
                <div class="col-3 stat-item">
                    <h4>{{ $latestImport->parsed_tables }}</h4>
                    <small>Terproses</small>
                </div>
                <div class="col-3 stat-item">
                    <h4>{{ $latestImport->completion_percentage }}%</h4>
                    <small>Kelengkapan</small>
                </div>
            </div>
        </div>

        @if($latestImport->status === 'failed')
        <div class="alert alert-danger alert-permanent">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Pembacaan Data Gagal!</strong><br>
            {{ $latestImport->parsing_notes }}
        </div>
        @endif

        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('pengajuan.borang-preview', $pengajuan->id) }}" class="btn btn-primary" target="_blank">
                <i class="bi bi-eye"></i> Lihat Preview HTML
            </a>

            <!-- 🆕 TOMBOL UPLOAD ULANG -->
            <button type="button" class="btn btn-outline-warning" onclick="showUploadUlangModal()">
                <i class="bi bi-arrow-repeat"></i> Upload Ulang Dokumen
            </button>

            <button type="button" class="btn btn-outline-success" onclick="processBorang({{ $pengajuan->id }})">
                <i class="bi bi-gear"></i> Proses Ulang Data
            </button>

            @if($latestImport->parsing_errors)
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalProcessingErrors">
                <i class="bi bi-bug"></i> Lihat Error Detail
            </button>
            @endif
        </div>

        @elseif($draftBorang)
        <!-- Not Processed Yet -->
        <div class="alert alert-info alert-permanent">
            <i class="bi bi-info-circle"></i>
            <strong>Draft LED Anda sudah diupload!</strong><br>
            File: <strong>{{ $draftBorang->original_filename }}</strong> ({{ $draftBorang->file_size_formatted }})
        </div>

        <div class="card bg-light mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-gear"></i> Apa yang dilakukan oleh sistem?
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <ol class="mb-0">
                            <li>Membaca struktur dokumen DOCX</li>
                            <li>Mendeteksi bagian (D.1, E.1, dll)</li>
                            <li>Mengekstrak tabel setelah marker</li>
                        </ol>
                    </div>
                    <div class="col-md-6">
                        <ol start="4" class="mb-0">
                            <li>Mapping ke master elemen & dataset</li>
                            <li>Validasi struktur data</li>
                            <li>Generate preview HTML</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-success" onclick="processBorang({{ $pengajuan->id }})" id="btnProcessBorang">
                <i class="bi bi-gear"></i> Proses & Validasi Borang
            </button>

            <!-- 🆕 TOMBOL UPLOAD ULANG -->
            <button type="button" class="btn btn-outline-warning" onclick="showUploadUlangModal()">
                <i class="bi bi-arrow-repeat"></i> Upload Ulang Dokumen
            </button>
        </div>

        <div id="processResult" class="mt-3" style="display: none;"></div>
        @else
        <div class="alert alert-warning alert-permanent">
            <i class="bi bi-exclamation-triangle"></i>
            Upload draft LED terlebih dahulu untuk melakukan pembacaan data.
        </div>
        @endif
    </div>
</div>

<!-- 🆕 MODAL UPLOAD ULANG -->
<div class="modal fade" id="modalUploadUlang" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat"></i> Upload Ulang Draft LED
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
                        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3" placeholder="Contoh: Revisi data mahasiswa tahun 2023, Perbaikan tabel E.1.1" required></textarea>
                        <small class="text-muted">Jelaskan perubahan yang dilakukan</small>
                        @error('keterangan')
                        <span class="invalid-feedback" role="alert">
                            {{ $message }}
                        </span>
                        @enderror
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

<div class="modal fade" id="uploadDraftModal" tabindex="-1" aria-labelledby="uploadDraftLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="uploadDraftLabel">
                    <i class="bi bi-upload"></i> Upload Draft Laporan Evaluasi Diri
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="uploadDraftForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    {{-- Instructions --}}
                    <div class="alert alert-info alert-permanent mb-3">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle"></i> Petunjuk Upload
                        </h6>
                        <ol class="mb-0 small">
                            <li>File harus berformat <strong>Microsoft Word (.docx)</strong></li>
                            <li>Gunakan template yang sudah dikirim oleh Desk Evaluator</li>
                            <li><strong>Jangan ubah</strong> struktur dokumen, kode elemen, atau format tabel</li>
                            <li>Isi bagian yang bertanda <strong>"Mohon isi di sini"</strong></li>
                            <li>Maksimal ukuran file: <strong>10MB</strong></li>
                        </ol>
                    </div>

                    {{-- File Upload Area --}}
                    <div class="mb-3">
                        <label for="draftBorangFile" class="form-label fw-semibold">
                            Pilih File Borang <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" id="draftBorangFile" name="draft_borang" accept=".docx" required>
                        <div class="invalid-feedback">
                            Mohon pilih file DOCX terlebih dahulu
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div class="mb-3">
                        <label for="keteranganUpload" class="form-label fw-semibold">
                            Keterangan <small class="text-muted">(Opsional)</small>
                        </label>
                        <textarea class="form-control" id="keteranganUpload" name="keterangan" rows="3" placeholder="Contoh: Upload versi 1 - revisi berdasarkan masukan DE"></textarea>
                        <small class="text-muted">
                            <i class="bi bi-lightbulb"></i>
                            Tambahkan catatan untuk memudahkan tracking versi
                        </small>
                    </div>

                    {{-- File Info Display --}}
                    <div id="fileInfoUpload" class="alert alert-secondary d-none">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-file-earmark-word text-primary"></i>
                                <strong>File dipilih:</strong>
                                <span id="fileNameUpload">-</span>
                            </div>
                            <div>
                                <small class="text-muted">
                                    Ukuran: <span id="fileSizeUpload">-</span>
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Upload Progress (hidden initially) --}}
                    <div id="uploadProgress" class="d-none">
                        <div class="mb-2">
                            <strong>Progress Upload:</strong>
                            <span id="uploadProgressText">0%</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">
                                0%
                            </div>
                        </div>
                    </div>

                    {{-- Alert --}}
                    <div id="uploadAlert" class="alert d-none mt-3" role="alert"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnCancelUpload">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitUpload">
                        <i class="bi bi-upload"></i> Upload Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uploadForm = document.getElementById('uploadDraftForm');
        const fileInput = document.getElementById('draftBorangFile');
        const fileInfo = document.getElementById('fileInfoUpload');
        const fileName = document.getElementById('fileNameUpload');
        const fileSize = document.getElementById('fileSizeUpload');
        const btnSubmitUpload = document.getElementById('btnSubmitUpload');
        const btnCancel = document.getElementById('btnCancelUpload');
        const uploadProgress = document.getElementById('uploadProgress');
        const uploadAlert = document.getElementById('uploadAlert');

        // File input change handler
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                // Validate file type
                const fileExtension = file.name.split('.').pop().toLowerCase();
                if (fileExtension !== 'docx') {
                    showUploadAlert('danger', 'Format file tidak valid! Harus .docx');
                    fileInput.value = '';
                    fileInfo.classList.add('d-none');
                    return;
                }

                // Validate file size (10MB max)
                if (file.size > 10 * 1024 * 1024) {
                    showUploadAlert('danger', 'Ukuran file terlalu besar! Maksimal 10MB');
                    fileInput.value = '';
                    fileInfo.classList.add('d-none');
                    return;
                }

                // Display file info
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                fileInfo.classList.remove('d-none');
                uploadAlert.classList.add('d-none');
            } else {
                fileInfo.classList.add('d-none');
            }
        });

        // Form submit handler
        uploadForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const file = fileInput.files[0];
            if (!file) {
                fileInput.classList.add('is-invalid');
                showUploadAlert('danger', 'Mohon pilih file terlebih dahulu!');
                return;
            }

            const formData = new FormData(uploadForm);

            // Disable buttons
            btnSubmitUpload.disabled = true;
            btnCancel.disabled = true;
            btnSubmitUpload.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';

            // Show progress
            uploadProgress.classList.remove('d-none');
            uploadAlert.classList.add('d-none');

            try {
                const response = await fetch('{{ route("pengajuan.upload-draft", $pengajuan->id) }}', {
                    method: 'POST'
                    , body: formData
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Update progress to 100%
                    updateUploadProgress(100);

                    await Swal.fire({
                        icon: 'success'
                        , title: 'Upload Berhasil!'
                        , html: `
                        <p>${data.message}</p>
                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="bi bi-info-circle"></i>
                                File: <strong>${data.data.filename}</strong><br>
                                Ukuran: <strong>${data.data.file_size}</strong><br>
                                Versi: <strong>${data.data.versi}</strong>
                            </small>
                        </div>
                    `
                        , confirmButtonColor: '#28a745'
                    });

                    // Close modal and reload
                    const modal = bootstrap.Modal.getInstance(document.getElementById('uploadDraftModal'));
                    modal.hide();
                    window.location.reload();

                } else {
                    throw new Error(data.message || 'Upload gagal');
                }

            } catch (error) {
                console.error('Upload error:', error);
                showUploadAlert('danger', error.message);

                // Re-enable buttons
                btnSubmitUpload.disabled = false;
                btnCancel.disabled = false;
                btnSubmitUpload.innerHTML = '<i class="bi bi-upload"></i> Upload Sekarang';
                uploadProgress.classList.add('d-none');
            }
        });

        function updateUploadProgress(percentage) {
            const progressBar = document.getElementById('uploadProgressBar');
            const progressText = document.getElementById('uploadProgressText');

            progressBar.style.width = percentage + '%';
            progressBar.textContent = percentage + '%';
            progressText.textContent = percentage + '%';

            if (percentage === 100) {
                progressBar.classList.remove('progress-bar-animated');
                progressBar.classList.add('bg-success');
            }
        }

        function showUploadAlert(type, message) {
            uploadAlert.className = `alert alert-${type}`;
            uploadAlert.innerHTML = `<i class="bi bi-${type === 'danger' ? 'exclamation-triangle' : 'info-circle'}"></i> ${message}`;
            uploadAlert.classList.remove('d-none');
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    });

    // 🆕 UPLOAD ULANG FUNCTIONS
    let modalUploadUlang;

    function showUploadUlangModal() {
        modalUploadUlang = new bootstrap.Modal(document.getElementById('modalUploadUlang'));
        modalUploadUlang.show();
    }

    // Modal Upload Handling
    const uploadAreaModal = document.getElementById('uploadAreaModal');
    const fileInputModal = document.getElementById('inputDraftBorangUlang');
    const filePreviewModal = document.getElementById('filePreviewModal');
    const btnSubmitUlang = document.getElementById('btnSubmitUlang');

    if (fileInputModal) {
        // Click to upload
        if (uploadAreaModal) uploadAreaModal.addEventListener('click', (e) => {
            if (e.target !== uploadAreaModal && e.target.closest('.btn')) return;
            fileInputModal.click();
        });

        // File selected
        fileInputModal.addEventListener('change', (e) => {
            handleFileSelectModal(e.target.files[0]);
        });

        // Drag & Drop
        if (uploadAreaModal) uploadAreaModal.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadAreaModal.classList.add('dragover');
        });

        if (uploadAreaModal) uploadAreaModal.addEventListener('dragleave', () => {
            uploadAreaModal.classList.remove('dragover');
        });

        if (uploadAreaModal) uploadAreaModal.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadAreaModal.classList.remove('dragover');

            const file = e.dataTransfer.files[0];
            if (file && file.name.endsWith('.docx')) {
                fileInputModal.files = e.dataTransfer.files;
                handleFileSelectModal(file);
            } else {
                alert('Hanya file DOCX yang diperbolehkan!');
            }
        });
    }

    function handleFileSelectModal(file) {
        if (!file) return;

        if (!file.name.endsWith('.docx')) {
            alert('Hanya file DOCX yang diperbolehkan!');
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert('Ukuran file maksimal 10 MB!');
            return;
        }

        document.getElementById('fileNameModal').textContent = file.name;
        document.getElementById('fileSizeModal').textContent = formatFileSize(file.size);

        uploadAreaModal.classList.add('d-none');
        filePreviewModal.classList.remove('d-none');
        filePreviewModal.classList.add('has-file');
        btnSubmitUlang.disabled = false;
    }

    function removeFileModal() {
        fileInputModal.value = '';
        uploadAreaModal.classList.remove('d-none');
        filePreviewModal.classList.add('d-none');
        filePreviewModal.classList.remove('has-file');
        btnSubmitUlang.disabled = true;
    }

    async function submitUploadUlang() {
        const formData = new FormData(document.getElementById('formUploadUlang'));
        const keterangan = formData.get('keterangan');

        if (!keterangan || keterangan.trim() === '') {
            alert('Alasan upload ulang harus diisi!');
            return;
        }

        if (!fileInputModal.files[0]) {
            alert('Pilih file terlebih dahulu!');
            return;
        }

        btnSubmitUlang.disabled = true;
        btnSubmitUlang.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';

        try {
            const response = await fetch('{{ route("pengajuan.upload-draft", $pengajuan->id) }}', {
                method: 'POST'
                , body: formData
                , headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    , 'Accept': 'application/json'
                    , 'X-Requested-With': 'XMLHttpRequest'
                , }
            });

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Server error (HTML response):', text.substring(0, 500));
                throw new Error('Server error. Periksa console untuk detail.');
            }

            const data = await response.json();

            if (response.ok && data.success) {
                modalUploadUlang.hide();
                alert('✅ ' + data.message);
                setTimeout(() => window.location.reload(), 500);
            } else {
                throw new Error(data.message || 'Upload gagal');
            }

        } catch (error) {
            console.error('Upload error:', error);
            alert('❌ ' + (error.message || 'Terjadi kesalahan saat upload'));
            btnSubmitUlang.disabled = false;
            btnSubmitUlang.innerHTML = '<i class="bi bi-upload"></i> Upload Versi Baru';
        }
    }

    // Reset modal on close
    if (modalUploadUlang) modalUploadUlang.addEventListener('hidden.bs.modal', function() {
        document.getElementById('formUploadUlang').reset();
        removeFileModal();
    });

</script>
@endpush
