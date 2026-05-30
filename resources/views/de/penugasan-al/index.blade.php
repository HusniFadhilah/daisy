@extends('layouts.template.app')

@section('title', 'Penugasan Asesmen Lapangan (AL)')

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
            <li class="breadcrumb-item active">Penugasan Asesor AL</li>
        </ol>
    </nav>
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-building"></i> Penugasan Asesor AL
            </h4>
            <p class="text-muted mb-0">Tugaskan asesor untuk melakukan Asesmen Lapangan</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Permohonan" :value="$stats['total']" description="Permohonan akreditasi PS yang telah memasuki tahap AL" icon="clipboard-check" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Siap AL" :value="$stats['siap_al']" description="AK selesai, siap visitasi, perlu menugaskan asesor AL" icon="exclamation-triangle" gradient="linear-gradient(135deg, #ee0979 0%, #ff6a00 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Sudah Ditugaskan" :value="$stats['sudah_ditugaskan']" description="Sudah ditugaskan dan saat ini sedang proses visitasi" icon="hourglass-split" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Selesai" :value="$stats['selesai']" description="AL telah selesai" icon="check-circle" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Filters -->

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
                    @include('de.penugasan-al.components.table-content', ['pengajuans' => $pengajuans])
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
            const response = await fetch(`{{ route('de.penugasan-al') }}?${queryString}`, {
                method: 'GET'
                , headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    , 'Accept': 'application/json'
                , }
            });

            const data = await response.json();

            if (data.success) {
                tableContainer.innerHTML = data.html;
                if (typeof initSelect2All === 'function') initSelect2All(tableContainer);

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
