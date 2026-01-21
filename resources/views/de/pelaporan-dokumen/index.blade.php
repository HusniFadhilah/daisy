@extends('layouts.template.app')

@section('title', 'Monitoring Pelaporan Validasi Dokumen')

@push('styles')
<style>
    .stat-card {
        border-radius: 12px;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-selesai {
        background: #d4edda;
        color: #155724;
    }

    .status-menunggu {
        background: #fff3cd;
        color: #856404;
    }

    .status-belum {
        background: #f8d7da;
        color: #721c24;
    }

    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
    }

    .action-btn {
        transition: all 0.3s ease;
    }

    .action-btn:hover {
        transform: scale(1.05);
    }

    .spinner-border {
        animation: spinner-border 0.75s linear infinite;
    }

    @keyframes spinner-border {
        to {
            transform: rotate(360deg);
        }
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-text"></i> Monitoring Pelaporan Validasi Dokumen
            </h4>
            <p class="text-muted mb-0">Pantau proses pelaporan validasi dokumen oleh validator</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Total Permohonan</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-2 fw-bold">{{ $stats['total'] }}</h2>
                            <small class="opacity-75">Sedang dalam tahap pelaporan</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Belum Upload</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-2 fw-bold">{{ $stats['belum_upload'] }}</h2>
                            <small class="opacity-75">Validator belum upload laporan</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Menunggu Finalisasi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-2 fw-bold">{{ $stats['menunggu_finalisasi'] }}</h2>
                            <small class="opacity-75">Sudah upload, belum finalisasi</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Selesai Dilaporkan</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-2 fw-bold">{{ $stats['selesai'] }}</h2>
                            <small class="opacity-75">Pelaporan telah difinalisasi</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
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
                            <input type="text" name="search" id="searchInput" class="form-control" placeholder="Nomor permohonan / nama prodi..." value="{{ request('search') }}">
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

                        <!-- Status Pelaporan -->
                        <div class="mb-3">
                            <label class="form-label text-white">Status Pelaporan</label>
                            <select name="status_pelaporan" id="statusPelaporanFilter" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="belum_upload" {{ request('status_pelaporan') == 'belum_upload' ? 'selected' : '' }}>Belum Upload</option>
                                <option value="sudah_upload" {{ request('status_pelaporan') == 'sudah_upload' ? 'selected' : '' }}>Menunggu Finalisasi</option>
                                <option value="selesai" {{ request('status_pelaporan') == 'selesai' ? 'selected' : '' }}>Selesai</option>
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
                    @include('de.pelaporan-dokumen.components.table-content', ['pengajuans' => $pengajuans])
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
            , status_pelaporan: document.getElementById('statusPelaporanFilter').value
        , };

        await loadTable(params);
    }

    // Reset filters
    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('universityFilter').value = '';
        document.getElementById('statusPelaporanFilter').value = '';
        loadTable({});
    }

    // Load table via AJAX
    async function loadTable(params = {}) {
        const loadingOverlay = document.getElementById('tableLoading');
        const tableContainer = document.getElementById('tableContainer');

        try {
            loadingOverlay.classList.remove('d-none');

            const queryString = new URLSearchParams(params).toString();
            const response = await fetch(`{{ route('de.pelaporan-dokumen') }}?${queryString}`, {
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
    ['searchInput', 'universityFilter', 'statusPelaporanFilter'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            if (id === 'searchInput') {
                // Debounce search input
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
