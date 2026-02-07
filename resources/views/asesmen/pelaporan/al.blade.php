{{-- resources/views/asesmen/pelaporan/al.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Pelaporan AL')

@push('styles')
<style>
    .filter-section {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .search-box {
        position: relative;
    }

    .search-box .bi-search {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }

    .search-box input {
        padding-left: 36px;
    }

    .info-banner {
        background: linear-gradient(135deg, #198754 0%, #146c43 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .timeline-preview {
        background: white;
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
    }

    .timeline-step {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .timeline-step i {
        font-size: 1.2rem;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="welcome-section mb-4">
        <div class="welcome-content">
            <h2>
                <i class="bi bi-building-check text-white"></i>
                Pelaporan AL
            </h2>
            <p class="mb-0">Kelola pelaporan hasil Asesmen Lapangan (AL) Program Studi</p>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('pelaporan.index') }}" class="text-link">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
            </li>
            <li class="breadcrumb-item active">Pelaporan AL</li>
        </ol>
    </nav>

    @if($assignments->count() > 0)

    <!-- Filter Section -->
    {{-- <div class="filter-section">
        <div class="row align-items-center">
            <div class="col-md-4 mb-2 mb-md-0">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Cari program studi atau universitas...">
                </div>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <select class="form-select" id="filterStatus">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu Pelaporan</option>
                    <option value="in_progress">Sedang Diproses</option>
                    <option value="completed">Selesai</option>
                </select>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <select class="form-select" id="sortBy">
                    <option value="newest">Terbaru</option>
                    <option value="oldest">Terlama</option>
                    <option value="name">Nama A-Z</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100" id="resetFilter">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </button>
            </div>
        </div>
    </div> --}}

    <!-- Table -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-check"></i> Daftar Pelaporan AL
                </h5>
                <small class="text-muted">Total: {{ $assignments->count() }}</small>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="35%">Permohonan Akreditasi</th>
                            <th width="20%">Program Studi</th>
                            <th width="20%">Status Pelaporan AL</th>
                            <th width="15%">Tanggal Pelaporan AL</th>
                            <th width="5%" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="assignmentsTbody">
                        @foreach($assignments as $index => $assignment)
                        @php
                        $asesmen = $assignment->asesmen;
                        $pengajuan = isset($asesmen->pengajuan) ? $asesmen->pengajuan : null;

                        $judul = $pengajuan ? $pengajuan->judul_short : $asesmen->name;
                        $nomor = $pengajuan ? $pengajuan->nomor_pengajuan : $asesmen->code;

                        $prodi = '-';
                        if (isset($asesmen->studyProgram)) {
                        $prodi = $asesmen->studyProgram->name;
                        }

                        $univ = '-';
                        if (isset($asesmen->studyProgram) && isset($asesmen->studyProgram->university)) {
                        $univ = $asesmen->studyProgram->university->name;
                        }

                        $canReport = false;
                        if ($pengajuan) {
                        $canReport = $pengajuan->canBeReported('al');
                        }

                        $isReported = false;
                        $reportedAt = null;
                        if ($pengajuan && !empty($pengajuan->tanggal_pelaporan_al)) {
                        $isReported = true;
                        $reportedAt = $pengajuan->tanggal_pelaporan_al;
                        }

                        $statusClass = 'in_progress';
                        if ($isReported) {
                        $statusClass = 'completed';
                        } elseif ($canReport) {
                        $statusClass = 'pending';
                        }

                        $ts = $assignment->created_at ? $assignment->created_at->timestamp : 0;
                        @endphp

                        <tr class="assignment-row" data-status="{{ $statusClass }}" data-name="{{ strtolower($prodi) }}" data-university="{{ strtolower($univ) }}" data-date="{{ $ts }}">

                            <td>{{ $index + 1 }}</td>

                            <td>
                                <p class="mb-0">{{ $judul }}</p>
                                <small class="text-muted">{{ $nomor }}</small>
                                <br>
                                <small class="text-muted">
                                    Dibuat pada:
                                    {{ $assignment->created_at ? \App\Libraries\Date::tglIndo($assignment->created_at) : '-' }}
                                </small>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark">{{ $asesmen->studyProgram->name ?? '-' }}</span>
                                <small class="text-muted small d-block">{{ $asesmen->studyProgram->university->name ?? '-' }}</small>
                            </td>

                            <td>
                                @if($isReported)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Selesai
                                </span>
                                @elseif($canReport)
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-hourglass-split"></i> Menunggu Pelaporan
                                </span>
                                @else
                                <span class="badge bg-info text-dark">
                                    <i class="bi bi-arrow-repeat"></i> Sedang Diproses
                                </span>
                                @endif
                            </td>

                            <td>
                                @if($reportedAt)
                                <small>{{ \App\Libraries\Date::tglIndo($reportedAt) }}</small>
                                <br>
                                <small class="text-muted">{{ $reportedAt->diffForHumans() }}</small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    {{-- @if($canReport && !$isReported) --}}
                                    <button type="button" class="btn btn-success js-open-pelaporan" title="Upload Pelaporan" data-type="al" data-assignment-id="{{ $assignment->id }}" data-nomor="{{ $nomor }}">
                                        <i class="bi bi-upload"></i>
                                    </button>
                                    {{-- @else
                                    <a href="#" class="btn btn-outline-success" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endif --}}

                                    {{-- Tombol Download --}}
                                    @php
                                    $hasDocument = $assignment->asesmen->documents()
                                    ->where('type', 'laporan_al')
                                    ->where('is_active', true)
                                    ->exists();
                                    @endphp

                                    @if($hasDocument)
                                    <a href="{{ route('pelaporan.al.download', $assignment->id) }}" class="btn btn-success" title="Download Laporan">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mt-4 row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
        <div class="col">
            <x-stat-card title="Total Penugasan" :value="$stats['total']" icon="folder" mode="white" description="" color="success" />
        </div>
        <div class="col">
            <x-stat-card title="Menunggu Pelaporan" :value="$stats['pending']" icon="hourglass-split" mode="white" description="" color="warning" />
        </div>
        <div class="col">
            <x-stat-card title="Sedang Diproses" :value="$stats['in_progress']" icon="arrow-repeat" mode="white" description="" color="info" />
        </div>
        <div class="col">
            <x-stat-card title="Selesai" :value="$stats['completed']" icon="check-circle" mode="white" description="" color="success" />
        </div>
    </div>

    <!-- No Results -->
    <div class="alert alert-info text-center d-none mt-3" id="noResults">
        <i class="bi bi-search"></i>
        Tidak ada hasil yang sesuai dengan filter
    </div>

    @else
    <!-- Empty State -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-building-x" style="font-size: 64px; opacity: 0.3; color: #198754;"></i>
            <h5 class="mt-3 mb-2">Tidak Ada Pelaporan AL</h5>
            <p class="text-muted mb-4">
                Anda belum memiliki penugasan pelaporan AL saat ini.<br>
                Pelaporan akan muncul setelah proses AL selesai.
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('pelaporan.index') }}" class="btn btn-outline-success">
                    <i class="bi bi-arrow-left"></i> Dashboard Pelaporan
                </a>
                <a href="{{ route('penawaran') }}" class="btn btn-success">
                    <i class="bi bi-envelope"></i> Lihat Penawaran
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/pelaporan.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('searchInput');
        var filterStatus = document.getElementById('filterStatus');
        var sortBy = document.getElementById('sortBy');
        var resetBtn = document.getElementById('resetFilter');

        var tbody = document.getElementById('assignmentsTbody');
        var noResults = document.getElementById('noResults');

        if (!tbody) return;

        var rows = Array.prototype.slice.call(tbody.querySelectorAll('.assignment-row'));

        function applyFilters() {
            var searchTerm = searchInput.value.toLowerCase();
            var statusFilter = filterStatus.value;
            var sortValue = sortBy.value;

            var visible = rows.filter(function(row) {
                var name = row.dataset.name || '';
                var university = row.dataset.university || '';
                var status = row.dataset.status || '';

                var matchSearch = !searchTerm ||
                    name.indexOf(searchTerm) !== -1 ||
                    university.indexOf(searchTerm) !== -1;

                var matchStatus = !statusFilter || status === statusFilter;

                return matchSearch && matchStatus;
            });

            if (sortValue === 'newest') {
                visible.sort(function(a, b) {
                    return b.dataset.date - a.dataset.date;
                });
            } else if (sortValue === 'oldest') {
                visible.sort(function(a, b) {
                    return a.dataset.date - b.dataset.date;
                });
            } else if (sortValue === 'name') {
                visible.sort(function(a, b) {
                    return a.dataset.name.localeCompare(b.dataset.name);
                });
            }

            rows.forEach(function(row) {
                row.classList.add('d-none');
            });

            if (visible.length > 0) {
                visible.forEach(function(row) {
                    row.classList.remove('d-none');
                    tbody.appendChild(row);
                });
                noResults.classList.add('d-none');
            } else {
                noResults.classList.remove('d-none');
            }
        }

        searchInput.addEventListener('input', applyFilters);
        filterStatus.addEventListener('change', applyFilters);
        sortBy.addEventListener('change', applyFilters);

        resetBtn.addEventListener('click', function() {
            searchInput.value = '';
            filterStatus.value = '';
            sortBy.value = 'newest';
            applyFilters();
        });
    });

</script>

<script>
    window.PELAPORAN_CFG = {
        al: {
            title: 'Pelaporan AL'
            , label: 'Laporan Hasil Asesmen Lapangan Program Studi (LHA)'
            , upload: @json(route('pelaporan.al.upload', ['assignment' => '__ID__']))
            , finalize: @json(route('pelaporan.al.finalize', ['assignment' => '__ID__']))
            , fileLabel: 'Laporan Hasil Asesmen Lapangan Program Studi (LHA)'
            , finalizeLabel: 'Pelaporan AL Telah Selesai'
            , additionalDescription: `Dokumen yang sudah digabungkan, yang diperlukan isinya adalah:
            • Penunjukan tugas Asesor untuk melaksanakan Penilaian LED
            • Proses penilaian LED oleh Asesor
            • Validasi Penilaian Kecukupan Asesor oleh Validator
            • Penyampaian Informasi kepada DE (lokasi AL, perjalanan asesor, berita acara AL)
            • Rekomendasi Penetapan Hasil Akreditasi`
        }
    };

</script>
@endpush
