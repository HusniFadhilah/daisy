{{-- resources/views/upps/permohonan-banding/create.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Buat Permohonan Banding')

@push('styles')
<style>
    .pengajuan-card {
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid #e0e0e0;
    }

    .pengajuan-card:hover {
        border-color: #0d6efd;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .pengajuan-card.selected {
        border-color: #198754;
        background-color: #d1e7dd;
    }

    .form-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.permohonan-banding') }}">Permohonan Banding</a></li>
            <li class="breadcrumb-item active">Buat Baru</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="mb-4">
        <h4>
            <i class="bi bi-file-earmark-plus"></i>
            Kirim Permohonan Banding
        </h4>
    </div>

    {{-- ✅ Jika belum pilih prodi, tampilkan pilihan prodi --}}
    @if(!$selectedProdiId)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="bi bi-building"></i> Pilih Program Studi
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Program Studi <span class="text-danger">*</span></label>
                    <select class="form-select" id="select_prodi" onchange="loadPengajuans()">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach($prodis as $prodi)
                        <option value="{{ $prodi->id }}">
                            {{ $prodi->full_name }}
                        </option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> Pilih program studi yang akan mengajukan banding
                    </small>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ✅ Jika sudah pilih prodi, tampilkan pengajuan & form --}}
    @if($selectedProdiId)

    {{-- Info Prodi yang Dipilih --}}
    <div class="alert alert-light alert-permanent border-start border-primary border-4 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">
                    <i class="bi bi-building"></i> Program Studi:
                </h6>
                <strong>{{ $selectedProdi ? $selectedProdi->full_name : '-' }}</strong>
            </div>
            <a href="{{ route('upps.permohonan-banding.create') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-repeat"></i> Ganti Prodi
            </a>
        </div>
    </div>

    {{-- Step 1: Pilih Pengajuan Akreditasi --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <span class="badge bg-primary me-2">1</span>
                Pilih Pengajuan Akreditasi yang Akan Dibanding
            </h5>
        </div>
        <div class="card-body">
            @if($pengajuansAvailable->count() > 0)
            <div class="row">
                @foreach($pengajuansAvailable as $p)
                @php
                $asesmen = $p->asesmen;
                $hasil = $asesmen ? $asesmen->hasil : null;
                $peringkat = $hasil ? $hasil->peringkat_akreditasi : '-';
                $skor = $hasil ? $hasil->skor_final : 0;
                $badgeColor = $hasil ? $hasil->getPeringkatColor($peringkat) : '#e9ecef';
                $isSelected = $selectedPengajuan && $selectedPengajuan->id === $p->id;
                $tanggalHasil = $p->tanggal_hasil_akreditasi_dikirim ? $p->tanggal_hasil_akreditasi_dikirim->format('d M Y') : '-';
                @endphp
                <div class="col-md-6 mb-3">
                    <div class="card pengajuan-card h-100 {{ $isSelected ? 'selected' : '' }}" onclick="selectPengajuan({{ $p->id }})" data-pengajuan-id="{{ $p->id }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0">
                                    <i class="bi bi-file-earmark-text"></i>
                                    {{ $p->nomor_pengajuan }}
                                </h6>
                                @if($isSelected)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Dipilih
                                </span>
                                @endif
                            </div>
                            <div class="mb-2">
                                <small class="text-muted d-block">
                                    <i class="bi bi-calendar"></i> Tahun: {{ $p->tahun_akreditasi }}
                                </small>
                                <small class="text-muted d-block">
                                    <i class="bi bi-tag"></i> Jenis: {{ $p->jenis_akreditasi_label }}
                                </small>
                                <small class="text-muted d-block">
                                    <i class="bi bi-award"></i> Peringkat Disampaikan:
                                    <span class="badge" style="background-color: {{ $badgeColor }}; color: #222;">
                                        {{ $peringkat }}
                                    </span>
                                </small>
                                <small class="text-muted d-block">
                                    <i class="bi bi-clock"></i> Hasil disampaikan: {{ $tanggalHasil }}
                                </small>
                            </div>
                            <hr>
                            <button type="button" class="btn btn-outline-success btn-sm w-100" onclick="selectPengajuan({{ $p->id }})">
                                <i class="bi bi-arrow-right"></i> Pilih Pengajuan Ini
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="text-muted mb-2 mt-3">
                    Tidak ada pengajuan akreditasi yang dapat dibanding untuk program studi ini.
                </p>
                <small class="text-muted">
                    Hanya pengajuan yang sudah disampaikan hasilnya dan dalam masa sanggah yang dapat dibanding.
                </small>
                <hr class="my-4">
                <a href="{{ route('upps.permohonan-banding.create') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Pilih Program Studi Lain
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Step 2: Form Banding --}}
    <form action="{{ route('upps.permohonan-banding.store') }}" method="POST" enctype="multipart/form-data" id="formBanding">
        @csrf

        <input type="hidden" name="id_pengajuan" id="id_pengajuan" value="{{ $selectedPengajuan ? $selectedPengajuan->id : '' }}">

        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <span class="badge bg-primary me-2">2</span>
                            Formulir Permohonan Banding
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- Alasan Banding --}}
                        <div class="form-section">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="bi bi-chat-left-text"></i> Alasan Banding
                            </h6>
                            <div class="mb-0">
                                <label class="form-label fw-bold">
                                    Alasan/Keberatan <span class="text-danger">*</span>
                                </label>
                                <textarea name="alasan_banding" class="form-control @error('alasan_banding') is-invalid @enderror" rows="8" required placeholder="Jelaskan alasan atau keberatan Anda terhadap hasil akreditasi yang diberikan...">{{ old('alasan_banding') }}</textarea>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Jelaskan secara detail alasan pengajuan banding (minimal 100 karakter)
                                </small>
                                @error('alasan_banding')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Upload Dokumen --}}
                        <div class="form-section">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="bi bi-file-earmark-arrow-up"></i> Dokumen Permohonan
                            </h6>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Surat Permohonan Banding (PDF) <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="file_surat_permohonan" id="file_surat_permohonan" class="form-control @error('file_surat_permohonan') is-invalid @enderror" accept=".pdf" required>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Format: PDF | Maksimal: 5 MB
                                </small>
                                <div id="filePreview" class="mt-2"></div>
                                @error('file_surat_permohonan')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Persyaratan Info -->
                            <div class="alert alert-light border">
                                <h6 class="fw-bold mb-2">
                                    <i class="bi bi-clipboard-check"></i> Persyaratan Dokumen
                                </h6>
                                <ul class="mb-0 ps-3">
                                    <li>Surat Permohonan Banding resmi dalam format PDF</li>
                                    <li>Menggunakan kop surat program studi/universitas</li>
                                    <li>Ditandatangani oleh pejabat berwenang (Ketua Program Studi/Dekan)</li>
                                    <li>Mencantumkan alasan banding secara jelas</li>
                                    <li>Dapat menggunakan template yang disediakan</li>
                                </ul>
                                <hr>
                                <a href="{{ route('upps.permohonan-banding.download-template-surat') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i> Download Template Surat
                                </a>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('upps.permohonan-banding') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="bi bi-send"></i> Kirim Permohonan Banding
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Info Card -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle text-info"></i> Informasi
                        </h6>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold">Masa Sanggah:</h6>
                        <p class="small text-muted mb-3">
                            Permohonan banding hanya dapat diajukan selama masa sanggah yang telah ditentukan.
                        </p>

                        <h6 class="fw-bold">Proses Banding:</h6>
                        <ol class="small mb-0 ps-3">
                            <li>Pengajuan banding diterima</li>
                            <li>Verifikasi kelengkapan dokumen</li>
                            <li>Penugasan asesor banding</li>
                            <li>Pelaksanaan asesmen banding</li>
                            <li>Penetapan hasil akhir</li>
                        </ol>
                    </div>
                </div>

                <!-- Warning Card -->
                <div class="card border-warning border-start border-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-warning">
                            <i class="bi bi-exclamation-triangle"></i> Perhatian
                        </h6>
                        <ul class="mb-0 ps-3 small">
                            <li class="mb-2">Pastikan data yang diisi sudah benar</li>
                            <li class="mb-2">File PDF maksimal 5MB</li>
                            <li class="mb-2">Permohonan yang sudah dikirim tidak dapat dibatalkan</li>
                            <li>Hasil banding bersifat final dan tidak dapat diganggu gugat</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // ✅ Load pengajuans when prodi selected (first time only)
    function loadPengajuans() {
        const prodiId = document.getElementById('select_prodi').value;
        if (prodiId) {
            window.location.href = "{{ route('upps.permohonan-banding.create') }}?prodi_id=" + prodiId;
        }
    }

    // ✅ Select pengajuan
    function selectPengajuan(pengajuanId) {
        // Remove selected class from all cards
        document.querySelectorAll('.pengajuan-card').forEach(card => {
            card.classList.remove('selected');
        });

        // Add selected class to clicked card
        const selectedCard = document.querySelector('[data-pengajuan-id="' + pengajuanId + '"]');
        if (selectedCard) {
            selectedCard.classList.add('selected');
            document.getElementById('id_pengajuan').value = pengajuanId;

            // Scroll to form
            const formElement = document.getElementById('formBanding');
            if (formElement) {
                formElement.scrollIntoView({
                    behavior: 'smooth'
                    , block: 'start'
                });
            }
        }
    }

    // ✅ File preview
    const fileInput = document.getElementById('file_surat_permohonan');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const preview = document.getElementById('filePreview');
            if (!e.target.files || !e.target.files.length) {
                preview.innerHTML = '';
                return;
            }

            const file = e.target.files[0];
            const fileSize = file.size / 1024 / 1024;

            if (file.type !== 'application/pdf') {
                preview.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle"></i> File harus berformat PDF</div>';
                e.target.value = '';
                return;
            }

            if (fileSize > 5) {
                preview.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle"></i> Ukuran file terlalu besar (' + fileSize.toFixed(2) + ' MB). Maksimal 5 MB</div>';
                e.target.value = '';
                return;
            }

            preview.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle"></i> <strong>' + file.name + '</strong> (' + fileSize.toFixed(2) + ' MB)</div>';
        });
    }

    // ✅ Form submit confirmation
    const btnSubmit = document.getElementById('btnSubmit');
    if (btnSubmit) {
        btnSubmit.addEventListener('click', function(e) {
            const pengajuanId = document.getElementById('id_pengajuan').value;
            if (!pengajuanId) {
                e.preventDefault();
                alert('Pilih pengajuan akreditasi terlebih dahulu!');
                document.querySelector('.pengajuan-card').scrollIntoView({
                    behavior: 'smooth'
                });
                return false;
            }

            if (!confirm('Apakah Anda yakin akan mengajukan permohonan banding ini? Tindakan ini tidak dapat dibatalkan.')) {
                e.preventDefault();
                return false;
            }

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
        });
    }

    // ✅ Auto-select if from URL param
    @if($selectedPengajuan)
    window.addEventListener('load', function() {
        selectPengajuan("{{ $selectedPengajuan->id }}");
    });
    @endif

</script>
@endpush
