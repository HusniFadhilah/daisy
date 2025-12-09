@extends('layouts.template.app')

@section('title', 'Penilaian AK - ' . $asesmen->name)

@push('styles')
<style>
    :root {
        --primary: #932136;
        --secondary: #870820;
        --success: #4caf50;
        --warning: #ff9800;
        --danger: #f44336;
        --info: #2196f3;
    }

    /* Keep all existing styles from original file */
    .header-card {
        background: white;
        border: none;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        border-radius: 12px;
    }

    .progress-wrapper {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
    }

    .progress {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        height: 20px;
    }

    .progress-bar {
        background: var(--light) !important;
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    .stat-circle {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 50%;
        width: 120px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .circle-content h2 {
        color: white;
        font-weight: 700;
    }

    /* Kriteria Card */
    .kriteria-card {
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .kriteria-card:hover {
        border-color: var(--primary);
        box-shadow: 0 5px 20px rgba(147, 33, 54, 0.15);
    }

    .kriteria-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        border: none;
        padding: 15px 20px;
    }

    .kriteria-btn {
        color: white;
        text-decoration: none;
        font-size: 16px;
        font-weight: 600;
        width: 100%;
        text-align: left;
        padding: 0;
    }

    .kriteria-btn:hover {
        color: white;
    }

    .kriteria-progress {
        font-size: 14px;
        padding: 6px 12px;
    }

    /* Elemen Card */
    .elemen-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
    }

    .elemen-header {
        background: #f8f9fa;
        border: none;
        padding: 12px 15px;
    }

    .elemen-btn {
        color: #333;
        text-decoration: none;
        font-size: 15px;
        width: 100%;
        text-align: left;
        padding: 0;
    }

    .elemen-btn:hover {
        color: var(--primary);
    }

    .elemen-progress {
        font-size: 12px;
        padding: 4px 10px;
    }

    /* Indikator Card */
    .indikator-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .indikator-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .indikator-card.has-penilaian {
        border-left: 4px solid var(--success);
    }

    /* Form Styling */
    .form-select:focus,
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(147, 33, 54, 0.25);
    }

    .skor-select option {
        padding: 10px;
    }

    .komentar-textarea {
        resize: vertical;
        min-height: 100px;
    }

    /* Chevron Animation */
    .chevron-icon {
        transition: transform 0.3s ease;
    }

    .collapsed .chevron-icon {
        transform: rotate(0deg);
    }

    button[aria-expanded="true"] .chevron-icon {
        transform: rotate(90deg);
    }

    /* Floating Action Button */
    .floating-actions {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .btn-floating {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        transition: all 0.3s ease;
    }

    .btn-floating:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    }

    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loading-overlay.show {
        display: flex;
    }

    .spinner-border {
        width: 1rem;
        height: 1rem;
        border-width: 0.3em;
    }

    /* NEW: Action Buttons Bar */
    .action-buttons-bar {
        position: sticky;
        top: 0;
        z-index: 100;
        background: white;
        padding: 15px 0;
        border-bottom: 2px solid #e0e0e0;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .btn-action-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stat-circle {
            width: 100px;
            height: 100px;
        }

        .circle-content h2 {
            font-size: 1.5rem;
        }

        .kriteria-btn,
        .elemen-btn {
            font-size: 14px;
        }

        .floating-actions {
            bottom: 20px;
            right: 20px;
        }

        .btn-action-group {
            flex-direction: column;
        }

        .btn-action-group .btn {
            width: 100%;
        }
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header Card -->
    <div class="card mb-4 header-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Penilaian Asesmen Kecukupan</h3>
                    <p class="text-muted mb-0">{{ $asesmen->name }}</p>
                </div>
                <a href="{{ route('ak.berkas') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <!-- Progress Section -->
            <div class="progress-wrapper mt-4">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0">Progress Penilaian</h5>
                            <span class="badge bg-primary fs-6" id="progressPercentage">
                                {{ $progress['percentage'] }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-gradient" role="progressbar" id="progressBarPenilaian" style="width: {{ $progress['percentage'] }}%" aria-valuenow="{{ $progress['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <small class="text-white mt-1 d-block">
                            <span id="progressCompleted">{{ $progress['completed'] }}</span> dari
                            <span id="progressTotal">{{ $progress['total'] }}</span> elemen penilaian selesai dinilai
                        </small>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="stat-circle">
                            <div class="circle-content">
                                <h2 class="mb-0" id="progressCount">{{ $progress['completed'] }}/{{ $progress['total'] }}</h2>
                                <small class="text-white">Elemen Penilaian</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white">
            <div class="row align-items-center my-2">
                <div class="col-12 mb-md-0">
                    {{-- Status Indicator --}}
                    @php
                    $assignment = $asesmen->userRoles->where('id_user', Auth::id())->first();
                    $statusPekerjaan = $assignment->status_pekerjaan ?? 'not_started';
                    $isSubmitted = $statusPekerjaan === 'submitted';
                    $isApproved = $statusPekerjaan === 'approved';
                    $needsRevision = $statusPekerjaan === 'revision';
                    @endphp

                    @if($needsRevision)
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Perlu Revisi!</strong> Validator meminta Anda untuk merevisi beberapa penilaian.
                        Silakan periksa catatan validasi dan lakukan perbaikan.
                    </div>
                    @endif

                    @if($isSubmitted && !$isApproved)
                    <div class="alert alert-info alert-permanent mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Sudah Di-Submit!</strong> Penilaian Anda sedang menunggu validasi dari validator.
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="btnUnsubmit">
                            <i class="bi bi-arrow-counterclockwise"></i> Batalkan Submit
                        </button>
                    </div>
                    @endif

                    @if($isApproved)
                    <div class="alert alert-success alert-permanent  mb-3">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Penilaian Disetujui!</strong> Penilaian Anda telah divalidasi dan disetujui oleh validator.
                        Asesmen siap dilanjutkan ke tahap AL.
                    </div>
                    @endif

                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        {{-- Submit Button --}}
                        <div>
                            @if(!$isSubmitted && !$isApproved)
                            <button type="button" class="btn btn-success btn-md" id="btnSubmit">
                                <i class="bi bi-check-circle"></i> Finalisasi dan Kirim
                            </button>
                            <small class="d-block text-muted mt-1">
                                <i class="bi bi-info-circle"></i>
                                Pastikan semua elemen sudah dinilai sebelum mengirim
                            </small>
                            @elseif($isSubmitted)
                            <button type="button" class="btn btn-secondary btn-md" disabled>
                                <i class="bi bi-clock-history"></i> Menunggu Validasi
                            </button>
                            @else
                            <button type="button" class="btn btn-success btn-md" disabled>
                                <i class="bi bi-check-all"></i> Penilaian Disetujui
                            </button>
                            @endif
                        </div>

                        {{-- Excel Actions (Dropdown Group) --}}
                        <div class="btn-group">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-download"></i> Download Excel
                                </button>

                                <ul class="dropdown-menu">
                                    <li>
                                        <h6 class="dropdown-header">
                                            <i class="bi bi-file-earmark-excel"></i> Pilih Jenis Download
                                        </h6>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>

                                    <li>
                                        <a class="dropdown-item" href="#" id="btnDownloadTemplate">
                                            <i class="bi bi-file-earmark-text text-info"></i>
                                            Download Template (Kosong)
                                            <small class="d-block text-muted">Format Excel untuk import</small>
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item" href="#" id="btnDownloadData">
                                            <i class="bi bi-file-earmark-excel text-success"></i>
                                            Download Hasil Penilaian
                                            <small class="d-block text-muted">Excel berisi penilaian Anda</small>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <button type="button" class="btn btn-outline-primary btn-md" id="btnImport" {{ $isSubmitted || $isApproved ? 'disabled' : '' }}>
                                <i class="bi bi-upload"></i> Import Excel
                            </button>

                            <button type="button" class="btn btn-outline-secondary btn-md" id="btnImportHistory">
                                <i class="bi bi-clock-history"></i>
                            </button>

                            <button type="button" class="btn btn-outline-danger btn-md" id="btnResetAll" {{ $isSubmitted || $isApproved ? 'disabled' : '' }} title="Reset Semua Penilaian">
                                <i class="bi bi-trash"></i> Reset All
                            </button>
                        </div>
                    </div>

                    {{-- Progress Summary --}}
                    <div class="mt-3 p-3 bg-light rounded">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h3 class="mb-0" id="summaryTotal"><b>{{ $progress['total'] }}</b></h3>
                                <small class="text-muted">Total Elemen</small>
                            </div>
                            <div class="col-md-3">
                                <h3 class="mb-0 text-success" id="summaryCompleted"><b>{{ $progress['completed'] }}</b></h3>
                                <small class="text-muted">Sudah Dinilai</small>
                            </div>
                            <div class="col-md-3">
                                <h3 class="mb-0 text-warning" id="summaryRemaining"><b>{{ $progress['remaining'] }}</b></h3>
                                <small class="text-muted">Belum Dinilai</small>
                            </div>
                            <div class="col-md-3">
                                <h3 class="mb-0 text-primary" id="summaryPercentage"><b>{{ $progress['percentage'] }}%</b></h3>
                                <small class="text-muted">Progress</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== HEATMAP MATRIX (ENHANCED) ========== -->
    @include('asesmen.ak.components.heatmap-matrix')

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white border-bottom py-2">
            <div class="d-flex justify-content-between">
                <h5 class="mb-0">
                    <i class="bi bi-card-checklist"></i> Elemen Penilaian
                </h5>
                <div class="btn-action-group justify-content-md-end">
                    <button id="toggleAllAccordion" class="btn btn-outline-primary btn-sm" data-expanded="false">
                        <i class="bi bi-arrows-expand"></i>
                        Expand All
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body pb-2">
            <!-- Alert Info -->
            <div class="alert alert-info alert-dismissible alert-permanent fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Petunjuk:</strong>
                <ol class="mb-0 mt-2">
                    <li>Gunakan <strong>tombol di atas</strong> untuk submit, export, atau import penilaian</li>
                    <li>Klik <strong>Expand/Collapse All</strong> untuk membuka/menutup semua accordion</li>
                    <li>Klik <strong>sel di matrix</strong> untuk langsung membuka elemen tersebut</li>
                    <li>Pilih kategori penilaian: <span class="badge bg-danger">0-1 (Not Met)</span>, <span class="badge bg-warning">2 (Weakness)</span>, <span class="badge bg-success">3 (Met)</span>, <span class="badge bg-success">4 (Exceeding)</span></li>
                    <li>Penilaian akan <strong>otomatis tersimpan</strong> setelah Anda mengisi kategori dan komentar</li>
                </ol>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Accordion per Kriteria & Pernyataan Standar -->
    <div class="accordion" id="accordionKriteria">
        @foreach($kriterias as $kriteriaIndex => $kriteria)
        <div class="card mb-3 kriteria-card">
            <!-- Kriteria Header -->
            <div class="card-header kriteria-header" id="heading-kriteria-{{ $kriteria->id_kriteria }}">
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-link kriteria-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-kriteria-{{ $kriteria->id_kriteria }}" aria-expanded="false" aria-controls="collapse-kriteria-{{ $kriteria->id_kriteria }}">
                        <i class="bi bi-chevron-right me-2 chevron-icon"></i>
                        <strong>{{ $kriteria->kode_kriteria }}:</strong> {{ $kriteria->nama_kriteria }}
                    </button>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge bg-secondary kriteria-progress" data-kriteria-id="{{ $kriteria->id_kriteria }}">
                            0 / {{ $kriteria->elemenStandar->count() }}
                        </span>
                        <button type="button" class="btn btn-sm btn-light" onclick="toggleKriteriaAccordion({{ $kriteria->id_kriteria }})" title="Expand/Collapse Semua Pernyataan Standar">
                            <i class="bi bi-arrows-expand"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Kriteria Body -->
            <div id="collapse-kriteria-{{ $kriteria->id_kriteria }}" class="accordion-collapse collapse kriteria-collapse" aria-labelledby="heading-kriteria-{{ $kriteria->id_kriteria }}" data-bs-parent="#accordionKriteria">
                <div class="card-body">
                    @if($kriteria->keterangan)
                    <div class="alert alert-light alert-permanent mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ $kriteria->keterangan }}
                    </div>
                    @endif

                    <!-- Nested Accordion per Pernyataan Standar (Elemen) -->
                    <div class="accordion accordion-elemen" id="accordionElemen-{{ $kriteria->id_kriteria }}">
                        @foreach($kriteria->elemenStandar as $elemenIndex => $elemen)
                        @php
                        // Get penilaian for this elemen (not indikator!)
                        $penilaian = $elemen->penilaian->first(); // Assuming relation exists
                        $hasPenilaian = $penilaian && $penilaian->skor !== null;

                        $totalIndikator = $elemen->indikator->count();

                        // Count jenis indikator
                        $kuantitatif = $elemen->indikator ? $elemen->indikator->filter(function($ind) {
                        return $ind->jenisIndikator &&
                        stripos($ind->jenisIndikator->nama_jenis, 'kuantitatif') !== false;
                        })->count() : 0;

                        $kualitatif = $elemen->indikator ? $elemen->indikator->filter(function($ind) {
                        return $ind->jenisIndikator &&
                        stripos($ind->jenisIndikator->nama_jenis, 'kualitatif') !== false;
                        })->count() : 0;
                        @endphp

                        <div class="card mb-3 elemen-card @if($hasPenilaian) has-penilaian @endif" data-elemen-id="{{ $elemen->id_elemen }}">
                            <!-- Pernyataan Standar Header -->
                            <div class="card-header elemen-header" id="heading-elemen-{{ $elemen->id_elemen }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <button class="btn btn-link elemen-btn collapsed d-flex align-items-center w-100" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-elemen-{{ $elemen->id_elemen }}" aria-expanded="false" aria-controls="collapse-elemen-{{ $elemen->id_elemen }}">

                                        <i class="bi bi-chevron-right me-2 chevron-icon"></i>

                                        <!-- kiri -->
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary">{{ $elemen->kode_elemen }}</span>
                                            <strong class="pernyataan-text">
                                                {{ $elemen->pernyataan_elemen }}
                                            </strong>
                                        </div>

                                        <!-- kanan: indikator + status -->
                                        <div class="ms-auto d-flex align-items-center gap-2">
                                            <span class="badge bg-info">{{ $totalIndikator }} Indikator</span>

                                            @if($hasPenilaian)
                                            <span class="badge bg-success status-badge">
                                                <i class="bi bi-check-circle"></i> Sudah Dinilai
                                            </span>
                                            @else
                                            <span class="badge bg-warning text-dark status-badge">
                                                <i class="bi bi-clock"></i> Belum Dinilai
                                            </span>
                                            @endif
                                        </div>

                                    </button>
                                </div>
                            </div>
                            <!-- Pernyataan Standar Body -->
                            <div id="collapse-elemen-{{ $elemen->id_elemen }}" class="accordion-collapse collapse elemen-collapse" aria-labelledby="heading-elemen-{{ $elemen->id_elemen }}" data-bs-parent="#accordionElemen-{{ $kriteria->id_kriteria }}">
                                <div class="card-body">
                                    {{-- @if($elemen->pernyataan)
                                    <div class="alert alert-light alert-permanent mb-4">
                                        <i class="bi bi-lightbulb me-2"></i>
                                        <strong>Keterangan:</strong> {{ $elemen->pernyataan[0]->pernyataan }}
                                </div>
                                @endif --}}

                                <!-- DAFTAR INDIKATOR (INFORMASI SAJA) -->
                                <div class="indikator-list-info mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">
                                            <i class="bi bi-list-check me-2"></i>
                                            <strong>Daftar Indikator sebagai Panduan Penilaian</strong>
                                        </h6>
                                        @if($kuantitatif > 0 || $kualitatif > 0)
                                        <div>
                                            @if($kuantitatif > 0)
                                            <span class="badge bg-success">
                                                <i class="bi bi-graph-up"></i> {{ $kuantitatif }} Kuantitatif
                                            </span>
                                            @endif
                                            @if($kualitatif > 0)
                                            <span class="badge bg-info">
                                                <i class="bi bi-chat-quote"></i> {{ $kualitatif }} Kualitatif
                                            </span>
                                            @endif
                                        </div>
                                        @endif
                                    </div>

                                    @if($totalIndikator > 0)
                                    <div class="alert alert-info alert-permanent">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <small>
                                            <strong>Catatan:</strong> Indikator di bawah ini adalah panduan untuk menilai pernyataan standar di atas.
                                            Pertimbangkan seluruh indikator dalam memberikan penilaian dan justifikasi.
                                        </small>
                                    </div>
                                    @else
                                    <div class="alert alert-secondary alert-permanent">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <small>
                                            Belum ada indikator yang terdapat di elemen penilaian
                                        </small>
                                    </div>
                                    @endif

                                    <!-- List Indikator -->
                                    <div class="list-group">
                                        @foreach($elemen->indikator as $indikatorIndex => $indikator)
                                        @php
                                        $jenisIndikator = $indikator->jenisIndikator;
                                        $isKuantitatif = $jenisIndikator && stripos($jenisIndikator->nama_jenis, 'kuantitatif') !== false;
                                        $isKualitatif = $jenisIndikator && stripos($jenisIndikator->nama_jenis, 'kualitatif') !== false;
                                        @endphp

                                        <div class="list-group-item">
                                            <div class="d-flex align-items-start">
                                                <div class="me-3 flex-shrink-0">
                                                    <span class="badge bg-secondary" style="font-size: 14px; padding: 8px 12px;">
                                                        {{ $indikatorIndex + 1 }}
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="mb-2">
                                                        <span class="badge bg-primary me-2">
                                                            {{ $indikator->kode_indikator }}
                                                        </span>

                                                        @if($isKuantitatif)
                                                        <span class="badge bg-success" title="Indikator Kuantitatif">
                                                            <i class="bi bi-graph-up"></i> Kuantitatif
                                                        </span>
                                                        @elseif($isKualitatif)
                                                        <span class="badge bg-info" title="Indikator Kualitatif">
                                                            <i class="bi bi-chat-quote"></i> Kualitatif
                                                        </span>
                                                        @else
                                                        <span class="badge bg-secondary">
                                                            {{ $jenisIndikator->nama_jenis ?? 'N/A' }}
                                                        </span>
                                                        @endif
                                                    </div>

                                                    <p class="mb-0" style="line-height: 1.6;">
                                                        {{ $indikator->deskripsi_indikator }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- FORM PENILAIAN (PER ELEMEN) -->
                                <div class="penilaian-form-wrapper mb-4">
                                    <div class="card border-{{ $hasPenilaian ? 'success' : 'warning' }}">
                                        <div class="card-header bg-{{ $hasPenilaian ? 'success' : 'warning' }} bg-opacity-10">
                                            <h6 class="mb-0">
                                                <i class="bi bi-clipboard-check me-2"></i>
                                                <strong>Penilaian Elemen</strong>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <form class="form-penilaian" data-elemen-id="{{ $elemen->id_elemen }}">
                                                <div class="row mb-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold">
                                                            <i class="bi bi-star me-1"></i> Pilih Kategori Penilaian
                                                        </label>
                                                        <select class="form-select skor-select" name="skor" required>
                                                            <option value="">-- Pilih Kategori --</option>
                                                            <option value="0" @if($hasPenilaian && $penilaian->skor == 0) selected @endif>
                                                                0 - Tidak Memenuhi (Not Met)
                                                            </option>
                                                            <option value="1" @if($hasPenilaian && $penilaian->skor == 1) selected @endif>
                                                                1 - Tidak Memenuhi (Not Met)
                                                            </option>
                                                            <option value="2" @if($hasPenilaian && $penilaian->skor == 2) selected @endif>
                                                                2 - Lemah (Weakness)
                                                            </option>
                                                            <option value="3" @if($hasPenilaian && $penilaian->skor == 3) selected @endif>
                                                                3 - Memenuhi (Met)
                                                            </option>
                                                            <option value="4" @if($hasPenilaian && $penilaian->skor == 4) selected @endif>
                                                                4 - Pelampauan Standar (Exceeding)
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <label class="form-label fw-semibold">
                                                            <i class="bi bi-chat-left-text me-1"></i> Komentar/Justifikasi Penilaian
                                                        </label>
                                                        <textarea class="form-control komentar-textarea" name="komentar" rows="4" placeholder="Berikan justifikasi dan analisis penilaian berdasarkan seluruh indikator di bawah ini..." required>{{ $hasPenilaian ? $penilaian->komentar : '' }}</textarea>
                                                        <small class="text-muted">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            <span class="char-count">{{ $hasPenilaian ? strlen($penilaian->komentar) : 0 }}</span> karakter
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="save-status text-muted small">
                                                        <i class="bi bi-cloud-check"></i>
                                                        <span class="status-text">
                                                            @if($hasPenilaian)
                                                            Tersimpan pada {{ $penilaian->updated_at->format('d M Y H:i') }}
                                                            @else
                                                            Belum ada penilaian
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-reset">
                                                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                                                        </button>
                                                        <button type="submit" class="btn btn-sm btn-primary btn-save">
                                                            <i class="bi bi-cloud-upload"></i> Simpan
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Floating Action Button -->
<div class="floating-actions">
    <button type="button" class="btn btn-primary btn-floating" id="btnScrollTop" title="Scroll to Top">
        <i class="bi bi-arrow-up"></i>
    </button>
</div>
</div>

{{-- Import Excel Modal --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="bi bi-upload"></i> Import Penilaian dari Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    {{-- Instructions --}}
                    <div class="alert alert-info alert-permanent mb-3">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle"></i> Petunjuk Import
                        </h6>
                        <ul class="mb-0 small">
                            <li>File harus berformat Excel (.xlsx atau .xls)</li>
                            <li>Gunakan template yang sudah disediakan</li>
                            <li>Jangan ubah struktur atau nama sheet</li>
                            <li>Kolom <strong>Kode Elemen (E)</strong> tidak boleh diubah</li>
                            <li>Isi penilaian pada kolom I-M (skor 0-4)</li>
                            <li>Maksimal ukuran file: 10MB</li>
                        </ul>
                    </div>

                    {{-- File Upload Area --}}
                    <div class="mb-3">
                        <label for="excelFile" class="form-label fw-semibold">
                            Pilih File Excel <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" id="excelFile" name="file" accept=".xlsx,.xls" required>
                        <div class="invalid-feedback">
                            Mohon pilih file Excel terlebih dahulu
                        </div>
                    </div>

                    {{-- File Info Display --}}
                    <div id="fileInfo" class="alert alert-secondary alert-permanent d-none">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-file-earmark-excel text-success"></i>
                                <strong>File dipilih:</strong>
                                <span id="fileName">-</span>
                            </div>
                            <div>
                                <small class="text-muted">
                                    Ukuran: <span id="fileSize">-</span>
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar (hidden initially) --}}
                    <div id="importProgress" class="d-none">
                        <div class="mb-2">
                            <strong>Progress Import:</strong>
                            <span id="progressText">0%</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div id="progressBarImport" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">
                                0%
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            <span id="importedRows">0</span> / <span id="totalRows">0</span> baris diproses
                        </small>
                    </div>

                    {{-- Status Alert --}}
                    <div id="importAlert" class="alert d-none mt-3" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnCloseImport">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitImport">
                        <i class="bi bi-upload"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Import Result Modal --}}
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" id="resultHeader">
                <h5 class="modal-title" id="resultModalLabel">
                    <i class="bi bi-check-circle"></i> Hasil Import
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Success Result --}}
                <div id="successResult" class="d-none">
                    <div class="text-center mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 text-success">Import Berhasil!</h4>
                    </div>

                    <div class="row text-center mb-3">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-primary mb-0" id="resultTotal">0</h3>
                                    <small class="text-muted">Total Baris</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-success mb-0" id="resultImported">0</h3>
                                    <small class="text-muted">Berhasil Import</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-danger mb-0" id="resultFailed">0</h3>
                                    <small class="text-muted">Gagal</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <i class="bi bi-info-circle"></i>
                        <strong>Penilaian Anda telah berhasil diimport!</strong>
                        <p class="mb-0 mt-2">Silakan review hasil import dan lakukan finalisasi jika sudah sesuai.</p>
                    </div>
                </div>

                {{-- Error Result --}}
                <div id="errorResult" class="d-none">
                    <div class="text-center mb-4">
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 text-danger">Import Gagal</h4>
                    </div>

                    <div class="alert alert-danger">
                        <strong>Error:</strong>
                        <p id="errorMessage" class="mb-0"></p>
                    </div>

                    {{-- Error List --}}
                    <div id="errorListContainer" class="d-none">
                        <h6 class="mb-2">Detail Error:</h6>
                        <div class="alert alert-warning">
                            <ul id="errorList" class="mb-0 small"></ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
                <button type="button" class="btn btn-primary" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Halaman
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Import History Modal --}}
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="historyModalLabel">
                    <i class="bi bi-clock-history"></i> Riwayat Import
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="historyContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat riwayat import...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import History Modal -->
<div class="modal fade" id="importHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history"></i> Riwayat Import Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading State -->
                <div id="historyLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat riwayat import...</p>
                </div>

                <!-- History Table -->
                <div id="historyContent" class="d-none">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="20%">Waktu</th>
                                    <th width="20%">File</th>
                                    <th width="15%">Status</th>
                                    <th width="15%">Progress</th>
                                    <th width="15%">Success Rate</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div id="historyEmpty" class="text-center py-5 d-none">
                        <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">Belum ada riwayat import</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Result Modal -->
<div class="modal fade" id="importResultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white" id="resultModalHeader">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle"></i> Import Berhasil
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="importResultContent">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Halaman
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@php
$statusPekerjaan = $assignment['status_pekerjaan'];
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const asesmenId = "{{ $asesmen->id }}";

        const checkStatusPekerjaan = @json(isset($assignment) && in_array($statusPekerjaan, ['submitted', 'approved']));
        let saveTimeout;
        const AUTO_SAVE_DELAY = 2000;
        let currentImportLogId = null;
        let importStatusInterval = null;

        // Loading Overlay
        const loadingOverlay = createLoadingOverlay();
        const toggleBtn = document.getElementById('toggleAllAccordion');

        // Initialize
        initializeCharCounters();
        initializeFormHandlers();
        initializeScrollButton();
        initializeActionButtons();
        updateAllProgress();

        /**
         * ========================================
         * ACTION BUTTONS HANDLERS (NEW)
         * ========================================
         */
        function initializeActionButtons() {
            // Expand All
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const isExpanded = this.dataset.expanded === 'true';
                    document.querySelectorAll('#accordionKriteria .accordion-collapse')
                        .forEach(panel => {
                            const c = bootstrap.Collapse.getOrCreateInstance(panel, {
                                toggle: false
                            });
                            isExpanded ? c.hide() : c.show();
                        });

                    this.dataset.expanded = (!isExpanded).toString();
                    this.innerHTML = isExpanded ?
                        '<i class="bi bi-arrows-expand"></i> Expand All' :
                        '<i class="bi bi-arrows-collapse"></i> Collapse All';
                });
            }

            // Submit Penilaian
            const btnSubmit = document.getElementById('btnSubmit');
            if (btnSubmit) btnSubmit.addEventListener('click', submitPenilaian);
            const btnUnsubmit = document.getElementById('btnUnsubmit');
            if (btnUnsubmit) btnUnsubmit.addEventListener('click', unSubmitPenilaian);

            // Export Excel
            const btnExport = document.getElementById('btnExport');
            if (btnExport) btnExport.addEventListener('click', exportExcel);

            // Import Excel
            document.getElementById('btnImport').addEventListener('click', function() {
                // Reset form
                document.getElementById('importForm').reset();
                document.getElementById('fileInfo').classList.add('d-none');
                document.getElementById('importProgress').classList.add('d-none');
                const importAlert = document.getElementById('importAlert')
                if (importAlert) importAlert.classList.add('d-none');

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('importModal'));
                modal.show();
            });
            document.getElementById('btnImportHistory').addEventListener('click', importHistoryExcel);
            const btnResetAll = document.getElementById('btnResetAll');
            if (btnResetAll) btnResetAll.addEventListener('click', resetAllPenilaian);
            // Import Form Submit
            document.getElementById('importForm').addEventListener('submit', importExcel);

            // Download Template
            document.getElementById('btnDownloadTemplate').addEventListener('click', downloadTemplate);
            document.getElementById('btnDownloadData').addEventListener('click', downloadDataExcel);
        }

        /**
         * Toggle Kriteria Accordion
         */
        window.toggleKriteriaAccordion = function(kriteriaId) {
            const kriteriaCollapse = document.querySelector(`#collapse-kriteria-${kriteriaId}`);
            const elemenCollapses = kriteriaCollapse.querySelectorAll('.elemen-collapse');

            // Check if any elemen is open
            const anyOpen = Array.from(elemenCollapses).some(el => el.classList.contains('show'));

            if (anyOpen) {
                // Close all elemen
                elemenCollapses.forEach(collapse => {
                    new bootstrap.Collapse(collapse, {
                        hide: true
                    });
                });
            } else {
                // Open all elemen
                elemenCollapses.forEach(collapse => {
                    new bootstrap.Collapse(collapse, {
                        show: true
                    });
                });
            }
        };

        /**
         * Submit Penilaian
         */
        async function submitPenilaian() {
            // Get current progress
            const completed = parseInt(document.getElementById('summaryCompleted').textContent);
            const total = parseInt(document.getElementById('summaryTotal').textContent);

            // Validation check
            if (completed < total) {
                const confirmed = await Swal.fire({
                    icon: 'warning'
                    , title: 'Penilaian Belum Lengkap'
                    , html: `
                    <p>Anda baru menilai <strong>${completed} dari ${total}</strong> elemen.</p>
                    <p class="text-danger">Anda harus menilai semua elemen sebelum submit!</p>
                `
                    , showCancelButton: true
                    , confirmButtonText: 'OK, Lanjutkan Penilaian'
                    , cancelButtonText: 'Batal'
                    , confirmButtonColor: '#932136'
                    , cancelButtonColor: '#6c757d'
                    , reverseButtons: true
                });

                return; // Stop submit
            }

            // Confirmation
            const confirmed = await Swal.fire({
                icon: 'question'
                , title: 'Konfirmasi Submit Penilaian'
                , html: `
                <div class="text-start">
                    <p><strong>Anda akan mengirim penilaian untuk validasi.</strong></p>
                    <p>Setelah di-submit:</p>
                    <ul>
                        <li>Penilaian akan dikirim ke validator</li>
                        <li>Anda tidak bisa edit penilaian</li>
                        <li>Validator akan memvalidasi penilaian Anda</li>
                        <li>Jika perlu revisi, Anda akan diminta memperbaiki</li>
                    </ul>
                    <p class="text-primary"><i class="bi bi-info-circle"></i> Total: <strong>${total} elemen</strong> sudah dinilai</p>
                </div>
            `
                , showCancelButton: true
                , confirmButtonText: '<i class="bi bi-send"></i> Ya, Submit Sekarang'
                , cancelButtonText: 'Batal'
                , confirmButtonColor: '#28a745'
                , cancelButtonColor: '#6c757d'
                , reverseButtons: true
                , width: '600px'
            });

            if (!confirmed.isConfirmed) {
                return;
            }

            showLoading();

            try {
                const response = await fetch(`/ak/berkas/${asesmenId}/submit`, {
                    method: 'POST'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                        , 'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                hideLoading();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Submit Berhasil!'
                        , html: `
                        <div class="text-center">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                            <p class="mt-3">${data.message}</p>
                            <div class="alert alert-info mt-3">
                                <small>
                                    <i class="bi bi-clock"></i> Di-submit pada: ${data.submitted_at}
                                </small>
                            </div>
                        </div>
                    `
                        , showConfirmButton: true
                        , confirmButtonText: 'OK'
                        , confirmButtonColor: '#28a745'
                    });

                    // Reload to update UI
                    window.location.reload();

                } else {
                    throw new Error(data.message || 'Gagal submit penilaian');
                }

            } catch (error) {
                hideLoading();
                console.error('Submit error:', error);

                Swal.fire({
                    icon: 'error'
                    , title: 'Gagal Submit'
                    , text: error.message
                    , confirmButtonColor: '#d33'
                });
            }
        }

        async function unSubmitPenilaian() {
            const confirmed = await Swal.fire({
                icon: 'warning'
                , title: 'Batalkan Submit?'
                , html: `
                <p>Apakah Anda yakin ingin membatalkan submit?</p>
                <p class="text-muted">Penilaian akan kembali ke status draft dan Anda bisa melakukan edit.</p>
            `
                , showCancelButton: true
                , confirmButtonText: 'Ya, Batalkan'
                , cancelButtonText: 'Tidak'
                , confirmButtonColor: '#ffc107'
                , cancelButtonColor: '#6c757d'
                , reverseButtons: true
            });

            if (!confirmed.isConfirmed) {
                return;
            }

            showLoading();

            try {
                const response = await fetch(`/ak/berkas/${asesmenId}/unsubmit`, {
                    method: 'POST'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                        , 'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                hideLoading();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Submit Dibatalkan'
                        , text: data.message
                        , confirmButtonColor: '#28a745'
                    });

                    // Reload to update UI
                    window.location.reload();

                } else {
                    throw new Error(data.message || 'Gagal batalkan submit');
                }

            } catch (error) {
                hideLoading();
                console.error('Unsubmit error:', error);

                Swal.fire({
                    icon: 'error'
                    , title: 'Gagal Batalkan'
                    , text: error.message
                    , confirmButtonColor: '#d33'
                });
            }
        }

        function setElemenStatus(idElemen, hasPenilaian) {
            const elemenCard = document.querySelector(`.elemen-card[data-elemen-id="${idElemen}"]`);
            if (!elemenCard) return;

            // Tambah/hapus class has-penilaian
            if (hasPenilaian) {
                elemenCard.classList.add('has-penilaian');
            } else {
                elemenCard.classList.remove('has-penilaian');
            }

            // Update badge status
            const badge = elemenCard.querySelector('.status-badge');
            if (!badge) return;

            if (hasPenilaian) {
                badge.classList.remove('bg-warning', 'text-dark');
                badge.classList.add('bg-success');
                badge.innerHTML = '<i class="bi bi-check-circle"></i> Sudah Dinilai';
            } else {
                badge.classList.remove('bg-success');
                badge.classList.add('bg-warning', 'text-dark');
                badge.innerHTML = '<i class="bi bi-clock"></i> Belum Dinilai';
            }

            // Sekalian update progress kriteria
            updateAllProgress();
        }

        /**
         * Export Excel
         */
        async function exportExcel() {
            showLoading();

            try {
                window.location.href = `/ak/berkas/${asesmenId}/export`;

                setTimeout(() => {
                    hideLoading();
                    Swal.fire({
                        icon: 'success'
                        , title: 'Export Berhasil!'
                        , text: 'File sedang didownload'
                        , timer: 2000
                        , showConfirmButton: false
                    });
                }, 1000);
            } catch (error) {
                hideLoading();
                Swal.fire({
                    icon: 'error'
                    , title: 'Error'
                    , text: 'Gagal export Excel'
                });
            }
        }

        /**
         * Import Excel
         */
        async function importExcel(e) {
            if (e) e.preventDefault();
            const fileInput = document.getElementById('excelFile');
            const file = fileInput.files[0];

            // Validate file selected
            if (!file) {
                fileInput.classList.add('is-invalid');
                showAlert('importAlert', 'danger', 'Mohon pilih file Excel terlebih dahulu!');
                return;
            }

            // Create FormData
            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');

            // Disable submit button
            const btnSubmit = document.getElementById('btnSubmitImport');
            const btnClose = document.getElementById('btnCloseImport');
            btnSubmit.disabled = true;
            btnClose.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';

            // Show progress
            const importProgress = document.getElementById('importProgress')
            if (importProgress) importProgress.classList.remove('d-none');
            const importAlert = document.getElementById('importAlert')
            if (importAlert) importAlert.classList.add('d-none');

            try {
                // Upload file
                const response = await fetch(`/ak/berkas/${asesmenId}/import`, {
                    method: 'POST'
                    , body: formData
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Upload gagal');
                }

                if (data.success) {
                    // File uploaded, start polling for progress
                    const importLogId = data.import_log_id;

                    // Update button
                    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

                    // Start polling
                    pollImportStatus(importLogId);
                } else {
                    throw new Error(data.message || 'Upload gagal');
                }

            } catch (error) {
                console.error('Import error:', error);

                // Re-enable buttons
                btnSubmit.disabled = false;
                btnClose.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-upload"></i> Upload & Import';

                // Show error
                showAlert('importAlert', 'danger', error.message);
                document.getElementById('importProgress').classList.add('d-none');
            }
        }

        /**
         * ============================================
         * POLLING IMPORT STATUS
         * ============================================
         */

        let pollingInterval = null;

        function pollImportStatus(importLogId) {
            // Clear any existing interval
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }

            // Poll every 2 seconds
            pollingInterval = setInterval(async () => {
                try {
                    const response = await fetch(`/ak/import-status/${importLogId}`, {
                        headers: {
                            'Accept': 'application/json'
                            , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();
                    if (data.success) {
                        const log = data.data;

                        // Update progress
                        updateProgressImport(log);

                        // Check if completed
                        if (log.status === 'completed' || log.status === 'failed') {
                            clearInterval(pollingInterval);
                            showResult(log);
                        }
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                    clearInterval(pollingInterval);
                    showAlert('importAlert', 'danger', 'Gagal memeriksa status import');
                }
            }, 2000);
        }

        /**
         * Create Loading Overlay
         */
        function createLoadingOverlay() {
            const overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `;
            document.body.appendChild(overlay);
            return overlay;
        }

        function showLoading() {
            loadingOverlay.classList.add('show');
        }

        function hideLoading() {
            loadingOverlay.classList.remove('show');
        }

        /**
         * Initialize Character Counters
         */
        function initializeCharCounters() {
            document.querySelectorAll('.komentar-textarea').forEach(textarea => {
                textarea.addEventListener('input', function() {
                    const charCount = this.closest('.col-md-8').querySelector('.char-count');
                    if (charCount) {
                        charCount.textContent = this.value.length;
                    }
                });
            });
        }

        /**
         * Initialize Form Handlers
         */
        function initializeFormHandlers() {
            document.querySelectorAll('.form-penilaian').forEach(form => {
                const idElemen = form.dataset.elemenId;

                if (!idElemen) {
                    console.error('Elemen ID not found for form:', form);
                    return;
                }

                const skorSelect = form.querySelector('.skor-select');
                const komentarTextarea = form.querySelector('.komentar-textarea');
                const btnSave = form.querySelector('.btn-save');
                const btnReset = form.querySelector('.btn-reset');

                // Auto-save on change (debounced)
                [skorSelect, komentarTextarea].forEach(element => {
                    element.addEventListener('input', function() {
                        clearTimeout(saveTimeout);
                        saveTimeout = setTimeout(() => {
                            if (skorSelect.value && komentarTextarea.value.trim()) {
                                autoSavePenilaian(form, idElemen);
                            }
                        }, AUTO_SAVE_DELAY);
                    });
                });

                // Manual save
                btnSave.addEventListener('click', function(e) {
                    e.preventDefault();
                    savePenilaian(form, idElemen);
                });

                // Reset form
                btnReset.addEventListener('click', function() {
                    if (confirm('Apakah Anda yakin ingin mereset penilaian ini?')) {
                        form.reset();
                        updateCharCount(komentarTextarea);
                        updateSaveStatus(form, 'Belum ada penilaian', 'text-muted');

                        const card = form.closest('.indikator-card');
                        card.classList.remove('has-penilaian');

                        const badge = card.querySelector('.badge.bg-success');
                        if (badge) {
                            badge.className = 'badge bg-warning text-dark';
                            badge.innerHTML = '<i class="bi bi-clock"></i> Belum Dinilai';
                        }
                    }
                });
            });
        }

        /**
         * Auto Save (Silent)
         */
        async function autoSavePenilaian(form, idElemen) {
            const formData = new FormData(form);

            try {
                const response = await fetch(`/ak/berkas/${asesmenId}/nilai`, {
                    method: 'POST'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Content-Type': 'application/json'
                        , 'Accept': 'application/json'
                    , }
                    , body: JSON.stringify({
                        id_elemen: idElemen
                        , skor: formData.get('skor')
                        , komentar: formData.get('komentar')
                    , })
                });

                const data = await response.json();

                if (data.success) {
                    const now = new Date();
                    const timeStr = now.toLocaleString('id-ID', {
                        day: '2-digit'
                        , month: 'short'
                        , year: 'numeric'
                        , hour: '2-digit'
                        , minute: '2-digit'
                    });
                    updateSaveStatus(form, `Tersimpan otomatis pada ${timeStr}`, 'text-success');
                    setElemenStatus(idElemen, true);
                    if (data.progress) {
                        updateProgressPenilaian(data.progress);
                    }
                    if (typeof window.updateMatrixCell === 'function') {
                        window.updateMatrixCell(idElemen, formData.get('skor'));
                    }
                }
            } catch (error) {
                console.error('Auto-save error:', error);
            }
        }

        /**
         * Manual Save
         */
        async function savePenilaian(form, idElemen) {
            const formData = new FormData(form);
            const skor = formData.get('skor');
            const komentar = formData.get('komentar');

            if (!skor) {
                Swal.fire({
                    icon: 'warning'
                    , title: 'Perhatian'
                    , text: 'Mohon pilih kategori penilaian terlebih dahulu'
                });
                return;
            }

            if (!komentar.trim()) {
                Swal.fire({
                    icon: 'warning'
                    , title: 'Perhatian'
                    , text: 'Mohon berikan komentar/justifikasi penilaian'
                });
                return;
            }

            showLoading();

            try {
                const response = await fetch(`/ak/berkas/${asesmenId}/nilai`, {
                    method: 'POST'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Content-Type': 'application/json'
                        , 'Accept': 'application/json'
                    , }
                    , body: JSON.stringify({
                        id_elemen: idElemen
                        , skor: skor
                        , komentar: komentar
                    , })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success'
                        , title: 'Berhasil!'
                        , text: 'Penilaian berhasil disimpan'
                        , timer: 2000
                        , showConfirmButton: false
                    });

                    const now = new Date();
                    const timeStr = now.toLocaleString('id-ID', {
                        day: '2-digit'
                        , month: 'short'
                        , year: 'numeric'
                        , hour: '2-digit'
                        , minute: '2-digit'
                    });
                    updateSaveStatus(form, `Tersimpan pada ${timeStr}`, 'text-success');

                    const card = form.closest('.indikator-card');
                    if (card) {
                        card.classList.add('has-penilaian');

                        const badge = card.querySelector('.badge.bg-warning');
                        if (badge) {
                            badge.className = 'badge bg-success';
                            badge.innerHTML = '<i class="bi bi-check-circle"></i> Sudah Dinilai';
                        }
                    }
                    if (data.progress) {
                        updateProgressPenilaian(data.progress);
                    }
                    if (typeof window.updateMatrixCell === 'function') {
                        window.updateMatrixCell(idElemen, skor);
                    }
                    if (typeof window.updateMatrixStats === 'function') updateMatrixStats();
                } else {
                    throw new Error(data.message || 'Gagal menyimpan penilaian');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error'
                    , title: 'Error'
                    , text: error.message
                });
            } finally {
                hideLoading();
            }
        }

        function updateSaveStatus(form, text, className) {
            const statusText = form.querySelector('.status-text');
            if (statusText) {
                statusText.textContent = text;
                statusText.className = className;
            }
        }

        function updateCharCount(textarea) {
            const charCount = textarea.closest('.col-md-8').querySelector('.char-count');
            if (charCount) {
                charCount.textContent = textarea.value.length;
            }
        }

        function updateProgressPenilaian(progress) {
            const progressBar = document.getElementById('progressBarPenilaian');
            const progressPercentage = document.getElementById('progressPercentage');
            const progressCompleted = document.getElementById('progressCompleted');
            const progressTotal = document.getElementById('progressTotal');
            const progressCount = document.getElementById('progressCount');

            if (progressBar) {
                progressBar.style.width = progress.percentage + '%';
                progressBar.setAttribute('aria-valuenow', progress.percentage);
            }

            if (progressPercentage) {
                progressPercentage.textContent = progress.percentage + '%';
            }

            if (progressCompleted) {
                progressCompleted.textContent = progress.completed;
            }

            if (progressTotal) {
                progressTotal.textContent = progress.total;
            }

            if (progressCount) {
                progressCount.textContent = progress.completed + '/' + progress.total;
            }

            updateAllProgress();
        }

        function updateAllProgress() {
            document.querySelectorAll('.kriteria-progress').forEach(badge => {
                const kriteriaCard = badge.closest('.kriteria-card');
                const totalIndikators = kriteriaCard.querySelectorAll('.elemen-card').length;
                const completedIndikators = kriteriaCard.querySelectorAll('.elemen-card.has-penilaian').length;

                badge.textContent = `${completedIndikators} / ${totalIndikators}`;

                if (completedIndikators === totalIndikators && totalIndikators > 0) {
                    badge.className = 'badge bg-success kriteria-progress';
                } else if (completedIndikators > 0) {
                    badge.className = 'badge bg-warning kriteria-progress';
                } else {
                    badge.className = 'badge bg-secondary kriteria-progress';
                }
            });

            document.querySelectorAll('.elemen-progress').forEach(badge => {
                const elemenCard = badge.closest('.elemen-card');
                const totalIndikators = elemenCard.querySelectorAll('.indikator-card').length;
                const completedIndikators = elemenCard.querySelectorAll('.indikator-card.has-penilaian').length;

                badge.textContent = `${completedIndikators} / ${totalIndikators}`;

                if (completedIndikators === totalIndikators && totalIndikators > 0) {
                    badge.className = 'badge bg-success elemen-progress';
                } else if (completedIndikators > 0) {
                    badge.className = 'badge bg-warning elemen-progress';
                } else {
                    badge.className = 'badge bg-info elemen-progress';
                }
            });
        }

        function initializeScrollButton() {
            const btnScrollTop = document.getElementById('btnScrollTop');

            if (btnScrollTop) {
                window.addEventListener('scroll', function() {
                    if (window.pageYOffset > 300) {
                        btnScrollTop.style.display = 'flex';
                    } else {
                        btnScrollTop.style.display = 'none';
                    }
                });

                btnScrollTop.addEventListener('click', function() {
                    window.scrollTo({
                        top: 0
                        , behavior: 'smooth'
                    });
                });
            }
        }

        if (typeof Swal === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            document.head.appendChild(script);
        }

        /**
         * ============================================
         * EXCEL EXPORT/IMPORT HANDLERS (ENHANCED)
         * ============================================
         */

        /**
         * ============================================
         * FILE UPLOAD HANDLING
         * ============================================
         */

        // File input change handler
        const fileInput = document.getElementById('excelFile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');

        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];

                if (file) {
                    // Display file info
                    fileName.textContent = file.name;
                    fileSize.textContent = formatFileSize(file.size);
                    fileInfo.classList.remove('d-none');

                    // Remove invalid feedback
                    fileInput.classList.remove('is-invalid');

                    // Validate file type
                    const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
                    const fileExtension = file.name.split('.').pop().toLowerCase();

                    if (!validTypes.includes(file.type) && !['xlsx', 'xls'].includes(fileExtension)) {
                        showAlert('importAlert', 'danger', 'Format file tidak valid! Harus .xlsx atau .xls');
                        fileInput.value = '';
                        fileInfo.classList.add('d-none');
                        return;
                    }

                    // Validate file size (10MB max)
                    if (file.size > 10 * 1024 * 1024) {
                        showAlert('importAlert', 'danger', 'Ukuran file terlalu besar! Maksimal 10MB');
                        fileInput.value = '';
                        fileInfo.classList.add('d-none');
                        return;
                    }

                    // Hide alert if validation passed
                    const importAlert = document.getElementById('importAlert')
                    if (importAlert) importAlert.classList.add('d-none');
                } else {
                    fileInfo.classList.add('d-none');
                }
            });
        }

        /**
         * Download Template (Empty Format)
         */
        ['btnDownloadTemplate', 'btnDownloadTemplateModal'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('click', e => {
                e.preventDefault();
                downloadTemplate();
            });
        });

        function downloadTemplate() {
            showLoading();

            window.location.href = `/ak/berkas/${asesmenId}/template`;

            setTimeout(() => {
                hideLoading();
                Swal.fire({
                    icon: 'success'
                    , title: 'Download Dimulai!'
                    , text: 'Template Excel sedang didownload'
                    , timer: 2000
                    , showConfirmButton: false
                });
            }, 1000);
        }

        /**
         * Download Data (Excel with Penilaian)
         */
        function downloadDataExcel() {
            showLoading();

            window.location.href = `/ak/berkas/${asesmenId}/export`;

            setTimeout(() => {
                hideLoading();
                Swal.fire({
                    icon: 'success'
                    , title: 'Download Dimulai!'
                    , text: 'File Excel dengan penilaian Anda sedang didownload'
                    , timer: 2000
                    , showConfirmButton: false
                });
            }, 1000);
        }

        /**
         * Start polling for import status
         */
        function startImportStatusPolling() {
            // Clear any existing interval
            if (importStatusInterval) {
                clearInterval(importStatusInterval);
            }

            // Poll every 2 seconds
            importStatusInterval = setInterval(async () => {
                try {
                    const response = await fetch(`/ak/import-status/${currentImportLogId}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        const status = result.data.status;
                        const progress = result.data.total_rows > 0 ?
                            Math.round((result.data.imported_rows / result.data.total_rows) * 100) :
                            0;

                        // Update progress bar
                        const progressBar = document.getElementById('importProgressBar');
                        progressBar.style.width = progress + '%';
                        progressBar.textContent = progress + '%';

                        // Check if completed or failed
                        if (status === 'completed') {
                            clearInterval(importStatusInterval);
                            showImportResult(result.data);

                        } else if (status === 'failed') {
                            clearInterval(importStatusInterval);
                            showImportError(result.data);
                        }
                    }

                } catch (error) {
                    console.error('Error checking import status:', error);
                    clearInterval(importStatusInterval);
                }
            }, 2000);
        }

        /**
         * Show import result (success)
         */
        function showImportResult(data) {
            // Close import modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('importModal'));
            if (modal) modal.hide();

            // Prepare result content
            const resultContent = `
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-success">Import Berhasil!</h4>
                </div>

                <div class="row text-center mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-primary mb-0">${data.total_rows}</h3>
                            <small class="text-muted">Total Baris</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-success mb-0">${data.imported_rows}</h3>
                            <small class="text-muted">Berhasil</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-danger mb-0">${data.failed_rows}</h3>
                            <small class="text-muted">Gagal</small>
                        </div>
                    </div>
                </div>

                <div class="alert alert-success">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Success Rate: ${data.success_rate}%</strong>
                    <p class="mb-0 mt-2 small">
                        Waktu selesai: ${data.completed_at}
                    </p>
                </div>

                ${data.errors && data.errors.length > 0 ? `
                    <div class="alert alert-warning">
                        <strong>⚠️ Peringatan:</strong>
                        <p class="mb-2">Beberapa baris gagal diimport:</p>
                        <ul class="mb-0 small">
                            ${data.errors.slice(0, 5).map(err => `<li>${err}</li>`).join('')}
                            ${data.errors.length > 5 ? `<li><em>...dan ${data.errors.length - 5} error lainnya</em></li>` : ''}
                        </ul>
                    </div>
                ` : ''}
            `;

            document.getElementById('importResultContent').innerHTML = resultContent;

            // Show result modal
            new bootstrap.Modal(document.getElementById('importResultModal')).show();

            // Update progress counters
            updateAllProgress();
        }

        /**
         * Show import error
         */
        function showImportError(data) {
            // Close import modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('importModal'));
            if (modal) modal.hide();

            // Prepare error content
            const errorContent = `
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-danger">Import Gagal</h4>
                </div>

                <div class="alert alert-danger">
                    <strong>Error:</strong>
                    <ul class="mb-0 mt-2">
                        ${data.errors.map(err => `<li>${err}</li>`).join('')}
                    </ul>
                </div>
            `;

            document.getElementById('importResultContent').innerHTML = errorContent;
            document.getElementById('resultModalHeader').className = 'modal-header bg-danger text-white';

            // Show result modal
            new bootstrap.Modal(document.getElementById('importResultModal')).show();
        }

        /**
         * Show Import History
         */
        async function importHistoryExcel() {
            const modal = new bootstrap.Modal(document.getElementById('historyModal'));
            modal.show();

            // Load history
            try {
                const response = await fetch(`/ak/berkas/${asesmenId}/import-history`, {
                    headers: {
                        'Accept': 'application/json'
                        , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    displayHistory(data.data);
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                document.getElementById('historyContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Gagal memuat riwayat: ${error.message}
                </div>
            `;
            }
        };

        /**
         * Display import history
         */
        function displayHistory(logs) {
            const content = document.getElementById('historyContent');

            if (logs.length === 0) {
                content.innerHTML = `
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Belum ada riwayat import.
                </div>
            `;
                return;
            }

            let html = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>File</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

            logs.forEach(log => {
                const statusBadge = getStatusBadge(log.status);
                const percentage = log.total_rows > 0 ?
                    Math.round((log.imported_rows / log.total_rows) * 100) :
                    0;

                html += `
                <tr>
                    <td>
                        <small>${formatDateTime(log.created_at)}</small>
                    </td>
                    <td>
                        <i class="bi bi-file-earmark-excel text-success"></i>
                        ${log.filename}
                    </td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="progress" style="height: 20px; width: 150px;">
                            <div class="progress-bar ${getProgressBarClass(log.status)}"
                                 style="width: ${percentage}%">
                                ${percentage}%
                            </div>
                        </div>
                        <small class="text-muted">
                            ${log.imported_rows}/${log.total_rows} baris
                        </small>
                    </td>
                    <td>
                        ${log.status === 'failed' && log.errors
                            ? `<small class="text-danger">${log.errors}</small>`
                            : '-'}
                    </td>
                </tr>
            `;
            });

            html += `
                    </tbody>
                </table>
            </div>
        `;

            content.innerHTML = html;
        }

        /**
         * Get status badge HTML
         */
        function getStatusBadge(status) {
            const badges = {
                'queued': '<span class="badge bg-secondary"><i class="bi bi-clock"></i> Queued</span>'
                , 'processing': '<span class="badge bg-info"><i class="bi bi-arrow-repeat"></i> Processing</span>'
                , 'completed': '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Completed</span>'
                , 'failed': '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Failed</span>'
            };

            return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
        }

        /**
         * Get progress bar HTML
         */
        function getProgressBar(log) {
            if (log.total_rows === 0) {
                return '<small class="text-muted">0%</small>';
            }

            const percent = Math.round((log.imported_rows / log.total_rows) * 100);
            const colorClass = percent === 100 ? 'bg-success' : percent > 50 ? 'bg-info' : 'bg-warning';

            return `
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar ${colorClass}" role="progressbar"
                        style="width: ${percent}%" aria-valuenow="${percent}"
                        aria-valuemin="0" aria-valuemax="100">
                        ${percent}%
                    </div>
                </div>
            `;
        }

        /**
         * Show import errors (global function for onclick)
         */
        window.showImportErrors = function(errors) {
            Swal.fire({
                icon: 'warning'
                , title: 'Import Errors'
                , html: `
                    <div class="text-start">
                        <ul class="mb-0">
                            ${errors.map(err => `<li>${err}</li>`).join('')}
                        </ul>
                    </div>
                `
                , width: '600px'
            });
        };

        /**
         * Clean up on page unload
         */
        window.addEventListener('beforeunload', function() {
            if (importStatusInterval) {
                clearInterval(importStatusInterval);
            }
        });

        /**
         * ============================================
         * DISABLE EDITING IF SUBMITTED
         * ============================================
         */
        if (checkStatusPekerjaan) {
            // Disable all form inputs if already submitted
            document.querySelectorAll('.form-penilaian').forEach(form => {
                form.querySelectorAll('select, textarea, button[type="submit"]').forEach(el => {
                    el.disabled = true;
                });

                // Add info message
                const infoDiv = document.createElement('div');
                infoDiv.className = 'alert alert-info mt-2';
                infoDiv.innerHTML = '<i class="bi bi-info-circle"></i> Penilaian sudah di-submit, tidak bisa diedit.';
                form.appendChild(infoDiv);
            });

            // Disable import if submitted
            const btnImport = document.getElementById('btnImport');
            if (btnImport) btnImport.setAttribute('disabled', 'disabled');
        }

        /**
         * ============================================
         * UPDATE PROGRESS
         * ============================================
         */

        function updateProgressImport(log) {
            const percentage = log.total_rows > 0 ?
                Math.round((log.imported_rows / log.total_rows) * 100) :
                0;

            // Update progress bar
            const progressBar = document.getElementById('progressBarImport');
            progressBar.style.width = percentage + '%';
            progressBar.textContent = percentage + '%';

            // Update text
            document.getElementById('progressText').textContent = percentage + '%';
            document.getElementById('importedRows').textContent = log.imported_rows;
            document.getElementById('totalRows').textContent = log.total_rows;

            // Change color based on status
            if (log.status === 'completed') {
                progressBar.classList.remove('bg-warning', 'bg-danger');
                progressBar.classList.add('bg-success');
            } else if (log.status === 'failed') {
                progressBar.classList.remove('bg-warning', 'bg-success');
                progressBar.classList.add('bg-danger');
            }
        }

        /**
         * ============================================
         * SHOW RESULT
         * ============================================
         */

        function showResult(log) {
            // Hide import modal
            const importModal = bootstrap.Modal.getInstance(document.getElementById('importModal'));
            if (importModal) {
                importModal.hide();
            }

            // Reset import form
            setTimeout(() => {
                document.getElementById('importForm').reset();
                document.getElementById('fileInfo').classList.add('d-none');
                document.getElementById('importProgress').classList.add('d-none');
                const importAlert = document.getElementById('importAlert')
                if (importAlert) importAlert.classList.add('d-none');

                const btnSubmit = document.getElementById('btnSubmitImport');
                const btnClose = document.getElementById('btnCloseImport');
                btnSubmit.disabled = false;
                btnClose.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-upload"></i> Upload & Import';
            }, 500);

            // Show result modal
            const resultModal = new bootstrap.Modal(document.getElementById('resultModal'));

            if (log.status === 'completed') {
                // Success
                document.getElementById('successResult').classList.remove('d-none');
                document.getElementById('errorResult').classList.add('d-none');

                document.getElementById('resultTotal').textContent = log.total_rows;
                document.getElementById('resultImported').textContent = log.imported_rows;
                document.getElementById('resultFailed').textContent = log.failed_rows;

                document.getElementById('resultHeader').className = 'modal-header bg-success text-white';
                document.getElementById('resultModalLabel').innerHTML = '<i class="bi bi-check-circle"></i> Import Berhasil';
            } else {
                // Failed
                document.getElementById('successResult').classList.add('d-none');
                document.getElementById('errorResult').classList.remove('d-none');

                document.getElementById('errorMessage').textContent = log.errors || 'Import gagal. Silakan coba lagi.';

                // Show error list if available
                if (log.errors && typeof log.errors === 'object') {
                    const errorList = document.getElementById('errorList');
                    errorList.innerHTML = '';

                    Object.values(log.errors).forEach(error => {
                        const li = document.createElement('li');
                        li.textContent = error;
                        errorList.appendChild(li);
                    });

                    document.getElementById('errorListContainer').classList.remove('d-none');
                }

                document.getElementById('resultHeader').className = 'modal-header bg-danger text-white';
                document.getElementById('resultModalLabel').innerHTML = '<i class="bi bi-x-circle"></i> Import Gagal';
            }

            resultModal.show();
        }

        /**
         * ============================================
         * UTILITY FUNCTIONS
         * ============================================
         */

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        function formatDateTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('id-ID', {
                day: '2-digit'
                , month: 'short'
                , year: 'numeric'
                , hour: '2-digit'
                , minute: '2-digit'
            });
        }

        function getStatusBadge(status) {
            const badges = {
                'queued': '<span class="badge bg-secondary">Antrian</span>'
                , 'processing': '<span class="badge bg-info">Memproses</span>'
                , 'completed': '<span class="badge bg-success">Selesai</span>'
                , 'failed': '<span class="badge bg-danger">Gagal</span>'
            };
            return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
        }

        function getProgressBarClass(status) {
            const classes = {
                'completed': 'bg-success'
                , 'failed': 'bg-danger'
                , 'processing': 'bg-info'
                , 'queued': 'bg-secondary'
            };
            return classes[status] || 'bg-secondary';
        }

        function showAlert(elementId, type, message) {
            const alert = document.getElementById(elementId);
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `<i class="bi bi-${type === 'danger' ? 'exclamation-triangle' : 'info-circle'}"></i> ${message}`;
            alert.classList.remove('d-none');
        }

        function showToast(message, type = 'info') {
            // Simple toast - can be replaced with Bootstrap toast
            Swal.fire({
                toast: true
                , position: 'top-end'
                , icon: type
                , title: message
                , showConfirmButton: false
                , timer: 3000
                , timerProgressBar: true
            });
        }

        /**
         * ============================================
         * JAVASCRIPT: RESET ALL PENILAIAN
         * ============================================

        /**
         * Reset All Penilaian dengan konfirmasi
         */
        async function resetAllPenilaian() {
            // Get current progress
            const completed = parseInt(document.getElementById('summaryCompleted').textContent);
            const total = parseInt(document.getElementById('summaryTotal').textContent);

            // Show warning if no penilaian yet
            if (completed === 0) {
                await Swal.fire({
                    icon: 'info'
                    , title: 'Tidak Ada Penilaian'
                    , text: 'Belum ada penilaian yang dibuat untuk direset.'
                    , confirmButtonColor: '#932136'
                });
                return;
            }

            // First confirmation - Warning
            const firstConfirm = await Swal.fire({
                icon: 'warning'
                , title: '⚠️ Reset Semua Penilaian?'
                , html: `
            <div class="text-start">
                <p><strong>PERHATIAN:</strong> Anda akan menghapus <strong class="text-danger">${completed} penilaian</strong> yang sudah dibuat!</p>

                <div class="alert alert-danger mt-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Tindakan ini TIDAK DAPAT dibatalkan!</strong>
                </div>

                <p class="mb-0">Pastikan Anda benar-benar ingin melakukan ini.</p>
            </div>
        `
                , showCancelButton: true
                , confirmButtonText: 'Ya, Saya Mengerti'
                , cancelButtonText: 'Batal'
                , confirmButtonColor: '#ff9800'
                , cancelButtonColor: '#6c757d'
                , reverseButtons: true
                , width: '600px'
            });

            if (!firstConfirm.isConfirmed) {
                return;
            }

            // Second confirmation - Final warning
            const secondConfirm = await Swal.fire({
                icon: 'error'
                , title: '🚨 KONFIRMASI TERAKHIR'
                , html: `
            <div class="text-start">
                <p class="text-danger"><strong>Ini adalah konfirmasi terakhir!</strong></p>

                <p>Setelah Anda klik "YA, HAPUS SEMUA", seluruh penilaian akan dihapus permanen:</p>

                <ul class="text-danger">
                    <li><strong>${completed} penilaian</strong> akan dihapus</li>
                    <li>Progress akan kembali ke <strong>0%</strong></li>
                    <li>Semua skor dan komentar akan hilang</li>
                    <li><strong>Data tidak dapat dikembalikan</strong></li>
                </ul>

                <div class="alert alert-warning mt-3">
                    <i class="bi bi-lightbulb me-2"></i>
                    <strong>Tips:</strong> Jika Anda hanya ingin edit beberapa penilaian saja,
                    lebih baik gunakan tombol "Reset" pada form penilaian individual.
                </div>
            </div>
        `
                , input: 'checkbox'
                , inputValue: 0
                , inputPlaceholder: 'Saya memahami konsekuensinya dan ingin melanjutkan'
                , confirmButtonText: 'YA, HAPUS SEMUA'
                , cancelButtonText: 'Batalkan'
                , confirmButtonColor: '#f44336'
                , cancelButtonColor: '#6c757d'
                , reverseButtons: true
                , width: '700px'
                , inputValidator: (result) => {
                    return !result && 'Anda harus mencentang checkbox untuk melanjutkan!';
                }
            });

            if (!secondConfirm.isConfirmed) {
                return;
            }

            // Show loading
            showLoading();

            try {
                const response = await fetch(`/ak/berkas/${asesmenId}/reset-all`, {
                    method: 'DELETE'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                        , 'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                hideLoading();

                if (data.success) {
                    // Show success message
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Reset Berhasil!'
                        , html: `
                    <div class="text-center">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        <p class="mt-3">${data.message}</p>
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle"></i>
                            <strong>${data.deleted_count} penilaian</strong> telah dihapus.
                            Progress kembali ke 0%.
                        </div>
                    </div>
                `
                        , confirmButtonText: 'OK'
                        , confirmButtonColor: '#28a745'
                    });

                    // Reload page to refresh all data
                    window.location.reload();

                } else {
                    throw new Error(data.message || 'Gagal mereset penilaian');
                }

            } catch (error) {
                hideLoading();
                console.error('Reset error:', error);

                Swal.fire({
                    icon: 'error'
                    , title: 'Gagal Reset'
                    , text: error.message
                    , confirmButtonColor: '#d33'
                });
            }
        }
    });

</script>
@endpush

@endsection
