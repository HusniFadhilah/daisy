{{-- resources/views/upps/penugasan-ak/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Penugasan Asesor AK')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penugasan-ak') }}">Penugasan Asesor AK</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-person-check"></i> Detail Penugasan Asesor AK
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.penugasan-ak') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-info-circle"></i>
                <strong>Hasil validasi telah dilaporkan</strong>
                <br>
                Menunggu penugasan asesor untuk tahap Asesmen Kecukupan oleh LAMDEPILAR
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Asesor Asesmen Kecukupan telah ditugaskan</strong>
                <br>
                Asesor: <strong>{{ $pengajuan->asesorAK->name ?? '-' }}</strong>
                <br>
                Menunggu asesor memulai proses asesmen kecukupan
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS)
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-clock-history"></i>
                <strong>Asesmen Kecukupan sedang berlangsung</strong>
                <br>
                Asesor sedang melakukan penilaian kecukupan terhadap dokumen akreditasi
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION)
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-hourglass-split"></i>
                <strong>Hasil Asesmen Kecukupan dalam validasi</strong>
                <br>
                Hasil penilaian asesor sedang divalidasi oleh tim LAMDEPILAR
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Asesmen Kecukupan telah selesai</strong>
                <br>
                Proses penilaian kecukupan dokumen telah selesai dilakukan
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-patch-check"></i>
                <strong>Hasil Asesmen Kecukupan telah dilaporkan</strong>
                <br>
                Hasil asesmen telah dilaporkan dan proses dilanjutkan ke tahap berikutnya
            </div>
            @endif

            <!-- Informasi Penugasan -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Penugasan Asesor AK
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
                            <th>Asesor AK Assigned</th>
                            <td>
                                @if($pengajuan->asesorAK)
                                : {{ $pengajuan->asesorAK->name }}
                                <br>
                                <small class="text-muted">{{ $pengajuan->asesorAK->email }}</small>
                                @else
                                : <span class="text-muted">Belum ditugaskan</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Penugasan Asesor</th>
                            <td>
                                : {{ $pengajuan->tanggal_penugasan_asesor_ak
                                    ? $pengajuan->tanggal_penugasan_asesor_ak->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Mulai Asesmen</th>
                            <td>
                                : {{ $pengajuan->tanggal_ak_mulai
                                    ? $pengajuan->tanggal_ak_mulai->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Asesmen Selesai</th>
                            <td>
                                : {{ $pengajuan->tanggal_ak_selesai
                                    ? $pengajuan->tanggal_ak_selesai->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Penugasan</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('asesmen_kecukupan', 'upps') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Surat Tugas Asesor AK -->
            @php
            $suratTugas = $pengajuan->dokumen
            ->where('jenis_dokumen', 'surat_tugas_asesor_ak')
            ->where('is_latest', true)
            ->first();
            @endphp

            @if($suratTugas)
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> Surat Tugas Asesor AK
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border mb-3">
                        <i class="bi bi-info-circle"></i>
                        <strong>Informasi:</strong> Surat tugas resmi penugasan asesor untuk Asesmen Kecukupan dari LAMDEPILAR.
                    </div>

                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $suratTugas->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($suratTugas->file_size / 1024, 2) }} KB
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> Diupload: {{ $suratTugas->created_at->format('d M Y H:i') }}
                                </small>
                                <br>
                                <span class="badge bg-warning text-dark">Surat Tugas Resmi</span>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $suratTugas->id) }}" class="btn btn-warning btn-md">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                            </a>
                        </div>
                    </div>

                    @if($suratTugas->keterangan)
                    <div class="alert alert-secondary mt-3 mb-0">
                        <small>
                            <strong>Catatan:</strong> {{ $suratTugas->keterangan }}
                        </small>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Informasi Asesor -->
            @if($pengajuan->asesorAK)
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge"></i> Informasi Asesor
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted small">Nama Asesor</label>
                            <p class="fw-bold mb-3">{{ $pengajuan->asesorAK->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Email</label>
                            <p class="fw-bold mb-3">{{ $pengajuan->asesorAK->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Tanggal Penugasan</label>
                            <p class="fw-bold mb-3">
                                {{ $pengajuan->tanggal_penugasan_asesor_ak
                                        ? $pengajuan->tanggal_penugasan_asesor_ak->format('d M Y H:i')
                                        : '-' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Status Asesmen</label>
                            <p class="mb-3">
                                @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED)
                                <span class="badge bg-info">Menunggu Dimulai</span>
                                @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS)
                                <span class="badge bg-warning">Sedang Berlangsung</span>
                                @elseif(in_array($pengajuan->status, [
                                \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
                                \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN
                                ]))
                                <span class="badge bg-success">Selesai</span>
                                @else
                                <span class="badge bg-secondary">-</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($suratTugas)
                    <hr>
                    <div class="text-center">
                        <p class="text-muted small mb-2">
                            <i class="bi bi-shield-check"></i> Asesor telah ditugaskan secara resmi melalui surat tugas
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Dokumen yang Dinilai -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-pdf"></i> Dokumen yang Dinilai
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $dokumenList = $pengajuan->dokumen
                    ->whereIn('jenis_dokumen', ['draft_borang', 'borang_final'])
                    ->where('is_latest', true);
                    @endphp

                    @if($dokumenList->count() > 0)
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
                                <span class="badge bg-success">Versi {{ $dokumen->versi }}</span>
                                @if($dokumen->jenis_dokumen === 'borang_final')
                                <span class="badge bg-primary">Final</span>
                                @else
                                <span class="badge bg-info">Draft</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $dokumen->id) }}" class="btn btn-success btn-md">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                            </a>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Tidak ada dokumen yang dinilai</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Timeline Penugasan -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Timeline Penugasan
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
                    ];

                    $logs = $pengajuan->statusLog
                    ->whereIn('status_to', $filterStatuses)
                    ->sortByDesc('changed_at');
                    @endphp

                    @if($logs->count() > 0)
                    <div class="timeline">
                        @foreach($logs as $log)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    @php
                                    $iconColor = match($log->status_to) {
                                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                                    \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
                                    \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN
                                    => 'text-success',
                                    \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                                    \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION
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
                    <p class="text-muted text-center mb-0">Belum ada riwayat penugasan</p>
                    @endif
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Asesmen Kecukupan
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Tahapan Asesmen Kecukupan:</strong>
                    </p>
                    <ol class="small mb-0 ps-3">
                        <li>Hasil validasi dokumen dilaporkan</li>
                        <li>LAMDEPILAR menugaskan asesor AK</li>
                        <li>Surat tugas diterbitkan dan dikirim</li>
                        <li>Asesor memulai proses asesmen kecukupan</li>
                        <li>Asesor melakukan penilaian kecukupan dokumen</li>
                        <li>Hasil asesmen divalidasi oleh tim</li>
                        <li>Hasil asesmen kecukupan dilaporkan</li>
                        <li>Proses dilanjutkan ke tahap berikutnya</li>
                    </ol>

                    <hr>

                    <p class="small mb-2">
                        <strong>Catatan:</strong>
                    </p>
                    <p class="small text-muted mb-0">
                        Asesmen Kecukupan adalah penilaian awal terhadap kelengkapan dan kesesuaian dokumen akreditasi sebelum dilanjutkan ke tahap asesmen lapangan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
