@extends('layouts.template.app')

@section('title', 'Penerimaan Dokumen LED+Suplemen dan LKPS')

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

    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .doc-status-badge {
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
    }

    .doc-complete {
        background: #d4edda;
        color: #155724;
    }

    .doc-incomplete {
        background: #fff3cd;
        color: #856404;
    }

    .doc-none {
        background: #f8d7da;
        color: #721c24;
    }

    .progress-custom {
        height: 8px;
        border-radius: 10px;
        background: #e9ecef;
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
    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-4 row-cols-lg-5 mb-4">
        <div class="col mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Menunggu Dokumen</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0 fw-bold">{{ $stats['total_menunggu_dokumen'] }}</h2>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                    <small class="opacity-75">Menunggu PS mengupload draft LED+Suplemen dan LKPS</small>
                </div>
            </div>
        </div>

        <div class="col mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Dokumen Lengkap</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0 fw-bold">{{ $stats['total_dokumen_lengkap'] }}</h2>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                    <small class="opacity-75">LED+Suplemen dan LKPS siap divalidasi oleh validator</small>
                </div>
            </div>
        </div>

        <div class="col mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Dalam Validasi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0 fw-bold">{{ $stats['total_dalam_validasi'] }}</h2>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                    </div>
                    <small class="opacity-75">LED+Suplemen dan LKPS sedang divalidasi oleh validator</small>
                </div>
            </div>
        </div>

        <div class="col mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Perlu Revisi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0 fw-bold">{{ $stats['total_perlu_revisi'] }}</h2>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                    <small class="opacity-75">LED+Suplemen dan LKPS diminta revisi oleh validator</small>
                </div>
            </div>
        </div>

        <div class="col mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #06beb6 0%, #48b1bf 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Tervalidasi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0 fw-bold">{{ $stats['total_tervalidasi'] }}</h2>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-patch-check"></i>
                        </div>
                    </div>
                    <small class="opacity-75">Validasi LED+Suplemen dan LKPS selesai</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="d-flex align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-file-earmark-text"></i> Daftar Penerimaan Dokumen
        </h4>

        <button type="button" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalKirimReminder">
            <i class="bi bi-bell"></i> Kirim Reminder Upload
        </button>
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
                            <label class="form-label text-white">Cari Pengajuan</label>
                            <input type="text" name="search" id="searchInput" class="form-control" placeholder="Nomor pengajuan atau prodi..." value="{{ request('search') }}">
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label text-white">Status Permohonan</label>
                            <select name="status" id="statusFilter" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI }}">
                                    Menunggu Upload
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA }}">
                                    Dokumen Masuk
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI }}">
                                    Dokumen Lengkap
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING }}">
                                    Menunggu Validasi
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION }}">
                                    Dalam Validasi
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED }}">
                                    Perlu Revisi
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED }}">
                                    Tervalidasi
                                </option>
                            </select>
                        </div>

                        <!-- Document Status -->
                        <div class="mb-3">
                            <label class="form-label text-white">Status Dokumen</label>
                            <select name="doc_status" id="docStatusFilter" class="form-select">
                                <option value="">Semua</option>
                                <option value="complete">Lengkap</option>
                                <option value="incomplete">Belum Lengkap</option>
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
                    @include('de.penerimaan-dokumen.components.table-content', ['pengajuans' => $pengajuans])
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Kirim Reminder -->
@include('de.penerimaan-dokumen.components.modal-kirim-reminder', ['pengajuans' => $pengajuans])

@endsection

@push('scripts')
<script>
    function applyFilters() {
        const params = {
            search: document.getElementById('searchInput').value
            , status: document.getElementById('statusFilter').value
            , doc_status: document.getElementById('docStatusFilter').value
            , university_id: document.getElementById('universityFilter').value
            , degree_level_id: document.getElementById('degreeLevelFilter').value
        , };

        loadTable(params);
    }

    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('docStatusFilter').value = '';
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
            const response = await fetch(`{{ route('de.penerimaan-dokumen') }}?${queryString}`, {
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
    ['searchInput', 'statusFilter', 'docStatusFilter', 'universityFilter', 'degreeLevelFilter'].forEach(id => {
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
