{{-- resources/views/prodi/penerimaan-permohonan/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Penerimaan Permohonan Akreditasi - ' . $pengajuan->nomor_pengajuan)

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('prodi.penerimaan-permohonan') }}">Penerimaan Permohonan Akreditasi</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-envelope-check"></i> Detail Penerimaan Permohonan Akreditasi
            </h5>
            <small class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('prodi.penerimaan-permohonan') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Informasi Permohonan -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-text"></i> Informasi Permohonan Akreditasi
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
                            <th>Status Permohonan</th>
                            <td>: {!! $pengajuan->status_badge !!}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Permohonan Diterima DE</th>
                            <td>: {{ $pengajuan->tanggal_surat_permohonan_diterima?->format('d M Y') ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Penerimaan Permohonan Akreditasi dari LAMDEPILAR -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope-check"></i> Penerimaan Permohonan Akreditasi dari LAMDEPILAR
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $dokumenPenerimaan = $pengajuan->dokumen->first();
                    @endphp

                    @if($dokumenPenerimaan)
                    <!-- Dokumen Sudah Ada -->
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i>
                        <strong>File Penerimaan Permohonan Akreditasi telah tersedia!</strong>
                        <br>
                        <small>Tersedia pada: {{ $dokumenPenerimaan->created_at->format('d M Y H:i') }}</small>
                    </div>

                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $dokumenPenerimaan->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($dokumenPenerimaan->file_size / 1024, 2) }} KB
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i>
                                    {{ $dokumenPenerimaan->created_at->format('d M Y H:i') }}
                                </small>
                                @if($dokumenPenerimaan->keterangan && $dokumenPenerimaan->keterangan != 'Penerimaan Permohonan Akreditasi dari LAMDEPILAR')
                                <br>
                                <small class="text-info">
                                    <i class="bi bi-info-circle"></i> {{ $dokumenPenerimaan->keterangan }}
                                </small>
                                @endif
                            </div>
                        </div>
                        <div class="btn-group-vertical">
                            <a href="{{ route('prodi.penerimaan-permohonan.download', $pengajuan->id) }}" class="btn btn-primary mb-2">
                                <i class="bi bi-download"></i> Download
                            </a>
                            <a href="{{ route('prodi.penerimaan-permohonan.preview', $pengajuan->id) }}" class="btn btn-info" target="_blank">
                                <i class="bi bi-eye"></i> Preview PDF
                            </a>
                        </div>
                    </div>

                    <!-- PDF Preview -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-file-pdf"></i> Preview Dokumen
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <iframe src="{{ route('prodi.penerimaan-permohonan.preview', $pengajuan->id) }}" style="width: 100%; height: 600px; border: none;" title="Preview File Penerimaan Permohonan Akreditasi">
                            </iframe>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle"></i> Informasi Penting
                        </h6>
                        <ul class="mb-0">
                            <li>File ini adalah konfirmasi bahwa permohonan akreditasi PS telah diterima oleh LAMDEPILAR</li>
                            <li>Silakan simpan file ini sebagai arsip resmi</li>
                            <li>Proses selanjutnya akan diinformasikan melalui sistem dan email</li>
                            <li>PS dapat mengunduh file ini kapan saja melalui sistem</li>
                        </ul>
                    </div>
                    @else
                    <!-- Belum Ada Dokumen -->
                    <div class="text-center py-5">
                        <i class="bi bi-hourglass-split text-warning" style="font-size: 64px;"></i>
                        <h6 class="mt-3">Menunggu Penerimaan Permohonan Akreditasi dari LAMDEPILAR</h6>
                        <p class="text-muted">
                            Permohonan Akreditasi PS telah diterima oleh LAMDEPILAR pada
                            <strong>{{ $pengajuan->tanggal_surat_permohonan_diterima?->format('d M Y') ?? '-' }}</strong>.
                            <br>
                            File penerimaan permohonan akreditasi akan segera tersedia di halaman ini.
                        </p>

                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-clock-history"></i>
                            <strong>Status:</strong> Menunggu penerimaan permohonan akreditasi dari LAMDEPILAR
                            <br>
                            <small>PS akan menerima notifikasi email ketika file penerimaan permohonan akreditasi telah tersedia</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Progress Timeline -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check"></i> Progress Workflow
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $dokumenPenerimaan = $pengajuan->dokumen->first();
                    @endphp

                    <div class="timeline">
                        <div class="timeline-step {{ $pengajuan->tanggal_pengingat ? 'completed' : '' }} mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill {{ $pengajuan->tanggal_pengingat ? 'text-success' : 'text-secondary' }}" style="font-size: 10px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Pengingat Diterima</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pengajuan->tanggal_pengingat?->format('d M Y') ?? 'Belum' }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="timeline-step {{ $pengajuan->tanggal_surat_permohonan_dikirim ? 'completed' : '' }} mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill {{ $pengajuan->tanggal_surat_permohonan_dikirim ? 'text-success' : 'text-secondary' }}" style="font-size: 10px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Permohonan Akreditasi Dikirim</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pengajuan->tanggal_surat_permohonan_dikirim?->format('d M Y') ?? 'Belum' }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="timeline-step {{ $pengajuan->tanggal_surat_permohonan_diterima ? 'completed' : '' }} mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill {{ $pengajuan->tanggal_surat_permohonan_diterima ? 'text-success' : 'text-secondary' }}" style="font-size: 10px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Permohonan Diterima LAMDEPILAR</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pengajuan->tanggal_surat_permohonan_diterima?->format('d M Y') ?? 'Belum' }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="timeline-step {{ $dokumenPenerimaan ? 'completed' : ($pengajuan->tanggal_surat_permohonan_diterima ? 'current' : '') }} mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill {{ $dokumenPenerimaan ? 'text-success' : ($pengajuan->tanggal_surat_permohonan_diterima ? 'text-warning' : 'text-secondary') }}" style="font-size: 10px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Penerimaan Permohonan Akreditasi Tersedia</strong>
                                    <br>
                                    @if($dokumenPenerimaan)
                                    <small class="text-success">
                                        <i class="bi bi-check-circle"></i>
                                        {{ $dokumenPenerimaan->created_at->format('d M Y') }}
                                    </small>
                                    @elseif($pengajuan->tanggal_surat_permohonan_diterima)
                                    <small class="text-warning">
                                        <i class="bi bi-hourglass-split"></i> Menunggu
                                    </small>
                                    @else
                                    <small class="text-muted">Belum</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="timeline-step {{ $pengajuan->tanggal_template_led_dikirim ? 'completed' : '' }}">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill {{ $pengajuan->tanggal_template_led_dikirim ? 'text-success' : 'text-secondary' }}" style="font-size: 10px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Template LED Dikirim</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pengajuan->tanggal_template_led_dikirim?->format('d M Y') ?? 'Belum' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Status (tetap, tidak perlu ubah kata kalau hanya menampilkan label dari statusMap) -->
            @php
            $filterStatuses = [
            \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
            \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
            ];

            $logs = $pengajuan->statusLog
            ->whereIn('status_to', $filterStatuses)
            ->sortByDesc('changed_at');
            @endphp

            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if($logs->count() > 0)
                    <div class="timeline">
                        @foreach($logs as $log)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill text-primary" style="font-size: 8px;"></i>
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

        </div>
    </div>
</div>
@endsection
