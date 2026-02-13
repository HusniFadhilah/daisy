@extends('layouts.template.app')

@section('title', 'Penyampaian Hasil Akreditasi')

@section('content')
<div class="container-fluid py-3">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-award"></i>
                        Penyampaian Hasil Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Program Info -->
                    <div class="alert alert-info alert-permanent">
                        <h6 class="fw-bold">Program Studi:</h6>
                        <p class="mb-0">
                            {{ $pengajuan->studyProgram->name }} -
                            {{ $pengajuan->studyProgram->university->name }}
                        </p>
                    </div>

                    <form action="{{ route('hasil-akreditasi.submit', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            <!-- Peringkat -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Status Akreditasi <span class="text-danger">*</span>
                                </label>
                                <select name="peringkat_akreditasi" class="form-select @error('peringkat_akreditasi') is-invalid @enderror" required>
                                    <option value="">Pilih Status</option>
                                    <option value="Unggul" {{ old('peringkat_akreditasi') === 'Unggul' ? 'selected' : '' }}>
                                        Unggul
                                    </option>
                                    <option value="Baik Sekali" {{ old('peringkat_akreditasi') === 'Baik Sekali' ? 'selected' : '' }}>
                                        Baik Sekali
                                    </option>
                                    <option value="Baik" {{ old('peringkat_akreditasi') === 'Baik' ? 'selected' : '' }}>
                                        Baik
                                    </option>
                                    <option value="Tidak Terakreditasi" {{ old('peringkat_akreditasi') === 'Tidak Terakreditasi' ? 'selected' : '' }}>
                                        Tidak Terakreditasi
                                    </option>
                                </select>
                                @error('peringkat_akreditasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Skor -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Skor Akhir (0-400) <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="skor_akhir" class="form-control @error('skor_akhir') is-invalid @enderror" min="0" max="400" step="0.01" value="{{ old('skor_akhir') }}" required>
                                @error('skor_akhir')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tanggal -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Tanggal Penyampaian <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="tanggal_penyampaian" class="form-control @error('tanggal_penyampaian') is-invalid @enderror" value="{{ old('tanggal_penyampaian', now()->format('Y-m-d')) }}" required>
                                @error('tanggal_penyampaian')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Masa Berlaku -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Masa Berlaku (Tahun) <span class="text-danger">*</span>
                                </label>
                                <select name="masa_berlaku_tahun" class="form-select @error('masa_berlaku_tahun') is-invalid @enderror" required>
                                    <option value="5" {{ old('masa_berlaku_tahun') == 5 ? 'selected' : '' }}>5 Tahun</option>
                                    <option value="4" {{ old('masa_berlaku_tahun') == 4 ? 'selected' : '' }}>4 Tahun</option>
                                    <option value="3" {{ old('masa_berlaku_tahun') == 3 ? 'selected' : '' }}>3 Tahun</option>
                                </select>
                                @error('masa_berlaku_tahun')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Upload Surat -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold">
                                    Surat Hasil Akreditasi (PDF) <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="surat_hasil_akreditasi" class="form-control @error('surat_hasil_akreditasi') is-invalid @enderror" accept=".pdf" required>
                                <small class="text-muted">Format: PDF | Maksimal: 5MB</small>
                                @error('surat_hasil_akreditasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Catatan -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Catatan Hasil</label>
                                <textarea name="catatan_hasil" class="form-control @error('catatan_hasil') is-invalid @enderror" rows="4" placeholder="Catatan tambahan terkait hasil akreditasi...">{{ old('catatan_hasil') }}</textarea>
                                @error('catatan_hasil')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Warning -->
                        <div class="alert alert-warning alert-permanent mt-3">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Perhatian:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Setelah hasil disampaikan, <strong>masa sanggah (7 hari)</strong> akan dimulai otomatis</li>
                                <li>Program studi akan diberikan kesempatan untuk mengajukan banding</li>
                                <li>Pastikan semua data sudah benar sebelum submit</li>
                            </ul>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Sampaikan Hasil Akreditasi
                            </button>
                            <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar: Timeline Preview -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-list-ol"></i> Step Selanjutnya
                    </h6>
                </div>
                <div class="card-body">
                    <ol class="ps-3">
                        <li class="mb-2">
                            <strong>Penyampaian Hasil</strong> (Step 14)
                            <span class="badge bg-warning">Current</span>
                        </li>
                        <li class="mb-2">Masa Sanggah (Step 15)</li>
                        <li class="mb-2">Pelaksanaan Banding (Step 16-17)</li>
                        <li class="mb-2">Penetapan Hasil (Step 18)</li>
                        <li class="mb-2">Pengumuman Hasil (Step 19)</li>
                        <li class="mb-2">Pelaporan Hasil (Step 20)</li>
                        <li>Penyimpanan Arsip (Step 21)</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
