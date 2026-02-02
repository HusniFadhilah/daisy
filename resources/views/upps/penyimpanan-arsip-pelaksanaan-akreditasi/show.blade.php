{{-- resources/views/upps/penyimpanan-arsip-akreditasi/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Arsip Akreditasi')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penyimpanan-arsip-akreditasi') }}">Penyimpanan Arsip</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-archive"></i> Detail Arsip Akreditasi
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.penyimpanan-arsip-akreditasi') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @php
    // Peringkat akhir: prioritas hasil banding jika ada
    $peringkatAkhir = $pengajuan->peringkat_hasil_banding ?? $pengajuan->peringkat_hasil;
    $nilaiAkhir = $pengajuan->nilai_akhir_banding ?? $pengajuan->nilai_akhir;

    $badgeClass = match($peringkatAkhir) {
    'Unggul' => 'bg-warning text-dark',
    'Baik Sekali' => 'bg-success',
    'Baik' => 'bg-info',
    'Tidak Terakreditasi' => 'bg-danger',
    default => 'bg-secondary',
    };
    @endphp

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Success Alert -->
            <div class="alert alert-success alert-permanent border-start border-4 border-success">
                <div class="d-flex align-items-start">
                    <i class="bi bi-archive-fill fs-1 me-3 text-success"></i>
                    <div class="flex-grow-1">
                        <h5 class="mb-2 fw-bold">
                            <i class="bi bi-check-circle-fill"></i> Arsip Akreditasi Tersimpan
                        </h5>
                        <p class="mb-2">
                            Seluruh dokumen pelaksanaan akreditasi telah disimpan dengan lengkap pada
                            <strong>{{ $pengajuan->tanggal_penyimpanan ? $pengajuan->tanggal_penyimpanan->format('d M Y H:i') : '-' }}</strong>.
                        </p>
                        <div class="alert alert-light border border-success mb-0">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Peringkat Akhir:</strong>
                                    <div class="mt-1">
                                        <span class="badge {{ $badgeClass }} fs-6">
                                            @if($peringkatAkhir === 'Unggul')
                                            <i class="bi bi-star-fill"></i>
                                            @elseif($peringkatAkhir === 'Baik Sekali')
                                            <i class="bi bi-award-fill"></i>
                                            @elseif($peringkatAkhir === 'Baik')
                                            <i class="bi bi-check-circle-fill"></i>
                                            @endif
                                            {{ $peringkatAkhir ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <strong>Nilai Akhir:</strong>
                                    <div class="fs-4 fw-bold text-success mt-1">{{ $nilaiAkhir ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                            <th width="30%">Nomor Permohonan</th>
                            <td>: {{ $pengajuan->nomor_pengajuan }}</td>
                        </tr>
                        <tr>
                            <th>Program Studi</th>
                            <td>: {{ $pengajuan->studyProgram->full_name }}</td>
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
                            <th>Tanggal Pengajuan</th>
                            <td>
                                : {{ $pengajuan->tanggal_pengajuan
                                    ? $pengajuan->tanggal_pengajuan->format('d M Y')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Penyimpanan</th>
                            <td>
                                : {{ $pengajuan->tanggal_penyimpanan
                                    ? $pengajuan->tanggal_penyimpanan->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                : <span class="badge {{ $pengajuan->status_badge_class }}">
                                    {{ $pengajuan->status_label }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Arsip Dokumen (Grouped) -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-folder-fill"></i> Arsip Dokumen Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    @if($groupedDokumen)
                    @foreach($groupedDokumen as $kategori => $dokumenList)
                    @if($dokumenList->count() > 0)
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-folder2-open"></i> {{ $kategori }}
                            <span class="badge bg-primary ms-2">{{ $dokumenList->count() }}</span>
                        </h6>

                        @foreach($dokumenList as $dokumen)
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                                <div>
                                    <strong>{{ $dokumen->original_filename }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ number_format($dokumen->file_size / 1024, 2) }} KB •
                                        Diupload: {{ $dokumen->created_at->format('d M Y H:i') }}
                                    </small>
                                    <br>
                                    <span class="badge bg-info">{{ $dokumen->jenis_dokumen_alias }}</span>
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $dokumen->id) }}" class="btn btn-success btn-md">
                                    <i class="bi bi-file-earmark-pdf"></i> Lihat File
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <hr>
                    @endif
                    @endforeach
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-folder-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada dokumen terarsip</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Statistik Proses -->
            @if($statistics)
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up"></i> Statistik Proses Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(isset($statistics['total_durasi']))
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-clock-history text-primary" style="font-size: 48px;"></i>
                                    <h3 class="fw-bold text-primary mt-2">{{ $statistics['total_durasi'] }}</h3>
                                    <p class="text-muted mb-0">Hari Total Proses</p>
                                    <small class="text-muted">Dari pengajuan hingga penyimpanan</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(isset($statistics['durasi_hingga_penetapan']))
                        <div class="col-md-6 mb-3">
                            <div class="card border-success h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-award text-success" style="font-size: 48px;"></i>
                                    <h3 class="fw-bold text-success mt-2">{{ $statistics['durasi_hingga_penetapan'] }}</h3>
                                    <p class="text-muted mb-0">Hari Hingga Penetapan</p>
                                    <small class="text-muted">Dari pengajuan hingga penetapan</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(isset($statistics['durasi_validasi_dokumen']))
                        <div class="col-md-6 mb-3">
                            <div class="card border-warning h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-file-check text-warning" style="font-size: 48px;"></i>
                                    <h3 class="fw-bold text-warning mt-2">{{ $statistics['durasi_validasi_dokumen'] }}</h3>
                                    <p class="text-muted mb-0">Hari Validasi Dokumen</p>
                                    <small class="text-muted">Validasi Dokumen</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(isset($statistics['durasi_ak']))
                        <div class="col-md-6 mb-3">
                            <div class="card border-info h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-clipboard-check text-info" style="font-size: 48px;"></i>
                                    <h3 class="fw-bold text-info mt-2">{{ $statistics['durasi_ak'] }}</h3>
                                    <p class="text-muted mb-0">Hari Asesmen Kecukupan</p>
                                    <small class="text-muted">Proses AK</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(isset($statistics['durasi_al']))
                        <div class="col-md-6 mb-3">
                            <div class="card border-secondary h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-pin-map text-secondary" style="font-size: 48px;"></i>
                                    <h3 class="fw-bold text-secondary mt-2">{{ $statistics['durasi_al'] }}</h3>
                                    <p class="text-muted mb-0">Hari Asesmen Lapangan</p>
                                    <small class="text-muted">Proses AL</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(isset($statistics['total_dokumen']))
                        <div class="col-md-6 mb-3">
                            <div class="card border-success h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-files text-success" style="font-size: 48px;"></i>
                                    <h3 class="fw-bold text-success mt-2">{{ $statistics['total_dokumen'] }}</h3>
                                    <p class="text-muted mb-0">Dokumen Terarsip</p>
                                    <small class="text-muted">File terbaru</small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ringkasan Hasil Akhir -->
            <div class="card mb-4 border-{{ $badgeClass === 'bg-warning text-dark' ? 'warning' : ($badgeClass === 'bg-success' ? 'success' : 'info') }}">
                <div class="card-header {{ $badgeClass }}">
                    <h6 class="mb-0">
                        <i class="bi bi-award"></i> Ringkasan Hasil Akhir
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-trophy-fill text-{{ $badgeClass === 'bg-warning text-dark' ? 'warning' : ($badgeClass === 'bg-success' ? 'success' : 'info') }}" style="font-size: 64px;"></i>
                    </div>
                    <h3 class="fw-bold mb-2">{{ $peringkatAkhir ?? '-' }}</h3>
                    @if($nilaiAkhir)
                    <p class="mb-3">
                        <span class="text-muted">Nilai Akhir:</span>
                        <br>
                        <span class="fs-3 fw-bold">{{ $nilaiAkhir }}</span>
                    </p>
                    @endif
                    <hr>
                    <div class="mb-2">
                        <small class="text-muted">Tanggal Penetapan:</small>
                        <p class="mb-0 fw-bold">
                            {{ $pengajuan->tanggal_penetapan ? $pengajuan->tanggal_penetapan->format('d M Y') : '-' }}
                        </p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Tanggal Arsip:</small>
                        <p class="mb-0 fw-bold">
                            {{ $pengajuan->tanggal_penyimpanan ? $pengajuan->tanggal_penyimpanan->format('d M Y') : '-' }}
                        </p>
                    </div>

                    @if($pengajuan->masa_berlaku_tahun)
                    <hr>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-calendar-check"></i>
                        <strong>Masa Berlaku:</strong>
                        <br>
                        {{ $pengajuan->masa_berlaku_tahun }} tahun
                    </div>
                    @endif
                </div>
            </div>

            <!-- Timeline Lengkap -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Timeline Lengkap
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                    $logs = $pengajuan->statusLog->sortByDesc('changed_at');
                    @endphp

                    @if($logs->count() > 0)
                    <div class="timeline">
                        @foreach($logs as $log)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    @php
                                    $iconColor = match($log->status_to) {
                                    \App\Models\PengajuanAkreditasi::STATUS_SELESAI,
                                    \App\Models\PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                                    \App\Models\PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                                    \App\Models\PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                                    \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN
                                    => 'text-success',
                                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                                    \App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH
                                    => 'text-warning',
                                    default => 'text-info',
                                    };
                                    @endphp
                                    <i class="bi bi-circle-fill {{ $iconColor }}" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label'] ?? $log->status_to }}
                                    </strong>
                                    <br>
                                    <small class="text-muted">{{ $log->changed_at->format('d M Y H:i') }}</small>

                                    @if($log->keterangan)
                                    <br>
                                    <small class="text-muted fst-italic">{{ $log->keterangan }}</small>
                                    @endif
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

            <!-- Info Card -->
            <div class="card mt-4 border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Penyimpanan
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Dokumen yang Disimpan:</strong>
                    </p>
                    <ul class="small mb-3 ps-3 text-muted">
                        <li>Laporan Evaluasi Diri (LED)</li>
                        <li>Laporan Kesiapan LED (LKLED)</li>
                        <li>Laporan Hasil Kecukupan (LHK)</li>
                        <li>Laporan Hasil Asesmen (LHA)</li>
                        <li>Surat Keterangan Hasil Akreditasi</li>
                        <li>Sertifikat Akreditasi</li>
                        <li>SK Penetapan</li>
                        <li>Dokumen Pendukung Lainnya</li>
                    </ul>

                    <hr>

                    <p class="small mb-2">
                        <strong>Masa Penyimpanan:</strong>
                    </p>
                    <p class="small text-muted mb-3">
                        Arsip disimpan selama 2 siklus masa berlaku akreditasi sesuai
                        dengan ketentuan LAMDEPILAR.
                    </p>

                    <div class="alert alert-success mb-0">
                        <i class="bi bi-shield-check"></i>
                        <strong>Status:</strong> Arsip tersimpan dengan aman dan dapat diakses kapan saja
                        untuk keperluan audit, verifikasi, atau dokumentasi.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
