{{-- resources\views\de\penyampaian-hasil-akreditasi\show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Penyampaian Hasil Akreditasi')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.penyampaian-hasil-akreditasi') }}">Penyampaian Hasil</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-clipboard-data"></i> Detail Penyampaian Hasil
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('de.penyampaian-hasil-akreditasi') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        {{-- Skor AL --}}
        <div class="col-lg-6 col-xl-3 my-1">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Skor AL</h6>
                    @if($hasil->skor_al)
                    <h1 class="mb-0 text-dark display-4">{{ number_format($hasil->skor_al, 0) }}</h1>
                    <small class="text-muted">dari 400</small>
                    @else
                    <h2 class="mb-0 text-muted">-</h2>
                    <small class="text-muted">Belum dihitung</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3 my-1">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Status Akreditasi (yang disampaikan ke PS)</h6>
                    @if($hasil->peringkat_akreditasi_hasil)
                    <span class="badge p-2 px-3 my-3 fs-6" style="background-color: {{ $hasil->getPeringkatColor() }}; color:#222">
                        {{ $hasil->peringkat_akreditasi_hasil }}
                    </span>
                    @if($hasil->statusAl->siklus_tahun)
                    <div><small class="text-muted">{{ $hasil->statusAl->siklus_tahun }} Tahun</small></div>
                    @endif
                    @else
                    <h3 class="mb-0 text-muted">Belum Difinalisasi</h3>
                    @endif
                </div>
            </div>
        </div>

        {{-- Status --}}
        <div class="col-lg-6 col-xl-3 my-1">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-4">Status Hasil</h6>
                    @if($hasil->isAlFinalized())
                    <span class="badge bg-light text-dark rounded-pill px-4 py-2 my-3 fs-6">
                        <i class="bi bi-check-circle"></i> Difinalisasi
                    </span>
                    @if($hasil->tanggal_finalisasi_al)
                    <div class="mt-2">
                        <small class="text-muted">{{ $hasil->tanggal_finalisasi_al->locale('id')->translatedFormat('d M Y') }}</small>
                    </div>
                    @endif
                    @else
                    <span class="badge bg-light text-dark rounded-pill px-4 py-2 my-3 fs-6">
                        <i class="bi bi-clock"></i> Draft
                    </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Total Elemen --}}
        <div class="col-lg-6 col-xl-3 my-1">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Elemen Dinilai</h6>
                    <h1 class="mb-0 display-4">{{ count($elemenList) }}</h1>
                    <small class="text-muted">Elemen standar</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    @if(!$hasil->isAlFinalized())
    <div class="card border-info mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1">
                        <i class="bi bi-info-circle text-info"></i>
                        Status Draft
                    </h5>
                    <p class="text-muted mb-0">
                        @php $peringkatAL = $hasil->getPeringkatFromSkor((float)($hasil->skor_al ?? 0)); @endphp
                        Hasil masih dalam status <strong>DRAFT</strong> dengan skor akhir yaitu: {{ number_format($hasil->skor_al, 0) }},
                        dan masuk ke kategori:
                        <span class="badge p-2 px-3 my-2" style="background-color: {{ $hasil->getPeringkatColor() }}; color:#222">
                            {{ $peringkatAL }}
                        </span>
                        <br>Anda dapat menghitung ulang atau melakukan finalisasi.
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group">
                        <form id="form-hitung-ulang" action="{{ route('de.penyampaian-hasil-akreditasi.calculate', $pengajuan->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="button" class="btn btn-outline-primary tombol-konfirmasi" data-id-form="form-hitung-ulang" data-message="hitung ulang hasil akreditasi">
                                <i class="bi bi-arrow-repeat"></i> Hitung Ulang
                            </button>
                        </form>

                        {{-- Tombol Finalisasi (trigger modal) --}}
                        <button type="button" class="btn btn-outline-success {{ !$canFinalize ? 'disabled' : '' }}" {{ !$canFinalize ? 'disabled' : '' }} data-bs-toggle="modal" data-bs-target="#modalFinalize" @if(!$canFinalize) title="Upload Berita Acara terlebih dahulu" @endif>
                            <i class="bi bi-lock"></i> Finalisasi
                        </button>
                    </div>

                    @if(!$canFinalize)
                    <div class="mt-2">
                        <small class="text-danger">
                            <i class="bi bi-exclamation-circle"></i>
                            Upload Berita Acara untuk finalisasi
                        </small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @elseif($hasil->isAlFinalized())
    <div class="alert alert-light alert-permanent">
        <i class="bi bi-check-circle-fill me-2"></i>
        Hasil telah <strong>Disampaikan ke Program Studi</strong> pada {{ $hasil->tanggal_finalisasi_al?->locale('id')->translatedFormat('d F Y, H:i') }}
    </div>
    @else
    <div class="alert alert-light alert-permanent">
        <i class="bi bi-check-circle-fill me-2"></i>
        Hasil belum diketahui
    </div>
    @endif

    @php
    $resumeBabs = $resume['bab'] ?? \App\Models\HasilAkreditasi::resumeAsesmenSkeleton()['bab'];
    $babDefaults = \App\Models\HasilAkreditasi::resumeBabDefaults();
    $charLimit = \App\Models\HasilAkreditasi::resumeBabCharLimit();

    $metaMasaBerlaku = old('masa_berlaku_tahun', $pengajuan->masa_berlaku_tahun ?? '');
    $metaNomorSertif = old('nomor_sertifikat', $pengajuan->nomor_sertifikat ?? $pengajuan->generateNomorSertifikat());
    $tanggalSertifikat = $pengajuan->tanggal_sertifikat ?? $pengajuan->tanggal_penetapan;
    $metaTanggalSertif = old('tanggal_sertifikat', optional($tanggalSertifikat)->format('Y-m-d'));
    $metaKeterangan = old('keterangan', '');
    @endphp

    <div class="card mb-4" id="cardResume">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-file-richtext"></i>
                Draft Resume Asesmen Akreditasi
            </h5>

            <span id="resumeStatusBadge">
                @if($resumeSaved)
                <span class="badge bg-success">Tersimpan</span>
                @else
                <span class="badge bg-warning text-dark">Belum disimpan</span>
                @endif
            </span>
        </div>

        <div class="card-body">
            <div id="babContainer">
                @foreach($resumeBabs as $idx => $bab)
                @php
                $edId = 'tme_bab_' . $idx;
                $babTitle = $bab['title'] ?? '';
                $babContent = !empty(trim(strip_tags($bab['content'] ?? '')))
                ? $bab['content']
                : '';
                @endphp

                <div class="border rounded p-3 mb-3 bab-item">
                    <label class="form-label small fw-semibold">Judul BAB</label>
                    <input type="text" class="form-control form-control-sm mb-2 bab-title" value="{{ $babTitle }}" maxlength="120">

                    <label class="form-label small fw-semibold">Isi BAB</label>
                    <textarea id="{{ $edId }}" class="form-control bab-content" rows="5">{!! $babContent !!}</textarea>
                </div>
                @endforeach
            </div>

            <hr>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small">Masa Berlaku Sertifikat</label>
                    <input type="number" id="meta_masa_berlaku_tahun" class="form-control form-control-sm" value="{{ $metaMasaBerlaku }}" min="1" max="10">
                </div>

                <div class="col-md-4">
                    <label class="form-label small">Nomor Sertifikat</label>
                    <input type="text" id="meta_nomor_sertifikat" class="form-control form-control-sm" value="{{ $metaNomorSertif }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label small">Tanggal Sertifikat</label>
                    <input type="date" id="meta_tanggal_sertifikat" class="form-control form-control-sm" value="{{ $metaTanggalSertif }}">
                </div>

                <div class="col-12">
                    <label class="form-label small">Keterangan</label>
                    <textarea id="meta_keterangan" class="form-control form-control-sm" rows="2">{{ $metaKeterangan }}</textarea>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="button" id="btnSaveResume" class="btn btn-sm btn-primary">
                    <i class="bi bi-floppy"></i> Simpan Draft Resume
                </button>
                {{-- tombol preview sertifikat sesuai request --}}
                <a href="{{ route('de.penyampaian-hasil-akreditasi.preview-sertifikat', $pengajuan->id) }}" class="btn btn-sm btn-outline-success" target="_blank">
                    <i class="bi bi-eye"></i> Preview
                </a>
            </div>
            <span id="saveStatus" class="small text-muted ms-2"></span>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-award"></i>
                Sertifikat Akreditasi
            </h5>
        </div>

        <div class="card-body">
            @if($sertifikat)
            <div class="d-flex justify-content-between align-items-center bg-light rounded p-3 mb-3">
                <div>
                    <strong>Sertifikat sudah diupload</strong><br>
                    <small class="text-muted">
                        {{ $sertifikat->original_filename ?? $sertifikat->nama_file }}
                    </small>
                </div>

                <a href="{{ route('de.penyampaian-hasil-akreditasi.download', [$pengajuan->id, 'sertifikat']) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye"></i> Lihat
                </a>
            </div>
            @else
            <div class="alert alert-warning alert-permanent">
                Sertifikat belum diupload.
            </div>
            @endif

            <form method="POST" action="{{ route('de.penyampaian-hasil-akreditasi.upload-sertifikat', $pengajuan->id) }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label small">Upload Sertifikat PDF</label>
                    <input type="file" name="file_sertifikat" class="form-control form-control-sm" accept=".pdf" {{ !$resumeSaved ? 'disabled' : '' }}>

                    @if(!$resumeSaved)
                    <small class="text-muted">
                        Simpan draft resume terlebih dahulu sebelum upload sertifikat.
                    </small>
                    @endif
                </div>

                <button type="submit" class="btn btn-sm btn-secondary" {{ !$resumeSaved ? 'disabled' : '' }}>
                    <i class="bi bi-upload"></i> Upload Sertifikat
                </button>
            </form>
        </div>
    </div>

    @if(!$hasil->isAlFinalized())
    {{-- Jika Berita Acara belum diupload --}}
    @if(!$beritaAcara)
    <div class="card border-light mb-4">
        <div class="card-header bg-light text-dark">
            <h5 class="mb-0">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Upload Berita Acara Rapat Penyampaian Hasil
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-light border-2 alert-permanent">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Perhatian:</strong> Anda harus mengupload <strong>Berita Acara Rapat Penyampaian Hasil Akreditasi</strong>
                sebelum dapat melakukan finalisasi hasil.
            </div>

            <form action="{{ route('de.penyampaian-hasil-akreditasi.upload-berita-acara', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="berita_acara" class="form-label">
                        File Berita Acara <span class="text-danger">*</span>
                    </label>
                    <input type="file" class="form-control @error('berita_acara') is-invalid @enderror" id="berita_acara" name="berita_acara" accept=".pdf" required>
                    <small class="text-muted">Format: PDF, Maksimal 10MB</small>
                    @error('berita_acara')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                    <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="3" placeholder="Catatan tambahan...">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-outline-dark">
                        <i class="bi bi-cloud-upload"></i> Upload Berita Acara
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Jika Berita Acara sudah diupload --}}
    @else
    <div class="card mb-4">
        <div class="card-header bg-light text-dark">
            <h5 class="mb-0">
                <i class="bi bi-file-earmark-check-fill"></i>
                Berita Acara Rapat Penyampaian Hasil
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                <div>
                    <h6 class="mb-1">{{ $beritaAcara->title }}</h6>
                    <p class="text-muted mb-2">
                        <i class="bi bi-file-pdf text-danger"></i>
                        {{ $beritaAcara->original_name }}
                    </p>
                    <small class="text-muted">
                        Diupload pada {{ $beritaAcara->uploaded_at?->locale('id')->translatedFormat('d M Y, H:i') }}
                    </small>
                    @if($beritaAcara->keterangan)
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="bi bi-chat-left-text"></i>
                            {{ $beritaAcara->keterangan }}
                        </small>
                    </div>
                    @endif
                </div>
                <div class="btn-group">
                    <a href="{{ route('de.penyampaian-hasil-akreditasi.download-berita-acara', $pengajuan->id) }}" class="btn btn-outline-dark" target="_blank">
                        <i class="bi bi-eye"></i> Lihat File
                    </a>
                    <form id="form-hapus-berita-finalisasi" action="{{ route('de.penyampaian-hasil-akreditasi.delete-berita-acara', $pengajuan->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-outline-danger tombol-hapus" data-id-form="form-hapus-berita-finalisasi" data-text="berita acara">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
    @else
    {{-- Jika sudah difinalisasi, tampilkan berita acara (read-only) --}}
    @if($beritaAcara)
    <div class="card mb-4">
        <div class="card-header bg-light text-dark">
            <h5 class="mb-0">
                <i class="bi bi-file-earmark-check-fill"></i>
                Berita Acara Rapat Penyampaian Hasil
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                <div>
                    <h6 class="mb-1">{{ $beritaAcara->title }}</h6>
                    <p class="text-muted mb-2">
                        <i class="bi bi-file-pdf text-danger"></i>
                        {{ $beritaAcara->original_name }}
                    </p>
                    <small class="text-muted">
                        Diupload pada {{ $beritaAcara->uploaded_at?->locale('id')->translatedFormat('d M Y, H:i') }}
                    </small>
                </div>
                <div>
                    <a href="{{ route('de.penyampaian-hasil-akreditasi.download-berita-acara', $pengajuan->id) }}" class="btn btn-outline-dark" target="_blank">
                        <i class="bi bi-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- Keterangan Batasan Skor --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-secondary text-white border-0">
            <h5 class="mb-0">
                <i class="bi bi-info-circle"></i>
                Keterangan Batasan Skor Akreditasi
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-2">
                @foreach($rentangSkor as $rentang)
                @php $makna = $rentang['makna'] ?? null; @endphp
                <div class="col-md-6">
                    <div class="d-flex align-items-start p-3 rounded h-100 border" style="background-color: {{ $rentang['warna'] ?? '#fff' }}">
                        <div class="me-3 text-nowrap pt-1">
                            <strong class="text-dark">
                                {{ $rentang['skor_min'] }} – {{ $rentang['skor_max'] }}
                            </strong>
                            @if(isset($rentang['persen_min']))
                            <div class="text-muted" style="font-size:.75rem">
                                {{ $rentang['persen_min'] }}–{{ $rentang['persen_max'] }}%
                            </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <strong class="text-dark">{{ $rentang['status'] }}</strong>
                            @if(isset($rentang['siklus_tahun']))
                            <span class="badge bg-secondary ms-1" style="font-size:.7rem">
                                {{ $rentang['siklus_tahun'] }} Tahun
                            </span>
                            @endif
                            @if($makna)
                            <div class="mt-1">
                                @if(is_array($makna))
                                <ul class="mb-0 ps-3" style="font-size:.8rem">
                                    @foreach($makna as $m)
                                    <li class="text-muted">{{ $m }}</li>
                                    @endforeach
                                </ul>
                                @else
                                <small class="text-muted">{{ $makna }}</small>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Penjelasan dua lapis syarat Unggul --}}
            <div class="alert alert-light alert-permanent mt-3 mb-0">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Catatan:</strong> Status akreditasi <strong>Unggul</strong> memerlukan dua lapis syarat:
                <div class="row mt-2 g-2">
                    <div class="col-md-6">
                        <div class="border rounded p-2" style="font-size:.83rem">
                            <strong class="d-block mb-1">
                                <i class="bi bi-key me-1"></i> Syarat Kunci
                            </strong>
                            <a href="{{ route('pengajuan.borang.lkps.preview',$pengajuan->id) }}">Lihat Detail LKPS</a>
                            <ul class="mb-0 ps-3 text-muted">
                                <li>Skor ≥ {{ $validationSummary['skor_minimum'] }}</li>
                                <li>Rasio DTPS : Mahasiswa sesuai batas rumpun</li>
                                <li>Jabatan/sertifikasi dosen memenuhi persentase minimum</li>
                                <li>Capaian lulusan (publikasi/inovasi) memenuhi persentase minimum</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-2" style="font-size:.83rem">
                            <strong class="d-block mb-1">
                                <i class="bi bi-stars me-1"></i> Syarat Perlu
                            </strong>
                            <ul class="mb-0 ps-3 text-muted">
                                <li>
                                    Minimal <strong>1 elemen</strong> berkategori
                                    <em>"{{ \App\Models\JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI }}"</em> di <strong>setiap</strong> kriteria:
                                    <span class="fw-semibold">
                                        {{ implode(', ', array_keys($validationSummary['kriteria_status'])) }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Validasi Syarat Unggul (hanya tampil jika skor memenuhi) --}}
    @if($validationSummary['skor_memenuhi'])
    @php $syaratP1 = $validationSummary['syarat_p1']; @endphp
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-{{ $validationSummary['dapat_unggul'] ? 'shield-check' : 'exclamation-triangle' }}"></i>
                Validasi Syarat Status Akreditasi UNGGUL
            </h5>
        </div>
        <div class="card-body">

            {{-- ══════════════════════════════════════════
             BLOK 1: SYARAT KUNCI
        ══════════════════════════════════════════ --}}
            <h6 class="text-muted mb-2 mt-1">
                <i class="bi bi-key me-1"></i> Syarat Kunci
            </h6>
            <div class="row mb-4 g-3">

                {{-- Skor --}}
                <div class="col-md-6">
                    <div class="d-flex align-items-start p-3 bg-light rounded h-100">
                        <i class="bi bi-check-circle-fill text-dark fs-4 me-3 mt-1 flex-shrink-0"></i>
                        <div>
                            <strong>Skor Memenuhi Syarat</strong>
                            <div class="text-muted" style="font-size:.85rem">
                                Skor {{ number_format($validationSummary['skor'], 0) }}
                                ≥ {{ $validationSummary['skor_minimum'] }} ✓
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Rasio DTPS --}}
                <div class="col-md-6">
                    <div class="d-flex align-items-start p-3 bg-light rounded h-100">
                        <i class="bi bi-{{ $syaratP1['rasio']['memenuhi'] ? 'check-circle-fill text-dark' : 'x-circle-fill text-danger' }} fs-4 me-3 mt-1 flex-shrink-0"></i>
                        <div>
                            <strong>Rasio DTPS : Mahasiswa</strong>
                            <div class="text-muted" style="font-size:.85rem">
                                {{ $syaratP1['rasio']['keterangan'] }}
                            </div>
                            @if(!is_null($syaratP1['rasio']['rasio'] ?? null))
                            <div class="text-muted mt-1" style="font-size:.78rem">
                                DTPS: {{ $syaratP1['rasio']['jumlah_dtps'] }}
                                &middot; Mahasiswa: {{ $syaratP1['rasio']['jumlah_mahasiswa'] ?? '—' }}
                                &middot; Rumpun: <em>{{ $syaratP1['rasio']['rumpun'] }}</em>
                            </div>
                            @else
                            <div class="text-warning mt-1" style="font-size:.78rem">
                                <i class="bi bi-exclamation-triangle me-1"></i>Data P.1/E.2 belum tersedia
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Jabatan / Sertifikasi Dosen --}}
                <div class="col-md-6">
                    <div class="d-flex align-items-start p-3 bg-light rounded h-100">
                        <i class="bi bi-{{ $syaratP1['jabatan']['memenuhi'] ? 'check-circle-fill text-dark' : 'x-circle-fill text-danger' }} fs-4 me-3 mt-1 flex-shrink-0"></i>
                        <div>
                            <strong>
                                {{ ucfirst($syaratP1['jabatan']['label_jabatan'] ?? 'Jabatan Valid') }}
                                ≥ {{ $syaratP1['jabatan']['persen_minimum'] ?? 50 }}%
                            </strong>
                            <div class="text-muted" style="font-size:.85rem">
                                {{ $syaratP1['jabatan']['keterangan'] }}
                            </div>
                            @if(($syaratP1['jabatan']['total_dtps'] ?? 0) > 0)
                            <div class="text-muted mt-1" style="font-size:.78rem">
                                {{ $syaratP1['jabatan']['jumlah_valid'] }}
                                dari {{ $syaratP1['jabatan']['total_dtps'] }} DTPS
                                ({{ $syaratP1['jabatan']['persen_valid'] }}%)
                                @if($syaratP1['jabatan']['filter_dtps_aktif'] ?? false)
                                &middot; <span class="text-success">Filter P.1.3 aktif</span>
                                @endif
                            </div>
                            @else
                            <div class="text-warning mt-1" style="font-size:.78rem">
                                <i class="bi bi-exclamation-triangle me-1"></i>Data P.1 belum tersedia
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Capaian Lulusan --}}
                <div class="col-md-6">
                    <div class="d-flex align-items-start p-3 bg-light rounded h-100">
                        <i class="bi bi-{{ $syaratP1['lulusan']['memenuhi'] ? 'check-circle-fill text-dark' : 'x-circle-fill text-danger' }} fs-4 me-3 mt-1 flex-shrink-0"></i>
                        <div>
                            <strong>
                                Capaian Lulusan ≥ {{ $syaratP1['lulusan']['persen_minimum'] ?? 10 }}%
                            </strong>
                            <div class="text-muted" style="font-size:.85rem">
                                {{ $syaratP1['lulusan']['keterangan'] }}
                            </div>
                            @if(($syaratP1['lulusan']['jumlah_mahasiswa'] ?? 0) > 0)
                            @php
                            $lulusan = $syaratP1['lulusan'];
                            @endphp
                            {{-- Ratio utama: mahasiswa terlibat (dipakai untuk syarat) --}}
                            <div class="text-muted mt-1" style="font-size:.78rem">
                                <strong>Mahasiswa terlibat:</strong>
                                {{ $lulusan['jumlah_mahasiswa_terlibat'] }}
                                dari {{ $lulusan['jumlah_mahasiswa'] }} mahasiswa TA
                                &nbsp;
                                <span class="badge {{ $lulusan['memenuhi'] ? 'bg-secondary' : 'bg-light text-danger border' }}">
                                    {{ number_format($lulusan['ratio_mahasiswa_terlibat'], 1) }}%
                                </span>
                                &nbsp;≥ {{ $lulusan['persen_minimum'] }}% ?
                                {{ $lulusan['memenuhi'] ? '✓' : '✗' }}
                            </div>
                            {{-- Ratio informatif: jumlah item penelitian/karya --}}
                            <div class="text-muted mt-1" style="font-size:.78rem">
                                <strong>Jumlah karya/penelitian:</strong>
                                {{ $lulusan['jumlah_penelitian'] }}
                                dari {{ $lulusan['jumlah_mahasiswa'] }} mahasiswa TA
                                &nbsp;
                                <span class="badge bg-light text-muted border">
                                    {{ number_format($lulusan['ratio_jumlah_penelitian_mahasiswa'], 1) }}%
                                </span>
                            </div>
                            <div class="text-muted mt-1" style="font-size:.78rem">
                                Tipe: <em>{{ $lulusan['tipe_capaian'] ?? '-' }}</em>
                            </div>
                            @else
                            <div class="text-warning mt-1" style="font-size:.78rem">
                                <i class="bi bi-exclamation-triangle me-1"></i>Data R.3.1 belum tersedia
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            <hr class="my-3">

            {{-- ══════════════════════════════════════════
             BLOK 2: SYARAT PERLU
        ══════════════════════════════════════════ --}}
            <h6 class="text-muted mb-2">
                <i class="bi bi-stars me-1"></i> Syarat Perlu — Pelampauan Standar per Kriteria
            </h6>

            {{-- Badge ringkasan --}}
            <div class="mb-3">
                @if($validationSummary['pelampauan_memenuhi'])
                <span class="badge bg-light text-dark border">
                    <i class="bi bi-check-circle me-1"></i>
                    Semua {{ count($validationSummary['kriteria_status']) }} kriteria terpenuhi
                </span>
                @else
                <span class="badge bg-light text-danger border">
                    <i class="bi bi-x-circle me-1"></i>
                    {{ count($validationSummary['missing_kriteria']) }} dari {{ count($validationSummary['kriteria_status']) }} kriteria belum terpenuhi
                </span>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="12%">Kriteria</th>
                            {{-- <th>Nama Kriteria</th> --}}
                            <th class="text-center" width="22%">Status Pelampauan</th>
                            <th class="text-center" width="18%">Jumlah Elemen {{ \App\Models\JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($validationSummary['kriteria_status'] as $kode => $status)
                        {{-- {{ dd($status) }} --}}
                        <tr class="{{ !$status['has_pelampauan'] ? 'table-light' : '' }}">
                            <td class="text-center">
                                <strong>{{ $kode }}</strong>
                            </td>
                            {{-- <td style="font-size:.85rem">
                                {{ $status['nama_kriteria'] ?? '—' }}
                            </td> --}}
                            <td class="text-center">
                                @if($status['has_pelampauan'])
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-check-circle"></i> Terpenuhi
                                </span>
                                @else
                                <span class="badge bg-light text-danger border">
                                    <i class="bi bi-x-circle"></i> Belum Terpenuhi
                                </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $status['jumlah_elemen_skor_4'] > 0 ? 'bg-secondary' : 'bg-light text-muted border' }}">
                                    {{ $status['jumlah_elemen_skor_4'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Ringkasan akhir --}}
            @if(!$validationSummary['dapat_unggul'])
            <div class="alert alert-light border-start border-dark border-2 alert-permanent mt-3 mb-0">
                <strong>⚠️ Perhatian:</strong>
                Meskipun skor ≥ {{ $validationSummary['skor_minimum'] }},
                status <strong>UNGGUL tidak dapat ditetapkan</strong> karena syarat berikut belum terpenuhi:
                <ul class="mb-0 mt-2" style="font-size:.9rem">

                    {{-- Syarat Kunci yang gagal --}}
                    @if(!$syaratP1['rasio']['memenuhi'])
                    <li>
                        <span class="badge bg-light text-dark border me-1">Syarat Kunci</span>
                        {{ $syaratP1['rasio']['keterangan'] }}
                    </li>
                    @endif
                    @if(!$syaratP1['jabatan']['memenuhi'])
                    <li>
                        <span class="badge bg-light text-dark border me-1">Syarat Kunci</span>
                        {{ $syaratP1['jabatan']['keterangan'] }}
                    </li>
                    @endif
                    @if(!$syaratP1['lulusan']['memenuhi'])
                    <li>
                        <span class="badge bg-light text-dark border me-1">Syarat Kunci</span>
                        {{ $syaratP1['lulusan']['keterangan'] }}
                    </li>
                    @endif

                    {{-- Syarat Perlu yang gagal --}}
                    @if(!$validationSummary['pelampauan_memenuhi'])
                    <li>
                        <span class="badge bg-light text-dark border me-1">Syarat Perlu</span>
                        Kriteria <strong>{{ implode(', ', $validationSummary['missing_kriteria']) }}</strong>
                        belum memiliki minimal 1 elemen {{ \App\Models\JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI }}.
                    </li>
                    @endif

                </ul>
            </div>
            @else
            <div class="alert alert-light border-start border-success border-3 alert-permanent mt-3 mb-0">
                <i class="bi bi-shield-check me-2"></i>
                <strong>Semua syarat Terakreditasi Unggul terpenuhi.</strong>
                Syarat kunci (rasio DTPS, jabatan dosen, capaian lulusan) dan syarat perlu
                (pelampauan standar di semua kriteria) telah terpenuhi.
            </div>
            @endif

        </div>
    </div>
    @endif
    {{-- {{ dd($validationSummary) }} --}}

    {{-- Detail Skor per Kriteria --}}
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-bar-chart-fill"></i>
                Detail Skor per Kriteria
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="10%">Kode</th>
                            <th width="40%">Nama Kriteria</th>
                            <th class="text-center" width="15%">Jumlah Elemen</th>
                            <th class="text-center" width="15%">Total Bobot</th>
                            <th class="text-center" width="15%">Skor Tertimbang</th>
                            <th class="text-center" width="10%">Pelampauan Standar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kriteriaList as $kode => $data)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $kode }}</span></td>
                            <td>{{ $data['nama'] }}</td>
                            <td class="text-center">{{ $data['elemen_count'] }}</td>
                            <td class="text-center">{{ number_format($data['total_bobot'], 2) }}</td>
                            <td class="text-center">
                                <strong class="text-dark">{{ number_format($data['total_skor'], 2) }}</strong>
                            </td>
                            <td class="text-center">
                                @if($data['has_pelampauan'])
                                <i class="bi bi-check-circle-fill text-dark fs-5"></i>
                                @else
                                <i class="bi bi-dash-circle text-muted fs-5"></i>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(!empty($kriteriaList))
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">TOTAL:</th>
                            <th class="text-center">{{ number_format($hasil->total_bobot_al ?? 0, 2) }}</th>
                            <th class="text-center">
                                <strong class="text-dark fs-5">{{ number_format($hasil->skor_al ?? 0, 2) }}</strong>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Detail Skor per Elemen Standar --}}
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-list-check"></i>
                Detail Skor per Elemen Standar
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover" id="table-elemen">
                    <thead class="table-light align-middle">
                        <tr>
                            <th width="5%">Kriteria</th>
                            <th width="30%">Pernyataan Elemen</th>
                            <th class="text-center" width="35%">Kategori</th>
                            <th class="text-center" width="10%">Bobot</th>
                            <th class="text-center" width="15%">Skor Tertimbang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $grouped = collect($elemenList)->groupBy('kode_kriteria');
                        @endphp

                        @forelse($grouped as $kodeKriteria => $items)
                        @php $rowspan = $items->count(); @endphp

                        @foreach($items as $index => $elemen)
                        @php
                        $kategori = $elemen['skor_kategori'] ?? ['label' => '-', 'color' => '#e9ecef'];
                        @endphp
                        <tr>
                            {{-- ✅ Merge hanya badge Kriteria --}}
                            @if($index === 0)
                            <td rowspan="{{ $rowspan }}" class="align-middle text-center">
                                <span class="badge bg-secondary fs-6 py-2 px-3">{{ $kodeKriteria }}</span>
                            </td>
                            @endif

                            {{-- Elemen --}}
                            <td>
                                <code class="text-primary fw-bold">
                                    {{ $elemen['kode_elemen'] }}
                                </code>
                                <span>
                                    {{ Str::limit($elemen['nama_elemen'], 120) }}
                                </span>
                            </td>

                            {{-- Kategori --}}
                            <td class="text-center">
                                <span class="badge text-wrap" style="width: 15rem; background-color: {{ $kategori['color'] }}; color: #222;">
                                    {{ $kategori['label'] }}
                                </span>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        ({{ number_format($elemen['skor'], 2) }})
                                    </small>
                                </div>
                            </td>

                            {{-- Bobot --}}
                            <td class="text-center">
                                {{ number_format($elemen['bobot'], 2) }}
                            </td>

                            {{-- Skor Tertimbang --}}
                            <td class="text-center">
                                <strong>
                                    {{ number_format($elemen['skor_tertimbang'], 2) }}
                                </strong>
                            </td>
                        </tr>
                        @endforeach

                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Belum ada data
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Catatan Validasi --}}
    @if($hasil->catatan_validasi)
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-chat-left-text-fill"></i>
                Catatan Validasi
            </h5>
        </div>
        <div class="card-body">
            <pre class="mb-0" style="white-space: pre-wrap;">{{ $hasil->catatan_validasi }}</pre>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Informasi Hasil Akreditasi -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Penyampaian Hasil Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Program Studi</th>
                            <td>: {{ $pengajuan->studyProgram->name }}</td>
                        </tr>
                        <tr>
                            <th>Universitas</th>
                            <td>: {{ $pengajuan->studyProgram->university->name }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Permohonan Akreditasi</th>
                            <td>: {{ $pengajuan->jenis_akreditasi_label }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Hasil Disampaikan</th>
                            <td>
                                : {{ $pengajuan->tanggal_hasil_akreditasi_dikirim
                                    ? $pengajuan->tanggal_hasil_akreditasi_dikirim->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Penyampaian Hasil Akreditasi</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('penyampaian_hasil', 'de','label_long_for','text-dark') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ringkasan Hasil -->

            <!-- Timeline -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                    ];

                    $logs = $pengajuan->statusLog
                    ->whereIn('status_to', $filterStatuses)
                    ->sortBy('created_at')
                    ->unique('status_to')
                    ->values();
                    @endphp

                    @if($logs->count() > 0)
                    <div class="timeline">
                        @foreach($logs as $log)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill text-success" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label'] ?? $log->status_to }}
                                    </strong>
                                    <br>
                                    <small class="text-muted">{{ $log->created_at->locale('id')->translatedFormat('d M Y H:i') }}</small>

                                    {{-- @if($log->keterangan)
                                    <br>
                                    <small class="text-muted fst-italic">{{ $log->keterangan }}</small>
                                    @endif --}}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted text-center mb-0">Belum ada riwayat</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Modal --}}
<div class="modal fade" id="modalFinalize" tabindex="-1" aria-labelledby="modalFinalizeLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="form-finalize-hasil-akreditasi" action="" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFinalizeLabel">Finalisasi & Atur Masa Sanggah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-light alert-permanent mb-3">
                        Pilih tanggal & waktu berakhir masa sanggah. Default: <strong>7 hari</strong> dari sekarang.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Masa sanggah berakhir pada</label>
                        @php
                        $minEnd = now()->addMinute()->format('Y-m-d\TH:i'); // minimal 1 menit dari server
                        //$defaultEnd = now()->addMinute(3)->format('Y-m-d\TH:i'); // default 7 hari
                        $defaultEnd = now()->addDays(7)->format('Y-m-d\TH:i'); // default 7 hari
                        @endphp
                        <input type="datetime-local" name="tanggal_masa_sanggah_selesai" class="form-control" min="{{ $minEnd }}" value="{{ old('tanggal_masa_sanggah_selesai', $defaultEnd) }}" required>

                        <small class="text-muted">
                            Waktu server: {{ now()->format('d-m-Y H:i:s') }} ({{ config('app.timezone') }})
                        </small>
                        @error('tanggal_masa_sanggah_selesai')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Timezone mengikuti aplikasi (Asia/Jakarta).</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-outline-success tombol-konfirmasi-hasil-akreditasi" data-id-form="form-finalize-hasil-akreditasi" data-message="Finalisasi hasil akreditasi? Tindakan ini tidak dapat dibatalkan" data-href="{{ route('de.penyampaian-hasil-akreditasi.finalize', $pengajuan->id) }}">
                        <i class="bi bi-lock"></i> Finalisasi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
@php
$errorHasTanggalMasaSanggahSelesai = $errors->has('tanggal_masa_sanggah_selesai');
@endphp
<script>
    alertConfirm({
        selector: '.tombol-konfirmasi-hasil-akreditasi'
        , formId: 'form-finalize-hasil-akreditasi'
        , isMessage: true
        , isDataHref: true
    });
    document.addEventListener('DOMContentLoaded', function() {
        // Kalau ada error untuk field dalam modal, buka modal otomatis
        @if($errorHasTanggalMasaSanggahSelesai)
        const el = document.getElementById('modalFinalize');
        if (el) {
            const modal = new bootstrap.Modal(el);
            modal.show();
        }
        @endif
    });

    let btnSave = document.getElementById('btnSaveResume');

    if (btnSave) {
        btnSave.addEventListener('click', async function() {
            const btn = this;
            const status = document.getElementById('saveStatus');

            btn.disabled = true;
            status.innerText = 'Menyimpan...';

            const bab = [];

            document.querySelectorAll('.bab-item').forEach(function(item) {
                let titleInput = item.querySelector('.bab-title');
                let textarea = item.querySelector('.bab-content');

                const title = titleInput ? titleInput.value : '';

                bab.push({
                    title: title
                    , content: textarea ? textarea.value : ''
                });
            });

            let masa = document.getElementById('meta_masa_berlaku_tahun');
            let nomor = document.getElementById('meta_nomor_sertifikat');
            let tanggal = document.getElementById('meta_tanggal_sertifikat');
            let ket = document.getElementById('meta_keterangan');

            const payload = {
                bab: bab
                , meta: {
                    masa_berlaku_tahun: masa ? masa.value : ''
                    , nomor_sertifikat: nomor ? nomor.value : ''
                    , tanggal_sertifikat: tanggal ? tanggal.value : ''
                    , keterangan: ket ? ket.value : ''
                }
            };

            try {
                const response = await fetch("{{ route('de.penyampaian-hasil-akreditasi.save-resume', $pengajuan->id) }}", {
                    method: 'POST'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        , 'Accept': 'application/json'
                    }
                    , body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (!response.ok || !result.ok) {
                    throw new Error(result.message || 'Gagal menyimpan resume.');
                }

                status.innerText = result.message;

                setTimeout(function() {
                    window.location.reload();
                }, 700);
            } catch (e) {
                status.innerText = e.message;
                btn.disabled = false;
            }
        });
    }

</script>
@endpush
@endsection
