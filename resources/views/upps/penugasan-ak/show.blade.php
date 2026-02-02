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

                <div class="mt-2">Asesor:</div>
                <ul class="mb-1">
                    @forelse($pengajuan->asesmen->asesorAK as $asesor)
                    <li><strong>{{ $asesor->user->name }}</strong></li>
                    @empty
                    <li>-</li>
                    @endforelse
                </ul>

                <small>Mohon menunggu asesor memulai proses asesmen kecukupan</small>
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
                            <th>Jenis Permohonan</th>
                            <td>: {{ $pengajuan->jenis_akreditasi_label }}</td>
                        </tr>
                        <tr>
                            <th>Asesor AK yang Ditugaskan</th>
                            <td>
                                @if($pengajuan->asesmen && $pengajuan->asesmen->asesorAK->count() > 0)
                                <ul class="mb-0 ps-3">
                                    @foreach($pengajuan->asesmen->asesorAK as $asesor)
                                    <li>
                                        <strong>{{ $asesor->user->name }}</strong>
                                        @if($asesor->urutan_asesor)
                                        <span class="badge bg-secondary">#{{ $asesor->urutan_asesor }}</span>
                                        @endif
                                        @if($asesor->user->email)
                                        <br>
                                        <small class="text-muted">{{ $asesor->user->email }}</small>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                                @else
                                <span class="text-muted">Belum ditugaskan</span>
                                @endif
                            </td>
                        </tr>
                        {{-- ✅ NEW: Validator AK --}}
                        <tr>
                            <th>Validator AK yang Ditugaskan</th>
                            <td>
                                @if($pengajuan->asesmen && $pengajuan->asesmen->validatorAK->count() > 0)
                                <ul class="mb-0 ps-3">
                                    @foreach($pengajuan->asesmen->validatorAK as $validator)
                                    <li>
                                        <strong>{{ $validator->user->name }}</strong>
                                        @if($validator->user->email)
                                        <br>
                                        <small class="text-muted">{{ $validator->user->email }}</small>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                                @else
                                <span class="text-muted">Belum ditugaskan</span>
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
                                : {{ $pengajuan->asesmen?->asesmenKecukupan?->tanggal_mulai
                                    ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenKecukupan->tanggal_mulai)->format('d M Y')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Asesmen Selesai</th>
                            <td>
                                : {{ $pengajuan->asesmen?->asesmenKecukupan?->tanggal_selesai
                                    ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenKecukupan->tanggal_selesai)->format('d M Y')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Penugasan</th>
                            <td>: <span class="badge bg-info">{{ $pengajuan->status_label }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- ✅ Surat Tugas Section --}}
            @php
            $suratTugasAsesor = $pengajuan->dokumen
            ->where('jenis_dokumen', 'surat_tugas_asesor_ak')
            ->where('is_latest', true)
            ->first();

            $suratTugasValidator = $pengajuan->dokumen
            ->where('jenis_dokumen', 'surat_tugas_validator_ak')
            ->where('is_latest', true)
            ->first();
            @endphp

            {{-- Surat Tugas Asesor AK --}}
            @if($suratTugasAsesor)
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
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
                                <br>
                                <span class="badge bg-success">Surat Tugas Asesor</span>
                                <span class="badge bg-secondary">Versi {{ $suratTugasAsesor->versi }}</span>
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

                    @if($suratTugasAsesor->keterangan)
                    <div class="alert alert-secondary mt-3 mb-0">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            <strong>Catatan:</strong> {{ $suratTugasAsesor->keterangan }}
                        </small>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- ✅ NEW: Surat Tugas Validator AK --}}
            @if($suratTugasValidator)
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-check"></i> Surat Tugas Validator AK
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border mb-3">
                        <i class="bi bi-info-circle"></i>
                        <strong>Informasi:</strong> Surat tugas resmi penugasan validator untuk Asesmen Kecukupan dari LAMDEPILAR.
                    </div>

                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $suratTugasValidator->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    @if($suratTugasValidator->file_size)
                                    {{ number_format($suratTugasValidator->file_size / 1024, 2) }} KB
                                    @endif
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> Dibuat: {{ $suratTugasValidator->created_at->format('d M Y H:i') }}
                                </small>
                                <br>
                                <span class="badge bg-primary">Surat Tugas Validator</span>
                                <span class="badge bg-secondary">Versi {{ $suratTugasValidator->versi }}</span>
                            </div>
                        </div>
                        <div>
                            @if($suratTugasValidator->path_file)
                            <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $suratTugasValidator->id) }}" class="btn btn-primary btn-md" target="_blank">
                                <i class="bi bi-download"></i> Download
                            </a>
                            @elseif($suratTugasValidator->template_link)
                            <a href="{{ $suratTugasValidator->template_link }}" class="btn btn-primary btn-md" target="_blank">
                                <i class="bi bi-box-arrow-up-right"></i> Buka Link
                            </a>
                            @endif
                        </div>
                    </div>

                    @if($suratTugasValidator->keterangan)
                    <div class="alert alert-secondary mt-3 mb-0">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            <strong>Catatan:</strong> {{ $suratTugasValidator->keterangan }}
                        </small>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- ✅ Info jika belum ada surat tugas --}}
            @if(!$suratTugasAsesor && !$suratTugasValidator && $pengajuan->asesmen && ($pengajuan->asesmen->asesorAK->count() > 0 || $pengajuan->asesmen->validatorAK->count() > 0))
            <div class="alert alert-warning alert-permanent mb-4">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Surat Tugas Belum Tersedia</strong>
                <br>
                <small>Surat tugas penugasan asesor/validator sedang dalam proses pembuatan oleh LAMDEPILAR.</small>
            </div>
            @endif

            <!-- Informasi Asesor -->
            @if($pengajuan->asesmen && $pengajuan->asesmen->asesorAK->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge"></i> Informasi Asesor
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3">
                        @foreach($pengajuan->asesmen->asesorAK as $asesor)
                        <li class="mb-3">
                            <strong>{{ $asesor->user->name }}</strong>
                            @if($asesor->urutan_asesor)
                            <span class="badge bg-secondary">#{{ $asesor->urutan_asesor }}</span>
                            @endif
                            <br>

                            @if($asesor->user->email)
                            <small class="text-muted">{{ $asesor->user->email }}</small><br>
                            @endif

                            <small class="text-muted">
                                Status Penawaran:
                                <span class="badge bg-{{ $asesor->status_penawaran === 'accepted' ? 'success' : ($asesor->status_penawaran === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($asesor->status_penawaran) }}
                                </span>
                            </small>
                            <br>
                            <small class="text-muted">
                                Status Asesmen:
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
                            </small>
                        </li>
                        @endforeach
                    </ul>

                    @if($pengajuan->tanggal_penugasan_asesor_ak)
                    <hr>
                    <small class="text-muted">
                        <i class="bi bi-clock"></i>
                        Ditugaskan: {{ $pengajuan->tanggal_penugasan_asesor_ak->format('d M Y H:i') }}
                    </small>
                    @endif
                </div>
            </div>
            @endif

            {{-- ✅ NEW: Informasi Validator --}}
            @if($pengajuan->asesmen && $pengajuan->asesmen->validatorAK->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-check"></i> Informasi Validator
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3">
                        @foreach($pengajuan->asesmen->validatorAK as $validator)
                        <li class="mb-3">
                            <strong>{{ $validator->user->name }}</strong>
                            <br>

                            @if($validator->user->email)
                            <small class="text-muted">{{ $validator->user->email }}</small><br>
                            @endif

                            <small class="text-muted">
                                Status Penawaran:
                                <span class="badge bg-{{ $validator->status_penawaran === 'accepted' ? 'success' : ($validator->status_penawaran === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($validator->status_penawaran) }}
                                </span>
                            </small>
                            <br>
                            <small class="text-muted">
                                Status Validasi:
                                @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS)
                                <span class="badge bg-warning">Menunggu</span>
                                @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION)
                                <span class="badge bg-info">Sedang Validasi</span>
                                @elseif(in_array($pengajuan->status, [
                                \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
                                \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN
                                ]))
                                <span class="badge bg-success">Selesai</span>
                                @else
                                <span class="badge bg-secondary">-</span>
                                @endif
                            </small>
                        </li>
                        @endforeach
                    </ul>
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
                        <i class="bi bi-clock-history"></i> Riwayat Status
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
