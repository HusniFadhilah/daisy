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
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    @php
                    $totalNeedsValidation = collect($needsValidation)->sum(function($item) {
                    return $item['asesors']->count();
                    });
                    @endphp
                    <h3 class="text-warning mb-0">{{ $totalNeedsValidation }}</h3>
                    <small class="text-muted">Penilaian Asesor Perlu Divalidasi oleh Anda</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary mb-0">{{ count($needsValidation) }}</h3>
                    <small class="text-muted">Asesmen Aktif</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success mb-0">0</h3>
                    <small class="text-muted">Selesai Divalidasi</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Asesmen List -->
    @forelse($needsValidation as $item)
    @php
    $idAsesors = $item['asesors']->pluck('id_user');
    @endphp
    <div class="card asesmen-validasi-card mb-4">
        <div class="card-header bg-primary text-white py-3 ps-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">{{ $item['asesmen']->name }}</h5>
                    <small>{{ $item['asesmen']->perguruan_tinggi ?? '' }}</small>
                </div>
                <span class="badge bg-warning text-dark">
                    Menunggu {{ $item['asesors']->count() }} Asesor
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div class="mb-3">
                    <p class="mb-1">
                        <i class="bi bi-tag"></i> <strong>Kode Panel:</strong>
                        <span class="badge bg-secondary">{{ $item['asesmen']->kode_panel ?? 'N/A' }}</span>
                    </p>
                    @if($item['asesmen']->description)
                    <p class="mb-0 text-muted">{{ Str::limit($item['asesmen']->description, 120) }}</p>
                    @endif
                </div>
                @if(isset($idAsesors[0]) && isset($idAsesors[1]))
                <a href="{{ route('ak.validasi.asesor', ['idAsesmen' => $item['asesmen']->id, 'asesor1Id' => $idAsesors[0],'asesor2Id' => $idAsesors[1]]) }}" class="btn btn-primary">
                    <i class="bi bi-check2-square"></i> Validasi sekarang
                </a>
                @endif
            </div>

            <h6 class="mb-3"><i class="bi bi-people"></i> Daftar Asesor yang Sudah Submit:</h6>

            @foreach($item['asesors'] as $asesor)
            @php
            // Calculate validation progress for this asesor
            $totalPenilaian = \App\Models\PenilaianElemen::where('id_asesmen', $item['asesmen']->id)
            ->where('id_asesor', $asesor->id_user)
            ->count();

            $validatedCount = \App\Models\PenilaianElemen::where('id_asesmen', $item['asesmen']->id)
            ->where('id_asesor', $asesor->id_user)
            ->whereIn('status_validasi', ['validated', 'approved'])
            ->count();

            $revisionCount = \App\Models\PenilaianElemen::where('id_asesmen', $item['asesmen']->id)
            ->where('id_asesor', $asesor->id_user)
            ->where('status_validasi', 'revision_required')
            ->count();

            $percentage = $totalPenilaian > 0 ? round(($validatedCount / $totalPenilaian) * 100, 1) : 0;
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
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Disubmit pada:</small>
                        <strong>{{ \App\Libraries\Date::tglWaktu($asesor->submitted_at) }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Progress Validasi:</small>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-{{ $percentage == 100 ? 'success' : ($percentage > 0 ? 'warning' : 'secondary') }}" role="progressbar" style="width: {{ $percentage }}%">
                                {{ $percentage }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $validatedCount }}/{{ $totalPenilaian }}
                            @if($revisionCount > 0)
                            <span class="text-danger">({{ $revisionCount }} perlu revisi)</span>
                            @endif
                        </small>
                    </div>
                </div>
            </div>
            @endforeach

            @php
            $asesorBelumSubmit = $item['asesors']->filter(function($asesor) {
            return is_null($asesor->submitted_at);
            });
            @endphp

            @if($asesorBelumSubmit->count() > 0)
            <h6 class="mb-3 mt-4"><i class="bi bi-exclamation-triangle"></i> Daftar Asesor yang Belum Submit:</h6>

            @foreach($asesorBelumSubmit as $asesor)
            <div class="asesor-list-item" style="background-color: #fff3cd; border-color: #ffeeba;">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3">
                                {{ substr($asesor->user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $asesor->user->name }}</div>
                                <small class="text-muted">{{ $asesor->user->email }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Status:</small>
                        <strong>Belum submit</strong>
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
</div>

<style>
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

</style>

@endsection
