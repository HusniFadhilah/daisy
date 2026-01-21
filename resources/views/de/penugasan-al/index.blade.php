@extends('layouts.template.app')

@section('title', 'Penugasan Asesor AL')

@section('content')
<div class="container-fluid py-3">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-people-fill"></i> Penugasan Asesor Asesmen Lapangan (AL)
            </h4>
            <p class="text-muted mb-0">Kelola penugasan asesor untuk visitasi lapangan</p>
        </div>
        <div>
            <button class="btn btn-outline-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">

        <!-- Total -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Total</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
                        </div>
                        <i class="bi bi-stack" style="font-size: 2.5rem; opacity: 0.35;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Siap AL -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Siap AL</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['siap_al'] }}</h2>
                        </div>
                        <i class="bi bi-check-circle" style="font-size: 2.5rem; opacity: 0.35;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ditugaskan -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Ditugaskan</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['sudah_ditugaskan'] }}</h2>
                        </div>
                        <i class="bi bi-person-check" style="font-size: 2.5rem; opacity: 0.35;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Berlangsung -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Berlangsung</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['sedang_berlangsung'] }}</h2>
                        </div>
                        <i class="bi bi-gear" style="font-size: 2.5rem; opacity: 0.4;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selesai -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #198754 0%, #0f5132 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Selesai</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['selesai'] + $stats['dilaporkan'] }}</h2>
                        </div>
                        <i class="bi bi-check-circle-fill" style="font-size: 2.5rem; opacity: 0.35;"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
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
                    <label class="form-label text-white">Status:</label>
                    <select id="filterStatus" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI }}">Siap AL</option>
                        <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED }}">Sudah Ditugaskan</option>
                        <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS }}">Sedang Berlangsung</option>
                        <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI }}">Selesai</option>
                        <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN }}">Dilaporkan</option>
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
            @include('de.penugasan-al.components.table-content', ['pengajuans' => $pengajuans])
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
        if (status) params.append('status', status);
        if (search) params.append('search', search);

        // Fetch data
        fetch(`{{ route('de.penugasan-al') }}?${params.toString()}`, {
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
