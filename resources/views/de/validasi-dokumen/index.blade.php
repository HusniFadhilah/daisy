@extends('layouts.template.app')

@section('title', 'Validasi Dokumen Dokumen')

@push('styles')
<style>
    .filter-card {
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
        color: white;
    }

    .progress-ring {
        width: 60px;
        height: 60px;
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
            <li class="breadcrumb-item active">Validasi Dokumen</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-clipboard-check"></i> Validasi Dokumen</h4>
            <p class="text-muted mb-0">Monitor validasi dokumen</p>
        </div>
    </div>
    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Penugasan" :value="$stats['total']" description="Validator telah ditugaskan oleh LAMDEPILAR untuk memvalidasi Dokumen" icon="clipboard-check" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Menunggu Konfirmasi" :value="$stats['pending']" description="Validator telah ditugaskan oleh LAMDEPILAR untuk memvalidasi dokumen, tetapi belum ada status menerima/menolak tawaran" icon="hourglass-split" gradient="linear-gradient(135deg, #ffc107 0%, #ff8c00 100%)" />
        </div>

        {{-- <div class="col mb-3">
            <x-stat-card title="Belum Mulai" :value="$stats['not_started']" description="Validator telah menerima tugas validasi dokumen dari LAMDEPILAR, tetapi belum dikerjakan" icon="clock" gradient="linear-gradient(135deg, #868f96 0%, #596164 100%)" />
        </div> --}}

        <div class="col mb-3">
            <x-stat-card title="Sedang Validasi" :value="$stats['in_progress']" description="Validator telah menerima tugas validasi dokumen dari LAMDEPILAR, dan sedang mengerjakan validasi dokumen" icon="arrow-repeat" gradient="linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)" />
        </div>

        {{-- <div class="col mb-3">
            <x-stat-card title="Perlu Revisi" :value="$stats['revision_required']" description="Validator sedang mengerjakan validasi dokumen, dan meminta PS untuk melakukan revisi Dokumen" icon="exclamation-triangle" gradient="linear-gradient(135deg, #ee0979 0%, #ff6a00 100%)" />
        </div> --}}

        <div class="col mb-3">
            <x-stat-card title="Disetujui" :value="$stats['approved']" description="Proses validasi Dokumen telah selesai dilakukan" icon="check-circle" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="d-flex align-items-center mb-4">
        <button type="button" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalKirimReminder">
            <i class="bi bi-bell"></i> Kirim Pengingat ke Validator
        </button>
    </div>

    <!-- Content -->
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
                    <form id="filterForm">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label text-white">Cari</label>
                            <input type="text" name="search" id="searchInput" class="form-control" placeholder="Nomor permohonan, prodi, atau validator..." value="{{ request('search') }}">
    </div>

    <!-- Status Penawaran -->
    <div class="mb-3">
        <label class="form-label text-white">Status Penawaran</label>
        <select name="status_penawaran" id="statusPenawaranFilter" class="form-select">
            <option value="">Semua Status</option>
            <option value="pending">Menunggu Konfirmasi</option>
            <option value="accepted">Diterima</option>
            <option value="rejected">Ditolak</option>
        </select>
    </div>

    <!-- Status Pekerjaan -->
    <div class="mb-3">
        <label class="form-label text-white">Status Pekerjaan</label>
        <select name="status_pekerjaan" id="statusPekerjaanFilter" class="form-select">
            <option value="">Semua Status</option>
            <option value="not_started">Belum Mulai</option>
            <option value="in_progress">Sedang Dikerjakan</option>
            <option value="submitted">Sudah Submit</option>
            <option value="revision_required">Perlu Revisi</option>
            <option value="approved">Disetujui</option>
        </select>
    </div>

    <!-- Validator -->
    <div class="mb-3">
        <label class="form-label text-white">Validator</label>
        <select name="validator_id" id="validatorFilter" class="form-select">
            <option value="">Semua Validator</option>
            @foreach($validators as $validator)
            <option value="{{ $validator->id }}">{{ $validator->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- University -->
    <div class="mb-3">
        <label class="form-label text-white">Universitas</label>
        <select name="university_id" id="universityFilter" class="form-select">
            <option value="">Semua Universitas</option>
            @foreach($universities as $univ)
            <option value="{{ $univ->id }}">{{ $univ->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Degree Level -->
    <div class="mb-3">
        <label class="form-label text-white">Jenjang</label>
        <select name="degree_level_id" id="degreeLevelFilter" class="form-select">
            <option value="">Semua Jenjang</option>
            @foreach($degreeLevels as $level)
            <option value="{{ $level->id }}">{{ $level->name }}</option>
            @endforeach
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

<!-- Main Content -->
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

        <!-- Table Content -->
        <div id="tableContainer">
            @include('de.validasi-dokumen.components.table-content', ['assignments' => $assignments])
        </div>
    </div>
</div>
</div>
</div>

<!-- Modal Kirim Reminder -->
@include('de.validasi-dokumen.components.modal-kirim-reminder', ['assignments' => $assignments])

@endsection

@push('scripts')
<script>
    function applyFilters() {
        const params = {
            search: document.getElementById('searchInput').value
            , status_penawaran: document.getElementById('statusPenawaranFilter').value
            , status_pekerjaan: document.getElementById('statusPekerjaanFilter').value
            , validator_id: document.getElementById('validatorFilter').value
            , university_id: document.getElementById('universityFilter').value
            , degree_level_id: document.getElementById('degreeLevelFilter').value
        , };

        loadTable(params);
    }

    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusPenawaranFilter').value = '';
        document.getElementById('statusPekerjaanFilter').value = '';
        document.getElementById('validatorFilter').value = '';
        document.getElementById('universityFilter').value = '';
        document.getElementById('degreeLevelFilter').value = '';
        loadTable({});
    }

    async function loadTable(params = {}) {
        const loadingOverlay = document.getElementById('tableLoading');
        const tableContainer = document.getElementById('tableContainer');

        try {
            loadingOverlay.classList.remove('d-none');

            const queryString = new URLSearchParams(params).toString();
            const response = await fetch(`{{ route('de.validasi-dokumen') }}?${queryString}`, {
                method: 'GET'
                , headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    , 'Accept': 'application/json'
                , }
            });

            const data = await response.json();

            if (data.success) {
                tableContainer.innerHTML = data.html;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Gagal memuat data.');
        } finally {
            loadingOverlay.classList.add('d-none');
        }
    }

    // Auto-apply filters
    ['searchInput', 'statusPenawaranFilter', 'statusPekerjaanFilter', 'validatorFilter', 'universityFilter', 'degreeLevelFilter'].forEach(id => {
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
