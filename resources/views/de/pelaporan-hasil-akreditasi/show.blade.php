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
    <div class="d-flex justify-content-between align-items-center mb-4">
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
                    @if($canUpload && (!$hasLaporan && !$hasSertifikat))
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="bi bi-upload"></i> Upload Dokumen (Laporan & Sertifikat)</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('de.pelaporan-hasil-akreditasi.upload-dokumen', $pengajuan->id) }}" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small text-muted">File Laporan Hasil</label>
                                        <input type="file" name="file_laporan" class="form-control form-control-sm" accept=".pdf,.doc,.docx">
                                        <small class="text-muted">PDF, DOC, DOCX (Max: 10MB)</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small text-muted">File Sertifikat</label>
                                        <input type="file" name="file_sertifikat" class="form-control form-control-sm" accept=".pdf">
                                        <small class="text-muted">PDF (Max: 5MB)</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small text-muted">Keterangan (opsional)</label>
                                        <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Keterangan..."></textarea>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small text-muted">Masa berlaku (tahun)</label>
                                        <input type="number" name="masa_berlaku_tahun" value="{{ $hasil->statusFinal->siklus_tahun }}" class="form-control form-control-sm" placeholder="Masa berlaku (tahun)" min="1" max="10">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-secondary btn-sm w-100">
                                    <i class="bi bi-upload"></i> Upload Dokumen
                                </button>
                            </form>
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
