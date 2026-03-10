{{-- resources/views/asesmen/banding/berkas/upload-excel.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Upload Penilaian Excel - ' . $asesmen->name)

@push('styles')
<style>
    .upload-area {
        border: 2px dashed #932136;
        border-radius: 12px;
        padding: 60px 20px;
        text-align: center;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .upload-area:hover {
        border-color: #c0392b;
        background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        transform: translateY(-2px);
    }

    .upload-area.dragover {
        border-color: #27ae60;
        background: linear-gradient(135deg, #d5f4e6 0%, #ffffff 100%);
    }

    .upload-area.disabled {
        cursor: not-allowed;
        opacity: 0.6;
        border-color: #6c757d;
    }

    .file-icon {
        font-size: 4rem;
        color: #932136;
    }

    .progress-wrapper {
        display: none;
    }

    #alertSubmitReminder {
        animation: pulseAlert 2s ease-in-out infinite;
        border-left: 5px solid #ff9800;
    }

    @keyframes pulseAlert {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(255, 152, 0, 0.4);
        }

        50% {
            box-shadow: 0 0 15px 5px rgba(255, 152, 0, 0.2);
        }
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                <div>
                    <h4 class="mb-1">
                        <i class="bi bi-upload"></i> Upload Penilaian AL
                    </h4>
                    <p class="text-muted mb-0">{{ $asesmen->getName(false) }}</p>
                </div>
                <a href="{{ route('banding.berkas') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            {{-- ✅ ALERT: Info Uploader --}}
            @if($firstUpload)
            <div class="mt-3">
                @if($isUploader)
                <div class="alert alert-success alert-permanent">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle fs-4 me-3"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-2">
                                <i class="bi bi-person-check"></i> Anda adalah Asesor yang Mengupload Excel
                            </h6>
                            <p class="mb-0">
                                Anda dapat mengupload file Excel baru untuk memperbarui data penilaian.
                            </p>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> Pertama kali diupload: {{ $firstUpload->created_at->locale('id')->translatedFormat('d M Y, H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
                @else
                <div class="alert alert-info alert-permanent">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="bi bi-info-circle fs-4 me-3"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-2">
                                <i class="bi bi-file-earmark-check"></i> Excel Telah Diupload
                            </h6>
                            <p class="mb-1">
                                File Excel telah diupload oleh asesor: <strong>{{ $firstUpload->asesor->name ?? 'Asesor' }}</strong>
                            </p>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> Diupload pada: {{ $firstUpload->created_at->locale('id')->translatedFormat('d M Y, H:i') }}
                            </small>
                            <hr class="my-2">
                            <p class="mb-0 small text-muted">
                                <i class="bi bi-lock"></i> Hanya asesor yang pertama kali mengupload yang dapat mengupload file baru.
                                Anda dapat melihat hasil penilaian di halaman detail.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- ✅ Progress Section --}}
            <div class="progress-wrapper mt-4">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0">Progres Penilaian</h5>
                            <span class="badge bg-primary fs-6" id="progressPercentage">
                                {{ $progress['percentage'] }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-gradient bg-success" role="progressbar" id="progressBarPenilaian" style="width: {{ $progress['percentage'] }}%" aria-valuenow="{{ $progress['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            <span id="progressCompleted">{{ $progress['completed'] }}</span> dari
                            <span id="progressTotal">{{ $progress['total'] }}</span> elemen penilaian selesai dinilai
                        </small>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="stat-circle">
                            <div class="circle-content">
                                <h2 class="mb-0" id="progressCount">{{ $progress['completed'] }}/{{ $progress['total'] }}</h2>
                                <small class="text-muted">Elemen Penilaian</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status & Actions Footer --}}
        @if($isSubmittedOnly || $isApproved || $isComplete)
        <div class="card-footer bg-white">
            <div class="row align-items-center my-2">
                <div class="col-12">
                    {{-- Alert Submit Reminder --}}
                    @if(!$isSubmittedOnly && !$isApproved && $isComplete)
                    <div class="alert alert-warning alert-dismissible alert-permanent mb-3" id="alertSubmitReminder">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="alert-heading mb-2">
                                    <i class="bi bi-check-circle"></i> Penilaian Telah Lengkap!
                                </h5>
                                <p class="mb-2">
                                    Anda telah menyelesaikan <strong>semua {{ $progress['total'] }} elemen penilaian</strong>.
                                    Mohon segera lakukan <strong>Finalisasi dan Kirim</strong> untuk menyelesaikan penilaian.
                                </p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    {{-- Alert Progress --}}
                    @if(!$isSubmittedOnly && !$isApproved && !$isComplete && $progress['percentage'] > 0)
                    <div class="alert alert-info alert-dismissible alert-permanent mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Progres Penilaian:</strong>
                        Anda telah menilai {{ $progress['completed'] }} dari {{ $progress['total'] }} elemen
                        (<strong>{{ $progress['percentage'] }}%</strong>).
                        Selesaikan <strong>{{ $progress['remaining'] }} elemen</strong> lagi untuk dapat melakukan finalisasi.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    {{-- Alert Submitted --}}
                    @if($isSubmittedOnly || $isApproved)
                    <div class="alert alert-success alert-permanent alert-dismissible mb-3">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Penilaian Telah Di-Submit!</strong>
                        Penilaian Anda telah berhasil dikirim dan disimpan.
                        @if($assignment->submitted_at)
                        <div class="mt-2 small text-muted">
                            <i class="bi bi-clock"></i> Di-submit pada: {{ \App\Libraries\Date::tglWaktu($assignment->submitted_at) }}
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                        <div>
                            @if(!$isSubmittedOnly && !$isApproved)
                            <button class="btn btn-success w-md-100 w-md-auto" id="btnSubmit">
                                <i class="bi bi-check-circle"></i> Finalisasi dan Kirim
                            </button>
                            <small class="d-block text-muted mt-1">
                                <i class="bi bi-info-circle"></i>
                                Pastikan semua elemen telah dinilai sebelum mengirim
                            </small>
                            @else
                            <button class="btn btn-success w-100 w-md-auto" disabled>
                                <i class="bi bi-check-all"></i> Penilaian Telah Di-Submit
                            </button>
                            @endif
                        </div>

                        <div>
                            <a href="{{ route('banding.berkas.show', $asesmen->id) }}" class="btn btn-outline-primary">
                                <i class="bi bi-eye"></i> Lihat Detail Penilaian
                            </a>
                        </div>
                    </div>

                    {{-- Progress Summary --}}
                    <div class="mt-3 p-3 bg-light rounded">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h3 class="mb-0"><b>{{ $progress['total'] }}</b></h3>
                                <small class="text-muted">Total Elemen</small>
                            </div>
                            <div class="col-md-3">
                                <h3 class="mb-0 text-success"><b>{{ $progress['completed'] }}</b></h3>
                                <small class="text-muted">Telah Dinilai</small>
                            </div>
                            <div class="col-md-3">
                                <h3 class="mb-0 text-warning"><b>{{ $progress['remaining'] }}</b></h3>
                                <small class="text-muted">Belum Dinilai</small>
                            </div>
                            <div class="col-md-3">
                                <h3 class="mb-0 text-primary"><b>{{ $progress['percentage'] }}%</b></h3>
                                <small class="text-muted">Progress</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="row">
        {{-- Left: Upload Form --}}
        <div class="col-lg-8">
            <div class="card {{ !$canUpload ? 'border-secondary' : '' }}">
                <div class="card-header {{ $canUpload ? 'bg-primary' : 'bg-secondary' }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cloud-upload"></i> Upload File Penilaian AL
                        @if(!$canUpload)
                        <span class="badge bg-light text-dark ms-2">Dinonaktifkan</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    {{-- ✅ Warning jika tidak bisa upload --}}
                    @if(!$canUpload)
                    <div class="alert alert-warning alert-permanent">
                        <i class="bi bi-lock"></i>
                        <strong>Upload Dinonaktifkan</strong><br>
                        File Excel sudah diupload oleh asesor lain. Hanya asesor yang pertama mengupload yang dapat mengupload file baru.
                    </div>
                    @endif

                    {{-- Upload Area --}}
                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf

                        <div class="upload-area {{ (!$canUpload || $isSubmittedOnly || $isApproved) ? 'disabled' : '' }}" id="uploadArea">
                            <input type="file" id="fileInput" name="file" accept=".xlsx,.xls" class="d-none" required {{ (!$canUpload || $isSubmittedOnly || $isApproved) ? 'disabled' : '' }}>

                            <div id="uploadPrompt">
                                <i class="bi bi-cloud-arrow-up file-icon"></i>
                                <h5 class="mt-3">
                                    @if($isSubmittedOnly || $isApproved)
                                    Upload Dinonaktifkan (Sudah Di-Submit)
                                    @elseif(!$canUpload)
                                    Upload Dinonaktifkan (Sudah Diupload Asesor Lain)
                                    @else
                                    Silahkan Upload File Excel Penilaian AL di Sini
                                    @endif
                                </h5>
                                <p class="text-muted mb-0">
                                    Format: .xlsx atau .xls (Max 10MB)
                                </p>
                            </div>

                            <div id="fileInfo" class="d-none">
                                <i class="bi bi-file-earmark-excel text-success" style="font-size: 4rem;"></i>
                                <h5 class="mt-3" id="fileName">-</h5>
                                <p class="text-muted mb-0">
                                    Ukuran: <span id="fileSize">-</span>
                                </p>
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="btnRemoveFile">
                                    <i class="bi bi-x-circle"></i> Batalkan Upload
                                </button>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="progress-wrapper mt-4" id="progressWrapper">
                            <div class="mb-2">
                                <strong>Progres <i>Upload</i>:</strong>
                                <span id="progressText" class="float-end">0%</span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">
                                    0%
                                </div>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-primary btn-md" id="btnUploadSubmit" {{ (!$canUpload || $isSubmittedOnly || $isApproved) ? 'disabled' : '' }}>
                                <i class="bi bi-upload"></i> Upload dan Proses
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- Right: Instructions & Team Info --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        Petunjuk Penilaian AL
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info alert-permanent alert-dismissible mb-4">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle"></i> Petunjuk Upload
                        </h6>
                        <ol class="mb-0 small">
                            <li>Download templat Excel terlebih dahulu menggunakan tombol di bawah</li>
                            <li>Silahkan mengisi penilaian pada kolom yang tersedia (cell berwarna kuning)</li>
                            <li>Mohon jangan mengubah struktur, nama sheet, atau kode elemen pada excel</li>
                            <li>Upload file Excel yang telah diisi</li>
                            <li>Setelah selesai upload, klik tombol <strong>Finalisasi dan Kirim</strong></li>
                        </ol>
                    </div>

                    <div class="mb-4 text-center">
                        <a href="{{ route('banding.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'template']) }}" class="btn btn-md btn-outline-primary">
                            <i class="bi bi-download"></i> Download Templat Penilaian AL
                        </a>
                    </div>
                </div>
            </div>

            {{-- ✅ Tim Asesor Info --}}
            @if($asesorTeam->count() > 0)
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-people"></i> Tim Asesor AL
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        @foreach($asesorTeam as $asesor)
                        <tr>
                            <td width="40">
                                @if($firstUpload && $firstUpload->id_asesor == $asesor->id_user)
                                <i class="bi bi-person-check-fill text-success" title="Uploader"></i>
                                @else
                                <i class="bi bi-person"></i>
                                @endif
                            </td>
                            <td>
                                {{ $asesor->user->name ?? '-' }}
                                @if($asesor->id_user == Auth::id())
                                <span class="badge bg-info ms-1">Anda</span>
                                @endif
                                @if($firstUpload && $firstUpload->id_asesor == $asesor->id_user)
                                <span class="badge bg-success ms-1">Uploader</span>
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
@php
$isSubmitted = $isSubmittedOnly || $isApproved;
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const idAsesmen = "{{ $asesmen->id }}";
        const isSubmitted = @json($isSubmitted);
        const canUpload = @json($canUpload);

        const qs = function(id) {
            return document.getElementById(id);
        };

        const setText = function(id, value) {
            const el = qs(id);
            if (el) el.textContent = value;
        };

        const el = {
            uploadArea: qs('uploadArea')
            , fileInput: qs('fileInput')
            , uploadPrompt: qs('uploadPrompt')
            , fileInfo: qs('fileInfo')
            , fileName: qs('fileName')
            , fileSize: qs('fileSize')
            , btnUploadSubmit: qs('btnUploadSubmit')
            , btnRemoveFile: qs('btnRemoveFile')
            , uploadForm: qs('uploadForm')
            , progressWrapper: qs('progressWrapper')
            , progressBar: qs('progressBar')
            , progressText: qs('progressText')
        , };

        // ✅ Disable upload if submitted OR not uploader
        if (isSubmitted || !canUpload) {
            if (el.uploadArea) el.uploadArea.style.cursor = 'not-allowed';
            if (el.fileInput) el.fileInput.disabled = true;
            console.log('Upload disabled:', isSubmitted ? 'Already submitted' : 'Not the uploader');
            return; // Stop all upload functionality
        }

        // Upload area click
        if (el.uploadArea && el.fileInput) {
            el.uploadArea.addEventListener('click', function(e) {
                if (el.btnRemoveFile) {
                    if (e.target !== el.btnRemoveFile && !el.btnRemoveFile.contains(e.target)) {
                        el.fileInput.click();
                    }
                } else {
                    el.fileInput.click();
                }
            });

            // Drag & drop
            el.uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                el.uploadArea.classList.add('dragover');
            });
            el.uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                el.uploadArea.classList.remove('dragover');
            });
            el.uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                el.uploadArea.classList.remove('dragover');

                const files = e.dataTransfer.files;
                if (files.length > 0 && el.fileInput) {
                    el.fileInput.files = files;
                    handleFileSelect();
                }
            });
        }

        // File input change
        if (el.fileInput) el.fileInput.addEventListener('change', handleFileSelect);

        function handleFileSelect() {
            if (!el.fileInput) return;
            const file = el.fileInput.files[0];
            if (!file) return;

            const validTypes = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                , 'application/vnd.ms-excel'
            ];
            const fileExtension = file.name.split('.').pop().toLowerCase();

            if (!validTypes.includes(file.type) && !['xlsx', 'xls'].includes(fileExtension)) {
                Swal.fire({
                    icon: 'error'
                    , title: 'Format File Salah'
                    , text: 'Hanya file Excel (.xlsx atau .xls) yang diperbolehkan'
                });
                el.fileInput.value = '';
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error'
                    , title: 'File Terlalu Besar'
                    , text: 'Ukuran file maksimal 10MB'
                });
                el.fileInput.value = '';
                return;
            }

            setText('fileName', file.name);
            setText('fileSize', formatFileSize(file.size));
            if (el.uploadPrompt) el.uploadPrompt.classList.add('d-none');
            if (el.fileInfo) el.fileInfo.classList.remove('d-none');
            if (el.btnUploadSubmit) el.btnUploadSubmit.disabled = false;
        }

        // Remove file
        if (el.btnRemoveFile) el.btnRemoveFile.addEventListener('click', function(e) {
            e.stopPropagation();
            if (el.fileInput) el.fileInput.value = '';
            if (el.uploadPrompt) el.uploadPrompt.classList.remove('d-none');
            if (el.fileInfo) el.fileInfo.classList.add('d-none');
            if (el.btnUploadSubmit) el.btnUploadSubmit.disabled = true;
        });

        // Submit form
        if (el.uploadForm) el.uploadForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const file = el.fileInput ? el.fileInput.files[0] : null;
            if (!file) {
                Swal.fire('Error', 'Pilih file terlebih dahulu', 'error');
                return;
            }

            if (el.btnUploadSubmit) {
                el.btnUploadSubmit.disabled = true;
                el.btnUploadSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';
            }

            if (el.progressWrapper) el.progressWrapper.style.display = 'block';

            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch(`/al/berkas/${idAsesmen}/import`, {
                    method: 'POST'
                    , body: formData
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Upload gagal');
                }

                if (data.success) {
                    const importLogId = data.import_log_id;
                    Swal.fire({
                        icon: 'info'
                        , title: 'Sedang Memproses File'
                        , html: 'File sedang diproses.<br>Halaman akan dimuat ulang setelah selesai.'
                        , showConfirmButton: false
                        , allowOutsideClick: false
                    });
                    pollImportStatus(importLogId);
                }
            } catch (error) {
                console.error('Upload error:', error);
                if (el.btnUploadSubmit) {
                    el.btnUploadSubmit.disabled = false;
                    el.btnUploadSubmit.innerHTML = '<i class="bi bi-upload"></i> Upload dan Proses';
                }
                if (el.progressWrapper) el.progressWrapper.style.display = 'none';
                Swal.fire({
                    icon: 'error'
                    , title: 'Upload Gagal'
                    , text: error.message
                });
            }
        });

        // Polling
        let pollInterval = null;
        let pollCount = 0;
        const MAX_POLL = 150;

        function pollImportStatus(importLogId) {
            pollInterval = setInterval(async function() {
                pollCount++;
                if (pollCount >= MAX_POLL) {
                    clearInterval(pollInterval);
                    Swal.fire({
                        icon: 'warning'
                        , title: 'Timeout'
                        , text: 'Proses memakan waktu lama. Mohon ulangi upload file'
                        , confirmButtonText: 'OK'
                    }).then(function() {
                        window.location.reload();
                    });
                    return;
                }

                try {
                    const response = await fetch(`/al/import-status/${importLogId}`);
                    const data = await response.json();
                    if (data.success) {
                        const log = data.data;
                        updateProgress(log);
                        if (log.status === 'completed' || log.status === 'failed') {
                            clearInterval(pollInterval);
                            showResult(log);
                        }
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }
            }, 2000);
        }

        function updateProgress(log) {
            const percentage = log.total_rows > 0 ? Math.round((log.imported_rows / log.total_rows) * 100) : 0;
            if (el.progressBar) {
                el.progressBar.style.width = percentage + '%';
                el.progressBar.textContent = percentage + '%';
            }
            if (el.progressText) el.progressText.textContent = percentage + '%';
        }

        function showResult(log) {
            if (log.status === 'completed') {
                Swal.fire({
                    icon: 'success'
                    , title: 'Proses Selesai!'
                    , html: '<div class="text-center"><p>File telah berhasil diupload dan diproses</p></div>'
                    , timer: 3000
                    , timerProgressBar: true
                }).then(() => window.location.reload());
                return;
            }

            // ✅ Format errors
            let errHtml = '';
            if (Array.isArray(log.errors)) {
                errHtml = `<div class="text-start">
      <p class="mb-2">Proses pembacaan data gagal karena:</p>
      <ul style="text-align:left; padding-left:18px;">
        ${log.errors.map(e => `<li>${escapeHtml(e)}</li>`).join('')}
      </ul>
      <p class="mt-2">Mohon lakukan pengecekan template excel dan coba upload ulang.</p>
    </div>`;
            } else {
                errHtml = `<p>${log.errors || 'Terjadi kesalahan saat memproses file'}</p>`;
            }

            Swal.fire({
                icon: 'error'
                , title: 'Proses Upload Gagal'
                , html: errHtml
                , confirmButtonText: 'OK'
            }).then(() => window.location.reload());
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Submit Penilaian Handler
        const btnSubmit = document.getElementById('btnSubmit');

        if (btnSubmit) btnSubmit.addEventListener('click', submitPenilaian);

        async function submitPenilaian() {
            const completed = parseInt(document.getElementById('progressCompleted').textContent);
            const total = parseInt(document.getElementById('progressTotal').textContent);

            if (completed < total) {
                Swal.fire({
                    icon: 'warning'
                    , title: 'Penilaian Belum Lengkap'
                    , html: `<p>Anda baru menilai <strong>${completed} dari ${total}</strong> elemen.</p>
                           <p class="text-danger">Anda harus menilai semua elemen sebelum submit!</p>`
                    , confirmButtonText: 'OK'
                });
                return;
            }

            const confirmed = await Swal.fire({
                icon: 'question'
                , title: 'Konfirmasi Submit Penilaian'
                , html: `
                    <div class="text-start">
                        <p><strong>Anda akan mengirim penilaian untuk finalisasi.</strong></p>
                        <p>Setelah di-submit:</p>
                        <ul>
                            <li>Penilaian akan disimpan secara permanen</li>
                            <li>Anda tidak bisa upload excel lagi</li>
                            <li>Penilaian tidak dapat diubah</li>
                        </ul>
                        <p class="text-primary">Total: <strong>${total} elemen</strong> telah dinilai</p>
                    </div>
                `
                , showCancelButton: true
                , confirmButtonText: 'Ya, Submit Sekarang'
                , cancelButtonText: 'Batal'
                , confirmButtonColor: '#28a745'
                , cancelButtonColor: '#6c757d'
            });

            if (!confirmed.isConfirmed) return;

            try {
                const response = await fetch(`/al/berkas/${idAsesmen}/submit`, {
                    method: 'POST'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                        , 'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Submit Berhasil!'
                        , html: `
                            <div class="text-center">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                                <p class="mt-3">${data.message}</p>
                            </div>
                        `
                        , confirmButtonText: 'OK'
                    });
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Gagal submit penilaian');
                }
            } catch (error) {
                console.error('Submit error:', error);
                Swal.fire({
                    icon: 'error'
                    , title: 'Gagal Submit'
                    , text: error.message
                });
            }
        }
    });

</script>
@endpush
@endsection
