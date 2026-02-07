@extends('layouts.template.app')

@section('title', 'Monitor Pelaporan AL')

@section('content')
<div class="container-fluid py-3">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-text"></i> Monitor Pelaporan AL
            </h4>
            <p class="text-muted mb-0">Monitor pelaporan AL setelah validasi selesai</p>
        </div>
        <div>
            <button class="btn btn-outline-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total AL Selesai" :value="$stats['total']" description="Validasi selesai" icon="clipboard-check" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Belum Dilaporkan" :value="$stats['belum_lapor']" description="Menunggu pelaporan" icon="exclamation-triangle" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Sudah Dilaporkan" :value="$stats['sudah_lapor']" description="Pelaporan lengkap" icon="check-circle" gradient="linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)" />
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body" style="background: linear-gradient(135deg, #932136 0%, #870820 100%);">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-white">Universitas:</label>
                    <select id="filterUniversity" class="form-select">
                        <option value="">Semua Universitas</option>
                        @foreach($universities as $univ)
                        <option value="{{ $univ->id }}">{{ $univ->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white">Status Pelaporan:</label>
                    <select id="filterStatus" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="belum_lapor">Belum Dilaporkan</option>
                        <option value="sudah_lapor">Sudah Dilaporkan</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white">Cari:</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Nomor permohonan atau nama prodi...">
                </div>
            </div>
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
            @include('de.pelaporan-al.components.table-content', ['pengajuans' => $pengajuans])
        </div>
    </div>
</div>

@push('scripts')
<script>
    let searchTimeout;

    // Apply filters
    function applyFilters() {
        const university = document.getElementById('filterUniversity').value;
        const status = document.getElementById('filterStatus').value;
        const search = document.getElementById('searchInput').value;

        // Show loading
        document.getElementById('loadingOverlay').classList.remove('d-none');

        // Build URL with query params
        const params = new URLSearchParams();
        if (university) params.append('university_id', university);
        if (status) params.append('status_pelaporan', status);
        if (search) params.append('search', search);

        // Fetch data
        fetch(`{{ route('de.pelaporan-al') }}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    , 'Accept': 'application/json'
                , }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('tableContent').innerHTML = data.html;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat data');
            })
            .finally(() => {
                document.getElementById('loadingOverlay').classList.add('d-none');
            });
    }

    // Event listeners
    document.getElementById('filterUniversity').addEventListener('change', applyFilters);
    document.getElementById('filterStatus').addEventListener('change', applyFilters);

    // Debounced search
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });

</script>
@endpush
@endsection
