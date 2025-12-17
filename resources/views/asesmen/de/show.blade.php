@extends('layouts.template.app')

@section('title', 'Detail Pengajuan - ' . $pengajuan->nomor_pengajuan)

@push('styles')
<style>
    .action-card {
        border-left: 4px solid #0d6efd;
    }

    .review-form {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>
                <i class="bi bi-file-earmark-text"></i>
                {{ $pengajuan->nomor_pengajuan }}
            </h2>
            <p class="text-muted mb-0">
                {{ $pengajuan->studyProgram->name }} -
                {{ $pengajuan->tahun_akreditasi }}
            </p>
        </div>
        <div>
            <span class="badge {{ $pengajuan->status_badge_class }} fs-6">
                {{ $pengajuan->status_label }}
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- ACTION: Kirim Form Borang (Langkah 3) -->
            @if($pengajuan->status === 'surat_permohonan_diterima')
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-file-earmark-arrow-down text-primary"></i>
                        Aksi Diperlukan: Kirim Form Borang
                    </h5>
                    <p class="mb-3">
                        Surat permohonan telah diterima. Kirimkan form borang template ke prodi untuk dilengkapi.
                    </p>

                    <form action="{{ route('de.pengajuan.kirim-borang', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Upload Borang Template</label>
                                <input type="file" name="borang_template" class="form-control" accept=".xlsx,.xls,.pdf" required>
                                <small class="text-muted">Format: XLSX, PDF | Max: 10 MB</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Petunjuk pengisian atau informasi tambahan"></textarea>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Kirim Form Borang ke Prodi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- ACTION: Review Kesiapan (Langkah 5a/5b) -->
            @if($pengajuan->status === 'draft_borang_diterima')
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-clipboard-check text-warning"></i>
                        Aksi Diperlukan: Review Kesiapan Borang
                    </h5>
                    <p class="mb-3">
                        Draft borang telah diupload. Lakukan review untuk menentukan apakah borang siap dilanjutkan ke tahap AK.
                    </p>

                    <div class="review-form">
                        <form action="{{ route('de.pengajuan.review', $pengajuan->id) }}" method="POST">
                            @csrf

                            <!-- Checklist Kesiapan -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Checklist Kesiapan</label>
                                <div class="border rounded p-3 bg-white">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="checklist[]" value="Data kuantitatif lengkap" id="check1">
                                        <label class="form-check-label" for="check1">
                                            Data kuantitatif lengkap dan akurat
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="checklist[]" value="Data kualitatif sesuai standar" id="check2">
                                        <label class="form-check-label" for="check2">
                                            Data kualitatif sesuai dengan standar akreditasi
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="checklist[]" value="Dokumen pendukung lengkap" id="check3">
                                        <label class="form-check-label" for="check3">
                                            Dokumen pendukung lengkap
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="checklist[]" value="Format borang sesuai template" id="check4">
                                        <label class="form-check-label" for="check4">
                                            Format borang sesuai template
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="checklist[]" value="Tidak ada data yang kontradiktif" id="check5">
                                        <label class="form-check-label" for="check5">
                                            Tidak ada data yang kontradiktif
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Hasil Review -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Hasil Review <span class="text-danger">*</span></label>
                                <select name="hasil_review" class="form-select" id="hasilReview" required>
                                    <option value="">-- Pilih Hasil Review --</option>
                                    <option value="siap">✅ SIAP - Lanjut ke tahap AK</option>
                                    <option value="belum_siap">❌ BELUM SIAP - Perlu perbaikan</option>
                                </select>
                            </div>

                            <!-- Jumlah Pembayaran (jika siap) -->
                            <div class="mb-4" id="divPembayaran" style="display: none;">
                                <label class="form-label fw-bold">Jumlah Pembayaran <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="jumlah_pembayaran" class="form-control" placeholder="0" min="0" step="1000">
                                </div>
                                <small class="text-muted">
                                    Invoice akan dibuat otomatis setelah review
                                </small>
                            </div>

                            <!-- Catatan Review -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Catatan Review <span class="text-danger">*</span></label>
                                <textarea name="catatan_review" class="form-control" rows="5" placeholder="Berikan catatan detail hasil review..." required></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Submit Review
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Reset Form
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            <!-- ACTION: Verifikasi Pembayaran -->
            @if($pengajuan->status === 'pembayaran_diterima' && $pengajuan->pembayaran && $pengajuan->pembayaran->status_pembayaran === 'dibayar')
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-credit-card text-success"></i>
                        Aksi Diperlukan: Verifikasi Pembayaran
                    </h5>

                    <div class="alert alert-info alert-permanent">
                        <strong>Invoice:</strong> {{ $pengajuan->pembayaran->nomor_invoice }}<br>
                        <strong>Jumlah:</strong> Rp {{ number_format($pengajuan->pembayaran->jumlah_pembayaran, 0, ',', '.') }}<br>
                        <strong>Tanggal Pembayaran:</strong> {{ $pengajuan->pembayaran->tanggal_pembayaran->format('d M Y') }}
                    </div>

                    <form action="{{ route('de.pengajuan.verifikasi-pembayaran', $pengajuan->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan Verifikasi</label>
                            <textarea name="catatan_verifikasi" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="status" value="verified" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Verifikasi & Setujui
                            </button>
                            <button type="submit" name="status" value="ditolak" class="btn btn-danger">
                                <i class="bi bi-x-circle"></i> Tolak Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- ACTION: Approve Lanjut ke AK (Langkah 8) -->
            @if($pengajuan->status === 'borang_final_diterima')
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-check-all text-success"></i>
                        Aksi Diperlukan: Approve Lanjut ke Tahap AK
                    </h5>
                    <p class="mb-3">
                        Borang final telah diterima dan pembayaran telah diverifikasi.
                        Setujui untuk melanjutkan ke tahap AK/Asesmen Dokumen.
                    </p>

                    <div class="alert alert-warning alert-permanent">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Pastikan semua dokumen telah lengkap dan sesuai
                        sebelum menyetujui pengajuan ini ke tahap AK.
                    </div>

                    <form action="{{ route('de.pengajuan.approve-ak', $pengajuan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui pengajuan ini untuk lanjut ke tahap AK?')">
                        @csrf
                        <button type="submit" class="btn btn-success btn-md">
                            <i class="bi bi-check-circle"></i> Setujui & Lanjutkan ke Tahap AK
                        </button>
                    </form>
                </div>
            </div>
            @endif

            @if($pengajuan->status === 'lanjut_ke_ak')
            <div class="card action-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        Pengajuan Disetujui - Lanjut ke Tahap AK
                    </h5>

                    @if($pengajuan->asesmen)
                    {{-- Jika sudah ada asesmen --}}
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i>
                        <strong>Asesmen sudah dibuat:</strong> {{ $pengajuan->asesmen->name }}
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('asesmen.show', $pengajuan->asesmen->id) }}" class="btn btn-primary">
                            <i class="bi bi-eye"></i> Lihat Detail Asesmen
                        </a>
                        <a href="{{ route('asesmen.edit', $pengajuan->asesmen->id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-pencil"></i> Edit Asesmen
                        </a>
                    </div>
                    @else
                    {{-- Jika belum ada asesmen --}}
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Langkah Selanjutnya:</strong> Buat asesmen baru untuk proses AK/Asesmen Dokumen dan assign asesor/validator.
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold">Data yang akan digunakan:</h6>
                            <ul class="mb-0">
                                <li><strong>Program Studi:</strong> {{ $pengajuan->studyProgram->full_name }}</li>
                                <li><strong>Universitas:</strong> {{ $pengajuan->studyProgram->university->name }}</li>
                                <li><strong>Tahun:</strong> {{ $pengajuan->tahun_akreditasi }}</li>
                                <li><strong>Jenis:</strong> {{ ucfirst($pengajuan->jenis_akreditasi) }}</li>
                            </ul>
                        </div>
                    </div>

                    <a href="{{ route('asesmen.create', ['pengajuan_id' => $pengajuan->id]) }}" class="btn btn-success btn-lg">
                        <i class="bi bi-plus-circle"></i> Buat Asesmen & Assign Asesor
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Informasi Pengajuan -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pengajuan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Nomor Pengajuan</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Program Studi</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->studyProgram->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Jenjang</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->studyProgram->degreeLevel->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tahun Akreditasi</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->tahun_akreditasi }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Jenis Akreditasi</label>
                            <p class="fw-bold mb-0">{{ ucfirst($pengajuan->jenis_akreditasi) }}</p>
                        </div>
                        @if($pengajuan->pengaju)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Pengaju</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->pengaju->name }}</p>
                        </div>
                        @endif
                    </div>

                    @if($pengajuan->catatan_pengaju)
                    <hr>
                    <label class="text-muted small">Catatan Pengaju</label>
                    <p class="mb-0">{{ $pengajuan->catatan_pengaju }}</p>
                    @endif
                </div>
            </div>

            <!-- Pembayaran Info -->
            @if($pengajuan->pembayaran)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-credit-card"></i> Informasi Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Nomor Invoice</label>
                            <p class="fw-bold mb-0">{{ $pengajuan->pembayaran->nomor_invoice }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Jumlah</label>
                            <p class="fw-bold mb-0">
                                Rp {{ number_format($pengajuan->pembayaran->jumlah_pembayaran, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $pengajuan->pembayaran->status_pembayaran === 'verified' ? 'success' : 'warning' }}">
                                    {{ strtoupper($pengajuan->pembayaran->status_pembayaran) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Jatuh Tempo</label>
                            <p class="fw-bold mb-0">
                                {{ $pengajuan->pembayaran->tanggal_jatuh_tempo->format('d M Y') }}
                            </p>
                        </div>
                        @if($pengajuan->pembayaran->tanggal_pembayaran)
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Tanggal Pembayaran</label>
                            <p class="fw-bold mb-0">
                                {{ $pengajuan->pembayaran->tanggal_pembayaran->format('d M Y') }}
                            </p>
                        </div>
                        @endif
                        @if($pengajuan->pembayaran->verified_by)
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Diverifikasi Oleh</label>
                            <p class="fw-bold mb-0">
                                {{ $pengajuan->pembayaran->verifier->name }}
                            </p>
                        </div>
                        @endif
                    </div>

                    @if($pengajuan->pembayaran->catatan_verifikasi)
                    <hr>
                    <label class="text-muted small">Catatan Verifikasi</label>
                    <p class="mb-0">{{ $pengajuan->pembayaran->catatan_verifikasi }}</p>
                    @endif

                    @if($pengajuan->pembayaran->alasan_penolakan)
                    <hr>
                    <label class="text-muted small text-danger">Alasan Penolakan</label>
                    <p class="mb-0 text-danger">{{ $pengajuan->pembayaran->alasan_penolakan }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Review History -->
            @if($pengajuan->reviewKesiapan->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check"></i> Riwayat Review Kesiapan
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($pengajuan->reviewKesiapan->sortByDesc('tanggal_review') as $review)
                    <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge {{ $review->hasil_review === 'siap' ? 'bg-success' : 'bg-danger' }} me-2">
                                    {{ $review->hasil_review === 'siap' ? 'SIAP' : 'BELUM SIAP' }}
                                </span>
                                <small class="text-muted">Versi {{ $review->versi_review }}</small>
                            </div>
                            <small class="text-muted">
                                {{ $review->tanggal_review->format('d M Y H:i') }}
                            </small>
                        </div>
                        <p class="mb-2"><strong>Reviewer:</strong> {{ $review->reviewer->name }}</p>
                        <p class="mb-2"><strong>Catatan:</strong></p>
                        <p class="text-muted mb-2">{{ $review->catatan_review }}</p>

                        @if($review->checklist_kesiapan && count($review->checklist_kesiapan) > 0)
                        <p class="mb-1"><strong>Checklist:</strong></p>
                        <ul class="mb-0">
                            @foreach($review->checklist_kesiapan as $item)
                            <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Dokumen -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-folder"></i> Dokumen
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($pengajuan->dokumen->groupBy('jenis_dokumen') as $jenis => $docs)
                    <div class="mb-3">
                        <h6 class="fw-bold text-primary">
                            {{ str_replace('_', ' ', ucwords($jenis)) }}
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama File</th>
                                        <th>Versi</th>
                                        <th>Upload Oleh</th>
                                        <th>Tanggal</th>
                                        <th>Ukuran</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($docs as $doc)
                                    <tr>
                                        <td>
                                            {{ $doc->original_filename }}
                                            @if($doc->is_latest)
                                            <span class="badge bg-success">Latest</span>
                                            @endif
                                        </td>
                                        <td>v{{ $doc->versi }}</td>
                                        <td>{{ $doc->uploader->name }}</td>
                                        <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $doc->file_size_formatted }}</td>
                                        <td>
                                            <a href="{{ $doc->download_url }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted mb-0">Belum ada dokumen</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Timeline -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach([
                        ['date' => $pengajuan->tanggal_pengingat, 'label' => 'Pengingat'],
                        ['date' => $pengajuan->tanggal_surat_permohonan, 'label' => 'Surat Permohonan'],
                        ['date' => $pengajuan->tanggal_borang_dikirim, 'label' => 'Borang Dikirim'],
                        ['date' => $pengajuan->tanggal_draft_borang, 'label' => 'Draft Diterima'],
                        ['date' => $pengajuan->tanggal_review_kesiapan, 'label' => 'Review'],
                        ['date' => $pengajuan->tanggal_pembayaran, 'label' => 'Pembayaran'],
                        ['date' => $pengajuan->tanggal_borang_final, 'label' => 'Borang Final'],
                        ['date' => $pengajuan->tanggal_lanjut_ak, 'label' => 'Lanjut AK'],
                        ] as $item)
                        <div class="d-flex mb-3">
                            <div class="me-3">
                                @if($item['date'])
                                <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                <i class="bi bi-circle text-muted"></i>
                                @endif
                            </div>
                            <div>
                                <strong>{{ $item['label'] }}</strong>
                                @if($item['date'])
                                <br><small class="text-muted">{{ $item['date']->format('d M Y H:i') }}</small>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Log Aktivitas -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check"></i> Log Aktivitas
                    </h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($pengajuan->statusLog->sortByDesc('changed_at') as $log)
                    <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">
                                {{ $log->changed_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <p class="mb-1 small">
                            <span class="badge bg-secondary">{{ str_replace('_', ' ', $log->status_from) }}</span>
                            <i class="bi bi-arrow-right"></i>
                            <span class="badge bg-primary">{{ str_replace('_', ' ', $log->status_to) }}</span>
                        </p>
                        @if($log->keterangan)
                        <small class="text-muted">{{ $log->keterangan }}</small>
                        @endif
                        <br>
                        <small class="text-muted">Oleh: {{ $log->changedBy->name }}</small>
                    </div>
                    @empty
                    <p class="text-muted small mb-0">Belum ada aktivitas</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Show/hide pembayaran field based on hasil review
    document.getElementById('hasilReview').addEventListener('change', function() {
        const divPembayaran = document.getElementById('divPembayaran');
        if (this.value === 'siap') {
            divPembayaran.style.display = 'block';
            divPembayaran.querySelector('input').required = true;
        } else {
            divPembayaran.style.display = 'none';
            divPembayaran.querySelector('input').required = false;
        }
    });

</script>
@endpush
@endsection
