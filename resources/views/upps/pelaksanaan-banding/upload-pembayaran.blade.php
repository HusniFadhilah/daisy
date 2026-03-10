{{-- resources/views/upps/pelaksanaan-banding/upload-pembayaran.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Upload Pembayaran Banding')

@section('content')
<div class="container-fluid py-3">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pelaksanaan-banding') }}">Pelaksanaan Banding</a></li>
            <li class="breadcrumb-item">
                <a href="{{ route('upps.pelaksanaan-banding.show', $pengajuan->id) }}">Detail</a>
            </li>
            <li class="breadcrumb-item active">Upload Pembayaran</li>
        </ol>
    </nav>

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-upload"></i> Upload Formulir & Bukti Pembayaran Banding
            </h5>
            <small class="text-muted">{{ $pembayaranBanding->nomor_invoice }}</small>
        </div>
        <a href="{{ route('upps.pelaksanaan-banding.show', $pengajuan->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- Info Invoice --}}
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Informasi Invoice Banding</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <th width="45%">Nomor Invoice</th>
                                    <td>: <strong>{{ $pembayaranBanding->nomor_invoice }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Program Studi</th>
                                    <td>: {{ $pengajuan->studyProgram->name }}</td>
                                </tr>
                                <tr>
                                    <th>Universitas</th>
                                    <td>: {{ $pengajuan->studyProgram->university->name }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <th width="45%">Nominal</th>
                                    <td>
                                        : <strong class="text-success fs-5">
                                            Rp {{ number_format($pembayaranBanding->jumlah_pembayaran, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Jatuh Tempo</th>
                                    <td>
                                        :
                                        {{ $pembayaranBanding->tanggal_jatuh_tempo?->locale('id')->translatedFormat('d M Y') ?? '-' }}
                                        @if($pembayaranBanding->tanggal_jatuh_tempo && $pembayaranBanding->tanggal_jatuh_tempo < now()) <span class="badge bg-danger ms-1">Terlambat</span>
                                            @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alert upload ulang --}}
            @if($pembayaranBanding->status_pembayaran === 'upload_ulang')
            <div class="alert alert-warning alert-permanent">
                <h5><i class="bi bi-arrow-repeat"></i> Permintaan Upload Ulang</h5>
                <p class="mb-0">
                    Bagian keuangan meminta Anda untuk mengupload ulang formulir & bukti pembayaran.
                </p>
                @if($pembayaranBanding->catatan_verifikasi)
                <hr>
                <strong>Catatan dari Keuangan:</strong>
                <p class="mb-0">{{ $pembayaranBanding->catatan_verifikasi }}</p>
                @endif
            </div>
            @endif

            {{-- Form Upload --}}
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cloud-upload"></i> Form Upload Formulir & Bukti Pembayaran Banding
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('upps.pelaksanaan-banding.upload-pembayaran.store', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" id="formUpload">
                        @csrf

                        {{-- Tanggal Pembayaran --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Tanggal Pembayaran <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local" name="tanggal_pembayaran" class="form-control @error('tanggal_pembayaran') is-invalid @enderror" value="{{ old('tanggal_pembayaran', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" required>
                            @error('tanggal_pembayaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Tanggal saat melakukan pembayaran ke rekening LAMDEPILAR</small>
                        </div>

                        {{-- File Upload --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                File Formulir & Bukti Pembayaran Banding (XLSX)
                                <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="file_formulir_pembayaran" id="fileInput" class="form-control @error('file_formulir_pembayaran') is-invalid @enderror" accept=".xlsx" required>
                            @error('file_formulir_pembayaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: XLSX (Formulir terisi + bukti bayar) | Maks: 5 MB</small>

                            {{-- Preview --}}
                            <div class="mt-2 d-none border rounded p-3 bg-light" id="filePreview">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-spreadsheet text-success me-3" style="font-size:36px;"></i>
                                    <div>
                                        <strong id="fileName"></strong><br>
                                        <small class="text-muted" id="fileSize"></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Catatan --}}
                        <div class="mb-4">
                            <label class="form-label">
                                Catatan Pembayaran <small class="text-muted">(Opsional)</small>
                            </label>
                            <textarea name="catatan_pembayaran" class="form-control" rows="3" placeholder="Nomor referensi transfer, bank, dll...">{{ old('catatan_pembayaran') }}</textarea>
                        </div>

                        {{-- Info --}}
                        <div class="alert alert-info alert-permanent">
                            <h6><i class="bi bi-info-circle"></i> Informasi Penting</h6>
                            <ul class="mb-0 small">
                                <li>Download dan isi templat formulir pembayaran banding terlebih dahulu</li>
                                <li>Upload formulir yang sudah terisi lengkap beserta bukti transfer</li>
                                <li>Setelah upload, pembayaran akan divalidasi oleh bagian keuangan LAMDEPILAR</li>
                                <li>Proses pelaksanaan banding baru dimulai setelah pembayaran tervalidasi</li>
                            </ul>
                        </div>

                        {{-- Tombol --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success" id="btnSubmit">
                                <i class="bi bi-upload"></i> Upload Formulir & Bukti Pembayaran
                            </button>
                            <a href="{{ route('upps.pelaksanaan-banding.show', $pengajuan->id) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('fileInput').addEventListener('change', function() {
        const file = this.files[0];
        const preview = document.getElementById('filePreview');

        if (!file) {
            preview.classList.add('d-none');
            return;
        }

        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent =
            (file.size / 1024).toFixed(2) + ' KB';
        preview.classList.remove('d-none');
    });

    // Disable setelah form submit
    document.getElementById('formUpload').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';
    });

</script>
@endpush
@endsection
