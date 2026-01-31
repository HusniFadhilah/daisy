{{-- resources/views/upps/penyampaian-template/request-upload.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Permintaan Upload Ulang Dokumen')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penyampaian-template') }}">Penyampaian Formulir dan Template Dokumen</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penyampaian-template.show', $pengajuan->id) }}">Detail</a></li>
            <li class="breadcrumb-item active">Permintaan Upload Ulang</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-arrow-repeat"></i> Permintaan Upload Ulang Dokumen
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.penyampaian-template.show', $pengajuan->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Info Permohonan -->
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Permohonan Upload Ulang Template
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm align-middle">
                        <tr>
                            <th width="30%" rowspan="2">Nomor Permohonan Akreditasi</th>
                            <td width="5%" rowspan="2">:</td>
                            <td width="65%" rowspan="2">
                                <strong>{{ $pengajuan->nomor_pengajuan }}</strong>
                            </td>
                        </tr>
                        <tr></tr>

                        <tr>
                            <th>Jenis Dokumen</th>
                            <td>:</td>
                            <td>
                                <strong>Template Dokumen Akreditasi</strong>
                            </td>
                        </tr>

                        <tr>
                            <th>Tahun Akreditasi</th>
                            <td>:</td>
                            <td>{{ $pengajuan->tahun_akreditasi }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Dokumen Saat Ini -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-pdf"></i> Dokumen Saat Ini
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
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
                                <span class="badge bg-info">Versi {{ $dokumen->versi }}</span>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penyampaian-template.download', [$pengajuan->id, $jenisDokumen]) }}" class="btn btn-success btn-md" target="_blank">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="alert alert-warning alert-permanent">
                <h6><i class="bi bi-exclamation-triangle"></i> Informasi Penting</h6>
                <ul class="mb-0">
                    <li>Permintaan upload ulang akan dikirim ke LAMDEPILAR</li>
                    <li>Status permohonan akreditasi <strong>tidak akan berubah</strong></li>
                    <li>Dokumen yang ada saat ini akan tetap tersimpan sampai LAMDEPILAR mengirim dokumen baru</li>
                    <li>Anda akan mendapat notifikasi setelah LAMDEPILAR merespon permintaan ini</li>
                    <li>Pastikan alasan yang Anda berikan jelas dan spesifik</li>
                </ul>
            </div>

            <!-- Form Request -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square"></i> Form Permintaan Upload Ulang
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('upps.penyampaian-template.request.submit', [$pengajuan->id, $jenisDokumen]) }}" method="POST">
                        @csrf

                        <!-- Alasan Request -->
                        <div class="mb-3">
                            <label for="alasan_request" class="form-label">
                                Alasan Permintaan Upload Ulang <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('alasan_request') is-invalid @enderror" id="alasan_request" name="alasan_request" rows="6" placeholder="Jelaskan alasan permintaan upload ulang dengan detail..." required>{{ old('alasan_request') }}</textarea>
                            @error('alasan_request')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Minimal 10 karakter, maksimal 1000 karakter. Contoh: "Dokumen template tidak dapat dibuka dengan baik" atau "Formulir pembayaran terlihat buram dan tidak terbaca"
                            </small>
                        </div>

                        <!-- Character Counter -->
                        <div class="mb-3">
                            <small class="text-muted">
                                <span id="charCount">0</span> / 1000 karakter
                            </small>
                        </div>

                        <!-- Example -->
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">
                                    <i class="bi bi-lightbulb"></i> Contoh Alasan:
                                </h6>
                                <ul class="mb-0 small">
                                    <li>File dokumen tidak dapat dibuka atau corrupt</li>
                                    <li>Kualitas scan/foto terlalu rendah atau buram sehingga tidak terbaca</li>
                                    <li>Terdapat informasi yang salah atau tidak sesuai dengan program studi kami</li>
                                    <li>Format file tidak sesuai dengan kebutuhan (misal: butuh format Word tapi terkirim PDF)</li>
                                    <li>Terdapat halaman yang hilang atau tidak lengkap</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Confirmation -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="confirmation" required>
                            <label class="form-check-label" for="confirmation">
                                Saya memahami bahwa permintaan ini akan dikirim ke LAMDEPILAR dan memerlukan persetujuan dari mereka
                            </label>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning btn-md">
                                <i class="bi bi-send"></i> Kirim Permintaan
                            </button>
                            <a href="{{ route('upps.penyampaian-template.show', $pengajuan->id) }}" class="btn btn-secondary btn-md">
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
    // Character counter
    const textarea = document.getElementById('alasan_request');
    const charCount = document.getElementById('charCount');

    textarea.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = length;

        if (length > 1000) {
            charCount.classList.add('text-danger');
            charCount.classList.remove('text-muted');
        } else {
            charCount.classList.remove('text-danger');
            charCount.classList.add('text-muted');
        }
    });

    // Initial count
    charCount.textContent = textarea.value.length;

</script>
@endpush
@endsection
