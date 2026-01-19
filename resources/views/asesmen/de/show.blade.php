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
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
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
            <span class="badge {{ $pengajuan->status_badge_class }} fs-6 text-wrap">
                {{ $pengajuan->status_label }}
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-12 col-lg-8">
            <!-- ACTION: Kirim Form LED (Langkah 3) -->
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA)
            <div class="card mb-4">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"> <i class="bi bi-receipt"></i> Aksi Diperlukan: Kirim Template LED+Suplemen dan LKPS, Formulir Pembayaran </h5>
                </div>
            </div>
            <ul class="nav nav-tabs mb-3" id="aksiPengajuanTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-template" data-bs-toggle="tab" data-bs-target="#pane-template" type="button" role="tab">
                        <i class="bi bi-file-earmark-arrow-down"></i> Template LED & LKPS
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-pembayaran" data-bs-toggle="tab" data-bs-target="#pane-pembayaran" type="button" role="tab">
                        <i class="bi bi-receipt"></i> Formulir Pembayaran
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="aksiPengajuanTabContent">
                {{-- ========================================= --}}
                {{-- TAB 1: TEMPLATE LED + SUPLEMEN + LKPS --}}
                {{-- ========================================= --}}
                <div class="tab-pane fade show active" id="pane-template" role="tabpanel">
                    <div class="card action-card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-file-earmark-arrow-down"></i>
                                Kirim Template LED+Suplemen dan LKPS
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">
                                Surat permohonan telah diterima. Kirimkan form Template LED+Suplemen dan LKPS ke prodi untuk dilengkapi.
                            </p>

                            <form action="{{ route('de.pengajuan.kirim-borang', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" id="formKirimBorang">
                                @csrf

                                {{-- Pilihan Metode Pengiriman --}}
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        Metode Pengiriman Template <span class="text-danger">*</span>
                                    </label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check @error('metode_kirim') is-invalid @enderror" name="metode_kirim" id="metodeLink" value="link" {{ old('metode_kirim', 'link') == 'link' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-primary" for="metodeLink">
                                            <i class="bi bi-link-45deg"></i> Kirim Link Template
                                        </label>

                                        <input type="radio" class="btn-check @error('metode_kirim') is-invalid @enderror" name="metode_kirim" id="metodeUpload" value="upload" {{ old('metode_kirim') == 'upload' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-primary" for="metodeUpload">
                                            <i class="bi bi-cloud-upload"></i> Upload File Template
                                        </label>
                                    </div>
                                    @error('metode_kirim')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- OPTION 1: Link Template --}}
                                <div id="divLink" class="mb-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3">
                                                <i class="bi bi-link"></i> Link Template LED+Suplemen dan LKPS
                                            </h6>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    URL Template <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="bi bi-globe"></i>
                                                    </span>
                                                    <input type="url" name="template_link" id="template_link" class="form-control @error('template_link') is-invalid @enderror" value="{{ old('template_link', url('pengajuan/' . $pengajuan->id . '/borang/download-template')) }}" placeholder="https://example.com/template.docx">
                                                </div>
                                                @error('template_link')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">
                                                    Link ke Template LED+Suplemen dan LKPS yang dapat diakses oleh prodi
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
                                                    File Template LED+Suplemen dan LKPS, formulir pembayaran <span class="text-danger">*</span>
                                                </label>
                                                <input type="file" name="borang_template" id="borang_template" class="form-control @error('borang_template') is-invalid @enderror" accept=".docx,.doc,.zip,.rar,.pdf,.xlsx">
                                                @error('borang_template')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">
                                                    Format: ZIP/RAR | Maksimal: 10 MB
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

                                {{-- Keterangan --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Keterangan</label>
                                    <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3" placeholder="Petunjuk pengisian atau informasi tambahan untuk prodi...">{{ old('keterangan') }}</textarea>
                                    @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                            <li>Status pengajuan akan diupdate ke <code>Penyampaian Template LED+Suplemen dan LKPS</code></li>
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
                </div>

                {{-- ========================================= --}}
                {{-- TAB 2: FORMULIR PEMBAYARAN --}}
                {{-- ========================================= --}}
                <div class="tab-pane fade" id="pane-pembayaran" role="tabpanel">
                    <div class="card action-card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-receipt"></i>
                                Kirim Formulir Pembayaran
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">
                                Kirimkan formulir pembayaran ke prodi. Formulir ini terpisah dari Template LED+Suplemen dan LKPS.
                            </p>

                            <form action="{{ route('de.pengajuan.kirim-formulir-pembayaran', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" id="formKirimFormulirPembayaran">
                                @csrf

                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        Metode Pengiriman Formulir <span class="text-danger">*</span>
                                    </label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check @error('metode_kirim_pembayaran') is-invalid @enderror" name="metode_kirim_pembayaran" id="metodePembayaranLink" value="link" {{ old('metode_kirim_pembayaran', 'link') == 'link' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-success" for="metodePembayaranLink">
                                            <i class="bi bi-link-45deg"></i> Kirim Link Formulir
                                        </label>

                                        <input type="radio" class="btn-check @error('metode_kirim_pembayaran') is-invalid @enderror" name="metode_kirim_pembayaran" id="metodePembayaranUpload" value="upload" {{ old('metode_kirim_pembayaran') == 'upload' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-success" for="metodePembayaranUpload">
                                            <i class="bi bi-cloud-upload"></i> Upload File Formulir
                                        </label>
                                    </div>
                                    @error('metode_kirim_pembayaran')
                                    <div class="text-danger mt-1">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                {{-- OPTION 1: Link --}}
                                <div id="divPembayaranLink" class="mb-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3">
                                                <i class="bi bi-link"></i> Link Formulir Pembayaran
                                            </h6>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    URL Formulir <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="bi bi-globe"></i>
                                                    </span>
                                                    <input type="url" name="pembayaran_link" id="pembayaran_link" class="form-control @error('pembayaran_link') is-invalid @enderror" placeholder="https://.../formulir_pembayaran.pdf" value="{{ old('pembayaran_link') }}">
                                                </div>
                                                @error('pembayaran_link')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                                <small class="text-muted">
                                                    Link formulir pembayaran yang dapat diakses prodi
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- OPTION 2: Upload --}}
                                <div id="divPembayaranUpload" class="mb-4" style="display:none;">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3">
                                                <i class="bi bi-cloud-upload"></i> Upload File Formulir Pembayaran
                                            </h6>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    File Formulir <span class="text-danger">*</span>
                                                </label>
                                                <input type="file" name="formulir_pembayaran_file" id="formulir_pembayaran_file" class="form-control @error('formulir_pembayaran_file') is-invalid @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.rar">
                                                @error('formulir_pembayaran_file')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                                <small class="text-muted">
                                                    Format: PDF/DOCX/XLSX/ZIP/RAR | Maksimal: 10 MB
                                                </small>
                                            </div>

                                            <div class="alert alert-warning alert-permanent mb-0">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                <strong>Perhatian:</strong> File akan disimpan di server dan dapat didownload oleh prodi.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">
                                            Nomor Invoice <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="nomor_invoice" class="form-control @error('nomor_invoice') is-invalid @enderror" placeholder="Masukkan nomor invoice..." value="{{ old('nomor_invoice') }}">
                                        @error('nomor_invoice')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">
                                            Jatuh Tempo (Hari) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" name="jatuh_tempo_hari" class="form-control @error('jatuh_tempo_hari') is-invalid @enderror" value="{{ old('jatuh_tempo_hari', 7) }}" min="1" max="30">
                                        @error('jatuh_tempo_hari')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                        <small class="text-muted">
                                            Jumlah hari dari hari ini. Default: 7 hari.
                                        </small>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-bold">
                                            Biaya Pembayaran Akreditasi (Rp) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" name="jumlah_pembayaran" class="form-control @error('jumlah_pembayaran') is-invalid @enderror" value="{{ old('jumlah_pembayaran', 53000000) }}" step="100000" min="0">
                                        @error('jumlah_pembayaran')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                        <small class="text-muted">
                                            Default: Rp 53.000.000,- (sesuai ketentuan)
                                        </small>
                                    </div>
                                </div>

                                {{-- Keterangan --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Keterangan</label>
                                    <textarea name="keterangan_pembayaran" class="form-control" rows="3" placeholder="Petunjuk pembayaran / rekening / hal yang perlu diperhatikan..."></textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success" id="btnSubmitPembayaran">
                                        <i class="bi bi-send"></i> Kirim Formulir Pembayaran
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- ACTION: Verifikasi Pembayaran -->
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-info-circle"></i>
                Sedang menunggu pembayaran dilakukan oleh Prodi {{ $pengajuan->studyProgram->name }}
            </div>
            @endif

            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN)
            @if($pengajuan->pembayaran && $pengajuan->pembayaran->status_pembayaran === 'menunggu_verifikasi')
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-info-circle"></i>
                Sedang menunggu verifikasi pembayaran oleh bagian keuangan LAMDEPILAR
            </div>
            @elseif($pengajuan->pembayaran && $pengajuan->pembayaran->status_pembayaran === 'upload_ulang')
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-info-circle"></i>
                Sedang menunggu upload ulang formulir pembayaran dan bukti pembayaran oleh Prodi {{ $pengajuan->studyProgram->name }}
            </div>
            @endif
            @endif

            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI && $pengajuan->pembayaran && $pengajuan->pembayaran->status_pembayaran === 'terverifikasi')
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-info-circle"></i>
                Sedang menunggu Prodi mengupload draft LED+Suplemen, dan LKPS
            </div>
            @endif

            @if(in_array($pengajuan->status, [\App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA, \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI]))
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-info-circle"></i>
                Silahkan tugaskan Validator untuk melakukan validasi dokumen LED+Suplemen, dan LKPS yang telah diupload oleh prodi
            </div>
            @endif

            @if(in_array($pengajuan->status, [\App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED]))
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-info-circle"></i>
                Sedang menunggu pelaporan LED+Suplemen dan LKPS selesai
            </div>
            @endif

            <!-- ACTION: Approve Lanjut ke AK (Langkah 8) -->
            @if(in_array($pengajuan->status,[\App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN]))
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-check-all text-success"></i>
                        Aksi Diperlukan: Approve Lanjut ke Tahap AK
                    </h5>
                    <p class="mb-3">
                        Pembayaran telah diverifikasi oleh bagian keuangan, LED+Suplemen dan LKPS final telah diterima, serta pelaporan validasi LED+Suplemen dan LKPS telah selesai dilaksanakan. Selanjutnya, dapat dilanjutkan untuk tahap penugasan Asesor untuk Asesmen Kecukupan (AK)
                        Silahkan setujui untuk melanjutkan ke tahap AK/Asesmen Dokumen.
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

            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED)
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
                        <p class="mb-0">Silahkan tugaskan Asesor di halaman "Lihat Detail Asesmen", atau edit deskripsi asesmen terlebih dahulu</p>
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
                        <strong>Langkah Selanjutnya:</strong> Buat asesmen baru untuk proses AK/Asesmen Dokumen dan tugaskan asesor/validator.
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
                        <i class="bi bi-plus-circle"></i> Buat Asesmen & Tugaskan Asesor
                    </a>
                    @endif
                </div>
            </div>
            @endif

            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN)
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        Lanjut ke Tahap AL, Tugaskan Asesor AL
                    </h5>

                    {{-- Jika sudah ada asesmen --}}
                    <div class="alert alert-success alert-permanent">
                        <i class="bi bi-check-circle"></i>
                        <strong>Asesmen Kecukupan telah selesai dilaksanakan & dilaporkan</strong>
                        <p class="mb-0">Silahkan tugaskan Asesor AL di halaman "Lihat Detail Asesmen"</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('asesmen.show', $pengajuan->asesmen->id) }}" class="btn btn-primary">
                            <i class="bi bi-eye"></i> Lihat Detail Asesmen
                        </a>
                    </div>
                </div>
            </div>
            @endif

            @if(in_array($pengajuan->status,[
            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
            \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
            \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
            \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
            ]))
            <div class="alert alert-info alert-permanent border-start border-4 border-primary mb-4">
                <div class="d-flex align-items-start">
                    <i class="bi bi-info-circle-fill fs-4 me-3 text-primary"></i>

                    <div class="flex-grow-1">
                        <h5 class="mb-1 fw-semibold">
                            Proses Asesmen Sedang Berjalan
                        </h5>

                        <p class="mb-2">
                            Status pengajuan saat ini:
                            <strong class="text-dark">{{ $pengajuan->status_label }}</strong>
                        </p>

                        <ul class="mb-2 ps-3 small">
                            <li>
                                Tim asesor telah <strong>ditugaskan</strong> dan sedang melakukan
                                <strong>penilaian terhadap LED, Suplemen, dan LKPS</strong>.
                            </li>
                            <li>
                                Selama proses asesmen berlangsung, <strong>data pengajuan bersifat terkunci</strong>
                                dan tidak dapat diubah.
                            </li>
                            <li>
                                Hasil asesmen akan tersedia setelah proses ini selesai dan dilaporkan.
                            </li>
                        </ul>

                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <a href="{{ route('asesmen.show', $pengajuan->asesmen->id) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> Lihat Detail Asesmen
                            </a>

                            <a href="{{ route('asesmen.edit', $pengajuan->asesmen->id) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i> Edit Asesmen
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            {{-- ============================================
     SECTION: VALIDATOR BORANG (if applicable)
     ============================================ --}}
            @if(in_array($pengajuan->status, [
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED
            ]) && $pengajuan->latestBorangImport)

            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-check"></i>
                            Validasi LED
                        </h5>
                        @if($canAssignValidator)
                        <a href="{{ route('de.pengajuan.assign-validator.form', $pengajuan->id) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-person-plus"></i>
                            {{ $currentValidator ? 'Tugaskan Ulang Validator' : 'Tugaskan Validator' }}
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
                                    <label class="text-muted small">Ditugaskan</label>
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
                                <a href="{{ route('de.pengajuan.assign-validator.form', $pengajuan->id) }}" class="btn btn-warning btn-sm mt-3">
                                    <i class="bi bi-arrow-repeat"></i>
                                    Tugaskan Validator Baru
                                </a>
                                @elseif($currentValidator->status_penawaran === 'accepted')
                                @if($currentValidator->borangValidation)
                                <a href="{{ route('validator.borang.show', $currentValidator->id) }}" class="btn btn-primary btn-sm mt-3" target="_blank">
                                    <i class="bi bi-eye"></i>
                                    Lihat Progress Validasi
                                </a>
                                @endif
                                @endif

                                {{-- Validation Details (if available) --}}
                                @if($currentValidator->borangValidation && $currentValidator->status_pekerjaan !== 'not_started')
                                <button class="btn btn-outline-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#modalRevisi">
                                    <i class="bi bi-clipboard-data"></i> Lihat Detail Revisi
                                </button>
                                @include('asesmen.de.modal-revisi')
                                @endif
                            </div>
                        </div>
                    </div>

                    @else
                    {{-- No validator assigned --}}
                    <div class="text-center py-4">
                        <i class="bi bi-person-x" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3 mb-3">
                            Belum ada validator yang ditugaskan untuk review LED
                        </p>
                        @if($canAssignValidator)
                        <a href="{{ route('de.pengajuan.assign-validator.form', $pengajuan->id) }}" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i>
                            Tugaskan Validator Sekarang
                        </a>
                        @else
                        <p class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            LED harus diselesaikan prodi terlebih dahulu sebelum menugaskan validator
                        </p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif

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
                            <p class="fw-bold mb-0">{{ ucfirst($pengajuan->jenis_akreditasi) }}</p>
                        </div>
                        @if($pengajuan->pengaju)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Pengaju</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->pengaju->name }}</p>
                            <small class="text-wrap">{{ $pengajuan->pengaju->email }}</small>
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

            <!-- Review History -->
            {{-- @if($pengajuan->reviewKesiapan->count() > 0)
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
@endif --}}

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
                <i class="bi bi-clock-history"></i> Timeline Pelaksanaan Akreditasi
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
                    <span class="badge bg-secondary">{{ str_replace('_', ' ', $log->status_from_label) }}</span>
                    <i class="bi bi-arrow-right"></i>
                    <span class="badge bg-primary">{{ str_replace('_', ' ', $log->status_to_label) }}</span>
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


    document.querySelectorAll('input[name="metode_kirim_pembayaran"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var divLink = document.getElementById('divPembayaranLink');
            var divUpload = document.getElementById('divPembayaranUpload');

            var inputLink = document.getElementById('pembayaran_link');
            var inputFile = document.getElementById('formulir_pembayaran_file');

            if (this.value === 'link') {
                if (divLink) divLink.style.display = 'block';
                if (divUpload) divUpload.style.display = 'none';

                if (inputLink) inputLink.required = true;
                if (inputFile) inputFile.required = false;
            } else {
                if (divLink) divLink.style.display = 'none';
                if (divUpload) divUpload.style.display = 'block';

                if (inputLink) inputLink.required = false;
                if (inputFile) inputFile.required = true;
            }
        });
    });

    // Validasi form formulir pembayaran (tanpa optional chaining)
    var formPembayaran = document.getElementById('formKirimFormulirPembayaran');
    if (formPembayaran) {
        formPembayaran.addEventListener('submit', function(e) {
            var metodeChecked = document.querySelector('input[name="metode_kirim_pembayaran"]:checked');
            var metode = metodeChecked ? metodeChecked.value : null;

            if (!metode) {
                e.preventDefault();
                alert('Mohon pilih metode pengiriman formulir pembayaran!');
                return false;
            }

            if (metode === 'link') {
                var inputLink = document.getElementById('pembayaran_link');
                var link = inputLink ? inputLink.value : '';

                if (!link) {
                    e.preventDefault();
                    alert('Mohon masukkan URL formulir pembayaran!');
                    return false;
                }

                try {
                    new URL(link);
                } catch (err) {
                    e.preventDefault();
                    alert('Format URL tidak valid!');
                    return false;
                }
            } else {
                var inputFile = document.getElementById('formulir_pembayaran_file');
                var file = null;

                if (inputFile && inputFile.files && inputFile.files.length > 0) {
                    file = inputFile.files[0];
                }

                if (!file) {
                    e.preventDefault();
                    alert('Mohon pilih file formulir pembayaran!');
                    return false;
                }

                if (file.size > 10 * 1024 * 1024) {
                    e.preventDefault();
                    alert('Ukuran file terlalu besar! Maksimal 10 MB.');
                    return false;
                }
            }

            return true;
        });
    }

</script>
@endpush
@endsection
