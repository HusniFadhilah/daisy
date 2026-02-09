{{-- resources/views/asesmen/al/berkas/ringkasan-asesor.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Upload Ringkasan Hasil Akreditasi - ' . $asesmen->name)

@section('content')
<div class="container-fluid py-3">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('al.berkas') }}">Berkas AL</a></li>
            <li class="breadcrumb-item active">Ringkasan Hasil Akreditasi</li>
        </ol>
    </nav>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="bi bi-file-earmark-text"></i> Upload Ringkasan Hasil Akreditasi
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
                            <i class="bi bi-person-check"></i> Anda adalah Asesor yang Mengupload Ringkasan
                        </h6>
                        <p class="mb-0">
                            Anda dapat menambah, mengedit, atau menghapus file ringkasan hasil akreditasi.
                        </p>
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> Pertama kali diupload: {{ $firstUpload->uploaded_at->format('d M Y, H:i') }}
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
                            <i class="bi bi-file-earmark-check"></i> Ringkasan Telah Diupload
                        </h6>
                        <p class="mb-1">
                            Ringkasan hasil akreditasi telah diupload oleh asesor: <strong>{{ $firstUpload->uploader->name ?? 'Asesor' }}</strong>
                        </p>
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> Diupload pada: {{ $firstUpload->uploaded_at->format('d M Y, H:i') }}
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

    <div class="row">
        {{-- LEFT --}}
        <div class="col-lg-8">

            {{-- CONFIDENTIAL DOCUMENT --}}
            <div class="card {{ !$canUpload ? 'border-secondary' : '' }}">
                <div class="card-header {{ $canUpload ? 'bg-dark' : 'bg-secondary' }} text-white p-4">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-lock"></i> Upload Ringkasan Hasil Akreditasi Menyeluruh (Confidential)
                        @if(!$canUpload)
                        <span class="badge bg-light text-dark ms-2">Dinonaktifkan</span>
                        @endif
                    </h5>
                    <small class="d-block opacity-75 mt-1">
                        Hanya untuk pihak internal. Berisi temuan khusus/indikasi ketidaksesuaian yang disengaja oleh PS
                        (laporan confidential dari asesor ke komite akreditasi LAMDEPILAR).
                    </small>
                </div>
                <div class="card-body">
                    @if(!$canUpload)
                    <div class="alert alert-warning alert-permanent">
                        <i class="bi bi-lock"></i>
                        <strong>Upload Dinonaktifkan</strong><br>
                        Ringkasan sudah diupload oleh asesor lain. Hanya asesor yang pertama mengupload yang dapat menambah atau mengedit dokumen.
                    </div>
                    @else
                    <form id="formUploadConf">
                        @csrf

                        <div class="upload-area" id="uploadAreaConf" style="border: 2px dashed #212529; border-radius: 12px; padding: 50px 20px; text-align: center; background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); cursor: pointer; transition: all 0.3s ease;">
                            <input type="file" id="fileInputConf" name="file" accept="application/pdf" class="d-none" required>

                            <div id="promptConf">
                                <i class="bi bi-cloud-arrow-up" style="font-size: 4rem; color:#212529;"></i>
                                <h6 class="mt-3 mb-1">Silahkan upload file di sini</h6>
                                <small class="text-muted">PDF, maksimal 20MB</small>
                            </div>

                            <div id="infoConf" class="d-none">
                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 3rem;"></i>
                                <div class="mt-2 fw-semibold" id="confName">-</div>
                                <small class="text-muted" id="confSize">-</small>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveConf">
                                        <i class="bi bi-x-circle"></i> Batalkan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-end">
                            <button type="submit" class="btn btn-dark" id="btnSubmitConf" disabled>
                                <i class="bi bi-upload"></i> Upload
                            </button>
                        </div>
                    </form>
                    @endif
                </div>

                <div class="card-body p-0">
                    @if($docsConf->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="40%">Nama File</th>
                                    <th width="20%">Diupload Oleh</th>
                                    <th width="20%">Waktu Upload</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($docsConf as $i => $doc)
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
                                        <small>{{ $doc->uploaded_at->format('d M Y, H:i') }}</small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a class="btn btn-outline-primary" href="{{ route('al.berkas.ringkasan-asesor.download', ['idAsesmen' => $asesmen->id, 'docId' => $doc->id]) }}" title="Download">
                                                <i class="bi bi-download"></i>
                                            </a>

                                            {{-- ✅ Tombol hapus hanya muncul jika user adalah uploader --}}
                                            @if($isUploader && $doc->uploaded_by == Auth::id())
                                            <button type="button" class="btn btn-outline-danger js-delete-doc" data-doc-id="{{ $doc->id }}" data-doc-name="{{ $doc->title }}" data-url="{{ route('al.berkas.ringkasan-asesor.delete', ['idAsesmen' => $asesmen->id, 'docId' => $doc->id]) }}" title="Hapus">
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
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size:54px;color:#ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada file confidential</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- RIGHT --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-info text-white">
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
                                <strong>Hanya satu asesor</strong> yang dapat mengupload ringkasan
                            </li>
                            <li class="mb-2">
                                Asesor yang <strong>pertama kali mengupload</strong> memiliki akses penuh untuk mengedit dan menghapus dokumen
                            </li>
                            <li>
                                Asesor lain dapat <strong>melihat dan mengunduh</strong> dokumen, tetapi tidak dapat mengedit
                            </li>
                        </ul>
                    </div>

                    <ol class="mb-0 small ps-3">
                        <li class="mb-2">Upload dokumen dalam bentuk <strong>PDF</strong></li>
                        <li class="mb-2">Pastikan file yang diupload sudah benar</li>
                        <li>Dokumen confidential <strong>hanya untuk pihak internal</strong> (tidak diketahui oleh prodi)</li>
                    </ol>
                </div>
            </div>

            {{-- ✅ Info Asesor Team --}}
            @if($asesorTeam->count() > 0)
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
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

        function formatSize(bytes) {
            var mb = bytes / (1024 * 1024);
            return mb.toFixed(2) + ' MB';
        }

        function bindUploader(cfg) {
            var area = document.getElementById(cfg.areaId);
            var input = document.getElementById(cfg.inputId);
            var prompt = document.getElementById(cfg.promptId);
            var info = document.getElementById(cfg.infoId);
            var nameEl = document.getElementById(cfg.nameId);
            var sizeEl = document.getElementById(cfg.sizeId);
            var btnRemove = document.getElementById(cfg.removeId);
            var btnSubmit = document.getElementById(cfg.submitId);
            var form = document.getElementById(cfg.formId);

            if (!area || !input || !form) return; // Safety check

            function reset() {
                input.value = '';
                prompt.classList.remove('d-none');
                info.classList.add('d-none');
                btnSubmit.disabled = true;
            }

            function handle() {
                var file = input.files[0];
                if (!file) return;

                if (file.type !== 'application/pdf') {
                    alert('File harus PDF');
                    reset();
                    return;
                }
                if (file.size > 20 * 1024 * 1024) {
                    alert('Ukuran file maksimal 20MB');
                    reset();
                    return;
                }

                nameEl.textContent = file.name;
                sizeEl.textContent = formatSize(file.size);

                prompt.classList.add('d-none');
                info.classList.remove('d-none');
                btnSubmit.disabled = false;
            }

            area.addEventListener('click', function(e) {
                if (e.target === btnRemove || btnRemove.contains(e.target)) return;
                input.click();
            });

            area.addEventListener('dragover', function(e) {
                e.preventDefault();
                area.classList.add('dragover');
            });

            area.addEventListener('dragleave', function(e) {
                e.preventDefault();
                area.classList.remove('dragover');
            });

            area.addEventListener('drop', function(e) {
                e.preventDefault();
                area.classList.remove('dragover');

                var files = e.dataTransfer.files;
                if (!files || files.length === 0) return;
                input.files = files;
                handle();
            });

            input.addEventListener('change', handle);

            btnRemove.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                reset();
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                var file = input.files[0];
                if (!file) return;

                var fd = new FormData();
                fd.append('file', file);
                fd.append('_token', "{{ csrf_token() }}");

                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';

                fetch(cfg.uploadUrl, {
                        method: 'POST'
                        , body: fd
                        , headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(res) {
                        if (res.success) {
                            alert(res.message || 'Upload berhasil!');
                            window.location.reload();
                        } else {
                            alert(res.message || 'Gagal upload');
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = '<i class="bi bi-upload"></i> Upload';
                        }
                    })
                    .catch(function(error) {
                        console.error('Upload error:', error);
                        alert('Terjadi kesalahan saat upload');
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="bi bi-upload"></i> Upload';
                    });
            });
        }

        bindUploader({
            areaId: 'uploadAreaConf'
            , inputId: 'fileInputConf'
            , promptId: 'promptConf'
            , infoId: 'infoConf'
            , nameId: 'confName'
            , sizeId: 'confSize'
            , removeId: 'btnRemoveConf'
            , submitId: 'btnSubmitConf'
            , formId: 'formUploadConf'
            , uploadUrl: "{{ route('al.berkas.ringkasan-asesor.upload', ['idAsesmen' => $asesmen->id, 'type' => 'hasil_akreditasi_confidential']) }}"
        });

        // DELETE DOCUMENT HANDLER
        var delBtns = document.querySelectorAll('.js-delete-doc');
        delBtns.forEach(function(btn) {
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
                        }
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(res) {
                        if (res.success) {
                            alert(res.message || 'Dokumen berhasil dihapus');
                            window.location.reload();
                        } else {
                            alert(res.message || 'Gagal hapus dokumen');
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

    });

</script>
@endpush
@endsection
