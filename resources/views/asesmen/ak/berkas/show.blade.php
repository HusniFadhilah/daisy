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
        width: 3rem;
        height: 3rem;
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
                            <div class="progress-bar bg-gradient" role="progressbar" id="progressBar" style="width: {{ $progress['percentage'] }}%" aria-valuenow="{{ $progress['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
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
            <!-- ========== ACTION BUTTONS BAR (NEW) ========== -->
            <div class="row align-items-center my-2">
                <div class="col-12 mb-md-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-success" id="btnSubmit">
                        <i class="bi bi-check-circle"></i> Finalisasi dan Kirim
                    </button>
                    <div class="btn-action-group">
                        <button type="button" class="btn btn-primary" id="btnExport">
                            <i class="bi bi-download"></i> Download File Excel
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="btnImport">
                            <i class="bi bi-upload"></i> Upload File Excel
                        </button>
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

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-upload"></i> Import Penilaian dari Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="importForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih File Excel:</label>
                        <input type="file" class="form-control" id="fileImport" accept=".xlsx,.xls" required>
                        <small class="text-muted">Format: .xlsx atau .xls</small>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Catatan:</strong>
                        <ul class="mb-0 mt-2 small">
                            <li>Download template terlebih dahulu</li>
                            <li>Isi sesuai format yang disediakan</li>
                            <li>Kolom: Kode Indikator, Skor, Komentar</li>
                        </ul>
                    </div>

                    <div class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-success" id="btnDownloadTemplate">
                            <i class="bi bi-download"></i> Download Template
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const asesmenId = "{{ $asesmen->id }}";
        let saveTimeout;
        const AUTO_SAVE_DELAY = 2000;

        // Loading Overlay
        const loadingOverlay = createLoadingOverlay();
        const toggleBtn = document.getElementById('toggleAllAccordion');
        if (!toggleBtn) return;

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

            // Submit Penilaian
            document.getElementById('btnSubmit').addEventListener('click', submitPenilaian);

            // Export Excel
            document.getElementById('btnExport').addEventListener('click', exportExcel);

            // Import Excel
            document.getElementById('btnImport').addEventListener('click', function() {
                new bootstrap.Modal(document.getElementById('importModal')).show();
            });

            // Import Form Submit
            document.getElementById('importForm').addEventListener('submit', importExcel);

            // Download Template
            document.getElementById('btnDownloadTemplate').addEventListener('click', downloadTemplate);
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
            const completed = document.querySelectorAll('.indikator-card.has-penilaian').length;
            const total = document.querySelectorAll('.indikator-card').length;

            if (completed < total) {
                const confirmed = await Swal.fire({
                    icon: 'warning'
                    , title: 'Penilaian Belum Lengkap'
                    , html: `Anda baru menilai <strong>${completed} dari ${total}</strong> indikator.<br>Apakah Anda yakin ingin submit?`
                    , showCancelButton: true
                    , confirmButtonText: 'Ya, Submit'
                    , cancelButtonText: 'Batal'
                    , confirmButtonColor: '#4caf50'
                    , cancelButtonColor: '#6c757d'
                });

                if (!confirmed.isConfirmed) return;
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

                if (data.success) {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Berhasil!'
                        , text: 'Penilaian berhasil di-submit'
                        , timer: 2000
                        , showConfirmButton: false
                    });

                    // Redirect or reload
                    window.location.href = "{{ route('ak.berkas') }}";
                } else {
                    throw new Error(data.message || 'Gagal submit penilaian');
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
            e.preventDefault();

            const fileInput = document.getElementById('fileImport');
            const file = fileInput.files[0];

            if (!file) {
                Swal.fire({
                    icon: 'warning'
                    , title: 'File Belum Dipilih'
                    , text: 'Mohon pilih file Excel terlebih dahulu'
                });
                return;
            }

            const formData = new FormData();
            formData.append('file', file);

            showLoading();
            bootstrap.Modal.getInstance(document.getElementById('importModal')).hide();

            try {
                const response = await fetch(`/ak/berkas/${asesmenId}/import`, {
                    method: 'POST'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                    }
                    , body: formData
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Import Berhasil!'
                        , html: `<strong>${data.imported}</strong> penilaian berhasil diimport`
                        , timer: 2000
                        , showConfirmButton: false
                    });

                    location.reload(); // Reload to show imported data
                } else {
                    throw new Error(data.message || 'Gagal import Excel');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error'
                    , title: 'Error'
                    , text: error.message
                });
            } finally {
                hideLoading();
                fileInput.value = ''; // Reset file input
            }
        }

        /**
         * Download Template
         */
        function downloadTemplate() {
            window.location.href = `/ak/berkas/${asesmenId}/template`;

            Swal.fire({
                icon: 'info'
                , title: 'Download Template'
                , text: 'Template sedang didownload'
                , timer: 1500
                , showConfirmButton: false
            });
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
                        updateProgress(data.progress);
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
                        updateProgress(data.progress);
                    }
                    if (typeof window.updateMatrixCell === 'function') {
                        window.updateMatrixCell(idElemen, skor);
                    }
                    if (typeof window.updateMatrixCell === 'function') {
                        window.updateMatrixCell(idElemen, skor);
                    }
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

        function updateProgress(progress) {
            const progressBar = document.getElementById('progressBar');
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
    });

</script>
@endpush

@endsection
