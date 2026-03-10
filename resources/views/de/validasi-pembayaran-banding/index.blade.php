{{-- resources/views/de/validasi-pembayaran-banding/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Validasi Pembayaran Banding')

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
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Validasi Pembayaran Banding</li>
        </ol>
    </nav>

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1"><i class="bi bi-credit-card"></i> Validasi Pembayaran Banding</h4>
            <p class="text-muted mb-0">Monitor invoice dan validasi pembayaran proses banding</p>
        </div>
        {{-- Tombol Kirim Invoice --}}
        <div class="text-end">
            <button type="button" class="btn btn-primary {{ $stats['belum_invoice'] === 0 ? 'disabled' : '' }}" data-bs-toggle="modal" data-bs-target="#modalKirimInvoiceBanding" {{ $stats['belum_invoice'] === 0 ? 'disabled' : '' }}>
                <i class="bi bi-send"></i> Kirim Invoice Banding
            </button>
            <div class="mt-1">
                <small class="text-muted">
                    {{ $stats['belum_invoice'] }} permohonan belum dikirimi invoice
                </small>
            </div>
        </div>
    </div>

    {{-- Alert perlu kirim invoice --}}
    @if($stats['belum_invoice'] > 0)
    <div class="alert alert-warning alert-permanent border-start border-2 border-warning mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-send fs-2 me-3 text-warning"></i>
            <div>
                <h5 class="mb-1 fw-bold">Invoice Banding Belum Dikirim</h5>
                <p class="mb-0">
                    Terdapat <strong>{{ $stats['belum_invoice'] }}</strong> permohonan banding
                    dengan status <em>Banding Diterima</em> yang belum dikirimi invoice pembayaran.
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Alert menunggu validasi --}}
    @if($stats['menunggu_verifikasi'] > 0)
    <div class="alert alert-info alert-permanent border-start border-2 border-info mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-clock-history fs-2 me-3 text-info"></i>
            <div>
                <h5 class="mb-1 fw-bold">Menunggu Validasi</h5>
                <p class="mb-0">
                    Terdapat <strong>{{ $stats['menunggu_verifikasi'] }}</strong>
                    pembayaran banding yang menunggu validasi dari bagian keuangan.
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Stat Cards --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-5 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Invoice" :value="$stats['total']" description="Invoice banding yang telah dibuat" icon="receipt" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>
        <div class="col mb-3">
            <x-stat-card title="Belum Dibayar" :value="$stats['menunggu_pembayaran']" description="Menunggu pembayaran dari PS" icon="hourglass-split" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
        </div>
        <div class="col mb-3">
            <x-stat-card title="Menunggu Validasi" :value="$stats['menunggu_verifikasi']" description="Bukti bayar diupload, perlu divalidasi" icon="clock-history" gradient="linear-gradient(135deg, #ffc107 0%, #ff8c00 100%)" />
        </div>
        <div class="col mb-3">
            <x-stat-card title="Upload Ulang" :value="$stats['upload_ulang']" description="Diminta perbaikan bukti pembayaran" icon="arrow-repeat" gradient="linear-gradient(135deg, #868f96 0%, #596164 100%)" />
        </div>
        <div class="col mb-3">
            <x-stat-card title="Tervalidasi" :value="$stats['terverifikasi']" description="Pembayaran banding sudah lunas" icon="check-circle" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
        </div>
    </div>

    {{-- Content --}}
    <div class="row">

        {{-- Filter Sidebar --}}
        <div class="col-lg-3 mb-4">
            <div class="card filter-card">
                <div class="card-header border-0">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter & Pencarian</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm">
                        <div class="mb-3">
                            <label class="form-label text-white">Cari Invoice / Prodi</label>
                            <input type="text" name="search" id="searchInput" class="form-control" placeholder="Nomor invoice atau nama prodi..." value="{{ request('search') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Status Pembayaran</label>
                            <select name="status_pembayaran" id="statusFilter" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="menunggu_pembayaran" @selected(request('status_pembayaran')==='menunggu_pembayaran' )>
                                    Menunggu Pembayaran
                                </option>
                                <option value="menunggu_verifikasi" @selected(request('status_pembayaran')==='menunggu_verifikasi' )>
                                    Menunggu Validasi
                                </option>
                                <option value="upload_ulang" @selected(request('status_pembayaran')==='upload_ulang' )>
                                    Upload Ulang
                                </option>
                                <option value="terverifikasi" @selected(request('status_pembayaran')==='terverifikasi' )>
                                    Tervalidasi
                                </option>
                                <option value="ditolak" @selected(request('status_pembayaran')==='ditolak' )>
                                    Ditolak
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Universitas</label>
                            <select name="university_id" id="universityFilter" class="form-select">
                                <option value="">Semua Universitas</option>
                                @foreach($universities as $univ)
                                <option value="{{ $univ->id }}" @selected(request('university_id')==$univ->id)>
                                    {{ $univ->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Jenjang</label>
                            <select name="degree_level_id" id="degreeLevelFilter" class="form-select">
                                <option value="">Semua Jenjang</option>
                                @foreach($degreeLevels as $level)
                                <option value="{{ $level->id }}" @selected(request('degree_level_id')==$level->id)>
                                    {{ $level->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

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

        {{-- Main Table --}}
        <div class="col-lg-9">
            <div class="position-relative">
                <div id="tableLoading" class="position-absolute top-0 start-0 w-100 h-100 d-none" style="background:rgba(255,255,255,.9);z-index:1000;">
                    <div class="d-flex justify-content-center align-items-center h-100" style="min-height:400px;">
                        <div class="text-center">
                            <div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div>
                            <p class="mt-3 text-muted">Memuat data...</p>
                        </div>
                    </div>
                </div>
                <div id="tableContainer">
                    @include('de.validasi-pembayaran-banding.components.table-content', compact('pembayarans'))
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Modal Kirim Invoice Banding --}}
@include('de.validasi-pembayaran-banding.components.modal-kirim-invoice')
@endsection

@push('scripts')
<script>
    function applyFilters() {
        loadTable({
            search: document.getElementById('searchInput').value
            , status_pembayaran: document.getElementById('statusFilter').value
            , university_id: document.getElementById('universityFilter').value
            , degree_level_id: document.getElementById('degreeLevelFilter').value
        , });
    }

    function resetFilters() {
        ['searchInput', 'statusFilter', 'universityFilter', 'degreeLevelFilter']
        .forEach(id => document.getElementById(id).value = '');
        loadTable({});
    }

    async function loadTable(params = {}) {
        const loading = document.getElementById('tableLoading');
        const container = document.getElementById('tableContainer');
        try {
            loading.classList.remove('d-none');
            const qs = new URLSearchParams(params).toString();
            const res = await fetch(`{{ route('de.validasi-pembayaran-banding.index') }}?${qs}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    , 'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) container.innerHTML = data.html;
        } catch (e) {
            console.error(e);
        } finally {
            loading.classList.add('d-none');
        }
    }

    ['statusFilter', 'universityFilter', 'degreeLevelFilter'].forEach(id => {
        const selectId = document.getElementById(id)
        if (selectId) selectId.addEventListener('change', applyFilters);
    });
    let searchTimer;
    const searchInput = document.getElementById('searchInput')
    if (searchInput) searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFilters, 500);
    });

</script>
@endpush
