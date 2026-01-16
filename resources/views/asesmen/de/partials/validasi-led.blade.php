{{-- HANDLE BORANG REVISION --}}
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
            Aksi Diperlukan: LED Perlu Revisi
        </h5>
    </div>
    <div class="card-body">
        <div class="alert alert-warning alert-permanent mb-4">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Validator meminta revisi pada LED.</strong><br>
            Kirim notifikasi ke prodi untuk memperbaiki sesuai catatan validator.
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

{{-- REASSIGN AFTER PRODI REVISION --}}
@if($pengajuan->status === 'borang_online_selesai' && $pengajuan->borangValidators()->where('status_pekerjaan', 'revision_required')->exists())
<div class="card action-card mb-4 border-info">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <i class="bi bi-arrow-repeat"></i>
            Aksi Diperlukan: LED Sudah Direvisi Prodi
        </h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info alert-permanent mb-4">
            <i class="bi bi-info-circle"></i>
            <strong>Prodi telah menyelesaikan revisi LED.</strong><br>
            Kembalikan ke validator untuk review ulang.
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
            <button type="submit" class="btn btn-success" onclick="return confirm('Kembalikan LED ke validator untuk review ulang?')">
                <i class="bi bi-arrow-repeat"></i> Kembalikan ke Validator
            </button>
        </form>
    </div>
</div>
@endif
