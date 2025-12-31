@extends('layouts.template.app')

@section('title', 'Dashboard Validasi')

@push('styles')
<style>
    .asesmen-validasi-card {
        transition: all 0.3s ease;
        border: 2px solid #e0e0e0;
    }

    .asesmen-validasi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-color: var(--primary);
    }

    .asesor-list-item {
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 10px;
        transition: all 0.2s ease;
    }

    .asesor-list-item:hover {
        background: #f8f9fa;
        border-color: var(--primary);
    }

    .avatar-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #932136, #870820);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 20px;
    }

    .stat-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .pending-asesor-item {
        padding: 12px;
        border: 1px solid #ffc107;
        border-radius: 6px;
        background: #fff9e6;
        margin-bottom: 8px;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="mb-4">
        <h2><i class="bi bi-check2-square"></i> Dashboard Validasi</h2>
        <p class="text-muted mb-0">Validasi penilaian asesor untuk asesmen yang ditugaskan</p>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center stat-card" data-bs-toggle="modal" data-bs-target="#modalAsesorSubmitted">
                <div class="card-body">
                    <h3 class="text-warning mb-0">{{ $stats['total_asesor_submitted'] }}</h3>
                    <small class="text-muted">Asesor Sudah Submit</small>
                    <div class="mt-2">
                        <span class="badge bg-warning">Perlu Divalidasi</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center stat-card" data-bs-toggle="modal" data-bs-target="#modalAsesorPending">
                <div class="card-body">
                    <h3 class="text-danger mb-0">{{ $stats['total_asesor_pending'] }}</h3>
                    <small class="text-muted">Asesor Belum Submit</small>
                    <div class="mt-2">
                        <span class="badge bg-danger">Menunggu</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center stat-card">
                <div class="card-body">
                    <h3 class="text-primary mb-0">{{ $stats['total_needs_validation'] }}</h3>
                    <small class="text-muted">Asesmen Aktif</small>
                    <div class="mt-2">
                        <span class="badge bg-primary">Perlu Validasi</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center stat-card">
                <div class="card-body">
                    <h3 class="text-success mb-0">{{ $stats['total_validated'] }}</h3>
                    <small class="text-muted">Selesai Divalidasi</small>
                    <div class="mt-2">
                        <span class="badge bg-success">Approved</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Asesmen List -->
    <h4 class="mb-3">
        <i class="bi bi-clipboard-check"></i> Asesmen Perlu Divalidasi
    </h4>

    @forelse($needsValidation as $item)
    <div class="card asesmen-validasi-card mb-4">
        <div class="card-header bg-primary text-white py-3 ps-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">{{ $item['asesmen']->name }}</h5>
                    <small>
                        @if($item['asesmen']->studyProgram)
                        {{ $item['asesmen']->studyProgram->full_name }} -
                        {{ $item['asesmen']->studyProgram->university->name ?? '' }}
                        @endif
                    </small>
                </div>
                <div class="text-end">
                    <span class="badge bg-warning text-dark mb-1">
                        {{ $item['asesors']->count() }} Asesor Submit
                    </span>
                    @if($item['asesors_pending']->count() > 0)
                    <br>
                    <span class="badge bg-danger">
                        {{ $item['asesors_pending']->count() }} Belum Submit
                    </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1">
                        <i class="bi bi-tag"></i> <strong>Kode Panel:</strong>
                        <span class="badge bg-secondary">{{ $item['asesmen']->kode_panel ?? 'N/A' }}</span>
                    </p>
                    @if($item['asesmen']->description)
                    <p class="mb-0 text-muted">{{ Str::limit($item['asesmen']->description, 120) }}</p>
                    @endif
                </div>

                @php
                // Get total accepted asesors for this asesmen
                $totalAcceptedAsesors = \App\Models\AsesmenUserRole::where('id_asesmen', $item['asesmen']->id)
                ->where('jenis_asesmen', 'ak')
                ->where('status_penawaran', 'accepted')
                ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
                ->count();

                // Check if all asesors have submitted
                $allAsesorsSubmitted = $item['asesors_pending']->count() === 0 && $item['asesors']->count() >= 2;

                // Check minimum requirements
                $hasMinimumAsesors = $totalAcceptedAsesors >= 2;
                @endphp

                @if(!$hasMinimumAsesors)
                <button class="btn btn-secondary" disabled title="Minimal 2 asesor harus di-assign">
                    <i class="bi bi-exclamation-triangle"></i> Kurang Asesor
                </button>
                @elseif(!$allAsesorsSubmitted)
                <button class="btn btn-warning" disabled title="Menunggu semua asesor submit penilaian">
                    <i class="bi bi-clock"></i> Menunggu {{ $item['asesors_pending']->count() }} Asesor Submit
                </button>
                @else
                <a href="{{ route('ak.validasi.asesor', ['idAsesmen' => $item['asesmen']->id, 'jenisAsesmen' => 'ak']) }}" class="btn btn-primary">
                    <i class="bi bi-check2-square"></i> Validasi Sekarang
                </a>
                @endif
            </div>

            <h6 class="mb-3">
                <i class="bi bi-people-fill"></i> Daftar Asesor yang Sudah Submit:
            </h6>

            @foreach($item['asesors'] as $asesor)
            @php
            $progress = $asesor->validation_progress ?? [
            'total' => 0,
            'validated' => 0,
            'revision' => 0,
            'pending' => 0,
            'percentage' => 0
            ];
            @endphp

            <div class="asesor-list-item">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3">
                                {{ substr($asesor->user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $asesor->user->name }}</div>
                                <small class="text-muted">{{ $asesor->user->email }}</small>
                                <div class="mt-1">
                                    <span class="badge bg-{{ $asesor->jenis_asesmen === 'ak' ? 'primary' : 'info' }}">
                                        {{ strtoupper($asesor->jenis_asesmen) }}
                                    </span>
                                    @if($asesor->urutan_asesor)
                                    <span class="badge bg-secondary">#{{ $asesor->urutan_asesor }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Disubmit pada:</small>
                        <strong>{{ \App\Libraries\Date::tglWaktu($asesor->submitted_at) }}</strong>
                    </div>
                    <div class="col-md-5">
                        <small class="text-muted d-block mb-1">Progress Validasi:</small>
                        <div class="progress" style="height: 24px;">
                            <div class="progress-bar bg-{{ $progress['percentage'] == 100 ? 'success' : ($progress['percentage'] > 0 ? 'warning' : 'secondary') }}" role="progressbar" style="width: {{ $progress['percentage'] }}%">
                                {{ $progress['percentage'] }}%
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">
                                <i class="bi bi-check-circle"></i> {{ $progress['validated'] }}/{{ $progress['total'] }} Validated
                            </small>
                            @if($progress['revision'] > 0)
                            <small class="text-danger">
                                <i class="bi bi-exclamation-triangle"></i> {{ $progress['revision'] }} Perlu Revisi
                            </small>
                            @endif
                            @if($progress['pending'] > 0)
                            <small class="text-warning">
                                <i class="bi bi-clock"></i> {{ $progress['pending'] }} Pending
                            </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            @if($item['asesors_pending']->count() > 0)
            <h6 class="mb-3 mt-4 text-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> Daftar Asesor yang Belum Submit:
            </h6>

            @foreach($item['asesors_pending'] as $asesor)
            <div class="asesor-list-item" style="background-color: #fff3cd; border-color: #ffc107;">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3" style="background: #ff9800;">
                                {{ substr($asesor->user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $asesor->user->name }}</div>
                                <small class="text-muted">{{ $asesor->user->email }}</small>
                                <div class="mt-1">
                                    <span class="badge bg-{{ $asesor->jenis_asesmen === 'ak' ? 'primary' : 'info' }}">
                                        {{ strtoupper($asesor->jenis_asesmen) }}
                                    </span>
                                    @if($asesor->urutan_asesor)
                                    <span class="badge bg-secondary">#{{ $asesor->urutan_asesor }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Status Pekerjaan:</small>
                        <strong>
                            @if($asesor->status_pekerjaan === 'not_started')
                            <span class="badge bg-secondary">Belum Mulai</span>
                            @elseif($asesor->status_pekerjaan === 'in_progress')
                            <span class="badge bg-info">Sedang Dikerjakan</span>
                            @elseif($asesor->status_pekerjaan === 'revision_required')
                            <span class="badge bg-danger">Perlu Revisi</span>
                            @else
                            <span class="badge bg-warning">{{ $asesor->status_pekerjaan }}</span>
                            @endif
                        </strong>
                    </div>
                    <div class="col-md-3 text-end">
                        <small class="text-muted">Menunggu Submit</small>
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>
    @empty
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
            <p class="text-muted mt-3 mb-0">Belum ada asesor yang submit penilaian untuk divalidasi.</p>
        </div>
    </div>
    @endforelse

    <!-- Validated Section (Optional) -->
    @if(count($validated) > 0)
    <hr class="my-5">

    <h4 class="mb-3 text-success">
        <i class="bi bi-check-circle-fill"></i> Asesmen yang Sudah Divalidasi
    </h4>

    @foreach($validated as $item)
    <div class="card asesmen-validasi-card mb-3 border-success">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">{{ $item['asesmen']->name }}</h6>
                    <small>
                        @if($item['asesmen']->studyProgram)
                        {{ $item['asesmen']->studyProgram->full_name }}
                        @endif
                    </small>
                </div>
                <span class="badge bg-light text-success">
                    <i class="bi bi-check-circle"></i> Sudah Divalidasi
                </span>
            </div>
        </div>
        <div class="card-body">
            <p class="mb-2">
                <strong>Kode Panel:</strong>
                <span class="badge bg-secondary">{{ $item['asesmen']->kode_panel ?? 'N/A' }}</span>
            </p>
            <p class="mb-2">
                <small class="text-muted">Disetujui pada: {{ \App\Libraries\Date::tglWaktu($item['assignment']->approved_at) }}</small>
            </p>
            <a href="{{ route('ak.validasi.asesor', ['idAsesmen' => $item['asesmen']->id, 'jenisAsesmen' => 'ak']) }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-eye"></i> Lihat Detail
            </a>
        </div>
    </div>
    @endforeach
    @endif
</div>

<!-- Modal: Asesor yang Sudah Submit -->
<div class="modal fade" id="modalAsesorSubmitted" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-people-fill"></i> Daftar Asesor yang Sudah Submit
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @foreach($needsValidation as $item)
                @if($item['asesors']->count() > 0)
                <h6 class="fw-bold mb-2">{{ $item['asesmen']->name }}</h6>
                @foreach($item['asesors'] as $asesor)
                <div class="pending-asesor-item mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $asesor->user->name }}</strong>
                            <br><small class="text-muted">{{ $asesor->user->email }}</small>
                        </div>
                        <span class="badge bg-warning">Perlu Validasi</span>
                    </div>
                </div>
                @endforeach
                <hr>
                @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal: Asesor yang Belum Submit -->
<div class="modal fade" id="modalAsesorPending" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill"></i> Daftar Asesor yang Belum Submit
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @forelse($asesorsPendingList as $pending)
                <div class="pending-asesor-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $pending['asesor']->name }}</strong>
                            <br><small class="text-muted">{{ $pending['asesor']->email }}</small>
                            <br><small><strong>Asesmen:</strong> {{ $pending['asesmen'] }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $pending['jenis_asesmen'] === 'ak' ? 'primary' : 'info' }}">
                                {{ strtoupper($pending['jenis_asesmen']) }}
                            </span>
                            <br>
                            <span class="badge bg-danger mt-1">{{ ucfirst(str_replace('_', ' ', $pending['status'])) }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-center text-muted">Semua asesor sudah submit! 🎉</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
