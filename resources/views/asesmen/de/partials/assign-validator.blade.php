{{-- ASSIGN VALIDATOR SECTION --}}
@if(in_array($pengajuan->status, [
'borang_online_selesai',
'borang_validation_pending',
'borang_in_validation',
'borang_revision_required',
'borang_validated'
]) && $pengajuan->latestBorangImport)

<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-clipboard-check"></i>
                Validasi Dokumen
            </h5>
            @if($canAssignValidator)
            <a href="{{ route('de.pengajuan.assign-validator.form', $pengajuan->id) }}" class="btn btn-light btn-sm">
                <i class="bi bi-person-plus"></i>
                {{ $currentValidator ? 'Reassign Validator' : 'Assign Validator' }}
            </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($currentValidator)
        {{-- Validator Info --}}
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="text-center">
                    <div class="avatar-circle mx-auto mb-2" style="width: 80px; height: 80px; font-size: 2rem;">
                        {{ substr($currentValidator->user->name, 0, 1) }}
                    </div>
                    <h6 class="fw-bold">{{ $currentValidator->user->name }}</h6>
                    <small class="text-muted text-wrap">{{ $currentValidator->user->email }}</small>
                </div>
            </div>

            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Role</label>
                        <p class="fw-bold mb-0">
                            <span class="badge bg-success">{{ $currentValidator->role_selected->alias }}</span>
                        </p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Status Penawaran</label>
                        <p class="mb-0">
                            @php
                            $penawaranBadge = match($currentValidator->status_penawaran) {
                            'accepted' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Diterima'],
                            'rejected' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Ditolak'],
                            'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Menunggu'],
                            default => ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Unknown'],
                            };
                            @endphp
                            <span class="badge bg-{{ $penawaranBadge['class'] }}">
                                <i class="bi bi-{{ $penawaranBadge['icon'] }}"></i>
                                {{ $penawaranBadge['text'] }}
                            </span>
                        </p>
                    </div>

                    @if($currentValidator->status_penawaran === 'accepted')
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Status Pekerjaan</label>
                        <p class="mb-0">
                            <span class="badge bg-info">
                                {{ $currentValidator->status_label ?? 'Belum Mulai' }}
                            </span>
                        </p>
                    </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2 mt-3">
                    @if($currentValidator->status_penawaran === 'pending')
                    <span class="badge bg-warning">
                        <i class="bi bi-hourglass-split"></i>
                        Menunggu validator menerima penawaran
                    </span>
                    @elseif($currentValidator->status_penawaran === 'rejected')
                    <a href="{{ route('de.pengajuan.assign-validator.form', $pengajuan->id) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-arrow-repeat"></i> Assign Validator Baru
                    </a>
                    @elseif($currentValidator->status_penawaran === 'accepted')
                    @if($currentValidator->borangValidation)
                    <a href="{{ route('validator.borang.show', $currentValidator->id) }}" class="btn btn-primary btn-sm" target="_blank">
                        <i class="bi bi-eye"></i> Lihat Progres Validasi
                    </a>
                    @endif
                    @endif
                </div>

                {{-- Validation Details --}}
                @if($currentValidator->borangValidation && $currentValidator->status_pekerjaan !== 'not_started')
                <div class="card bg-light mt-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-clipboard-data"></i> Detail Validasi
                        </h6>

                        @if($currentValidator->borangValidation->catatan_validator)
                        <div class="mb-2">
                            <strong>Catatan Validator:</strong>
                            <p class="mb-0">{{ $currentValidator->borangValidation->catatan_validator }}</p>
                        </div>
                        @endif

                        @if($currentValidator->borangValidation->revision_points && count($currentValidator->borangValidation->revision_points) > 0)
                        <div class="mb-2">
                            <strong>Poin Revisi ({{ count($currentValidator->borangValidation->revision_points) }}):</strong>
                            <ul class="mb-0">
                                @foreach($currentValidator->borangValidation->revision_points as $point)
                                <li>{{ $point }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        @else
        {{-- No validator assigned --}}
        <div class="text-center py-4">
            <i class="bi bi-person-x" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3 mb-3">
                Belum ada validator yang di-assign untuk review LED
            </p>
            @if($canAssignValidator)
            <a href="{{ route('de.pengajuan.assign-validator.form', $pengajuan->id) }}" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Assign Validator Sekarang
            </a>
            @else
            <p class="text-muted">
                <i class="bi bi-info-circle"></i>
                LED harus diselesaikan prodi terlebih dahulu sebelum assign validator
            </p>
            @endif
        </div>
        @endif
    </div>
</div>
@endif
