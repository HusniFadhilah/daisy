@extends('layouts.template.app')

@section('title', 'Tugaskan Validator LED - ' . $pengajuan->nomor_pengajuan)

@section('content')
<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>
                <i class="bi bi-person-check"></i>
                Tugaskan Validator LED
            </h2>
            <p class="text-muted mb-0">
                {{ $pengajuan->nomor_pengajuan }} - {{ $pengajuan->studyProgram->name }}
            </p>
        </div>
        <a href="{{ route('de.penerimaan-dokumen.show', $pengajuan->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        {{-- Left: Pengajuan Info --}}
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Permohonan Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Nomor Permohonan Akreditasi</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Program Studi</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->studyProgram->name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Jenjang</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->studyProgram->degreeLevel->name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Universitas</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->studyProgram->university->name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Status Permohonan</label>
                        <p>
                            <span class="badge {{ $pengajuan->status_badge_class }} text-wrap">
                                {{ $pengajuan->status_label }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Borang Info --}}
            @if($pengajuan->latestBorangImport)
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> Info LED
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="text-muted small">File</label>
                        <p class="mb-0">{{ $pengajuan->latestBorangImport->original_filename }}</p>
                    </div>
                    <div class="mb-2">
                        <label class="text-muted small">Kelengkapan</label>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-{{ $pengajuan->latestBorangImport->completion_percentage >= 80 ? 'success' : 'warning' }}" style="width: {{ $pengajuan->latestBorangImport->completion_percentage }}%">
                                {{ $pengajuan->latestBorangImport->completion_percentage }}%
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="text-muted small">Status</label>
                        <p>
                            <span class="badge bg-{{ $pengajuan->latestBorangImport->status === 'completed' ? 'success' : 'warning' }}">
                                {{ strtoupper($pengajuan->latestBorangImport->status) }}
                            </span>
                        </p>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small">Diproses</label>
                        <p class="mb-0">{{ $pengajuan->latestBorangImport->imported_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Right: Assign Form --}}
        <div class="col-md-8">
            {{-- Current Assignment (if exists) --}}
            @if($currentAssignment)
            <div class="alert alert-warning alert-permanent mb-4">
                <h5 class="alert-heading">
                    <i class="bi bi-exclamation-triangle"></i>
                    Validator Sudah Ditugaskan
                </h5>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Validator:</strong>
                        <p class="mb-1">{{ $currentAssignment->user->name }}</p>
                        <p class="mb-1"><small>{{ $currentAssignment->user->email }}</small></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Status Penawaran:</strong>
                        <p>
                            <span class="badge bg-{{ $currentAssignment->status_penawaran === 'accepted' ? 'success' : ($currentAssignment->status_penawaran === 'rejected' ? 'danger' : 'warning') }}">
                                {{ strtoupper($currentAssignment->status_penawaran) }}
                            </span>
                        </p>
                        @if($currentAssignment->status_penawaran === 'accepted')
                        <strong>Status Pekerjaan:</strong>
                        <p>
                            <span class="badge bg-info">
                                {{ $currentAssignment->status_label ?? 'Belum Mulai' }}
                            </span>
                        </p>
                        @endif
                    </div>
                </div>

                {{-- ✅ ADD: Action Buttons based on status --}}
                <div class="mt-3 pt-3 border-top">
                    @if($currentAssignment->status_penawaran === 'pending')
                    <div class="alert alert-info alert-permanent mb-3">
                        <i class="bi bi-info-circle"></i>
                        Validator sedang menunggu konfirmasi. Anda dapat <strong>membatalkan</strong> atau <strong>tugaskan ulang</strong> jika diperlukan.
                    </div>

                    <div class="d-flex gap-2">
                        <form action="{{ route('de.penerimaan-dokumen.cancel-validator', $currentAssignment->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Batalkan penugasan validator ini? Email penawaran akan dibatalkan.')">
                                <i class="bi bi-x-circle"></i> Batalkan Penugasan
                            </button>
                        </form>

                        <button type="button" class="btn btn-warning btn-sm" onclick="document.getElementById('assignForm').scrollIntoView({ behavior: 'smooth' })">
                            <i class="bi bi-arrow-repeat"></i> Tugaskan Ulang ke Validator Lain
                        </button>
                    </div>

                    @elseif($currentAssignment->status_penawaran === 'rejected')
                    <div class="alert alert-danger alert-permanent mb-3">
                        <i class="bi bi-x-circle"></i>
                        Validator menolak penawaran. Silakan tugaskan validator baru.
                        @if($currentAssignment->response_note)
                        <br><strong>Alasan:</strong> "{{ $currentAssignment->response_note }}"
                        @endif
                    </div>

                    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('assignForm').scrollIntoView({ behavior: 'smooth' })">
                        <i class="bi bi-person-plus"></i> Tugaskan Validator Baru
                    </button>

                    @elseif($currentAssignment->status_penawaran === 'accepted')
                    <div class="alert alert-success alert-permanent mb-3">
                        <i class="bi bi-check-circle"></i>
                        Validator sudah menerima penawaran dan sedang bekerja.
                    </div>

                    @if($currentAssignment->responded_at)
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> Diterima pada: {{ $currentAssignment->responded_at->format('d M Y H:i') }}
                    </small>
                    @endif

                    <div class="mt-2">
                        <a href="{{ route('validator.borang.show', $currentAssignment->id) }}" class="btn btn-info btn-sm" target="_blank">
                            <i class="bi bi-eye"></i> Lihat Progress Validasi
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Assign Form --}}
            <div class="card" id="assignForm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-plus"></i>
                        {{ $currentAssignment ? 'Tugaskan Ulang Validator Baru' : 'Tugaskan Validator' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form id="assignValidatorForm">
                        @csrf

                        {{-- Pilih Validator --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Pilih Validator <span class="text-danger">*</span>
                            </label>
                            <select name="id_validator" id="id_validator" class="form-select" required>
                                <option value="">-- Pilih Validator --</option>
                                @forelse($validators as $validator)
                                <option value="{{ $validator->id }}" data-email="{{ $validator->email }}" {{ $currentAssignment && $currentAssignment->id_user == $validator->id ? 'disabled' : '' }}>
                                    {{ $validator->name }} ({{ $validator->email }})
                                    {{ $currentAssignment && $currentAssignment->id_user == $validator->id ? '- CURRENT' : '' }}
                                </option>
                                @empty
                                <option value="" disabled>Tidak ada validator tersedia</option>
                                @endforelse
                            </select>
                            <small class="text-muted">
                                Pilih validator yang akan mereview LED
                            </small>
                        </div>

                        <div id="validatorPreview" style="display: none;" class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="fw-bold">Validator Terpilih:</h6>
                                <p class="mb-0">
                                    <i class="bi bi-person"></i> <strong id="previewName">-</strong><br>
                                    <i class="bi bi-envelope"></i> <span id="previewEmail">-</span>
                                </p>
                            </div>
                        </div>

                        {{-- Catatan DE --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Catatan untuk Validator (Opsional)
                            </label>
                            <textarea name="catatan_de" id="catatan_de" class="form-control" rows="3" placeholder="Tambahkan catatan atau instruksi khusus untuk validator..."></textarea>
                            <small class="text-muted">
                                Catatan ini akan dikirim bersama email penawaran
                            </small>
                        </div>

                        {{-- Info Box --}}
                        <div class="alert alert-info alert-permanent">
                            <strong><i class="bi bi-info-circle"></i> Yang Akan Terjadi:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Sistem akan membuat asesmen (jika belum ada)</li>
                                <li>Penugasan dengan <code>jenis_asesmen = 'dokumen'</code> akan dibuat</li>
                                <li>Email penawaran akan dikirim ke validator</li>
                                <li>Status Permohonan akreditasi akan diupdate ke <code>Validator LED Ditugaskan</code></li>
                                @if($currentAssignment && $currentAssignment->status_penawaran === 'pending')
                                <li class="text-warning"><strong>Penugasan lama yang pending akan dihapus</strong></li>
                                @endif
                            </ul>
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success" id="btnSubmit">
                                <i class="bi bi-send"></i>
                                {{ $currentAssignment ? 'Tugaskan Ulang Validator' : 'Tugaskan Validator' }}
                            </button>
                            <a href="{{ route('de.penerimaan-dokumen.show', $pengajuan->id) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Help Card --}}
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-question-circle"></i> Persyaratan Validator LED
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Minimal <strong>1 validator</strong> untuk review LED</li>
                        <li>Validator harus memiliki role <code>validator</code> di sistem</li>
                        <li>Validator harus menerima penawaran sebelum bisa mulai review</li>
                        <li>LED harus sudah di-submit dan status <code>Diterima</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Submit form
    document.getElementById('assignValidatorForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = Object.fromEntries(formData);

        // Validation
        if (!data.id_validator) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: 'Mohon pilih validator terlebih dahulu!'
            });
            return;
        }

        // Confirm
        const selectedOption = document.getElementById('id_validator').selectedOptions[0];
        const validatorName = selectedOption.text.split('(')[0].trim();

        const result = await Swal.fire({
            icon: 'question'
            , title: 'Konfirmasi Penugasan'
            , html: `Tugaskan validator <strong>${validatorName}</strong> untuk review LED?`
            , showCancelButton: true
            , confirmButtonText: 'Ya, Tugaskan!'
            , cancelButtonText: 'Batal'
        });

        if (!result.isConfirmed) return;

        const btnSubmit = document.getElementById('btnSubmit');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

        try {
            const response = await fetch('{{ route("de.penerimaan-dokumen.assign-validator", $pengajuan->id) }}', {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    , 'Accept': 'application/json'
                }
                , body: JSON.stringify(data)
            });

            const responseData = await response.json();

            if (!responseData.meta || responseData.meta.status !== 'success') {
                throw new Error(responseData.meta ? responseData.meta.message : 'Request gagal');
            }

            await Swal.fire({
                icon: 'success'
                , title: 'Berhasil!'
                , html: responseData.meta.message
                , timer: 2000
                , showConfirmButton: false
            });

            window.location.href = '{{ route("de.penerimaan-dokumen.show", $pengajuan->id) }}';
        } catch (error) {
            console.error('Assignment error:', error);

            Swal.fire({
                icon: 'error'
                , title: 'Gagal'
                , text: error.message
            });

            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-send"></i> {{ $currentAssignment ? "Tugaskan Ulang Validator" : "Tugaskan Validator" }}';
        }
    });

    document.getElementById('id_validator').addEventListener('change', function() {
        const preview = document.getElementById('validatorPreview');
        const selectedOption = this.options[this.selectedIndex];

        if (this.value) {
            document.getElementById('previewName').textContent = selectedOption.text.split('(')[0].trim();
            document.getElementById('previewEmail').textContent = selectedOption.dataset.email;
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    });

</script>
@endpush
@endsection
