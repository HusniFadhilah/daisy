@extends('layouts.template.app')

@section('title', 'Upload LHA Asesor - ' . $asesmen->name)

@push('styles')
<style>
    .upload-area {
        border: 2px dashed #198754;
        border-radius: 12px;
        padding: 50px 20px;
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
                        <i class="bi bi-file-earmark-text"></i> Upload Ringkasan Hasil Akreditasi
                    </h4>
                    <p class="text-muted mb-0">Prodi {{ $asesmen->studyProgram->name }}</p>
                </div>
                <a href="{{ route('al.berkas') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- LEFT --}}
        <div class="col-lg-8">

            {{-- 2) Confidential --}}
            <div class="card">
                <div class="card-header bg-light text-dark p-4">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-lock"></i> Upload Ringkasan Hasil Akreditasi Menyeluruh (Confidential)
                    </h5>
                    <small class="d-block opacity-75 mt-1">
                        Hanya untuk pihak internal. Berisi temuan khusus/indikasi ketidaksesuaian yang disengaja oleh PS
                        (laporan confidential dari asesor ke komite akreditasi LAMDEPILAR).
                    </small>
                </div>
                <div class="card-body">
                    <form id="formUploadConf">
                        @csrf

                        <div class="upload-area" id="uploadAreaConf" style="border-color:#212529;">
                            <input type="file" id="fileInputConf" name="file" accept="application/pdf" class="d-none" required>

                            <div id="promptConf">
                                <i class="bi bi-cloud-arrow-up file-icon" style="color:#212529;"></i>
                                <h6 class="mt-3 mb-1">Silahkan upload file di sini</h6>
                                <small class="text-muted">PDF, maksimal 5MB</small>
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
                </div>

                <div class="card-body p-0">
                    @if($docsConf->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Nama</th>
                                    <th width="20%">Upload</th>
                                    <th width="10%" class="text-center">Aksi</th>
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

                                            <button type="button" class="btn btn-outline-danger js-delete-doc" data-url="{{ route('al.berkas.ringkasan-asesor.delete', ['idAsesmen' => $asesmen->id, 'docId' => $doc->id]) }}" title="Hapus">
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
                <div class="card-header bg-light">
                    <h6 class="mb-0">Petunjuk</h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0 small">
                        <li>Upload dokumen dalam bentuk PDF</li>
                        <li>Pastikan file benar</li>
                        <li>Dokumen confidential hanya untuk pihak internal (tidak diketahui oleh prodi)</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

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
                    alert('Ukuran file maksimal 5MB');
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
                        alert(res.message || 'OK');
                        if (res.success) window.location.reload();
                        btnSubmit.disabled = false;
                    })
                    .catch(function() {
                        alert('Gagal upload');
                        btnSubmit.disabled = false;
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

        var delBtns = document.querySelectorAll('.js-delete-doc');
        delBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!confirm('Hapus dokumen ini')) return;

                fetch(btn.dataset.url, {
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
                        alert(res.message || 'OK');
                        if (res.success) window.location.reload();
                    })
                    .catch(function() {
                        alert('Gagal hapus dokumen');
                    });
            });
        });

    });

</script>
@endpush
@endsection
