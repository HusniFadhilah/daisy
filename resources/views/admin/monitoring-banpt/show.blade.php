@extends('layouts.template.app')

@section('title', 'Detail Perubahan BAN-PT - DAISY LAMDEPILAR')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <a href="{{ route('admin.monitoring-banpt.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle"></i> {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show"><i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-x-circle"></i> {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Header info --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Detail Perubahan BAN-PT
                <span class="badge bg-{{ $change->status_badge_class }} ms-2">{{ $change->status_label }}</span>
            </h5>
            <small class="text-muted">Terdeteksi: {{ $change->detected_at->format('d/m/Y H:i') }}</small>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <dt>Perguruan Tinggi</dt>
                    <dd>{{ $studyProgram?->university?->name ?? $change->old_university_name ?? '-' }}</dd>
                </div>
                <div class="col-md-3">
                    <dt>Program Studi</dt>
                    <dd>{{ $studyProgram?->name ?? $change->old_program_studi ?? '-' }}</dd>
                </div>
                <div class="col-md-3">
                    <dt>Jenjang</dt>
                    <dd>{{ $studyProgram?->degreeLevel?->alias ?? $change->old_jenjang ?? '-' }}</dd>
                </div>
            </div>

            @if($change->isConflict() && $change->conflict_reason)
            <div class="alert alert-danger mt-3 mb-0">
                <strong><i class="bi bi-flag"></i> Alasan Konflik:</strong> {{ $change->conflict_reason }}
            </div>
            @endif
        </div>
    </div>

    {{-- Peringatan blocking --}}
    @if($blockReason)
    <div class="alert alert-warning">
        <i class="bi bi-shield-exclamation"></i>
        <strong>Perhatian:</strong> {{ $blockReason }}
        Perubahan ini tidak dapat diterapkan secara langsung.
    </div>
    @endif

    {{-- Perbandingan data --}}
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card h-100 border-secondary">
                <div class="card-header bg-light">
                    <i class="bi bi-database"></i> Data Lokal Saat Ini
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Peringkat</dt>
                        <dd class="col-sm-7">{{ $change->old_peringkat_akreditasi ?? '<em class="text-muted">Kosong</em>' }}</dd>

                        <dt class="col-sm-5">Tgl. Kedaluwarsa</dt>
                        <dd class="col-sm-7">{{ $change->old_tanggal_kedaluwarsa?->format('d/m/Y') ?? '-' }}</dd>

                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7">
                            @php $badge = match($change->old_status_kedaluwarsa) {
                                'Aktif' => 'success', 'Kedaluwarsa' => 'danger', default => 'secondary'
                            }; @endphp
                            <span class="badge bg-{{ $badge }}">{{ $change->old_status_kedaluwarsa ?? '-' }}</span>
                        </dd>

                        @if($studyProgram)
                        <dt class="col-sm-5">Sumber Data</dt>
                        <dd class="col-sm-7">{{ $studyProgram->akreditasi_source ?? '-' }}</dd>

                        <dt class="col-sm-5">Terakhir Cek BAN-PT</dt>
                        <dd class="col-sm-7">{{ $studyProgram->last_banpt_checked_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-cloud-download"></i> Data BAN-PT Terbaru
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Peringkat</dt>
                        <dd class="col-sm-7">
                            @if($change->old_peringkat_akreditasi !== $change->new_peringkat_akreditasi)
                                <strong class="text-primary">{{ $change->new_peringkat_akreditasi ?? '-' }}</strong>
                                <span class="badge bg-info ms-1">Berubah</span>
                            @else
                                {{ $change->new_peringkat_akreditasi ?? '-' }}
                            @endif
                        </dd>

                        <dt class="col-sm-5">Tgl. Kedaluwarsa</dt>
                        <dd class="col-sm-7">
                            @php
                                $oldTgl = $change->old_tanggal_kedaluwarsa?->format('d/m/Y');
                                $newTgl = $change->new_tanggal_kedaluwarsa?->format('d/m/Y');
                            @endphp
                            @if($oldTgl !== $newTgl)
                                <strong class="text-primary">{{ $newTgl ?? '-' }}</strong>
                                <span class="badge bg-info ms-1">Berubah</span>
                            @else
                                {{ $newTgl ?? '-' }}
                            @endif
                        </dd>

                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7">
                            @php $badge2 = match($change->new_status_kedaluwarsa) {
                                'Aktif' => 'success', 'Kedaluwarsa' => 'danger', default => 'secondary'
                            }; @endphp
                            <span class="badge bg-{{ $badge2 }}">{{ $change->new_status_kedaluwarsa ?? '-' }}</span>
                        </dd>

                        <dt class="col-sm-5">Label PT (BAN-PT)</dt>
                        <dd class="col-sm-7"><code>{{ $change->banpt_pt_label ?? '-' }}</code></dd>

                        <dt class="col-sm-5">Label PS (BAN-PT)</dt>
                        <dd class="col-sm-7"><code>{{ $change->banpt_ps_label ?? '-' }}</code></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    {{-- Raw BAN-PT Payload --}}
    <div class="card mb-3">
        <div class="card-header">
            <a class="text-decoration-none" data-bs-toggle="collapse" href="#banptPayload">
                <i class="bi bi-code-slash"></i> Raw Payload BAN-PT
                <i class="bi bi-chevron-down float-end"></i>
            </a>
        </div>
        <div class="collapse" id="banptPayload">
            <div class="card-body">
                <pre class="bg-light p-3 rounded small" style="max-height:300px;overflow-y:auto">{{ json_encode($change->banpt_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    </div>

    {{-- Audit trail --}}
    @if($change->isApplied() || $change->isIgnored())
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-person-check"></i> Audit</div>
        <div class="card-body">
            <dl class="row mb-0">
                @if($change->isApplied())
                <dt class="col-sm-3">Diterapkan oleh</dt>
                <dd class="col-sm-9">{{ $change->appliedBy?->name ?? '-' }} &mdash; {{ $change->applied_at?->format('d/m/Y H:i') }}</dd>
                @endif
                @if($change->isIgnored())
                <dt class="col-sm-3">Diabaikan oleh</dt>
                <dd class="col-sm-9">{{ $change->ignoredBy?->name ?? '-' }} &mdash; {{ $change->ignored_at?->format('d/m/Y H:i') }}</dd>
                @endif
            </dl>
        </div>
    </div>
    @endif

    {{-- Tombol aksi --}}
    @if($change->isPending())
    <div class="card">
        <div class="card-header">Aksi</div>
        <div class="card-body d-flex gap-2 flex-wrap">

            @if(!$blockReason)
            <form method="POST" action="{{ route('admin.monitoring-banpt.apply', $change) }}">
                @csrf
                <button type="submit" class="btn btn-success"
                    onclick="return confirm('Terapkan perubahan BAN-PT ke data program studi? Aksi ini akan mengubah peringkat dan tanggal kedaluwarsa.')">
                    <i class="bi bi-check-lg"></i> Terapkan
                </button>
            </form>
            @else
            <button class="btn btn-success" disabled title="{{ $blockReason }}">
                <i class="bi bi-check-lg"></i> Terapkan
            </button>
            @endif

            <form method="POST" action="{{ route('admin.monitoring-banpt.ignore', $change) }}">
                @csrf
                <button type="submit" class="btn btn-outline-warning"
                    onclick="return confirm('Abaikan perubahan ini? Data lokal tidak akan diubah.')">
                    <i class="bi bi-x-lg"></i> Abaikan
                </button>
            </form>

            <button type="button" class="btn btn-outline-danger"
                data-bs-toggle="modal" data-bs-target="#conflictModal">
                <i class="bi bi-flag"></i> Tandai Konflik
            </button>
        </div>
    </div>

    {{-- Modal Konflik --}}
    <div class="modal fade" id="conflictModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.monitoring-banpt.conflict', $change) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tandai Konflik</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Alasan konflik (opsional)</label>
                        <textarea name="conflict_reason" class="form-control" rows="3"
                            placeholder="Jelaskan mengapa perubahan ini dianggap konflik..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tandai Konflik</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
