@extends('layouts.template.app')

@section('title', 'Pelaksanaan & Monitoring AL')

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
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-clipboard-data"></i> Pelaksanaan & Monitoring AL
            </h4>
            <p class="text-muted mb-0">Monitor visitasi lapangan dan tugaskan validator untuk pelaporan</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total AL" :value="$stats['total']" description="Dalam pelaksanaan" icon="clipboard-check" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Sedang Visitasi" :value="$stats['sedang_visitasi']" description="Asesor di lapangan" icon="people" gradient="linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Perlu Validator" :value="$stats['perlu_validator']" description="Untuk pelaporan" icon="exclamation-triangle" gradient="linear-gradient(135deg, #fa709a 0%, #fee140 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Selesai" :value="$stats['selesai']" description="AL dilaporkan" icon="check-circle" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Filters -->
        <div class="col-lg-3 mb-4">
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

                        <!-- Status AL -->
                        <div class="mb-3">
                            <label class="form-label text-white">Status</label>
                            <select name="status_al" id="statusALFilter" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="sedang_visitasi" {{ request('status_al') == 'sedang_visitasi' ? 'selected' : '' }}>Sedang Visitasi</option>
                                <option value="perlu_validator" {{ request('status_al') == 'perlu_validator' ? 'selected' : '' }}>Perlu Validator</option>
                                <option value="selesai" {{ request('status_al') == 'selesai' ? 'selected' : '' }}>Selesai</option>
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
        </div>

        <!-- Table Content -->
        <div class="col-lg-9">
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
                    @include('de.pelaksanaan-al.components.table-content', ['pengajuans' => $pengajuans])
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
            , status_al: document.getElementById('statusALFilter').value
        , };

        await loadTable(params);
    }

    // Reset filters
    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('universityFilter').value = '';
        document.getElementById('statusALFilter').value = '';
        loadTable({});
    }

    // Load table via AJAX
    async function loadTable(params = {}) {
        const loadingOverlay = document.getElementById('tableLoading');
        const tableContainer = document.getElementById('tableContainer');

        try {
            loadingOverlay.classList.remove('d-none');

            const queryString = new URLSearchParams(params).toString();
            const response = await fetch(`{{ route('de.pelaksanaan-al') }}?${queryString}`, {
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
            alert('Gagal memuat data. Silakan refresh halaman.');
        } finally {
            loadingOverlay.classList.add('d-none');
        }
    }

    // Auto-apply filters on change
    ['searchInput', 'universityFilter', 'statusALFilter'].forEach(id => {
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
