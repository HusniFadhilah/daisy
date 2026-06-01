{{-- resources/views/de/permohonan-banding/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Kirim Penerimaan Banding — ' . $pengajuan->nomor_pengajuan)

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

</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.permohonan-banding') }}">Penerimaan Banding</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-send-check"></i> Kirim Penerimaan Permohonan Banding
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('de.permohonan-banding') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @php
    $suratPermohonanBanding = $pengajuan->dokumen
    ->firstWhere('jenis_dokumen', 'surat_permohonan_banding');

    $suratPenerimaan = $pengajuan->dokumen
    ->firstWhere('jenis_dokumen', 'surat_penerimaan_banding_de');
    @endphp

    <div class="row">

        {{-- ── MAIN COLUMN ── --}}
        <div class="col-lg-8 mb-4">


            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DITERIMA && !$sudahAdaInvoice)
            <div class="card mb-4 border-warning">
                <div class="card-body text-center py-4">
                    <i class="bi bi-send fs-1 text-warning mb-2 d-block"></i>
                    <h5>Invoice Pembayaran Banding Belum Dikirim</h5>
                    <p class="text-muted">Kirim invoice pembayaran banding (Rp {{ number_format(\App\Models\PengajuanPembayaran::biayaBanding(), 0, ',', '.') }}) ke PS agar proses dapat dilanjutkan.</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalKirimInvoiceBanding">
                        <i class="bi bi-send"></i> Kirim Invoice Banding
                    </button>
                </div>
            </div>

            {{-- Include modal dengan satu pengajuan --}}
            @include('de.validasi-pembayaran-banding.components.modal-kirim-invoice', [
            'pengajuanBelumInvoice' => collect([$pengajuan]),
            'modeSingle' => true,
            ])
            @endif

            @if($sudahAdaInvoice)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-receipt"></i>
                Invoice banding sudah dikirim.
                <a href="{{ route('de.validasi-pembayaran-banding.show', $pengajuan->pembayaranBanding->id) }}" class="alert-link ms-2">
                    Lihat Status Pembayaran →
                </a>
                @if($pengajuan->pembayaranBanding->status_pembayaran === 'terverifikasi')
                <br>Pembayaran telah tervalidasi dan dapat dilanjutkan ke penugasan asesor banding.
                @endif
            </div>
            @endif

            {{-- ── Surat Permohonan Banding dari UPPS ── --}}
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-pdf"></i> Surat Permohonan Banding dari PS/UPPS
                    </h5>
                </div>
                <div class="card-body">
                    @if($suratPermohonanBanding)
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center rounded gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-pdf text-danger me-3" style="font-size: 36px;"></i>
                            <div>
                                <strong>{{ $suratPermohonanBanding->original_filename }}</strong><br>
                                <small class="text-muted">
                                    Diupload: {{ $suratPermohonanBanding->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small><br>
                                <small class="text-muted">
                                    {{ number_format($suratPermohonanBanding->file_size / 1024, 2) }} KB
                                </small>
                            </div>
                        </div>
                        <a href="{{ route('de.permohonan-banding.download-permohonan', $pengajuan->id) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-eye"></i> Lihat File
                        </a>
                    </div>

                    {{-- Alasan banding --}}
                    @if($pengajuan->alasan_banding)
                    <div class="mt-3 p-3 bg-light rounded border-start border-warning border-3">
                        <small class="text-muted d-block mb-1 fw-bold">
                            <i class="bi bi-chat-left-quote"></i> Alasan Banding dari PS/UPPS:
                        </small>
                        <p class="mb-0 small">{{ $pengajuan->alasan_banding }}</p>
                    </div>
                    @endif

                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2">Surat permohonan banding belum diupload</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Upload / Status Penerimaan ── --}}
            @if($suratPenerimaan)
            {{-- Sudah dikirim --}}
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-check-circle"></i> Penerimaan Banding Telah Dikirim
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success alert-permanent">
                        <i class="bi bi-info-circle"></i>
                        Surat penerimaan banding telah dikirim pada
                        <strong>
                            {{ $suratPenerimaan->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                        </strong>
                    </div>

                    <div class="file-preview">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center rounded gap-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-pdf text-danger me-3" style="font-size: 48px;"></i>
                                <div>
                                    <strong>{{ $suratPenerimaan->original_filename }}</strong><br>
                                    <small class="text-muted">
                                        {{ number_format($suratPenerimaan->file_size / 1024, 2) }} KB
                                    </small>
                                    @if($suratPenerimaan->keterangan)
                                    <br>
                                    <small class="text-info">
                                        <i class="bi bi-info-circle"></i> {{ $suratPenerimaan->keterangan }}
                                    </small>
                                    @endif
                                </div>
                            </div>
                            <div class="btn-group-vertical">
                                <a href="{{ route('de.permohonan-banding.download', $pengajuan->id) }}" class="btn btn-info btn-sm mb-2">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                                <form action="{{ route('de.permohonan-banding.destroy', $pengajuan->id) }}" method="POST" id="formHapus">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="confirmDelete()">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning alert-permanent mt-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Catatan:</strong> Menghapus file akan mengembalikan status ke "Banding Diajukan"
                        dan Anda perlu mengupload ulang.
                    </div>
                </div>
            </div>

            @else
            {{-- Belum dikirim — tampilkan form upload --}}
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-upload"></i> Upload Surat Penerimaan Banding
                    </h5>
                </div>
                <div class="card-body">

                    @if($pengajuan->status !== \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN)
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Status pengajuan saat ini adalah
                        <strong>{{ $pengajuan->status_label }}</strong>.
                        Penerimaan hanya dapat dikirim ketika status "Banding Diajukan".
                    </div>
                    @else

                    <form action="{{ route('de.permohonan-banding.kirim', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" id="formUploadPenerimaan">
                        @csrf

                        {{-- Upload Zone --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-file-pdf"></i>
                                File Surat Penerimaan Banding (PDF)
                                <span class="text-danger">*</span>
                            </label>

                            <div class="upload-zone" id="uploadZone">
                                <input type="file" class="d-none @error('file_surat_penerimaan') is-invalid @enderror" id="file_surat_penerimaan" name="file_surat_penerimaan" accept=".pdf" required>

                                <div id="uploadPlaceholder">
                                    <i class="bi bi-cloud-upload" style="font-size: 48px; color: #6c757d;"></i>
                                    <p class="mt-3 mb-1 fw-bold">Upload file PDF ke sini</p>
                                    <p class="text-muted small mb-0">Maksimal 5 MB</p>
                                </div>

                                <div id="filePreview" style="display: none;">
                                    <i class="bi bi-file-pdf text-danger" style="font-size: 48px;"></i>
                                    <p class="mt-3 mb-1 fw-bold" id="fileName"></p>
                                    <p class="text-muted small mb-2" id="fileSize"></p>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFile()">
                                        <i class="bi bi-x-circle"></i> Batalkan
                                    </button>
                                </div>
                            </div>

                            @error('file_surat_penerimaan')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Keterangan --}}
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-chat-left-text"></i> Keterangan
                                <small class="text-muted">(Opsional)</small>
                            </label>
                            <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3" placeholder="Catatan tambahan jika diperlukan...">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('de.permohonan-banding') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="bi bi-send"></i> Kirim Penerimaan Banding
                            </button>
                        </div>
                    </form>

                    @endif
                </div>
            </div>
            @endif

            {{-- ── Info Pengajuan ── --}}
            <div class="card mt-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Permohonan Banding</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th>Program Studi</th>
                            <td>: {{ $pengajuan->studyProgram->name }}</td>
                        </tr>
                        <tr>
                            <th>Universitas</th>
                            <td>: {{ $pengajuan->studyProgram->university->name }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Akreditasi</th>
                            <td>: {{ $pengajuan->jenis_akreditasi_label }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Banding Diajukan</th>
                            <td>:
                                {{ $pengajuan->tanggal_permohonan_banding
                                    ? $pengajuan->tanggal_permohonan_banding->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Penerimaan Banding</th>
                            <td>:
                                {{ $pengajuan->tanggal_penerimaan_banding
                                    ? $pengajuan->tanggal_penerimaan_banding->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('permohonan_banding', 'de','label_long_for','text-dark') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>

        {{-- ── SIDEBAR ── --}}
        <div class="col-lg-4">

            {{-- Riwayat Status --}}
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Status</h5>
                </div>
                <div class="card-body" style="max-height:400px;overflow-y:auto;">
                    @php
                    $logs = $pengajuan->statusLog
                    ->whereIn('status_to', [
                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
                    ])
                    ->sortBy('changed_at')
                    ->unique('status_to');
                    @endphp

                    @forelse($logs as $log)
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0 pt-1">
                            <i class="bi bi-circle-fill text-secondary" style="font-size:8px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <strong>
                                {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label']
                                    ?? $log->status_to }}
                            </strong><br>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($log->changed_at ?? $log->created_at)
                                    ->locale('id')->translatedFormat('d M Y H:i') }}
                            </small>
                            {{-- @if($log->keterangan)
                            <br>
                            <small class="text-muted fst-italic">{{ $log->keterangan }}</small>
                            @endif --}}
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center small mb-0">Belum ada riwayat</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
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
                <p>Apakah Anda yakin ingin menghapus file surat penerimaan banding ini?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i>
                    Status pengajuan akan dikembalikan ke <strong>"Banding Diajukan"</strong>.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="submitDelete()">
                    <i class="bi bi-trash"></i> Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ── Upload Zone ──────────────────────────────────────────────
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('file_surat_penerimaan');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const filePreview = document.getElementById('filePreview');

    if (uploadZone) {
        uploadZone.addEventListener('click', () => {
            if (!fileInput.files.length) fileInput.click();
        });

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
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect();
            }
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', handleFileSelect);
    }

    function handleFileSelect() {
        const file = fileInput.files[0];
        if (!file) return;

        if (file.type !== 'application/pdf') {
            Swal.fire('Perhatian', 'File harus berformat PDF!', 'warning');
            fileInput.value = '';
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire('Perhatian', 'Ukuran file maksimal 5 MB!', 'warning');
            fileInput.value = '';
            return;
        }

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

    // ── Form Submit — disable SETELAH submit ─────────────────────
    const formUpload = document.getElementById('formUploadPenerimaan');
    if (formUpload) {
        formUpload.addEventListener('submit', function() {
            const btn = document.getElementById('btnSubmit');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
            }
        });
    }

    // ── Delete ───────────────────────────────────────────────────
    function confirmDelete() {
        new bootstrap.Modal(document.getElementById('modalConfirmDelete')).show();
    }

    function submitDelete() {
        document.getElementById('formHapus').submit();
    }

</script>
@endpush
