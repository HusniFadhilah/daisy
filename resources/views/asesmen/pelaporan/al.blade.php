{{-- resources/views/asesmen/pelaporan/al.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Pelaporan AL')

@push('styles')
<style>
    .filter-section {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .search-box {
        position: relative;
    }

    .search-box .bi-search {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }

    .search-box input {
        padding-left: 36px;
    }

    .info-banner {
        background: linear-gradient(135deg, #198754 0%, #146c43 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .timeline-preview {
        background: white;
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
    }

    .timeline-step {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .timeline-step i {
        font-size: 1.2rem;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="welcome-section mb-4">
        <div class="welcome-content">
            <h2>
                <i class="bi bi-building-check text-white"></i>
                Pelaporan Asesmen Lapangan
            </h2>
            <p class="mb-0">Kelola pelaporan hasil asesmen lapangan Program Studi</p>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('pelaporan.index') }}">
                    <i class="bi bi-house-door"></i> Dashboard Pelaporan
                </a>
            </li>
            <li class="breadcrumb-item active">Pelaporan AL</li>
        </ol>
    </nav>

    <!-- Info Banner -->
    <div class="info-banner">
        <div class="row align-items-center">
            <div class="col-md-1 text-center">
                <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
            </div>
            <div class="col-md-11">
                <h5 class="mb-2">Tentang Pelaporan Asesmen Lapangan</h5>
                <p class="mb-2">
                    Laporan Hasil Asesmen Lapangan Program Studi (LHA) mencakup dokumentasi lengkap dari:
                </p>
                <div class="timeline-preview">
                    <div class="timeline-step">
                        <i class="bi bi-1-circle-fill text-success"></i>
                        <span class="text-dark">Penunjukan tugas asesor untuk pelaksanaan AL</span>
                    </div>
                    <div class="timeline-step">
                        <i class="bi bi-2-circle-fill text-success"></i>
                        <span class="text-dark">Proses penilaian LED oleh asesor di lapangan</span>
                    </div>
                    <div class="timeline-step">
                        <i class="bi bi-3-circle-fill text-success"></i>
                        <span class="text-dark">Validasi penilaian kecukupan asesor</span>
                    </div>
                    <div class="timeline-step">
                        <i class="bi bi-4-circle-fill text-success"></i>
                        <span class="text-dark">Informasi lokasi, perjalanan, dan berita acara AL</span>
                    </div>
                    <div class="timeline-step">
                        <i class="bi bi-5-circle-fill text-success"></i>
                        <span class="text-dark">Rekomendasi penetapan hasil akreditasi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <i class="bi bi-folder text-success" style="font-size: 2rem;"></i>
                    </div>
                    <h3 class="text-success mb-1">{{ $stats['total'] }}</h3>
                    <small class="text-muted">Total Penugasan</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <i class="bi bi-hourglass-split text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <h3 class="text-warning mb-1">{{ $stats['pending'] }}</h3>
                    <small class="text-muted">Menunggu Pelaporan</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <i class="bi bi-arrow-repeat text-info" style="font-size: 2rem;"></i>
                    </div>
                    <h3 class="text-info mb-1">{{ $stats['in_progress'] }}</h3>
                    <small class="text-muted">Sedang Diproses</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    </div>
                    <h3 class="text-success mb-1">{{ $stats['completed'] }}</h3>
                    <small class="text-muted">Selesai</small>
                </div>
            </div>
        </div>
    </div>

    @if($assignments->count() > 0)
    <!-- Filter Section -->
    <div class="filter-section">
        <div class="row align-items-center">
            <div class="col-md-4 mb-2 mb-md-0">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Cari program studi atau universitas...">
                </div>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <select class="form-select" id="filterStatus">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu Pelaporan</option>
                    <option value="in_progress">Sedang Diproses</option>
                    <option value="completed">Selesai</option>
                </select>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <select class="form-select" id="sortBy">
                    <option value="newest">Terbaru</option>
                    <option value="oldest">Terlama</option>
                    <option value="name">Nama A-Z</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100" id="resetFilter">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Cards Grid -->
    <div class="row" id="assignmentsGrid">
        @foreach($assignments as $assignment)
        @php
        $pengajuan = $assignment->asesmen->pengajuan;
        $canReport = $pengajuan ? $pengajuan->canBeReported('al') : false;
        $isReported = $pengajuan?->tanggal_pelaporan_al !== null;
        $reportedAt = $pengajuan?->tanggal_pelaporan_al;

        $statusClass = $isReported ? 'completed' : ($canReport ? 'pending' : 'waiting');
        @endphp

        <div class="col-md-6 col-lg-4 mb-4 assignment-card" data-status="{{ $statusClass }}" data-name="{{ strtolower($assignment->asesmen->studyProgram->name ?? '') }}" data-university="{{ strtolower($assignment->asesmen->studyProgram->university->name ?? '') }}" data-date="{{ $assignment->created_at->timestamp }}">
            <x-pelaporan.pelaporan-card :assignment="$assignment" type="al" :canReport="$canReport" :isReported="$isReported" :reportedAt="$reportedAt" />
        </div>
        @endforeach
    </div>

    <!-- No Results -->
    <div class="alert alert-info text-center d-none" id="noResults">
        <i class="bi bi-search"></i>
        Tidak ada hasil yang sesuai dengan filter
    </div>

    @else
    <!-- Empty State -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-building-x" style="font-size: 64px; opacity: 0.3; color: #198754;"></i>
            <h5 class="mt-3 mb-2">Tidak Ada Pelaporan AL</h5>
            <p class="text-muted mb-4">
                Anda belum memiliki penugasan pelaporan asesmen lapangan saat ini.<br>
                Pelaporan AL akan tersedia setelah proses asesmen lapangan selesai.
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('pelaporan.index') }}" class="btn btn-outline-success">
                    <i class="bi bi-arrow-left"></i> Dashboard Pelaporan
                </a>
                <a href="{{ route('penawaran') }}" class="btn btn-success">
                    <i class="bi bi-envelope"></i> Lihat Penawaran
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/pelaporan.js') }}"></script>
<script>
    // Same filter/sort script
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const filterStatus = document.getElementById('filterStatus');
        const sortBy = document.getElementById('sortBy');
        const resetBtn = document.getElementById('resetFilter');
        const assignmentsGrid = document.getElementById('assignmentsGrid');
        const noResults = document.getElementById('noResults');

        if (!assignmentsGrid) return;

        const cards = Array.from(assignmentsGrid.querySelectorAll('.assignment-card'));

        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusFilter = filterStatus.value;
            const sortValue = sortBy.value;

            let visibleCards = cards.filter(card => {
                const name = card.dataset.name;
                const university = card.dataset.university;
                const status = card.dataset.status;

                const matchesSearch = !searchTerm ||
                    name.includes(searchTerm) ||
                    university.includes(searchTerm);

                const matchesStatus = !statusFilter || status === statusFilter;

                return matchesSearch && matchesStatus;
            });

            if (sortValue === 'newest') {
                visibleCards.sort((a, b) => b.dataset.date - a.dataset.date);
            } else if (sortValue === 'oldest') {
                visibleCards.sort((a, b) => a.dataset.date - b.dataset.date);
            } else if (sortValue === 'name') {
                visibleCards.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
            }

            cards.forEach(card => card.style.display = 'none');

            if (visibleCards.length > 0) {
                visibleCards.forEach(card => {
                    card.style.display = 'block';
                    assignmentsGrid.appendChild(card);
                });
                noResults.classList.add('d-none');
            } else {
                noResults.classList.remove('d-none');
            }
        }

        searchInput.addEventListener('input', applyFilters);
        filterStatus.addEventListener('change', applyFilters);
        sortBy.addEventListener('change', applyFilters);

        resetBtn.addEventListener('click', function() {
            searchInput.value = '';
            filterStatus.value = '';
            sortBy.value = 'newest';
            applyFilters();
        });
    });

    window.PELAPORAN_CFG = {
        al: {
            title: 'Rekap AL dan Pelaporan AL'
            , label: 'Laporan Hasil Asesmen Lapangan Program Studi (LHA)'
            , upload: @json(route('pelaporan.al.upload', ['assignment' => '__ID__']))
            , finalize: @json(route('pelaporan.al.finalize', ['assignment' => '__ID__']))
            , fileLabel: 'Laporan Hasil Asesmen Lapangan Program Studi (LHA)'
            , finalizeLabel: 'Pelaporan AL Telah Selesai'
            , additionalDescription: `Dokumen yang sudah digabungkan, yang diperlukan isinya adalah:
                •	Penunjukan tugas Asesor untuk melaksanakan Penilaian LED
                •	Proses peneliaan LED oleh Asesor.
                •	Validasi Penilaian Kecukupan Asesor Oleh Validator
                •	Penyampaian Informasi Kepada DE tentang:
                    o	Lokasi AL
                    o	Perjalan asesor ke lokasi AL
                    o	Berita Acara yang menyatakan AL telah dilaksanakan dan disepakati
                •	Rekomendasi Penetapan Hasil Akreditasi`
        }
    };

</script>
@endpush
