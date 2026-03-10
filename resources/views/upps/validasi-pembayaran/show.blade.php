{{-- resources/views/upps/validasi-pembayaran/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pembayaran')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.validasi-pembayaran') }}">Validasi Pembayaran</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-credit-card"></i> Detail Pembayaran
            </h5>
            <small class="text-muted">{{ $pembayaran->pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.validasi-pembayaran') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @if($pembayaran->status_pembayaran === 'menunggu_pembayaran')
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-hourglass-split"></i>
                <strong>Menunggu pembayaran</strong>
                <br>
                Mohon segera lakukan pembayaran sebelum jatuh tempo:
                <strong>{{ $pembayaran->tanggal_jatuh_tempo?->locale('id')->translatedFormat('d M Y') ?? '-' }}</strong>
                @if($pembayaran->tanggal_jatuh_tempo && $pembayaran->tanggal_jatuh_tempo < now()) <br>
                    <span class="badge bg-danger mt-2">Pembayaran telah melewati jatuh tempo!</span>
                    @endif
            </div>
            @elseif($pembayaran->status_pembayaran === 'menunggu_verifikasi')
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-clock-history"></i>
                <strong>Menunggu validasi pembayaran</strong>
                <br>
                Formulir & Bukti pembayaran telah diupload pada {{ $pembayaran->tanggal_pembayaran?->locale('id')->translatedFormat('d M Y H:i') ?? '-' }}.<br>Mohon menunggu proses validasi pembayaran
            </div>
            @elseif($pembayaran->status_pembayaran === 'terverifikasi')
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Pembayaran telah divalidasi</strong>
                <br>
                Terima kasih telah melakukan pembayaran akreditasi. Mohon dapat melakukan pengiriman dokumen akreditasi, pada tahap selanjutnya
            </div>
            @elseif($pembayaran->status_pembayaran === 'upload_ulang')
            <div class="alert alert-secondary alert-permanent">
                <i class="bi bi-arrow-repeat"></i>
                <strong>Diminta untuk upload ulang formulir & bukti pembayaran</strong>
                <br>
                Silakan upload ulang formulir & bukti pembayaran yang lebih jelas.
                @if($pembayaran->catatan_verifikasi)
                <br><br>
                <strong>Catatan:</strong> {{ $pembayaran->catatan_verifikasi }}
                @endif
            </div>
            @elseif($pembayaran->status_pembayaran === 'ditolak')
            <div class="alert alert-danger alert-permanent">
                <i class="bi bi-x-circle"></i>
                <strong>Pembayaran ditolak oleh LAMDEPILAR</strong>
                <br>
                @if($pembayaran->alasan_penolakan)
                <strong>Alasan:</strong> {{ $pembayaran->alasan_penolakan }}
                @endif
            </div>
            @endif

            <!-- Action Button -->
            @if(in_array($pembayaran->status_pembayaran, ['menunggu_pembayaran', 'upload_ulang']) && $dokumenPembayaran->count() > 0)
            <div class="card my-4 border-warning">
                <div class="card-body text-center">
                    <h5 class="mb-3">
                        @if($pembayaran->status_pembayaran === 'upload_ulang')
                        <i class="bi bi-arrow-repeat"></i> Perlu Upload Ulang Formulir & Bukti Pembayaran
                        @else
                        <i class="bi bi-info-circle"></i> Ingin Mengubah Formulir & Bukti Pembayaran?
                        @endif
                    </h5>
                    <p class="text-muted">
                        @if($pembayaran->status_pembayaran === 'upload_ulang')
                        LAMDEPILAR meminta Anda untuk upload ulang formulir & bukti pembayaran yang lebih jelas.
                        @else
                        Anda dapat mengganti formulir & bukti pembayaran yang telah diupload sebelumnya.
                        @endif
                    </p>
                    <a href="{{ route('upps.validasi-pembayaran.upload.form', $pembayaran->id) }}" class="btn btn-warning btn-md">
                        <i class="bi bi-upload"></i> Upload Ulang Formulir & Bukti Pembayaran
                    </a>
                </div>
            </div>
            @endif

            <!-- Bukti Pembayaran -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-pdf"></i> Formulir & Bukti Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    @if($dokumenPembayaran->count() > 0)
                    @php
                    $dokumen = $dokumenPembayaran->first();
                    @endphp
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center rounded gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $dokumen->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($dokumen->file_size / 1024, 2) }} KB •
                                    Diupload: {{ $dokumen->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small>
                                @if($dokumen->is_latest)
                                <br>
                                <span class="badge bg-success">Versi Terbaru</span>
                                @else
                                <br>
                                <span class="badge bg-secondary">Versi {{ $dokumen->versi }}</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.validasi-pembayaran.dokumen.download', $dokumen->id) }}" class="btn btn-success btn-md">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada formulir & bukti pembayaran yang diupload. <br>Mohon upload dengan mengklik tombol berikut</p>

                        @if(in_array($pembayaran->status_pembayaran, ['menunggu_pembayaran', 'upload_ulang']))
                        <a href="{{ route('upps.validasi-pembayaran.upload.form', $pembayaran->id) }}" class="btn btn-success btn-md mt-3">
                            <i class="bi bi-upload"></i> Upload Formulir & Bukti Pembayaran
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informasi Pembayaran -->
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Nomor Invoice</th>
                            <td>: <strong>{{ $pembayaran->nomor_invoice }}</strong></td>
                        </tr>
                        <tr>
                            <th>{{ in_array($pembayaran->status_pembayaran,['terverifikasi','menunggu_verifikasi','upload_ulang']) ? 'Nominal Pembayaran' : 'Nominal Tertagih' }}</th>
                            <td>
                                : <strong class="text-success fs-5">
                                    Rp {{ number_format($pembayaran->jumlah_pembayaran, 0, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Invoice</th>
                            <td>: {{ $pembayaran->created_at->locale('id')->translatedFormat('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Jatuh Tempo</th>
                            <td>
                                : {{ $pembayaran->tanggal_jatuh_tempo?->locale('id')->translatedFormat('d M Y') ?? '-' }}
                                @if($pembayaran->tanggal_jatuh_tempo && $pembayaran->tanggal_jatuh_tempo < now() && in_array($pembayaran->status_pembayaran, ['menunggu_pembayaran', 'upload_ulang']))
                                    <span class="badge bg-danger ms-2">Terlambat</span>
                                    @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pembayaran</th>
                            <td>: {{ $pembayaran->tanggal_pembayaran?->locale('id')->translatedFormat('d M Y H:i') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status Pembayaran</th>
                            <td>
                                @php
                                $statusConfig = [
                                'menunggu_pembayaran' => ['class' => 'warning', 'icon' => 'hourglass-split', 'text' => 'Menunggu Pembayaran'],
                                'menunggu_verifikasi' => ['class' => 'info', 'icon' => 'clock-history', 'text' => 'Menunggu Validasi'],
                                'terverifikasi' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Tervalidasi'],
                                'upload_ulang' => ['class' => 'secondary', 'icon' => 'arrow-repeat', 'text' => 'Upload Ulang'],
                                'ditolak' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Ditolak'],
                                ];
                                $status = $statusConfig[$pembayaran->status_pembayaran] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Unknown'];
                                @endphp
                                : <span class="badge bg-{{ $status['class'] }}">
                                    <i class="bi bi-{{ $status['icon'] }}"></i>
                                    {{ $status['text'] }}
                                </span>
                            </td>
                        </tr>
                    </table>

                    @if($pembayaran->catatan_pembayaran)
                    <hr>
                    <h6 class="fw-bold">Catatan Pembayaran:</h6>
                    <p class="text-muted">{{ $pembayaran->catatan_pembayaran }}</p>
                    @endif

                    @if($pembayaran->catatan_verifikasi)
                    <hr>
                    <h6 class="fw-bold">Catatan Validasi:</h6>
                    <p class="text-muted">{{ $pembayaran->catatan_verifikasi }}</p>
                    @endif

                    @if($pembayaran->alasan_penolakan)
                    <hr>
                    <h6 class="fw-bold text-danger">Alasan Penolakan:</h6>
                    <p class="text-muted">{{ $pembayaran->alasan_penolakan }}</p>
                    @endif
                </div>
            </div>
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
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <div class="timeline">
                        <!-- Invoice Dibuat -->
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill text-secondary" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Invoice Diterima</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pembayaran->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Pembayaran Dilakukan -->
                        @if($pembayaran->tanggal_pembayaran)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill text-secondary" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Formulir & Bukti Pembayaran Diupload</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pembayaran->pengajuan->formulirPembayaran?->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Pembayaran Divalidasi -->
                        @if($pembayaran->tanggal_verifikasi)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill
                                            {{ $pembayaran->status_pembayaran === 'terverifikasi' ? 'text-success' : 'text-danger' }}" style="font-size: 8px;">
                                    </i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        @if($pembayaran->tanggal_ditolak)
                                        Ditolak
                                        @elseif($pembayaran->tanggal_verifikasi && $pembayaran->status_pembayaran == 'terverifikasi')
                                        Pembayaran Divalidasi
                                        @elseif($pembayaran->tanggal_upload_ulang || $pembayaran->status_pembayaran == 'upload_ulang')
                                        Diminta Upload Ulang
                                        @else
                                        -
                                        @endif
                                    </strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pembayaran->tanggal_verifikasi->locale('id')->translatedFormat('d M Y H:i') }}
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
