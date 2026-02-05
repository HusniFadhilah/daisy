@extends('layouts.template.app')

@section('title', 'Penilaian AL - ' . $asesmen->name)

@push('styles')
<style>
    .highlight-revision {
        animation: pulseRevision 1s ease-in-out 3;
        border: 2px solid #ff9800 !important;
    }

    @keyframes pulseRevision {

        0%,
        100% {
            background-color: #fff3e0;
            transform: scale(1);
        }

        50% {
            background-color: #ffe0b2;
            transform: scale(1.01);
        }
    }

    /* Panduan Penilaian Table */
    .panduan-penilaian-wrapper .table {
        font-size: 13px;
    }

    .panduan-penilaian-wrapper .table td {
        padding: 12px;
        vertical-align: top;
    }

    .panduan-penilaian-wrapper .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .penilaian-desc {
        line-height: 1.6;
        white-space: pre-line;
    }

    /* Badge skor dalam tabel */
    .panduan-penilaian-wrapper .badge {
        font-weight: 700;
        min-width: 50px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .panduan-penilaian-wrapper .table {
            font-size: 11px;
        }

        .panduan-penilaian-wrapper .badge {
            font-size: 12px !important;
            padding: 4px 8px !important;
        }
    }

    .step-circle {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        border: 2px solid #ced4da;
        color: #6c757d;
        background: #fff;
    }

    .step-circle.active {
        border-color: #0d6efd;
        color: #0d6efd;
        background: #e7f1ff;
    }

    .step-circle.done {
        border-color: #198754;
        color: #198754;
        background: #eaf7ef;
    }

    .step-circle.inactive {
        border-color: #ced4da;
        color: #6c757d;
        background: #fff;
    }

    .step-line {
        flex: 1;
        min-width: 60px;
        height: 2px;
        background: #dee2e6;
    }

</style>
@endpush

@php
$statusPekerjaan = $assignment->status_pekerjaan ?? 'not_started';
$isSubmittedOnly = $statusPekerjaan === 'submitted';
$isSubmitted = isset($assignment) && in_array($statusPekerjaan, ['submitted', 'approved', 'validated']);
$isApproved = $statusPekerjaan === 'approved';
$needsRevision = $statusPekerjaan === 'revision_required';
$isComplete = $progress['percentage'] == 100;
@endphp

@section('content')
<div class="container-fluid py-3">
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

                {{-- STEP 1 --}}
                <a href="{{ route('al.berkas.show', ['idAsesmen' => $asesmen->id, 'step' => 1]) }}" class="text-decoration-none d-flex align-items-center gap-2">
                    <span class="step-circle {{ $step === 1 ? 'active' : 'done' }}">1</span>
                    <div>
                        <div class="fw-bold {{ $step === 1 ? '' : 'text-muted' }}">Penilaian Asesmen Lapangan</div>
                        {{-- <small class="text-muted">Isi kategori & justifikasi per elemen</small> --}}
                    </div>
                </a>

                <div class="step-line"></div>

                {{-- STEP 2 --}}
                <a href="{{ route('al.berkas.show', ['idAsesmen' => $asesmen->id, 'step' => 2]) }}" class="text-decoration-none d-flex align-items-center gap-2 {{ !($isSubmittedOnly && !$isApproved) ? 'disabled-link' : '' }}" {{ !($isSubmittedOnly && !$isApproved) ? 'disabled' : '' }}>
                    <span class="step-circle {{ $step === 2 ? 'active' : 'inactive' }}">2</span>
                    <div>
                        <div class="fw-bold {{ $step === 2 ? '' : 'text-muted' }}">Hasil dan Berita Acara Asesmen Lapangan (AL)</div>
                        {{-- <small class="text-muted">Upload BA dan Download PDF laporan</small> --}}
                    </div>
                </a>

            </div>
        </div>
    </div>

    @if($step === 1)
    <!-- Header Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Penilaian Asesmen Lapangan</h3>
                    <p class="text-muted mb-0">{{ $asesmen->name }}</p>
                </div>
                <a href="{{ route('al.berkas') }}" class="btn btn-outline-secondary">
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
                            <div class="progress-bar bg-gradient bg-success" role="progressbar" id="progressBarPenilaian" style="width: {{ $progress['percentage'] }}%" aria-valuenow="{{ $progress['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
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
                    @if(!$isSubmittedOnly && !$isApproved && $isComplete)
                    <div class="alert alert-warning alert-dismissible alert-permanent mb-3" id="alertSubmitReminder">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="alert-heading mb-2">
                                    <i class="bi bi-check-circle"></i> Penilaian Telah Lengkap!
                                </h5>
                                <p class="mb-2">
                                    Anda telah menyelesaikan <strong>semua {{ $progress['total'] }} elemen penilaian</strong>.
                                    Segera lakukan <strong>Finalisasi dan Kirim</strong> agar penilaian Anda dapat divalidasi oleh LAMDEPILAR.
                                </p>
                                <hr>
                                <div class="mb-0">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i> Penilaian belum akan tersimpan secara permanen sampai di-submit
                                    </small>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if(!$isSubmittedOnly && !$isApproved && !$isComplete && $progress['percentage'] > 0)
                    <div class="alert alert-info alert-dismissible alert-permanent mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Progress Penilaian:</strong>
                        Anda telah menilai {{ $progress['completed'] }} dari {{ $progress['total'] }} elemen
                        (<strong>{{ $progress['percentage'] }}%</strong>).
                        Selesaikan <strong>{{ $progress['remaining'] }} elemen</strong> lagi untuk dapat melakukan finalisasi.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if($isSubmittedOnly && !$isApproved)
                    <div class="alert alert-info alert-permanent alert-dismissible mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Telah Di-Submit!</strong> Penilaian Anda sedang menunggu validasi dari LAMDEPILAR.
                        @if(app()->environment('local'))
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2 mt-2" id="btnUnsubmit">
                            <i class="bi bi-arrow-counterclockwise"></i> Batalkan Submit
                        </button>
                        @endif
                    </div>
                    @endif

                    @if($isApproved)
                    <div class="alert alert-success alert-permanent alert-dismissible mb-3">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Penilaian Disetujui!</strong> Penilaian Anda pada tahap Asesmen Lapangan (AL) telah divalidasi dan disetujui oleh LAMDEPILAR. Silahkan unduh file Hasil penilaian lengkap di <a href="{{ route('al.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'personal']) }}" class="alert-link">link ini</a>. Tanda tangani, lalu upload ulang di step ke-2 (Hasil dan berita acara Asesmen Lapangan) di halaman <a href="{{ route('al.berkas.show', ['idAsesmen' => $asesmen->id, 'step' => '2']) }}" class="alert-link">berikut ini</a>.
                    </div>
                    @endif

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">

                        <!-- Finalisasi -->
                        <div>
                            @if(!$isSubmittedOnly && !$isApproved)
                            <button class="btn btn-success w-md-100 w-md-auto" id="btnSubmit">
                                <i class="bi bi-check-circle"></i> Finalisasi dan Kirim
                            </button>

                            <small class="d-block text-muted mt-1">
                                <i class="bi bi-info-circle"></i>
                                Pastikan semua elemen telah dinilai sebelum mengirim
                            </small>

                            @elseif($isSubmittedOnly)
                            <button class="btn btn-secondary w-100 w-md-auto" disabled>
                                <i class="bi bi-clock-history"></i> Menunggu Validasi
                            </button>

                            @else
                            <button class="btn btn-success w-100 w-md-auto" disabled>
                                <i class="bi bi-check-all"></i> Penilaian Disetujui
                            </button>
                            @endif
                        </div>

                        <!-- Excel Buttons -->
                        <div class="btn-group flex-wrap w-md-100 w-md-auto">

                            <!-- Download -->
                            <button class="btn btn-primary dropdown-toggle flex-grow-1 flex-md-grow-0" data-bs-toggle="dropdown">
                                <i class="bi bi-download"></i> Download Excel
                            </button>

                            <ul class="dropdown-menu">
                                <li class="dropdown-header">
                                    <i class="bi bi-file-earmark-excel"></i> Pilih Jenis Excel
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                <!-- Download Template -->
                                <li>
                                    <a class="dropdown-item" href="{{ route('al.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'template']) }}" id="btnDownloadTemplate">
                                        <i class="bi bi-file-earmark-text text-info"></i> Download Template
                                        <small class="d-block text-muted">Format Excel sebagai template</small>
                                    </a>
                                </li>

                                <!-- Hasil Penilaian - Lengkap -->
                                <li>
                                    <a class="dropdown-item btnDownloadData" data-mode="full" href="{{ route('al.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'full']) }}">
                                        <i class="bi bi-file-earmark-spreadsheet text-primary"></i> Hasil Penilaian Lengkap
                                        <small class="d-block text-muted">Menu + Kertas Kerja + Semua Asesor</small>
                                    </a>
                                </li>

                                <!-- Hasil Penilaian - Personal -->
                                <li>
                                    <a class="dropdown-item btnDownloadData" data-mode="personal" href="{{ route('al.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'personal']) }}">
                                        <i class="bi bi-person-check text-success"></i> Hasil Penilaian Anda
                                        <small class="d-block text-muted">Hanya Sheet Penilaian Anda</small>
                                    </a>
                                </li>
                            </ul>

                            <!-- Upload -->
                            <button class="btn btn-outline-primary" id="btnImport" {{ $isSubmittedOnly || $isApproved ? 'disabled' : '' }}>
                                <i class="bi bi-upload"></i> Upload Excel
                            </button>

                            <!-- History -->
                            <button class="btn btn-outline-secondary" id="btnImportHistory">
                                <i class="bi bi-clock-history"></i>
                            </button>

                            <!-- Reset -->
                            <button class="btn btn-outline-danger" id="btnResetAll" {{ $isSubmittedOnly || $isApproved ? 'disabled' : '' }}>
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
                                <small class="text-muted">Telah Dinilai</small>
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

    @if(isset($asesmen->pengajuan))
    @include('asesmen.ak.components.documents')
    @endif
    <!-- ========== HEATMAP MATRIX (ENHANCED) ========== -->
    @include('asesmen.al.components.heatmap-matrix')

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white border-bottom py-2">
            <div class="d-flex justify-content-between align-items-center">
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
                    <li>Gunakan <strong>tombol Finalisasi & Kirim</strong> untuk submit penilaian, <strong>tombol Download Excel</strong> untuk mengunduh template atau hasil penilaian dalam format excel, serta <strong>tombol Upload Excel</strong> untuk mengupload penilaian excel serta menyimpannya ke sistem</li>
                    <li>Klik <strong>Expand/Collapse All</strong> untuk membuka/menutup semua form elemen penilaian</li>
                    <li>Klik <strong>sel di matriks visualisasi penilaian</strong> untuk langsung membuka elemen penilaian dan menilai elemen tersebut</li>
                    <li>Pilih kategori penilaian:
                        @foreach ($jenjangs as $jenjang)
                        <span class="badge text-wrap text-break" style="background:{{ $jenjang->color }}; color: {{ \App\Models\JenjangPenilaian::textColorByBg($jenjang->color) }}">
                            {{ $jenjang->skor }} - {{ $jenjang->name }}
                        </span>
                        @endforeach
                    </li>
                    <li>Penilaian akan <strong>otomatis tersimpan</strong> setelah Anda mengisi kategori penilaian dan komentar/justifikasi penilaian</li>
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
            <div class="card-header kriteria-header" id="heading-kriteria-{{ $kriteria->id }}">
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-link kriteria-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-kriteria-{{ $kriteria->id }}" aria-expanded="false" aria-controls="collapse-kriteria-{{ $kriteria->id }}">
                        <i class="bi bi-chevron-right me-2 chevron-icon"></i>
                        <strong>{{ $kriteria->kode_kriteria }}:</strong> {{ $kriteria->nama_kriteria }}
                    </button>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge bg-secondary kriteria-progress" data-kriteria-id="{{ $kriteria->id }}">
                            0 / {{ $kriteria->elemenStandar->count() }}
                        </span>
                        <button type="button" class="btn btn-sm btn-light" onclick="toggleKriteriaAccordion({{ $kriteria->id }})" title="Expand/Collapse Semua Pernyataan Standar">
                            <i class="bi bi-arrows-expand"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Kriteria Body -->
            <div id="collapse-kriteria-{{ $kriteria->id }}" class="accordion-collapse collapse kriteria-collapse" aria-labelledby="heading-kriteria-{{ $kriteria->id }}" data-bs-parent="#accordionKriteria">
                <div class="card-body">
                    @if($kriteria->keterangan)
                    <div class="alert alert-light alert-permanent alert-dismissible mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ $kriteria->keterangan }}
                    </div>
                    @endif

                    <!-- Nested Accordion per Pernyataan Standar (Elemen) -->
                    <div class="accordion accordion-elemen" id="accordionElemen-{{ $kriteria->id }}">
                        @foreach($kriteria->elemenStandar as $elemenIndex => $elemen)
                        @php
                        // Get penilaian for this elemen (not indikator!)
                        $penilaianElemenAl = $elemen->penilaianElemenAl->first(); // Assuming relation exists
                        $hasPenilaian = $penilaianElemenAl && $penilaianElemenAl->skor !== null;
                        $needsRevisionElemen = $penilaianElemenAl && $penilaianElemenAl->status_validasi === 'revision_required';
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

                        <div class="card mb-3 elemen-card @if($hasPenilaian) has-penilaian @endif" data-elemen-id="{{ $elemen->id }}">
                            <!-- Pernyataan Standar Header -->
                            <div class="card-header elemen-header" id="heading-elemen-{{ $elemen->id }}">
                                <div class="d-md-flex justify-content-between align-items-center">
                                    <button class="btn btn-link elemen-btn collapsed d-flex flex-column flex-md-row align-items-start align-items-md-center w-100 gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-elemen-{{ $elemen->id }}" aria-expanded="false" aria-controls="collapse-elemen-{{ $elemen->id }}">

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
                                                <i class="bi bi-check-circle"></i> Telah Dinilai
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
                            <div id="collapse-elemen-{{ $elemen->id }}" class="accordion-collapse collapse elemen-collapse" aria-labelledby="heading-elemen-{{ $elemen->id }}" data-bs-parent="#accordionElemen-{{ $kriteria->id }}">
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
                                    <div class="alert alert-info alert-permanent alert-dismissible">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <small>
                                            <strong>Catatan:</strong> Indikator di bawah ini adalah panduan untuk menilai pernyataan standar di atas.
                                            Pertimbangkan seluruh indikator dalam memberikan penilaian dan justifikasi.
                                        </small>
                                    </div>
                                    @else
                                    <div class="alert alert-secondary alert-permanent alert-dismissible">
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
                                                        {{-- {{ nl2br(e(str_replace("\r\n", "\n",$indikator->deskripsi_indikator))) }} --}}
                                                        {!! nl2br(e(str_replace("\r\n", "\n",$indikator->deskripsi_indikator))) !!}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                @if($elemen->indikatorPenilaian && $elemen->indikatorPenilaian->count() > 0)
                                @php
                                // Group berdasarkan jenjang
                                $grouped = $elemen->indikatorPenilaian->groupBy('id_jenjang_penilaian');

                                // Ambil daftar jenjang (urut berdasarkan skor)
                                $jenjangList = $grouped
                                ->map(function ($items) {
                                return $items->first()->jenjangPenilaian; // object jenjang
                                })
                                ->sortBy('skor')
                                ->values();
                                @endphp

                                <div class="panduan-penilaian-wrapper mb-4">
                                    <div class="card border-info">
                                        <div class="card-header bg-success bg-opacity-10 d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                                            <h6 class="mb-0">
                                                <i class="bi bi-table me-2"></i>
                                                <strong>📊 Panduan Penilaian per Kategori</strong>
                                            </h6>

                                            {{-- optional: collapse --}}
                                            <button class="btn btn-sm btn-outline-dark ms-md-auto align-self-md-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePanduanTable{{ $elemen->id }}" aria-expanded="false" aria-controls="collapsePanduanTable{{ $elemen->id }}">
                                                <i class="bi bi-chevron-down"></i> Tampilkan
                                            </button>
                                        </div>

                                        <div id="collapsePanduanTable{{ $elemen->id }}" class="collapse">
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered align-top text-start mb-0 panduan-table-auto">
                                                        <thead class="table-success text-center">
                                                            <tr>
                                                                @foreach($jenjangList as $jenjang)
                                                                <th>{{ $jenjang->name }}</th>
                                                                @endforeach
                                                            </tr>

                                                            <tr>
                                                                @foreach($jenjangList as $jenjang)
                                                                <th class="text-center">
                                                                    <span class="badge" style="background: {{ $jenjang->color }}; color: {{ \App\Models\JenjangPenilaian::textColorByBg($jenjang->color) }}">
                                                                        {{ $jenjang->skor }}
                                                                    </span>
                                                                </th>
                                                                @endforeach
                                                            </tr>
                                                        </thead>

                                                        <tbody>
                                                            {{-- Baris 3: Isi indikator per jenjang (menyamping) --}}
                                                            <tr>
                                                                @foreach($jenjangList as $jenjang)
                                                                @php
                                                                $items = $grouped->get($jenjang->id, collect());
                                                                @endphp

                                                                <td>
                                                                    @forelse($items as $item)
                                                                    <div class="mb-2">
                                                                        {!! nl2br(e(str_replace("\r\n", "\n", $item->deskripsi_penilaian))) !!}
                                                                        @if($item->keterangan)
                                                                        <div class="alert alert-secondary alert-permanent mt-2 mb-0 p-2">
                                                                            <small>
                                                                                <i class="bi bi-lightbulb"></i>
                                                                                <strong>Catatan:</strong> {{ $item->keterangan }}
                                                                            </small>
                                                                        </div>
                                                                        @endif
                                                                    </div>
                                                                    @empty
                                                                    <em class="text-muted">Belum ada indikator pada jenjang ini.</em>
                                                                    @endforelse
                                                                </td>
                                                                @endforeach
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- FORM PENILAIAN (PER ELEMEN) -->
                                <div class="penilaian-form-wrapper mb-4">
                                    <div class="card border-{{ $needsRevisionElemen ? 'warning' : ($hasPenilaian ? 'success' : 'warning') }}">
                                        <div class="card-header bg-{{ $needsRevisionElemen ? 'warning' : ($hasPenilaian ? 'success' : 'warning') }} bg-opacity-10">
                                            <h6 class="mb-0">
                                                <i class="bi bi-clipboard-check me-2"></i>
                                                <strong>Penilaian Elemen</strong>

                                                @if($needsRevisionElemen)
                                                <span class="badge bg-warning text-dark float-end">
                                                    <i class="bi bi-exclamation-triangle"></i> Perlu Revisi
                                                </span>
                                                @endif
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            {{-- Alert Revisi --}}
                                            @if($needsRevisionElemen)
                                            @php
                                            $preferensiSkor = $penilaianElemenAl->preferensi_skor;
                                            @endphp
                                            <div class="alert alert-warning alert-permanent alert-dismissible mb-3">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <h6 class="alert-heading">
                                                            <i class="bi bi-chat-left-quote"></i> Catatan Validator:
                                                        </h6>
                                                        <p class="mb-2"><strong>"{{ $penilaianElemenAl->catatan_validator }}"</strong></p>
                                                    </div>

                                                    {{-- ✅ SKOR FINAL VALIDATOR --}}
                                                    @if($preferensiSkor)
                                                    <div class="col-md-4">
                                                        <div class="card border-primary bg-light">
                                                            <div class="card-body p-3 text-center">
                                                                <small class="text-muted d-block mb-2">
                                                                    <i class="bi bi-star-fill"></i> Preferensi Kategori oleh Validator:
                                                                </small>
                                                                <div class="skor-validator-display mb-2">
                                                                    <span class="badge" style="font-size: 1.5rem; padding: 0.75rem 1.25rem; background: {{ \App\Models\JenjangPenilaian::getSkorColor($preferensiSkor) }}">
                                                                        <strong>{{ $preferensiSkor }}</strong>
                                                                    </span>
                                                                </div>

                                                                <small class="text-muted">{{ \App\Models\JenjangPenilaian::getSkorLabelAttribute($preferensiSkor) }}</small>
                                                                {{-- Quick Action Button --}}
                                                                <button type="button" class="btn btn-sm btn-primary w-100 mt-2 btn-use-validator-score" data-skor="{{ $preferensiSkor }}" data-elemen-id="{{ $elemen->id }}" title="Gunakan kategori penilaian yang direkomendasikan validator">
                                                                    <i class="bi bi-lightning-charge"></i> Gunakan Kategori Ini
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>

                                                <hr>

                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <small class="text-muted">
                                                            <i class="bi bi-person"></i> <strong>Validator:</strong> {{ $penilaianElemenAl->validator->name ?? 'N/A' }}
                                                        </small>
                                                    </div>
                                                    <div class="col-md-6 text-end">
                                                        <small class="text-muted">
                                                            <i class="bi bi-clock"></i> <strong>Tanggal:</strong> {{ \App\Libraries\Date::tglWaktu($penilaianElemenAl->validated_at) }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                            <form class="form-penilaian" data-elemen-id="{{ $elemen->id }}">
                                                <div class="row mb-3">
                                                    <div class="col-md-12 mb-3">
                                                        <label class="form-label fw-semibold">
                                                            <i class="bi bi-star me-1"></i> Pilih Kategori Penilaian
                                                        </label>
                                                        <select class="form-select skor-select" name="skor" required>
                                                            <option value="">-- Pilih Kategori --</option>
                                                            @foreach ($jenjangs as $jenjang)
                                                            <option value="{{ $jenjang->skor }}" @if($hasPenilaian && $penilaianElemenAl->skor == $jenjang->skor) selected @endif style="background:{{ $jenjang->color }}; color:{{ \App\Models\JenjangPenilaian::textColorByBg($jenjang->color) }}">
                                                                {{ $jenjang->skor }} - {{ $jenjang->name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12 komentar-section">
                                                        <label class="form-label fw-semibold">
                                                            <i class="bi bi-chat-left-text me-1"></i> Komentar/Justifikasi Penilaian
                                                        </label>
                                                        <textarea class="form-control komentar-textarea" name="komentar" rows="15" placeholder="Berikan justifikasi dan analisis penilaian berdasarkan seluruh indikator di bawah ini..." required>{{ $hasPenilaian ? $penilaianElemenAl->komentar : '' }}</textarea>
                                                        <small class="text-muted">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            <span class="char-count">{{ $hasPenilaian ? strlen($penilaianElemenAl->komentar) : 0 }}</span> karakter
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                                                    <div class="save-status text-muted small d-flex align-items-center flex-wrap">
                                                        <i class="bi bi-cloud-check me-2"></i>
                                                        <span class="status-text">
                                                            @if($hasPenilaian)
                                                            Tersimpan pada {{ \App\Libraries\Date::tglWaktu($penilaianElemenAl->updated_at) }}
                                                            @else
                                                            Belum ada penilaian
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="btn-group ms-md-auto">
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
    @elseif($step === 2)
    @include('asesmen.al.components.berita-acara')
    @endif
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
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="bi bi-upload"></i> Upload Penilaian dari Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    {{-- Instructions --}}
                    <div class="alert alert-info alert-permanent alert-dismissible mb-3">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle"></i> Petunjuk Upload
                        </h6>
                        <ul class="mb-0 small">
                            <li>File harus berformat Excel (.xlsx atau .xls)</li>
                            <li>Gunakan template yang telah disediakan</li>
                            <li>Jangan ubah struktur atau nama sheet</li>
                            <li>Kolom <strong>Kode Elemen (E)</strong> tidak boleh diubah</li>
                            <li>Isi penilaian pada kolom I-M (pilih salah satu kategori, dan berikan justifikasi), pada cell warna kuning</li>
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
                            <strong>Progress Upload:</strong>
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
                        <i class="bi bi-upload"></i> Upload Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Import History Modal --}}
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="historyModalLabel">
                    <i class="bi bi-clock-history"></i> Riwayat Upload Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="historyContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat riwayat upload excel...</p>
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

<!-- Import Result Modal -->
<div class="modal fade" id="importResultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white" id="resultModalHeader">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle"></i> Upload Excel Berhasil
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const idAsesmen = "{{ $asesmen->id }}";

        const checkStatusPekerjaan = @json($isSubmitted);
        const needsRevision = @json($needsRevision);
        let saveTimeout;
        let isSaving = false;
        const AUTO_SAVE_DELAY = 2000;
        let currentImportLogId = null;
        let importStatusInterval = null;

        let pollingInterval = null;
        let pollCount = 0;
        const MAX_POLL_COUNT = 150; // 5 menit (150 * 2 detik)
        const POLL_INTERVAL = 2000; // 2 detik

        // Loading Overlay
        const loadingOverlay = createLoadingOverlay();
        const toggleBtn = document.getElementById('toggleAllAccordion');
        const importModal = document.getElementById('importModal');

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
            const btnImport = document.getElementById('btnImport');
            if (btnImport) btnImport.addEventListener('click', function() {
                // Reset form
                document.getElementById('importForm').reset();
                document.getElementById('fileInfo').classList.add('d-none');
                document.getElementById('importProgress').classList.add('d-none');
                const importAlert = document.getElementById('importAlert')
                if (importAlert) importAlert.classList.add('d-none');

                // Show modal
                const modal = new bootstrap.Modal(importModal);
                modal.show();
            });
            const btnImportHistory = document.getElementById('btnImportHistory');
            if (btnImportHistory) btnImportHistory.addEventListener('click', importHistoryExcel);
            const btnResetAll = document.getElementById('btnResetAll');
            if (btnResetAll) btnResetAll.addEventListener('click', resetAllPenilaian);
            // Import Form Submit
            document.getElementById('importForm').addEventListener('submit', importExcel);

            // Download Template
            const btnDownloadTemplate = document.getElementById('btnDownloadTemplate')
            if (btnDownloadTemplate) btnDownloadTemplate.addEventListener('click', downloadTemplate);
            document.querySelectorAll('.btnDownloadData').forEach(btn => {
                btn.addEventListener('click', downloadDataExcel);
            });
        }

        /**
         * Toggle Kriteria Accordion
         */
        window.toggleKriteriaAccordion = function(kriteriaId, btn) {
            const kriteriaCollapse = document.getElementById(`collapse-kriteria-${kriteriaId}`);
            if (!kriteriaCollapse) return;

            const kriteriaInstance = bootstrap.Collapse.getOrCreateInstance(
                kriteriaCollapse, {
                    toggle: false
                }
            );

            const isOpen = kriteriaCollapse.classList.contains('show');

            // 1️⃣ Toggle kriteria
            isOpen ? kriteriaInstance.hide() : kriteriaInstance.show();

            // 2️⃣ Toggle semua elemen di dalam kriteria
            const elemenCollapses = kriteriaCollapse.querySelectorAll('.elemen-collapse');

            elemenCollapses.forEach(el => {
                const instance = bootstrap.Collapse.getOrCreateInstance(el, {
                    toggle: false
                });
                isOpen ? instance.hide() : instance.show();
            });
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
                    <p class="text-primary"><i class="bi bi-info-circle"></i> Total: <strong>${total} elemen</strong> telah dinilai</p>
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
                const response = await fetch(`/al/berkas/${idAsesmen}/submit`, {
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
                            <div class="alert alert-info alert-permanent mt-3">
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
                const response = await fetch(`/al/berkas/${idAsesmen}/unsubmit`, {
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
                badge.innerHTML = '<i class="bi bi-check-circle"></i> Telah Dinilai';
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
                window.location.href = `/al/berkas/${idAsesmen}/export`;

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
                const response = await fetch(`/al/berkas/${idAsesmen}/import`, {
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
                console.error('Upload excel error:', error);

                // Re-enable buttons
                btnSubmit.disabled = false;
                btnClose.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-upload"></i> Upload Sekarang';

                // Show error
                showAlert('importAlert', 'danger', error.message);
                document.getElementById('importProgress').classList.add('d-none');
                // Clear file input
                fileInput.value = '';
                fileInfo.classList.add('d-none');
            }
        }

        /**
         * ============================================
         * POLLING IMPORT STATUS
         * ============================================
         */

        function pollImportStatus(importLogId) {
            // Clear any existing interval
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }

            // Reset poll count
            pollCount = 0;

            // Poll every 2 seconds
            pollingInterval = setInterval(async () => {
                pollCount++;

                // ✅ TIMEOUT: Stop after 5 minutes
                if (pollCount >= MAX_POLL_COUNT) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;

                    showAlert('importAlert', 'warning'
                        , 'Import timeout. Proses memakan waktu lebih lama dari biasanya. ' +
                        'Silakan refresh halaman untuk cek status terbaru.'
                    );

                    // Re-enable buttons
                    const btnSubmit = document.getElementById('btnSubmitImport');
                    const btnClose = document.getElementById('btnCloseImport');
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="bi bi-upload"></i> Upload Sekarang';
                    }
                    if (btnClose) btnClose.disabled = false;

                    return;
                }

                try {
                    const response = await fetch(`/al/import-status/${importLogId}`, {
                        headers: {
                            'Accept': 'application/json'
                            , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    // ✅ Handle HTTP errors
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }

                    const data = await response.json();

                    if (data.success) {
                        const log = data.data;

                        // Update progress with ETA
                        updateProgressImport(log);

                        // ✅ Check if completed or failed
                        if (log.status === 'completed' || log.status === 'failed') {
                            clearInterval(pollingInterval);
                            pollingInterval = null;
                            pollCount = 0;

                            // Show result
                            if (log.status === 'completed') {
                                showImportResult(log);
                            } else {
                                showImportError(log);
                            }
                        }
                    } else {
                        throw new Error(data.message || 'Gagal mendapatkan status import');
                    }

                } catch (error) {
                    console.error('Polling error:', error);

                    // ✅ Retry logic: Only clear after 3 consecutive errors
                    if (!window.pollErrorCount) window.pollErrorCount = 0;
                    window.pollErrorCount++;

                    if (window.pollErrorCount >= 3) {
                        clearInterval(pollingInterval);
                        pollingInterval = null;
                        window.pollErrorCount = 0;

                        showAlert('importAlert', 'danger'
                            , `Gagal memeriksa status import: ${error.message}. ` +
                            'Silakan refresh halaman untuk cek status.'
                        );

                        // Re-enable buttons
                        const btnSubmit = document.getElementById('btnSubmitImport');
                        const btnClose = document.getElementById('btnCloseImport');
                        if (btnSubmit) {
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = '<i class="bi bi-upload"></i> Upload Sekarang';
                        }
                        if (btnClose) btnClose.disabled = false;
                    } else {
                        console.warn(`Polling error (${window.pollErrorCount}/3), retrying...`);
                    }
                }
            }, POLL_INTERVAL);
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
            const MAX_CHAR = 2000;

            document.querySelectorAll('.komentar-textarea').forEach(textarea => {
                const counter = textarea
                    .closest('.komentar-section')
                    .querySelector('.char-count');

                textarea.addEventListener('input', function() {
                    if (this.value.length > MAX_CHAR) this.value = this.value.substring(0, MAX_CHAR);
                    if (counter) counter.textContent = this.value.length;
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
                    Swal.fire({
                        title: 'Yakin ingin mereset?'
                        , text: 'Semua penilaian yang telah diisi akan dihapus.'
                        , icon: 'warning'
                        , showCancelButton: true
                        , confirmButtonText: 'Ya, Reset'
                        , cancelButtonText: 'Batal'
                        , confirmButtonColor: '#dc3545'
                        , cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
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

                            // Optional: notifikasi sukses kecil
                            Swal.fire({
                                icon: 'success'
                                , title: 'Berhasil'
                                , text: 'Penilaian berhasil direset.'
                                , timer: 1500
                                , showConfirmButton: false
                            });
                        }
                    });
                });
            });
        }

        /**
         * Auto Save (Silent)
         */
        async function autoSavePenilaian(form, idElemen) {
            const formData = new FormData(form);
            if (isSaving) return;
            isSaving = true;
            try {
                const response = await fetch(`/al/berkas/${idAsesmen}/nilai`, {
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
            } finally {
                isSaving = false;
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
                const response = await fetch(`/al/berkas/${idAsesmen}/nilai`, {
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
                            badge.innerHTML = '<i class="bi bi-check-circle"></i> Telah Dinilai';
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
            const charCount = textarea.closest('.komentar-section').querySelector('.char-count');
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

            window.location.href = `/al/berkas/${idAsesmen}/template`;

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
        function downloadDataExcel(e) {
            showLoading();
            const mode = e.currentTarget.dataset.mode;
            window.location.href = `/al/berkas/${idAsesmen}/export?mode=${mode}`;

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
         * Show import result (success)
         */
        function showImportResult(data) {
            // Close import modal
            const modal = bootstrap.Modal.getInstance(importModal);
            if (modal) modal.hide();

            // Prepare result content
            const resultContent = `
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-success">Simpan Data Excel Berhasil!</h4>
                </div>

                <div class="row text-center mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-primary mb-0">${data.total_rows}</h3>
                            <small class="text-muted">Total Baris Terbaca</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-success mb-0">${data.imported_rows}</h3>
                            <small class="text-muted">Penilaian Elemen Berhasil Disimpan</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-danger mb-0">${data.failed_rows}</h3>
                            <small class="text-muted">Penilaian Elemen Gagal Disimpan</small>
                        </div>
                    </div>
                </div>

                <div class="alert alert-success alert-permanent">
                    <p class="mb-0 small">
                        Waktu selesai: ${data.completed_at}
                    </p>
                </div>

                ${data.errors && data.errors.length > 0 ? `
                    <div class="alert alert-warning alert-permanent alert-dismissible">
                        <strong>⚠️ Peringatan:</strong>
                        <p class="mb-2">Beberapa baris gagal diproses:</p>
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
            const modal = bootstrap.Modal.getInstance(importModal);
            if (modal) modal.hide();

            // Prepare error content
            const errorContent = `
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-danger">Import Gagal</h4>
                </div>

                <div class="alert alert-danger alert-permanent">
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
            const modal = showModalById('historyModal');

            // Load history
            try {
                const response = await fetch(`/al/berkas/${idAsesmen}/import-history`, {
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
                <div class="alert alert-danger alert-permanent">
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
                <div class="alert alert-info alert-permanent">
                    <i class="bi bi-info-circle"></i>
                    Belum ada riwayat upload excel.
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
                , title: 'Upload excel error'
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
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }
        });

        /**
         * ============================================
         * DISABLE EDITING IF SUBMITTED
         * ============================================
         */
        if (checkStatusPekerjaan && !needsRevision) {
            // Disable all form inputs ONLY if submitted AND NOT needs revision
            document.querySelectorAll('.form-penilaian').forEach(form => {
                form.querySelectorAll('select, textarea, button[type="submit"]').forEach(el => {
                    el.disabled = true;
                });

                // Add info message
                const infoDiv = document.createElement('div');
                infoDiv.className = 'alert alert-info alert-permanent mt-2';
                infoDiv.innerHTML = '<i class="bi bi-info-circle"></i> Penilaian telah di-submit, tidak bisa diedit.';
                form.appendChild(infoDiv);
            });

            // Disable import if submitted and not needs revision
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

            if (log.started_at && log.imported_rows > 0) {
                const elapsed = Date.now() - new Date(log.started_at).getTime();
                const avgPerRow = elapsed / log.imported_rows;
                const remaining = (log.total_rows - log.imported_rows) * avgPerRow;
                const eta = Math.ceil(remaining / 1000); // seconds

                document.getElementById('progressText').textContent =
                    `${percentage}% (sisa ~${eta} detik)`;
            }
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
            alert.className = `alert alert-${type} alert-permanent`;
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

        const importResultModal = document.getElementById('importResultModal');
        if (importResultModal) {
            importResultModal.addEventListener('hidden.bs.modal', function() {
                // Show loading indicator
                Swal.fire({
                    title: 'Memuat ulang data...'
                    , html: 'Mohon tunggu sebentar'
                    , allowOutsideClick: false
                    , allowEscapeKey: false
                    , didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Refresh halaman setelah delay singkat
                setTimeout(() => {
                    window.location.reload();
                }, 300);
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
                <p><strong>PERHATIAN:</strong> Anda akan menghapus <strong class="text-danger">${completed} penilaian</strong> yang telah dibuat!</p>

                <div class="alert alert-danger alert-permanent mt-3">
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

                <div class="alert alert-warning alert-permanent alert-dismissible mt-3">
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
                const response = await fetch(`/al/berkas/${idAsesmen}/reset-all`, {
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
                        <div class="alert alert-info alert-permanent mt-3">
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

    /**
     * ============================================
     * HANDLE REVISI - AUTO OPEN ACCORDION
     * ============================================
     */
    document.querySelectorAll('.btn-buka-revisi').forEach(btn => {
        btn.addEventListener('click', function() {
            const elemenId = this.dataset.elemenId;
            const kriteriaId = this.dataset.kriteriaId;

            // 1. Buka accordion kriteria
            const kriteriaCollapse = document.querySelector(`#collapse-kriteria-${kriteriaId}`);
            if (kriteriaCollapse) {
                const kriteriaInstance = bootstrap.Collapse.getOrCreateInstance(kriteriaCollapse, {
                    toggle: false
                });
                kriteriaInstance.show();
            }

            // 2. Tunggu kriteria terbuka, lalu buka elemen
            setTimeout(() => {
                const elemenCollapse = document.querySelector(`#collapse-elemen-${elemenId}`);
                if (elemenCollapse) {
                    const elemenInstance = bootstrap.Collapse.getOrCreateInstance(elemenCollapse, {
                        toggle: false
                    });
                    elemenInstance.show();

                    // 3. Scroll ke elemen
                    setTimeout(() => {
                        const elemenCard = document.querySelector(`.elemen-card[data-elemen-id="${elemenId}"]`);
                        if (elemenCard) {
                            elemenCard.scrollIntoView({
                                behavior: 'smooth'
                                , block: 'center'
                            });

                            // 4. Highlight element
                            elemenCard.classList.add('highlight-revision');
                            setTimeout(() => {
                                elemenCard.classList.remove('highlight-revision');
                            }, 3000);

                            // 5. Focus ke textarea komentar
                            const textarea = elemenCard.querySelector('.komentar-textarea');
                            if (textarea) {
                                textarea.focus();
                            }
                        }
                    }, 500);
                }
            }, 500);
        });
    });

    /**
     * ============================================
     * HANDLE "GUNAKAN SKOR INI" BUTTON
     * ============================================
     */
    document.querySelectorAll('.btn-use-validator-score').forEach(btn => {
        btn.addEventListener('click', function() {
            const skor = this.dataset.skor;
            const elemenId = this.dataset.elemenId;

            // Find form for this elemen
            const form = document.querySelector(`.form-penilaian[data-elemen-id="${elemenId}"]`);
            if (!form) return;

            const skorSelect = form.querySelector('.skor-select');
            if (!skorSelect) return;

            // Set value
            skorSelect.value = skor;

            // Trigger change event for auto-save
            skorSelect.dispatchEvent(new Event('input', {
                bubbles: true
            }));

            // Visual feedback
            this.innerHTML = '<i class="bi bi-check-circle"></i> Skor Diterapkan!';
            this.classList.remove('btn-primary');
            this.classList.add('btn-success');

            // Scroll to komentar textarea
            const komentarTextarea = form.querySelector('.komentar-textarea');
            if (komentarTextarea) {
                komentarTextarea.focus();
                komentarTextarea.scrollIntoView({
                    behavior: 'smooth'
                    , block: 'center'
                });
            }

            // Show toast
            Swal.fire({
                toast: true
                , position: 'top-end'
                , icon: 'success'
                , title: `Kategori ${skor} diterapkan!`
                , text: 'Silakan perbarui komentar/justifikasi Anda'
                , showConfirmButton: false
                , timer: 3000
                , timerProgressBar: true
            });

            // Reset button after 3 seconds
            setTimeout(() => {
                this.innerHTML = '<i class="bi bi-lightning-charge"></i> Gunakan Kategori Ini';
                this.classList.remove('btn-success');
                this.classList.add('btn-primary');
            }, 3000);
        });
    });

    function togglePanduanAccordion(elemenId) {
        const accordion = document.getElementById(`accordionPanduan${elemenId}`);
        const items = accordion.querySelectorAll('.accordion-collapse');
        const anyOpen = Array.from(items).some(item => item.classList.contains('show'));

        items.forEach(item => {
            const bsCollapse = bootstrap.Collapse.getOrCreateInstance(item, {
                toggle: false
            });
            anyOpen ? bsCollapse.hide() : bsCollapse.show();
        });
    }

</script>
@endpush

@endsection
