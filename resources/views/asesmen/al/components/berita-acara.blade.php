@if(!$isFinalized)
<div class="alert alert-info alert-permanent alert-dismissible mb-3">
    <i class="bi bi-info-circle me-2"></i>
    Silahkan finalisasi Hasil dan Berita Acara Asesmen Lapangan (AL) dengan klik tombol "Finalisasi Berita Acara" di bawah ini
</div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-folder2-open"></i> Hasil dan Berita Acara Asesmen Lapangan (AL)
        </h5>
        @if(!$isFinalized)
        <button type="button" class="btn btn-primary btn-sm" onclick="handleFinalize(event)">
            <i class="bi bi-check-circle-fill me-2"></i> Finalisasi Berita Acara
        </button>
        @else
        <div class="badge bg-success">
            <i class="bi bi-check-circle-fill"></i> Berita Acara sudah difinalisasi
        </div>
        @endif
    </div>

    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Skeleton Loading --}}
        <div id="skeletonLoading">
            <div class="alert alert-info alert-permanent placeholder-glow">
                <span class="placeholder col-12"></span>
                <span class="placeholder col-10"></span>
            </div>
            <div class="row g-2 align-items-end">
                <div class="col-md-8">
                    <div class="placeholder-glow">
                        <span class="placeholder col-6 mb-2"></span>
                        <span class="placeholder col-12" style="height: 38px;"></span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="placeholder-glow">
                        <span class="placeholder col-12" style="height: 38px;"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Upload Section - akan ditampilkan jika tidak ada file --}}
        <div id="uploadSection" style="display: none;">
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-info-circle"></i>
                Upload file Hasil dan Berita Acara Asesmen Lapangan (AL) (PDF).
                Format file yang harus ditanda-tangani dapat didownload pada
                <a href="{{ route('al.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'personal']) }}" class="alert-link">
                    link ini
                </a>.
            </div>

            <form id="uploadForm" action="{{ route('al.berkas.documents.upload', $asesmen->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Upload file Hasil dan Berita Acara Asesmen Lapangan (AL) (PDF)</label>
                        <input type="file" name="files[]" class="form-control" accept="application/pdf" multiple required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-upload"></i> Upload
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- File List Section - akan ditampilkan jika sudah ada file --}}
        <div id="fileListSection" style="display: none;">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-list-ul"></i> Daftar Berita Acara
            </h6>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th>Nama File</th>
                            <th style="width: 15%">Ukuran</th>
                            <th style="width: 20%">Tanggal Upload</th>
                            <th class="text-end" style="width: 20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="fileTableBody">
                        {{-- Akan diisi via JavaScript --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Delete --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus dokumen <strong id="deleteFileName"></strong>?</p>
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        Tindakan ini tidak dapat dibatalkan!
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const asesmenId = "{{ $asesmen->id }}";
    const isFinalized = "{{ $isFinalized }}";
    const listUrl = "{{ route('al.berkas.documents.list', $asesmen->id) }}";
    const finalizeUrl = "{{ route('al.berkas.documents.finalize', $asesmen->id) }}";

    // Load files saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        loadFiles();

        // Handle form submit
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Show loading on submit button
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';

            fetch(this.action, {
                    method: 'POST'
                    , body: formData
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;

                    if (data.success) {
                        // Reload files
                        loadFiles();
                        // Reset form
                        this.reset();
                        // Show success message
                        showAlert('success', data.message || 'File berhasil diupload');
                    } else {
                        showAlert('danger', data.message || 'Gagal upload file');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    showAlert('danger', 'Terjadi kesalahan saat upload file');
                });
        });
    });

    function loadFiles() {
        // Show skeleton, hide others
        document.getElementById('skeletonLoading').style.display = 'block';
        document.getElementById('uploadSection').style.display = 'none';
        document.getElementById('fileListSection').style.display = 'none';

        fetch(listUrl)
            .then(response => response.json())
            .then(data => {
                // Hide skeleton
                document.getElementById('skeletonLoading').style.display = 'none';

                if (data.success && data.data.length > 0) {
                    // Ada file, tampilkan list
                    document.getElementById('uploadSection').style.display = 'none';
                    document.getElementById('fileListSection').style.display = 'block';
                    renderFiles(data.data);
                } else {
                    // Tidak ada file, tampilkan upload form
                    document.getElementById('uploadSection').style.display = 'block';
                    document.getElementById('fileListSection').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Hide skeleton on error
                document.getElementById('skeletonLoading').style.display = 'none';
                // Show upload form as fallback
                document.getElementById('uploadSection').style.display = 'block';
                showAlert('danger', 'Gagal memuat data. Silakan refresh halaman.');
            });
    }

    function renderFiles(files) {
        const tbody = document.getElementById('fileTableBody');
        tbody.innerHTML = '';

        files.forEach((file, index) => {
            const row = `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                    <strong>${file.title}</strong>
                    <br>
                    <small class="text-muted">oleh ${file.uploader_name}</small>
                </td>
                <td>
                    <small class="text-muted">${file.size_formatted}</small>
                </td>
                <td>
                    <small class="text-muted">${file.uploaded_at}</small>
                </td>
                <td class="text-end">
                    <a href="${file.download_url}"
                       class="btn btn-sm btn-outline-primary"
                       title="Download">
                        <i class="bi bi-download"></i> Download
                    </a>

                    <button type="button"
                            class="btn btn-sm btn-outline-danger"
                            onclick="confirmDelete(${file.id}, '${escapeHtml(file.title)}', '${file.delete_url}')"
                            title="Hapus" ${isFinalized ? 'disabled':''}>
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
            tbody.innerHTML += row;
        });
    }

    function confirmDelete(fileId, fileName, deleteUrl) {
        document.getElementById('deleteFileName').textContent = fileName;
        document.getElementById('deleteForm').action = deleteUrl;

        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();

        // Handle delete form submit
        document.getElementById('deleteForm').onsubmit = function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menghapus...';

            fetch(deleteUrl, {
                    method: 'DELETE'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;

                    if (data.success) {
                        modal.hide();
                        loadFiles();
                        showAlert('success', data.message || 'File berhasil dihapus');
                    } else {
                        showAlert('danger', data.message || 'Gagal menghapus file');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    showAlert('danger', 'Terjadi kesalahan saat menghapus file');
                });
        };
    }

    function handleFinalize(event) {
        event.preventDefault();

        Swal.fire({
            title: 'Finalisasi Berita Acara?'
            , text: 'Setelah difinalisasi, data tidak dapat diubah.'
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonText: 'Ya, Finalisasi'
            , cancelButtonText: 'Batal'
            , confirmButtonColor: '#198754'
            , cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (!result.isConfirmed) return;

            const btn = event.target;
            const originalText = btn.innerHTML;

            // Loading button
            btn.disabled = true;
            btn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

            fetch(finalizeUrl, {
                    method: 'POST'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                        , 'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;

                    if (data.success) {
                        Swal.fire({
                            icon: 'success'
                            , title: 'Berhasil'
                            , text: data.message
                            , timer: 2000
                            , showConfirmButton: false
                        });

                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        Swal.fire({
                            icon: 'error'
                            , title: 'Gagal'
                            , text: data.message || 'Gagal memfinalisasi dokumen'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.disabled = false;
                    btn.innerHTML = originalText;

                    Swal.fire({
                        icon: 'error'
                        , title: 'Error'
                        , text: 'Terjadi kesalahan saat memfinalisasi dokumen'
                    });
                });
        });
    }

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

        document.querySelector('.card-body').insertBefore(alertDiv, document.querySelector('.card-body').firstChild);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;'
            , '<': '&lt;'
            , '>': '&gt;'
            , '"': '&quot;'
            , "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

</script>

<style>
    /* Skeleton Loading Animation */
    .placeholder {
        display: inline-block;
        min-height: 1em;
        vertical-align: middle;
        cursor: wait;
        background-color: currentColor;
        opacity: 0.2;
    }

    .placeholder-glow .placeholder {
        animation: placeholder-glow 2s ease-in-out infinite;
    }

    @keyframes placeholder-glow {
        50% {
            opacity: 0.1;
        }
    }

    .alert-permanent {
        margin-bottom: 1rem;
    }

</style>
