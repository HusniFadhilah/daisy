@extends('layouts.template.app')

@section('title', 'Detail Pengajuan - ' . $pengajuan->nomor_pengajuan)

@push('styles')
<style>
    .action-card {
        border-left: 4px solid #0d6efd;
    }

    .review-form {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }

    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #932136, #870820);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>
                <i class="bi bi-file-earmark-text"></i>
                {{ $pengajuan->nomor_pengajuan }}
            </h2>
            <p class="text-muted mb-0">
                {{ $pengajuan->studyProgram->name }} -
                {{ $pengajuan->tahun_akreditasi }}
            </p>
        </div>
        <div>
            <span class="badge {{ $pengajuan->status_badge_class }} fs-6">
                {{ $pengajuan->status_label }}
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-12 col-lg-8">
            <!-- ACTION: Kirim Form Borang (Langkah 3) -->
            @if($pengajuan->status === 'surat_permohonan_diterima')
            <div class="card action-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-arrow-down"></i>
                        Aksi Diperlukan: Kirim Form Borang
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        Surat permohonan telah diterima. Kirimkan form Template LED ke prodi untuk dilengkapi.
                    </p>

                    <form action="{{ route('de.pengajuan.kirim-borang', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" id="formKirimBorang">
                        @csrf

                        {{-- Pilihan Metode Pengiriman --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Metode Pengiriman Template <span class="text-danger">*</span>
                            </label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="metode_kirim" id="metodeLink" value="link" checked>
                                <label class="btn btn-outline-primary" for="metodeLink">
                                    <i class="bi bi-link-45deg"></i> Kirim Link Template
                                </label>

                                <input type="radio" class="btn-check" name="metode_kirim" id="metodeUpload" value="upload">
                                <label class="btn btn-outline-primary" for="metodeUpload">
                                    <i class="bi bi-cloud-upload"></i> Upload File Template
                                </label>
                            </div>
                        </div>

                        {{-- OPTION 1: Link Template --}}
                        <div id="divLink" class="mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-link"></i> Link Template LED
                                    </h6>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            URL Template <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-globe"></i>
                                            </span>
                                            <input type="url" name="template_link" id="template_link" class="form-control" value="{{ url('pengajuan/' . $pengajuan->id . '/borang/download-template') }}" placeholder="https://example.com/template.docx">
                                        </div>
                                        <small class="text-muted">
                                            Link ke template LED yang dapat diakses oleh prodi
                                        </small>
                                    </div>

                                    <div class="alert alert-info alert-permanent mb-0">
                                        <strong><i class="bi bi-info-circle"></i> Default Template:</strong>
                                        <p class="mb-2">
                                            Template default tersedia di:
                                            <a href="{{ route('pengajuan.borang.download-template', $pengajuan->id) }}" target="_blank" class="alert-link">
                                                <i class="bi bi-download"></i> Download Preview
                                            </a>
                                        </p>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="useDefaultLink()">
                                                <i class="bi bi-arrow-clockwise"></i> Gunakan Link Default
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearLink()">
                                                <i class="bi bi-x-circle"></i> Kosongkan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- OPTION 2: Upload File --}}
                        <div id="divUpload" class="mb-4" style="display: none;">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-cloud-upload"></i> Upload File Template
                                    </h6>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            File Template LED <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" name="borang_template" id="borang_template" class="form-control" accept=".docx,.doc">
                                        <small class="text-muted">
                                            Format: DOCX, DOC | Maksimal: 10 MB
                                        </small>
                                    </div>

                                    <div class="alert alert-warning alert-permanent mb-0">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <strong>Perhatian:</strong> File yang diupload akan disimpan di server
                                        dan dapat didownload oleh prodi.
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Keterangan (untuk kedua metode) --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Petunjuk pengisian atau informasi tambahan untuk prodi..."></textarea>
                            <small class="text-muted">
                                Keterangan akan dikirim bersama notifikasi email ke prodi
                            </small>
                        </div>

                        {{-- Preview Info --}}
                        <div class="card border-info mb-3">
                            <div class="card-body">
                                <h6 class="fw-bold text-info mb-2">
                                    <i class="bi bi-info-circle"></i> Yang Akan Terjadi:
                                </h6>
                                <ul class="mb-0 small">
                                    <li id="infoMetode">Link template akan dikirim ke email prodi</li>
                                    <li>Prodi dapat mengakses template melalui link/download file</li>
                                    <li>Status pengajuan akan diupdate ke <code>borang_dikirim</code></li>
                                    <li>Notifikasi email akan dikirim ke UPPS</li>
                                </ul>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="bi bi-send"></i> Kirim Template ke Prodi
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- ACTION: Review Kesiapan (Langkah 5a/5b) -->
            @if(in_array($pengajuan->status, ['draft_borang_diterima', 'borang_online_selesai']))
            @php
            $latestImport = $pengajuan->latestBorangImport;
            $draftBorang = $pengajuan->dokumen->where('jenis_dokumen', 'draft_borang')->where('is_latest', true)->first();
            @endphp

            <div class="card action-card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                        Aksi Diperlukan: Review Kesiapan Borang
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Alert Status --}}
                    <div class="alert alert-success alert-permanent mb-4">
                        <i class="bi bi-check-circle"></i>
                        <strong>Prodi telah mengupload draft LED.</strong><br>
                        Silakan review kelengkapan dan kesiapan borang sebelum melanjutkan ke tahap pembayaran.
                    </div>

                    {{-- Preview & Form Online Links --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card bg-primary bg-opacity-10 border-primary h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-eye text-white fs-1 mb-3 d-block"></i>
                                    <h6 class="fw-bold text-white">Preview LED HTML</h6>
                                    <p class="text-white small mb-3">
                                        Lihat preview LED yang sudah diproses<br>
                                        dari dokumen DOCX
                                    </p>
                                    @if($latestImport)
                                    <a href="{{ route('de.pengajuan.borang-view', [$pengajuan->id, $latestImport->id]) }}" class="btn btn-light" target="_blank">
                                        <i class="bi bi-eye"></i> Lihat Preview
                                    </a>
                                    @else
                                    <button class="btn btn-light" disabled>
                                        <i class="bi bi-eye-slash"></i> Belum Diproses
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-info bg-opacity-10 border-info h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-pencil-square fs-1 text-info mb-3 d-block"></i>
                                    <h6 class="fw-bold">Form Isian Online</h6>
                                    <p class="text-muted small mb-3">
                                        Lihat form online yang diisi prodi<br>
                                        (Read-only untuk DE)
                                    </p>
                                    <a href="{{ route('pengajuan.borang-online', $pengajuan->id) }}" class="btn btn-info" target="_blank">
                                        <i class="bi bi-pencil-square"></i> Lihat Form Online
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Processing Stats --}}
                    @if($latestImport)
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-graph-up"></i> Status Pemrosesan Data
                            </h6>
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="p-3 bg-white rounded shadow-sm">
                                        <h4 class="mb-0 text-primary">{{ $latestImport->total_sections }}</h4>
                                        <small class="text-muted">Bagian</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-white rounded shadow-sm">
                                        <h4 class="mb-0 text-success">{{ $latestImport->total_tables }}</h4>
                                        <small class="text-muted">Total Tabel</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-white rounded shadow-sm">
                                        <h4 class="mb-0 text-info">{{ $latestImport->parsed_tables }}</h4>
                                        <small class="text-muted">Terproses</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-white rounded shadow-sm">
                                        <h4 class="mb-0 text-warning">{{ $latestImport->completion_percentage }}%</h4>
                                        <small class="text-muted">Kelengkapan</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-top">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td width="200">
                                            <i class="bi bi-file-word text-primary"></i>
                                            <strong>File:</strong>
                                        </td>
                                        <td>{{ $latestImport->original_filename }}</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="bi bi-person text-success"></i>
                                            <strong>Diproses oleh:</strong>
                                        </td>
                                        <td>{{ $latestImport->importer->name ?? 'System' }}</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="bi bi-clock text-info"></i>
                                            <strong>Waktu:</strong>
                                        </td>
                                        <td>{{ $latestImport->imported_at->format('d M Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="bi bi-check-circle text-success"></i>
                                            <strong>Status:</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $latestImport->status === 'success' ? 'success' : 'warning' }}">
                                                {{ strtoupper($latestImport->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if($latestImport->status === 'failed')
                    <div class="alert alert-danger alert-permanent mb-4">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Pembacaan Data Gagal:</strong><br>
                        {{ $latestImport->parsing_notes }}
                    </div>
                    @endif
                    @endif

                    {{-- Download Draft Document --}}
                    @if($draftBorang)
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-file-earmark-word"></i> Dokumen Draft
                            </h6>
                            <div class="row small">
                                <div class="col-md-3">
                                    <i class="bi bi-file-word text-primary"></i>
                                    <strong>File:</strong><br>
                                    {{ $draftBorang->original_filename }}
                                </div>
                                <div class="col-md-3">
                                    <i class="bi bi-hdd text-info"></i>
                                    <strong>Ukuran:</strong><br>
                                    {{ $draftBorang->file_size_formatted ?? '-' }}
                                </div>
                                <div class="col-md-3">
                                    <i class="bi bi-clock text-warning"></i>
                                    <strong>Upload:</strong><br>
                                    {{ $draftBorang->created_at->format('d M Y H:i') }}
                                </div>
                                <div class="col-md-3">
                                    <i class="bi bi-tag text-secondary"></i>
                                    <strong>Versi:</strong><br>
                                    v{{ $draftBorang->versi ?? '1' }}
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-top">
                                <a href="{{ route('pengajuan.borang.export-docx', $pengajuan->id) }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-file-earmark-arrow-down"></i> Download Draft DOCX
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ============================================
             FORM REVIEW KESIAPAN
             ============================================ --}}
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-clipboard-check"></i> Form Review Kesiapan Borang
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('de.pengajuan.review', $pengajuan->id) }}" method="POST" id="formReviewKesiapan">
                                @csrf

                                {{-- Hasil Review --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        Hasil Review <span class="text-danger">*</span>
                                    </label>
                                    <select name="hasil_review" class="form-select" id="hasilReview" required>
                                        <option value="">-- Pilih Hasil Review --</option>
                                        <option value="siap">
                                            ✅ SIAP - Lanjut ke Pembayaran
                                        </option>
                                        <option value="belum_siap">
                                            ❌ BELUM SIAP - Perlu Revisi
                                        </option>
                                    </select>
                                    <small class="text-muted">
                                        Pilih "SIAP" jika borang sudah lengkap dan memenuhi syarat
                                    </small>
                                </div>

                                {{-- Catatan Review --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        Catatan Review <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="catatan_review" class="form-control" rows="5" required placeholder="Berikan catatan detail tentang hasil review:
- Kelengkapan data
- Validitas dokumen pendukung
- Format dan struktur borang
- Saran perbaikan (jika ada)"></textarea>
                                    <small class="text-muted">
                                        Minimal 5 karakter. Berikan feedback yang konstruktif.
                                    </small>
                                </div>

                                {{-- Checklist Kesiapan --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        Checklist Kesiapan (Opsional)
                                    </label>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="checklist[]" value="Kelengkapan data memenuhi standar" id="check1">
                                                <label class="form-check-label" for="check1">
                                                    Kelengkapan data memenuhi standar
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="checklist[]" value="Validitas dokumen pendukung terpenuhi" id="check2">
                                                <label class="form-check-label" for="check2">
                                                    Validitas dokumen pendukung terpenuhi
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="checklist[]" value="Format sesuai template LAMDEPILAR" id="check3">
                                                <label class="form-check-label" for="check3">
                                                    Format sesuai template LAMDEPILAR
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="checklist[]" value="Data kuantitatif terverifikasi" id="check4">
                                                <label class="form-check-label" for="check4">
                                                    Data kuantitatif terverifikasi
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="checklist[]" value="Narasi deskriptif lengkap dan jelas" id="check5">
                                                <label class="form-check-label" for="check5">
                                                    Narasi deskriptif lengkap dan jelas
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ============================================
                         PEMBAYARAN SECTION (Only if SIAP)
                         ============================================ --}}
                                <div id="divPembayaran" style="display: none;">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="bi bi-credit-card"></i> Generate Invoice Pembayaran
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info alert-permanent mb-3">
                                                <i class="bi bi-info-circle"></i>
                                                <strong>Perhatian:</strong> Invoice pembayaran akan otomatis digenerate
                                                setelah Anda submit review dengan hasil "SIAP".
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">
                                                        Jumlah Pembayaran (Rp) <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="number" name="jumlah_pembayaran" class="form-control" value="5000000" step="100000" min="0">
                                                    <small class="text-muted">
                                                        Default: Rp 5.000.000,- (sesuai ketentuan)
                                                    </small>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">
                                                        Jatuh Tempo (Hari) <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="number" name="jatuh_tempo_hari" class="form-control" value="14" min="1" max="30">
                                                    <small class="text-muted">
                                                        Jumlah hari dari hari ini. Default: 14 hari.
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="alert alert-warning alert-permanent mb-0">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                <strong>Catatan:</strong> Prodi akan menerima notifikasi invoice
                                                dan harus melakukan pembayaran sebelum jatuh tempo.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit Buttons --}}
                                <div class="d-flex gap-2 mt-4 pt-3 border-top">
                                    <button type="submit" class="btn btn-success btn-md">
                                        <i class="bi bi-send"></i> Submit Review Kesiapan
                                    </button>

                                    <button type="reset" class="btn btn-outline-secondary btn-md">
                                        <i class="bi bi-arrow-counterclockwise"></i> Reset Form
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================
     JAVASCRIPT: Show/Hide Pembayaran Section
     ============================================ --}}
            @push('scripts')
            <script>
                // Show/hide pembayaran field based on hasil review
                hasilReview = document.getElementById('hasilReview')
                if (hasilReview) hasilReview.addEventListener('change', function() {
                    const divPembayaran = document.getElementById('divPembayaran');
                    if (this.value === 'siap') {
                        divPembayaran.style.display = 'block';
                        divPembayaran.querySelector('input[name="jumlah_pembayaran"]').required = true;
                        divPembayaran.querySelector('input[name="jatuh_tempo_hari"]').required = true;
                    } else {
                        divPembayaran.style.display = 'none';
                        divPembayaran.querySelector('input[name="jumlah_pembayaran"]').required = false;
                        divPembayaran.querySelector('input[name="jatuh_tempo_hari"]').required = false;
                    }
                });

                // Form validation
                const formReviewKesiapan = document.getElementById('formReviewKesiapan')
                if (formReviewKesiapan) formReviewKesiapan.addEventListener('submit', function(e) {
                    const hasilReview = document.getElementById('hasilReview').value;
                    const catatan = document.querySelector('textarea[name="catatan_review"]').value;

                    if (!hasilReview) {
                        e.preventDefault();
                        alert('Mohon pilih hasil review!');
                        return false;
                    }

                    if (catatan.length < 5) {
                        e.preventDefault();
                        alert('Catatan review minimal 5 karakter!');
                        return false;
                    }

                    // Confirm submission
                    const confirmMsg = hasilReview === 'siap' ?
                        'Apakah Anda yakin borang SIAP dan akan melanjutkan ke pembayaran?' :
                        'Apakah Anda yakin borang BELUM SIAP dan perlu revisi dari prodi?';

                    if (!confirm(confirmMsg)) {
                        e.preventDefault();
                        return false;
                    }

                    return true;
                });

            </script>
            @endpush
            @endif

            <!-- ACTION: Verifikasi Pembayaran -->
            @if($pengajuan->status === 'pembayaran_diterima' && $pengajuan->pembayaran && $pengajuan->pembayaran->status_pembayaran === 'dibayar')
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-credit-card text-success"></i>
                        Aksi Diperlukan: Verifikasi Pembayaran
                    </h5>

                    <div class="alert alert-info alert-permanent">
                        <strong>Invoice:</strong> {{ $pengajuan->pembayaran->nomor_invoice }}<br>
                        <strong>Jumlah:</strong> Rp {{ number_format($pengajuan->pembayaran->jumlah_pembayaran, 0, ',', '.') }}<br>
                        <strong>Tanggal Pembayaran:</strong> {{ \App\Libraries\Date::tglIndo($pengajuan->pembayaran->tanggal_pembayaran) }}
                    </div>

                    <form action="{{ route('de.pengajuan.verifikasi-pembayaran', $pengajuan->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan Verifikasi</label>
                            <textarea name="catatan_verifikasi" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="status" value="verified" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Verifikasi & Setujui
                            </button>
                            <button type="submit" name="status" value="ditolak" class="btn btn-danger">
                                <i class="bi bi-x-circle"></i> Tolak Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- ACTION: Approve Lanjut ke AK (Langkah 8) -->
            @if($pengajuan->status === 'borang_final_diterima')
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-check-all text-success"></i>
                        Aksi Diperlukan: Approve Lanjut ke Tahap AK
                    </h5>
                    <p class="mb-3">
                        Borang final telah diterima dan pembayaran telah diverifikasi.
                        Setujui untuk melanjutkan ke tahap AK/Asesmen Dokumen.
                    </p>

                    <div class="alert alert-warning alert-permanent">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Pastikan semua dokumen telah lengkap dan sesuai
                        sebelum menyetujui pengajuan ini ke tahap AK.
                    </div>

                    <form action="{{ route('de.pengajuan.approve-ak', $pengajuan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui pengajuan ini untuk lanjut ke tahap AK?')">
                        @csrf
                        <button type="submit" class="btn btn-success btn-md">
                            <i class="bi bi-check-circle"></i> Setujui & Lanjutkan ke Tahap AK
                        </button>
                    </form>
                </div>
            </div>
            @endif

            @if($pengajuan->status === 'pengajuan_completed')
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        Pengajuan Disetujui - Lanjut ke Tahap AK
                    </h5>

                    @if($pengajuan->asesmen)
                    {{-- Jika sudah ada asesmen --}}
                    <div class="alert alert-success alert-permanent">
                        <i class="bi bi-check-circle"></i>
                        <strong>Asesmen sudah dibuat:</strong> {{ $pengajuan->asesmen->name }}
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('asesmen.show', $pengajuan->asesmen->id) }}" class="btn btn-primary">
                            <i class="bi bi-eye"></i> Lihat Detail Asesmen
                        </a>
                        <a href="{{ route('asesmen.edit', $pengajuan->asesmen->id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-pencil"></i> Edit Asesmen
                        </a>
                    </div>
                    @else
                    {{-- Jika belum ada asesmen --}}
                    <div class="alert alert-info alert-permanent">
                        <i class="bi bi-info-circle"></i>
                        <strong>Langkah Selanjutnya:</strong> Buat asesmen baru untuk proses AK/Asesmen Dokumen dan assign asesor/validator.
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold">Data yang akan digunakan:</h6>
                            <ul class="mb-0">
                                <li><strong>Program Studi:</strong> {{ $pengajuan->studyProgram->full_name }}</li>
                                <li><strong>Universitas:</strong> {{ $pengajuan->studyProgram->university->name }}</li>
                                <li><strong>Tahun:</strong> {{ $pengajuan->tahun_akreditasi }}</li>
                                <li><strong>Jenis:</strong> {{ ucfirst($pengajuan->jenis_akreditasi) }}</li>
                            </ul>
                        </div>
                    </div>

                    <a href="{{ route('asesmen.create', ['pengajuan_id' => $pengajuan->id]) }}" class="btn btn-success btn-md">
                        <i class="bi bi-plus-circle"></i> Buat Asesmen & Assign Asesor
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- ============================================
     SECTION: VALIDATOR BORANG (if applicable)
     ============================================ --}}
            @if(in_array($pengajuan->status, [
            'borang_online_selesai',
            'borang_validation_pending',
            'borang_in_validation',
            'borang_revision_required',
            'borang_validated'
            ]) && $pengajuan->latestBorangImport)

            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-check"></i>
                            Validasi LED
                        </h5>
                        @if(!$currentValidator || $currentValidator->status_penawaran === 'rejected')
                        <a href="{{ route('de.pengajuan.assign-validator.form', $pengajuan->id) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-person-plus"></i>
                            {{ $currentValidator ? 'Reassign Validator' : 'Assign Validator' }}
                        </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($currentValidator)
                    {{-- Validator Info --}}
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="avatar-circle mx-auto mb-2" style="width: 80px; height: 80px; font-size: 2rem;">
                                    {{ substr($currentValidator->user->name, 0, 1) }}
                                </div>
                                <h6 class="fw-bold">{{ $currentValidator->user->name }}</h6>
                                <small class="text-muted">{{ $currentValidator->user->email }}</small>
                            </div>
                        </div>

                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Role</label>
                                    <p class="fw-bold mb-0">
                                        <span class="badge bg-success">
                                            {{ $currentValidator->role->alias }}
                                        </span>
                                    </p>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Status Penawaran</label>
                                    <p class="mb-0">
                                        @php
                                        $penawaranBadge = match($currentValidator->status_penawaran) {
                                        'accepted' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Diterima'],
                                        'rejected' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Ditolak'],
                                        'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Menunggu'],
                                        default => ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Unknown'],
                                        };
                                        @endphp
                                        <span class="badge bg-{{ $penawaranBadge['class'] }}">
                                            <i class="bi bi-{{ $penawaranBadge['icon'] }}"></i>
                                            {{ $penawaranBadge['text'] }}
                                        </span>
                                    </p>
                                </div>

                                @if($currentValidator->status_penawaran === 'accepted')
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Status Pekerjaan</label>
                                    <p class="mb-0">
                                        <span class="badge bg-info">
                                            {{ $currentValidator->status_label ?? 'Belum Mulai' }}
                                        </span>
                                    </p>
                                </div>
                                @endif

                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Di-assign</label>
                                    <p class="mb-0">{{ $currentValidator->created_at->format('d M Y H:i') }}</p>
                                </div>

                                @if($currentValidator->responded_at)
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Respon</label>
                                    <p class="mb-0">{{ $currentValidator->responded_at->format('d M Y H:i') }}</p>
                                </div>
                                @endif

                                @if($currentValidator->status_penawaran === 'accepted' && $currentValidator->approved_at)
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Selesai Review</label>
                                    <p class="mb-0">{{ $currentValidator->approved_at->format('d M Y H:i') }}</p>
                                </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex gap-2 mt-3">
                                @if($currentValidator->status_penawaran === 'pending')
                                <span class="badge bg-warning">
                                    <i class="bi bi-hourglass-split"></i>
                                    Menunggu validator menerima penawaran
                                </span>
                                @elseif($currentValidator->status_penawaran === 'rejected')
                                <a href="{{ route('de.pengajuan.assign-validator.form', $pengajuan->id) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-arrow-repeat"></i>
                                    Assign Validator Baru
                                </a>
                                @elseif($currentValidator->status_penawaran === 'accepted')
                                @if($currentValidator->borangValidation)
                                <a href="{{ route('borang.show', $currentValidator->id) }}" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="bi bi-eye"></i>
                                    Lihat Progress Validasi
                                </a>
                                @endif
                                @endif
                            </div>

                            {{-- Validation Details (if available) --}}
                            @if($currentValidator->borangValidation && $currentValidator->status_pekerjaan !== 'not_started')
                            <div class="card bg-light mt-3">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-clipboard-data"></i>
                                        Detail Validasi
                                    </h6>

                                    @if($currentValidator->borangValidation->catatan_validator)
                                    <div class="mb-2">
                                        <strong>Catatan Validator:</strong>
                                        <p class="mb-0">{{ $currentValidator->borangValidation->catatan_validator }}</p>
                                    </div>
                                    @endif

                                    @if($currentValidator->borangValidation->revision_points && count($currentValidator->borangValidation->revision_points) > 0)
                                    <div class="mb-2">
                                        <strong>Poin Revisi ({{ count($currentValidator->borangValidation->revision_points) }}):</strong>
                                        <ul class="mb-0">
                                            @foreach($currentValidator->borangValidation->revision_points as $point)
                                            <li>{{ $point }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    @else
                    {{-- No Validator Assigned Yet --}}
                    <div class="text-center py-4">
                        <i class="bi bi-person-x" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3 mb-3">
                            Belum ada validator yang di-assign untuk review LED
                        </p>
                        <a href="{{ route('de.pengajuan.assign-validator.form', $pengajuan->id) }}" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i>
                            Assign Validator Sekarang
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Informasi Pengajuan -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pengajuan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Nomor Pengajuan</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Program Studi</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->studyProgram->name }}</p>
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
                            <p class="fw-bold mb-0">{{ ucfirst($pengajuan->jenis_akreditasi) }}</p>
                        </div>
                        @if($pengajuan->pengaju)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Pengaju</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->pengaju->name }}</p>
                        </div>
                        @endif
                    </div>

                    @if($pengajuan->catatan_pengaju)
                    <hr>
                    <label class="text-muted small">Catatan Pengaju</label>
                    <p class="mb-0">{{ $pengajuan->catatan_pengaju }}</p>
                    @endif
                </div>
            </div>

            <!-- Pembayaran Info -->
            @if($pengajuan->pembayaran)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-credit-card"></i> Informasi Pembayaran
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
                                <span class="badge bg-{{ $pengajuan->pembayaran->status_pembayaran === 'verified' ? 'success' : 'warning' }}">
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
                            <label class="text-muted small">Tanggal Pembayaran</label>
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

                    @if($pengajuan->pembayaran->catatan_verifikasi)
                    <hr>
                    <label class="text-muted small">Catatan Verifikasi</label>
                    <p class="mb-0">{{ $pengajuan->pembayaran->catatan_verifikasi }}</p>
                    @endif

                    @if($pengajuan->pembayaran->alasan_penolakan)
                    <hr>
                    <label class="text-muted small text-danger">Alasan Penolakan</label>
                    <p class="mb-0 text-danger">{{ $pengajuan->pembayaran->alasan_penolakan }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Review History -->
            @if($pengajuan->reviewKesiapan->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check"></i> Riwayat Review Kesiapan
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($pengajuan->reviewKesiapan->sortByDesc('tanggal_review') as $review)
                    <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge {{ $review->hasil_review === 'siap' ? 'bg-success' : 'bg-danger' }} me-2">
                                    {{ $review->hasil_review === 'siap' ? 'SIAP' : 'BELUM SIAP' }}
                                </span>
                                <small class="text-muted">Versi {{ $review->versi_review }}</small>
                            </div>
                            <small class="text-muted">
                                {{ $review->tanggal_review->format('d M Y H:i') }}
                            </small>
                        </div>
                        <p class="mb-2"><strong>Reviewer:</strong> {{ $review->reviewer->name }}</p>
                        <p class="mb-2"><strong>Catatan:</strong></p>
                        <p class="text-muted mb-2">{{ $review->catatan_review }}</p>

                        @if($review->checklist_kesiapan && count($review->checklist_kesiapan) > 0)
                        <p class="mb-1"><strong>Checklist:</strong></p>
                        <ul class="mb-0">
                            @foreach($review->checklist_kesiapan as $item)
                            <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Dokumen -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-folder"></i> Dokumen
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($pengajuan->dokumen->groupBy('jenis_dokumen') as $jenis => $docs)
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
                                        <td>{{ $doc->uploader->name }}</td>
                                        <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $doc->file_size_formatted }}</td>
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
                    <p class="text-muted mb-0">Belum ada dokumen</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-12 col-lg-4">
            <!-- Timeline -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Timeline Proses Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach([
                        // ========================================
                        // FASE 1: PERSIAPAN & PENGAJUAN
                        // ========================================
                        [
                        'date' => $pengajuan->tanggal_pengingat,
                        'label' => 'Pengingat Masa Akreditasi',
                        'icon' => 'bi-bell',
                        'phase' => 'Persiapan'
                        ],
                        [
                        'date' => $pengajuan->tanggal_surat_permohonan,
                        'label' => 'Surat Permohonan PS',
                        'icon' => 'bi-envelope',
                        'phase' => 'Persiapan'
                        ],
                        [
                        'date' => $pengajuan->tanggal_borang_dikirim,
                        'label' => 'Penyampaian Template LED',
                        'icon' => 'bi-file-earmark-arrow-down',
                        'phase' => 'Persiapan'
                        ],
                        [
                        'date' => $pengajuan->tanggal_draft_borang,
                        'label' => 'Draft LED Diterima dari Prodi',
                        'icon' => 'bi-file-earmark-check',
                        'phase' => 'Persiapan'
                        ],

                        // ========================================
                        // FASE 2: VALIDASI (NEW!)
                        // ========================================
                        [
                        'date' => $pengajuan->tanggal_validasi_borang_assigned,
                        'label' => 'Validator LED Di-assign',
                        'icon' => 'bi-person-check',
                        'phase' => 'Validasi LED',
                        'color' => 'success'
                        ],
                        [
                        'date' => $pengajuan->tanggal_validasi_borang_selesai,
                        'label' => 'Validasi LED Selesai',
                        'icon' => 'bi-clipboard-check',
                        'phase' => 'Validasi LED',
                        'color' => 'success'
                        ],

                        // ========================================
                        // FASE 3: REVIEW KESIAPAN & PEMBAYARAN
                        // ========================================
                        [
                        'date' => $pengajuan->tanggal_review_kesiapan,
                        'label' => 'Review Kesiapan oleh DE',
                        'icon' => 'bi-clipboard2-check',
                        'phase' => 'Review'
                        ],
                        [
                        'date' => $pengajuan->tanggal_pembayaran,
                        'label' => 'Pembayaran Diterima',
                        'icon' => 'bi-credit-card',
                        'phase' => 'Pembayaran'
                        ],
                        [
                        'date' => $pengajuan->tanggal_borang_final,
                        'label' => 'LED PS Final Diterima',
                        'icon' => 'bi-file-earmark-text',
                        'phase' => 'Pembayaran'
                        ],
                        [
                        'date' => $pengajuan->tanggal_lanjut_ak,
                        'label' => 'Keputusan Lanjut ke AK',
                        'icon' => 'bi-check-circle',
                        'phase' => 'Pembayaran'
                        ],

                        // ========================================
                        // FASE 4: ASESMEN KECUKUPAN (AK)
                        // ========================================
                        [
                        'date' => $pengajuan->tanggal_ak_mulai,
                        'label' => 'Proses Penilaian Dokumen (AK) Dimulai',
                        'icon' => 'bi-file-earmark-medical',
                        'phase' => 'Asesmen Kecukupan',
                        'color' => 'success'
                        ],
                        [
                        'date' => $pengajuan->tanggal_ak_selesai,
                        'label' => 'Validasi Hasil AK Selesai',
                        'icon' => 'bi-clipboard-check',
                        'phase' => 'Asesmen Kecukupan',
                        'color' => 'success'
                        ],

                        // ========================================
                        // FASE 5: ASESMEN LAPANGAN (AL)
                        // ========================================
                        [
                        'date' => $pengajuan->tanggal_al_mulai,
                        'label' => 'Proses Asesmen Lapangan (AL) Dimulai',
                        'icon' => 'bi-building',
                        'phase' => 'Asesmen Lapangan',
                        'color' => 'success'
                        ],
                        [
                        'date' => $pengajuan->tanggal_al_selesai,
                        'label' => 'Validasi Hasil AL Selesai',
                        'icon' => 'bi-clipboard-data',
                        'phase' => 'Asesmen Lapangan',
                        'color' => 'success'
                        ],

                        // ========================================
                        // FASE 6: PENYELESAIAN
                        // ========================================
                        [
                        'date' => $pengajuan->tanggal_hasil_akreditasi,
                        'label' => 'Penyampaian Hasil Akreditasi',
                        'icon' => 'bi-envelope-paper',
                        'phase' => 'Penyelesaian',
                        'color' => 'success'
                        ],
                        [
                        'date' => $pengajuan->tanggal_banding,
                        'label' => 'Banding (Jika Ada)',
                        'icon' => 'bi-arrow-repeat',
                        'phase' => 'Penyelesaian',
                        'color' => 'danger',
                        'optional' => true
                        ],
                        [
                        'date' => $pengajuan->tanggal_penetapan,
                        'label' => 'Penetapan Hasil Akreditasi',
                        'icon' => 'bi-award',
                        'phase' => 'Penyelesaian',
                        'color' => 'success'
                        ],
                        [
                        'date' => $pengajuan->tanggal_pengumuman,
                        'label' => 'Pengumuman Hasil Akreditasi',
                        'icon' => 'bi-megaphone',
                        'phase' => 'Penyelesaian',
                        'color' => 'success'
                        ],
                        [
                        'date' => $pengajuan->tanggal_penyimpanan,
                        'label' => 'Penyimpanan Berkas Akreditasi',
                        'icon' => 'bi-archive',
                        'phase' => 'Penyelesaian',
                        'color' => 'secondary'
                        ],
                        ] as $item)
                        @php
                        $isCompleted = $item['date'] !== null;
                        $iconColor = $isCompleted ? 'text-success' : 'text-muted';
                        $itemColor = $item['color'] ?? ($isCompleted ? 'success' : 'muted');
                        $isOptional = $item['optional'] ?? false;
                        @endphp

                        <div class="d-flex mb-3 {{ $isOptional && !$isCompleted ? 'opacity-50' : '' }}">
                            <div class="me-3">
                                @if($isCompleted)
                                <i class="bi bi-check-circle-fill {{ $iconColor }}" style="font-size: 1.2rem;"></i>
                                @else
                                <i class="bi bi-circle {{ $iconColor }}"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="{{ $isCompleted ? 'text-' . $itemColor : 'text-muted' }}">
                                            <i class="{{ $item['icon'] ?? 'bi-circle' }} me-1"></i>
                                            {{ $item['label'] }}
                                            @if($isOptional)
                                            <span class="badge bg-secondary ms-1">Opsional</span>
                                            @endif
                                        </strong>
                                    </div>
                                    @if($isCompleted)
                                    <span class="badge bg-{{ $itemColor }}">
                                        {{ $item['date']->format('d M Y') }}
                                    </span>
                                    @endif
                                </div>
                                @if($isCompleted)
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

            <!-- Log Aktivitas -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check"></i> Log Aktivitas
                    </h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($pengajuan->statusLog->sortByDesc('changed_at') as $log)
                    <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">
                                {{ $log->changed_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <p class="mb-1 small">
                            <span class="badge bg-secondary">{{ str_replace('_', ' ', $log->status_from) }}</span>
                            <i class="bi bi-arrow-right"></i>
                            <span class="badge bg-primary">{{ str_replace('_', ' ', $log->status_to) }}</span>
                        </p>
                        @if($log->keterangan)
                        <small class="text-muted">{{ $log->keterangan }}</small>
                        @endif
                        <br>
                        <small class="text-muted">Oleh: {{ $log->changedBy->name }}</small>
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
    // Show/hide pembayaran field based on hasil review
    hasilReview = document.getElementById('hasilReview')
    if (hasilReview) hasilReview.addEventListener('change', function() {
        const divPembayaran = document.getElementById('divPembayaran');
        if (this.value === 'siap') {
            divPembayaran.style.display = 'block';
            divPembayaran.querySelector('input').required = true;
        } else {
            divPembayaran.style.display = 'none';
            divPembayaran.querySelector('input').required = false;
        }
    });

    async function parseBorang(pengajuanId, dokumenId) {
        const btn = document.getElementById('btnParse');
        const resultDiv = document.getElementById('parseResult');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sedang memproses data...';
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Memproses dokumen, mohon tunggu...</div>';

        try {
            const response = await fetch(`/de/pengajuan/${pengajuanId}/borang/${dokumenId}/parse`, {
                method: 'POST'
                , headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    , 'Accept': 'application/json'
                , }
            });

            const data = await response.json();

            if (data.success) {
                resultDiv.innerHTML = `
                <div class="alert alert-success alert-permanent">
                    <i class="bi bi-check-circle"></i>
                    <strong>Pembacaan data berhasil!</strong><br>
                    Kelengkapan: ${data.data.completeness}%<br>
                    Status: ${data.data.status}
                </div>
            `;

                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                resultDiv.innerHTML = `
                <div class="alert alert-danger alert-permanent">
                    <i class="bi bi-x-circle"></i>
                    <strong>Pembacaan data gagal:</strong> ${data.message}
                </div>
            `;
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-gear"></i> Proses & Ekstrak Data';
            }
        } catch (error) {
            console.error('Error:', error);
            resultDiv.innerHTML = `
            <div class="alert alert-danger alert-permanent">
                <i class="bi bi-x-circle"></i>
                <strong>Error:</strong> ${error.message}
            </div>
        `;
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-gear"></i> Proses & Ekstrak Data';
        }
    }

    function reParseBorang(pengajuanId, dokumenId) {
        if (confirm('Apakah Anda yakin ingin memproses ulang dokumen ini? Data pemrosesan sebelumnya akan ditimpa.')) {
            parseBorang(pengajuanId, dokumenId);
        }
    }


    // Toggle between link and upload
    document.querySelectorAll('input[name="metode_kirim"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const divLink = document.getElementById('divLink');
            const divUpload = document.getElementById('divUpload');
            const templateLink = document.getElementById('template_link');
            const borangTemplate = document.getElementById('borang_template');
            const infoMetode = document.getElementById('infoMetode');

            if (this.value === 'link') {
                divLink.style.display = 'block';
                divUpload.style.display = 'none';
                templateLink.required = true;
                borangTemplate.required = false;
                infoMetode.textContent = 'Link template akan dikirim ke email prodi';
            } else {
                divLink.style.display = 'none';
                divUpload.style.display = 'block';
                templateLink.required = false;
                borangTemplate.required = true;
                infoMetode.textContent = 'File template akan diupload dan dapat didownload oleh prodi';
            }
        });
    });

    // Use default link
    function useDefaultLink() {
        const template_link = document.getElementById('template_link');
        if (template_link) template_link.value = "{{ url('pengajuan/' . $pengajuan->id . '/borang/download-template') }}";
    }

    // Clear link
    function clearLink() {
        const template_link = document.getElementById('template_link');
        if (template_link) template_link.value = '';
    }

    // Form validation
    const formKirimBorang = document.getElementById('formKirimBorang')
    if (formKirimBorang) formKirimBorang.addEventListener('submit', function(e) {
        const metode = document.querySelector('input[name="metode_kirim"]:checked').value;

        if (metode === 'link') {
            const link = document.getElementById('template_link').value;
            if (!link) {
                e.preventDefault();
                alert('Mohon masukkan URL template!');
                return false;
            }

            // Validate URL format
            try {
                new URL(link);
            } catch (error) {
                e.preventDefault();
                alert('Format URL tidak valid!');
                return false;
            }
        } else {
            const file = document.getElementById('borang_template').files[0];
            if (!file) {
                e.preventDefault();
                alert('Mohon pilih file template!');
                return false;
            }

            // Validate file size (10 MB)
            if (file.size > 10 * 1024 * 1024) {
                e.preventDefault();
                alert('Ukuran file terlalu besar! Maksimal 10 MB.');
                return false;
            }
        }

        return true;
    });

    // Auto-fill default on page load
    document.addEventListener('DOMContentLoaded', function() {
        useDefaultLink();
    });

</script>
@endpush
@endsection
