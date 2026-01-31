{{-- resources/views/upps/pelaporan-dokumen/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pelaporan Dokumen')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pelaporan-dokumen') }}">Pelaporan Dokumen</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-file-earmark-check"></i> Detail Pelaporan Dokumen
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.pelaporan-dokumen') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Dokumen telah divalidasi</strong>
                <br>
                Menunggu pelaporan hasil validasi oleh LAMDEPILAR ke tahap berikutnya
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-send-check"></i>
                <strong>Hasil validasi telah dilaporkan</strong>
                <br>
                Dokumen telah dilaporkan dan menunggu penugasan asesor untuk tahap Asesmen Kecukupan
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Asesor telah ditugaskan</strong>
                <br>
                Proses dilanjutkan ke tahap Asesmen Kecukupan dengan asesor yang telah ditugaskan
            </div>
            @elseif(in_array($pengajuan->status, [
            \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION
            ]))
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-clock-history"></i>
                <strong>Asesmen Kecukupan sedang berlangsung</strong>
                <br>
                Tim asesor sedang melakukan penilaian kecukupan dokumen akreditasi
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Asesmen Kecukupan selesai</strong>
                <br>
                Penilaian kecukupan dokumen telah selesai dilakukan
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-patch-check"></i>
                <strong>Hasil Asesmen Kecukupan telah dilaporkan</strong>
                <br>
                Proses Asesmen Kecukupan selesai dan hasil telah dilaporkan
            </div>
            @endif

            <!-- Informasi Pelaporan -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pelaporan Dokumen
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
                            <th>Validator</th>
                            <td>
                                @if($pengajuan->validator)
                                : {{ $pengajuan->validator->name }}
                                <br>
                                <small class="text-muted">{{ $pengajuan->validator->email }}</small>
                                @else
                                : <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Validasi Selesai</th>
                            <td>
                                : {{ $pengajuan->tanggal_validasi_borang_selesai
                                    ? $pengajuan->tanggal_validasi_borang_selesai->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Dilaporkan</th>
                            <td>
                                : {{ $pengajuan->tanggal_validasi_borang_dilaporkan
                                    ? $pengajuan->tanggal_validasi_borang_dilaporkan->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Pelaporan</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('borang_final', 'upps') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Hasil Validasi -->
            @if($pengajuan->borangValidation)
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check"></i> Hasil Validasi Dokumen
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Status Validasi</label>
                            <p class="fw-bold mb-0">
                                <span class="badge bg-success">TERVALIDASI</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Tanggal Validasi</label>
                            <p class="fw-bold mb-0">
                                {{ $pengajuan->tanggal_validasi_borang_selesai
                                        ? $pengajuan->tanggal_validasi_borang_selesai->format('d M Y H:i')
                                        : '-' }}
                            </p>
                        </div>
                    </div>

                    @if($pengajuan->borangValidation->catatan_validator)
                    <hr>
                    <label class="text-muted small">Catatan Validator</label>
                    <div class="alert alert-light border mb-0">
                        <p class="mb-0">{{ $pengajuan->borangValidation->catatan_validator }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Dokumen yang Dilaporkan -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-pdf"></i> Dokumen yang Dilaporkan
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
                        <p class="text-muted mt-2 mb-0">Tidak ada dokumen yang dilaporkan</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Timeline Pelaporan -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Timeline Pelaporan
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
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
                                    \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                                    \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
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
                    <p class="text-muted text-center mb-0">Belum ada riwayat pelaporan</p>
                    @endif
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Proses Pelaporan Dokumen:</strong>
                    </p>
                    <ol class="small mb-0 ps-3">
                        <li>Dokumen selesai divalidasi oleh validator</li>
                        <li>LAMDEPILAR melaporkan hasil validasi</li>
                        <li>Sistem menugaskan asesor untuk Asesmen Kecukupan</li>
                        <li>Asesor melakukan penilaian kecukupan dokumen</li>
                        <li>Hasil asesmen kecukupan dilaporkan</li>
                        <li>Proses dilanjutkan ke tahap berikutnya</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
