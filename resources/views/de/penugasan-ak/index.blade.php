{{-- resources\views\de\penugasan-ak\index.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Penugasan Asesmen Kecukupan (AK)')

@push('styles')
<style>
    .filter-card {
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
        color: white;
    }

    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Penugasan Asesor AK</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-person-check"></i> Penugasan Asesor AK
            </h4>
            <p class="text-muted mb-0">Tugaskan asesor & validator untuk melakukan asesmen kecukupan</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Permohonan" :value="$stats['total']" description="Permohonan akreditasi PS yang telah memasuki tahap AK" icon="clipboard-check" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Belum Ditugaskan" :value="$stats['belum_ditugaskan']" description="Perlu menugaskan asesor/validator" icon="exclamation-triangle" gradient="linear-gradient(135deg, #ee0979 0%, #ff6a00 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Sudah Ditugaskan" :value="$stats['sudah_ditugaskan']" description="Sedang proses asesmen" icon="hourglass-split" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Selesai" :value="$stats['selesai']" description="AK telah selesai" icon="check-circle" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Filters -->
        {{-- <div class="col-lg-3 mb-4">
            <div class="card filter-card">
                <div class="card-header border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel"></i> Filter & Pencarian
                    </h5>
                </div>
                <div class="card-body">
                    <form id="filterForm" onsubmit="return false;">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label text-white">Cari Permohonan</label>
                            <input type="text" name="search" id="searchInput" class="form-control" placeholder="Nomor / nama prodi..." value="{{ request('search') }}">
    </div>

    <!-- University -->
    <div class="mb-3">
        <label class="form-label text-white">Universitas</label>
        <select name="university_id" id="universityFilter" class="form-select">
            <option value="">Semua Universitas</option>
            @foreach($universities as $univ)
            <option value="{{ $univ->id }}" {{ request('university_id') == $univ->id ? 'selected' : '' }}>
                {{ $univ->name }}
            </option>
            @endforeach
        </select>
    </div>

    <!-- Status AK -->
    <div class="mb-3">
        <label class="form-label text-white">Status AK</label>
        <select name="status_ak" id="statusAKFilter" class="form-select">
            <option value="">Semua Status</option>
            <option value="belum_ditugaskan" {{ request('status_ak') == 'belum_ditugaskan' ? 'selected' : '' }}>Belum Ditugaskan</option>
            <option value="sudah_ditugaskan" {{ request('status_ak') == 'sudah_ditugaskan' ? 'selected' : '' }}>Sudah Ditugaskan</option>
            <option value="selesai" {{ request('status_ak') == 'selesai' ? 'selected' : '' }}>Selesai</option>
        </select>
    </div>

    <!-- Buttons -->
    <div class="d-grid gap-2">
        <button type="button" class="btn btn-light" onclick="applyFilters()">
            <i class="bi bi-search"></i> Terapkan Filter
        </button>
        <button type="button" class="btn btn-outline-light" onclick="resetFilters()">
            <i class="bi bi-x-circle"></i> Reset
        </button>
    </div>
    </form>
</div>
</div>
</div> --}}

<!-- Table Content -->
<div class="col-lg-12">
    <div class="position-relative">
        <!-- Loading Overlay -->
        <div id="tableLoading" class="position-absolute top-0 start-0 w-100 h-100 d-none" style="background: rgba(255,255,255,0.9); z-index: 1000;">
            <div class="d-flex justify-content-center align-items-center h-100" style="min-height: 400px;">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat data...</p>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div id="tableContainer">
            @include('de.penugasan-ak.components.table-content', ['pengajuans' => $pengajuans])
        </div>
    </div>
</div>
</div>
</div>

@push('scripts')
<script>
    // Apply filters
    async function applyFilters() {
        const params = {
            search: document.getElementById('searchInput').value
            , university_id: document.getElementById('universityFilter').value
            , status_ak: document.getElementById('statusAKFilter').value
        , };

        await loadTable(params);
    }

    // Reset filters
    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('universityFilter').value = '';
        document.getElementById('statusAKFilter').value = '';
        loadTable({});
    }

    // Load table via AJAX
    async function loadTable(params = {}) {
        const loadingOverlay = document.getElementById('tableLoading');
        const tableContainer = document.getElementById('tableContainer');

        try {
            loadingOverlay.classList.remove('d-none');

            const queryString = new URLSearchParams(params).toString();
            const response = await fetch(`{{ route('de.penugasan-ak') }}?${queryString}`, {
                method: 'GET'
                , headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    , 'Accept': 'application/json'
                , }
            });

            const data = await response.json();

            if (data.success) {
                tableContainer.innerHTML = data.html;

                // Update URL
                const newUrl = new URL(window.location);
                Object.keys(params).forEach(key => {
                    if (params[key]) {
                        newUrl.searchParams.set(key, params[key]);
                    } else {
                        newUrl.searchParams.delete(key);
                    }
                });
                window.history.pushState({}, '', newUrl);
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Perhatian', 'Gagal memuat data. Silakan refresh halaman.', 'error');
        } finally {
            loadingOverlay.classList.add('d-none');
        }
    }

    // Auto-apply filters on change
    ['searchInput', 'universityFilter', 'statusAKFilter'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            if (id === 'searchInput') {
                let searchTimeout;
                element.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => applyFilters(), 500);
                });
            } else {
                element.addEventListener('change', applyFilters);
            }
        }
    });

</script>
@endpush
@endsection
