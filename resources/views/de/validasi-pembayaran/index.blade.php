@extends('layouts.template.app')

@section('title', 'Validasi Pembayaran')

@push('styles')
<style>
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

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
            <li class="breadcrumb-item active">Validasi Pembayaran</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1"><i class="bi bi-credit-card"></i> Validasi Pembayaran</h4>
            <p class="text-muted mb-0">Monitor validasi pembayaran akreditasi</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-5 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Invoice" :value="$stats['total']" description="Total invoice yang telah dibuat" icon="receipt" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Menunggu Pembayaran" :value="$stats['menunggu_pembayaran']" description="Invoice telah dikirim, PS belum melakukan pembayaran" icon="hourglass-split" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Menunggu Validasi" :value="$stats['menunggu_verifikasi']" description="Bukti bayar telah diupload, menunggu validasi oleh bagian keuangan" icon="clock-history" gradient="linear-gradient(135deg, #ffc107 0%, #ff8c00 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Upload Ulang" :value="$stats['upload_ulang']" description="Bukti bayar / pembayaran belum valid, perlu perbaikan" icon="arrow-repeat" gradient="linear-gradient(135deg, #868f96 0%, #596164 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Tervalidasi" :value="$stats['terverifikasi']" description="Pembayaran valid & disetujui oleh bagian keuangan" icon="check-circle" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
        </div>
    </div>

    <!-- Payment Summary Card with Password Protection - BARU -->
    <div class="row mb-4">
        <div class="col-md-9 mt-3">
            @include('de.validasi-pembayaran.components.payment-summary-card')
        </div>
        <div class="col-md-3 mt-2">
            {{-- Tombol reminder keuangan --}}
            <button type="button" class="btn btn-primary btn-sm my-1 w-100" data-bs-toggle="modal" data-bs-target="#modalReminderKeuangan" {{ $pendingValidasi->isEmpty() ? 'disabled' : '' }}>
                <i class="bi bi-bell"></i> Ingatkan Keuangan
                @if($pendingValidasi->isNotEmpty())
                <span class="badge bg-danger">{{ $pendingValidasi->count() }}</span>
                @endif
            </button>

            {{-- Tombol reminder UPPS --}}
            <button type="button" class="btn btn-primary btn-sm my-1 w-100" data-bs-toggle="modal" data-bs-target="#modalReminderUPPS" {{ $pendingPembayaran->isEmpty() ? 'disabled' : '' }}>
                <i class="bi bi-bell"></i> Ingatkan UPPS
                @if($pendingPembayaran->isNotEmpty())
                <span class="badge bg-danger">{{ $pendingPembayaran->count() }}</span>
                @endif
            </button>
        </div>
    </div>

    <!-- Content -->
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
                    <form id="filterForm">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label text-white">Cari Invoice/Prodi</label>
                            <input type="text" name="search" id="searchInput" class="form-control" placeholder="Nomor invoice atau nama prodi..." value="{{ request('search') }}">
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label text-white">Status Pembayaran</label>
                            <select name="status_pembayaran" id="statusFilter" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="menunggu_pembayaran" {{ request('status_pembayaran') == 'menunggu_pembayaran' ? 'selected' : '' }}>
                                    Menunggu Pembayaran
                                </option>
                                <option value="menunggu_verifikasi" {{ request('status_pembayaran') == 'menunggu_verifikasi' ? 'selected' : '' }}>
                                    Menunggu Validasi
                                </option>
                                <option value="terverifikasi" {{ request('status_pembayaran') == 'terverifikasi' ? 'selected' : '' }}>
                                    Tervalidasi
                                </option>
                                <option value="upload_ulang" {{ request('status_pembayaran') == 'upload_ulang' ? 'selected' : '' }}>
                                    Upload Ulang
                                </option>
                                <option value="ditolak" {{ request('status_pembayaran') == 'ditolak' ? 'selected' : '' }}>
                                    Ditolak
                                </option>
                            </select>
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

                        <!-- Degree Level -->
                        <div class="mb-3">
                            <label class="form-label text-white">Jenjang</label>
                            <select name="degree_level_id" id="degreeLevelFilter" class="form-select">
                                <option value="">Semua Jenjang</option>
                                @foreach($degreeLevels as $level)
                                <option value="{{ $level->id }}" {{ request('degree_level_id') == $level->id ? 'selected' : '' }}>
                                    {{ $level->name }}
                                </option>
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
        </div>

        <!-- Main Content -->
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

                <!-- Table Content -->
                <div id="tableContainer">
                    @include('de.validasi-pembayaran.components.table-content', ['pembayarans' => $pembayarans])
                </div>
            </div>
        </div>
    </div>
</div>
<x-modal-kirim-reminder-pembayaran modal-id="modalReminderKeuangan" form-action="{{ route('de.validasi-pembayaran.kirim-reminder-keuangan') }}" title="Ingatkan Keuangan untuk Segera Validasi" :pembayarans="$pendingValidasi" input-name="id_pembayaran" item-description="Menunggu validasi keuangan" default-message="Yth. Bagian Keuangan LAMDEPILAR,

Terdapat bukti pembayaran yang masih menunggu validasi. Mohon segera diproses agar tidak menghambat proses akreditasi program studi terkait.

Terima kasih.
Sekretariat LAMDEPILAR" />
<x-modal-kirim-reminder-pembayaran modal-id="modalReminderUPPS" form-action="{{ route('de.validasi-pembayaran.kirim-reminder-upps') }}" title="Ingatkan UPPS untuk Segera Membayar" :pembayarans="$pendingPembayaran" input-name="id_pembayaran" item-description="Menunggu pembayaran / upload ulang bukti" default-message="Yth. Unit Pengelola Program Studi,

Kami mengingatkan bahwa invoice akreditasi Anda masih belum dibayarkan. Mohon segera lakukan pembayaran sebelum melewati tanggal jatuh tempo.

Terima kasih atas perhatiannya.
Sekretariat LAMDEPILAR" />
@endsection

@push('scripts')
<script>
    // Apply filters
    function applyFilters() {
        const params = {
            search: document.getElementById('searchInput').value
            , status_pembayaran: document.getElementById('statusFilter').value
            , university_id: document.getElementById('universityFilter').value
            , degree_level_id: document.getElementById('degreeLevelFilter').value
        , };

        loadTable(params);
    }

    // Reset filters
    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('universityFilter').value = '';
        document.getElementById('degreeLevelFilter').value = '';
        loadTable({});
    }

    // Load table via AJAX
    async function loadTable(params = {}) {
        const loadingOverlay = document.getElementById('tableLoading');
        const tableContainer = document.getElementById('tableContainer');

        try {
            loadingOverlay.classList.remove('d-none');

            const queryString = new URLSearchParams(params).toString();
            const response = await fetch(`{{ route('de.validasi-pembayaran') }}?${queryString}`, {
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
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Perhatian', 'Gagal memuat data', 'error');
        } finally {
            loadingOverlay.classList.add('d-none');
        }
    }

    // Auto-apply filters on change
    ['searchInput', 'statusFilter', 'universityFilter', 'degreeLevelFilter'].forEach(id => {
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
