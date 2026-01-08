@extends('layouts.template.app')

@section('title', 'Daftar Berkas Penilaian AL')


@push('styles')
<style>
    .asesmen-card {
        transition: all 0.3s ease;
        border: 1px solid #e0e0e0;
    }

    .asesmen-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-color: var(--primary);
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <!-- Welcome Section -->
    <div class="welcome-section mb-4">
        <div class="welcome-content">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-clipboard-check"></i> Daftar Berkas Penilaian AL
                    </h2>
                    <p class="mb-0 opacity-75">
                        Kelola dan lakukan penilaian lapangan pada akreditasi perguruan tinggi
                    </p>
                </div>
                <div class="text-end">
                    <h3 class="mb-0">{{ $asesmens->total() }}</h3>
                    <small>Total Asesmen</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h6 class="mb-0">
                <i class="bi bi-funnel"></i> Filter & Pencarian
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('al.berkas') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Pencarian</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control" placeholder="Cari nama asesmen..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status Penawaran</label>
                    <select name="status_penawaran" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status_penawaran') == 'pending' ? 'selected' : '' }}>
                            Menunggu Konfirmasi
                        </option>
                        <option value="accepted" {{ request('status_penawaran') == 'accepted' ? 'selected' : '' }}>
                            Diterima
                        </option>
                        <option value="rejected" {{ request('status_penawaran') == 'rejected' ? 'selected' : '' }}>
                            Ditolak
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status Pekerjaan</label>
                    <select name="status_pekerjaan" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach($statusPekerjaan as $key => $status)
                        <option value="{{ $key }}" {{ request('status_pekerjaan') === $key ? 'selected' : '' }}>
                            {{ $status['label'] }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        <a href="{{ route('al.berkas') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Stats -->
    @if($asesmens->total() > 0)
    <div class="row mb-4">
        @php
        $stats = [
        'pending' => $asesmens->where('userRoles.0.status_penawaran', 'pending')->count(),
        'in_progress' => $asesmens->where('userRoles.0.status_pekerjaan', 'in_progress')->count(),
        'submitted' => $asesmens->where('userRoles.0.status_pekerjaan', 'submitted')->count(),
        'revision' => $asesmens->where('userRoles.0.status_pekerjaan', 'revision_required')->count(),
        'approved' => $asesmens->where('userRoles.0.status_pekerjaan', 'approved')->count(),
        ];
        @endphp

        <div class="col-md-3 mb-2">
            <div class="card border-warning text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-warning">{{ $stats['pending'] }}</h3>
                    <small class="text-muted">Menunggu Konfirmasi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card border-info text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-info">{{ $stats['in_progress'] }}</h3>
                    <small class="text-muted">Sedang Dikerjakan</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card border-primary text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-primary">{{ $stats['submitted'] }}</h3>
                    <small class="text-muted">Menunggu Validasi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card border-warning text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-warning">{{ $stats['revision'] }}</h3>
                    <small class="text-muted">Perlu Revisi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card border-success text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-success">{{ $stats['approved'] }}</h3>
                    <small class="text-muted">Disetujui</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Assessment List -->
    <div class="row">
        @forelse($asesmens as $asesmen)
        @php
        $statusInfo = $asesmen->statusInfo;
        $assignment = $asesmen->userRoles->first();
        @endphp
        <div class="col-md-6 col-lg-6 col-xl-4 mb-4">
            <div class="card asesmen-card h-100">
                {{-- Status Indicator Corner --}}
                <div class="status-indicator {{ $assignment->status_indicator }}"></div>

                <div class="card-body">
                    {{-- Header --}}
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1 me-2">
                            <h5 class="card-title mb-1">
                                {{ $asesmen->name }}
                            </h5>
                        </div>
                        {{-- Status Badge --}}
                        <span class="badge badge-sm {{ $statusInfo['badge_class'] }} flex-shrink-0">
                            <i class="{{ $statusInfo['badge_icon'] }}"></i>
                            {{ $statusInfo['badge_text'] }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="bi bi-building"></i>
                            {{ $asesmen->studyProgram->full_name ?? 'N/A' }}
                        </small>
                    </div>

                    {{-- Description --}}
                    @if($statusInfo['description'])
                    <div class="alert alert-light alert-permanent mb-3 py-2">
                        <small class="description-text mb-0">
                            <i class="bi bi-info-circle"></i>
                            {{ $statusInfo['description'] }}
                        </small>
                    </div>
                    @endif

                    {{-- Progress Bar (Only if accepted) --}}
                    @if($assignment->status_penawaran === 'accepted' && $assignment->status_pekerjaan !== 'approved')
                    <div class="progress-summary mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted fw-semibold">Progress Penilaian</small>
                            <span class="badge bg-primary">
                                {{ $asesmen->progress['percentage'] }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-gradient" role="progressbar" style="width: {{ $asesmen->progress['percentage'] }}%" aria-valuenow="{{ $asesmen->progress['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">
                                <i class="bi bi-check-circle"></i>
                                {{ $asesmen->progress['completed'] }} / {{ $asesmen->progress['total'] }} elemen
                            </small>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i>
                                {{ $asesmen->progress['remaining'] }} tersisa
                            </small>
                        </div>
                    </div>
                    @endif

                    {{-- Role & Date Info --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">
                                <i class="bi bi-person-badge"></i>
                                <strong>{{ $assignment->role->alias ?? 'N/A' }}</strong>
                            </small>
                            <small class="text-muted">
                                <i class="bi bi-calendar3"></i>
                                {{ \App\Libraries\Date::tglIndo($asesmen->created_at) }}
                            </small>
                        </div>
                    </div>
                    {{-- Additional Info for Submitted/Approved --}}
                    @if(in_array($assignment->status_pekerjaan, ['submitted', 'approved']))
                    <div class="alert alert-info alert-permanent py-2 mb-3">
                        <small>
                            <i class="bi bi-send-check"></i>
                            <strong>Di-submit:</strong>
                            {{ \App\Libraries\Date::tglWaktu($assignment->submitted_at) }}
                        </small>
                    </div>
                    @endif

                    @if($assignment->status_pekerjaan === 'approved')
                    <div class="alert alert-success alert-permanent py-2 mb-3">
                        <small>
                            <i class="bi bi-check-all"></i>
                            <strong>Disetujui:</strong>
                            {{ \App\Libraries\Date::tglWaktu($assignment->approved_at) }}
                        </small>
                    </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="d-grid gap-2">
                        @if($statusInfo['button_route'] ?? false)
                        {{-- Button ke route khusus (penawaran) --}}
                        <a href="{{ route($statusInfo['button_route'], ['idAsesmen'=>$asesmen->id,'jenisAsesmen'=>$assignment->jenis_asesmen]) }}" class="btn {{ $statusInfo['button_class'] }}" @if($statusInfo['button_disabled']) disabled @endif>
                            <i class="{{ $statusInfo['button_icon'] }}"></i>
                            {{ $statusInfo['button_text'] }}
                        </a>
                        @else
                        {{-- Button ke berkas show --}}
                        <a href="{{ route('al.berkas.show', $asesmen->id) }}" class="btn {{ $statusInfo['button_class'] }}" @if($statusInfo['button_disabled']) disabled @endif>
                            <i class="{{ $statusInfo['button_icon'] }}"></i>
                            {{ $statusInfo['button_text'] }}
                        </a>
                        @endif

                        {{-- Secondary Actions --}}
                        @if($assignment->status_penawaran === 'accepted' && $assignment->status_pekerjaan !== 'not_started')
                        <a href="{{ route('al.berkas.show', $asesmen->id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i> Lihat Detail
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Card Footer with additional info --}}
                <div class="card-footer bg-light text-muted">
                    <div class="d-flex justify-content-between align-items-center">
                        <small>
                            <i class="bi bi-clock-history"></i>
                            Terakhir diupdate: {{ $asesmen->updated_at->diffForHumans() }}
                        </small>
                        @if($assignment->status_pekerjaan === 'revision_required')
                        <span class="badge bg-danger">
                            <i class="bi bi-exclamation-circle"></i> Urgent
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 5rem; color: #e0e0e0;"></i>
                    <h4 class="mt-4 text-muted">Belum Ada Asesmen</h4>
                    <p class="text-muted mb-4">
                        Belum ada berkas penilaian yang ditugaskan kepada Anda.
                    </p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="bi bi-house"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($asesmens->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $asesmens->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
