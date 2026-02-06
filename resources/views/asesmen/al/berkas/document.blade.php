@extends('layouts.template.app')

@section('title', 'Upload Berita Acara AL - ' . $asesmen->name)

@push('styles')
<style>
    .upload-area {
        border: 2px dashed #198754;
        border-radius: 12px;
        padding: 60px 20px;
        text-align: center;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .upload-area:hover {
        border-color: #146c43;
        background: linear-gradient(135deg, #f0fff5 0%, #ffffff 100%);
        transform: translateY(-2px);
    }

    .upload-area.dragover {
        border-color: #0f5132;
        background: linear-gradient(135deg, #d5f4e6 0%, #ffffff 100%);
    }

    .file-icon {
        font-size: 4rem;
        color: #198754;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="bi bi-file-earmark-pdf"></i> Upload Berita Acara AL
                    </h4>
                    <p class="text-muted mb-0">{{ $asesmen->name }}</p>
                </div>
                <a href="{{ route('al.berkas') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="card-footer bg-white">
            <div class="alert alert-success alert-permanent mb-0">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
            </div>
        </div>
        @endif
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-permanent">
        <div class="fw-semibold mb-2">Terjadi kesalahan:</div>
        <ul class="mb-0">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cloud-upload"></i> Upload File Berita Acara (PDF)
                    </h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('al.berkas.documents.upload', ['id' => $asesmen->id]) }}" enctype="multipart/form-data" id="uploadForm">
                        @csrf

                        <div class="upload-area" id="uploadArea">
                            <input type="file" id="fileInput" name="files[]" accept="application/pdf" class="d-none" multiple required>

                            <div id="uploadPrompt">
                                <i class="bi bi-cloud-arrow-up file-icon"></i>
                                <h5 class="mt-3">Silahkan upload file berita acara di sini</h5>
                                <p class="text-muted mb-0">PDF, ukuran maksimal 5MB</p>
                            </div>

                            <div id="fileInfo" class="d-none">
                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 4rem;"></i>
                                <h6 class="mt-3 mb-1" id="fileCount">-</h6>
                                <small class="text-muted" id="fileNames">-</small>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveFile">
                                        <i class="bi bi-x-circle"></i> Batalkan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-success" id="btnUploadSubmit" disabled>
                                <i class="bi bi-upload"></i> Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-list-ul"></i> Daftar File</h6>
                        <small class="text-muted">Total: {{ $docs->count() }}</small>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if($docs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="55%">Nama</th>
                                    <th width="20%">Upload</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($docs as $i => $doc)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $doc->title }}</div>
                                        <small class="text-muted">{{ $doc->original_name }}</small>
                                    </td>
                                    <td>
                                        @if($doc->uploaded_at)
                                        <small>{{ $doc->uploaded_at->format('d M Y, H:i') }}</small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('al.berkas.documents.preview', ['id' => $asesmen->id, 'docId' => $doc->id]) }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center" target="_blank" title="Lihat File">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            {{-- ✅ Ubah jadi button dengan data attribute --}}
                                            <button type="button" class="btn btn-outline-danger d-flex align-items-center justify-content-center btn-delete-doc" data-doc-id="{{ $doc->id }}" data-doc-name="{{ $doc->title }}" data-url="{{ route('al.berkas.documents.delete', ['id' => $asesmen->id, 'docId' => $doc->id]) }}" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size:64px;color:#ddd;"></i>
                        <p class="text-muted mt-3 mb-0">Belum ada berita acara yang diunggah</p>
                    </div>
                    @endif
                </div>

                @php
                $hasActiveDocs = $docs->where('is_active', true)->count() > 0;
                @endphp

                @if($hasActiveDocs)
                <div class="card-footer bg-white d-flex justify-content-end gap-2">
                    <button class="btn btn-success" id="btnFinalize">
                        <i class="bi bi-check-circle"></i> Finalisasi
                    </button>
                </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">Petunjuk</h6>
                </div>
                <div class="card-body">
                    <ol class="mb-4 small ps-3">
                        <li>
                            Silahkan <strong>download</strong> format berita acara berikut, di mana telah diisi pada saat proses penilaian AL. Bagian pengesahan terdapat pada halaman paling bawah
                            <div class="my-2 text-center">
                                <a href="{{ route('al.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'personal']) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i> Download Format Berita Acara AL
                                </a>
                            </div>
                        </li>
                        <li>Mohon lakukan <strong>pengesahan</strong> berita acara tersebut bersama dengan prodi</li>
                        <li>Lalu <strong>upload</strong> berita acara yang telah disahkan tersebut dalam bentuk PDF</li>
                        <li>Anda masih dapat mengedit file berita acara tersebut ketika belum difinalisasi</li>
                        <li>Setelah upload selesai, segera lakukan <strong>finalisasi</strong> dan kirim</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var uploadArea = document.getElementById('uploadArea');
        var fileInput = document.getElementById('fileInput');
        var uploadPrompt = document.getElementById('uploadPrompt');
        var fileInfo = document.getElementById('fileInfo');
        var fileCount = document.getElementById('fileCount');
        var fileNames = document.getElementById('fileNames');
        var btnRemoveFile = document.getElementById('btnRemoveFile');
        var btnUploadSubmit = document.getElementById('btnUploadSubmit');
        var uploadForm = document.getElementById('uploadForm');

        function resetFile() {
            fileInput.value = '';
            uploadPrompt.classList.remove('d-none');
            fileInfo.classList.add('d-none');
            btnUploadSubmit.disabled = true;
        }

        function handleFileSelect() {
            var files = fileInput.files;
            if (!files || files.length === 0) return;

            for (var i = 0; i < files.length; i++) {
                if (files[i].type !== 'application/pdf') {
                    alert('Semua file harus PDF');
                    resetFile();
                    return;
                }
                if (files[i].size > 20 * 1024 * 1024) {
                    alert('Ukuran file maksimal 20MB per file');
                    resetFile();
                    return;
                }
            }

            fileCount.textContent = files.length + ' file dipilih';
            var names = [];
            for (var j = 0; j < files.length; j++) names.push(files[j].name);
            fileNames.textContent = names.join(', ');

            uploadPrompt.classList.add('d-none');
            fileInfo.classList.remove('d-none');
            btnUploadSubmit.disabled = false;
        }

        uploadArea.addEventListener('click', function(e) {
            if (btnRemoveFile && (e.target === btnRemoveFile || btnRemoveFile.contains(e.target))) return;
            fileInput.click();
        });

        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');

            var files = e.dataTransfer.files;
            if (files && files.length > 0) {
                fileInput.files = files;
                handleFileSelect();
            }
        });

        fileInput.addEventListener('change', handleFileSelect);

        btnRemoveFile.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            resetFile();
        });

        // ✅ AJAX FORM SUBMIT
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(uploadForm);

            btnUploadSubmit.disabled = true;
            btnUploadSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';

            fetch("{{ route('al.berkas.documents.upload', ['id' => $asesmen->id]) }}", {
                    method: 'POST'
                    , body: formData
                    , headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        , 'Accept': 'application/json'
                    }
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        alert(data.message || 'File berhasil diupload!');
                        window.location.reload();
                    } else {
                        alert(data.message || 'Gagal upload file');
                        btnUploadSubmit.disabled = false;
                        btnUploadSubmit.innerHTML = '<i class="bi bi-upload"></i> Upload';
                    }
                })
                .catch(function(error) {
                    console.error('Upload error:', error);
                    alert('Terjadi kesalahan saat upload file');
                    btnUploadSubmit.disabled = false;
                    btnUploadSubmit.innerHTML = '<i class="bi bi-upload"></i> Upload';
                });
        });

        // ✅ DELETE DOCUMENT HANDLER
        document.querySelectorAll('.btn-delete-doc').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var docName = this.getAttribute('data-doc-name');
                var deleteUrl = this.getAttribute('data-url');

                if (!confirm('Apakah Anda yakin ingin menghapus dokumen "' + docName + '"?')) {
                    return;
                }

                // Disable button
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                fetch(deleteUrl, {
                        method: 'DELETE'
                        , headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            , 'Accept': 'application/json'
                            , 'Content-Type': 'application/json'
                        }
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            alert(data.message || 'Dokumen berhasil dihapus');
                            window.location.reload();
                        } else {
                            alert(data.message || 'Gagal menghapus dokumen');
                            window.location.reload();
                        }
                    })
                    .catch(function(error) {
                        console.error('Delete error:', error);
                        alert('Terjadi kesalahan saat menghapus dokumen');
                        window.location.reload();
                    });
            });
        });

        // ✅ FINALIZE BUTTON
        var btnFinalize = document.getElementById('btnFinalize');
        if (btnFinalize) {
            btnFinalize.addEventListener('click', function() {
                if (!confirm('Apakah Anda yakin ingin finalisasi berita acara?\n\nSetelah difinalisasi, dokumen akan dikirim ke Program Studi untuk peninjauan.')) {
                    return;
                }

                btnFinalize.disabled = true;
                btnFinalize.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

                fetch("{{ route('al.berkas.documents.finalize', ['id' => $asesmen->id]) }}", {
                        method: 'POST'
                        , headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            , 'Accept': 'application/json'
                            , 'Content-Type': 'application/json'
                        }
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            alert(data.message || 'Berita acara berhasil difinalisasi!');
                            window.location.href = "{{ route('al.berkas', $asesmen->id) }}";
                        } else {
                            alert(data.message || 'Gagal finalisasi');
                            btnFinalize.disabled = false;
                            btnFinalize.innerHTML = '<i class="bi bi-check-circle"></i> Finalisasi';
                        }
                    })
                    .catch(function(error) {
                        console.error('Finalize error:', error);
                        alert('Terjadi kesalahan saat finalisasi');
                        btnFinalize.disabled = false;
                        btnFinalize.innerHTML = '<i class="bi bi-check-circle"></i> Finalisasi';
                    });
            });
        }
    });

</script>
@endpush
@endsection
