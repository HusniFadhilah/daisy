{{-- resources/views/de/pelaporan-hasil-akreditasi/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pelaporan Hasil Akreditasi')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.pelaporan-hasil-akreditasi') }}">Pelaporan Hasil Akreditasi</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-graph-up"></i> Detail Pelaporan Hasil Akreditasi
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('de.pelaporan-hasil-akreditasi') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @php
    // Catatan: bagian hasil/asesmen kamu sebelumnya belum lengkap (variabel $peringkat belum didefinisikan).
    // Saya biarkan aman: ambil dari field yang paling umum dipakai.
    $peringkat = $pengajuan->peringkat_final ?? $pengajuan->peringkat_hasil ?? '-';
    $hasil = $pengajuan->asesmen->hasil ?? null;

    $allowed = [
    \App\Models\PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
    \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
    \App\Models\PengajuanAkreditasi::STATUS_SELESAI,
    ];

    $log = $pengajuan->latestRelevantStatusLog($allowed);

    // Ambil dokumen terbaru
    $dokumenHasil = $pengajuan->dokumen
    ->whereIn('jenis_dokumen', ['sertifikat', 'laporan_hasil'])
    ->where('is_latest', true)
    ->values();

    $laporanHasil = $dokumenHasil->firstWhere('jenis_dokumen', 'laporan_hasil');
    $sertifikat = $dokumenHasil->firstWhere('jenis_dokumen', 'sertifikat');

    // Rule upload: hanya jika belum "HASIL_DILAPORKAN"
    $canUpload = !in_array($log?->status_to, [\App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,\App\Models\PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,\App\Models\PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,\App\Models\PengajuanAkreditasi::STATUS_SELESAI]);

    $hasLaporan = !is_null($laporanHasil);
    $hasSertifikat = !is_null($sertifikat);
    // $resume sudah di-pass dari controller (show method)
    $resumeBabs = $resume['bab'] ?? \App\Models\HasilAkreditasi::resumeAsesmenSkeleton()['bab'];
    $babDefaults = \App\Models\HasilAkreditasi::resumeBabDefaults();
    $charLimit = \App\Models\HasilAkreditasi::resumeBabCharLimit();
    $resumeSaved = $hasil ? $hasil->hasResumeAsesmen() : false;

    // Nilai awal field meta (untuk prefill di form resume)
    $metaMasaBerlaku = old('masa_berlaku_tahun',$pengajuan->masa_berlaku_tahun ?? ($hasil && $hasil->statusFinal ? $hasil->statusFinal->siklus_tahun : ''));
    $metaNomorSertif = old('nomor_sertifikat', $pengajuan->nomor_sertifikat ?? $pengajuan->generateNomorSertifikat());
    $tanggalSertifikat = $pengajuan->tanggal_sertifikat ?? $pengajuan->tanggal_penetapan;
    $metaTanggalSertif = old('tanggal_sertifikat',
    $tanggalSertifikat?->format('Y-m-d'));
    $metaKeterangan = old('keterangan', '');
    @endphp

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN)
            <div class="alert alert-light alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Proses Pelaporan Hasil</strong><br>
                Mohon melihat dan memverifikasi Sertifikat Akreditasi pada <a href="{{ route('de.pelaporan-hasil-akreditasi.preview-sertifikat', $pengajuan->id) }}" target="_blank">link berikut</a>. <br>Kemudian menyusun Laporan Hasil Akreditasi serta menguploadnya pada bagian bawah ini
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN)
            <div class="alert alert-light alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Proses Pelaporan Hasil</strong><br>
                Keseluruhan permohonan dan pelaporan proses akreditasi program studi dapat dilihat pada detail berikut
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_SELESAI)
            <div class="alert alert-light alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Pelaporan Hasil Selesai</strong><br>
                Keseluruhan permohonan dan pelaporan proses akreditasi program studi dapat dilihat pada detail berikut
            </div>
            @endif

            <!-- Dokumen Hasil & Laporan -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> Sertifikat Akreditasi dan Laporan Hasil Akreditasi
                    </h5>
                </div>
                <div class="card-body">

                    {{-- FORM UPLOAD (seperti contoh) --}}
                    {{-- FORM UPLOAD GABUNGAN --}}
                    @if($canUpload)
                    <div class="card mb-3" id="cardResume">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background:#f8f9fa; cursor:pointer;" onclick="toggleResume()">
                            <span class="fw-semibold">
                                <span class="badge rounded-pill me-2" style="background:#932136; font-size:.75rem;">1</span>
                                <i class="bi bi-file-richtext"></i> Isi Resume Asesmen
                                <small class="text-muted fw-normal ms-1">(wajib sebelum upload file)</small>
                            </span>
                            <span id="resumeStatusBadge">
                                @if($resumeSaved)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Tersimpan
                                </span>
                                @else
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock"></i> Belum disimpan
                                </span>
                                @endif
                            </span>
                        </div>

                        <div id="resumeBody" class="{{ $resumeSaved ? 'collapse' : '' }}">
                            <div class="card-body">

                                <div class="alert alert-light alert-permanent border small py-2 mb-3">
                                    <i class="bi bi-info-circle text-primary"></i>
                                    Isi setiap BAB, ubah judul, tambah atau hapus BAB sesuai kebutuhan.
                                    Klik <strong>Simpan Resume</strong> sebelum upload file dokumen.
                                </div>

                                {{-- ── Container BAB dinamis ── --}}
                                <div id="babContainer">
                                    @foreach($resumeBabs as $idx => $bab)
                                    @php
                                    $edId = 'tme_bab_' . $idx;
                                    $babTitle = isset($bab['title']) ? $bab['title'] : '';
                                    // Jika content null/kosong, prefill dengan default (jika index tersedia)
                                    $babContent = (isset($bab['content']) && !empty(trim(strip_tags($bab['content']))))
                                    ? $bab['content']
                                    : (isset($babDefaults[$idx]) ? $babDefaults[$idx] : '');
                                    @endphp

                                    <div class="bab-block card mb-3 border" data-idx="{{ $idx }}">
                                        <div class="card-header py-2 px-3 d-flex align-items-center gap-2" style="background:#f8f9fa;">
                                            {{-- Drag handle --}}
                                            <span class="drag-handle text-muted" style="cursor:grab; font-size:1.1rem;" title="Seret untuk mengubah urutan">⠿</span>

                                            {{-- Judul BAB (editable) --}}
                                            <input type="text" class="bab-title-input form-control form-control-sm fw-semibold" value="{{ $babTitle }}" maxlength="120" placeholder="Judul BAB..." style="font-size:.85rem; border:none; background:transparent;
                                      box-shadow:none; padding:0; flex:1;">

                                            {{-- Tombol hapus --}}
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-bab" title="Hapus BAB ini" onclick="hapusBab(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <div class="card-body p-2">
                                            {{-- Counter --}}
                                            <div class="d-flex justify-content-end mb-1">
                                                <span class="bab-counter small">
                                                    <span class="ctr-used fw-bold text-success">0</span>
                                                    <span class="text-muted"> / {{ number_format($charLimit) }}</span>
                                                </span>
                                            </div>
                                            {{-- TinyMCE textarea --}}
                                            <textarea id="{{ $edId }}" class="tinymce-resume" data-limit="{{ $charLimit }}" style="width:100%;">{{ $babContent }}</textarea>
                                            <div class="bab-progress progress mt-1" style="height:3px;">
                                                <div class="progress-bar bg-success" style="width:0%;transition:width .25s;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>{{-- /babContainer --}}

                                {{-- ── Tombol tambah BAB ── --}}
                                <button type="button" id="btnTambahBab" class="btn btn-sm btn-outline-secondary mb-3" onclick="tambahBab()">
                                    <i class="bi bi-plus-circle"></i> Tambah BAB
                                </button>

                                <hr class="my-3">

                                {{-- ── Field meta sertifikat ── --}}
                                <p class="small fw-semibold text-muted mb-2">
                                    <i class="bi bi-card-list"></i> Data Sertifikat
                                </p>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label form-label-sm small">Masa Berlaku (tahun)</label>
                                        <input type="number" id="meta_masa_berlaku" class="form-control form-control-sm" value="{{ $metaMasaBerlaku }}" min="1" max="10" placeholder="cth. 5">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label form-label-sm small">Nomor Sertifikat</label>
                                        <input type="text" id="meta_nomor_sertifikat" class="form-control form-control-sm" value="{{ $metaNomorSertif }}" maxlength="100" placeholder="cth. 0001/LAMDIK-SER/S1/01/2025">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label form-label-sm small">Tanggal Sertifikat Dikeluarkan</label>
                                        <input type="date" id="meta_tanggal_sertifikat" class="form-control form-control-sm" value="{{ $metaTanggalSertif }}">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label form-label-sm small">Keterangan (opsional)</label>
                                        <textarea id="meta_keterangan" class="form-control form-control-sm" rows="2" placeholder="Catatan tambahan...">{{ $metaKeterangan }}</textarea>
                                    </div>
                                </div>

                                {{-- ── Tombol simpan ── --}}
                                <div class="d-flex align-items-center gap-3">
                                    <button type="button" id="btnSaveResume" class="btn btn-sm" style="background:#932136;color:#fff;min-width:160px;">
                                        <span id="btnSaveText">
                                            <i class="bi bi-floppy"></i> Simpan Resume
                                        </span>
                                        <span id="btnSaveSpinner" class="d-none">
                                            <span class="spinner-border spinner-border-sm"></span> Menyimpan…
                                        </span>
                                    </button>
                                    <span id="saveStatus" class="small text-muted"></span>
                                </div>

                            </div>
                        </div>
                    </div>
                    @endif


                    {{-- ================================================================
     LANGKAH 2 — UPLOAD FILE (sama seperti sebelumnya)
     ================================================================ --}}

                    @if($canUpload && (!$hasLaporan || !$hasSertifikat))
                    <div class="card mb-4" id="cardUpload">
                        <div class="card-header d-flex justify-content-between align-items-center bg-secondary text-white">
                            <h6 class="mb-0">
                                <span class="badge rounded-pill bg-light text-dark me-2" style="font-size:.75rem;">2</span>
                                <i class="bi bi-upload"></i> Upload Laporan {{ !$hasSertifikat ? 'dan Sertifikat' : '' }}
                            </h6>
                            @if(!$resumeSaved)
                            <span class="badge bg-warning text-dark small">
                                <i class="bi bi-lock"></i> Selesaikan resume dulu
                            </span>
                            @endif
                        </div>

                        <div id="uploadLockOverlay" class="{{ $resumeSaved ? 'd-none' : '' }}" style="position:absolute;inset:0;background:rgba(255,255,255,.75);
                z-index:10;display:flex;align-items:center;justify-content:center;
                border-radius:0 0 .375rem .375rem;pointer-events:all;">
                            <div class="text-center p-3">
                                <i class="bi bi-lock-fill text-secondary" style="font-size:2rem;"></i>
                                <p class="mt-2 mb-0 text-muted small fw-semibold">
                                    Simpan resume terlebih dahulu untuk membuka bagian ini.
                                </p>
                            </div>
                        </div>

                        <div class="card-body" style="position:relative;">
                            <form id="formUploadDokumen" method="POST" action="{{ route('de.pelaporan-hasil-akreditasi.upload-dokumen', $pengajuan->id) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small">File Laporan Hasil</label>
                                        <input type="file" name="file_laporan" class="form-control form-control-sm @error('file_laporan') is-invalid @enderror" accept=".pdf,.doc,.docx" {{ !$resumeSaved ? 'disabled' : '' }}>
                                        <div class="form-text">PDF, DOC, DOCX · maks 10 MB</div>
                                        @error('file_laporan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    @if(!$hasSertifikat)
                                    <div class="col-md-6">
                                        <label class="form-label small">File Sertifikat</label>
                                        <input type="file" name="file_sertifikat" class="form-control form-control-sm @error('file_sertifikat') is-invalid @enderror" accept=".pdf" {{ !$resumeSaved ? 'disabled' : '' }}>
                                        <div class="form-text">PDF · maks 5 MB</div>
                                        @error('file_sertifikat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    @endif
                                    <div class="col-12">
                                        <button type="submit" id="btnUpload" class="btn btn-secondary btn-sm w-100" {{ !$resumeSaved ? 'disabled' : '' }}>
                                            <i class="bi bi-upload"></i> Upload Dokumen
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    @if(!$canUpload && $hasil && $hasil->hasResumeAsesmen())
                    @php $rDb = $hasil->getResumeAsesmenOrDefault(); @endphp
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between">
                            <h6 class="mb-0 text-secondary">
                                <i class="bi bi-clipboard2-check"></i> Resume Asesmen
                            </h6>
                            @if(!empty($rDb['diisi_pada']))
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($rDb['diisi_pada'])->locale('id')->translatedFormat('d M Y H:i') }}
                            </small>
                            @endif
                        </div>
                        <div class="card-body">
                            @foreach($rDb['bab'] ?? [] as $bab)
                            @php $html = isset($bab['content']) ? $bab['content'] : ''; @endphp
                            @if($html)
                            <div class="mb-3">
                                <p class="fw-semibold small text-uppercase text-secondary mb-1" style="letter-spacing:.4px;">
                                    {{ isset($bab['title']) ? $bab['title'] : '' }}
                                </p>
                                <div class="ps-3 border-start border-2" style="border-color:#932136!important;font-size:.875rem;line-height:1.7;">
                                    {!! $html !!}
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- LIST DOKUMEN TERUPLOAD --}}
                    @if($hasLaporan || $hasSertifikat)
                    <div class="card border-0">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-files"></i> Dokumen Terupload</h6>
                        </div>
                        <div class="card-body px-0 py-0">
                            @if($sertifikat)
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded mb-2">
                                <div>
                                    <i class="bi bi-file-text text-primary me-2"></i>
                                    <strong>Sertifikat</strong><br>
                                    <small class="text-muted">{{ $sertifikat->original_filename }}</small>
                                </div>
                                <a href="{{ route('de.pelaporan-hasil-akreditasi.download', [$pengajuan->id, 'sertifikat']) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                            @endif
                            @if($laporanHasil)
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <div>
                                    <i class="bi bi-file-text text-primary me-2"></i>
                                    <strong>Laporan Hasil</strong><br>
                                    <small class="text-muted">{{ $laporanHasil->original_filename }}</small>
                                </div>
                                <a href="{{ route('de.pelaporan-hasil-akreditasi.download', [$pengajuan->id, 'laporan_hasil']) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Laporan hasil akreditasi belum tersedia</p>
                    </div>
                    @endif

                    {{-- Tombol Selesaikan Pelaporan (opsional, seperti contoh) --}}
                    @if($canUpload && ($hasLaporan && $hasSertifikat))
                    <div class="card mt-3">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0"><i class="bi bi-check-circle"></i> Selesaikan Pelaporan</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('de.pelaporan-hasil-akreditasi.selesaikan', $pengajuan->id) }}">
                                @csrf
                                <div class="alert alert-info alert-permanent">
                                    <i class="bi bi-info-circle"></i>
                                    Setelah diselesaikan, status akan berubah menjadi "Hasil Dilaporkan" dan siap untuk arsip.
                                </div>
                                <div class="mb-3">
                                    <textarea name="catatan_pelaporan" class="form-control" rows="3" placeholder="Catatan pelaporan (opsional)..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-warning btn-md w-100">
                                    <i class="bi bi-check-circle"></i> Selesaikan Pelaporan
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            <!-- Informasi Permohonan -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Permohonan Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">Nomor Permohonan</th>
                            <td>: {{ $pengajuan->nomor_permohonan }}</td>
                        </tr>
                        <tr>
                            <th>Program Studi</th>
                            <td>: {{ $pengajuan->studyProgram->name }}</td>
                        </tr>
                        <tr>
                            <th>Universitas</th>
                            <td>: {{ $pengajuan->studyProgram->university->name }}</td>
                        </tr>
                        <tr>
                            <th>Jenjang</th>
                            <td>: {{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Permohonan</th>
                            <td>: {{ $pengajuan->jenis_akreditasi_label }}</td>
                        </tr>
                        <tr>
                            <th>Tahun Akreditasi</th>
                            <td>: {{ $pengajuan->tahun_akreditasi }}</td>
                        </tr>
                        <tr>
                            <th>Status Pelaporan Hasil Akreditasi</th>
                            <td>
                                : {!! $pengajuan->getCustomBadgeLastStatus('pelaporan_hasil', 'de', 'label_long_for','text-dark') !!}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Hasil Akhir Akreditasi -->
            <div class="card mb-4 border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Hasil Akhir Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Status Akreditasi</label><br>
                            @php
                            // fallback warna jika method tidak ada / hasil null
                            $warna = method_exists($hasil, 'getPeringkatColor') ? $hasil->getPeringkatColor($peringkat) : '#ced4da';
                            @endphp
                            <span class="badge p-2 px-3 my-2 fs-6" style="background-color: {{ $warna }}; color:#222">
                                {{ $peringkat }}
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tanggal Penetapan</label>
                            <p class="mb-0">
                                {{ $pengajuan->tanggal_penetapan
                                    ? $pengajuan->tanggal_penetapan->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tanggal Pelaporan</label>
                            <p class="mb-0">
                                {{ $pengajuan->tanggal_pelaporan_hasil
                                    ? $pengajuan->tanggal_pelaporan_hasil->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </p>
                        </div>
                        {{-- @if($pengajuan->tanggal_surat_permohonan_dikirim && $pengajuan->tanggal_pelaporan_hasil)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Total Durasi Proses</label>
                            <p class="mb-0">
                                {{ max(1,$pengajuan->tanggal_surat_permohonan_dikirim->diffInDays($pengajuan->tanggal_pelaporan_hasil)) }} hari
                        <small class="text-muted">(dari permohonan akreditasi hingga pelaporan hasil)</small>
                        </p>
                    </div>
                    @endif --}}
                </div>

                @if($pengajuan->peringkat_hasil_banding)
                <hr>
                <div class="alert alert-info alert-permanent border border-info mb-0">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>Catatan:</strong> Hasil yang ditampilkan adalah hasil akhir setelah proses banding.
                </div>
                @endif


                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                    <div>
                        <i class="bi bi-patch-check text-success me-2"></i>
                        <strong>Sertifikat Akreditasi</strong><br>
                    </div>
                    <div class="d-flex gap-2">
                        {{-- tombol preview sertifikat sesuai request --}}
                        <a href="{{ route('de.pelaporan-hasil-akreditasi.preview-sertifikat', $pengajuan->id) }}" class="btn btn-sm btn-outline-success" target="_blank">
                            <i class="bi bi-eye"></i> Preview
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Statistik Proses -->
        @if($pengajuan->tanggal_pengajuan && $pengajuan->tanggal_pelaporan_hasil)
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="bi bi-graph-up"></i> Statistik Proses
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Total Durasi</label>
                    <div class="display-6 fw-bold text-primary">
                        {{ max(1,$pengajuan->tanggal_surat_permohonan_dikirim->diffInDays($pengajuan->tanggal_pelaporan_hasil)) }}
                    </div>
                    <small class="text-muted">hari</small>
                </div>

                <hr>

                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Mulai:</span>
                        <strong>{{ $pengajuan->tanggal_pengajuan->locale('id')->translatedFormat('d M Y') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Selesai:</span>
                        <strong>{{ $pengajuan->tanggal_pelaporan_hasil->locale('id')->translatedFormat('d M Y') }}</strong>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Timeline -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history"></i> Riwayat Status
                </h5>
            </div>
            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                @php
                $filterStatuses = [
                \App\Models\PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                \App\Models\PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@8.3.1/tinymce.min.js"></script>
<script src="{{ asset('assets/js/tinymce.js') }}"></script>
<script>
    window.__RESUME_PAGE__ = {
        charLimit: "{{ $charLimit }}"
        , resumeSaved: "{{ $resumeSaved ? '1' : '0' }}"
        , saveUrl: "{{ route('de.pelaporan-hasil-akreditasi.save-resume', $pengajuan->id) }}"
        , csrfToken: "{{ csrf_token() }}"
        , babCount: "{{ count($resumeBabs) }}"
        , metaMasaBerlaku: "{{ $metaMasaBerlaku }}"
        , metaNomorSertif: "{{ addslashes($metaNomorSertif) }}"
        , metaTanggalSertif: "{{ $metaTanggalSertif }}"
        , metaKeterangan: "{{ addslashes($metaKeterangan) }}"
    };

</script>
<script>
    (function() {
        'use strict';

        // ── Baca semua nilai dari objek data, bukan dari Blade langsung ──
        var _d = window.__RESUME_PAGE__;
        var CHAR_LIMIT = parseInt(_d.charLimit, 10);
        var resumeSaved = _d.resumeSaved === '1';
        var SAVE_URL = _d.saveUrl;
        var CSRF = _d.csrfToken;
        var babCounter = parseInt(_d.babCount, 10);

        // Prefill field meta dari nilai PHP (hanya sekali, saat page load)
        (function prefillMeta() {
            var fields = {
                meta_masa_berlaku: _d.metaMasaBerlaku
                , meta_nomor_sertifikat: _d.metaNomorSertif
                , meta_tanggal_sertifikat: _d.metaTanggalSertif
                , meta_keterangan: _d.metaKeterangan
            , };
            for (var id in fields) {
                if (!fields.hasOwnProperty(id)) continue;
                var el = document.getElementById(id);
                if (!el || el.value !== '') continue; // jangan timpa jika sudah ada nilai
                el.value = fields[id];
            }
        }());

        // ── TinyMCE config dasar ──────────────────────────────────
        var TINYMCE_BASE = {
            license_key: 'gpl'
            , language: 'id'
            , height: 200
            , menubar: false
            , statusbar: false
            , plugins: 'lists'
            , toolbar: 'bold italic underline | bullist numlist | removeformat'
            , content_style: 'body{font-family:Montserrat,sans-serif;font-size:13px;' +
                'line-height:1.65;color:#222;margin:8px 12px;}' +
                'p{margin:0 0 6px 0;}ol,ul{margin:0 0 6px 0;padding-left:22px;}'
        , };

        // ── Hitung panjang plain text ─────────────────────────────
        function plainLen(html) {
            var d = document.createElement('div');
            d.innerHTML = html;
            var t = d.textContent || d.innerText || '';
            return t.replace(/\s+/g, ' ').trim().length;
        }

        // ── Update counter & progress bar pada 1 bab-block ───────
        function updateCounter(block, html) {
            var used = plainLen(html);
            var pct = Math.min(100, (used / CHAR_LIMIT) * 100);
            var usedEl = block.querySelector('.ctr-used');
            var barEl = block.querySelector('.bab-progress .progress-bar');

            if (!usedEl) return;

            usedEl.textContent = used.toLocaleString('id');

            var over = used > CHAR_LIMIT;
            var warn = pct >= 85;
            usedEl.className = 'ctr-used fw-bold ' +
                (over ? 'text-danger' : (warn ? 'text-warning' : 'text-success'));

            if (barEl) {
                barEl.style.width = pct + '%';
                barEl.className = 'progress-bar ' +
                    (over ? 'bg-danger' : (warn ? 'bg-warning' : 'bg-success'));
            }
        }

        // ── Init TinyMCE untuk 1 textarea (by id) ────────────────
        function initEditor(textareaId) {
            var cfg = {};
            // copy base config
            for (var k in TINYMCE_BASE) {
                if (TINYMCE_BASE.hasOwnProperty(k)) cfg[k] = TINYMCE_BASE[k];
            }
            cfg.selector = '#' + textareaId;
            cfg.setup = function(editor) {
                editor.on('init', function() {
                    var block = document.getElementById(textareaId);
                    block = block ? block.closest('.bab-block') : null;
                    if (block) updateCounter(block, editor.getContent());
                });
                editor.on('input keyup change SetContent', function() {
                    var block = document.getElementById(textareaId);
                    block = block ? block.closest('.bab-block') : null;
                    if (block) updateCounter(block, editor.getContent());
                });
            };
            tinymce.init(cfg);
        }

        // ── Init semua editor yang sudah ada di DOM ───────────────
        document.querySelectorAll('.tinymce-resume').forEach(function(ta) {
            initEditor(ta.id);
        });

        // ── Tambah BAB baru ───────────────────────────────────────
        window.tambahBab = function() {
            var idx = babCounter++;
            var edId = 'tme_bab_' + idx;
            var template = '<div class="bab-block card mb-3 border" data-idx="' + idx + '">' +
                '<div class="card-header py-2 px-3 d-flex align-items-center gap-2" style="background:#f8f9fa;">' +
                '<span class="drag-handle text-muted" style="cursor:grab;font-size:1.1rem;" title="Seret untuk mengubah urutan">⠿</span>' +
                '<input type="text" class="bab-title-input form-control form-control-sm fw-semibold"' +
                ' value="BAB Baru" maxlength="120" placeholder="Judul BAB..."' +
                ' style="font-size:.85rem;border:none;background:transparent;box-shadow:none;padding:0;flex:1;">' +
                '<button type="button" class="btn btn-sm btn-outline-danger btn-hapus-bab"' +
                ' title="Hapus BAB ini" onclick="hapusBab(this)"><i class="bi bi-trash"></i></button>' +
                '</div>' +
                '<div class="card-body p-2">' +
                '<div class="d-flex justify-content-end mb-1">' +
                '<span class="bab-counter small">' +
                '<span class="ctr-used fw-bold text-success">0</span>' +
                '<span class="text-muted"> / ' + CHAR_LIMIT.toLocaleString('id') + '</span>' +
                '</span></div>' +
                '<textarea id="' + edId + '" class="tinymce-resume"' +
                ' data-limit="' + CHAR_LIMIT + '" style="width:100%;"></textarea>' +
                '<div class="bab-progress progress mt-1" style="height:3px;">' +
                '<div class="progress-bar bg-success" style="width:0%;transition:width .25s;"></div>' +
                '</div></div></div>';

            var container = document.getElementById('babContainer');
            if (!container) return;
            container.insertAdjacentHTML('beforeend', template);

            // Init TinyMCE untuk editor baru
            initEditor(edId);
        };

        // ── Hapus BAB ─────────────────────────────────────────────
        window.hapusBab = function(btn) {
            var blocks = document.querySelectorAll('#babContainer .bab-block');
            if (blocks.length <= 1) {
                showToast('Minimal harus ada 1 BAB.', 'warning');
                return;
            }
            var block = btn.closest('.bab-block');
            if (!block) return;

            // Destroy TinyMCE instance
            var ta = block.querySelector('textarea');
            if (ta && ta.id) {
                var ed = tinymce.get(ta.id);
                if (ed) ed.remove();
            }
            block.remove();
        };

        // ── Kumpulkan semua BAB dari DOM ──────────────────────────
        function collectBab() {
            var result = [];
            var blocks = document.querySelectorAll('#babContainer .bab-block');
            blocks.forEach(function(block) {
                var titleEl = block.querySelector('.bab-title-input');
                var ta = block.querySelector('textarea');
                var title = (titleEl && titleEl.value) ? titleEl.value.trim() : '';
                var content = '';
                if (ta && ta.id) {
                    var ed = tinymce.get(ta.id);
                    content = ed ? ed.getContent() : ta.value;
                }
                result.push({
                    title: title
                    , content: content
                });
            });
            return result;
        }

        // ── Kumpulkan field meta dari DOM ─────────────────────────
        function collectMeta() {
            var masaEl = document.getElementById('meta_masa_berlaku');
            var nomorEl = document.getElementById('meta_nomor_sertifikat');
            var tglEl = document.getElementById('meta_tanggal_sertifikat');
            var ketEl = document.getElementById('meta_keterangan');
            return {
                masa_berlaku_tahun: masaEl ? masaEl.value.trim() : ''
                , nomor_sertifikat: nomorEl ? nomorEl.value.trim() : ''
                , tanggal_sertifikat: tglEl ? tglEl.value.trim() : ''
                , keterangan: ketEl ? ketEl.value.trim() : ''
            , };
        }

        // ── Validasi semua BAB ────────────────────────────────────
        function validateBab() {
            var errors = [];
            var blocks = document.querySelectorAll('#babContainer .bab-block');
            blocks.forEach(function(block, i) {
                var titleEl = block.querySelector('.bab-title-input');
                var ta = block.querySelector('textarea');
                var title = (titleEl && titleEl.value) ? titleEl.value.trim() : '';
                var content = '';
                if (ta && ta.id) {
                    var ed = tinymce.get(ta.id);
                    content = ed ? ed.getContent() : ta.value;
                }
                if (!title) errors.push('Judul BAB ke-' + (i + 1) + ' kosong.');
                if (plainLen(content) > CHAR_LIMIT) {
                    errors.push('BAB "' + (title || (i + 1)) + '" melebihi ' + CHAR_LIMIT + ' karakter.');
                }
            });
            return errors;
        }

        // ── Toggle collapse card resume ───────────────────────────
        window.toggleResume = function() {
            var body = document.getElementById('resumeBody');
            if (body) body.classList.toggle('collapse');
        };

        // ── Unlock form upload ────────────────────────────────────
        function unlockUpload() {
            var overlay = document.getElementById('uploadLockOverlay');
            if (overlay) overlay.classList.add('d-none');

            var fields = document.querySelectorAll(
                '#formUploadDokumen input, #formUploadDokumen textarea, #btnUpload'
            );
            fields.forEach(function(el) {
                el.removeAttribute('disabled');
            });
        }

        // ── Update badge setelah simpan ───────────────────────────
        function markSaved(savedAt) {
            var wrap = document.getElementById('resumeStatusBadge');
            if (wrap) {
                wrap.innerHTML = '<span class="badge bg-success">' +
                    '<i class="bi bi-check-circle"></i> Tersimpan' +
                    (savedAt ? ' &middot; ' + savedAt : '') +
                    '</span>';
            }
            var body = document.getElementById('resumeBody');
            if (body) body.classList.add('collapse');
        }

        // ── AJAX simpan ───────────────────────────────────────────
        var btnSave = document.getElementById('btnSaveResume');
        if (btnSave) {
            btnSave.addEventListener('click', function() {
                var errors = validateBab();
                if (errors.length) {
                    showToast(errors.join(' · '), 'danger');
                    return;
                }

                var btnText = document.getElementById('btnSaveText');
                var btnSpinner = document.getElementById('btnSaveSpinner');
                var saveStatus = document.getElementById('saveStatus');

                if (btnText) btnText.classList.add('d-none');
                if (btnSpinner) btnSpinner.classList.remove('d-none');
                btnSave.disabled = true;

                var bab = collectBab();
                var meta = collectMeta();

                fetch(SAVE_URL, {
                        method: 'POST'
                        , headers: {
                            'Content-Type': 'application/json'
                            , 'X-CSRF-TOKEN': CSRF
                            , 'Accept': 'application/json'
                            , 'X-Requested-With': 'XMLHttpRequest'
                        , }
                        , body: JSON.stringify({
                            bab: bab
                            , meta: meta
                        })
                    , })
                    .then(function(resp) {
                        return resp.json().then(function(data) {
                            return {
                                resp: resp
                                , data: data
                            };
                        });
                    })
                    .then(function(obj) {
                        var resp = obj.resp;
                        var data = obj.data;

                        if (!resp.ok || !data.ok) {
                            var msgs = '';
                            if (data.errors) {
                                msgs = Object.values(data.errors).reduce(function(acc, arr) {
                                    return acc.concat(Array.isArray(arr) ? arr : [arr]);
                                }, []).join(' · ');
                            } else {
                                msgs = data.message ? data.message : 'Gagal menyimpan.';
                            }
                            showToast(msgs, 'danger');
                            if (saveStatus) saveStatus.textContent = '';
                        } else {
                            resumeSaved = true;
                            if (saveStatus) {
                                saveStatus.innerHTML = '<i class="bi bi-check-circle text-success"></i> Disimpan ' +
                                    (data.saved_at ? data.saved_at : '');
                            }
                            markSaved(data.saved_at ? data.saved_at : '');
                            unlockUpload();
                            showToast('Resume berhasil disimpan (' + data.bab_count + ' BAB). Silakan upload file.', 'success');
                        }
                    })
                    .catch(function() {
                        showToast('Koneksi gagal. Coba lagi.', 'danger');
                    })
                    .finally(function() {
                        if (btnText) btnText.classList.remove('d-none');
                        if (btnSpinner) btnSpinner.classList.add('d-none');
                        btnSave.disabled = false;
                    });
            });
        }

        // ── Guard form upload ─────────────────────────────────────
        var formUpload = document.getElementById('formUploadDokumen');
        if (formUpload) {
            formUpload.addEventListener('submit', function(e) {
                if (!resumeSaved) {
                    e.preventDefault();
                    showToast('Simpan resume asesmen terlebih dahulu.', 'warning');
                }
            });
        }

        // ── Toast ─────────────────────────────────────────────────
        function showToast(msg, type) {
            if (!type) type = 'info';
            var map = {
                success: 'text-bg-success'
                , danger: 'text-bg-danger'
                , warning: 'text-bg-warning text-dark'
                , info: 'text-bg-primary'
            , };
            var cls = map[type] ? map[type] : 'text-bg-primary';
            var id = '_t' + Date.now();

            var wrap = document.getElementById('_toast_wrap');
            if (!wrap) {
                wrap = document.createElement('div');
                wrap.id = '_toast_wrap';
                wrap.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;min-width:280px;';
                document.body.appendChild(wrap);
            }

            wrap.insertAdjacentHTML('beforeend'
                , '<div id="' + id + '" class="toast align-items-center ' + cls +
                ' border-0 show mb-2" role="alert" style="min-width:280px;">' +
                '<div class="d-flex">' +
                '<div class="toast-body small">' + msg + '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto"' +
                ' onclick="this.closest(\'.toast\').remove()"></button>' +
                '</div></div>');

            setTimeout(function() {
                var el = document.getElementById(id);
                if (el) el.remove();
            }, 4500);
        }

    }());

</script>
@endpush
