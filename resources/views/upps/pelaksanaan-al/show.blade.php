{{-- resources/views/upps/pelaksanaan-al/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pelaksanaan AL')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pelaksanaan-al') }}">Pelaksanaan AL</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-clipboard-check"></i> Detail Pelaksanaan Asesmen Lapangan
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.pelaksanaan-al') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS)
                <div class="alert alert-warning alert-permanent">
                    <i class="bi bi-clock-history"></i>
                    <strong>Asesmen Lapangan sedang berlangsung</strong>
                    <br>
                    Asesor sedang melakukan asesmen lapangan ke program studi
                </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_ON_VALIDATION)
                <div class="alert alert-info alert-permanent">
                    <i class="bi bi-hourglass-split"></i>
                    <strong>Hasil Asesmen Lapangan dalam validasi</strong>
                    <br>
                    Hasil penilaian asesor sedang divalidasi oleh tim LAMDEPILAR
                </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI)
                <div class="alert alert-success alert-permanent">
                    <i class="bi bi-check-circle"></i>
                    <strong>Asesmen Lapangan telah selesai</strong>
                    <br>
                    Proses asesmen lapangan telah selesai dilakukan
                </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN)
                <div class="alert alert-success alert-permanent">
                    <i class="bi bi-patch-check"></i>
                    <strong>Hasil Asesmen Lapangan telah dilaporkan</strong>
                    <br>
                    Hasil asesmen telah dilaporkan dan proses dilanjutkan ke tahap berikutnya
                </div>
            @endif

            <!-- Informasi Pelaksanaan -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pelaksanaan AL
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
                            <th>Asesor AL</th>
                            <td>
                                @if($pengajuan->asesorAL)
                                    : {{ $pengajuan->asesorAL->name }}
                                    <br>
                                    <small class="text-muted">{{ $pengajuan->asesorAL->email }}</small>
                                @else
                                    : <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Mulai Asesmen</th>
                            <td>
                                : {{ $pengajuan->tanggal_al_mulai
                                    ? $pengajuan->tanggal_al_mulai->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Asesmen Selesai</th>
                            <td>
                                : {{ $pengajuan->tanggal_al_selesai
                                    ? $pengajuan->tanggal_al_selesai->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Pelaksanaan</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('asesmen_lapangan', 'upps') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Surat Tugas Asesor AL -->
            @php
                $suratTugasAL = $pengajuan->dokumen
                    ->where('jenis_dokumen', 'surat_tugas_asesor_al')
                    ->where('is_latest', true)
                    ->first();
            @endphp

            @if($suratTugasAL)
                <div class="card mb-4 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-text"></i> Surat Tugas Asesor AL
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light border mb-3">
                            <i class="bi bi-info-circle"></i>
                            <strong>Informasi:</strong> Surat tugas resmi penugasan asesor untuk Asesmen Lapangan dari LAMDEPILAR.
                        </div>

                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                                <div>
                                    <strong>{{ $suratTugasAL->original_filename }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ number_format($suratTugasAL->file_size / 1024, 2) }} KB
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> Diupload: {{ $suratTugasAL->created_at->format('d M Y H:i') }}
                                    </small>
                                    <br>
                                    <span class="badge bg-warning text-dark">Surat Tugas Resmi</span>
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $suratTugasAL->id) }}"
                                   class="btn btn-warning btn-md">
                                    <i class="bi bi-file-earmark-pdf"></i> Lihat File
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Berita Acara Asesmen Lapangan -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-check"></i> Berita Acara Asesmen Lapangan
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $beritaAcaraList = $pengajuan->asesmen->beritaAcaraAL ?? collect([]);
                    @endphp

                    @if($beritaAcaraList->count() > 0)
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle"></i>
                            <strong>Informasi:</strong> Berikut adalah daftar berita acara yang telah diupload oleh asesor.
                            Silakan tinjau dan berikan persetujuan.
                        </div>

                        @foreach($beritaAcaraList as $index => $beritaAcara)
                            <div class="card mb-3 border-{{
                                $beritaAcara->status_persetujuan_prodi === 'approved' ? 'success' :
                                ($beritaAcara->status_persetujuan_prodi === 'rejected' ? 'danger' :
                                ($beritaAcara->status_persetujuan_prodi === 'revision_required' ? 'info' : 'warning'))
                            }}">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">
                                                <i class="bi bi-file-earmark-pdf text-danger"></i>
                                                <strong>{{ $beritaAcara->title }}</strong>
                                            </h6>
                                            <small class="text-muted">
                                                Diupload oleh: {{ $beritaAcara->uploader->name ?? '-' }} pada
                                                {{ $beritaAcara->uploaded_at ? $beritaAcara->uploaded_at->format('d M Y H:i') : '-' }}
                                            </small>
                                        </div>
                                        <div>
                                            <span class="badge {{ $beritaAcara->status_prodi_badge_class }}">
                                                {{ $beritaAcara->status_prodi_label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <p class="mb-2">
                                                <strong>File:</strong> {{ $beritaAcara->original_name }}
                                            </p>
                                            <p class="mb-2">
                                                <strong>Ukuran:</strong> {{ number_format($beritaAcara->size / 1024, 2) }} KB
                                            </p>

                                            @if($beritaAcara->catatan_prodi)
                                                <hr>
                                                <p class="mb-1"><strong>Catatan Program Studi:</strong></p>
                                                <div class="alert alert-light border mb-0">
                                                    {{ $beritaAcara->catatan_prodi }}
                                                </div>
                                            @endif

                                            @if($beritaAcara->approved_at_prodi)
                                                <hr>
                                                <small class="text-muted">
                                                    <i class="bi bi-clock"></i>
                                                    Ditindaklanjuti pada: {{ $beritaAcara->approved_at_prodi->format('d M Y H:i') }}
                                                    @if($beritaAcara->prodiApprover)
                                                        oleh {{ $beritaAcara->prodiApprover->name }}
                                                    @endif
                                                </small>
                                            @endif
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <a href="{{ route('al.berkas.documents.download', ['id' => $pengajuan->id_asesmen, 'docId' => $beritaAcara->id]) }}"
                                               class="btn btn-success btn-md mb-2 w-100">
                                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                                            </a>

                                            @if($beritaAcara->canBeRevised())
                                                <a href="{{ route('upps.pelaksanaan-al.berita-acara.approve.form', ['id' => $pengajuan->id, 'docId' => $beritaAcara->id]) }}"
                                                   class="btn btn-primary btn-md w-100">
                                                    <i class="bi bi-pencil-square"></i>
                                                    {{ $beritaAcara->status_persetujuan_prodi === 'revision_required' ? 'Tinjau Revisi' : 'Tinjau & Setujui' }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                            <p class="text-muted mt-2 mb-0">Belum ada berita acara yang diupload</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Timeline Pelaksanaan -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Timeline Pelaksanaan
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                        $filterStatuses = [
                            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                            \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                            \App\Models\PengajuanAkreditasi::STATUS_AL_ON_VALIDATION,
                            \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
                            \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
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
                                                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                                                    \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
                                                    \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN
                                                        => 'text-success',
                                                    \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                                                    \App\Models\PengajuanAkreditasi::STATUS_AL_ON_VALIDATION
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
                        <p class="text-muted text-center mb-0">Belum ada riwayat pelaksanaan</p>
                    @endif
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Berita Acara
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Status Persetujuan Berita Acara:</strong>
                    </p>
                    <ul class="small mb-3 ps-3">
                        <li><span class="badge bg-warning">Menunggu Persetujuan</span> - Berita acara menunggu peninjauan</li>
                        <li><span class="badge bg-success">Disetujui</span> - Berita acara telah disetujui</li>
                        <li><span class="badge bg-info">Perlu Revisi</span> - Berita acara perlu diperbaiki</li>
                        <li><span class="badge bg-danger">Ditolak</span> - Berita acara ditolak</li>
                    </ul>

                    <hr>

                    <p class="small mb-2">
                        <strong>Catatan:</strong>
                    </p>
                    <p class="small text-muted mb-0">
                        Berita acara yang sudah disetujui atau ditolak tidak dapat diubah lagi.
                        Jika berita acara perlu revisi, asesor akan melakukan perbaikan dan upload ulang.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
