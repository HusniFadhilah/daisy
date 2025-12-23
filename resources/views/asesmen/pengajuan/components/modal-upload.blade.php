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
            <strong>Draft borang Anda sudah diupload!</strong><br>
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
            Upload draft borang terlebih dahulu untuk melakukan pembacaan data.
        </div>
        @endif
    </div>
</div>

<!-- 🆕 MODAL UPLOAD ULANG -->
<div class="modal fade" id="modalUploadUlang" tabindex="-1">
    <div class="modal-dialog modal-lg">
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

@push('scripts')
<script>
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
