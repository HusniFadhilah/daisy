@extends('layouts.template.app')

@section('title', 'Monitoring Validasi AK')

@section('content')
<div class="container-fluid py-3">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-clipboard-check"></i> Monitoring Validasi Asesmen Kecukupan (AK)
            </h4>
            <p class="text-muted mb-0">Monitoring proses validasi hasil asesmen kecukupan oleh validator</p>
        </div>
        <div>
            <button class="btn btn-outline-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Total AK</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
                        </div>
                        <div>
                            <i class="bi bi-stack" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Belum Mulai</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['belum_mulai'] }}</h2>
                        </div>
                        <div>
                            <i class="bi bi-hourglass" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Sedang Penilaian</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['sedang_penilaian'] }}</h2>
                        </div>
                        <div>
                            <i class="bi bi-pencil-square" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Sedang Validasi</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['sedang_validasi'] }}</h2>
                        </div>
                        <div>
                            <i class="bi bi-shield-check" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Selesai</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['selesai'] }}</h2>
                        </div>
                        <div>
                            <i class="bi bi-check-circle" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #14b8a6 0%, #0f766e 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Dilaporkan</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['dilaporkan'] }}</h2>
                        </div>
                        <div>
                            <i class="bi bi-file-earmark-check" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
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
                    <label class="form-label text-white">Status Validasi:</label>
                    <select id="filterStatus" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="belum_mulai">Belum Mulai</option>
                        <option value="sedang_penilaian">Sedang Penilaian</option>
                        <option value="sedang_validasi">Sedang Validasi</option>
                        <option value="selesai">Selesai</option>
                        <option value="dilaporkan">Dilaporkan</option>
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
            @include('de.validasi-ak.components.table-content', ['pengajuans' => $pengajuans])
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
        if (status) params.append('status_validasi', status);
        if (search) params.append('search', search);

        // Fetch data
        fetch(`{{ route('de.validasi-ak') }}?${params.toString()}`, {
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
