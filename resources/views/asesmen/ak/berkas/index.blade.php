@extends('layouts.template.app')

@section('title', 'Daftar Berkas Penilaian AK')


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

    .progress {
        border-radius: 10px;
        background-color: #f0f0f0;
    }

    .progress-bar {
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    .welcome-section {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(147, 33, 54, 0.2);
    }

    .welcome-content h2 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .welcome-content p {
        font-size: 16px;
        opacity: 0.95;
        margin: 0;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Welcome Section -->
    <div class="welcome-section mb-4">
        <div class="welcome-content">
            <h2><i class="bi bi-clipboard-check"></i> Daftar Berkas Penilaian AK</h2>
            <p>Kelola dan lakukan penilaian akreditasi perguruan tinggi</p>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('ak.berkas') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama asesmen..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="in_progress">Sedang Dinilai</option>
                        <option value="completed">Selesai</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block w-100">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <a href="{{ route('ak.berkas') }}" class="btn btn-secondary d-block w-100">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Assessment List -->
    <div class="row">
        @forelse($asesmens as $asesmen)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card asesmen-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0">{{ $asesmen->assessment_name }}</h5>
                        @if($asesmen->progress['percentage'] == 100)
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Selesai
                        </span>
                        @elseif($asesmen->progress['percentage'] > 0)
                        <span class="badge bg-warning">
                            <i class="bi bi-clock-history"></i> Progress
                        </span>
                        @else
                        <span class="badge bg-secondary">
                            <i class="bi bi-file-text"></i> Belum Mulai
                        </span>
                        @endif
                    </div>

                    <p class="card-text text-muted small">{{ Str::limit($asesmen->description, 100) }}</p>

                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Progress Penilaian</small>
                            <small class="fw-bold text-primary">{{ $asesmen->progress['percentage'] }}%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $asesmen->progress['percentage'] }}%" aria-valuenow="{{ $asesmen->progress['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $asesmen->progress['completed'] }} / {{ $asesmen->progress['total'] }} indikator selesai
                        </small>
                    </div>

                    <!-- Role Info -->
                    <div class="mb-3">
                        <small class="text-muted d-block">
                            <i class="bi bi-person-badge"></i> Role:
                            <span class="fw-semibold">{{ $asesmen->userRoles->first()->role->role_name ?? 'N/A' }}</span>
                        </small>
                        <small class="text-muted">
                            <i class="bi bi-calendar3"></i> Ditambahkan: {{ $asesmen->created_at->format('d M Y') }}
                        </small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <a href="{{ route('ak.berkas.show', $asesmen->id) }}" class="btn btn-primary">
                            <i class="bi bi-pencil-square"></i> Mulai Penilaian
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted mt-3 mb-0">Belum ada berkas penilaian yang ditugaskan kepada Anda.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($asesmens->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $asesmens->links() }}
    </div>
    @endif
</div>

@endsection
