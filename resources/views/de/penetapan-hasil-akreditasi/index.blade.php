{{-- resources/views/de/penetapan-hasil-akreditasi/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Penetapan Hasil Akreditasi')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Penetapan Hasil Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-clipboard-check"></i> Penetapan Hasil Akreditasi
            </h4>
            <p class="text-muted mb-0">Kelola dan tetapkan hasil akreditasi program studi</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Permohonan Akreditasi" :value="$stats['total']" description="Permohonan akreditasi yang masuk tahap penetapan hasil" icon="collection" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Belum Ditetapkan" :value="$stats['belum_ditetapkan']" description="Penetapan hasil masih dalam proses" icon="clock" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Sudah Ditetapkan" :value="$stats['sudah_ditetapkan']" description="Hasil telah ditetapkan" icon="check-circle" gradient="linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)" />
        </div>
    </div>

    <!-- Table -->
    <div id="tableContainer" class="position-relative">
        <div id="loadingOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none" style="background: rgba(255,255,255,0.8); z-index: 10;">
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        <div id="tableContent">
            @include('de.penetapan-hasil-akreditasi.components.table-content', ['pengajuans' => $pengajuans])
        </div>
    </div>
</div>

@push('scripts')
<script>
    var searchTimeout;

    function getValueById(id) {
        var el = document.getElementById(id);
        return el ? el.value : '';
    }

    function applyFilters() {
        var status = getValueById('filterStatus');
        var peringkat = getValueById('filterPeringkat');
        var search = getValueById('searchInput');

        var overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.classList.remove('d-none');

        var params = new URLSearchParams();
        if (status) params.append('status', status);
        if (peringkat) params.append('peringkat', peringkat);
        if (search) params.append('search', search);

        fetch(`{{ route('de.penetapan-hasil-akreditasi') }}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    , 'Accept': 'application/json'
                }
            })
            .then(function(r) {
                return r.json();
            })
            .then(function(data) {
                if (data && data.success) {
                    var table = document.getElementById('tableContent');
                    if (table) table.innerHTML = data.html;
                }
            })
            .catch(function(err) {
                console.error(err);
                alert('Terjadi kesalahan saat memuat data');
            })
            .finally(function() {
                if (overlay) overlay.classList.add('d-none');
            });
    }

    var btnApply = document.getElementById('btnApply');
    if (btnApply) {
        btnApply.addEventListener('click', function(e) {
            e.preventDefault();
            applyFilters();
        });
    }

    var filterStatus = document.getElementById('filterStatus');
    if (filterStatus) filterStatus.addEventListener('change', applyFilters);

    var filterPeringkat = document.getElementById('filterPeringkat');
    if (filterPeringkat) filterPeringkat.addEventListener('change', applyFilters);

    var searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 500);
        });
    }

</script>
@endpush
@endsection
