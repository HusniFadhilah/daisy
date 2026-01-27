@extends('layouts.template.app')

@section('title', 'Pengingat Masa Akreditasi')

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

    .status-aktif {
        background: #d4edda;
        color: #155724;
    }

    .status-kedaluwarsa {
        background: #f8d7da;
        color: #721c24;
    }

    .status-belum {
        background: #e2e3e5;
        color: #383d41;
    }

    .status-urgent {
        background: #fff3cd;
        color: #856404;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    .progress-custom {
        height: 8px;
        border-radius: 10px;
        background: #e9ecef;
    }

    .progress-bar-custom {
        border-radius: 10px;
        transition: width 0.6s ease;
    }

    .peringkat-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 14px;
    }

    .peringkat-unggul {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .peringkat-baik-sekali {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .peringkat-baik {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }

    .peringkat-c {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: white;
    }

    .timeline-card {
        border-left: 4px solid #ffc107;
        transition: all 0.3s ease;
    }

    .timeline-card:hover {
        border-left-color: #ff6b6b;
        box-shadow: 0 4px 12px rgba(255, 107, 107, 0.2);
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

    .timeline-container {
        display: flex;
        overflow-x: auto;
        gap: 15px;
        padding: 20px 0;
        scroll-snap-type: x mandatory;
    }

    .timeline-card {
        min-width: 280px;
        scroll-snap-align: start;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .timeline-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .timeline-urgent {
        border: 2px solid #ffc107;
        background: #fff3cd;
    }

    .timeline-normal {
        border: 2px solid #e9ecef;
        background: white;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }

    .calendar-month {
        border-radius: 12px;
        padding: 15px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid #e9ecef;
    }

    .calendar-month:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .calendar-month.has-expiring {
        background: linear-gradient(135deg, #fff3cd 0%, #ffe8a1 100%);
        border-color: #ffc107;
    }

    .calendar-month.no-expiring {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border-color: #28a745;
    }

    .calendar-count {
        font-size: 2rem;
        font-weight: 700;
        margin: 10px 0;
    }

    .tab-pane {
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .badge-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
    }

    /* ✅ Loading Animation */
    .spinner-border {
        animation: spinner-border 0.75s linear infinite;
    }

    @keyframes spinner-border {
        to {
            transform: rotate(360deg);
        }
    }

    /* ✅ Smooth transition for timeline cards */
    .timeline-container {
        transition: opacity 0.3s ease;
    }

    .timeline-container.loading {
        opacity: 0.5;
    }

    /* ✅ Toast notification positioning */
    .toast-container {
        z-index: 9999 !important;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Total Program Studi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="">
                            <h2 class="mb-2 fw-bold">{{ $stats['total'] }}</h2>
                            <small class="opacity-75">Total prodi yang terdata pada Daisy</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-book"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Akreditasi Aktif</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="">
                            <h2 class="mb-2 fw-bold">{{ $stats['aktif'] }}</h2>
                            <small class="opacity-75">Prodi yang akreditasinya masih aktif</small>
                            {{-- <small class="opacity-75">
                                {{ $stats['total'] > 0 ? round(($stats['aktif'] / $stats['total']) * 100, 1) : 0 }}%
                            </small> --}}
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #e35821ff 0%, #aa4d0aff 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Pengingat Masa Akreditasi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['pengingat_bulan_target'] }}</h2>
                            <small class="opacity-75">PS yang perlu diingatkan tentang masa akreditasi berakhir dalam 7 bulan dari sekarang</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Kedaluwarsa</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['kedaluwarsa'] }}</h2>
                            <small class="opacity-75">PS yang masa akreditasinya telah kedaluwarsa dari sekarang</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #868f96 0%, #596164 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Belum Terakreditasi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['belum_terakreditasi'] }}</h2>
                            <small class="opacity-75">PS yang perlu diajukan akreditasi</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Urgent Alerts -->
    @if($stats['pengingat_bulan_target'] > 0)
    <div class="row mb-2">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible alert-permanent fade show" style="border-left: 4px solid #ffc107;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Perhatian!</strong>
                Ada <strong>{{ $stats['pengingat_bulan_target'] }}</strong> prodi yang perlu diingatkan tentang masa akreditasi berakhir dalam 7 bulan dari sekarang ({{ $stats['pengingat']['target_month_label'] }}).
                <a href="javascript:void(0)" class="alert-link ms-2" onclick="openReminderModal()">
                    Lihat Detail →
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="d-flex align-items-center mb-4">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active position-relative" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline-view" type="button">
                    <i class="bi bi-calendar-week"></i> Timeline View
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-view" type="button">
                    <i class="bi bi-calendar3"></i> Calendar View
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="table-tab" data-bs-toggle="tab" data-bs-target="#table-view" type="button">
                    <i class="bi bi-table"></i> Table View
                </button>
            </li>
        </ul>

        <!-- tombol kanan -->
        <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal" data-bs-target="#modalKirimPengingat">
            <i class="bi bi-bell"></i> Kirim Pengingat Akreditasi
        </button>
    </div>

    <!-- Tab Contents -->
    <div class="tab-content">
        <!-- ✅ TIMELINE VIEW -->
        <div class="tab-pane fade show active" id="timeline-view">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history"></i> Timeline Kedaluwarsa Akreditasi
                            </h5>
                            <small id="periodeLabelText">{{ $timelineData['periode_label'] }}</small>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" id="periodeSelector">
                                <option value="1bulan" {{ $periode == '1bulan' ? 'selected' : '' }}>Per Bulan</option>
                                <option value="3bulan" {{ $periode == '3bulan' ? 'selected' : '' }}>Per 3 Bulan (Triwulan)</option>
                                <option value="4bulan" {{ $periode == '4bulan' ? 'selected' : '' }}>Per 4 Bulan (Caturwulan)</option>
                                <option value="6bulan" {{ $periode == '6bulan' ? 'selected' : '' }}>Per 6 Bulan (Semester)</option>
                                <option value="12bulan" {{ $periode == '12bulan' ? 'selected' : '' }}>Per Tahun</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="timelineLoading" class="position-absolute top-0 start-0 w-100 h-100 d-none" style="background: rgba(255,255,255,0.9); z-index: 1000;">
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted">Memuat data...</p>
                            </div>
                        </div>
                    </div>
                    <div class="timeline-container" id="timelineContainer">
                        @include('asesmen.pemetaan.components.timeline-cards', ['timeline' => $timelineData['timeline']])
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ CALENDAR VIEW -->
        <div class="tab-pane fade" id="calendar-view">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar3"></i> Kalender Kedaluwarsa (dalam Tahun ini)
                    </h5>
                </div>
                <div class="card-body">
                    <div id="calendarLoading" class="position-absolute top-0 start-0 w-100 h-100 d-none" style="background: rgba(255,255,255,0.9); z-index: 1000;">
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="text-center">
                                <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted">Memuat kalender...</p>
                            </div>
                        </div>
                    </div>
                    <div class="calendar-grid" id="calendarContainer">
                        @include('asesmen.pemetaan.components.calendar-grid', ['calendarData' => $calendarData])
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ TABLE VIEW (Keep previous table view here) -->
        <div class="tab-pane fade" id="table-view">
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
                                    <label class="form-label text-white">Cari Program Studi</label>
                                    <input type="text" name="search" id="searchInput" class="form-control" placeholder="Nama prodi..." value="{{ request('search') }}">
                                </div>

                                <!-- Year Filter (Multiple) -->
                                <div class="mb-3">
                                    <label class="form-label text-white">Tahun Kedaluwarsa</label>
                                    <select name="year[]" id="yearFilter" class="form-select" multiple>
                                        @php
                                        $currentYear = now()->year;
                                        $selectedYears = (array)request('year', []);
                                        @endphp
                                        @for($year = $currentYear; $year <= $currentYear + 10; $year++) <option value="{{ $year }}" {{ in_array($year, $selectedYears) ? 'selected' : '' }}>
                                            {{ $year }}
                                            </option>
                                            @endfor
                                    </select>
                                    <small class="text-white opacity-75 mt-1 d-block">Pilih tahun kedaluwarsa</small>
                                </div>

                                <!-- Month Filter (Multiple) -->
                                <div class="mb-3">
                                    <label class="form-label text-white">Bulan Kedaluwarsa</label>
                                    <select name="month[]" id="monthFilter" class="form-select" multiple>
                                        @php
                                        $months = [
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                                        4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                        10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                        ];
                                        $selectedMonths = (array)request('month', []);
                                        @endphp
                                        @foreach($months as $num => $name)
                                        <option value="{{ $num }}" {{ in_array($num, $selectedMonths) ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <small class="text-white opacity-75 mt-1 d-block">Pilih bulan kedaluwarsa</small>
                                </div>

                                <!-- University (Multiple) -->
                                <div class="mb-3">
                                    <label class="form-label text-white">Universitas</label>
                                    <select name="university_id[]" id="universityFilter" class="form-select" multiple>
                                        @foreach($universities as $univ)
                                        <option value="{{ $univ->id }}" {{ in_array($univ->id, (array)request('university_id', [])) ? 'selected' : '' }}>
                                            {{ $univ->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <small class="text-white opacity-75 mt-1 d-block">Pilih satu atau lebih</small>
                                </div>

                                <!-- Degree Level (Multiple) -->
                                <div class="mb-3">
                                    <label class="form-label text-white">Jenjang</label>
                                    <select name="degree_level_id[]" id="degreeLevelFilter" class="form-select" multiple>
                                        @foreach($degreeLevels as $level)
                                        <option value="{{ $level->id }}" {{ in_array($level->id, (array)request('degree_level_id', [])) ? 'selected' : '' }}>
                                            {{ $level->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <small class="text-white opacity-75 mt-1 d-block">Pilih satu atau lebih</small>
                                </div>

                                <!-- Status (Multiple) -->
                                <div class="mb-3">
                                    <label class="form-label text-white">Status Akreditasi</label>
                                    <select name="status_kedaluwarsa[]" id="statusFilter" class="form-select" multiple>
                                        <option value="Aktif" {{ in_array('Aktif', (array)request('status_kedaluwarsa', [])) ? 'selected' : '' }}>Aktif</option>
                                        <option value="Kedaluwarsa" {{ in_array('Kedaluwarsa', (array)request('status_kedaluwarsa', [])) ? 'selected' : '' }}>Kedaluwarsa</option>
                                        <option value="Belum Terakreditasi" {{ in_array('Belum Terakreditasi', (array)request('status_kedaluwarsa', [])) ? 'selected' : '' }}>Belum Terakreditasi</option>
                                    </select>
                                    <small class="text-white opacity-75 mt-1 d-block">Pilih satu atau lebih</small>
                                </div>

                                <!-- Peringkat (Multiple) -->
                                @php
                                $selectedPeringkat = (array) request()->input('peringkat', []);
                                @endphp

                                <div class="mb-3">
                                    <label class="form-label text-white">Peringkat</label>
                                    <select name="peringkat[]" id="peringkatFilter" class="form-select" multiple>
                                        <option value="Unggul" {{ in_array('Unggul', $selectedPeringkat) ? 'selected' : '' }}>
                                            Unggul
                                        </option>
                                        <option value="Baik Sekali" {{ in_array('Baik Sekali', $selectedPeringkat) ? 'selected' : '' }}>
                                            Baik Sekali
                                        </option>
                                        <option value="Baik" {{ in_array('Baik', $selectedPeringkat) ? 'selected' : '' }}>
                                            Baik
                                        </option>
                                        <option value="C" {{ in_array('C', $selectedPeringkat) ? 'selected' : '' }}>
                                            C
                                        </option>
                                    </select>
                                    <small class="text-white text-block opacity-75 mt-1 d-block">Pilih satu atau lebih</small>
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

                                <!-- Active Filters Badge -->
                                <div id="activeFiltersCount" class="mt-3 text-center d-none">
                                    <span class="badge bg-light text-dark">
                                        <i class="bi bi-funnel-fill"></i>
                                        <span id="filterCount">0</span> Filter Aktif
                                    </span>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Peringkat Distribution -->
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-bar-chart"></i> Distribusi Peringkat
                            </h6>
                        </div>
                        <div class="card-body">
                            @php
                            $peringkatData = [
                            'Unggul' => $stats['by_peringkat']['Unggul'] ?? 0,
                            'Baik Sekali' => $stats['by_peringkat']['Baik Sekali'] ?? 0,
                            'Baik' => $stats['by_peringkat']['Baik'] ?? 0,
                            'C' => $stats['by_peringkat']['C'] ?? 0,
                            ];
                            @endphp

                            @foreach($peringkatData as $peringkat => $count)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="fw-bold">{{ $peringkat }}</small>
                                    <small class="text-muted">{{ $count }}</small>
                                </div>
                                <div class="progress progress-custom">
                                    <div class="progress-bar progress-bar-custom bg-{{ $peringkat == 'Unggul' ? 'primary' : ($peringkat == 'Baik Sekali' ? 'success' : ($peringkat == 'Baik' ? 'info' : 'warning')) }}" style="width: {{ $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <div class="position-relative">
                        <!-- ✅ Loading Overlay -->
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

                        <!-- ✅ Table Content -->
                        <div id="tableContainer">
                            @include('asesmen.pemetaan.components.table-content', ['studyPrograms' => $studyPrograms, 'urgentPrograms' => $urgentPrograms])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Kirim Pengingat -->
<div class="modal fade" id="modalKirimPengingat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('de.pengajuan.kirim-pengingat') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-bell"></i> Kirim Pengingat Akreditasi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Program Studi</label>
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; padding: 10px; border-radius: 4px;">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pilih Program Studi</label>

                                <select id="selectProdiPengingat" name="id_program_studi[]" class="form-select" multiple="multiple" style="width: 100%;">
                                </select>

                                <small class="text-muted">Ketik untuk mencari prodi (nama/kode), lalu pilih.</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pesan Pengingat</label>
                        <textarea name="pesan_pengingat" class="form-control" rows="8" required>Yth. Unit Pengelola Program Studi,

Masa akreditasi program studi Anda akan segera berakhir. Kami mengingatkan untuk segera mempersiapkan dan mengajukan permohonan akreditasi.
Terima kasih atas perhatiannya.

Hormat kami,
Dewan Eksekutif (DE) LAMDEPILAR</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Kirim Pengingat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="reminderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-list"></i> Detail Pengingat Masa Akreditasi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Target (bulan dari sekarang)</label>
                        <select class="form-select" id="reminderTargetMonths">
                            <option value="3">3 bulan</option>
                            <option value="6">6 bulan</option>
                            <option value="7" selected>7 bulan</option>
                            <option value="12">12 bulan</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Window periode (bulan)</label>
                        <select class="form-select" id="reminderWindowMonths">
                            <option value="1" selected>1 bulan (hanya bulan target)</option>
                            <option value="3">3 bulan</option>
                            <option value="6">6 bulan</option>
                            <option value="12">12 bulan</option>
                        </select>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary w-100" onclick="loadReminderDetail()">
                            <i class="bi bi-search"></i> Terapkan
                        </button>
                    </div>
                </div>

                <div id="reminderLoading" class="d-none text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="text-muted mt-2">Memuat data...</div>
                </div>

                <div id="reminderDetailContainer"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let prodiSelect2Initialized = false;
    let filterSelect2Initialized = false;

    function initFilterSelect2() {
        if (filterSelect2Initialized) return;

        // Year Filter
        $('#yearFilter').select2({
            theme: 'bootstrap-5'
            , placeholder: 'Pilih Tahun...'
            , allowClear: true
            , width: '100%'
            , closeOnSelect: false
            , language: {
                noResults: function() {
                    return "Tidak ada hasil";
                }
                , searching: function() {
                    return "Mencari...";
                }
            }
        });

        // Month Filter
        $('#monthFilter').select2({
            theme: 'bootstrap-5'
            , placeholder: 'Pilih Bulan...'
            , allowClear: true
            , width: '100%'
            , closeOnSelect: false
            , language: {
                noResults: function() {
                    return "Tidak ada hasil";
                }
                , searching: function() {
                    return "Mencari...";
                }
            }
        });

        // University Filter
        $('#universityFilter').select2({
            theme: 'bootstrap-5'
            , placeholder: 'Pilih Universitas...'
            , allowClear: true
            , width: '100%'
            , closeOnSelect: false
            , language: {
                noResults: function() {
                    return "Tidak ada hasil";
                }
                , searching: function() {
                    return "Mencari...";
                }
            }
        });

        // Degree Level Filter
        $('#degreeLevelFilter').select2({
            theme: 'bootstrap-5'
            , placeholder: 'Pilih Jenjang...'
            , allowClear: true
            , width: '100%'
            , closeOnSelect: false
            , language: {
                noResults: function() {
                    return "Tidak ada hasil";
                }
                , searching: function() {
                    return "Mencari...";
                }
            }
        });

        // Status Filter
        $('#statusFilter').select2({
            theme: 'bootstrap-5'
            , placeholder: 'Pilih Status...'
            , allowClear: true
            , width: '100%'
            , closeOnSelect: false
            , language: {
                noResults: function() {
                    return "Tidak ada hasil";
                }
                , searching: function() {
                    return "Mencari...";
                }
            }
        });

        // Peringkat Filter
        $('#peringkatFilter').select2({
            theme: 'bootstrap-5'
            , placeholder: 'Pilih Peringkat...'
            , allowClear: true
            , width: '100%'
            , closeOnSelect: false
            , language: {
                noResults: function() {
                    return "Tidak ada hasil";
                }
                , searching: function() {
                    return "Mencari...";
                }
            }
        });

        filterSelect2Initialized = true;

        // Update filter count on change
        updateActiveFilterCount();

        $('#yearFilter, #monthFilter, #universityFilter, #degreeLevelFilter, #statusFilter, #peringkatFilter').on('change', function() {
            updateActiveFilterCount();
        });
    }

    // ========================================
    // ✅ UPDATE ACTIVE FILTER COUNT
    // ========================================
    function updateActiveFilterCount() {
        let count = 0;

        if ($('#yearFilter').val() && $('#yearFilter').val().length > 0) count++;
        if ($('#monthFilter').val() && $('#monthFilter').val().length > 0) count++;
        if ($('#universityFilter').val() && $('#universityFilter').val().length > 0) count++;
        if ($('#degreeLevelFilter').val() && $('#degreeLevelFilter').val().length > 0) count++;
        if ($('#statusFilter').val() && $('#statusFilter').val().length > 0) count++;
        if ($('#peringkatFilter').val() && $('#peringkatFilter').val().length > 0) count++;
        if ($('#searchInput').val().trim() !== '') count++;

        const badge = $('#activeFiltersCount');
        const countSpan = $('#filterCount');

        if (count > 0) {
            countSpan.text(count);
            badge.removeClass('d-none');
        } else {
            badge.addClass('d-none');
        }
    }

    // ========================================
    // ✅ GET FILTER PARAMS
    // ========================================
    function getFilterParams() {
        return {
            search: $('#searchInput').val() || ''
            , year: $('#yearFilter').val() || []
            , month: $('#monthFilter').val() || []
            , university_id: $('#universityFilter').val() || []
            , degree_level_id: $('#degreeLevelFilter').val() || []
            , status_kedaluwarsa: $('#statusFilter').val() || []
            , peringkat: $('#peringkatFilter').val() || []
        };
    }

    // ========================================
    // ✅ INITIALIZE SELECT2 FOR PRODI REMINDER
    // ========================================
    function initSelect2Prodi() {
        if (prodiSelect2Initialized) return;

        $('#selectProdiPengingat').select2({
            theme: 'bootstrap-5'
            , dropdownParent: $('#modalKirimPengingat')
            , placeholder: 'Cari & pilih Program Studi...'
            , allowClear: true
            , width: '100%'
            , ajax: {
                url: `{{ route('de.pemetaan.prodi.search.ajax') }}`
                , dataType: 'json'
                , delay: 250
                , data: function(params) {
                    return {
                        q: params.term || ''
                        , page: params.page || 1
                    };
                }
                , processResults: function(data) {
                    return data;
                }
                , cache: true
            }
        });

        prodiSelect2Initialized = true;
    }

    // Saat modal "Kirim Pengingat" dibuka
    document.getElementById('modalKirimPengingat').addEventListener('shown.bs.modal', function() {
        initSelect2Prodi();
    });

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-id-study-program]');
        if (!btn) return;

        const id = btn.getAttribute('data-id-study-program');
        const text = btn.getAttribute('data-text-study-program') || `Prodi #${id}`;

        const modalEl = document.getElementById('modalKirimPengingat');

        const onShown = function() {
            initSelect2Prodi();

            const $select = $('#selectProdiPengingat');
            const exists = $select.find("option[value='" + id + "']").length > 0;

            if (!exists) {
                const newOption = new Option(text, id, true, true);
                $select.append(newOption).trigger('change');
            } else {
                $select.val([...(new Set([...($select.val() || []), id]))]).trigger('change');
            }

            modalEl.removeEventListener('shown.bs.modal', onShown);
        };

        modalEl.addEventListener('shown.bs.modal', onShown);
    });

    document.querySelectorAll('[data-bs-target="#modalKirimPengingat"]').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!this.hasAttribute('data-id-study-program')) {
                const modalEl = document.getElementById('modalKirimPengingat');

                const onShown = function() {
                    initSelect2Prodi();
                    $('#selectProdiPengingat').val(null).trigger('change');
                    modalEl.removeEventListener('shown.bs.modal', onShown);
                };

                modalEl.addEventListener('shown.bs.modal', onShown);
            }
        });
    });

    // ========================================
    // ✅ TIMELINE VIEW AJAX WITH FILTERS
    // ========================================
    document.getElementById('periodeSelector').addEventListener('change', async function() {
        await loadTimelineWithFilters();
    });

    async function loadTimelineWithFilters() {
        const periode = document.getElementById('periodeSelector').value;
        const filters = getFilterParams();
        const loadingOverlay = document.getElementById('timelineLoading');
        const timelineContainer = document.getElementById('timelineContainer');
        const periodeLabelText = document.getElementById('periodeLabelText');

        try {
            loadingOverlay.classList.remove('d-none');

            // Build query string
            const queryParams = new URLSearchParams();
            queryParams.append('periode', periode);

            // Add filters
            Object.keys(filters).forEach(key => {
                if (Array.isArray(filters[key]) && filters[key].length > 0) {
                    filters[key].forEach(value => {
                        queryParams.append(`${key}[]`, value);
                    });
                } else if (!Array.isArray(filters[key]) && filters[key]) {
                    queryParams.append(key, filters[key]);
                }
            });

            const response = await fetch(`{{ route('de.pemetaan.timeline.ajax') }}?${queryParams}`, {
                method: 'GET'
                , headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    , 'Accept': 'application/json'
                , }
            });

            const data = await response.json();

            if (data.success) {
                timelineContainer.innerHTML = data.html;
                periodeLabelText.textContent = data.periode_label;

                const newUrl = new URL(window.location);
                newUrl.searchParams.set('periode', periode);
                window.history.pushState({
                    view: 'timeline'
                    , periode: periode
                }, '', newUrl);
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('error', 'Gagal memuat timeline.');
        } finally {
            loadingOverlay.classList.add('d-none');
        }
    }

    // ========================================
    // ✅ CALENDAR VIEW AJAX WITH FILTERS
    // ========================================
    async function refreshCalendar() {
        const filters = getFilterParams();
        const loadingOverlay = document.getElementById('calendarLoading');
        const calendarContainer = document.getElementById('calendarContainer');

        try {
            loadingOverlay.classList.remove('d-none');

            // Build query string
            const queryParams = new URLSearchParams();

            Object.keys(filters).forEach(key => {
                if (Array.isArray(filters[key]) && filters[key].length > 0) {
                    filters[key].forEach(value => {
                        queryParams.append(`${key}[]`, value);
                    });
                } else if (!Array.isArray(filters[key]) && filters[key]) {
                    queryParams.append(key, filters[key]);
                }
            });

            const response = await fetch(`{{ route('de.pemetaan.calendar.ajax') }}?${queryParams}`, {
                method: 'GET'
                , headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    , 'Accept': 'application/json'
                , }
            });

            const data = await response.json();

            if (data.success) {
                calendarContainer.innerHTML = data.html;

                const newUrl = new URL(window.location);
                newUrl.searchParams.set('view', 'calendar');
                window.history.pushState({
                    view: 'calendar'
                }, '', newUrl);
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('error', 'Gagal memuat kalender.');
        } finally {
            loadingOverlay.classList.add('d-none');
        }
    }

    // ========================================
    // ✅ TABLE VIEW AJAX
    // ========================================
    async function loadTable(params = {}) {
        const loadingOverlay = document.getElementById('tableLoading');
        const tableContainer = document.getElementById('tableContainer');

        try {
            loadingOverlay.classList.remove('d-none');

            // Build query string with arrays properly
            const queryString = new URLSearchParams();

            Object.keys(params).forEach(key => {
                if (Array.isArray(params[key])) {
                    params[key].forEach(value => {
                        queryString.append(`${key}[]`, value);
                    });
                } else if (params[key]) {
                    queryString.append(key, params[key]);
                }
            });

            const response = await fetch(`{{ route('de.pemetaan.table.ajax') }}?${queryString}`, {
                method: 'GET'
                , headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    , 'Accept': 'application/json'
                , }
            });

            const data = await response.json();

            if (data.success) {
                tableContainer.innerHTML = data.html;

                // Update total count
                const totalElement = document.getElementById('totalPrograms');
                if (totalElement) {
                    totalElement.textContent = data.total;
                }

                // Update URL
                const newUrl = new URL(window.location);
                newUrl.searchParams.set('view', 'table');
                Object.keys(params).forEach(key => {
                    if (params[key] && params[key].length > 0) {
                        newUrl.searchParams.set(key, JSON.stringify(params[key]));
                    } else {
                        newUrl.searchParams.delete(key);
                    }
                });
                window.history.pushState({
                    view: 'table'
                    , params: params
                }, '', newUrl);
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('error', 'Gagal memuat tabel.');
        } finally {
            loadingOverlay.classList.add('d-none');
        }
    }

    // ========================================
    // ✅ APPLY FILTERS
    // ========================================
    function applyFilters() {
        const params = getFilterParams();

        // Check which tab is active
        const activeTab = document.querySelector('button[data-bs-toggle="tab"].active');
        const targetId = activeTab ? activeTab.getAttribute('data-bs-target') : null;

        if (targetId === '#timeline-view') {
            loadTimelineWithFilters();
        } else if (targetId === '#calendar-view') {
            refreshCalendar();
        } else {
            loadTable(params);
        }
    }

    // ========================================
    // ✅ RESET FILTERS
    // ========================================
    function resetFilters() {
        document.getElementById('searchInput').value = '';
        $('#yearFilter').val(null).trigger('change');
        $('#monthFilter').val(null).trigger('change');
        $('#universityFilter').val(null).trigger('change');
        $('#degreeLevelFilter').val(null).trigger('change');
        $('#statusFilter').val(null).trigger('change');
        $('#peringkatFilter').val(null).trigger('change');

        updateActiveFilterCount();

        // Check which tab is active
        const activeTab = document.querySelector('button[data-bs-toggle="tab"].active');
        const targetId = activeTab ? activeTab.getAttribute('data-bs-target') : null;

        if (targetId === '#timeline-view') {
            loadTimelineWithFilters();
        } else if (targetId === '#calendar-view') {
            refreshCalendar();
        } else {
            loadTable({});
        }
    }

    // ========================================
    // ✅ AUTO-APPLY FILTERS ON CHANGE
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2
        initFilterSelect2();

        // Search input with debounce
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                updateActiveFilterCount();
                applyFilters();
            }, 500);
        });

        // Select2 filters
        $('#yearFilter, #monthFilter, #universityFilter, #degreeLevelFilter, #statusFilter, #peringkatFilter').on('change', function() {
            applyFilters();
        });
    });

    // ========================================
    // ✅ TAB SWITCHING WITH FILTERS
    // ========================================
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            const targetId = event.target.getAttribute('data-bs-target');

            const newUrl = new URL(window.location);

            if (targetId === '#timeline-view') {
                newUrl.searchParams.set('view', 'timeline');
                loadTimelineWithFilters();
            } else if (targetId === '#calendar-view') {
                newUrl.searchParams.set('view', 'calendar');
                refreshCalendar();
            } else if (targetId === '#table-view') {
                newUrl.searchParams.set('view', 'table');
                const params = getFilterParams();
                loadTable(params);
            }

            window.history.pushState({
                view: targetId
            }, '', newUrl);
        });
    });

    // ========================================
    // ✅ BROWSER BACK/FORWARD
    // ========================================
    window.addEventListener('popstate', function(event) {
        if (event.state) {
            if (event.state.view === 'timeline' && event.state.periode) {
                document.getElementById('timeline-tab').click();
                document.getElementById('periodeSelector').value = event.state.periode;
                loadTimelineWithFilters();
            } else if (event.state.view === 'calendar') {
                document.getElementById('calendar-tab').click();
            } else if (event.state.view === 'table') {
                document.getElementById('table-tab').click();
                if (event.state.params) {
                    loadTable(event.state.params);
                }
            }
        }
    });

    // Export Excel
    function exportExcel() {
        alert('Export feature coming soon!');
    }

    // ========================================
    // ✅ INITIALIZE ON PAGE LOAD
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        // Check URL params and activate correct tab
        const urlParams = new URLSearchParams(window.location.search);
        const view = urlParams.get('view');

        if (view === 'calendar') {
            document.getElementById('calendar-tab').click();
        } else if (view === 'table') {
            document.getElementById('table-tab').click();
        }

        // Set initial history state
        const currentTab = document.querySelector('button[data-bs-toggle="tab"].active');
        if (currentTab) {
            const targetId = currentTab.getAttribute('data-bs-target');
            window.history.replaceState({
                view: targetId
            }, '', window.location.href);
        }
    });

    // ========================================
    // ✅ REMINDER MODAL
    // ========================================
    function openReminderModal() {
        const modal = new bootstrap.Modal(document.getElementById('reminderModal'));
        modal.show();
        loadReminderDetail();
    }

    async function loadReminderDetail(pageUrl = null) {
        const loading = document.getElementById('reminderLoading');
        const container = document.getElementById('reminderDetailContainer');

        const targetMonths = document.getElementById('reminderTargetMonths').value;
        const windowMonths = document.getElementById('reminderWindowMonths').value;

        try {
            loading.classList.remove('d-none');
            container.innerHTML = '';

            const baseUrl = `{{ route('de.pemetaan.reminder.detail.ajax') }}`;
            const url = new URL(baseUrl, window.location.origin);

            url.searchParams.set('target_months', targetMonths);
            url.searchParams.set('window_months', windowMonths);

            const res = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    , 'Accept': 'application/json'
                }
            });

            const data = await res.json();
            if (!data.success) throw new Error('Request gagal');

            container.innerHTML = data.html;

            // Tangkap klik pagination
            container.querySelectorAll('.pagination a').forEach(a => {
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    loadReminderDetail(a.getAttribute('href'));
                });
            });

        } catch (err) {
            console.error(err);
            container.innerHTML = `
                <div class="alert alert-danger">
                    Gagal memuat detail pengingat.
                </div>
            `;
        } finally {
            loading.classList.add('d-none');
        }
    }

    // Auto reload ketika dropdown berubah
    ['reminderTargetMonths', 'reminderWindowMonths'].forEach(id => {
        document.addEventListener('change', (e) => {
            if (e.target && e.target.id === id) {
                loadReminderDetail();
            }
        });
    });

    // Toast notification helper
    function showToast(type, message) {
        // Implement your toast notification here
        console.log(`${type}: ${message}`);
    }

</script>
@endpush
@endsection
