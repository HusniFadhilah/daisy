@extends('layouts.template.app')

@section('title', 'Detail Formulir Pembayaran - ' . $pengajuan->nomor_pengajuan)

@push('styles')
<style>
    .document-preview {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        background: #f8f9fa;
        min-height: 400px;
    }

    .document-preview iframe {
        width: 100%;
        min-height: 600px;
        border: none;
        border-radius: 4px;
        background: white;
    }

    .document-preview img {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
    }

    .info-card {
        border-left: 4px solid #0d6efd;
    }

    .download-button {
        transition: all 0.3s ease;
    }

    .download-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="bi bi-file-earmark-text"></i> Detail Formulir Pembayaran
                    </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('keuangan.formulir.index') }}">Formulir Pembayaran</a>
                            </li>
                            <li class="breadcrumb-item active">{{ $pengajuan->nomor_pengajuan }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('keuangan.formulir.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Informasi -->
        <div class="col-lg-4 mb-4">

            <!-- Informasi Pengajuan -->
            <div class="card info-card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pengajuan
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="40%"><strong>Nomor Permohonan Akreditasi</strong></td>
                            <td>{{ $pengajuan->nomor_pengajuan }}</td>
                        </tr>
                        <tr>
                            <td><strong>Program Studi</strong></td>
                            <td>{{ $pengajuan->studyProgram->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Universitas</strong></td>
                            <td>{{ $pengajuan->studyProgram->university->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Jenjang</strong></td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $pengajuan->studyProgram->degreeLevel->name }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Jenis Akreditasi</strong></td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $pengajuan->jenis_akreditasi_label }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Informasi Pembayaran -->
            @if($pengajuan->pembayaran)
            <div class="card info-card mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-credit-card"></i> Informasi Pembayaran
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="40%"><strong>Nomor Invoice</strong></td>
                            <td>{{ $pengajuan->pembayaran->nomor_invoice }}</td>
                        </tr>
                        <tr>
                            <td><strong>Jumlah</strong></td>
                            <td class="text-success fw-bold">
                                Rp {{ number_format($pengajuan->pembayaran->jumlah_pembayaran, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Jatuh Tempo</strong></td>
                            <td>
                                {{ $pengajuan->pembayaran->tanggal_jatuh_tempo?->locale('id')->translatedFormat('d M Y') ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Bayar</strong></td>
                            <td>
                                {{ $pengajuan->pembayaran->tanggal_pembayaran?->locale('id')->translatedFormat('d M Y H:i') ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td>
                                @php
                                $statusConfig = [
                                'menunggu_pembayaran' => ['class' => 'warning', 'icon' => 'hourglass-split', 'text' => 'Menunggu Pembayaran'],
                                'menunggu_verifikasi' => ['class' => 'info', 'icon' => 'clock-history', 'text' => 'Menunggu Validasi'],
                                'terverifikasi' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Tervalidasi'],
                                'upload_ulang' => ['class' => 'secondary', 'icon' => 'arrow-repeat', 'text' => 'Upload Ulang'],
                                ];
                                $status = $statusConfig[$pengajuan->pembayaran->status_pembayaran] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => '-'];
                                @endphp
                                <span class="badge bg-{{ $status['class'] }}">
                                    <i class="bi bi-{{ $status['icon'] }}"></i>
                                    {{ $status['text'] }}
                                </span>
                            </td>
                        </tr>
                    </table>

                    @if($pengajuan->pembayaran->catatan_pembayaran)
                    <div class="alert alert-info mt-3 mb-0">
                        <small><strong>Catatan:</strong></small>
                        <p class="mb-0 small">{{ $pengajuan->pembayaran->catatan_pembayaran }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Dokumen List -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-files"></i> Dokumen Pembayaran
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($dokumenPembayaran as $dok)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <i class="bi bi-file-earmark-pdf text-danger"></i>
                                        {{ $dok->jenis_dokumen_alias }}
                                    </h6>
                                    <small class="text-muted d-block">
                                        {{ $dok->original_filename }}
                                    </small>
                                    <small class="text-muted">
                                        {{ $dok->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                    </small>
                                </div>
                                <div class="btn-group-vertical btn-group-sm">
                                    <a href="{{ $dok->download_url }}" class="btn btn-primary btn-sm download-button" target="_blank">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                    <button type="button" class="btn btn-info btn-sm" onclick="previewDocument('{{ $dok->preview_url }}', '{{ $dok->file_extension }}')">
                                        <i class="bi bi-eye"></i> Preview
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item text-center py-4">
                            <i class="bi bi-inbox text-muted"></i>
                            <p class="mb-0 text-muted small mt-2">Tidak ada dokumen</p>
                        </div>
                        @endforelse

                        <!-- Bukti Pembayaran dari tabel pembayaran -->
                        @if($buktiPembayaran)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <i class="bi bi-receipt text-success"></i>
                                        Bukti Pembayaran
                                    </h6>
                                    <small class="text-muted d-block">
                                        {{ basename($buktiPembayaran) }}
                                    </small>
                                    <small class="text-muted">
                                        Upload: {{ $pengajuan->pembayaran->tanggal_pembayaran?->locale('id')->translatedFormat('d M Y H:i') ?? '-' }}
                                    </small>
                                </div>
                                <div class="btn-group-vertical btn-group-sm">
                                    <a href="{{ route('keuangan.pembayaran.download-bukti', $pengajuan->id) }}" class="btn btn-primary btn-sm download-button" target="_blank">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                    <button type="button" class="btn btn-info btn-sm" onclick="previewBukti('{{ Storage::disk('public')->url($buktiPembayaran) }}')">
                                        <i class="bi bi-eye"></i> Preview
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            @if($pengajuan->pembayaran && $pengajuan->pembayaran->status_pembayaran == 'menunggu_verifikasi')
            <div class="d-grid gap-2 mt-3">
                <a href="{{ route('keuangan.pembayaran.show', $pengajuan->id) }}" class="btn btn-success btn-md">
                    <i class="bi bi-check-circle"></i> Validasi Pembayaran
                </a>
            </div>
            @endif

        </div>

        <!-- Right Column - Preview -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-eye"></i> Preview Dokumen
                    </h6>
                </div>
                <div class="card-body document-preview" id="documentPreview">
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                        <p class="text-muted mt-3">
                            Klik tombol <strong>"Preview"</strong> pada dokumen untuk melihat preview
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function previewDocument(url, extension) {
        const preview = $('#documentPreview');

        preview.html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-3 text-muted">Memuat dokumen...</p></div>');

        setTimeout(() => {
            let content = '';

            if (extension === 'pdf') {
                content = `<iframe src="${url}"></iframe>`;
            } else if (['jpg', 'jpeg', 'png', 'gif'].includes(extension.toLowerCase())) {
                content = `<img src="${url}" alt="Preview" class="img-fluid">`;
            } else {
                content = `
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-x fs-1 text-warning"></i>
                        <p class="text-muted mt-3">Preview tidak tersedia untuk tipe file ini</p>
                        <a href="${url}" target="_blank" class="btn btn-primary">
                            <i class="bi bi-download"></i> Download File
                        </a>
                    </div>
                `;
            }

            preview.html(content);
        }, 500);
    }

    function previewBukti(url) {
        const preview = $('#documentPreview');
        const extension = url.split('.').pop().toLowerCase();

        preview.html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-3 text-muted">Memuat dokumen...</p></div>');

        setTimeout(() => {
            let content = '';

            if (extension === 'pdf') {
                content = `<iframe src="${url}"></iframe>`;
            } else if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
                content = `<img src="${url}" alt="Bukti Pembayaran" class="img-fluid">`;
            } else {
                content = `
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-x fs-1 text-warning"></i>
                        <p class="text-muted mt-3">Preview tidak tersedia</p>
                        <a href="{{ route('keuangan.pembayaran.download-bukti', $pengajuan->id) }}"
                           target="_blank" class="btn btn-primary">
                            <i class="bi bi-download"></i> Download File
                        </a>
                    </div>
                `;
            }

            preview.html(content);
        }, 500);
    }

    // Auto preview first document on load
    $(document).ready(function() {
        @if($formulirPembayaran)
        previewDocument('{{ $formulirPembayaran->preview_url }}', '{{ $formulirPembayaran->file_extension }}');
        @endif
    });

</script>
@endpush
