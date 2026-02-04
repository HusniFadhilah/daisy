{{-- resources/views/upps/pelaporan-al/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pelaporan AL')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pelaporan-al') }}">Pelaporan AL</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-file-earmark-check"></i> Detail Pelaporan Asesmen Lapangan
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.pelaporan-al') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
            ]; // ini contoh, bisa dinamis dari config/db/request

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Proses Pelaporan AL</strong><br>
                Permohonan akreditasi program studi memasuki tahap pelaporan AL
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Proses Pelaporan AL</strong><br>
                Permohonan akreditasi program studi memasuki tahap pelaporan AL
            </div>
            @endif

            <!-- Informasi Pelaporan -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pelaporan AL
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
                            <th>Jenis Permohonan</th>
                            <td>: {{ $pengajuan->jenis_akreditasi_label }}</td>
                        </tr>
                        <tr>
                            <th>Tahun Akreditasi</th>
                            <td>: {{ $pengajuan->tahun_akreditasi }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Mulai AL</th>
                            <td>
                                : {{ $pengajuan->tanggal_al_mulai
                                    ? $pengajuan->tanggal_al_mulai->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal AL Selesai</th>
                            <td>
                                : {{ $pengajuan->tanggal_al_selesai
                                    ? $pengajuan->tanggal_al_selesai->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pelaporan AL</th>
                            <td>
                                : {{ $pengajuan->tanggal_al_dilaporkan
                                    ? $pengajuan->tanggal_al_dilaporkan->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Durasi Asesmen</th>
                            <td>
                                : @if($pengajuan->tanggal_al_mulai && $pengajuan->tanggal_al_selesai)
                                {{ $pengajuan->tanggal_al_mulai->diffInDays($pengajuan->tanggal_al_selesai) }} hari
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status Pelaporan AL</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('pelaporan_al', 'upps', 'label_long_for') !!}</td>
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
                            <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $suratTugasAL->id) }}" class="btn btn-warning btn-md">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Berita Acara yang Disetujui -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-check-fill"></i> Berita Acara yang Disetujui
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $beritaAcaraApproved = $pengajuan->asesmen->beritaAcaraAL
                    ->where('status_persetujuan_prodi', 'approved') ?? collect([]);
                    @endphp

                    @if($beritaAcaraApproved->count() > 0)
                    <div class="alert alert-success mb-3">
                        <i class="bi bi-check-circle"></i>
                        <strong>Total {{ $beritaAcaraApproved->count() }} berita acara</strong> telah disetujui oleh program studi.
                    </div>

                    @foreach($beritaAcaraApproved as $index => $beritaAcara)
                    <div class="card mb-3 border-success">
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
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Disetujui
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

                                    <hr>
                                    <small class="text-success">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Disetujui pada: {{ $beritaAcara->approved_at_prodi->format('d M Y H:i') }}
                                        @if($beritaAcara->prodiApprover)
                                        oleh {{ $beritaAcara->prodiApprover->name }}
                                        @endif
                                    </small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <a href="{{ route('al.berkas.documents.download', ['id' => $pengajuan->id_asesmen, 'docId' => $beritaAcara->id]) }}" class="btn btn-success btn-md w-100">
                                        <i class="bi bi-file-earmark-pdf"></i> Lihat File
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada berita acara yang disetujui</p>
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
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                    \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
                    \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
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
                                    \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
                                    \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN
                                    => 'text-success',
                                    \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
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
                    <p class="text-muted text-center mb-0">Belum ada riwayat pelaporan</p>
                    @endif
                </div>
            </div>

            <!-- Ringkasan Asesmen -->
            @if($pengajuan->asesmen->asesorAL)
            <div class="card mt-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-clipboard-data"></i> Ringkasan Asesmen
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Asesor</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->asesmen->asesorAL()->name }}</p>
                        <small class="text-muted">{{ $pengajuan->asesmen->asesorAL()->email }}</small>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small">Durasi Pelaksanaan</label>
                        <p class="fw-bold mb-0">
                            @if($pengajuan->tanggal_al_mulai && $pengajuan->tanggal_al_selesai)
                            {{ $pengajuan->tanggal_al_mulai->diffInDays($pengajuan->tanggal_al_selesai) }} hari
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small">Total Berita Acara Disetujui</label>
                        <p class="fw-bold mb-0">
                            {{ $beritaAcaraApproved->count() }} dokumen
                        </p>
                    </div>

                    <hr>

                    <div>
                        <label class="text-muted small">Status Pelaporan AL</label>
                        <p class="mb-0">
                            {!! $pengajuan->getCustomBadgeLastStatus('pelaporan_al', 'upps', 'label_long_for') !!}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Info Card -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pelaporan
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Proses Pelaporan AL:</strong>
                    </p>
                    <ol class="small mb-0 ps-3">
                        <li>Asesor melakukan asesmen lapangan</li>
                        <li>Asesor mengupload berita acara</li>
                        <li>Program studi meninjau dan menyetujui</li>
                        <li>Hasil asesmen divalidasi LAMDEPILAR</li>
                        <li>Hasil asesmen dilaporkan</li>
                        <li>Proses dilanjutkan ke tahap berikutnya</li>
                    </ol>

                    <hr>

                    <p class="small mb-2">
                        <strong>Catatan:</strong>
                    </p>
                    <p class="small text-muted mb-0">
                        Halaman ini menampilkan hasil pelaporan Asesmen Lapangan yang telah selesai dilakukan.
                        Berita acara yang ditampilkan adalah yang telah disetujui oleh program studi.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
