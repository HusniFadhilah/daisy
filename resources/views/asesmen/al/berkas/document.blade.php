{{-- resources/views/asesmen/al/berkas/document.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Upload Berita Acara AL - ' . $asesmen->name)

@section('content')
<div class="container-fluid py-3">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('al.berkas') }}">Berkas AL</a></li>
            <li class="breadcrumb-item active">Upload Berita Acara</li>
        </ol>
    </nav>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="bi bi-file-earmark-pdf"></i> Upload Berita Acara AL
                    </h4>
                    <p class="text-muted mb-0">{{ $asesmen->getName(false) }}</p>
                </div>
                <a href="{{ route('al.berkas') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        {{-- ✅ ALERT: Info Uploader --}}
        @if($firstUpload)
        <div class="card-footer bg-white">
            @if($isUploader)
            <div class="alert alert-success alert-permanent mb-0">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <i class="bi bi-check-circle fs-4 me-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-2">
                            <i class="bi bi-person-check"></i> Anda adalah Asesor yang Mengupload Berita Acara
                        </h6>
                        <p class="mb-0">
                            Anda dapat menambah, mengedit, atau menghapus file berita acara sampai proses finalisasi dilakukan.
                        </p>
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> Pertama kali diupload: {{ $firstUpload->uploaded_at->locale('id')->translatedFormat('d M Y, H:i') }}
                        </small>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-info alert-permanent mb-0">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <i class="bi bi-info-circle fs-4 me-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-2">
                            <i class="bi bi-file-earmark-check"></i> Berita Acara Telah Diupload
                        </h6>
                        <p class="mb-1">
                            Berita acara telah diupload oleh asesor: <strong>{{ $firstUpload->uploader->name ?? 'Asesor' }}</strong>
                        </p>
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> Diupload pada: {{ $firstUpload->uploaded_at->locale('id')->translatedFormat('d M Y, H:i') }}
                        </small>
                        <hr class="my-2">
                        <p class="mb-0 small text-muted">
                            <i class="bi bi-lock"></i> Hanya asesor yang pertama kali mengupload yang dapat mengedit atau menambah dokumen.
                            Anda dapat melihat dan mengunduh dokumen yang telah diupload.
                        </p>
                    </div>
                </div>
            </div>
            @endif
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
            {{-- ✅ UPLOAD CARD - Disabled jika bukan uploader --}}
            <div class="card {{ !$canUpload ? 'border-secondary' : '' }}">
                <div class="card-header {{ $canUpload ? 'bg-success' : 'bg-secondary' }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cloud-upload"></i> Upload File Berita Acara (PDF)
                        @if(!$canUpload)
                        <span class="badge bg-light text-dark ms-2">Dinonaktifkan</span>
                        @endif
                    </h5>
                </div>

                <div class="card-body">
                    @if(!$canUpload)
                    <div class="alert alert-warning alert-permanent">
                        <i class="bi bi-lock"></i>
                        <strong>Upload Dinonaktifkan</strong><br>
                        Berita acara sudah diupload oleh asesor lain. Hanya asesor yang pertama mengupload yang dapat menambah atau mengedit dokumen.
                    </div>
                    @else
                    <form method="POST" action="{{ route('al.berkas.documents.upload', ['id' => $asesmen->id]) }}" enctype="multipart/form-data" id="uploadForm">
                        @csrf

                        <div class="upload-area" id="uploadArea" style="border: 2px dashed #198754; border-radius: 12px; padding: 60px 20px; text-align: center; background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); cursor: pointer; transition: all 0.3s ease;">
                            <input type="file" id="fileInput" name="files[]" accept="application/pdf" class="d-none" multiple required>

                            <div id="uploadPrompt">
                                <i class="bi bi-cloud-arrow-up" style="font-size: 4rem; color: #198754;"></i>
                                <h5 class="mt-3">Silahkan upload file berita acara di sini</h5>
                                <p class="text-muted mb-0">PDF, ukuran maksimal 5MB per file</p>
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
                    @endif
                </div>
            </div>

            {{-- DAFTAR FILE --}}
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
                                    <th width="45%">Nama File</th>
                                    <th width="20%">Diupload Oleh</th>
                                    <th width="20%">Waktu Upload</th>
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
                                        <small>
                                            <i class="bi bi-person"></i>
                                            {{ $doc->uploader->name ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($doc->uploaded_at)
                                        <small>{{ $doc->uploaded_at->locale('id')->translatedFormat('d M Y, H:i') }}</small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('al.berkas.documents.preview', ['id' => $asesmen->id, 'docId' => $doc->id]) }}" class="btn btn-outline-primary" target="_blank" title="Lihat File">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            {{-- ✅ Tombol hapus hanya muncul jika user adalah uploader --}}
                                            @if($isUploader && $doc->uploaded_by == Auth::id())
                                            <button type="button" class="btn btn-outline-danger btn-delete-doc" data-doc-id="{{ $doc->id }}" data-doc-name="{{ $doc->title }}" data-url="{{ route('al.berkas.documents.delete', ['id' => $asesmen->id, 'docId' => $doc->id]) }}" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif
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
                $hasActiveDocs = $docs->where('status_persetujuan_de', 'approved')->count() > 0;
                @endphp
                {{-- ✅ Finalisasi hanya bisa dilakukan oleh uploader --}}
                @if(!$hasActiveDocs && $isUploader)
                <div class="card-footer bg-white d-flex justify-content-end gap-2">
                    <button class="btn btn-success" id="btnFinalize">
                        <i class="bi bi-check-circle"></i> Finalisasi dan Kirim
                    </button>
                </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Petunjuk
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-light alert-permanent border mb-3">
                        <h6 class="fw-bold mb-2">
                            <i class="bi bi-lightbulb"></i> Informasi Penting
                        </h6>
                        <ul class="mb-0 small ps-3">
                            <li class="mb-2">
                                <strong>Hanya satu asesor</strong> yang dapat mengupload berita acara
                            </li>
                            <li class="mb-2">
                                Asesor yang <strong>pertama kali mengupload</strong> memiliki akses penuh untuk mengedit dan menghapus dokumen
                            </li>
                            <li>
                                Asesor lain dapat <strong>melihat dan mengunduh</strong> dokumen, tetapi tidak dapat mengedit
                            </li>
                        </ul>
                    </div>

                    <ol class="mb-4 small ps-3">
                        <li class="mb-2">
                            Download format berita acara yang telah terisi dari proses penilaian AL
                            <div class="my-2 text-center">
                                <a href="{{ route('al.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'personal']) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i> Download Format Berita Acara
                                </a>
                            </div>
                        </li>
                        <li class="mb-2">Lakukan <strong>pengesahan</strong> berita acara bersama dengan prodi</li>
                        <li class="mb-2">Upload berita acara yang telah disahkan dalam bentuk <strong>PDF</strong></li>
                        <li class="mb-2">File dapat diedit sebelum difinalisasi</li>
                        <li>Setelah selesai, klik <strong>Finalisasi dan Kirim</strong></li>
                    </ol>
                </div>
            </div>

            {{-- ✅ Info Asesor Team --}}
            @php
            $asesorTeam = \App\Models\AsesmenUserRole::where('id_asesmen', $asesmen->id)
            ->where('jenis_asesmen', 'al')
            ->whereHas('role', function ($q) {
            $q->where('name', 'asesor');
            })
            ->with('user')
            ->get();
            @endphp

            @if($asesorTeam->count() > 0)
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-people"></i> Tim Asesor AL
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        @foreach($asesorTeam as $asesor)
                        <tr>
                            <td width="40">
                                @if($firstUpload && $firstUpload->uploaded_by == $asesor->id_user)
                                <i class="bi bi-person-check-fill text-success" title="Uploader"></i>
                                @else
                                <i class="bi bi-person"></i>
                                @endif
                            </td>
                            <td>
                                {{ $asesor->user->name ?? '-' }}
                                @if($firstUpload && $firstUpload->uploaded_by == $asesor->id_user)
                                <span class="badge bg-success ms-1">Telah Mengupload</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canUpload = @json($canUpload);

        // Jika tidak bisa upload, disable semua fungsi upload
        if (!canUpload) {
            console.log('Upload disabled - not the uploader');
            return;
        }

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

        if (uploadArea) {
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
        }

        if (fileInput) fileInput.addEventListener('change', handleFileSelect);

        if (btnRemoveFile) {
            btnRemoveFile.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                resetFile();
            });
        }

        // AJAX FORM SUBMIT
        if (uploadForm) {
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
        }

        // DELETE DOCUMENT HANDLER
        document.querySelectorAll('.btn-delete-doc').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var docName = this.getAttribute('data-doc-name');
                var deleteUrl = this.getAttribute('data-url');

                if (!confirm('Apakah Anda yakin ingin menghapus dokumen "' + docName + '"?')) {
                    return;
                }

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

        // FINALIZE BUTTON
        var btnFinalize = document.getElementById('btnFinalize');
        if (btnFinalize) {
            btnFinalize.addEventListener('click', function() {
                if (!confirm('Apakah Anda yakin ingin finalisasi berita acara?\n\nSetelah difinalisasi, dokumen akan dikirim dan tidak dapat diubah lagi.')) {
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
                            window.location.href = "{{ route('al.berkas') }}";
                        } else {
                            alert(data.message || 'Gagal finalisasi');
                            btnFinalize.disabled = false;
                            btnFinalize.innerHTML = '<i class="bi bi-check-circle"></i> Finalisasi dan Kirim';
                        }
                    })
                    .catch(function(error) {
                        console.error('Finalize error:', error);
                        alert('Terjadi kesalahan saat finalisasi');
                        btnFinalize.disabled = false;
                        btnFinalize.innerHTML = '<i class="bi bi-check-circle"></i> Finalisasi dan Kirim';
                    });
            });
        }
    });

</script>
@endpush
@endsection
