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
                <i class="bi bi-megaphone"></i> Detail Pelaporan Hasil Akreditasi
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('de.pelaporan-hasil-akreditasi') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @php
    // Peringkat akhir: prioritas hasil banding jika ada
    $hasil = $pengajuan->asesmen->hasil ?? null;
    @endphp

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Success Alert -->
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
            \App\Models\PengajuanAkreditasi::STATUS_SELESAI,
            ]; // ini contoh, bisa dinamis dari config/db/request

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN)
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
                        <i class="bi bi-file-earmark-text"></i> Laporan Hasil Akreditasi dan Sertifikat Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $dokumenHasil = $pengajuan->dokumen
                    ->whereIn('jenis_dokumen', ['sertifikat','laporan_hasil'])
                    ->where('is_latest', true);
                    @endphp

                    @if($dokumenHasil->count() > 0)
                    @foreach($dokumenHasil as $dokumen)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded mb-2">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-dark me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $dokumen->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($dokumen->file_size / 1024, 2) }} KB •
                                    Diupload: {{ $dokumen->created_at->format('d M Y H:i') }}
                                </small>
                                <br>
                                @if($dokumen->jenis_dokumen === 'sertifikat')
                                <small class="text-dark">Sertifikat Akreditasi</small>
                                @elseif($dokumen->jenis_dokumen === 'sk_akreditasi')
                                <small class="text-dark">SK Akreditasi</small>
                                @elseif($dokumen->jenis_dokumen === 'sk_penetapan')
                                <small class="text-dark">SK Penetapan</small>
                                @elseif($dokumen->jenis_dokumen === 'laporan_hasil')
                                <small class="text-dark">Laporan Hasil</small>
                                @endif
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $dokumen->id) }}" class="btn btn-outline-dark btn-md">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                            </a>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Dokumen hasil & laporan belum tersedia</p>
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
                            <label class="text-muted small">Peringkat Akreditasi</label><br>
                            <span class="badge p-2 px-3 my-2 fs-6" style="background-color: {{ $hasil->getPeringkatColor($peringkat) }}; color:#222">
                                {{ $peringkat }}
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tanggal Penetapan</label>
                            <p class="mb-0">
                                {{ $pengajuan->tanggal_penetapan
                                    ? $pengajuan->tanggal_penetapan->format('d M Y H:i')
                                    : '-' }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tanggal Pelaporan</label>
                            <p class="mb-0">
                                {{ $pengajuan->tanggal_pelaporan_hasil
                                    ? $pengajuan->tanggal_pelaporan_hasil->format('d M Y H:i')
                                    : '-' }}
                            </p>
                        </div>
                        @if($pengajuan->tanggal_surat_permohonan_dikirim && $pengajuan->tanggal_pelaporan_hasil)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Total Durasi Proses</label>
                            <p class="mb-0">
                                {{ max(1,$pengajuan->tanggal_surat_permohonan_dikirim->diffInDays($pengajuan->tanggal_pelaporan_hasil)) }} hari
                                <small class="text-muted">(dari permohonan akreditasi hingga pelaporan hasil)</small>
                            </p>
                        </div>
                        @endif
                    </div>

                    @if($pengajuan->peringkat_hasil_banding)
                    <hr>
                    <div class="alert alert-info border border-info mb-0">
                        <i class="bi bi-info-circle-fill"></i>
                        <strong>Catatan:</strong> Hasil yang ditampilkan adalah hasil akhir setelah proses banding.
                    </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ringkasan Hasil Akhir -->

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
                            <strong>{{ $pengajuan->tanggal_pengajuan->format('d M Y') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Selesai:</span>
                            <strong>{{ $pengajuan->tanggal_pelaporan_hasil->format('d M Y') }}</strong>
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
                    //\App\Models\PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                    //\App\Models\PengajuanAkreditasi::STATUS_SELESAI,
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
                                    <small class="text-muted">{{ $log->created_at->format('d M Y H:i') }}</small>

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
@endsection