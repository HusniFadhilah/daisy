{{-- resources/views/upps/validasi-pembayaran/upload.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Upload Formulir & Bukti Pembayaran')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.validasi-pembayaran') }}">Validasi Pembayaran</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.validasi-pembayaran.show', $pembayaran->id) }}">Detail</a></li>
            <li class="breadcrumb-item active">Upload Formulir & Bukti Pembayaran</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-upload"></i> Upload Formulir & Bukti Pembayaran
            </h5>
            <small class="text-muted">{{ $pembayaran->nomor_invoice }}</small>
        </div>
        <a href="{{ route('upps.validasi-pembayaran.show', $pembayaran->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Info Invoice -->
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Invoice
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th width="45%">Nomor Invoice</th>
                                    <td>: <strong>{{ $pembayaran->nomor_invoice }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Program Studi</th>
                                    <td>: {{ $pembayaran->pengajuan->studyProgram->name }}</td>
                                </tr>
                                <tr>
                                    <th>Universitas</th>
                                    <td>: {{ $pembayaran->pengajuan->studyProgram->university->name }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th width="45%">Jumlah</th>
                                    <td>
                                        : <strong class="text-success">
                                            Rp {{ number_format($pembayaran->jumlah_pembayaran, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Jatuh Tempo</th>
                                    <td>
                                        : {{ $pembayaran->tanggal_jatuh_tempo?->format('d M Y') ?? '-' }}
                                        @if($pembayaran->tanggal_jatuh_tempo && $pembayaran->tanggal_jatuh_tempo < now()) <br>
                                            <span class="badge bg-danger">Terlambat</span>
                                            @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form Alert -->
            @if($pembayaran->status_pembayaran === 'upload_ulang')
            <div class="alert alert-warning alert-permanent">
                <h5><i class="bi bi-arrow-repeat"></i> Permintaan Upload Ulang</h5>
                <p class="mb-0">
                    Bagian keuangan LAMDEPILAR meminta Anda untuk upload ulang formulir & bukti pembayaran yang lebih jelas.
                </p>
                @if($pembayaran->catatan_verifikasi)
                <hr>
                <strong>Catatan dari Bagian Keuangan:</strong>
                <p class="mb-0">{{ $pembayaran->catatan_verifikasi }}</p>
                @endif
            </div>
            @endif

            <!-- Upload Form -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cloud-upload"></i> Form Upload Formulir & Bukti Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('upps.validasi-pembayaran.upload', $pembayaran->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Tanggal Pembayaran -->
                        <div class="mb-3">
                            <label for="tanggal_pembayaran" class="form-label">
                                Tanggal Pembayaran <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control @error('tanggal_pembayaran') is-invalid @enderror" id="tanggal_pembayaran" name="tanggal_pembayaran" value="{{ old('tanggal_pembayaran', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required>
                            @error('tanggal_pembayaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Tanggal saat Anda melakukan pembayaran
                            </small>
                        </div>

                        <!-- File Bukti Pembayaran -->
                        <div class="mb-3">
                            <label for="file_formulir_pembayaran" class="form-label">
                                File Formulir & Bukti Pembayaran <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control @error('file_formulir_pembayaran') is-invalid @enderror" id="file_formulir_pembayaran" name="file_formulir_pembayaran" accept=".pdf,.jpg,.jpeg,.png,.xlsx" required>
                            @error('file_formulir_pembayaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Format: XLSX (Maksimal 5MB)
                            </small>
                        </div>

                        <!-- Preview -->
                        <div class="mb-3 d-none" id="filePreview">
                            <label class="form-label">Preview File</label>
                            <div class="border rounded p-3 bg-light">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark text-danger me-3" style="font-size: 48px;"></i>
                                    <div>
                                        <strong id="fileName"></strong>
                                        <br>
                                        <small class="text-muted" id="fileSize"></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Pembayaran -->
                        <div class="mb-3">
                            <label for="catatan_pembayaran" class="form-label">
                                Catatan Pembayaran (Opsional)
                            </label>
                            <textarea class="form-control @error('catatan_pembayaran') is-invalid @enderror" id="catatan_pembayaran" name="catatan_pembayaran" rows="3" placeholder="Tambahkan catatan jika diperlukan...">{{ old('catatan_pembayaran') }}</textarea>
                            @error('catatan_pembayaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Important Note -->
                        <div class="alert alert-info alert-permanent">
                            <h6><i class="bi bi-info-circle"></i> Informasi Penting</h6>
                            <ul class="mb-0">
                                <li>Pastikan formulir & bukti pembayaran yang diupload jelas dan dapat dibaca</li>
                                <li>Formulir & bukti pembayaran harus menunjukkan nominal yang sesuai dengan invoice</li>
                                <li>Setelah upload, formulir bukti pembayaran akan divalidasi oleh LAMDEPILAR</li>
                                <li>Anda akan mendapat notifikasi hasil validasi melalui email</li>
                            </ul>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-md">
                                <i class="bi bi-upload"></i> Upload Formulir & Bukti Pembayaran
                            </button>
                            <a href="{{ route('upps.validasi-pembayaran.show', $pembayaran->id) }}" class="btn btn-secondary btn-md">
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
    // File preview
    document.getElementById('file_formulir_pembayaran').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');

        if (file) {
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            preview.classList.remove('d-none');
        } else {
            preview.classList.add('d-none');
        }
    });

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

</script>
@endpush
@endsection
