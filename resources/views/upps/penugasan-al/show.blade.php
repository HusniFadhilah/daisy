{{-- resources/views/upps/penugasan-al/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Penugasan Asesor AL')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penugasan-al') }}">Penugasan Asesor AL</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-person-check"></i> Detail Penugasan Asesor AL
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.penugasan-al') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
            \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            ]; // ini contoh, bisa dinamis dari config/db/request

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Penugasan Asesor AL</strong><br>
                Sekretariat sedang menugaskan asesor untuk melakukan penilaian AL
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Penugasan Asesor AL</strong><br>
                Sekretariat telah menugaskan asesor untuk melakukan penilaian AL
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Penugasan Asesor AL</strong><br>
                Sekretariat telah menugaskan asesor untuk melakukan penilaian AL
            </div>
            @endif

            <!-- Informasi Penugasan -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Penugasan Asesor AL
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Tanggal Penugasan Asesor AL</th>
                            <td>
                                : {{ $pengajuan->tanggal_penugasan_asesor_al
                                    ? $pengajuan->tanggal_penugasan_asesor_al->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Mulai AL</th>
                            <td>
                                : {{ $pengajuan->asesmen?->asesmenLapangan?->tanggal_mulai
                                    ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenLapangan->tanggal_mulai)->format('d M Y')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal AL Selesai</th>
                            <td>
                                : {{ $pengajuan->asesmen?->asesmenLapangan?->tanggal_selesai
                                    ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenLapangan->tanggal_selesai)->format('d M Y')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Penugasan Asesor AK</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('penugasan_asesor_al', 'upps', 'label_long_for') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- ✅ Surat Tugas Section --}}
            @php
            $suratTugasAsesor = $pengajuan->dokumen
            ->where('jenis_dokumen', 'surat_tugas_asesor_al')
            ->where('is_latest', true)
            ->first();

            $suratTugasValidator = $pengajuan->dokumen
            ->where('jenis_dokumen', 'surat_tugas_validator_al')
            ->where('is_latest', true)
            ->first();
            @endphp

            {{-- Surat Tugas Asesor AL --}}
            @if($suratTugasAsesor)
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> Surat Tugas Asesor AL
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $suratTugasAsesor->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    @if($suratTugasAsesor->file_size)
                                    {{ number_format($suratTugasAsesor->file_size / 1024, 2) }} KB
                                    @endif
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> Dibuat: {{ $suratTugasAsesor->created_at->format('d M Y H:i') }}
                                </small>
                            </div>
                        </div>
                        <div>
                            @if($suratTugasAsesor->path_file)
                            <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $suratTugasAsesor->id) }}" class="btn btn-success btn-md" target="_blank">
                                <i class="bi bi-download"></i> Download
                            </a>
                            @elseif($suratTugasAsesor->template_link)
                            <a href="{{ $suratTugasAsesor->template_link }}" class="btn btn-success btn-md" target="_blank">
                                <i class="bi bi-box-arrow-up-right"></i> Buka Link
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ✅ Info jika belum ada surat tugas --}}
            @if(!$suratTugasAsesor && !$suratTugasValidator && $pengajuan->asesmen && ($pengajuan->asesmen->asesorAL->count() > 0 || $pengajuan->asesmen->validatorAL->count() > 0))
            <div class="alert alert-warning alert-permanent mb-4">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Surat Tugas Belum Tersedia</strong>
                <br>
                <small>Surat tugas penugasan asesor/validator sedang dalam proses pembuatan oleh LAMDEPILAR.</small>
            </div>
            @endif

            <!-- Informasi Asesor -->
            @if($pengajuan->asesmen && $pengajuan->asesmen->asesorAL->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge"></i> Informasi Asesor
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3">
                        @foreach($pengajuan->asesmen->asesorAL as $asesor)
                        <li class="mb-3">
                            <strong>{{ $asesor->user->name }}</strong>
                            @if($asesor->urutan_asesor)
                            <span class="badge bg-secondary">#{{ $asesor->urutan_asesor }}</span>
                            @endif
                            <br>

                            @if($asesor->user->email)
                            <small class="text-muted">{{ $asesor->user->email }}</small><br>
                            @endif
                            @if($asesor->user->phone)
                            <small class="text-muted">{{ $asesor->user->phone }}</small><br>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Timeline Penugasan -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                    ];

                    $logs = $pengajuan->statusLog
                    ->whereIn('status_to', $filterStatuses)
                    ->sortBy('changed_at');
                    @endphp

                    @if($logs->count() > 0)
                    <div class="timeline">
                        @foreach($logs as $log)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    @php
                                    $iconColor = match($log->status_to) {
                                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                                    => 'text-success',
                                    \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN
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
                    <p class="text-muted text-center mb-0">Belum ada riwayat penugasan</p>
                    @endif
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Asesmen Lapangan
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Tahapan Asesmen Lapangan:</strong>
                    </p>
                    <ol class="small mb-0 ps-3">
                        <li>Hasil Asesmen Kecukupan dilaporkan</li>
                        <li>LAMDEPILAR menugaskan asesor AL</li>
                        <li>Surat tugas diterbitkan dan dikirim</li>
                        <li>Asesor memulai proses asesmen lapangan</li>
                        <li>Asesor melakukan kunjungan ke program studi</li>
                        <li>Asesor melakukan penilaian komprehensif</li>
                        <li>Hasil asesmen divalidasi oleh tim</li>
                        <li>Hasil asesmen lapangan dilaporkan</li>
                        <li>Proses dilanjutkan ke tahap berikutnya</li>
                    </ol>

                    <hr>

                    <p class="small mb-2">
                        <strong>Catatan:</strong>
                    </p>
                    <p class="small text-muted mb-0">
                        Asesmen Lapangan adalah tahap penilaian komprehensif dengan kunjungan langsung asesor ke program studi untuk memverifikasi dokumen dan kondisi aktual program studi.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
