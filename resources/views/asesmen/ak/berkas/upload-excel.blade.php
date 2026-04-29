{{-- resources/views/asesmen/ak/berkas/upload-excel.blade.php --}}

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

    .file-icon {
        font-size: 4rem;
        color: #932136;
    }

    .upload-progress-wrapper {
        display: none;
    }

    .highlight-revision {
        animation: pulseRevision 1s ease-in-out 3;
        border: 2px solid #ff9800 !important;
    }

    @keyframes pulseRevision {

        0%,
        100% {
            background-color: #fff3e0;
            transform: scale(1);
        }

        50% {
            background-color: #ffe0b2;
            transform: scale(1.01);
        }
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
                        <i class="bi bi-upload"></i> Upload Penilaian AK
                    </h4>
                    <p class="text-muted mb-0">{{ $asesmen->getName(false) }}</p>
                </div>
                <a href="{{ route('ak.berkas') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

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
                        <small class="text-white mt-1 d-block">
                            <span id="progressCompleted">{{ $progress['completed'] }}</span> dari
                            <span id="progressTotal">{{ $progress['total'] }}</span> elemen penilaian selesai dinilai
                        </small>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="stat-circle">
                            <div class="circle-content">
                                <h2 class="mb-0" id="progressCount">{{ $progress['completed'] }}/{{ $progress['total'] }}</h2>
                                <small class="text-white">Elemen Penilaian</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ Status & Actions Footer --}}

        @if($isSubmitted || $isSubmittedOnly || $isApproved || $isComplete || $hasRevisionRequests)
        <div class="card-footer bg-white">
            <div class="row align-items-center my-2">
                <div class="col-12">
                    {{-- ✅ Alert Revisi --}}
                    @if($hasRevisionRequests)
                    <div class="alert alert-warning alert-dismissible alert-permanent mb-3">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="alert-heading mb-2">
                                    <i class="bi bi-pencil-square"></i> Ada {{ $countNeedsRevisions }} Permintaan Revisi!
                                </h5>
                                <p class="mb-2">
                                    Validator meminta Anda merevisi <strong>{{ $countNeedsRevisions }} elemen penilaian</strong>.
                                    Silakan perbaiki menggunakan penilaian <i>by system</i> atau upload ulang excel yang telah diperbaiki.
                                </p>
                                <hr>
                                <div class="mb-0">
                                    <a href="{{ route('ak.berkas.show', $asesmen->id) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-eye"></i> Lihat Detail Revisi
                                    </a>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    {{-- ✅ Alert Submit Reminder --}}
                    @if(!$isSubmittedOnly && !$isApproved && $isComplete && !$hasRevisionRequests)
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
                                    {{-- <br>Anda dapat melakukan cek split penilaian antar asesor di tombol berikut <a class="btn btn-info btn-sm" href="{{ route('ak.berkas.cek-split', $asesmen->id) }}" target="_blank">
                                    <i class="bi bi-search"></i> Cek Split Penilaian
                                    </a> --}}
                                    <br>Mohon segera lakukan <strong>Finalisasi dan Kirim</strong> setelah melakukan cek split, agar penilaian Anda dapat divalidasi.
                                </p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    {{-- ✅ Alert Submitted --}}
                    @if($isSubmittedOnly && !$isApproved)
                    <div class="alert alert-info alert-permanent alert-dismissible mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Telah Di-Submit!</strong> Penilaian Anda sedang menunggu validasi.
                    </div>
                    @endif

                    {{-- ✅ Alert Approved --}}
                    @if($isApproved)
                    <div class="alert alert-success alert-permanent alert-dismissible mb-3">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Penilaian Disetujui!</strong> Penilaian Anda telah divalidasi dan disetujui.
                    </div>
                    @endif

                    <!-- Finalisasi -->
                    @include('asesmen.ak.components.finalisasi-button')

                    {{-- ✅ Progress Summary --}}
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
                        @if($hasRevisionRequests)
                        <div class="mt-3 pt-3 border-top">
                            <div class="text-center">
                                <h3 class="mb-0 text-warning">
                                    <b>{{ $countNeedsRevisions }}</b>
                                </h3>
                                <small class="text-muted">Perlu Revisi</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="row">
        {{-- Left: Upload Form --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cloud-upload"></i> Upload File Penilaian AK
                    </h5>
                </div>
                <div class="card-body">

                    {{-- Upload Area --}}
                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf

                        <div class="upload-area" id="uploadArea">
                            <input type="file" id="fileInput" name="file" accept=".xlsx,.xls" class="d-none" required {{ ($isSubmittedOnly || $isApproved) ? 'disabled' : '' }}>

                            <div id="uploadPrompt">
                                <i class="bi bi-cloud-arrow-up file-icon"></i>
                                <h5 class="mt-3">
                                    @if($isSubmittedOnly || $isApproved)
                                    Upload Dinonaktifkan (Sudah Submit/Disetujui)
                                    @else
                                    Silahkan Upload File Excel Penilaian AK di Sini
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
                        <div class="upload-progress-wrapper mt-4" id="progressWrapper">
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
                            <button type="submit" class="btn btn-primary btn-md" id="btnUploadSubmit" {{ ($isSubmittedOnly || $isApproved) ? 'disabled' : '' }}>
                                <i class="bi bi-upload"></i> Upload dan Proses
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- Right: Instructions --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        Petunjuk Penilaian AK
                    </h6>
                </div>
                <div class="card-body">
                    {{-- Instructions --}}
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

                    {{-- Download Templat Button --}}
                    <div class="mb-4 text-center">
                        <a href="{{ route('ak.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'template']) }}" class="btn btn-md btn-outline-primary">
                            <i class="bi bi-download"></i> Download Templat Penilaian AK
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const idAsesmen = "{{ $asesmen->id }}";
        const isSubmitted = @json($isSubmittedOnly);
        const isApproved = @json($isApproved);

        // --- Helper DOM ---
        const qs = function(id) {
            return document.getElementById(id);
        };

        const setText = function(id, value) {
            const el = qs(id);
            if (el) el.textContent = value;
        };

        // --- Cache elemen ---
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

        // ✅ Disable upload if submitted/approved
        if (isSubmitted || isApproved) {
            if (el.uploadArea) el.uploadArea.style.cursor = 'not-allowed';
            if (el.fileInput) el.fileInput.disabled = true;
            return; // Stop all upload functionality
        }

        // --- Upload area click ---
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

        // --- Handle file select ---
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

        // --- Remove file ---
        if (el.btnRemoveFile) el.btnRemoveFile.addEventListener('click', function(e) {
            e.stopPropagation();
            if (el.fileInput) el.fileInput.value = '';
            if (el.uploadPrompt) el.uploadPrompt.classList.remove('d-none');
            if (el.fileInfo) el.fileInfo.classList.add('d-none');
            if (el.btnUploadSubmit) el.btnUploadSubmit.disabled = true;
        });

        // --- Submit form ---
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
                const response = await fetch(`/ak/berkas/${idAsesmen}/import`, {
                    method: 'POST'
                    , body: formData
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Upload gagal');

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

        // --- Polling ---
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
                    const response = await fetch(`/ak/import-status/${importLogId}`);
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

        async function showResult(log) {
            if (log.status !== 'completed') {
                // ── Error tetap sama seperti sebelumnya ────────────────
                let errHtml = '';
                if (Array.isArray(log.errors)) {
                    console.log(log.errors)
                    errHtml = `<div class="text-start">
              <p class="mb-2">Proses pembacaan data gagal karena:</p>
              <ul style="text-align:left; padding-left:18px;">
                <li>Terjadi kesalahan saat membaca file</li>
              </ul>
              <p class="mt-2">Mohon lakukan pengecekan template excel dan coba upload ulang.</p>
            </div>`;
                } else {
                    errHtml = `<p>${log.errors || 'Terjadi kesalahan saat memproses file'}</p>`;
                }
                await Swal.fire({
                    icon: 'error'
                    , title: 'Proses Upload Gagal'
                    , html: errHtml
                    , confirmButtonText: 'OK'
                , });
                window.location.reload();
                return;
            }

            // ── Upload berhasil → cek split dulu ──────────────────────
            Swal.fire({
                icon: 'info'
                , title: 'Mengecek Split Penilaian…'
                , html: 'Mohon tunggu sebentar.'
                , allowOutsideClick: false
                , showConfirmButton: false
                , didOpen: () => Swal.showLoading()
            , });

            try {
                const splitRes = await fetch(`/ak/berkas/${idAsesmen}/check-split-result`, {
                    headers: {
                        'Accept': 'application/json'
                        , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    , }
                , });
                const splitData = await splitRes.json();

                if (!splitData.success) throw new Error(splitData.message);

                // ── Kondisi 1: Belum ada asesor lain yang mengisi ─────
                if (!splitData.otherHasFilled) {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Upload Berhasil!'
                        , html: `
                    <p>File berhasil diproses.</p>
                    <div class="alert alert-info text-start mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Asesor lain belum mengisi penilaian.<br>
                        Anda dapat melakukan cek split penilaian antar asesor di tombol berikut:
                        <br><br>
                        <a href="/ak/berkas/${idAsesmen}/cek-split" target="_blank"
                           class="btn btn-info btn-sm">
                            <i class="bi bi-search"></i> Cek Split Penilaian
                        </a>
                    </div>
                `
                        , confirmButtonText: 'OK'
                        , confirmButtonColor: '#28a745'
                    , });
                    window.location.reload();
                    return;
                }

                // ── Kondisi 2: Ada asesor lain → tampilkan split ──────
                if (splitData.splitCount === 0) {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Upload Berhasil — Tidak Ada Split!'
                        , html: `
                    <p>File berhasil diproses.</p>
                    <div class="alert alert-success text-start mt-3">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Tidak ada split penilaian</strong> dengan asesor lain.
                        Semua elemen memiliki selisih penilaian ≤ 1.
                    </div>
                `
                        , confirmButtonText: 'OK'
                        , confirmButtonColor: '#28a745'
                    , });
                    window.location.reload();
                    return;
                }

                // ── Ada split → bangun tabel notifikasi ───────────────
                const showBerkasBase = `/ak/berkas/${idAsesmen}`;
                const splitRows = splitData.splitItems.map(item => {
                    const skorBadges = item.skors.map(s => {
                        let skor = s.skor;
                        let nama = s.nama;
                        return `
                        <div class="mb-2">
                            <span class="badge bg-light text-dark">${nama}</span>
                            <span class="badge text-dark" style="background-color: ${getSkorColorJS(skor)};">${getSkorLabelShort(skor)}</span>
                        </div>`
                    }).join(' ');
                    return `
                <tr style="cursor:pointer;"
                    onclick="window.open('${showBerkasBase}#elemen-${item.elemenId}', '_blank')"
                    title="Klik untuk melihat di halaman penilaian">
                    <td class="text-center"><span class="badge bg-primary">${item.kodeKriteria}</span></td>
                    <td><strong class="me-2">${item.kodeElemen}</strong> <small>${item.pernyataan}</small></td>
                    <td>${skorBadges}</td>
                    <td class="text-center">
                        <span class="badge bg-danger">Selisih ${item.selisih}</span>
                    </td>
                    <td class="text-center">
                        <a href="${showBerkasBase}#elemen-${item.elemenId}" target="_blank"
                           class="btn btn-sm btn-warning"
                           onclick="event.stopPropagation()">
                            <i class="bi bi-eye"></i> Lihat
                        </a>
                    </td>
                </tr>
            `;
                }).join('');

                await Swal.fire({
                    icon: 'warning'
                    , title: `Upload Berhasil — Ditemukan ${splitData.splitCount} Split!`
                    , html: `
                <div class="text-start">
                    <p>File berhasil diproses, namun ditemukan
                    <strong>${splitData.splitCount} elemen</strong> dengan selisih penilaian
                    antar asesor <strong>&gt; 1</strong>.</p>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Klik baris elemen untuk membuka halaman penilaian dan melihat detail.
                    </div>

                    <div style="max-height:300px; overflow-y:auto;">
                        <table class="table table-sm table-hover table-bordered align-middle"
                               style="font-size:13px;">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th width="10%">Kriteria</th>
                                    <th width="20%">Elemen</th>
                                    <th width="40%">Kategori Penilaian</th>
                                    <th width="15%">Selisih</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>${splitRows}</tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <a href="/ak/berkas/${idAsesmen}/cek-split" target="_blank"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-people"></i> Buka Halaman Cek Split Lengkap
                        </a>
                    </div>
                </div>
            `
                    , width: '900px'
                    , confirmButtonText: 'Mengerti, Lanjutkan'
                    , confirmButtonColor: '#ff9800'
                    , allowOutsideClick: false
                , });

            } catch (err) {
                console.error('Split check error:', err);
                // Jika cek split gagal, tetap lanjut reload tanpa blokir
                await Swal.fire({
                    icon: 'success'
                    , title: 'Upload Berhasil!'
                    , text: 'File berhasil diproses.'
                    , timer: 2000
                    , showConfirmButton: false
                , });
            }

            window.location.reload();
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // ✅ Submit Penilaian Handler
        const btnSubmit = document.getElementById('btnSubmit');
        const btnSubmitFromAlert = document.getElementById('btnSubmitFromAlert');

        if (btnSubmit) btnSubmit.addEventListener('click', submitPenilaian);
        if (btnSubmitFromAlert) btnSubmitFromAlert.addEventListener('click', submitPenilaian);

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
                        <p><strong>Anda akan mengirim penilaian untuk validasi.</strong></p>
                        <p>Setelah di-submit:</p>
                        <ul>
                            <li>Penilaian akan dikirim ke validator</li>
                            <li>Anda tidak bisa upload excel lagi</li>
                            <li>Validator akan memvalidasi penilaian Anda</li>
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
                const response = await fetch(`/ak/berkas/${idAsesmen}/submit`, {
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
