{{-- ============================================
     SECTION 1: HANDLE BORANG REVISION (from validator)
     ============================================ --}}
@if($pengajuan->status === 'borang_revision_required')
@php
$validatorAssignment = $pengajuan->borangValidators()
->where('status_pekerjaan', 'revision_required')
->with(['borangValidation', 'user'])
->first();
@endphp

@if($validatorAssignment)
<div class="card action-card mb-4 border-warning">
    <div class="card-header bg-warning">
        <h5 class="mb-0">
            <i class="bi bi-exclamation-triangle"></i>
            Aksi Diperlukan: Borang Perlu Revisi
        </h5>
    </div>
    <div class="card-body">
        <div class="alert alert-warning alert-permanent mb-4">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Validator meminta revisi pada borang ini.</strong><br>
            Kirim notifikasi ke prodi untuk memperbaiki borang sesuai catatan validator.
        </div>

        {{-- Validator Info --}}
        <div class="card bg-light mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Validator</label>
                        <p class="fw-bold mb-0">{{ $validatorAssignment->user->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Tanggal Review</label>
                        <p class="mb-0">{{ $validatorAssignment->updated_at->format('d M Y H:i') }}</p>
                    </div>
                </div>

                @if($validatorAssignment->borangValidation)
                <div class="mb-3">
                    <h6 class="fw-bold">Catatan Validator:</h6>
                    <div class="alert alert-light">
                        {{ $validatorAssignment->borangValidation->catatan_validator }}
                    </div>
                </div>

                @if($validatorAssignment->borangValidation->revision_points && count($validatorAssignment->borangValidation->revision_points) > 0)
                <div class="mb-3">
                    <h6 class="fw-bold">Poin Revisi ({{ count($validatorAssignment->borangValidation->revision_points) }}):</h6>
                    <ol class="mb-0">
                        @foreach($validatorAssignment->borangValidation->revision_points as $point)
                        <li class="mb-2">{{ $point }}</li>
                        @endforeach
                    </ol>
                </div>
                @endif
                @endif
            </div>
        </div>

        {{-- Action Button --}}
        <a href="{{ route('de.pengajuan.handle-borang-revision', $pengajuan->id) }}" class="btn btn-warning" onclick="return confirm('Kirim notifikasi revisi ke prodi?')">
            <i class="bi bi-send"></i> Kirim Notifikasi Revisi ke Prodi
        </a>
    </div>
</div>
@endif
@endif

{{-- ============================================
     SECTION 2: REASSIGN AFTER PRODI REVISION
     ============================================ --}}
@if($pengajuan->status === 'borang_online_selesai' && $pengajuan->borangValidators()->where('status_pekerjaan', 'revision_required')->exists())
<div class="card action-card mb-4 border-info">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <i class="bi bi-arrow-repeat"></i>
            Aksi Diperlukan: Borang Sudah Direvisi Prodi
        </h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info alert-permanent mb-4">
            <i class="bi bi-info-circle"></i>
            <strong>Prodi telah menyelesaikan revisi borang.</strong><br>
            Kembalikan borang ke validator untuk review ulang.
        </div>

        @php
        $previousValidator = $pengajuan->borangValidators()
        ->where('status_pekerjaan', 'revision_required')
        ->with('user')
        ->first();
        @endphp

        @if($previousValidator)
        <div class="card bg-light mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-2">Validator Sebelumnya:</h6>
                <p class="mb-0">
                    <i class="bi bi-person"></i> <strong>{{ $previousValidator->user->name }}</strong><br>
                    <i class="bi bi-envelope"></i> {{ $previousValidator->user->email }}
                </p>
            </div>
        </div>
        @endif

        <form action="{{ route('de.pengajuan.reassign-after-revision', $pengajuan->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success" onclick="return confirm('Kembalikan borang ke validator {{ $previousValidator ? $previousValidator->user->name : '' }} untuk review ulang?')">
                <i class="bi bi-arrow-repeat"></i> Kembalikan ke Validator
            </button>
        </form>
    </div>
</div>
@endif

{{-- ============================================
     SECTION 3: REVIEW KESIAPAN (ONLY AFTER borang_validated)
     ============================================ --}}
@if($pengajuan->status === 'borang_validated')
@php
$latestImport = $pengajuan->latestBorangImport;
$draftBorang = $pengajuan->dokumen->where('jenis_dokumen', 'draft_borang')->where('is_latest', true)->first();
@endphp

<div class="card action-card mb-4 border-success">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <i class="bi bi-clipboard-check"></i>
            Aksi Diperlukan: Review Kesiapan LED
        </h5>
    </div>
    <div class="card-body">
        {{-- Alert Status --}}
        <div class="alert alert-success alert-permanent mb-4">
            <i class="bi bi-check-circle"></i>
            <strong>LED telah divalidasi oleh validator.</strong><br>
            Silakan review kesiapan LED sebelum melanjutkan ke tahap pembayaran.
        </div>

        {{-- Preview & Form Online Links --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card bg-primary bg-opacity-10 border-primary h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-eye text-white fs-1 mb-3 d-block"></i>
                        <h6 class="fw-bold text-white">Preview LED</h6>
                        <p class="text-white small mb-3">
                            Lihat preview LED yang sudah diproses
                        </p>
                        @if($latestImport)
                        <a href="{{ route('de.pengajuan.borang-view', [$pengajuan->id, $latestImport->id]) }}" class="btn btn-primary" target="_blank">
                            <i class="bi bi-eye"></i> Lihat Preview
                        </a>
                        @else
                        <button class="btn btn-light" disabled>
                            <i class="bi bi-eye-slash"></i> Belum Diproses
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card bg-info bg-opacity-10 border-info h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-pencil-square fs-1 text-info mb-3 d-block"></i>
                        <h6 class="fw-bold">Form Isian Online</h6>
                        <p class="text-muted small mb-3">
                            Lihat form online yang diisi prodi
                        </p>
                        <a href="{{ route('pengajuan.borang-online', $pengajuan->id) }}" class="btn btn-info" target="_blank">
                            <i class="bi bi-pencil-square"></i> Lihat Form Online
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Validation Summary --}}
        @if($currentValidator && $currentValidator->borangValidation)
        <div class="card bg-light mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-shield-check"></i> Hasil Validasi
                </h6>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="text-muted small">Validator</label>
                        <p class="mb-0"><strong>{{ $currentValidator->user->name }}</strong></p>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="text-muted small">Tanggal Validasi</label>
                        <p class="mb-0">{{ $currentValidator->approved_at ? $currentValidator->approved_at->format('d M Y H:i') : '-' }}</p>
                    </div>
                </div>

                @if($currentValidator->borangValidation->catatan_validator)
                <div class="mt-3 pt-3 border-top">
                    <label class="text-muted small">Catatan Validator:</label>
                    <p class="mb-0">{{ $currentValidator->borangValidation->catatan_validator }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Download Draft Document --}}
        @if($draftBorang)
        <div class="card bg-light mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-file-earmark-word"></i> Dokumen Draft
                </h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1"><strong>{{ $draftBorang->original_filename }}</strong></p>
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> {{ $draftBorang->created_at->format('d M Y H:i') }} |
                            <i class="bi bi-hdd"></i> {{ $draftBorang->file_size_formatted ?? '-' }}
                        </small>
                    </div>
                    <a href="{{ route('pengajuan.borang.export-docx', $pengajuan->id) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- ============================================
             FORM REVIEW KESIAPAN
             ============================================ --}}
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="bi bi-clipboard-check"></i> Form Review Kesiapan LED
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('de.pengajuan.review', $pengajuan->id) }}" method="POST" id="formReviewKesiapan">
                    @csrf

                    {{-- Hasil Review --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Hasil Review <span class="text-danger">*</span>
                        </label>
                        <select name="hasil_review" class="form-select" id="hasilReview" required>
                            <option value="">-- Pilih Hasil Review --</option>
                            <option value="siap">✅ SIAP - Lanjut ke Pembayaran</option>
                            <option value="belum_siap">❌ BELUM SIAP - Perlu Perbaikan</option>
                        </select>
                    </div>

                    {{-- Catatan Review --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Catatan Review <span class="text-danger">*</span>
                        </label>
                        <textarea name="catatan_review" class="form-control" rows="5" required placeholder="Berikan catatan detail tentang hasil review (minimal 20 karakter)"></textarea>
                    </div>

                    {{-- Checklist Kesiapan --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Checklist Kesiapan</label>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="checklist[kelengkapan_data]" value="1" id="check1">
                                    <label class="form-check-label" for="check1">
                                        Kelengkapan data memenuhi standar
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="checklist[kualitas_narasi]" value="1" id="check2">
                                    <label class="form-check-label" for="check2">
                                        Kualitas narasi/deskripsi
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="checklist[kelengkapan_tabel]" value="1" id="check3">
                                    <label class="form-check-label" for="check3">
                                        Kelengkapan data tabel
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="checklist[kesesuaian_format]" value="1" id="check4">
                                    <label class="form-check-label" for="check4">
                                        Kesesuaian format
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pembayaran Section (Only if SIAP) --}}
                    <div id="divPembayaran" style="display: none;">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-credit-card"></i> Generate Invoice Pembayaran
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info alert-permanent mb-3">
                                    <i class="bi bi-info-circle"></i>
                                    Invoice pembayaran akan otomatis digenerate setelah submit review "SIAP".
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        Jumlah Pembayaran (Rp) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="jumlah_pembayaran" class="form-control" value="5000000" step="100000" min="0">
                                    <small class="text-muted">Default: Rp 5.000.000,-</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="d-flex gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-send"></i> Submit Review Kesiapan
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Show/hide pembayaran based on hasil review
    const hasilReviewSelect = document.getElementById('hasilReview');
    if (hasilReviewSelect) {
        hasilReviewSelect.addEventListener('change', function() {
            const divPembayaran = document.getElementById('divPembayaran');
            const jumlahInput = divPembayaran.querySelector('input[name="jumlah_pembayaran"]');

            if (this.value === 'siap') {
                divPembayaran.style.display = 'block';
                jumlahInput.required = true;
            } else {
                divPembayaran.style.display = 'none';
                jumlahInput.required = false;
            }
        });
    }

    // Form validation
    const formReview = document.getElementById('formReviewKesiapan');
    if (formReview) {
        formReview.addEventListener('submit', function(e) {
            const hasil = document.getElementById('hasilReview').value;
            const catatan = document.querySelector('textarea[name="catatan_review"]').value;

            if (!hasil) {
                e.preventDefault();
                alert('Mohon pilih hasil review!');
                return false;
            }

            if (catatan.length < 20) {
                e.preventDefault();
                alert('Catatan review minimal 20 karakter!');
                return false;
            }

            const confirmMsg = hasil === 'siap' ?
                'Apakah Anda yakin LED SIAP dan akan melanjutkan ke pembayaran?' :
                'Apakah Anda yakin LED BELUM SIAP dan perlu perbaikan dari prodi?';

            if (!confirm(confirmMsg)) {
                e.preventDefault();
                return false;
            }
        });
    }

</script>
@endpush
@endif
