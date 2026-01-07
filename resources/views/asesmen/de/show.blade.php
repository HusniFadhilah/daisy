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
        <div class="col-md-8">
            <!-- ACTION: Kirim Form Borang (Langkah 3) -->
            @if($pengajuan->status === 'surat_permohonan_diterima')
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-file-earmark-arrow-down text-primary"></i>
                        Aksi Diperlukan: Kirim Form Borang
                    </h5>
                    <p class="mb-3">
                        Surat permohonan telah diterima. Kirimkan form borang template ke prodi untuk dilengkapi.
                    </p>

                    <form action="{{ route('de.pengajuan.kirim-borang', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Upload Borang Template</label>
                                <input type="file" name="borang_template" class="form-control" accept=".docx" required>
                                <small class="text-muted">Format: DOCX | Max: 10 MB</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Petunjuk pengisian atau informasi tambahan"></textarea>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Kirim Form Borang ke Prodi
                                </button>
                            </div>
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
                        <strong>Prodi telah mengupload draft borang.</strong><br>
                        Silakan review kelengkapan dan kesiapan borang sebelum melanjutkan ke tahap pembayaran.
                    </div>

                    {{-- Preview & Form Online Links --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card bg-primary bg-opacity-10 border-primary h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-eye text-white fs-1 mb-3 d-block"></i>
                                    <h6 class="fw-bold text-white">Preview Borang HTML</h6>
                                    <p class="text-white small mb-3">
                                        Lihat preview borang yang sudah diproses<br>
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
        <div class="col-md-4">
            <!-- Timeline -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach([
                        ['date' => $pengajuan->tanggal_pengingat, 'label' => 'Pengingat Masa Akreditasi'],
                        ['date' => $pengajuan->tanggal_surat_permohonan, 'label' => 'Surat Permohonan PS'],
                        ['date' => $pengajuan->tanggal_borang_dikirim, 'label' => 'Penyampaian Template LED'],
                        ['date' => $pengajuan->tanggal_draft_borang, 'label' => 'Dokumen LED Diterima'],
                        ['date' => $pengajuan->tanggal_review_kesiapan, 'label' => 'Review Kesiapan'],
                        ['date' => $pengajuan->tanggal_pembayaran, 'label' => 'Pembayaran'],
                        ['date' => $pengajuan->tanggal_borang_final, 'label' => 'LED PS Final'],
                        ['date' => $pengajuan->tanggal_lanjut_ak, 'label' => 'Keputusan Kesiapan'],
                        ['date' => $pengajuan->tanggal_lanjut_ak, 'label' => 'Proses Penilaian Dokumen (AK)'],
                        ['date' => $pengajuan->tanggal_lanjut_ak, 'label' => 'Validasi Hasil AK'],
                        ['date' => $pengajuan->tanggal_lanjut_ak, 'label' => 'Proses Asesmen Lapangan (AL)'],
                        ['date' => $pengajuan->tanggal_lanjut_ak, 'label' => 'Penyampaian Hasil Akreditasi'],
                        ['date' => $pengajuan->tanggal_lanjut_ak, 'label' => 'Banding'],
                        ['date' => $pengajuan->tanggal_lanjut_ak, 'label' => 'Penetapan Hasil Akreditasi'],
                        ['date' => $pengajuan->tanggal_lanjut_ak, 'label' => 'Pengumuman Hasil Akreditasi'],
                        ['date' => $pengajuan->tanggal_lanjut_ak, 'label' => 'Penyimpanan Berkas Akreditasi'],
                        ] as $item)
                        <div class="d-flex mb-3">
                            <div class="me-3">
                                @if($item['date'])
                                <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                <i class="bi bi-circle text-muted"></i>
                                @endif
                            </div>
                            <div>
                                <strong>{{ $item['label'] }}</strong>
                                @if($item['date'])
                                <br><small class="text-muted">{{ $item['date']->format('d M Y H:i') }}</small>
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

</script>
@endpush
@endsection
