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

    /* ✅ Active Filter Badge Animation */
    .active-filters-badge {
        animation: pulse-badge 2s infinite;
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }

    @keyframes pulse-badge {
        0% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }

    /* ✅ Filter Applied Indicator */
    .filter-applied-indicator {
        position: relative;
    }

    .filter-applied-indicator::after {
        content: '';
        position: absolute;
        top: -5px;
        right: -5px;
        width: 10px;
        height: 10px;
        background: #dc3545;
        border-radius: 50%;
        border: 2px solid white;
    }

    /* ✅ Clear Filter Button */
    .clear-single-filter {
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .clear-single-filter:hover {
        opacity: 1;
        color: #dc3545;
    }

    .btn-reminder-bg {
        background: linear-gradient(135deg, #e35821ff 0%, #aa4d0aff 100%);
        color: #fff !important;
        padding: 6px 14px;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
    }

    .btn-reminder-bg:hover {
        opacity: 0.9;
        color: #fff;
    }

    #reminderModal,
    #modalKirimPengingat {
        z-index: 1065 !important;
    }

    .modal-backdrop {
        z-index: 1060 !important;
    }

    #reminderModal .modal-dialog {
        margin-left: auto;
        margin-right: auto;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Pengingat Masa Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1"><i class="bi bi-clock-history"></i> Pengingat Masa Akreditasi</h4>
            <p class="text-muted mb-0">Kirim pengingat masa akreditasi kepada PS</p>
        </div>
    </div>

    <!-- Urgent Alerts -->
    @if($stats['pengingat_bulan_target'] > 0)
    <div class="row mb-2">
        <div class="col-12">
            <div class="alert alert-warning alert-permanent fade show" style="border-left: 4px solid #ffc107;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Perhatian!</strong>
                Ada <strong>{{ $stats['pengingat_bulan_target'] }}</strong> PS yang perlu diingatkan tentang masa akreditasi berakhir dalam 7 bulan dari sekarang ({{ $stats['pengingat']['target_month_label'] }}).
                <a href="javascript:void(0)" class="mt-2 btn-reminder-bg" onclick="openReminderModal()">
                    Kirim Pengingat →
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-5 mb-4">
        <div class="col mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Total Program Studi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="">
                            <h2 class="mb-2 fw-bold">{{ $stats['total'] }}</h2>
                            <small class="opacity-75">Total PS yang terdata pada Daisy</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-book"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Akreditasi Aktif</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="">
                            <h2 class="mb-2 fw-bold">{{ $stats['aktif'] }}</h2>
                            <small class="opacity-75">Jumlah PS yang akreditasinya masih aktif</small>
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

        <div class="col mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #e35821ff 0%, #aa4d0aff 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Pengingat Masa Akreditasi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['pengingat_bulan_target'] }}</h2>
                            <small class="opacity-75">Jumlah PS yang perlu diingatkan tentang masa akreditasi berakhir dalam 7 bulan dari sekarang</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Kedaluwarsa</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['kedaluwarsa'] }}</h2>
                            <small class="opacity-75">Jumlah PS yang masa akreditasinya telah kedaluwarsa dari sekarang</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #868f96 0%, #596164 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Belum Terakreditasi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['belum_terakreditasi'] }}</h2>
                            <small class="opacity-75">Jumlah PS yang status akreditasinya adalah "Belum Terakreditasi"</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
        {{-- <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal" data-bs-target="#modalKirimPengingat">
            <i class="bi bi-bell"></i> Kirim Pengingat Akreditasi
        </button> --}}
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
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-funnel"></i> Filter & Pencarian
                                </h5>
                                {{-- ✅ Active Filters Badge --}}
                                <span id="activeFiltersBadge" class="badge bg-danger active-filters-badge d-none">
                                    <span id="activeFiltersCount">0</span>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="filterForm" onsubmit="return false;">

                                {{-- ✅ Search --}}
                                <div class="mb-3">
                                    <label class="form-label text-white">
                                        <i class="bi bi-search"></i> Cari Program Studi
                                        <span class="clear-single-filter float-end" onclick="clearSingleFilter('search')" style="display: {{ request('search') ? 'inline' : 'none' }};">
                                            <i class="bi bi-x-circle"></i>
                                        </span>
                                    </label>
                                    <input type="text" name="search" id="searchInput" class="form-control {{ request('search') ? 'filter-applied-indicator' : '' }}" placeholder="Nama prodi..." value="{{ request('search') }}">
                                </div>

                                {{-- ✅ NEW: is_example Filter --}}
                                <div class="mb-3">
                                    <label class="form-label text-white">
                                        <i class="bi bi-flag"></i> Tipe Data
                                        <span class="clear-single-filter float-end" onclick="clearSingleFilter('is_example')" style="display: {{ request('is_example', 'both') != 'both' ? 'inline' : 'none' }};">
                                            <i class="bi bi-x-circle"></i>
                                        </span>
                                    </label>
                                    <select name="is_example" id="isExampleFilter" class="form-select {{ request('is_example', 'both') != 'both' ? 'filter-applied-indicator' : '' }}">
                                        <option value="both" {{ request('is_example', 'both') == 'both' ? 'selected' : '' }}>
                                            Semua Data
                                        </option>
                                        <option value="false" {{ request('is_example') == 'false' ? 'selected' : '' }}>
                                            Hanya Data Real
                                        </option>
                                        <option value="true" {{ request('is_example') == 'true' ? 'selected' : '' }}>
                                            Hanya Data Contoh
                                        </option>
                                    </select>
                                </div>

                                {{-- Year Filter --}}
                                <div class="mb-3">
                                    <label class="form-label text-white">
                                        <i class="bi bi-calendar-range"></i> Tahun Kedaluwarsa
                                        <span class="clear-single-filter float-end" onclick="clearSingleFilter('year')" style="display: {{ request('year') ? 'inline' : 'none' }};">
                                            <i class="bi bi-x-circle"></i>
                                        </span>
                                    </label>
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

                                {{-- Month Filter --}}
                                <div class="mb-3">
                                    <label class="form-label text-white">
                                        <i class="bi bi-calendar-month"></i> Bulan Kedaluwarsa
                                        <span class="clear-single-filter float-end" onclick="clearSingleFilter('month')" style="display: {{ request('month') ? 'inline' : 'none' }};">
                                            <i class="bi bi-x-circle"></i>
                                        </span>
                                    </label>
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
                                </div>

                                {{-- University Filter --}}
                                <div class="mb-3">
                                    <label class="form-label text-white">
                                        <i class="bi bi-building"></i> Universitas
                                        <span class="clear-single-filter float-end" onclick="clearSingleFilter('university_id')" style="display: {{ request('university_id') ? 'inline' : 'none' }};">
                                            <i class="bi bi-x-circle"></i>
                                        </span>
                                    </label>
                                    <select name="university_id[]" id="universityFilter" class="form-select" multiple>
                                        @foreach($universities as $univ)
                                        <option value="{{ $univ->id }}" {{ in_array($univ->id, (array)request('university_id', [])) ? 'selected' : '' }}>
                                            {{ $univ->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Degree Level Filter --}}
                                <div class="mb-3">
                                    <label class="form-label text-white">
                                        <i class="bi bi-mortarboard"></i> Jenjang
                                        <span class="clear-single-filter float-end" onclick="clearSingleFilter('degree_level_id')" style="display: {{ request('degree_level_id') ? 'inline' : 'none' }};">
                                            <i class="bi bi-x-circle"></i>
                                        </span>
                                    </label>
                                    <select name="degree_level_id[]" id="degreeLevelFilter" class="form-select" multiple>
                                        @foreach($degreeLevels as $level)
                                        <option value="{{ $level->id }}" {{ in_array($level->id, (array)request('degree_level_id', [])) ? 'selected' : '' }}>
                                            {{ $level->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Status Filter --}}
                                <div class="mb-3">
                                    <label class="form-label text-white">
                                        <i class="bi bi-check-circle"></i> Status Akreditasi
                                        <span class="clear-single-filter float-end" onclick="clearSingleFilter('status_kedaluwarsa')" style="display: {{ request('status_kedaluwarsa') ? 'inline' : 'none' }};">
                                            <i class="bi bi-x-circle"></i>
                                        </span>
                                    </label>
                                    <select name="status_kedaluwarsa[]" id="statusFilter" class="form-select" multiple>
                                        <option value="Aktif" {{ in_array('Aktif', (array)request('status_kedaluwarsa', [])) ? 'selected' : '' }}>
                                            Aktif
                                        </option>
                                        <option value="Kedaluwarsa" {{ in_array('Kedaluwarsa', (array)request('status_kedaluwarsa', [])) ? 'selected' : '' }}>
                                            Kedaluwarsa
                                        </option>
                                        <option value="Belum Terakreditasi" {{ in_array('Belum Terakreditasi', (array)request('status_kedaluwarsa', [])) ? 'selected' : '' }}>
                                            Belum Terakreditasi
                                        </option>
                                    </select>
                                </div>

                                {{-- Peringkat Filter --}}
                                <div class="mb-3">
                                    <label class="form-label text-white">
                                        <i class="bi bi-star"></i> Status Akreditasi
                                        <span class="clear-single-filter float-end" onclick="clearSingleFilter('peringkat')" style="display: {{ request('peringkat') ? 'inline' : 'none' }};">
                                            <i class="bi bi-x-circle"></i>
                                        </span>
                                    </label>
                                    <select name="peringkat[]" id="peringkatFilter" class="form-select" multiple>
                                        @php $selectedPeringkat = (array) request('peringkat', []); @endphp
                                        @forelse($peringkatList as $peringkat)
                                        <option value="{{ $peringkat }}" {{ in_array($peringkat, $selectedPeringkat) ? 'selected' : '' }}>
                                            {{ $peringkat }}
                                        </option>
                                        @empty
                                        <option disabled>Tidak ada data</option>
                                        @endforelse
                                    </select>
                                </div>

                                {{-- Buttons --}}
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-light" onclick="applyFilters()">
                                        <i class="bi bi-search"></i> Terapkan Filter
                                    </button>
                                    <button type="button" class="btn btn-outline-light" onclick="resetFilters()">
                                        <i class="bi bi-x-circle"></i> Reset Semua Filter
                                    </button>
                                </div>

                                {{-- ✅ Active Filters Summary --}}
                                <div id="activeFiltersSummary" class="mt-3 d-none">
                                    <div class="alert alert-light p-2">
                                        <small class="fw-bold">
                                            <i class="bi bi-funnel-fill"></i> Filter Aktif:
                                        </small>
                                        <div id="filtersList" class="mt-2"></div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════════
     Card 1: Peringkat Akreditasi
     Sumber: GROUP BY peringkat_akreditasi (study_programs)
     ═══════════════════════════════════════════════ --}}
                    <div class="card mt-3">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0 small fw-bold">
                                <i class="bi bi-award text-primary me-1"></i>
                                Distribusi Status Akreditasi
                            </h6>
                        </div>
                        <div class="card-body py-2">
                            @php
                            $peringkatCfg = [
                            'Unggul' => ['color' => '#764ba2', 'icon' => 'bi-trophy-fill'],
                            'Baik Sekali' => ['color' => '#11998e', 'icon' => 'bi-star-fill'],
                            'Baik' => ['color' => '#4facfe', 'icon' => 'bi-hand-thumbs-up-fill'],
                            'C' => ['color' => '#fa709a', 'icon' => 'bi-exclamation-circle-fill'],
                            '(Tidak Ada)' => ['color' => '#adb5bd', 'icon' => 'bi-dash-circle'],
                            ];
                            $totalPeringkat = array_sum($stats['by_peringkat'] ?? []);
                            @endphp

                            @forelse($stats['by_peringkat'] ?? [] as $label => $count)
                            @php
                            $cfg = $peringkatCfg[$label] ?? ['color' => '#adb5bd', 'icon' => 'bi-circle'];
                            $pct = $totalPeringkat > 0 ? round(($count / $totalPeringkat) * 100, 1) : 0;
                            @endphp
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="fw-semibold d-flex align-items-center gap-1">
                                        <i class="bi {{ $cfg['icon'] }}" style="color:{{ $cfg['color'] }};font-size:11px;"></i>
                                        {{ $label }}
                                    </small>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="fw-bold" style="font-size:12px;">{{ $count }}</span>
                                        <small class="text-muted" style="font-size:10px;">({{ $pct }}%)</small>
                                    </div>
                                </div>
                                <div class="progress" style="height:6px;border-radius:10px;background:#e9ecef;">
                                    <div class="progress-bar" style="width:{{ $pct }}%;border-radius:10px;background:{{ $cfg['color'] }};"></div>
                                </div>
                            </div>
                            @empty
                            <small class="text-muted">Tidak ada data.</small>
                            @endforelse

                            <div class="d-flex justify-content-between pt-2 mt-1 border-top">
                                <small class="text-muted">Total</small>
                                <small class="fw-bold">{{ $totalPeringkat }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════════
     Card 2: Status Kedaluwarsa
     Sumber: GROUP BY status_kedaluwarsa (study_programs)
     + referensi label/warna dari tabel status_akreditasi
     ═══════════════════════════════════════════════ --}}
                    <div class="card mt-3">
                        <div class="card-header bg-light py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-0 small fw-bold">
                                        <i class="bi bi-patch-check text-success me-1"></i>
                                        Status Akreditasi
                                    </h6>
                                </div>
                            </div>
                        </div>
                        <div class="card-body py-2">
                            @php
                            $statusCfg = [
                            'Aktif' => ['color' => '#28a745', 'bg' => '#d4edda', 'icon' => 'bi-check-circle-fill'],
                            'Kedaluwarsa' => ['color' => '#dc3545', 'bg' => '#f8d7da', 'icon' => 'bi-x-circle-fill'],
                            'Belum Terakreditasi' => ['color' => '#6c757d', 'bg' => '#e2e3e5', 'icon' => 'bi-hourglass-split'],
                            '(Tidak Ada)' => ['color' => '#adb5bd', 'bg' => '#f8f9fa', 'icon' => 'bi-dash-circle'],
                            ];
                            $totalStatus = array_sum($stats['by_status_prodi'] ?? []);
                            @endphp

                            @forelse($stats['by_status_prodi'] ?? [] as $label => $count)
                            @php
                            $cfg = $statusCfg[$label] ?? ['color' => '#adb5bd', 'bg' => '#f8f9fa', 'icon' => 'bi-circle'];
                            $pct = $totalStatus > 0 ? round(($count / $totalStatus) * 100, 1) : 0;
                            @endphp
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="fw-semibold d-flex align-items-center gap-1">
                                        <i class="bi {{ $cfg['icon'] }}" style="color:{{ $cfg['color'] }};font-size:11px;"></i>
                                        {{ $label }}
                                    </small>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="fw-bold" style="font-size:12px;">{{ $count }}</span>
                                        <small class="text-muted" style="font-size:10px;">({{ $pct }}%)</small>
                                    </div>
                                </div>
                                <div class="progress" style="height:6px;border-radius:10px;background:#e9ecef;">
                                    <div class="progress-bar" style="width:{{ $pct }}%;border-radius:10px;background:{{ $cfg['color'] }};"></div>
                                </div>
                            </div>
                            @empty
                            <small class="text-muted">Tidak ada data.</small>
                            @endforelse

                            <div class="d-flex justify-content-between pt-2 mt-1 border-top">
                                <small class="text-muted">Total</small>
                                <small class="fw-bold">{{ $totalStatus }}</small>
                            </div>
                        </div>

                        {{-- ── Referensi tabel status_akreditasi dari DB ─────────── --}}
                        @if(($stats['status_akreditasi_master'] ?? collect())->isNotEmpty())
                        <div class="card-footer bg-white pt-2 pb-2">
                            <div class="text-muted mb-2" style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">
                                <i class="bi bi-info-circle me-1"></i>Referensi Status (LAMDEPILAR)
                            </div>
                            @foreach($stats['status_akreditasi_master'] as $master)
                            <div class="d-flex align-items-start gap-2 mb-2">
                                <span class="rounded-2 flex-shrink-0 mt-1" style="width:10px;height:10px;background:{{ $master->warna ?: '#adb5bd' }};"></span>
                                <div style="font-size:10px;line-height:1.3;">
                                    <span class="fw-semibold">{{ $master->status }}</span>
                                    <span class="text-muted ms-1">· siklus {{ $master->siklus_tahun }} thn</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-9 mb-4">

                    <!-- Main Content -->
                    {{-- ✅ Urgent Programs Section --}}
                    <div id="urgentSection">
                        @include('asesmen.pemetaan.components.urgent-cards', ['urgentPrograms' => $urgentPrograms])
                    </div>

                    {{-- ✅ Programs DataTable --}}
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-table"></i> Daftar Program Studi
                                <span id="totalProgramsBadge" class="badge bg-primary ms-2">{{ $studyPrograms->total() }}</span>
                            </h5>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-success" onclick="exportExcel()">
                                    <i class="bi bi-file-excel"></i> Export Excel
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="refreshDataTable()">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="programsDataTable" class="table table-hover align-middle mb-0" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Program Studi</th>
                                            <th style="width: 80px;">Jenjang</th>
                                            <th style="width: 120px;">Status Akreditasi</th>
                                            <th style="width: 150px;">Status Kedaluwarsa</th>
                                            <th style="width: 150px;">Kedaluwarsa</th>
                                            <th style="width: 100px;">Sisa Waktu</th>
                                            <th style="width: 150px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- DataTables will populate this --}}
                                    </tbody>
                                </table>
                            </div>
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
            <form action="{{ route('de.pemetaan.kirim-pengingat') }}" method="POST">
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
                                <label class="form-label fw-bold">Cari Nama Program Studi</label>

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
Sekretariat LAMDEPILAR</textarea>
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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content" style="max-height: 95vh;">
            <div class="modal-header bg-primary text-white flex-shrink-0">
                <h5 class="modal-title">
                    <i class="bi bi-bell-fill"></i> Detail Pengingat Masa Akreditasi
                    <span id="reminderTotalBadge" class="badge bg-light text-primary ms-2">0 PS</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- ✅ FILTER PANEL (permanen, tidak di-replace AJAX) --}}
            <div class="flex-shrink-0 border-bottom bg-light px-3 pt-3 pb-2">

                {{-- Baris 1: Target & Window --}}
                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1">Target (bulan dari sekarang)</label>
                        <select class="form-select form-select-sm" id="reminderTargetMonths" data-no-select2>
                            <option value="">- Pilih -</option>
                            <option value="3">3 bulan</option>
                            <option value="6">6 bulan</option>
                            <option value="7" selected>7 bulan</option>
                            <option value="12">12 bulan</option>
                            <option value="24">24 bulan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1">Window periode (bulan)</label>
                        <select class="form-select form-select-sm" id="reminderWindowMonths" data-no-select2>
                            <option value="">- Pilih -</option>
                            <option value="1" selected>1 bulan (hanya bulan target)</option>
                            <option value="3">3 bulan</option>
                            <option value="6">6 bulan</option>
                            <option value="12">12 bulan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1">Dari Tanggal</label>
                        <input type="date" class="form-control form-control-sm" id="reminderDateStart" placeholder="Dari tanggal">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1">Sampai Tanggal</label>
                        <input type="date" class="form-control form-control-sm" id="reminderDateEnd" placeholder="Sampai tanggal">
                    </div>
                </div>

                {{-- Baris 2: Filter konten --}}
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1"><i class="bi bi-search"></i> Nama Prodi</label>
                        <input type="text" class="form-control form-control-sm" id="reminderSearchInput" placeholder="Ketik nama prodi...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1"><i class="bi bi-building"></i> Universitas</label>
                        <select class="form-select form-select-sm" id="reminderUniversitasFilter" multiple>
                            @foreach($universities as $univ)
                            <option value="{{ $univ->id }}">{{ $univ->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1"><i class="bi bi-flag"></i> Kategori Data</label>
                        <select class="form-select form-select-sm" id="reminderIsExampleFilter">
                            <option value="both" selected>Semua Data</option>
                            <option value="false">Hanya Data Real</option>
                            <option value="true">Hanya Data Contoh</option>
                        </select>
                    </div>
                </div>

                {{-- Baris 3: Filter tambahan --}}
                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1"><i class="bi bi-calendar-month"></i> Bulan Kedaluwarsa</label>
                        <select class="form-select form-select-sm" id="reminderBulanFilter" multiple>
                            @php
                            $namabulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
                            5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
                            9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
                            @endphp
                            @foreach($namabulan as $num => $nama)
                            <option value="{{ $num }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1"><i class="bi bi-calendar"></i> Tahun Kedaluwarsa</label>
                        <select class="form-select form-select-sm" id="reminderTahunFilter" multiple>
                            @php $currentYear = now()->year; @endphp
                            @for($y = $currentYear; $y <= $currentYear + 10; $y++) <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1"><i class="bi bi-star"></i> Status Akreditasi</label>
                        <select class="form-select form-select-sm" id="reminderPeringkatFilter" multiple>
                            @forelse($peringkatList as $peringkat)
                            <option value="{{ $peringkat }}">{{ $peringkat }}</option>
                            @empty
                            <option disabled>Tidak ada data</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1"><i class="bi bi-check-circle"></i> Status</label>
                        <select class="form-select form-select-sm" id="reminderStatusFilter" multiple>
                            <option value="Aktif">Aktif</option>
                            <option value="Kedaluwarsa">Kedaluwarsa</option>
                            <option value="Belum Terakreditasi">Belum Terakreditasi</option>
                        </select>
                    </div>
                </div>

                {{-- Tombol aksi filter --}}
                <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-primary btn-sm" onclick="applyReminderFilters()">
                        <i class="bi bi-search"></i> Terapkan Filter
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="resetReminderFilters()">
                        <i class="bi bi-x-circle"></i> Reset
                    </button>
                    <span id="reminderActiveFilterBadge" class="badge bg-danger d-none ms-1">
                        <i class="bi bi-funnel-fill"></i> <span id="reminderActiveFilterCount">0</span> filter aktif
                    </span>
                </div>
            </div>

            {{-- Body: konten AJAX --}}
            <div class="modal-body" style="overflow-y: auto; flex: 1 1 auto;">
                <div id="reminderLoading" class="d-none text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;"></div>
                    <div class="text-muted mt-3">Memuat data...</div>
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
    let currentFilters = {};
    let dataTable = null;
    let dataTableInitializing = false;
    let reminderSelect2Initialized = false;

    document.addEventListener('DOMContentLoaded', function() {
        ['modalKirimPengingat', 'reminderModal'].forEach(id => {
            const modal = document.getElementById(id);
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        });

        // Initialize from URL params
        initializeFiltersFromURL();

        // Initialize Select2
        initFilterSelect2();

        // Setup event listeners
        setupFilterListeners();

        // Initial filter count update
        updateActiveFilterDisplay();

        // Check URL and activate correct tab
        const urlParams = new URLSearchParams(window.location.search);
        const view = urlParams.get('view');

        if (view === 'calendar') {
            document.getElementById('calendar-tab').click();
        } else if (view === 'table') {
            document.getElementById('table-tab').click();
        } else {
            // ✅ Only init DataTable if table view is the default active tab
            setTimeout(() => {
                const tableView = document.querySelector('#table-view');
                if (tableView && tableView.classList.contains('show', 'active')) {
                    initDataTable();
                }
            }, 200);
        }
    });

    // ========================================
    // ✅ INITIALIZE DATATABLE
    // ========================================
    function initDataTable() {
        // ✅ Prevent concurrent initialization
        if (dataTableInitializing) {
            return;
        }

        dataTableInitializing = true;

        // Destroy existing instance
        if (dataTable) {
            try {
                dataTable.destroy();
                dataTable = null;
            } catch (e) {
                console.warn('DataTable destroy error:', e);
            }
        }

        try {
            dataTable = $('#programsDataTable').DataTable({
                processing: true
                , serverSide: true
                , ajax: {
                    url: '{{ route("de.pemetaan.datatable.ajax") }}'
                    , type: 'GET'
                    , data: function(d) {
                        const filters = getFilterParams();
                        // Add custom filters
                        d.is_example = filters.is_example;

                        // Array filters
                        if (Array.isArray(filters.year) && filters.year.length > 0) {
                            filters.year.forEach(year => {
                                d['year[]'] = d['year[]'] || [];
                                d['year[]'].push(year);
                            });
                        }

                        if (Array.isArray(filters.month) && filters.month.length > 0) {
                            filters.month.forEach(month => {
                                d['month[]'] = d['month[]'] || [];
                                d['month[]'].push(month);
                            });
                        }

                        if (Array.isArray(filters.university_id) && filters.university_id.length > 0) {
                            filters.university_id.forEach(id => {
                                d['university_id[]'] = d['university_id[]'] || [];
                                d['university_id[]'].push(id);
                            });
                        }

                        if (Array.isArray(filters.degree_level_id) && filters.degree_level_id.length > 0) {
                            filters.degree_level_id.forEach(id => {
                                d['degree_level_id[]'] = d['degree_level_id[]'] || [];
                                d['degree_level_id[]'].push(id);
                            });
                        }

                        if (Array.isArray(filters.status_kedaluwarsa) && filters.status_kedaluwarsa.length > 0) {
                            filters.status_kedaluwarsa.forEach(status => {
                                d['status_kedaluwarsa[]'] = d['status_kedaluwarsa[]'] || [];
                                d['status_kedaluwarsa[]'].push(status);
                            });
                        }

                        if (Array.isArray(filters.peringkat) && filters.peringkat.length > 0) {
                            filters.peringkat.forEach(peringkat => {
                                d['peringkat[]'] = d['peringkat[]'] || [];
                                d['peringkat[]'].push(peringkat);
                            });
                        }

                        // Search from sidebar
                        if (filters.search && filters.search.trim() !== '') {
                            d.search = d.search || {};
                            d.search.value = filters.search;
                        }

                        return d;
                    }
                    , error: function(xhr, error, code) {
                        console.error('DataTables AJAX Error:', {
                            xhr
                            , error
                            , code
                        });
                        showToast('error', 'Gagal memuat data tabel');
                    }
                }
                , columns: [{
                        data: 'number'
                        , orderable: false
                        , searchable: false
                        , className: 'text-center'
                    }
                    , {
                        data: 'program_studi'
                        , render: function(data, type, row) {
                            return `
                        <div class="fw-bold">${data.name}</div>
                        <small class="text-muted">${data.university}</small>
                    `;
                        }
                    }
                    , {
                        data: 'jenjang'
                        , render: function(data) {
                            return `<span class="badge bg-secondary">${data}</span>`;
                        }
                    }
                    , {
                        data: 'peringkat'
                        , render: function(data) {
                            if (data.value === '-') {
                                return '<span class="text-muted">-</span>';
                            }
                            return `<span class="${data.class}">${data.value}</span>`;
                        }
                    }
                    , {
                        data: 'status'
                        , render: function(data) {
                            return `<span class="${data.class}">${data.value}</span>`;
                        }
                    }
                    , {
                        data: 'tanggal_kedaluwarsa'
                        , render: function(data, type, row) {
                            if (data === '-') {
                                return '<small class="text-muted">-</small>';
                            }
                            return `<small>${data}</small>`;
                        }
                    }
                    , {
                        data: 'sisa_waktu'
                        , orderable: false
                        , render: function(data) {
                            if (data.days === null) {
                                return '<small class="text-muted">-</small>';
                            } else if (data.days < 0) {
                                return '<small class="text-danger fw-bold">Expired</small>';
                            }

                            let progressColor = 'success';
                            if (data.progress <= 20) progressColor = 'danger';
                            else if (data.progress <= 50) progressColor = 'warning';

                            return `
                        <div class="progress progress-custom">
                            <div class="progress-bar progress-bar-custom bg-${progressColor}"
                                 style="width: ${data.progress}%"></div>
                        </div>
                        <small class="text-muted">${data.label}</small>
                    `;
                        }
                    }
                    , {
                        data: null
                        , orderable: false
                        , searchable: false
                        , render: function(data, type, row) {
                            let buttons = `
                        <div class="btn-group btn-group-sm">
                            <a href="/de/pengingat-masa-akreditasi/${row.program_studi.id}"
                               class="btn btn-outline-primary action-btn">
                                <i class="bi bi-eye"></i>
                            </a>
                    `;

                            buttons += '</div>';
                            return buttons;
                        }
                    }
                ]
                , order: [
                    [5, 'asc']
                ]
                , pageLength: 20
                , lengthMenu: [
                    [10, 20, 50, 100, -1]
                    , [10, 20, 50, 100, "Semua"]
                ]
                , language: {
                    processing: `
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Memuat data...</div>
                `
                    , search: "Cari:"
                    , lengthMenu: "Tampilkan _MENU_ data per halaman"
                    , info: "Menampilkan _START_ - _END_ dari _TOTAL_ data"
                    , infoEmpty: "Tidak ada data"
                    , infoFiltered: "(difilter dari _MAX_ total data)"
                    , paginate: {
                        first: "Pertama"
                        , last: "Terakhir"
                        , next: "Selanjutnya"
                        , previous: "Sebelumnya"
                    }
                    , zeroRecords: "Tidak ada data ditemukan"
                    , emptyTable: "Tidak ada data tersedia"
                }
                , drawCallback: function(settings) {
                    const info = this.api().page.info();
                    $('#totalProgramsBadge').text(info.recordsTotal);
                    updateURLWithDataTableState();
                }
                , initComplete: function() {
                    dataTableInitializing = false;
                }
            });
        } catch (error) {
            console.error('❌ DataTable initialization error:', error);
            showToast('error', 'Gagal menginisialisasi tabel');
            dataTableInitializing = false;
        }
    }

    // ========================================
    // ✅ UPDATE URL WITH DATATABLE STATE
    // ========================================
    function updateURLWithDataTableState() {
        if (!dataTable) return;

        const filters = getFilterParams();
        const info = dataTable.page.info();

        const url = buildURLWithParams(filters, {
            view: 'table'
            , page: info.page + 1
            , length: info.length
        });

        window.history.replaceState({
            view: 'table'
        }, '', url);
    }

    // ========================================
    // ✅ REFRESH DATATABLE
    // ========================================
    function refreshDataTable() {
        if (dataTable) {
            dataTable.ajax.reload(null, false); // false = stay on current page
            showToast('success', 'Data berhasil direfresh');
        }
    }

    // ========================================
    // ✅ LOAD URGENT PROGRAMS
    // ========================================
    async function loadUrgentPrograms() {
        const filters = getFilterParams();

        try {
            const queryParams = new URLSearchParams();

            Object.keys(filters).forEach(key => {
                if (Array.isArray(filters[key]) && filters[key].length > 0) {
                    filters[key].forEach(value => {
                        queryParams.append(`${key}[]`, value);
                    });
                } else if (!Array.isArray(filters[key]) && filters[key] && filters[key] !== 'both') {
                    queryParams.append(key, filters[key]);
                }
            });

            const response = await fetch(`{{ route('de.pemetaan.urgent.ajax') }}?${queryParams}`, {
                method: 'GET'
                , headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    , 'Accept': 'application/json'
                , }
            });

            const data = await response.json();

            if (data.success) {
                const urgentSection = document.getElementById('urgentSection');
                const urgentContainer = document.getElementById('urgentCardsContainer');

                if (data.count > 0) {
                    if (urgentContainer) {
                        urgentContainer.innerHTML = data.html;
                    }
                    if (urgentSection) {
                        urgentSection.classList.remove('d-none');
                    }
                } else {
                    if (urgentSection) {
                        urgentSection.classList.add('d-none');
                    }
                }
            }
        } catch (error) {
            console.error('Error loading urgent programs:', error);
        }
    }

    // ========================================
    // ✅ INITIALIZE FILTERS FROM URL
    // ========================================
    function initializeFiltersFromURL() {
        const urlParams = new URLSearchParams(window.location.search);

        // Set search input
        const search = urlParams.get('search');
        if (search) {
            document.getElementById('searchInput').value = search;
        }

        // Set is_example filter
        const isExample = urlParams.get('is_example');
        if (isExample) {
            document.getElementById('isExampleFilter').value = isExample;
        }

        // Set multiselect values (will be applied after Select2 initialization)
        currentFilters = {
            year: urlParams.getAll('year[]')
            , month: urlParams.getAll('month[]')
            , university_id: urlParams.getAll('university_id[]')
            , degree_level_id: urlParams.getAll('degree_level_id[]')
            , status_kedaluwarsa: urlParams.getAll('status_kedaluwarsa[]')
            , peringkat: urlParams.getAll('peringkat[]')
        , };
    }

    function initFilterSelect2() {
        if (filterSelect2Initialized) return;

        const select2Config = {
            theme: 'bootstrap-5'
            , allowClear: true
            , width: '100%'
            , closeOnSelect: false
            , language: {
                noResults: () => "Tidak ada hasil"
                , searching: () => "Mencari..."
            }
        };

        // Initialize all filters
        const filters = [
            'yearFilter'
            , 'monthFilter'
            , 'universityFilter'
            , 'degreeLevelFilter'
            , 'statusFilter'
            , 'peringkatFilter'
        ];

        filters.forEach(filterId => {
            $(`#${filterId}`).select2(select2Config);
        });

        // Set values from URL
        if (currentFilters.year.length) $('#yearFilter').val(currentFilters.year).trigger('change');
        if (currentFilters.month.length) $('#monthFilter').val(currentFilters.month).trigger('change');
        if (currentFilters.university_id.length) $('#universityFilter').val(currentFilters.university_id).trigger('change');
        if (currentFilters.degree_level_id.length) $('#degreeLevelFilter').val(currentFilters.degree_level_id).trigger('change');
        if (currentFilters.status_kedaluwarsa.length) $('#statusFilter').val(currentFilters.status_kedaluwarsa).trigger('change');
        if (currentFilters.peringkat.length) $('#peringkatFilter').val(currentFilters.peringkat).trigger('change');

        filterSelect2Initialized = true;
    }

    // ========================================
    // ✅ SETUP EVENT LISTENERS
    // ========================================
    function setupFilterListeners() {
        // Search with debounce
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');

        // Remove existing listener if any
        searchInput.removeEventListener('input', handleSearchInput);
        searchInput.addEventListener('input', handleSearchInput);

        function handleSearchInput() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                updateActiveFilterDisplay();
                applyFilters(); // Auto-apply on search
            }, 500);
        }

        // is_example filter
        document.getElementById('isExampleFilter').addEventListener('change', function() {
            updateActiveFilterDisplay();
            applyFilters();
        });

        // Select2 filters - auto-apply
        $('#yearFilter, #monthFilter, #universityFilter, #degreeLevelFilter, #statusFilter, #peringkatFilter')
            .off('change') // Remove existing handlers
            .on('change', function() {
                updateActiveFilterDisplay();
                applyFilters(); // Auto-apply
            });
    }

    // ========================================
    // ✅ UPDATE ACTIVE FILTER DISPLAY
    // ========================================
    function updateActiveFilterDisplay() {
        const params = getFilterParams();
        let count = 0;
        let filtersList = [];

        // Count active filters
        if (params.search.trim() !== '') {
            count++;
            filtersList.push(`<span class="badge bg-info me-1 mb-1">
            <i class="bi bi-search"></i> "${params.search}"
            <i class="bi bi-x-circle ms-1 cursor-pointer" onclick="clearSingleFilter('search')"></i>
        </span>`);
        }

        if (params.is_example !== 'both') {
            count++;
            const label = params.is_example === 'false' ? 'Data Real' : 'Data Contoh';
            filtersList.push(`<span class="badge bg-warning text-dark me-1 mb-1">
            <i class="bi bi-flag"></i> ${label}
            <i class="bi bi-x-circle ms-1 cursor-pointer" onclick="clearSingleFilter('is_example')"></i>
        </span>`);
        }

        if (params.year.length > 0) {
            count++;
            filtersList.push(`<span class="badge bg-primary me-1 mb-1">
            <i class="bi bi-calendar"></i> ${params.year.length} Tahun
            <i class="bi bi-x-circle ms-1 cursor-pointer" onclick="clearSingleFilter('year')"></i>
        </span>`);
        }

        if (params.month.length > 0) {
            count++;
            filtersList.push(`<span class="badge bg-primary me-1 mb-1">
            <i class="bi bi-calendar-month"></i> ${params.month.length} Bulan
            <i class="bi bi-x-circle ms-1 cursor-pointer" onclick="clearSingleFilter('month')"></i>
        </span>`);
        }

        if (params.university_id.length > 0) {
            count++;
            filtersList.push(`<span class="badge bg-success me-1 mb-1">
            <i class="bi bi-building"></i> ${params.university_id.length} Universitas
            <i class="bi bi-x-circle ms-1 cursor-pointer" onclick="clearSingleFilter('university_id')"></i>
        </span>`);
        }

        if (params.degree_level_id.length > 0) {
            count++;
            filtersList.push(`<span class="badge bg-info me-1 mb-1">
            <i class="bi bi-mortarboard"></i> ${params.degree_level_id.length} Jenjang
            <i class="bi bi-x-circle ms-1 cursor-pointer" onclick="clearSingleFilter('degree_level_id')"></i>
        </span>`);
        }

        if (params.status_kedaluwarsa.length > 0) {
            count++;
            filtersList.push(`<span class="badge bg-secondary me-1 mb-1">
            <i class="bi bi-check-circle"></i> ${params.status_kedaluwarsa.length} Status
            <i class="bi bi-x-circle ms-1 cursor-pointer" onclick="clearSingleFilter('status_kedaluwarsa')"></i>
        </span>`);
        }

        if (params.peringkat.length > 0) {
            count++;
            filtersList.push(`<span class="badge bg-danger me-1 mb-1">
            <i class="bi bi-star"></i> ${params.peringkat.length} Status Akreditasi
            <i class="bi bi-x-circle ms-1 cursor-pointer" onclick="clearSingleFilter('peringkat')"></i>
        </span>`);
        }

        // Update badge
        const badge = $('#activeFiltersBadge');
        const countSpan = $('#activeFiltersCount');

        if (count > 0) {
            countSpan.text(count);
            badge.removeClass('d-none');
        } else {
            badge.addClass('d-none');
        }

        // Update summary
        const summary = $('#activeFiltersSummary');
        const list = $('#filtersList');

        if (count > 0) {
            list.html(filtersList.join(''));
            summary.removeClass('d-none');
        } else {
            summary.addClass('d-none');
        }

        // Update clear button visibility
        updateClearButtonVisibility();
    }

    // ========================================
    // ✅ UPDATE CLEAR BUTTON VISIBILITY
    // ========================================
    function updateClearButtonVisibility() {
        const params = getFilterParams();

        // Toggle clear buttons
        $('[onclick*="clearSingleFilter(\'search\')"]').toggle(params.search.trim() !== '');
        $('[onclick*="clearSingleFilter(\'is_example\')"]').toggle(params.is_example !== 'both');
        $('[onclick*="clearSingleFilter(\'year\')"]').toggle(params.year.length > 0);
        $('[onclick*="clearSingleFilter(\'month\')"]').toggle(params.month.length > 0);
        $('[onclick*="clearSingleFilter(\'university_id\')"]').toggle(params.university_id.length > 0);
        $('[onclick*="clearSingleFilter(\'degree_level_id\')"]').toggle(params.degree_level_id.length > 0);
        $('[onclick*="clearSingleFilter(\'status_kedaluwarsa\')"]').toggle(params.status_kedaluwarsa.length > 0);
        $('[onclick*="clearSingleFilter(\'peringkat\')"]').toggle(params.peringkat.length > 0);
    }

    // ========================================
    // ✅ CLEAR SINGLE FILTER
    // ========================================
    function clearSingleFilter(filterName) {
        if (filterName === 'search') {
            document.getElementById('searchInput').value = '';
        } else if (filterName === 'is_example') {
            document.getElementById('isExampleFilter').value = 'both';
        } else {
            const filterMap = {
                'year': 'yearFilter'
                , 'month': 'monthFilter'
                , 'university_id': 'universityFilter'
                , 'degree_level_id': 'degreeLevelFilter'
                , 'status_kedaluwarsa': 'statusFilter'
                , 'peringkat': 'peringkatFilter'
            };

            if (filterMap[filterName]) {
                $(`#${filterMap[filterName]}`).val(null).trigger('change');
            }
        }

        updateActiveFilterDisplay();
        applyFilters();
    }

    // ========================================
    // ✅ BUILD URL WITH PARAMS
    // ========================================
    function buildURLWithParams(params, additionalParams = {}) {
        const url = new URL(window.location.href);

        // Clear existing params
        url.search = '';

        // Add all params
        const allParams = {
            ...params
            , ...additionalParams
        };

        Object.keys(allParams).forEach(key => {
            const value = allParams[key];

            if (Array.isArray(value) && value.length > 0) {
                value.forEach(v => url.searchParams.append(`${key}[]`, v));
            } else if (value && value !== 'both' && (!Array.isArray(value) || value.length > 0)) {
                url.searchParams.set(key, value);
            }
        });

        return url;
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
            , is_example: $('#isExampleFilter').val() || 'both'
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
                } else if (!Array.isArray(filters[key]) && filters[key] && filters[key] !== 'both') {
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

                // Update URL
                const url = buildURLWithParams(filters, {
                    periode: periode
                    , view: 'timeline'
                });
                window.history.pushState({
                    view: 'timeline'
                    , periode: periode
                }, '', url);

                showToast('success', 'Timeline berhasil dimuat');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('error', 'Gagal memuat timeline');
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
                } else if (!Array.isArray(filters[key]) && filters[key] && filters[key] !== 'both') {
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

                // Update URL
                const url = buildURLWithParams(filters, {
                    view: 'calendar'
                });
                window.history.pushState({
                    view: 'calendar'
                }, '', url);

                showToast('success', 'Kalender berhasil dimuat');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('error', 'Gagal memuat kalender');
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

            // Build query string
            const queryString = new URLSearchParams();

            Object.keys(params).forEach(key => {
                if (Array.isArray(params[key]) && params[key].length > 0) {
                    params[key].forEach(value => {
                        queryString.append(`${key}[]`, value);
                    });
                } else if (params[key] && params[key] !== 'both') {
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
                const url = buildURLWithParams(params, {
                    view: 'table'
                });
                window.history.pushState({
                    view: 'table'
                    , params: params
                }, '', url);

                showToast('success', `Data berhasil dimuat (${data.total} prodi)`);
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('error', 'Gagal memuat tabel');
        } finally {
            loadingOverlay.classList.add('d-none');
        }
    }

    // ========================================
    // ✅ APPLY FILTERS
    // ========================================
    async function applyFilters() {
        const params = getFilterParams();

        // Determine active view
        const activeTab = document.querySelector('button[data-bs-toggle="tab"].active');
        const targetId = activeTab ? activeTab.getAttribute('data-bs-target') : null;

        if (targetId === '#timeline-view') {
            await loadTimelineWithFilters();
        } else if (targetId === '#calendar-view') {
            await refreshCalendar();
        } else if (targetId === '#table-view') {
            // ✅ Just reload DataTable - filters will be fetched dynamically
            if (dataTable) {
                dataTable.ajax.reload(null, false); // false = stay on current page
            } else {
                initDataTable();
            }

            // Also reload urgent programs
            await loadUrgentPrograms();

            // Update URL
            const url = buildURLWithParams(params, {
                view: 'table'
            });
            window.history.pushState({
                view: 'table'
            }, '', url);
        }
    }

    // ========================================
    // ✅ RESET FILTERS
    // ========================================
    function resetFilters() {
        // Clear all inputs
        document.getElementById('searchInput').value = '';
        document.getElementById('isExampleFilter').value = 'both';

        // Clear Select2
        $('#yearFilter, #monthFilter, #universityFilter, #degreeLevelFilter, #statusFilter, #peringkatFilter')
            .val(null).trigger('change');

        updateActiveFilterDisplay();

        // Apply reset
        applyFilters();
    }

    // ========================================
    // ✅ TAB SWITCHING WITH FILTERS
    // ========================================
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            const targetId = event.target.getAttribute('data-bs-target');

            if (targetId === '#timeline-view') {
                loadTimelineWithFilters();
            } else if (targetId === '#calendar-view') {
                refreshCalendar();
            } else if (targetId === '#table-view') {
                // ✅ Only init if doesn't exist, otherwise just reload
                if (!dataTable && !dataTableInitializing) {
                    initDataTable();
                } else if (dataTable) {
                    dataTable.ajax.reload();
                }
                loadUrgentPrograms();
            }
        });
    });

    // ========================================
    // ✅ BROWSER BACK/FORWARD
    // ========================================
    window.addEventListener('popstate', function(event) {
        if (event.state) {
            // Reinitialize filters from URL
            initializeFiltersFromURL();
            initFilterSelect2();
            updateActiveFilterDisplay();

            // Activate correct tab
            if (event.state.view === 'timeline') {
                document.getElementById('timeline-tab').click();
            } else if (event.state.view === 'calendar') {
                document.getElementById('calendar-tab').click();
            } else if (event.state.view === 'table') {
                document.getElementById('table-tab').click();

                // Reload DataTable
                if (dataTable) {
                    dataTable.ajax.reload();
                } else {
                    initDataTable();
                }
            }
        }
    });

    // Export Excel
    function exportExcel() {
        const filters = getFilterParams();
        const queryParams = new URLSearchParams();

        Object.keys(filters).forEach(key => {
            if (Array.isArray(filters[key]) && filters[key].length > 0) {
                filters[key].forEach(value => {
                    queryParams.append(`${key}[]`, value);
                });
            } else if (!Array.isArray(filters[key]) && filters[key] && filters[key] !== 'both') {
                queryParams.append(key, filters[key]);
            }
        });

        window.location.href = `{{ route('de.pemetaan.export') }}?${queryParams}`;
    }

    // ========================================
    // ✅ REMINDER MODAL
    // ========================================
    let reminderFiltersInitialized = false;

    function openReminderModal() {
        const modalEl = document.getElementById('reminderModal');
        if (!modalEl) {
            console.error('reminderModal element tidak ditemukan');
            return;
        }

        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');

        modalEl.style.display = '';
        modalEl.style.zIndex = '1065';
        const dialog = modalEl.querySelector('.modal-dialog');
        if (dialog) {
            dialog.style.transform = 'none';
            dialog.style.opacity = '1';
            dialog.style.visibility = 'visible';
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        // Initialize filters on first open
        if (!reminderFiltersInitialized) {
            setTimeout(() => {
                initReminderFilters();
            }, 300);
        }

        loadReminderDetail(1);
    }

    function initReminderFilters() {
        // Prevent multiple initialization
        if (reminderFiltersInitialized) {
            return;
        }

        // Wait for modal content to be fully rendered
        setTimeout(() => {
            const peringkatSelect = $('#reminderPeringkatFilter');
            const statusSelect = $('#reminderStatusFilter');
            const modalBody = document.querySelector('#reminderModal .modal-body');

            // Check if elements exist
            if (peringkatSelect.length === 0 || statusSelect.length === 0) {
                return;
            }

            // Destroy existing Select2 instances if any
            if (peringkatSelect.hasClass('select2-hidden-accessible')) {
                peringkatSelect.select2('destroy');
            }
            if (statusSelect.hasClass('select2-hidden-accessible')) {
                statusSelect.select2('destroy');
            }

            // Initialize Select2
            const select2Config = {
                theme: 'bootstrap-5'
                , dropdownParent: $('#reminderModal')
                , placeholder: 'Pilih...'
                , allowClear: true
                , width: '100%'
                , closeOnSelect: false
                , language: {
                    noResults: () => "Tidak ada hasil"
                    , searching: () => "Mencari..."
                }
            };

            peringkatSelect.select2(select2Config);
            statusSelect.select2(select2Config);

            // ✅ Pastikan modal body tetap scrollable setelah Select2 init
            if (modalBody) {
                modalBody.style.overflowY = 'auto';
                modalBody.style.maxHeight = '70vh';
            }

            // Search with debounce
            let searchTimeout;
            $('#reminderSearchInput').off('input').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadReminderDetail(1);
                }, 500);
            });

            // Auto-apply on filter change
            peringkatSelect.off('change').on('change', function() {
                loadReminderDetail(1);
            });

            statusSelect.off('change').on('change', function() {
                loadReminderDetail(1);
            });

            reminderFiltersInitialized = true;
        }, 200);
    }

    // Inisialisasi Select2 di dalam modal (cukup sekali)
    function initReminderSelect2() {
        if (reminderSelect2Initialized) return;

        const cfg = {
            theme: 'bootstrap-5'
            , dropdownParent: $('#reminderModal')
            , allowClear: true
            , width: '100%'
            , closeOnSelect: false
            , placeholder: 'Pilih...'
            , language: {
                noResults: () => 'Tidak ada hasil'
                , searching: () => 'Mencari...'
            }
        };

        $('#reminderUniversitasFilter').select2({
            ...cfg
            , placeholder: 'Cari universitas...'
        });
        $('#reminderBulanFilter').select2(cfg);
        $('#reminderTahunFilter').select2(cfg);
        $('#reminderPeringkatFilter').select2(cfg);
        $('#reminderStatusFilter').select2(cfg);

        // Target & Window — single-select, bisa kosong
        const cfgSingle = {
            theme: 'bootstrap-5'
            , dropdownParent: $('#reminderModal')
            , allowClear: true
            , width: '100%'
            , placeholder: '- Pilih -'
            , language: { noResults: () => 'Tidak ada hasil' }
        };
        $('#reminderTargetMonths').select2(cfgSingle);
        $('#reminderWindowMonths').select2(cfgSingle);

        // Auto-apply saat filter berubah
        $('#reminderUniversitasFilter, #reminderBulanFilter, #reminderTahunFilter, #reminderPeringkatFilter, #reminderStatusFilter')
            .on('change', () => {
                updateReminderActiveFilters();
                loadReminderDetail(1);
            });

        $('#reminderIsExampleFilter').on('change', () => {
            updateReminderActiveFilters();
            loadReminderDetail(1);
        });

        // Search dengan debounce
        let debounce;
        $('#reminderSearchInput').on('input', function() {
            clearTimeout(debounce);
            debounce = setTimeout(() => {
                updateReminderActiveFilters();
                loadReminderDetail(1);
            }, 500);
        });

        // Date range
        $('#reminderDateStart, #reminderDateEnd').on('change', () => {
            updateReminderActiveFilters();
            loadReminderDetail(1);
        });

        // Target & Window
        $('#reminderTargetMonths, #reminderWindowMonths').on('change', () => loadReminderDetail(1));

        reminderSelect2Initialized = true;
    }

    function getReminderFilters() {
        return {
            search: ($('#reminderSearchInput').val() || '').trim()
            , university_id: $('#reminderUniversitasFilter').val() || []
            , is_example: $('#reminderIsExampleFilter').val() || 'both'
            , month: $('#reminderBulanFilter').val() || []
            , year: $('#reminderTahunFilter').val() || []
            , peringkat: $('#reminderPeringkatFilter').val() || []
            , status: $('#reminderStatusFilter').val() || []
            , date_start: $('#reminderDateStart').val() || ''
            , date_end: $('#reminderDateEnd').val() || ''
        , };
    }

    // Hitung & tampilkan badge filter aktif
    function updateReminderActiveFilters() {
        const f = getReminderFilters();
        let count = 0;
        if (f.search) count++;
        if (f.university_id.length) count++;
        if (f.is_example !== 'both') count++;
        if (f.month.length) count++;
        if (f.year.length) count++;
        if (f.peringkat.length) count++;
        if (f.status.length) count++;
        if (f.date_start || f.date_end) count++;

        const badge = document.getElementById('reminderActiveFilterBadge');
        document.getElementById('reminderActiveFilterCount').textContent = count;
        badge.classList.toggle('d-none', count === 0);
    }

    function applyReminderFilters() {
        updateReminderActiveFilters();
        loadReminderDetail(1);
    }

    function resetReminderFilters() {
        $('#reminderSearchInput').val('');
        $('#reminderIsExampleFilter').val('both');
        $('#reminderDateStart, #reminderDateEnd').val('');
        $('#reminderUniversitasFilter, #reminderBulanFilter, #reminderTahunFilter, #reminderPeringkatFilter, #reminderStatusFilter')
            .val(null).trigger('change');
        updateReminderActiveFilters();
        loadReminderDetail(1);
    }

    async function loadReminderDetail(page = 1) {
        const loading = document.getElementById('reminderLoading');
        const container = document.getElementById('reminderDetailContainer');

        const targetMonths = document.getElementById('reminderTargetMonths').value;
        const windowMonths = document.getElementById('reminderWindowMonths').value;
        const filters = getReminderFilters();

        try {
            loading.classList.remove('d-none');
            container.style.opacity = '0.4';

            const url = new URL('{{ route("de.pemetaan.reminder.detail.ajax") }}', window.location.origin);
            url.searchParams.set('target_months', targetMonths);
            url.searchParams.set('window_months', windowMonths);
            url.searchParams.set('page', page);

            // Append semua filter
            if (filters.search) url.searchParams.set('search', filters.search);
            if (filters.is_example !== 'both') url.searchParams.set('is_example', filters.is_example);
            if (filters.date_start) url.searchParams.set('date_start', filters.date_start);
            if (filters.date_end) url.searchParams.set('date_end', filters.date_end);
            filters.university_id.forEach(v => url.searchParams.append('university_id[]', v));
            filters.month.forEach(v => url.searchParams.append('month[]', v));
            filters.year.forEach(v => url.searchParams.append('year[]', v));
            filters.peringkat.forEach(v => url.searchParams.append('peringkat[]', v));
            filters.status.forEach(v => url.searchParams.append('status[]', v));
            if (targetMonths !== '') url.searchParams.set('target_months', targetMonths);
            if (windowMonths !== '') url.searchParams.set('window_months', windowMonths);

            const res = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    , 'Accept': 'application/json'
                }
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();
            if (!data.success) throw new Error(data.message || 'Request gagal');

            container.innerHTML = data.html;

            // Update badge total
            const totalBadge = document.getElementById('reminderTotalBadge');
            if (totalBadge && data.meta && data.meta.total !== undefined) {
                totalBadge.textContent = `${data.meta.total} PS`;
            }

        } catch (err) {
            container.innerHTML = `
            <div class="alert alert-danger m-3">
                <i class="bi bi-exclamation-triangle"></i>
                Gagal memuat data: ${err.message}
            </div>`;
        } finally {
            loading.classList.add('d-none');
            container.style.opacity = '1';
        }
    }

    // Inisialisasi saat modal dibuka
    document.getElementById('reminderModal').addEventListener('shown.bs.modal', function() {
        initReminderSelect2();
        if (!document.getElementById('reminderDetailContainer').innerHTML.trim()) {
            loadReminderDetail(1);
        }
    });

    // Auto reload ketika dropdown berubah
    document.addEventListener('DOMContentLoaded', function() {
        ['reminderTargetMonths', 'reminderWindowMonths'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('change', function() {
                    // Reset filters when changing target/window
                    reminderFiltersInitialized = false;
                    loadReminderDetail(1);
                });
            }
        });

        // Initialize on modal shown
        const reminderModal = document.getElementById('reminderModal');
        if (reminderModal) {
            reminderModal.addEventListener('shown.bs.modal', function() {
                const modalBody = this.querySelector('.modal-body');
                if (modalBody) {
                    modalBody.style.overflowY = 'auto';
                    modalBody.style.maxHeight = '70vh';
                    modalBody.scrollTop = 0;
                }

                // Initialize filters
                setTimeout(() => {
                    initReminderFilters();
                }, 300);
            });

            // ✅ Prevent scroll issues when Select2 dropdown opens
            reminderModal.addEventListener('select2:open', function() {
                const modalBody = this.querySelector('.modal-body');
                if (modalBody) {
                    modalBody.style.overflowY = 'auto';
                }
            });
        }
    });

</script>
@endpush
@endsection
