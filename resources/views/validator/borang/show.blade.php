@extends('layouts.template.app')

@section('title', 'Validasi Dokumen - ' . $pengajuan->nomor_pengajuan)

@push('styles')
<style>
    .review-card {
        border-left: 4px solid #0d6efd;
    }

    .grade-btn {
        min-width: 100px;
    }

    .grade-btn.active {
        font-weight: bold;
    }

    .progress-circle {
        width: 100px;
        height: 100px;
    }

    .progress-grade-footer {
        display: flex;
        justify-content: center;
        gap: .75rem;
        flex-wrap: wrap;
        padding: .4rem .75rem;
    }

    .progress-grade-footer .legend-badge {
        margin-right: 0;
    }

    .monitoring-scroll {
        max-height: 360px;
        /* silakan ubah: 300-500 */
        overflow-y: auto;
    }

    .legend-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-right: .5rem;
    }

    .legend-dot {
        width: .65rem;
        height: .65rem;
        border-radius: 999px;
        display: inline-block;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>
                <i class="bi bi-clipboard-check"></i>
                Validasi Dokumen
            </h3>
            <p class="text-muted mb-0">
                {{ $pengajuan->judul }}
            </p>
        </div>
        <a href="{{ route('validator.borang.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Progress Overview --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">LED</h6>
                    <h3 class="mb-0">
                        <span id="count-led-reviewed">{{ $validation->reviewed_led }}</span> /
                        <span id="count-led-total">{{ $validation->total_elemen_led }}</span>
                    </h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-primary" id="bar-led" style="width: {{ $progress['led_percentage'] }}%"></div>
                    </div>
                    <small><span id="percent-led">{{ $progress['led_percentage'] }}</span>%</small>
                </div>
                <div class="card-footer bg-white border-0">
                    <div class="progress-grade-footer small">
                        <span class="legend-badge">
                            <span class="legend-dot bg-primary"></span> A (Sudah Tepat): <strong id="led-a">0</strong>
                        </span>
                        <span class="legend-badge">
                            <span class="legend-dot bg-warning"></span> B (Kurang Lengkap): <strong id="led-b">0</strong>
                        </span>
                        <span class="legend-badge">
                            <span class="legend-dot bg-danger"></span> C (Perlu Diperbaiki): <strong id="led-c">0</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Suplemen</h6>
                    <h3 class="mb-0">
                        <span id="count-suplemen-reviewed">{{ $validation->reviewed_suplemen }}</span> /
                        <span id="count-suplemen-total">{{ $validation->total_elemen_suplemen }}</span>
                    </h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-info" id="bar-suplemen" style="width: {{ $progress['suplemen_percentage'] }}%"></div>
                    </div>
                    <small><span id="percent-suplemen">{{ $progress['suplemen_percentage'] }}</span>%</small>
                </div>
                <div class="card-footer bg-white border-0">
                    <div class="progress-grade-footer small">
                        <span class="legend-badge">
                            <span class="legend-dot bg-primary"></span> A (Sudah Tepat): <strong id="suplemen-a">0</strong>
                        </span>
                        <span class="legend-badge">
                            <span class="legend-dot bg-warning"></span> B (Kurang Lengkap): <strong id="suplemen-b">0</strong>
                        </span>
                        <span class="legend-badge">
                            <span class="legend-dot bg-danger"></span> C (Perlu Diperbaiki): <strong id="suplemen-c">0</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">LKPS</h6>
                    <h3 class="mb-0">
                        <span id="count-lkps-reviewed">{{ $validation->reviewed_lkps }}</span> /
                        <span id="count-lkps-total">{{ $validation->total_indikator_lkps }}</span>
                    </h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-success" id="bar-lkps" style="width: {{ $progress['lkps_percentage'] }}%"></div>
                    </div>
                    <small><span id="percent-lkps">{{ $progress['lkps_percentage'] }}</span>%</small>
                </div>
                <div class="card-footer bg-white border-0">
                    <div class="progress-grade-footer small">
                        <span class="legend-badge">
                            <span class="legend-dot bg-primary"></span> A (Sudah Tepat): <strong id="lkps-a">0</strong>
                        </span>
                        <span class="legend-badge">
                            <span class="legend-dot bg-warning"></span> B (Kurang Lengkap): <strong id="lkps-b">0</strong>
                        </span>
                        <span class="legend-badge">
                            <span class="legend-dot bg-danger"></span> C (Perlu Diperbaiki): <strong id="lkps-c">0</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Total Progress</h6>
                    <h3 class="mb-0"><span id="percent-total">{{ $progress['percentage'] }}</span>%</h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-warning" id="bar-total" style="width: {{ $progress['percentage'] }}%"></div>
                    </div>
                    <small>
                        <span id="count-total-reviewed">{{ $progress['reviewed'] }}</span> /
                        <span id="count-total">{{ $progress['total'] }}</span> item
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- @include('validator.borang.partials.monitoring-review') --}}

    {{-- Dokumen yang diupload Prodi --}}
    <div class="card mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-folder2-open"></i> Dokumen dari Prodi
            </h5>
            <small class="text-muted">File terbaru</small>
        </div>

        <div class="card-body">
            @php
            $docCards = [
            'led' => [
            'label' => 'Laporan Evaluasi Diri (LED)',
            'btn_class' => 'btn-primary',
            'empty_text' => 'Belum ada file LED diupload.',
            ],
            'suplemen' => [
            'label' => 'Suplemen',
            'btn_class' => 'btn-info text-white',
            'empty_text' => 'Belum ada file suplemen diupload.',
            ],
            'lkps' => [
            'label' => 'Laporan Kinerja Program Studi (LKPS)',
            'btn_class' => 'btn-success',
            'empty_text' => 'Belum ada file LKPS diupload.',
            ],
            'formulir_pembayaran' => [
            'label' => 'Formulir & Bukti Pembayaran Akreditasi',
            'btn_class' => 'btn-info',
            'empty_text' => 'Belum ada file Formulir & Bukti Pembayaran diupload.',
            ],
            'surat_permohonan' => [
            'label' => 'Surat Permohonan Akreditasi',
            'btn_class' => 'btn-primary',
            'empty_text' => 'Belum ada file Surat Permohonan Akreditasi diupload.',
            ],
            ];
            @endphp
            <div class="row g-3">
                @foreach($docCards as $key => $config)
                @php $file = $uploadedFiles[$key] ?? null; @endphp

                <div class="col-lg-4">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex align-items-start gap-3">

                            {{-- Icon --}}
                            <i class="bi {{ $file?->file_icon_class ?? 'bi-file-earmark' }} fs-4 flex-shrink-0"></i>

                            {{-- Text --}}
                            <div class="flex-grow-1">
                                <div class="fw-bold mb-1">{{ $config['label'] }}</div>

                                @if($file)
                                <div class="small fw-semibold text-break">
                                    {{ $file->original_filename }}
                                </div>
                                <div class="text-muted small">
                                    {{ $file->file_size_formatted ?? '' }} •
                                    {{ $file->created_at->diffForHumans() }}
                                </div>
                                @else
                                <div class="text-muted small">
                                    {{ $config['empty_text'] }}
                                </div>
                                @endif
                            </div>

                            {{-- Button --}}
                            @if($file && $file->download_url)
                            <div class="flex-shrink-0">
                                <a class="btn btn-sm {{ $config['btn_class'] }}" href="{{ $file->download_url }}" target="_blank" rel="noopener">
                                    <i class="bi bi-download"></i> Buka
                                </a>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Optional: pengesahan --}}
            @if(!empty($uploadedFiles['pengesahan']))
            <hr class="my-3">
            <div class="d-flex align-items-start gap-3">
                <i class="bi {{ $uploadedFiles['pengesahan']->file_icon_class }} fs-4 flex-shrink-0"></i>

                <div class="flex-grow-1">
                    <div class="fw-bold">Lembar Pengesahan</div>
                    <div class="text-muted small text-break">
                        {{ $uploadedFiles['pengesahan']->original_filename }}
                        • {{ $uploadedFiles['pengesahan']->file_size_formatted ?? '' }}
                        • {{ $uploadedFiles['pengesahan']->created_at->diffForHumans() }}
                    </div>
                </div>

                <div class="flex-shrink-0">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ $uploadedFiles['pengesahan']->download_url }}" target="_blank" rel="noopener">
                        <i class="bi bi-download"></i> Buka
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Excel Import/Export --}}
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-file-earmark-excel"></i> Upload/Download Excel Validasi
            </h5>
            <span class="badge bg-light text-dark">Opsional</span>
        </div>
        <div class="card-body">
            <div class="alert alert-info alert-permanent mb-3">
                <i class="bi bi-info-circle"></i>
                <strong>Tips:</strong> Anda dapat melakukan validasi melalui Excel untuk mempermudah proses.
                Download template, isi validasi, lalu upload kembali ke sistem.
            </div>

            <div class="row g-3">
                {{-- Download Section --}}
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-download"></i> Download Excel
                            </h6>
                            <p class="card-text text-muted small">
                                Download file Excel untuk validasi offline
                            </p>

                            <div class="btn-group w-100" role="group">
                                <a href="{{ route('validator.borang.download-template', $assignment->id) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-file-earmark"></i> Template Kosong
                                </a>
                                <a href="{{ route('validator.borang.download-review', $assignment->id) }}" class="btn btn-outline-success">
                                    <i class="bi bi-file-earmark-check"></i> Hasil Validasi Anda
                                </a>
                            </div>

                            <div class="mt-2">
                                <small class="text-muted">
                                    <strong>Template Kosong:</strong> File Excel baru tanpa isian<br>
                                    <strong>Hasil Validasi:</strong> File Excel berisi validasi yang sudah Anda isi
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Upload Section --}}
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-upload"></i> Upload Excel
                            </h6>
                            <p class="card-text text-muted small">
                                Upload file Excel yang sudah diisi untuk diproses
                            </p>

                            <form action="{{ route('validator.borang.upload-review', $assignment->id) }}" method="POST" enctype="multipart/form-data" id="formUploadReview">
                                @csrf

                                <div class="mb-3">
                                    <input type="file" class="form-control" name="file" id="fileReview" accept=".xlsx,.xls" required>
                                    <div class="form-text">
                                        Format: .xlsx atau .xls (Maks. 10MB)
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100" id="btnUploadReview">
                                    <i class="bi bi-upload"></i> Upload File
                                </button>
                            </form>

                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Data yang diupload akan digabungkan dengan validasi online yang sudah ada
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Review Content --}}
    <div class="row">
        <div class="col-12">

            {{-- Global Controls --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnToggleAll">
                        <i class="bi bi-arrows-expand"></i> Expand All
                    </button>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="btnResetDropdown">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                        <ul class="dropdown-menu" id="resetDropdownMenu">
                            <li>
                                <button class="dropdown-item" type="button" data-reset-target="active" id="reset-active">
                                    Reset Tab Aktif
                                </button>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <button class="dropdown-item" type="button" data-reset-target="led" id="reset-led">
                                    Reset LED
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item" type="button" data-reset-target="suplemen" id="reset-suplemen">
                                    Reset Suplemen
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item" type="button" data-reset-target="lkps" id="reset-lkps">
                                    Reset LKPS
                                </button>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <button class="dropdown-item text-danger fw-semibold" type="button" data-reset-target="all" id="reset-all">
                                    Reset SEMUA (LED + Suplemen + LKPS)
                                </button>
                            </li>
                        </ul>
                    </div>

                    @if(app()->environment('local'))
                    <button type="button" class="btn btn-outline-danger btn-sm" id="btnAutoTestReview">
                        <i class="bi bi-lightning-charge"></i> Auto Test Review
                    </button>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark" id="activeTabLabel">Tab: LED</span>
                    <span class="badge bg-warning text-dark" id="tabReviewedInfo">-</span>
                </div>
            </div>

            {{-- Tabs: LED | Suplemen | LKPS --}}
            <ul class="nav nav-tabs mb-3" id="reviewTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-led" data-bs-toggle="tab" data-bs-target="#pane-led" type="button" role="tab">
                        <i class="bi bi-file-earmark-text"></i> LED
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-suplemen" data-bs-toggle="tab" data-bs-target="#pane-suplemen" type="button" role="tab">
                        <i class="bi bi-file-earmark-plus"></i> Suplemen
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-lkps" data-bs-toggle="tab" data-bs-target="#pane-lkps" type="button" role="tab">
                        <i class="bi bi-table"></i> LKPS (Kuantitatif)
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="reviewTabContent">

                {{-- =========================
           PANE LED
      ========================== --}}
                <div class="tab-pane fade show active" id="pane-led" role="tabpanel" aria-labelledby="tab-led">
                    @foreach($kriterias as $kriteria)
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">{{ $kriteria->kode_kriteria }} - {{ $kriteria->nama_kriteria }}</h5>
                        </div>

                        <div class="card-body">
                            <div class="accordion" id="acc-led-{{ $kriteria->id }}">
                                @foreach($kriteria->elemenStandar as $elemen)
                                @php
                                $reviewData = $validation->review_led ?? [];
                                $isReviewed = isset($reviewData[$elemen->id]);
                                $badgeClass = $isReviewed ? 'bg-success' : 'bg-warning text-dark';
                                $badgeText = $isReviewed ? 'Validasi Lengkap' : 'Validasi Belum Lengkap';
                                @endphp

                                <div class="accordion-item elemen-accordion-item" data-tab="led" data-elemen-id="{{ $elemen->id }}" data-required-count="1" data-reviewed-count="{{ $isReviewed ? 1 : 0 }}">
                                    <h2 class="accordion-header" id="h-led-{{ $elemen->id }}">
                                        <button class="accordion-button collapsed d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#c-led-{{ $elemen->id }}" aria-expanded="false" aria-controls="c-led-{{ $elemen->id }}">
                                            <span class="badge bg-light text-dark">{{ $elemen->kode_elemen }}</span>
                                            <span class="flex-grow-1"><strong>{{ $elemen->pernyataan_elemen }}</strong></span>
                                            <span class="badge {{ $badgeClass }} elemen-status-badge">{{ $badgeText }}</span>
                                        </button>
                                    </h2>
                                    <div id="c-led-{{ $elemen->id }}" class="accordion-collapse collapse" aria-labelledby="h-led-{{ $elemen->id }}" data-bs-parent="#acc-led-{{ $kriteria->id }}">
                                        <div class="accordion-body">
                                            @include('validator.borang.partials.review-item', [
                                            'category' => 'led',
                                            'itemId' => $elemen->id,
                                            'validation' => $validation,
                                            'assignmentId' => $assignment->id,
                                            ])
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- =========================
           PANE SUPLEMEN
      ========================== --}}
                <div class="tab-pane fade" id="pane-suplemen" role="tabpanel" aria-labelledby="tab-suplemen">

                    @php
                    $reviewData = $validation->review_suplemen ?? [];
                    @endphp

                    @forelse($suplemenGrouped as $sectionKey => $items)
                    @continue($sectionKey === 'header')
                    @php
                    // hitung complete per section
                    $required = $items->count();
                    $reviewedCount = 0;
                    foreach($items as $it){
                    if(isset($reviewData[$it->id])) $reviewedCount++;
                    }
                    $isComplete = ($required === 0) ? true : ($reviewedCount === $required);
                    $badgeClass = $isComplete ? 'bg-success' : 'bg-warning text-dark';
                    $badgeText = $isComplete ? 'Validasi Lengkap' : 'Validasi Belum Lengkap';
                    @endphp

                    <div class="card mb-3">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <div class="fw-bold">
                                {{ \Illuminate\Support\Str::headline($sectionKey) }}
                            </div>
                            <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                        </div>

                        <div class="card-body">
                            <div class="accordion" id="acc-suplemen-{{ $sectionKey }}">

                                @foreach($items as $it)
                                @php
                                $isReviewedItem = isset($reviewData[$it->id]);
                                $itemBadgeClass = $isReviewedItem ? 'bg-success' : 'bg-warning text-dark';
                                $itemBadgeText = $isReviewedItem ? 'Validasi Lengkap' : 'Validasi Belum Lengkap';
                                @endphp

                                <div class="accordion-item elemen-accordion-item" data-tab="suplemen" data-elemen-id="{{ $it->id }}" data-required-count="1" data-reviewed-count="{{ $isReviewedItem ? 1 : 0 }}">
                                    <h2 class="accordion-header" id="h-suplemen-ds-{{ $it->id }}">
                                        <button class="accordion-button collapsed d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#c-suplemen-ds-{{ $it->id }}" aria-expanded="false" aria-controls="c-suplemen-ds-{{ $it->id }}">
                                            <span class="badge bg-light text-dark">#{{ $it->urutan }}</span>
                                            <span class="flex-grow-1">
                                                <strong>{{ $it->text_content }}</strong>
                                            </span>
                                            <span class="badge {{ $itemBadgeClass }} elemen-status-badge">{{ $itemBadgeText }}</span>
                                        </button>
                                    </h2>

                                    <div id="c-suplemen-ds-{{ $it->id }}" class="accordion-collapse collapse" aria-labelledby="h-suplemen-ds-{{ $it->id }}" data-bs-parent="#acc-suplemen-{{ $sectionKey }}">
                                        <div class="accordion-body">
                                            @include('validator.borang.partials.review-item', [
                                            'category' => 'suplemen',
                                            'itemId' => $it->id, // ✅ dataset_suplemen.id
                                            'validation' => $validation,
                                            'assignmentId' => $assignment->id,
                                            ])
                                        </div>
                                    </div>
                                </div>
                                @endforeach

                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-secondary alert-permanent">
                        Dataset Suplemen untuk jenjang <strong>{{ $pengajuan->studyProgram->degreeLevel->code }}</strong> belum tersedia.
                    </div>
                    @endforelse
                </div>

                {{-- =========================
           PANE LKPS (Kuantitatif)
      ========================== --}}
                <div class="tab-pane fade" id="pane-lkps" role="tabpanel" aria-labelledby="tab-lkps">
                    @foreach($kriterias as $kriteria)
                    @php
                    // Filter elemen yang punya indikator kuantitatif
                    $elemenWithIndikator = $kriteria->elemenStandar->filter(fn($elemen) => $elemen->indikator->count() > 0);
                    @endphp

                    @if($elemenWithIndikator->isNotEmpty())
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">{{ $kriteria->kode_kriteria }} - {{ $kriteria->nama_kriteria }}</h5>
                        </div>

                        <div class="card-body">
                            <div class="accordion" id="acc-lkps-{{ $kriteria->id }}">
                                @foreach($elemenWithIndikator as $elemen)
                                @php
                                $indikators = $elemen->indikator ?? collect();
                                $required = $indikators->count();
                                $reviewData = $validation->review_lkps ?? [];
                                $reviewedCount = 0;
                                foreach($indikators as $ind){
                                if(isset($reviewData[$ind->id])) $reviewedCount++;
                                }
                                $isComplete = ($required > 0) ? ($reviewedCount === $required) : true;
                                $badgeClass = $isComplete ? 'bg-success' : 'bg-warning text-dark';
                                $badgeText = $isComplete ? 'Validasi Lengkap' : 'Validasi Belum Lengkap';
                                @endphp

                                <div class="accordion-item elemen-accordion-item" data-tab="lkps" data-elemen-id="{{ $elemen->id }}" data-required-count="{{ $required }}" data-reviewed-count="{{ $reviewedCount }}">
                                    <h2 class="accordion-header" id="h-lkps-{{ $elemen->id }}">
                                        <button class="accordion-button collapsed d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#c-lkps-{{ $elemen->id }}" aria-expanded="false" aria-controls="c-lkps-{{ $elemen->id }}">
                                            <span class="badge bg-light text-dark">{{ $elemen->kode_elemen }}</span>
                                            <span class="flex-grow-1"><strong>{{ $elemen->pernyataan_elemen }}</strong></span>
                                            <span class="badge {{ $badgeClass }} elemen-status-badge">{{ $badgeText }}</span>
                                            <span class="badge bg-dark ms-2 elemen-lkps-counter">{{ $reviewedCount }}/{{ $required }}</span>
                                        </button>
                                    </h2>
                                    <div id="c-lkps-{{ $elemen->id }}" class="accordion-collapse collapse" aria-labelledby="h-lkps-{{ $elemen->id }}" data-bs-parent="#acc-lkps-{{ $kriteria->id }}">
                                        <div class="accordion-body">
                                            @foreach($indikators as $indikator)
                                            <div class="border rounded p-3 mb-3">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong>{{ $indikator->kode_indikator }}</strong> - {{ $indikator->deskripsi_indikator }}
                                                    </div>
                                                    <span class="badge bg-light text-dark">ID: {{ $indikator->id }}</span>
                                                </div>

                                                <div class="mt-2">
                                                    @include('validator.borang.partials.review-item', [
                                                    'category' => 'lkps',
                                                    'itemId' => $indikator->id,
                                                    'validation' => $validation,
                                                    'assignmentId' => $assignment->id,
                                                    'elemenId' => $elemen->id,
                                                    ])
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>

            </div>

            {{-- Finalisasi & Kirim (JSON) --}}
            <div class="card border-success mt-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-send-check"></i> Finalisasi & Kirim
                    </h5>
                    <span class="badge bg-light text-dark" id="finalStatusBadge">
                        {{ $validation->isCompletelyReviewed() ? 'Validasi Lengkap' : 'Validasi Belum Lengkap' }}
                    </span>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <label class="form-label fw-bold">Catatan Umum LED</label>
                            <textarea id="catatan_led" class="form-control" rows="3" placeholder="Catatan umum untuk LED...">{{ $validation->catatan_led }}</textarea>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="form-label fw-bold">Catatan Umum Suplemen</label>
                            <textarea id="catatan_suplemen" class="form-control" rows="3" placeholder="Catatan umum untuk Suplemen...">{{ $validation->catatan_suplemen }}</textarea>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="form-label fw-bold">Catatan Umum LKPS</label>
                            <textarea id="catatan_lkps" class="form-control" rows="3" placeholder="Catatan umum untuk LKPS...">{{ $validation->catatan_lkps }}</textarea>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Catatan Validator (Keseluruhan)</label>
                        <textarea id="catatan_validator" class="form-control" rows="4" placeholder="Catatan keseluruhan untuk prodi...">{{ $validation->catatan_validator }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        {{-- Pilih aksi (tidak submit) --}}
                        <button type="button" class="btn btn-outline-success" id="btnPickApprove">
                            <i class="bi bi-check-circle"></i> Pilih Setujui Dokumen
                        </button>

                        <button type="button" class="btn btn-outline-warning" id="btnPickRevision">
                            <i class="bi bi-exclamation-triangle"></i> Pilih Minta Revisi
                        </button>

                        <span class="badge bg-secondary" id="pickedActionBadge">
                            Aksi belum dipilih
                        </span>

                        {{-- Tombol submit final --}}
                        <button type="button" class="btn btn-primary ms-auto" id="btnSubmitFinal" disabled>
                            <i class="bi bi-send"></i> Submit Final
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@php
$assignmentId = $assignment->id;
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const LOCK_BORANG = @json($lockBorang);
        const ASSIGNMENT_ID = @json($assignmentId);

        // =============== PICK ACTION (NO SUBMIT) ===============
        let pickedAction = null; // 'approve' | 'revision'
        const isEnvLocal = @json($isEnvLocal); // boolean
        const izinkanTestLocal = true;

        // =============== TAB STATE (single source of truth) ===============
        let activeTab = 'led';

        const tabMap = {
            'tab-led': {
                tab: 'led'
                , label: 'LED'
            }
            , 'tab-suplemen': {
                tab: 'suplemen'
                , label: 'Suplemen'
            }
            , 'tab-lkps': {
                tab: 'lkps'
                , label: 'LKPS'
            }
        , };

        const paneByTab = {
            led: '#pane-led'
            , suplemen: '#pane-suplemen'
            , lkps: '#pane-lkps'
        , };

        function getPaneId(tab = activeTab) {
            return paneByTab[tab] || '#pane-led';
        }

        function labelByTab(tab) {
            return tab === 'led' ? 'LED' : (tab === 'suplemen' ? 'Suplemen' : 'LKPS');
        }

        // =============== DOM REFS ===============
        const btnPickApprove = document.getElementById('btnPickApprove');
        const btnPickRevision = document.getElementById('btnPickRevision');
        const pickedBadge = document.getElementById('pickedActionBadge');
        const btnSubmitFinal = document.getElementById('btnSubmitFinal');
        const btnToggleAll = document.getElementById('btnToggleAll');
        const btnAutoTestReview = document.getElementById('btnAutoTestReview');
        const btnResetDropdown = document.getElementById('btnResetDropdown');
        const resetDropdownMenu = document.getElementById('resetDropdownMenu');
        const monitoringCollapse = document.getElementById('monitoringCollapse');
        const iconToggle = document.getElementById('iconToggleMonitoring');
        const btnToggleMon = document.getElementById('btnToggleMonitoring');

        // Upload form refs
        const formUpload = document.getElementById('formUploadReview');
        const btnUpload = document.getElementById('btnUploadReview');
        const fileInput = document.getElementById('fileReview');

        // =============== RENDER PICKED ACTION ===============
        function renderPickedAction() {
            if (!pickedAction) {
                pickedBadge.textContent = 'Aksi belum dipilih';
                pickedBadge.className = 'badge bg-secondary';
                btnSubmitFinal.disabled = true;

                btnPickApprove.className = 'btn btn-outline-success';
                btnPickRevision.className = 'btn btn-outline-warning';
                return;
            }

            btnSubmitFinal.disabled = false;

            if (pickedAction === 'approve') {
                pickedBadge.textContent = 'Aksi terpilih: SETUJUI DOKUMEN';
                pickedBadge.className = 'badge bg-success';
                btnPickApprove.className = 'btn btn-success';
                btnPickRevision.className = 'btn btn-outline-warning';
            } else {
                pickedBadge.textContent = 'Aksi terpilih: MINTA REVISI DOKUMEN';
                pickedBadge.className = 'badge bg-warning text-dark';
                btnPickRevision.className = 'btn btn-warning';
                btnPickApprove.className = 'btn btn-outline-success';
            }
        }

        renderPickedAction();

        btnPickApprove.addEventListener('click', () => {
            pickedAction = 'approve';
            renderPickedAction();
        });

        btnPickRevision.addEventListener('click', () => {
            pickedAction = 'revision';
            renderPickedAction();
        });

        // =============== UPLOAD VALIDATION ===============
        if (formUpload) {
            formUpload.addEventListener('submit', function(e) {
                if (!fileInput.files.length) {
                    e.preventDefault();
                    alert('Pilih file Excel terlebih dahulu');
                    return;
                }

                const file = fileInput.files[0];
                const maxSize = 10 * 1024 * 1024; // 10MB

                if (file.size > maxSize) {
                    e.preventDefault();
                    alert('Ukuran file terlalu besar (maksimal 10MB)');
                    return;
                }

                // Show loading
                btnUpload.disabled = true;
                btnUpload.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
            });
        }

        // =============== FINAL SUBMIT (JSON) ===============
        async function submitFinal(action) {
            const msg = action === 'approve' ?
                'Semua item harus kategori <strong>Sudah Tepat</strong>.' :
                'Item dengan kategori <strong>Kurang Lengkap, Perlu Melengkapi</strong> atau <strong>Perlu diperbaiki</strong> akan dikirim ke prodi.';

            const result = await Swal.fire({
                title: action === 'approve' ? 'Setujui Validasi?' : 'Minta Prodi Merevisi?'
                , html: msg
                , icon: action === 'approve' ? 'question' : 'warning'
                , showCancelButton: true
                , confirmButtonText: 'Ya, lanjutkan'
                , cancelButtonText: 'Batal'
                , reverseButtons: true
            });

            if (!result.isConfirmed) return;

            const payload = {
                action
                , catatan_validator: document.getElementById('catatan_validator').value
                , catatan_led: document.getElementById('catatan_led').value
                , catatan_suplemen: document.getElementById('catatan_suplemen').value
                , catatan_lkps: document.getElementById('catatan_lkps').value
            , };

            const url = @json(route('validator.borang.submit', $assignmentId));

            try {
                const res = await fetch(url, {
                    method: 'POST'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'Accept': 'application/json'
                        , 'X-CSRF-TOKEN': @json(csrf_token())
                    }
                    , body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    Swal.fire({
                        icon: 'error'
                        , title: 'Gagal'
                        , text: data.message || 'Gagal submit'
                    });
                    return;
                }

                Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil'
                    , text: data.message || 'Data berhasil diproses'
                });
                if (data.redirect) window.location.href = data.redirect;
            } catch (err) {
                console.error(err);
                alert('Terjadi error saat submit');
            }
        }

        btnSubmitFinal.addEventListener('click', () => {
            if (!pickedAction) return;
            submitFinal(pickedAction);
        });

        // =============== UPDATE ELEMEN STATUS (GLOBAL) ===============
        window.__updateElemenStatus = function({
            tab
            , elemenId
            , reviewedCount
            , requiredCount
        }) {
            const selector = `.elemen-accordion-item[data-tab="${tab}"][data-elemen-id="${elemenId}"]`;
            const el = document.querySelector(selector);
            if (!el) return;

            el.dataset.reviewedCount = String(reviewedCount);
            el.dataset.requiredCount = String(requiredCount);

            const badge = el.querySelector('.elemen-status-badge');
            const counter = el.querySelector('.elemen-lkps-counter');

            const isComplete = (requiredCount === 0) ? true : (reviewedCount >= requiredCount);

            if (badge) {
                badge.className = 'badge elemen-status-badge ' + (isComplete ? 'bg-success' : 'bg-warning text-dark');
                badge.textContent = isComplete ? 'Validasi Lengkap' : 'Validasi Belum Lengkap';
            }
            if (counter) counter.textContent = `${reviewedCount}/${requiredCount}`;

            updateTabReviewedInfo();
        };

        // =============== REFRESH STATS (GENERIC) ===============
        async function refreshStats() {
            const url = @json(route('validator.borang.stats', $assignmentId));

            try {
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || 'Gagal ambil statistik');

                const sections = [{
                        key: 'led'
                        , prefix: 'led'
                    }
                    , {
                        key: 'suplemen'
                        , prefix: 'suplemen'
                    }
                    , {
                        key: 'lkps'
                        , prefix: 'lkps'
                    }
                , ];

                sections.forEach(({
                    key
                    , prefix
                }) => {
                    const s = data[key];
                    if (!s) return;

                    const elReviewed = document.getElementById(`count-${prefix}-reviewed`);
                    const elTotal = document.getElementById(`count-${prefix}-total`);
                    const elPercent = document.getElementById(`percent-${prefix}`);
                    const elBar = document.getElementById(`bar-${prefix}`);

                    if (elReviewed) elReviewed.textContent = s.reviewed;
                    if (elTotal) elTotal.textContent = s.total;
                    if (elPercent) elPercent.textContent = s.percentage;
                    if (elBar) elBar.style.width = s.percentage + '%';
                });

                // TOTAL
                document.getElementById('percent-total').textContent = data.percentage;
                document.getElementById('bar-total').style.width = data.percentage + '%';
                document.getElementById('count-total-reviewed').textContent = data.reviewed;
                document.getElementById('count-total').textContent = data.total;

                // Final badge
                const badge = document.getElementById('finalStatusBadge');
                if (data.is_complete) {
                    badge.textContent = 'Validasi Lengkap';
                    badge.className = 'badge bg-success';
                } else {
                    badge.textContent = 'Validasi Belum Lengkap';
                    badge.className = 'badge bg-warning text-dark';
                }
            } catch (err) {
                console.error('refreshStats error:', err);
            }
        }

        // =============== TAB INFO (ELEMN COMPLETE COUNT) ===============
        function updateTabReviewedInfo() {
            const items = document.querySelectorAll(`.elemen-accordion-item[data-tab="${activeTab}"]`);
            let total = 0
                , complete = 0;

            items.forEach(it => {
                total++;
                const req = parseInt(it.dataset.requiredCount || '0', 10);
                const rev = parseInt(it.dataset.reviewedCount || '0', 10);
                const isComplete = (req === 0) ? true : (rev >= req);
                if (isComplete) complete++;
            });

            const info = document.getElementById('tabReviewedInfo');
            if (info) info.textContent = `Elemen lengkap: ${complete}/${total}`;
        }

        // =============== EXPAND / COLLAPSE ALL (ONLY ACTIVE TAB) ===============
        function getCollapsesInTab(tab = activeTab) {
            const paneId = getPaneId(tab);
            return Array.from(document.querySelectorAll(`${paneId} .accordion-collapse`));
        }

        function isAllExpandedInTab(tab = activeTab) {
            const collapses = getCollapsesInTab(tab);
            if (collapses.length === 0) return false;
            return collapses.every(el => el.classList.contains('show'));
        }

        function setAllCollapse(expand, tab = activeTab) {
            const collapses = getCollapsesInTab(tab);
            collapses.forEach(el => {
                const inst = bootstrap.Collapse.getOrCreateInstance(el, {
                    toggle: false
                });
                expand ? inst.show() : inst.hide();
            });
        }

        function updateToggleAllButton() {
            if (!btnToggleAll) return;
            const allExpanded = isAllExpandedInTab(activeTab);

            if (allExpanded) {
                btnToggleAll.className = 'btn btn-outline-secondary btn-sm';
                btnToggleAll.innerHTML = '<i class="bi bi-arrows-collapse"></i> Collapse All';
            } else {
                btnToggleAll.className = 'btn btn-outline-primary btn-sm';
                btnToggleAll.innerHTML = '<i class="bi bi-arrows-expand"></i> Expand All';
            }
        }

        if (btnToggleAll) {
            btnToggleAll.addEventListener('click', () => {
                const allExpanded = isAllExpandedInTab(activeTab);
                setAllCollapse(!allExpanded, activeTab);
                setTimeout(updateToggleAllButton, 50);
            });

            document.addEventListener('shown.bs.collapse', (e) => {
                if (e.target && e.target.classList.contains('accordion-collapse')) updateToggleAllButton();
            });
            document.addEventListener('hidden.bs.collapse', (e) => {
                if (e.target && e.target.classList.contains('accordion-collapse')) updateToggleAllButton();
            });
        }

        // =============== RESET DROPDOWN HELPERS ===============
        function hasReviewItemInTab(tab) {
            const paneId = getPaneId(tab);
            return document.querySelectorAll(`${paneId} .review-item`).length > 0;
        }

        function hasAnyReviewItem() {
            return ['led', 'suplemen', 'lkps'].some(t => hasReviewItemInTab(t));
        }

        function setDropdownItemDisabled(btn, disabled, title) {
            if (!btn) return;
            btn.disabled = disabled;
            btn.classList.toggle('disabled', disabled);
            if (disabled && title) btn.setAttribute('title', title);
            if (!disabled) btn.removeAttribute('title');
        }

        function updateResetDropdownState() {
            const any = hasAnyReviewItem();

            if (btnResetDropdown) {
                btnResetDropdown.disabled = !any;
                btnResetDropdown.classList.toggle('disabled', !any);
                if (!any) btnResetDropdown.setAttribute('title', 'Tidak ada item untuk di-reset');
                else btnResetDropdown.removeAttribute('title');
            }

            const config = [{
                    id: 'reset-active'
                    , enabled: hasReviewItemInTab(activeTab)
                    , title: 'Tab ini kosong'
                }
                , {
                    id: 'reset-led'
                    , enabled: hasReviewItemInTab('led')
                    , title: 'LED kosong'
                }
                , {
                    id: 'reset-suplemen'
                    , enabled: hasReviewItemInTab('suplemen')
                    , title: 'Suplemen kosong'
                }
                , {
                    id: 'reset-lkps'
                    , enabled: hasReviewItemInTab('lkps')
                    , title: 'LKPS kosong'
                }
                , {
                    id: 'reset-all'
                    , enabled: any
                    , title: 'Tidak ada item untuk di-reset'
                }
            , ];

            config.forEach(({
                id
                , enabled
                , title
            }) => {
                setDropdownItemDisabled(document.getElementById(id), !enabled, title);
            });
        }

        // =============== RESET UI LOCAL ===============
        function resetAllInTab(tab) {
            const paneId = getPaneId(tab);

            // 1) reset grade + catatan + status
            const wraps = document.querySelectorAll(`${paneId} .review-item`);
            wraps.forEach(wrap => {
                wrap.querySelectorAll('.grade-btn').forEach(b => b.classList.remove('active'));

                const note = wrap.querySelector('.catatan-input');
                if (note) {
                    note.value = '';
                    note.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                    note.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }

                const status = wrap.querySelector('.status-text');
                if (status) status.innerHTML = '<i class="bi bi-pencil"></i> Belum disimpan';
            });

            // 2) reset badge & counter per elemen
            const elemenItems = document.querySelectorAll(`${paneId} .elemen-accordion-item`);
            elemenItems.forEach(el => {
                const req = parseInt(el.dataset.requiredCount || '0', 10);
                el.dataset.reviewedCount = '0';

                const badge = el.querySelector('.elemen-status-badge');
                const counter = el.querySelector('.elemen-lkps-counter');

                const isComplete = (req === 0);
                if (badge) {
                    badge.className = 'badge elemen-status-badge ' + (isComplete ? 'bg-success' : 'bg-warning text-dark');
                    badge.textContent = isComplete ? 'Validasi Lengkap' : 'Validasi Belum Lengkap';
                }
                if (counter) counter.textContent = `0/${req}`;
            });
        }

        // =============== RESET TO SERVER ===============
        async function resetToServer(target) {
            // target: 'active' | 'led' | 'suplemen' | 'lkps' | 'all'
            const category = (target === 'active') ? activeTab : target;

            // guard: kalau kosong, jangan jalan
            const hasItem = (category === 'all') ? hasAnyReviewItem() : hasReviewItemInTab(category);
            if (!hasItem) {
                alert('Tidak ada item untuk di-reset pada pilihan ini.');
                return;
            }

            const label = (category === 'all') ? 'SEMUA (LED + Suplemen + LKPS)' : labelByTab(category);

            const resetNotes = confirm(`Reset ${label} juga termasuk Catatan Umum? (OK=ya, Cancel=tidak)`);
            const ok = confirm(`Yakin reset ${label}? Ini langsung menghapus review di DB.`);
            if (!ok) return;

            const url = @json(route('validator.borang.reset-review', $assignmentId));

            try {
                const res = await fetch(url, {
                    method: 'POST'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'Accept': 'application/json'
                        , 'X-CSRF-TOKEN': @json(csrf_token())
                    }
                    , body: JSON.stringify({
                        category: category, // 'led'|'suplemen'|'lkps'|'all'
                        reset_notes: resetNotes
                    })
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    alert(data.message || 'Gagal reset');
                    return;
                }

                // reset UI lokal
                if (category === 'all') {
                    ['led', 'suplemen', 'lkps'].forEach(t => resetAllInTab(t));
                } else {
                    resetAllInTab(category);
                }

                // reset catatan umum jika diminta
                if (resetNotes) {
                    if (category === 'all' || category === 'led') document.getElementById('catatan_led').value = '';
                    if (category === 'all' || category === 'suplemen') document.getElementById('catatan_suplemen').value = '';
                    if (category === 'all' || category === 'lkps') document.getElementById('catatan_lkps').value = '';
                }

                await refreshStats();
                await loadMonitoringTable();
                updateTabReviewedInfo();
                updateToggleAllButton();
                updateResetDropdownState();

                alert(data.message || `Reset ${label} berhasil`);
            } catch (err) {
                console.error(err);
                alert('Terjadi error saat reset');
            }
        }

        // bind reset dropdown (single binding)
        if (resetDropdownMenu) {
            resetDropdownMenu.querySelectorAll('button[data-reset-target]').forEach(btn => {
                btn.addEventListener('click', () => {
                    resetToServer(btn.dataset.resetTarget);
                });
            });
        }

        // =============== TAB CHANGE LISTENER ===============
        document.querySelectorAll('#reviewTabs button[data-bs-toggle="tab"]').forEach(btn => {
            btn.addEventListener('shown.bs.tab', (e) => {
                const id = e.target.id;
                activeTab = tabMap[id] ? tabMap[id].tab : 'led';

                const label = document.getElementById('activeTabLabel');
                if (label) label.textContent = 'Tab: ' + (tabMap[id] ? tabMap[id].label : 'LED');

                updateTabReviewedInfo();
                updateToggleAllButton();
                updateResetDropdownState();
            });
        });

        // =============== EVENT DELEGATION: CLICK GRADE ===============
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.review-item .grade-btn');
            if (!btn) return;

            const wrap = btn.closest('.review-item');
            if (!wrap) return;

            wrap.querySelectorAll('.grade-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const status = wrap.querySelector('.status-text');
            if (status) status.innerHTML = '<i class="bi bi-pencil"></i> Belum disimpan';
        });

        // =============== EVENT DELEGATION: SAVE REVIEW ===============
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.review-item .btn-save-review');
            if (!btn) return;

            const wrap = btn.closest('.review-item');
            if (!wrap) return;

            const category = wrap.dataset.category;
            const itemId = parseInt(wrap.dataset.itemId, 10);
            const elemenId = wrap.dataset.elemenId ? parseInt(wrap.dataset.elemenId, 10) : null;

            const active = wrap.querySelector('.grade-btn.active');
            const grade = active ? active.dataset.grade : null;
            const catatanInput = (wrap.querySelector('.catatan-input') || {}).value
            const catatan = catatanInput ? catatanInput : '';

            if (!grade) {
                Swal.fire({
                    icon: 'warning'
                    , title: 'Perhatian'
                    , text: 'Pilih kategori penilaian terlebih dahulu.'
                });
                return;
            }

            const status = wrap.querySelector('.status-text');
            const originalBtn = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
            if (status) status.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';

            try {
                const url = @json(route("validator.borang.update-review", $assignmentId));
                const res = await fetch(url, {
                    method: 'POST'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'Accept': 'application/json'
                        , 'X-CSRF-TOKEN': @json(csrf_token())
                    }
                    , body: JSON.stringify({
                        category: category
                        , item_id: itemId
                        , grade: grade
                        , catatan: catatan
                    })
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    if (status) status.innerHTML = '<i class="bi bi-x-circle text-danger"></i> ' + (data.message || 'Gagal menyimpan');
                    return;
                }

                if (status) status.innerHTML = '<i class="bi bi-check-circle text-success"></i> Tersimpan';

                // update status elemen (badge lengkap/belum lengkap)
                if (category === 'led' || category === 'suplemen') {
                    if (typeof window.__updateElemenStatus === 'function') {
                        window.__updateElemenStatus({
                            tab: category
                            , elemenId: itemId
                            , reviewedCount: 1
                            , requiredCount: 1
                        });
                    }
                }

                // LKPS: hitung ulang indikator dalam elemen (UI-side)
                if (category === 'lkps' && elemenId) {
                    const allInElemen = document.querySelectorAll(`.review-item[data-category="lkps"][data-elemen-id="${elemenId}"]`);
                    const requiredCount = allInElemen.length;
                    let reviewedCount = 0;

                    allInElemen.forEach(x => {
                        const activeGrade = x.querySelector('.grade-btn.active');
                        if (activeGrade) {
                            const statusText = x.querySelector('.status-text');
                            const st = statusText ? statusText.innerText : '';
                            if (st.includes('Tersimpan')) reviewedCount++;
                        }
                    });

                    reviewedCount = Math.min(requiredCount, Math.max(reviewedCount, 1));

                    if (typeof window.__updateElemenStatus === 'function') {
                        window.__updateElemenStatus({
                            tab: 'lkps'
                            , elemenId
                            , reviewedCount
                            , requiredCount
                        });
                    }
                }

                await refreshStats();
                await loadMonitoringTable();
            } catch (err) {
                console.error(err);
                if (status) status.innerHTML = '<i class="bi bi-x-circle text-danger"></i> Error';
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalBtn;
            }
        });

        // =============== AUTO TEST HELPERS ===============
        function pickWeightedGrade() {
            const r = Math.random();
            if (r < 0.85) return 'A';
            if (r < 0.95) return 'B';
            return 'C';
        }

        function randomNoteByGrade(grade) {
            const notesA = [
                "Sudah tepat. Narasi jelas dan eviden mendukung."
                , "Sudah sesuai. Data konsisten dan dapat diverifikasi."
                , "Sudah baik. Struktur rapi dan informasi memadai."
                , "Tepat. Kesesuaian indikator dan bukti pendukung sudah ok."
                , "Baik. Tidak ada catatan signifikan."
            ];
            const notesB = [
                "Kurang lengkap. Mohon tambahkan bukti/tautan pendukung."
                , "Kurang lengkap. Perlu penjelasan lebih detail pada bagian tertentu."
                , "Masih kurang. Mohon lengkapi data/angka agar konsisten."
                , "Kurang lengkap. Perlu rujukan dokumen pendukung yang relevan."
                , "Mohon lengkapi narasi dan pastikan periode data jelas."
            ];
            const notesC = [
                "Perlu diperbaiki. Ada ketidaksesuaian narasi dengan eviden."
                , "Perlu perbaikan. Angka/indikator tidak konsisten, mohon koreksi."
                , "Perlu diperbaiki. Bukti tidak mendukung pernyataan yang ditulis."
                , "Perlu perbaikan. Struktur dan penjelasan belum sesuai ketentuan."
                , "Perlu diperbaiki. Mohon revisi agar selaras dengan dokumen pendukung."
            ];
            const pick = (arr) => arr[Math.floor(Math.random() * arr.length)];
            if (grade === 'A') return pick(notesA);
            if (grade === 'B') return pick(notesB);
            return pick(notesC);
        }

        function buildGeneralNoteFromStats(label, stats) {
            const pct = (x) => stats.total ? Math.round((x / stats.total) * 100) : 0;
            const aPct = pct(stats.A);
            const bPct = pct(stats.B);
            const cPct = pct(stats.C);

            let tone = 'positive';
            if (stats.C > 0 || cPct >= 5) tone = 'critical';
            else if (stats.B > 0 || bPct >= 10) tone = 'mixed';

            if (tone === 'positive') {
                return `${label}: Mayoritas sudah tepat (A ${aPct}%). Secara umum sudah baik dan konsisten, eviden mendukung. Pastikan final check konsistensi angka/rujukan.`;
            }
            if (tone === 'mixed') {
                return `${label}: Umumnya sudah tepat (A ${aPct}%), namun masih ada yang kurang lengkap (B ${bPct}%). Mohon lengkapi bukti pendukung/penjelasan pada item terkait, serta cek konsistensi periode data.`;
            }
            return `${label}: Ditemukan beberapa item perlu perbaikan (C ${cPct}%) dan/atau kurang lengkap (B ${bPct}%). Mohon revisi agar narasi selaras dengan eviden, perbaiki inkonsistensi angka, dan lengkapi dokumen pendukung.`;
        }

        function sleep(ms) {
            return new Promise(r => setTimeout(r, ms));
        }

        async function runAutoTestReview() {
            if (isEnvLocal !== true || izinkanTestLocal !== true) {
                alert('Auto Test hanya boleh dijalankan di ENV local dan izinkanTestLocal=true');
                return;
            }

            // expand semua pane supaya semua review-item ter-render
            ['led', 'suplemen', 'lkps'].forEach(t => {
                document.querySelectorAll(`${getPaneId(t)} .accordion-collapse`).forEach(el => {
                    bootstrap.Collapse.getOrCreateInstance(el, {
                        toggle: false
                    }).show();
                });
            });

            await sleep(200);

            const stats = {
                led: {
                    A: 0
                    , B: 0
                    , C: 0
                    , total: 0
                }
                , suplemen: {
                    A: 0
                    , B: 0
                    , C: 0
                    , total: 0
                }
                , lkps: {
                    A: 0
                    , B: 0
                    , C: 0
                    , total: 0
                }
                , all: {
                    A: 0
                    , B: 0
                    , C: 0
                    , total: 0
                }
            };

            const items = document.querySelectorAll('.review-item');

            for (const wrap of items) {
                const category = wrap.dataset.category;
                const grade = pickWeightedGrade();

                const btnGrade = wrap.querySelector(`.js-grade-btn[data-grade="${grade}"]`);
                if (btnGrade) {
                    btnGrade.click();
                    await sleep(20);
                }

                const note = wrap.querySelector('.js-review-note');
                if (note) {
                    note.value = randomNoteByGrade(grade);
                    note.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                    note.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                    await sleep(20);
                }

                const btnSave = wrap.querySelector('.btn-save-review');
                if (btnSave) {
                    btnSave.click();
                    await sleep(80);
                }

                if (stats[category]) {
                    stats[category][grade]++;
                    stats[category].total++;
                }
                stats.all[grade]++;
                stats.all.total++;
            }

            const catLed = document.getElementById('catatan_led');
            const catSup = document.getElementById('catatan_suplemen');
            const catLkps = document.getElementById('catatan_lkps');
            const catAll = document.getElementById('catatan_validator');

            if (catLed) catLed.value = buildGeneralNoteFromStats('Catatan Umum LED', stats.led);
            if (catSup) catSup.value = buildGeneralNoteFromStats('Catatan Umum Suplemen', stats.suplemen);
            if (catLkps) catLkps.value = buildGeneralNoteFromStats('Catatan Umum LKPS', stats.lkps);

            if (catAll) {
                catAll.value =
                    buildGeneralNoteFromStats('Catatan Validator (Keseluruhan)', stats.all) +
                    `\n\nRingkasan distribusi (TOTAL ${stats.all.total} item): A=${stats.all.A}, B=${stats.all.B}, C=${stats.all.C}.`;
            }

            [catLed, catSup, catLkps, catAll].forEach(el => {
                if (!el) return;
                el.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
                el.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            });

            updateTabReviewedInfo();
            updateToggleAllButton();
            updateResetDropdownState();
            await refreshStats();
            await loadMonitoringTable();

            alert(`Auto Test Review selesai. LED: A${stats.led.A}/B${stats.led.B}/C${stats.led.C}, Suplemen: A${stats.suplemen.A}/B${stats.suplemen.B}/C${stats.suplemen.C}, LKPS: A${stats.lkps.A}/B${stats.lkps.B}/C${stats.lkps.C}`);
        }

        if (btnAutoTestReview) {
            btnAutoTestReview.addEventListener('click', () => runAutoTestReview().catch(console.error));
        }

        if (monitoringCollapse && iconToggle && btnToggleMon) {
            monitoringCollapse.addEventListener('shown.bs.collapse', () => {
                iconToggle.className = 'bi bi-chevron-up';
                const span = btnToggleMon.querySelector('span')
                if (span) span.replaceChildren(document.createTextNode('Sembunyikan'));
            });
            monitoringCollapse.addEventListener('hidden.bs.collapse', () => {
                iconToggle.className = 'bi bi-chevron-down';
                const span = btnToggleMon.querySelector('span')
                if (span) span.replaceChildren(document.createTextNode('Tampilkan'));
            });
        }

        async function loadMonitoringTable() {
            const url = @json(route('validator.borang.summary', $assignmentId));

            const bodyLed = document.getElementById('monitoringBodyLed');
            const bodySup = document.getElementById('monitoringBodySuplemen');
            const bodyLkps = document.getElementById('monitoringBodyLkps');

            // fallback kalau markup belum diganti
            const legacyBody = document.getElementById('monitoringBody');

            const setLoading = (el, cols) => {
                if (!el) return;
                el.innerHTML = `<tr><td colspan="${cols}" class="text-muted">Memuat...</td></tr>`;
            };

            setLoading(bodyLed, 7);
            setLoading(bodySup, 7);
            setLoading(bodyLkps, 7);
            if (legacyBody) legacyBody.innerHTML = `<tr><td colspan="8" class="text-muted">Memuat...</td></tr>`;

            try {
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data ? data.error : 'Gagal ambil summary');

                // rekap: hanya total/reviewed/unreviewed
                const mTotal = document.getElementById('m-total')
                if (mTotal) mTotal.textContent = `Total: ${data.rekap.total}`;
                const mReviewed = document.getElementById('m-reviewed')
                if (mReviewed) mReviewed.textContent = `Reviewed: ${data.rekap.reviewed}`;
                const mUnreviewed = document.getElementById('m-unreviewed')
                if (mUnreviewed) mUnreviewed.textContent = `Belum: ${data.rekap.unreviewed}`;

                const rows = Array.isArray(data.rows) ? data.rows : [];

                const byCat = {
                    led: rows.filter(r => r.category === 'led')
                    , suplemen: rows.filter(r => r.category === 'suplemen')
                    , lkps: rows.filter(r => r.category === 'lkps')
                , };

                const countGrades = (arr) => {
                    const out = {
                        A: 0
                        , B: 0
                        , C: 0
                    };
                    arr.forEach(r => {
                        if (r && (r.grade === 'A' || r.grade === 'B' || r.grade === 'C')) out[r.grade]++;
                    });
                    return out;
                };

                const setText = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = String(val ? val : 0);
                };

                const ledG = countGrades(byCat.led);
                const supG = countGrades(byCat.suplemen);
                const lkpsG = countGrades(byCat.lkps);

                setText('led-a', ledG.A);
                setText('led-b', ledG.B);
                setText('led-c', ledG.C);
                setText('suplemen-a', supG.A);
                setText('suplemen-b', supG.B);
                setText('suplemen-c', supG.C);
                setText('lkps-a', lkpsG.A);
                setText('lkps-b', lkpsG.B);
                setText('lkps-c', lkpsG.C);

                const renderRows = (arr) => {
                    if (!arr.length) {
                        return `<tr><td colspan="7" class="text-muted">Tidak ada data.</td></tr>`;
                    }

                    return arr.map(r => {
                        const status = r.reviewed ?
                            `<span class="badge bg-success">Sudah</span>` :
                            `<span class="badge bg-secondary">Belum</span>`;

                        const grade = r.grade || '-';
                        const gradeBadge =
                            grade === 'A' ? `<span class="badge bg-primary">A</span>` :
                            grade === 'B' ? `<span class="badge bg-warning text-dark">B</span>` :
                            grade === 'C' ? `<span class="badge bg-danger">C</span>` :
                            `<span class="text-muted">-</span>`;

                        const cat = (r.catatan || '').toString();
                        const catShort = cat.length > 70 ? cat.slice(0, 70) + '…' : (cat || '-');

                        const safeTitle = cat.replaceAll('"', '&quot;');

                        return `
          <tr>
            <td>${r.group ?? '-'}</td>
            <td>${r.kode ?? '-'}</td>
            <td>${(r.judul ?? '').toString()}</td>
            <td>${status}</td>
            <td>${gradeBadge}</td>
            <td title="${safeTitle}">${catShort}</td>
            <td>
              <button class="btn btn-sm btn-outline-primary js-jump" data-anchor="${r.anchor}">
                <i class="bi bi-box-arrow-in-right"></i> Lihat detail
              </button>
            </td>
          </tr>
        `;
                    }).join('');
                };

                if (bodyLed) bodyLed.innerHTML = renderRows(byCat.led);
                if (bodySup) bodySup.innerHTML = renderRows(byCat.suplemen);
                if (bodyLkps) bodyLkps.innerHTML = renderRows(byCat.lkps);

                // optional: kalau masih ada table lama
                if (legacyBody) {
                    legacyBody.innerHTML = rows.length ?
                        `<tr><td colspan="8" class="text-muted">Monitoring sudah dipisah per tab.</td></tr>` :
                        `<tr><td colspan="8" class="text-muted">Tidak ada data.</td></tr>`;
                }

            } catch (err) {
                console.error(err);

                const fail = (el, cols) => {
                    if (!el) return;
                    el.innerHTML = `<tr><td colspan="${cols}" class="text-danger">Gagal memuat monitoring.</td></tr>`;
                };

                fail(bodyLed, 7);
                fail(bodySup, 7);
                fail(bodyLkps, 7);
                if (legacyBody) legacyBody.innerHTML = `<tr><td colspan="8" class="text-danger">Gagal memuat monitoring.</td></tr>`;
            }
        }

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-jump');
            if (!btn) return;

            const anchorId = btn.dataset.anchor;
            const header = document.getElementById(anchorId);
            if (!header) return;

            // switch tab utama berdasarkan prefix anchor
            const tabId =
                anchorId.startsWith('h-led-') ? 'tab-led' :
                anchorId.startsWith('h-suplemen-') ? 'tab-suplemen' :
                anchorId.startsWith('h-lkps-') ? 'tab-lkps' : null;

            if (tabId) {
                const tabBtn = document.getElementById(tabId);
                if (tabBtn) bootstrap.Tab.getOrCreateInstance(tabBtn).show();
            }

            // buka collapse terkait
            const item = header.closest('.accordion-item');
            const collapse = item ? item.querySelector('.accordion-collapse') : null;
            if (collapse) bootstrap.Collapse.getOrCreateInstance(collapse, {
                toggle: false
            }).show();

            header.scrollIntoView({
                behavior: 'smooth'
                , block: 'start'
            });
        });

        // =============== INIT UI STATE ===============
        loadMonitoringTable();
        updateTabReviewedInfo();
        updateToggleAllButton();
        updateResetDropdownState();
    });

</script>
@endpush

@endsection
