@extends('layouts.template.app')

@section('title', 'Penyampaian Hasil Akreditasi')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Penyampaian Hasil Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-clipboard-data"></i> Penyampaian Hasil Akreditasi
            </h4>
            <p class="text-muted mb-0">Kelola dan finalisasi hasil akreditasi program studi</p>
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
            <x-stat-card title="Total Permohonan Akreditasi" :value="$stats['total']" description="Permohonan akreditasi yang masuk tahap penyampaian hasil" icon="collection" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Belum Difinalisasi" :value="$stats['belum_final']" description="Penyampaian hasil masih berstatus draft" icon="clock" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Sudah Difinalisasi" :value="$stats['sudah_final']" description="Hasil AL telah difinalisasi" icon="check-circle" gradient="linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)" />
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
            @include('de.penyampaian-hasil-akreditasi.components.table-content', ['pengajuans' => $pengajuans])
        </div>
    </div>
</div>

@push('scripts')
<script>
    let searchTimeout;

    function applyFilters() {
        const status = document.getElementById('filterStatus').value;
        const peringkat = document.getElementById('filterPeringkat').value;
        const search = document.getElementById('searchInput').value;

        document.getElementById('loadingOverlay').classList.remove('d-none');

        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (peringkat) params.append('peringkat', peringkat);
        if (search) params.append('search', search);

        fetch(`{{ route('de.penyampaian-hasil-akreditasi') }}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    , 'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('tableContent').innerHTML = data.html;
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Perhatian', 'Terjadi kesalahan saat memuat data', 'error');
            })
            .finally(() => {
                document.getElementById('loadingOverlay').classList.add('d-none');
            });
    }

    document.getElementById('btnApply').addEventListener('click', function(e) {
        e.preventDefault();
        applyFilters();
    });

    document.getElementById('filterStatus').addEventListener('change', applyFilters);
    document.getElementById('filterPeringkat').addEventListener('change', applyFilters);

    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });

</script>
@endpush
@endsection
