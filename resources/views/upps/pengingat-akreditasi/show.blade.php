{{-- resources/views/upps/pengingat-akreditasi/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pengingat Masa Akreditasi')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pengingat-akreditasi') }}">Pengingat Masa Akreditasi</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-bell"></i> Detail Pengingat Masa Akreditasi
            </h5>
            <small class="text-muted">Tahun Akreditasi {{ $pengingat->tahun_akreditasi }}</small>
        </div>
        <a href="{{ route('upps.pengingat-akreditasi') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @if($pengingat->status === \App\Models\PengingatAkreditasi::STATUS_BELUM_DIRESPON)
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Pengingat belum direspon!</strong>
                Silakan buat permohonan akreditasi sebagai respon terhadap pengingat ini.
            </div>
            @elseif($pengingat->status === \App\Models\PengingatAkreditasi::STATUS_DIRESPON)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Pengingat sudah direspon!</strong>
                Respon dikirim pada {{ $pengingat->tanggal_direspon->format('d M Y H:i') }}
                ({{ $pengingat->durasi_respon }} hari setelah pengingat dikirim)
            </div>
            @elseif($pengingat->status === \App\Models\PengingatAkreditasi::STATUS_KEDALUWARSA)
            <div class="alert alert-secondary alert-permanent">
                <i class="bi bi-clock-history"></i>
                <strong>Pengingat kedaluwarsa</strong>
                Batas waktu respon telah terlampaui.
            </div>
            @endif

            <!-- Informasi Pengingat -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pengingat
                    </h5>
                </div>

                <div class="card-body">

                    {{-- ===================== --}}
                    {{-- DETAIL PENGINGAT --}}
                    {{-- ===================== --}}
                    <h6 class="text-primary mb-3">
                        <i class="bi bi-bell"></i> Detail Pengingat
                    </h6>

                    <table class="table table-borderless mb-3">
                        <tr>
                            <th width="30%">Pengirim</th>
                            <td>
                                : {{ $pengingat->pengirim->name ?? '-' }}
                                @if($pengingat->pengirim)
                                <br>
                                <small class="text-muted">{{ $pengingat->pengirim->email }}</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Dikirim</th>
                            <td>: {{ $pengingat->tanggal_dikirim->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                : <span class="badge {{ $pengingat->status_badge_class }}">
                                    {{ $pengingat->status_label }}
                                </span>
                            </td>
                        </tr>

                        @if($pengingat->tanggal_direspon)
                        <tr>
                            <th>Tanggal Direspon</th>
                            <td>: {{ $pengingat->tanggal_direspon->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Durasi Respon</th>
                            <td>: {{ $pengingat->durasi_respon }} hari</td>
                        </tr>
                        @endif
                    </table>

                    <hr>

                    {{-- ===================== --}}
                    {{-- INFORMASI PROGRAM STUDI --}}
                    {{-- ===================== --}}
                    <h6 class="text-secondary mb-3">
                        <i class="bi bi-mortarboard"></i> Informasi Program Studi yang Diingatkan
                    </h6>

                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="30%">Program Studi</th>
                            <td>: {{ $pengingat->studyProgram->full_name }}</td>
                        </tr>
                        <tr>
                            <th>Universitas</th>
                            <td>: {{ $pengingat->studyProgram->university->name }}</td>
                        </tr>
                        <tr>
                            <th>Jenjang</th>
                            <td>: {{ $pengingat->studyProgram->degreeLevel->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tahun Akreditasi</th>
                            <td>: {{ $pengingat->tahun_akreditasi }}</td>
                        </tr>
                    </table>

                </div>
            </div>

            <!-- Pesan Pengingat -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope-open"></i> Pesan Pengingat
                    </h5>
                </div>
                <div class="card-body">
                    @if($pengingat->pesan_pengingat)
                    <div class="alert alert-light alert-permanent">
                        {!! nl2br(e($pengingat->pesan_pengingat)) !!}
                    </div>
                    @else
                    <p class="text-muted mb-0">Tidak ada pesan khusus</p>
                    @endif
                </div>
            </div>

            <!-- Permohonan Terkait -->
            {{-- @if($pengingat->pengajuan)
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-text"></i> Permohonan Akreditasi Terkait
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Nomor Permohonan</th>
                            <td>: {{ $pengingat->pengajuan->nomor_pengajuan }}</td>
            </tr>
            <tr>
                <th>Jenis Akreditasi</th>
                <td>: {{ $pengingat->pengajuan->jenis_akreditasi_label }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>: {!! $pengingat->pengajuan->getCustomBadgeLastStatus('surat_permohonan_ps','upps') !!}</td>
            </tr>
            <tr>
                <th>Tanggal Permohonan</th>
                <td>: {{ $pengingat->pengajuan->tanggal_surat_permohonan_dikirim?->format('d M Y H:i') ?? '-' }}</td>
            </tr>
            </table>

            <a href="{{ route('pengajuan.show', $pengingat->pengajuan->id) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-eye"></i> Lihat Detail Permohonan Akreditasi
            </a>
        </div>
    </div>
    @endif --}}

    <!-- Action Button -->
    @if($pengingat->status === \App\Models\PengingatAkreditasi::STATUS_BELUM_DIRESPON)
    <div class="card border-success mt-4">
        <div class="card-body text-center">
            <h5 class="mb-3">Siap untuk merespon pengingat ini?</h5>
            <p class="text-muted mb-4">
                Anda akan membuat permohonan akreditasi sebagai respon terhadap pengingat ini.
            </p>
            <a href="{{ route('upps.pengingat-akreditasi.respond.form', $pengingat->id) }}" class="btn btn-success btn-md">
                <i class="bi bi-reply-fill"></i> Respon Pengingat & Buat Permohonan
            </a>
        </div>
    </div>
    @endif
</div>

<!-- Sidebar -->
<div class="col-lg-4">
    <!-- Timeline -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i> Riwayat Status
            </h5>
        </div>
        <div class="card-body">
            <div class="timeline">
                <!-- Pengingat Dikirim -->
                <div class="timeline-item mb-3">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-circle-fill text-success" style="font-size: 10px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <strong>Pengingat Masa Akreditasi Dikirim oleh LAMDEPILAR</strong>
                            <br>
                            <small class="text-muted">
                                {{ $pengingat->tanggal_dikirim->format('d M Y H:i') }}
                            </small>
                            <br>
                            <small class="text-muted">
                                oleh {{ $pengingat->pengirim->name ?? 'LAMDEPILAR' }}
                            </small>
                        </div>
                    </div>
                </div>

                @if($pengingat->tanggal_direspon)
                <!-- Pengingat Direspon -->
                <div class="timeline-item">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-circle-fill text-success" style="font-size: 10px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <strong>Pengingat Masa Akreditasi Telah Direspon oleh PS</strong>
                            <br>
                            <small class="text-muted">
                                {{ $pengingat->tanggal_direspon->format('d M Y H:i') }}
                            </small>
                            <br>
                            <small class="text-success">
                                <i class="bi bi-check-circle"></i> Permohonan akreditasi telah dibuat
                            </small>
                        </div>
                    </div>
                </div>
                @else
                <!-- Menunggu Respon -->
                <div class="timeline-item">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-circle-fill text-warning" style="font-size: 10px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <strong>Menunggu Respon</strong>
                            <br>
                            <small class="text-warning">
                                <i class="bi bi-hourglass-split"></i> Belum direspon
                            </small>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
</div>
@endsection
