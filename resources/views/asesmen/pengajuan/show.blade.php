@extends('layouts.template.app')

@section('title', 'Detail Permohonan Akreditasi - ' . $pengajuan->nomor_pengajuan)

@push('styles')
<style>
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 5px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #6c757d;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #e0e0e0;
    }

    .timeline-item.completed::before {
        background: #28a745;
        box-shadow: 0 0 0 2px #28a745;
    }

    .action-card {
        border-left: 4px solid #0d6efd;
        animation: slideIn 0.3s ease;
    }

    /* Timeline Styles */
    .timeline-section {
        position: relative;
        margin-bottom: 2rem;
    }

    .timeline-phase-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .timeline-phase-header.completed {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .timeline-phase-header.in-progress {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    }

    .timeline-items {
        position: relative;
        padding-left: 2rem;
    }

    .timeline-items::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e0e0e0;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
        display: flex;
        gap: 1rem;
    }

    .timeline-item-icon {
        position: absolute;
        left: -1.65rem;
        top: 0.25rem;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #e0e0e0;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
        transition: all 0.3s;
    }

    .timeline-item.completed .timeline-item-icon {
        background: #28a745;
        border-color: #28a745;
        color: white;
    }

    .timeline-item.pending .timeline-item-icon {
        background: #f8f9fa;
        border-color: #dee2e6;
        color: #adb5bd;
    }

    .timeline-item.current .timeline-item-icon {
        background: #ffc107;
        border-color: #ffc107;
        color: white;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
        }

        50% {
            box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
        }
    }

    .timeline-item-content {
        flex: 1;
        background: #f8f9fa;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border-left: 3px solid #dee2e6;
        transition: all 0.3s;
    }

    .timeline-item.completed .timeline-item-content {
        background: #d4edda;
        border-left-color: #28a745;
    }

    .timeline-item.current .timeline-item-content {
        background: #fff3cd;
        border-left-color: #ffc107;
    }

    .timeline-item-content:hover {
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .timeline-item-label {
        font-weight: 600;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .timeline-item-date {
        font-size: 0.875rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .timeline-item.completed .timeline-item-date {
        color: #155724;
    }

    .timeline-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
    }

    .phase-progress {
        margin-top: 0.5rem;
        font-size: 0.875rem;
    }

    .phase-progress-bar {
        height: 4px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 2px;
        overflow: hidden;
        margin-top: 0.25rem;
    }

    .phase-progress-fill {
        height: 100%;
        background: white;
        transition: width 0.3s;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .processing-stats {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 15px;
    }

    .processing-stats .stat-item {
        text-align: center;
        padding: 10px;
    }

    .processing-stats .stat-item h4 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .file-preview-card {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
    }

    .file-preview-card.has-file {
        border-color: #28a745;
        background: #d4edda;
    }

    .upload-area {
        border: 2px dashed #ced4da;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .upload-area:hover {
        border-color: #0d6efd;
        background: #f8f9fa;
    }

    .upload-area.dragover {
        border-color: #28a745;
        background: #d4edda;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4>
                <i class="bi bi-file-earmark-text"></i>
                {{ $pengajuan->judul }}
            </h4>
            <p class="text-muted mb-0">
                Nomor: {{ $pengajuan->nomor_pengajuan }}
            </p>
        </div>
        <div>
            <span class="badge {{ $pengajuan->status_badge_class }} fs-6 text-wrap">
                {{ $pengajuan->status_label }}
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            @if(in_array($pengajuan->status, [\App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM]))
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-info-circle"></i>
                Mohon menunggu LAMDEPILAR melakukan verifikasi surat permohonan akreditasi dari program studi
            </div>
            @endif

            <!-- SECTION: Proses & Preview LED -->
            @if(in_array($pengajuan->status, [\App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA, \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI]))
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-info-circle"></i>
                Mohon menunggu Validasi Dokumen selesai dilakukan
            </div>
            @include('asesmen.pengajuan.components.modal-upload')
            @endif

            @if(in_array($pengajuan->status, [\App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED]))
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-info-circle"></i>
                Mohon menunggu Pelaporan Validasi Dokumen selesai dilakukan
            </div>
            @endif

            @if(in_array($pengajuan->status, [\App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,\App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION]))
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-info-circle"></i>
                Mohon menunggu proses valdasi Dokumen selesai dilakukan
            </div>
            @endif

            @if(in_array($pengajuan->status, [\App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED]))
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-info-circle"></i>
                Terdapat revisi dokumen Dokumen. Mohon periksa kembali dan lakukan revisi dokumen berdasarkan catatan oleh reviewer
            </div>
            @endif

            @if(in_array($pengajuan->status, [\App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN]))
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-info-circle"></i>
                Dokumen Dokumen telah selesai divalidasi dan diproses. Selanjutnya akan dilakukan penugasan Asesor untuk Asesmen Kecukupan
            </div>
            @endif

            @if(in_array($pengajuan->status, [\App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA]))
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-info-circle"></i>
                Permohonan akreditasi program studi telah diterima. Mohon tunggu LAMDEPILAR melakukan pengiriman Formulir Pembayaran dan Template Dokumen
            </div>
            @endif

            @if(in_array($pengajuan->status, [\App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM]))
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-info-circle"></i>
                Formulir Pembayaran serta Template Dokumen telah dikirim oleh LAMDEPILAR. Mohon tunggu LAMDEPILAR melakukan permintaan pembayaran.
            </div>
            @endif

            @if(in_array($pengajuan->status,[
            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
            \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
            \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
            \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN
            ]))
            <div class="alert alert-info alert-permanent border-start border-4 border-primary mb-4">
                <div class="d-flex align-items-start">
                    <i class="bi bi-info-circle-fill fs-4 me-3 text-primary"></i>

                    <div class="flex-grow-1">
                        <h5 class="mb-1 fw-semibold">
                            Proses Asesmen Sedang Berjalan
                        </h5>

                        <p class="mb-2">
                            Status Permohonan akreditasi saat ini:
                            <strong class="text-dark text-wrap">{{ $pengajuan->status_label }}</strong>
                        </p>

                        <ul class="mb-2 ps-3 small">
                            <li>
                                Tim asesor telah <strong>ditugaskan</strong> dan sedang melakukan
                                <strong>penilaian terhadap LED, Suplemen, dan LKPS</strong>.
                            </li>
                            <li>
                                Selama proses asesmen berlangsung, <strong>data Permohonan akreditasi bersifat terkunci</strong>
                                dan tidak dapat diubah.
                            </li>
                            <li>
                                Hasil asesmen akan tersedia setelah seluruh proses ini selesai dan dilaporkan.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <!-- ACTION: Upload/Isi Draft LED -->
            @if(($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI && $pengajuan->pembayaran && $pengajuan->pembayaran->status_pembayaran === 'terverifikasi')||$pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-info-circle"></i>
                Terima kasih telah melakukan pembayaran akreditasi. Pembayaran Anda telah berhasil diverifikasi oleh bagian keuangan LAMDEPILAR.
            </div>
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-exclamation-circle text-warning"></i>
                        Aksi Diperlukan: Lengkapi LED & LKPS
                    </h5>

                    <p class="text-muted mb-3">
                        Silakan lengkapi LED dan LKPS melalui form pengisian di sistem, ataupun upload file secara langsung.
                    </p>

                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <i class="bi bi-pencil-square fs-1 text-primary mb-3"></i>
                            <h5 class="mb-2">Pengisian LED & LKPS</h5>
                            <p class="text-muted mb-3">
                                Mohon isi dan lengkapi seluruh data LED (kualitatif) dan LKPS (kuantitatif) terlebih dahulu.
                            </p>

                            <a href="{{ route('pengajuan.borang-online', $pengajuan->id) }}" class="btn btn-primary">
                                <i class="bi bi-pencil-square"></i> Buka Halaman Pengisian LED & LKPS
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

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
                                    <p class="mb-2"><strong>Silahkan upload file di sini</strong></p>
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

            <!-- ACTION: Upload Pembayaran -->
            @if(in_array($pengajuan->status,[\App\Models\PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN,\App\Models\PengajuanAkreditasi::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN]))

            @if($pengajuan->pembayaran->status_pembayaran == 'menunggu_pembayaran')
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-info-circle"></i>
                Permohonan akreditasi dan Invoice pembayaran berhasil dibuat. Silahkan isi data berikut dan silahkan tunggu bagian keuangan LAMDEPILAR melakukan verifikasi
            </div>
            @elseif($pengajuan->pembayaran->status_pembayaran == 'menunggu_verifikasi')
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-info-circle"></i>
                Terima kasih telah mengirimkan bukti pembayaran dan formulir pembayaran. Mohon menunggu proses verifikasi pembayaran oleh bagian keuangan LAMDEPILAR
            </div>
            @elseif($pengajuan->pembayaran->status_pembayaran == 'upload_ulang')
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-info-circle"></i>
                Bagian keuangan LAMDEPILAR meminta prodi melakukan upload ulang formulir pembayaran dan bukti pembayaran
            </div>
            @endif

            @if($pengajuan->pembayaran && in_array($pengajuan->pembayaran->status_pembayaran,['menunggu_pembayaran','upload_ulang']))
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-credit-card text-success"></i>
                        Aksi Diperlukan: Upload Bukti Pembayaran
                    </h5>

                    @if($pengajuan->pembayaran)
                    <div class="alert alert-info alert-permanent">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Invoice:</strong> {{ $pengajuan->pembayaran->nomor_invoice }}
                            </div>
                            <div class="col-md-6">
                                <strong>Jumlah:</strong> Rp {{ number_format($pengajuan->pembayaran->jumlah_pembayaran, 0, ',', '.') }}
                            </div>
                            <div class="col-md-6">
                                <strong>Jatuh Tempo:</strong> {{ \App\Libraries\Date::tglIndo($pengajuan->pembayaran->tanggal_jatuh_tempo) }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <form action="{{ route('pengajuan.upload-pembayaran', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Pembayaran</label>
                                <input type="datetime-local" name="tanggal_pembayaran" class="form-control @error('tanggal_pembayaran') is-invalid @enderror" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                @error('tanggal_pembayaran')
                                <span class="invalid-feedback" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Formulir dan Bukti Pembayaran yang Telah Diisi</label>
                                <input type="file" name="formulir_pembayaran" class="form-control @error('formulir_pembayaran') is-invalid @enderror" required>
                                <small class="text-muted">Format: XLSX | Max: 5 MB</small>
                                @error('formulir_pembayaran')
                                <span class="invalid-feedback" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                            {{-- <div class="col-md-6">
                                <label class="form-label fw-bold">Bukti Pembayaran</label>
                                <input type="file" name="bukti_pembayaran" class="form-control @error('bukti_pembayaran') is-invalid @enderror" accept=".xlsx,.pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted">Format: XLSX, PDF, JPG, PNG | Max: 5 MB</small>
                                @error('bukti_pembayaran')
                                <span class="invalid-feedback" role="alert">
                                    {{ $message }}
                            </span>
                            @enderror
                        </div> --}}
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Catatan Pembayaran</label>
                            <textarea name="catatan_pembayaran" class="form-control" rows="3" placeholder="Masukkan catatan pembayaran di sini (apabila ada)"></textarea>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-upload"></i> Upload {{ $pengajuan->pembayaran->status_pembayaran == 'upload_ulang' ? 'Ulang ' : '' }}Bukti Pembayaran
                            </button>
                        </div>
                </div>
                </form>
            </div>
        </div>
        @endif
        @endif

        @if($pengajuan->pembayaran)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-credit-card"></i> Informasi Pembayaran {{ $pengajuan->pembayaran->status_pembayaran == 'upload_ulang' ? '(Versi Sebelumnya)' : '' }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="text-muted small">Nomor Invoice</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->pembayaran->nomor_invoice }}</p>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="text-muted small">Jumlah</label>
                        <p class="fw-bold mb-0">
                            Rp {{ number_format($pengajuan->pembayaran->jumlah_pembayaran, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="text-muted small">Status</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $pengajuan->pembayaran->status_pembayaran === 'terverifikasi' ? 'success' : 'warning' }}">
                                {{ strtoupper($pengajuan->pembayaran->status_pembayaran) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="text-muted small">Jatuh Tempo</label>
                        <p class="fw-bold mb-0">
                            {{ \App\Libraries\Date::tglIndo($pengajuan->pembayaran->tanggal_jatuh_tempo) }}
                        </p>
                    </div>
                    @if($pengajuan->pembayaran->tanggal_pembayaran)
                    <div class="col-md-6 mb-2">
                        <label class="text-muted small">Tanggal Pembayaran dari Prodi</label>
                        <p class="fw-bold mb-0">
                            {{ \App\Libraries\Date::tglIndo($pengajuan->pembayaran->tanggal_pembayaran) }}
                        </p>
                    </div>
                    @endif
                    @if($pengajuan->pembayaran->verified_by)
                    <div class="col-md-6 mb-2">
                        <label class="text-muted small">Diverifikasi Oleh</label>
                        <p class="fw-bold mb-0">
                            {{ $pengajuan->pembayaran->verifier->name }}
                        </p>
                    </div>
                    @endif
                </div>

                @if($pengajuan->pembayaran->catatan_pembayaran)
                <hr>
                <label class="text-muted small">Catatan Pembayaran (dari Prodi)</label>
                <p class="mb-0">{{ $pengajuan->pembayaran->catatan_pembayaran }}</p>
                @endif

                @if($pengajuan->pembayaran->catatan_verifikasi)
                <hr>
                <label class="text-muted small">Catatan Verifikasi (dari Keuangan)</label>
                <p class="mb-0">{{ $pengajuan->pembayaran->catatan_verifikasi }}</p>
                @endif

                @if($pengajuan->pembayaran->alasan_penolakan)
                <hr>
                <label class="text-muted small text-danger">Alasan Penolakan (dari Keuangan)</label>
                <p class="mb-0 text-danger">{{ $pengajuan->pembayaran->alasan_penolakan }}</p>
                @endif
            </div>
        </div>
        @endif

        <hr>

        <!-- Informasi Permohonan akreditasi -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle"></i> Informasi Permohonan Akreditasi
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Nomor Permohonan Akreditasi</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Program Studi</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->studyProgram->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Universitas/Institut</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->studyProgram->university->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Jenjang</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->studyProgram->degreeLevel->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Tahun Akreditasi</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->tahun_akreditasi }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Jenis Akreditasi</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->jenis_akreditasi_label }}</p>
                    </div>
                    @if($pengajuan->pengaju)
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Pemohon</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->pengaju->name }}</p>
                        <small class="text-wrap">{{ $pengajuan->pengaju->email }}</small>
                    </div>
                    @endif
                    {{-- <div class="col-md-6 mb-3">
                            <label class="text-muted small">DE</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->deskEvaluator->name ?? 'Belum ditugaskan' }}</p>
                </div> --}}
            </div>

            @if($pengajuan->catatan_pengaju)
            <hr>
            <label class="text-muted small">Catatan Pemohon</label>
            <p class="mb-0">{{ $pengajuan->catatan_pengaju }}</p>
            @endif
        </div>
    </div>

    <!-- Dokumen -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-folder"></i> Dokumen
            </h5>
        </div>
        <div class="card-body">
            @forelse($pengajuan->dokumen->groupBy('jenis_dokumen_alias') as $jenis => $docs)
            <div class="mb-3">
                <h6 class="fw-bold text-primary">
                    {{ str_replace('_', ' ', ucwords($jenis)) }}
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Nama File</th>
                                <th>Versi</th>
                                <th>Upload Oleh</th>
                                <th>Tanggal</th>
                                <th>Ukuran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($docs as $doc)
                            <tr>
                                <td>
                                    {{ $doc->original_filename }}
                                    @if($doc->is_latest)
                                    <span class="badge bg-success">Latest</span>
                                    @endif
                                </td>
                                <td>v{{ $doc->versi }}</td>
                                <td>{{ $doc->uploader->name ?? '-' }}</td>
                                <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $doc->file_size_formatted ?? '' }}</td>
                                <td>
                                    <a href="{{ $doc->download_url }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @empty
            <p class="text-muted mb-0">Belum ada dokumen yang diupload.</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Sidebar -->
<div class="col-md-4">
    <!-- Timeline -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i> Timeline Proses Akreditasi
            </h5>
        </div>
        <div class="card-body">
            <div class="timeline">
                @foreach($pengajuan->timelineItems() as $step => $item)
                @php
                $isDone = $item['state'] === 'done';
                $isCurrent = $item['state'] === 'current';
                $itemColor = $item['color']; // success|warning|secondary

                $iconColor = $isDone ? 'text-success' : ($isCurrent ? 'text-' . $itemColor : 'text-muted');
                @endphp

                <div class="d-flex mb-3">
                    <div class="me-3">
                        @if($isDone)
                        <i class="bi bi-check-circle-fill {{ $iconColor }}" style="font-size:1.2rem;"></i>
                        @elseif($isCurrent)
                        <i class="bi bi-hourglass-split {{ $iconColor }}" style="font-size:1.2rem;"></i>
                        @else
                        <i class="bi bi-circle {{ $iconColor }}"></i>
                        @endif
                    </div>

                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <strong class="{{ ($isDone || $isCurrent) ? 'text-'.$itemColor : 'text-muted' }}">
                                <i class="{{ $item['icon'] }} me-1"></i>
                                {{ $item['label'] }}
                            </strong>

                            @if($item['date'])
                            <span class="badge bg-{{ $itemColor }}">
                                {{ $item['date']->format('d M Y') }}
                            </span>
                            @endif
                        </div>

                        @if($item['date'])
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> {{ $item['date']->format('H:i') }} WIB
                        </small>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Status Log -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-list-check"></i> Log Aktivitas
            </h5>
        </div>
        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
            @forelse($pengajuan->statusLog->sortBy('changed_at') as $log)
            <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                <div class="d-flex justify-content-between">
                    <small class="text-muted">
                        {{ $log->changed_at->format('d/m/Y H:i') }}
                    </small>
                    <small class="text-muted">
                        {{ $log->changedBy->name }}
                    </small>
                </div>
                <p class="mb-0 small">
                    <span class="badge bg-secondary">{{ str_replace('_', ' ', $log->status_from_label) }}</span>
                    <i class="bi bi-arrow-right"></i>
                    <span class="badge bg-primary">{{ str_replace('_', ' ', $log->status_to_label) }}</span>
                </p>
                @if($log->keterangan)
                <small class="text-muted">{{ $log->keterangan }}</small>
                @endif
            </div>
            @empty
            <p class="text-muted small mb-0">Belum ada aktivitas</p>
            @endforelse
        </div>
    </div>
</div>
</div>
</div>
@push('scripts')
<script>
    // File Upload Handling
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('inputDraftBorang');
    const filePreview = document.getElementById('filePreview');
    const btnUploadBorang = document.getElementById('btnUploadBorang');
    const formUpload = document.getElementById('formUploadBorang');
    const btnResetBorangShow = document.getElementById('btnResetBorangShow');

    if (btnResetBorangShow) btnResetBorangShow.addEventListener('click', function() {
        window.location.href = '{{ route("pengajuan.borang-online", $pengajuan->id) }}#reset';
    });

    if (fileInput) {
        // Click to upload
        if (uploadArea) uploadArea.addEventListener('click', (e) => {
            if (e.target !== uploadArea && e.target.closest('.btn')) return;
            fileInput.click();
        });

        // File selected
        fileInput.addEventListener('change', (e) => {
            handleFileSelect(e.target.files[0]);
        });

        // Drag & Drop
        if (uploadArea) uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        if (uploadArea) uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        if (uploadArea) uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');

            const file = e.dataTransfer.files[0];
            if (file && file.name.endsWith('.docx')) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect(file);
            } else {
                alert('Hanya file DOCX yang diperbolehkan!');
            }
        });
    }

    function handleFileSelect(file) {
        if (!file) return;

        // Validate file
        if (!file.name.endsWith('.docx')) {
            alert('Hanya file DOCX yang diperbolehkan!');
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert('Ukuran file maksimal 10 MB!');
            return;
        }

        // Show preview
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatFileSize(file.size);

        uploadArea.classList.add('d-none');
        filePreview.classList.remove('d-none');
        filePreview.classList.add('has-file');
        btnUploadBorang.disabled = false;
    }

    function removeFile() {
        fileInput.value = '';
        uploadArea.classList.remove('d-none');
        filePreview.classList.add('d-none');
        filePreview.classList.remove('has-file');
        btnUploadBorang.disabled = true;
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Form Submit
    if (formUpload) formUpload.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!fileInput.files[0]) {
            alert('Pilih file terlebih dahulu!');
            return;
        }

        const formData = new FormData(formUpload);
        btnUploadBorang.disabled = true;
        btnUploadBorang.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';

        try {
            const response = await fetch('{{ route("pengajuan.upload-draft", $pengajuan->id) }}', {
                method: 'POST'
                , body: formData
                , headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (data.success || response.ok) {
                alert('✅ Draft LED berhasil diupload!');
                window.location.reload();
            } else {
                alert('❌ Upload gagal: ' + (data.message || 'Terjadi kesalahan'));
                btnUploadBorang.disabled = false;
                btnUploadBorang.innerHTML = '<i class="bi bi-upload"></i> Upload Draft LED';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('❌ Terjadi kesalahans: ' + error.message);
            btnUploadBorang.disabled = false;
            btnUploadBorang.innerHTML = '<i class="bi bi-upload"></i> Upload Draft LED';
        }
    });

    // Process Borang
    async function processBorang(pengajuanId) {
        const btn = document.getElementById('btnProcessBorang');
        const resultDiv = document.getElementById('processResult');

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sedang memproses...';
        }

        if (resultDiv) {
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = `
            <div class="alert alert-info alert-permanent">
                <div class="d-flex align-items-center">
                    <div class="spinner-border text-primary me-3" role="status"></div>
                    <div>
                        <strong>Memproses dokumen DOCX...</strong><br>
                        <small>Sedang melakukan pembacaan data dari borang. Proses ini memerlukan 10-30 detik.</small>
                    </div>
                </div>
            </div>
        `;
        }

        try {
            const response = await fetch(`/permohonan-akreditasi/${pengajuanId}/process-borang`, {
                method: 'POST'
                , headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    , 'Accept': 'application/json'
                , }
            });

            const data = await response.json();

            if (data.success) {
                if (resultDiv) {
                    resultDiv.innerHTML = `
                    <div class="alert alert-success alert-permanent">
                        <i class="bi bi-check-circle"></i>
                        <strong>Pembacaan data berhasil!</strong><br>
                        <ul class="mb-0 mt-2">
                            <li>Total Bagian: <strong>${data.data.total_sections}</strong></li>
                            <li>Total Tabel: <strong>${data.data.total_tables}</strong></li>
                            <li>Berhasil Diproses: <strong>${data.data.parsed_tables}/${data.data.total_tables}</strong></li>
                            <li>Kelengkapan: <strong>${data.data.completion}%</strong></li>
                        </ul>
                    </div>
                `;
                }

                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                if (resultDiv) {
                    resultDiv.innerHTML = `
                    <div class="alert alert-danger alert-permanent">
                        <i class="bi bi-x-circle"></i>
                        <strong>Pembacaan data gagal:</strong><br>
                        ${data.message}
                    </div>
                `;
                }
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-gear"></i> Proses & Validasi Borang';
                }
            }
        } catch (error) {
            console.error('Error:', error);
            if (resultDiv) {
                resultDiv.innerHTML = `
                <div class="alert alert-danger alert-permanent">
                    <i class="bi bi-x-circle"></i>
                    <strong>Terjadi kesalahan:</strong><br>
                    ${error.message}
                </div>
            `;
            }
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-gear"></i> Proses & Validasi Borang';
            }
        }
    }

    // ✅ Upload Kuantitatif Handler
    function triggerUploadKuantitatif() {
        document.getElementById('inputKuantitatif').click();
    }

    async function handleUploadKuantitatif(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate
        if (!file.name.match(/\.(xlsx|xls)$/)) {
            Swal.fire('Error', 'File harus berformat Excel (.xlsx atau .xls)', 'error');
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            Swal.fire('Error', 'Ukuran file maksimal 10MB', 'error');
            return;
        }

        // Show confirmation
        const result = await Swal.fire({
            icon: 'question'
            , title: 'Upload Data Kuantitatif?'
            , html: `
            <p>File: <strong>${file.name}</strong></p>
            <p>Ukuran: <strong>${formatFileSize(file.size)}</strong></p>
            <p class="text-muted mt-2">Data tabel akan diproses dan diisi otomatis</p>
        `
            , showCancelButton: true
            , confirmButtonText: 'Ya, Upload'
            , cancelButtonText: 'Batal'
        });

        if (!result.isConfirmed) return;

        // Upload
        const formData = new FormData();
        formData.append('file_kuantitatif', file);

        showLoading();

        try {
            const response = await fetch('{{ route("pengajuan.upload-kuantitatif", $pengajuan->id) }}', {
                method: 'POST'
                , headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    , 'Accept': 'application/json'
                }
                , body: formData
            });

            const data = await response.json();
            hideLoading();

            if (data.success) {
                // Show import progress
                showImportProgress('kuantitatif', data.data.dokumen_id);

                Swal.fire({
                    icon: 'info'
                    , title: 'Sedang Diproses'
                    , text: 'File sedang diproses di background...'
                    , showConfirmButton: false
                    , allowOutsideClick: false
                    , didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Start polling
                pollImportStatus(data.data.dokumen_id, 'kuantitatif');
            } else {
                throw new Error(data.message || 'Upload gagal');
            }
        } catch (error) {
            hideLoading();
            Swal.fire('Error', error.message, 'error');
        }
    }
    // Smooth scroll to current phase
    document.addEventListener('DOMContentLoaded', function() {
        const currentItem = document.querySelector('.timeline-item.current');
        if (currentItem) {
            currentItem.scrollIntoView({
                behavior: 'smooth'
                , block: 'center'
            });
        }
    });

    // Tooltip untuk timeline items
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

</script>
@endpush
@endsection
