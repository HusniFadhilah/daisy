{{-- resources/views/de/penugasan-banding/index.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Penugasan Banding')

@section('content')
<div class="container-fluid py-3">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Penugasan Banding</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1"><i class="bi bi-person-check"></i> Penugasan Banding</h4>
            <p class="text-muted mb-0">Tugaskan Asesor Banding & Validator untuk surveillance proses banding</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Banding" :value="$stats['total']" description="Permohonan yang memasuki fase banding" icon="clipboard-check" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>
        <div class="col mb-3">
            <x-stat-card title="Belum Ditugaskan" :value="$stats['belum_ditugaskan']" description="Perlu surveyor & validator" icon="exclamation-triangle" gradient="linear-gradient(135deg, #ee0979 0%, #ff6a00 100%)" />
        </div>
        <div class="col mb-3">
            <x-stat-card title="Sudah Ditugaskan" :value="$stats['sudah_ditugaskan']" description="Sedang proses banding" icon="hourglass-split" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
        </div>
        <div class="col mb-3">
            <x-stat-card title="Selesai" :value="$stats['selesai']" description="Banding telah dilaporkan" icon="check-circle" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
        </div>
    </div>

    {{-- Table --}}
    <div class="col-12">
        <div class="position-relative">
            <div id="tableLoading" class="position-absolute top-0 start-0 w-100 h-100 d-none" style="background: rgba(255,255,255,0.9); z-index: 1000;">
                <div class="d-flex justify-content-center align-items-center h-100" style="min-height:300px;">
                    <div class="text-center">
                        <div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div>
                        <p class="mt-3 text-muted">Memuat data...</p>
                    </div>
                </div>
            </div>
            <div id="tableContainer">
                @include('de.penugasan-banding.components.table-content', ['pengajuans' => $pengajuans])
            </div>
        </div>
    </div>

</div>
@push('scripts')
<script>
    async function applyFilters() {
        const searchEl = document.getElementById('searchInput');
        const univEl = document.getElementById('universityFilter');
        const statusEl = document.getElementById('statusFilter');
        const params = {
            search: searchEl ? searchEl.value : ''
            , university_id: univEl ? univEl.value : ''
            , status_surveillance: statusEl ? statusEl.value : ''
        , };
        await loadTable(params);
    }

    function resetFilters() {
        ['searchInput', 'universityFilter', 'statusFilter'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        loadTable({});
    }

    async function loadTable(params = {}) {
        const loading = document.getElementById('tableLoading');
        const container = document.getElementById('tableContainer');
        try {
            loading.classList.remove('d-none');
            const qs = new URLSearchParams(params).toString();
            const response = await fetch(`{{ route('de.penugasan-banding') }}?${qs}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    , 'Accept': 'application/json'
                }
            , });
            const data = await response.json();
            if (data.success) {
                container.innerHTML = data.html;
                const newUrl = new URL(window.location);
                Object.keys(params).forEach(k => params[k] ?
                    newUrl.searchParams.set(k, params[k]) :
                    newUrl.searchParams.delete(k)
                );
                window.history.pushState({}, '', newUrl);
            }
        } catch (e) {
            console.error(e);
            Swal.fire('Perhatian', 'Gagal memuat data. Silakan refresh.', 'error');
        } finally {
            loading.classList.add('d-none');
        }
    }

    let searchTimeout;
    const searchEl = document.getElementById('searchInput');
    if (searchEl) {
        searchEl.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => applyFilters(), 500);
        });
    }
    ['universityFilter', 'statusFilter'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', applyFilters);
    });

</script>
@endpush
@endsection
