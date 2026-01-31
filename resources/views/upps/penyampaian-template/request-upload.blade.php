{{-- resources/views/upps/penyampaian-template/request-upload.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Permintaan Pengiriman Ulang Dokumen')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penyampaian-template') }}">Formulir dan Template Dokumen</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penyampaian-template.show', $pengajuan->id) }}">Detail</a></li>
            <li class="breadcrumb-item active">Permintaan Pengiriman Ulang</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-arrow-repeat"></i> Permintaan Pengiriman Ulang Dokumen
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
                        <i class="bi bi-info-circle"></i> Informasi Permohonan Pengiriman Ulang Template
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
                                <strong>Formulir Pembayaran & Template Dokumen Akreditasi</strong>
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
                    @if(!$templateLed && !$formulirPembayaran)
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Dokumen belum tersedia.</p>
                    </div>
                    @else
                    @if($formulirPembayaran)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $formulirPembayaran->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($formulirPembayaran->file_size / 1024, 2) }} KB •
                                    Diupload: {{ $formulirPembayaran->created_at->format('d M Y H:i') }}
                                </small>
                                <br>
                                <span class="badge bg-info">Versi {{ $formulirPembayaran->versi }}</span>
                                <span class="badge bg-secondary">Formulir Pembayaran</span>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penyampaian-template.download', [$pengajuan->id, 'template_formulir_pembayaran']) }}" class="btn btn-success btn-md" target="_blank">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                            </a>
                        </div>
                    </div>
                    @endif

                    @if($templateLed)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $templateLed->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($templateLed->file_size / 1024, 2) }} KB •
                                    Diupload: {{ $templateLed->created_at->format('d M Y H:i') }}
                                </small>
                                <br>
                                <span class="badge bg-info">Versi {{ $templateLed->versi }}</span>
                                <span class="badge bg-secondary">Template Dokumen Akreditasi</span>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penyampaian-template.download', [$pengajuan->id, 'borang_template']) }}" class="btn btn-success btn-md" target="_blank">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                            </a>
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            <!-- Info Alert -->
            <div class="alert alert-warning alert-permanent">
                <h6><i class="bi bi-exclamation-triangle"></i> Informasi Penting</h6>
                <ul class="mb-0">
                    <li>Permintaan <strong>pengiriman ulang</strong> akan dikirim ke LAMDEPILAR</li>
                    <li>Status permohonan akreditasi <strong>tidak akan berubah</strong></li>
                    <li>Dokumen saat ini tetap tersimpan sampai LAMDEPILAR mengirim dokumen terbaru</li>
                    <li>Anda akan mendapat notifikasi setelah LAMDEPILAR merespon permintaan ini</li>
                    <li>Pastikan alasan yang Anda berikan jelas dan spesifik</li>
                </ul>
            </div>

            <!-- Form Request -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square"></i> Form Permintaan Pengiriman Ulang
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('upps.penyampaian-template.request.submit', [$pengajuan->id]) }}" method="POST">
                        @csrf

                        {{-- Pilih dokumen yang diminta --}}
                        <div class="mb-3">
                            <label class="form-label">
                                Pilih Dokumen yang Diminta Pengiriman Ulang <span class="text-danger">*</span>
                            </label>

                            <select name="jenis_dokumen[]" class="form-select @error('jenis_dokumen') is-invalid @enderror" multiple required>
                                @if($templateLed)
                                <option value="borang_template" {{ in_array('borang_template', old('jenis_dokumen', [])) ? 'selected' : '' }}>
                                    Template Dokumen Akreditasi
                                </option>
                                @endif
                                @if($formulirPembayaran)
                                <option value="template_formulir_pembayaran" {{ in_array('template_formulir_pembayaran', old('jenis_dokumen', [])) ? 'selected' : '' }}>
                                    Formulir Pembayaran
                                </option>
                                @endif
                            </select>

                            @error('jenis_dokumen')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="form-text text-muted">
                                Anda dapat memilih satu dokumen atau keduanya sekaligus.
                            </small>
                        </div>

                        <!-- Alasan Request -->
                        <div class="mb-3">
                            <label for="alasan_request" class="form-label">
                                Alasan Permintaan Pengiriman Ulang <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('alasan_request') is-invalid @enderror" id="alasan_request" name="alasan_request" rows="6" placeholder="Mohon jelaskan alasan permintaan pengiriman ulang dokumen..." required>{{ old('alasan_request') }}</textarea>
                            @error('alasan_request')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Contoh: "Dokumen tidak dapat dibuka" atau "Kualitas scan formulir pembayaran buram dan tidak terbaca"
                            </small>
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

@endsection
