{{-- resources/views/validator/borang/show.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Review LED - ' . $pengajuan->nomor_pengajuan)

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

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>
                <i class="bi bi-clipboard-check"></i>
                Review LED
            </h2>
            <p class="text-muted mb-0">
                {{ $pengajuan->studyProgram->name }} - {{ $pengajuan->tahun_akreditasi }}
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

    {{-- Dokumen yang diupload Prodi --}}
    <div class="card mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-folder2-open"></i> Dokumen dari Prodi
            </h5>
            <small class="text-muted">File terbaru</small>
        </div>

        <div class="card-body">
            <div class="row g-3">

                {{-- LED --}}
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex align-items-start gap-3">

                            {{-- Icon --}}
                            <i class="bi {{ !empty($uploadedFiles['led']) ? $uploadedFiles['led']->file_icon_class : 'bi-file-earmark' }} fs-4 flex-shrink-0"></i>

                            {{-- Text --}}
                            <div class="flex-grow-1">
                                <div class="fw-bold mb-1">LED</div>

                                @if(!empty($uploadedFiles['led']))
                                <div class="small fw-semibold text-break">
                                    {{ $uploadedFiles['led']->original_filename }}
                                </div>
                                <div class="text-muted small">
                                    {{ $uploadedFiles['led']->file_size_formatted }} •
                                    {{ $uploadedFiles['led']->created_at->diffForHumans() }}
                                </div>
                                @else
                                <div class="text-muted small">Belum ada file LED diupload.</div>
                                @endif
                            </div>

                            {{-- Button --}}
                            @if(!empty($uploadedFiles['led']) && $uploadedFiles['led']->download_url)
                            <div class="flex-shrink-0">
                                <a class="btn btn-sm btn-primary" href="{{ $uploadedFiles['led']->download_url }}" target="_blank" rel="noopener">
                                    <i class="bi bi-download"></i> Buka
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Suplemen --}}
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex align-items-start gap-3">

                            {{-- Icon --}}
                            <i class="bi {{ !empty($uploadedFiles['suplemen']) ? $uploadedFiles['suplemen']->file_icon_class : 'bi-file-earmark' }} fs-4 flex-shrink-0"></i>

                            {{-- Text --}}
                            <div class="flex-grow-1">
                                <div class="fw-bold mb-1">Suplemen</div>

                                @if(!empty($uploadedFiles['suplemen']))
                                <div class="small fw-semibold text-break">
                                    {{ $uploadedFiles['suplemen']->original_filename }}
                                </div>
                                <div class="text-muted small">
                                    {{ $uploadedFiles['suplemen']->file_size_formatted }} •
                                    {{ $uploadedFiles['suplemen']->created_at->diffForHumans() }}
                                </div>
                                @else
                                <div class="text-muted small">Belum ada file suplemen diupload.</div>
                                @endif
                            </div>

                            {{-- Button --}}
                            @if(!empty($uploadedFiles['suplemen']) && $uploadedFiles['suplemen']->download_url)
                            <div class="flex-shrink-0">
                                <a class="btn btn-sm btn-info text-white" href="{{ $uploadedFiles['suplemen']->download_url }}" target="_blank" rel="noopener">
                                    <i class="bi bi-download"></i> Buka
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- LKPS --}}
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex align-items-start gap-3">

                            {{-- Icon --}}
                            <i class="bi {{ !empty($uploadedFiles['lkps']) ? $uploadedFiles['lkps']->file_icon_class : 'bi-file-earmark' }} fs-4 flex-shrink-0"></i>

                            {{-- Text --}}
                            <div class="flex-grow-1">
                                <div class="fw-bold mb-1">LKPS</div>

                                @if(!empty($uploadedFiles['lkps']))
                                <div class="small fw-semibold text-break">
                                    {{ $uploadedFiles['lkps']->original_filename }}
                                </div>
                                <div class="text-muted small">
                                    {{ $uploadedFiles['lkps']->file_size_formatted }} •
                                    {{ $uploadedFiles['lkps']->created_at->diffForHumans() }}
                                </div>
                                @else
                                <div class="text-muted small">Belum ada file LKPS diupload.</div>
                                @endif
                            </div>

                            {{-- Button --}}
                            @if(!empty($uploadedFiles['lkps']) && $uploadedFiles['lkps']->download_url)
                            <div class="flex-shrink-0">
                                <a class="btn btn-sm btn-success" href="{{ $uploadedFiles['lkps']->download_url }}" target="_blank" rel="noopener">
                                    <i class="bi bi-download"></i> Buka
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

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
                        • {{ $uploadedFiles['pengesahan']->file_size_formatted }}
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

    {{-- Review Content --}}
    <div class="row">
        <div class="col-12">

            {{-- Global Controls --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnExpandAll">
                        <i class="bi bi-arrows-expand"></i> Expand All
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCollapseAll">
                        <i class="bi bi-arrows-collapse"></i> Collapse All
                    </button>
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
                                $badgeText = $isReviewed ? 'Lengkap' : 'Belum Lengkap';
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
                    $badgeText = $isComplete ? 'Lengkap' : 'Belum Lengkap';
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
                                $itemBadgeText = $isReviewedItem ? 'Lengkap' : 'Belum Lengkap';
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
                    <div class="alert alert-secondary">
                        Dataset Suplemen untuk jenjang <strong>{{ $pengajuan->studyProgram->degreeLevel->code }}</strong> belum tersedia.
                    </div>
                    @endforelse
                </div>

                {{-- =========================
           PANE LKPS (Kuantitatif)
      ========================== --}}
                <div class="tab-pane fade" id="pane-lkps" role="tabpanel" aria-labelledby="tab-lkps">
                    @foreach($kriterias as $kriteria)
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">{{ $kriteria->kode_kriteria }} - {{ $kriteria->nama_kriteria }}</h5>
                        </div>

                        <div class="card-body">
                            <div class="accordion" id="acc-lkps-{{ $kriteria->id }}">
                                @foreach($kriteria->elemenStandar as $elemen)

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
                                $badgeText = $isComplete ? 'Lengkap' : 'Belum Lengkap';
                                @endphp

                                <div class="accordion-item elemen-accordion-item" data-tab="lkps" data-elemen-id="{{ $elemen->id }}" data-required-count="{{ $required }}" data-reviewed-count="{{ $reviewedCount }}">
                                    <h2 class="accordion-header" id="h-lkps-{{ $elemen->id }}">
                                        <button class="accordion-button collapsed d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#c-lkps-{{ $elemen->id }}" aria-expanded="false" aria-controls="c-lkps-{{ $elemen->id }}">
                                            <span class="badge bg-light text-dark">{{ $elemen->kode_elemen }}</span>
                                            <span class="flex-grow-1"><strong>{{ $elemen->pernyataan_elemen }}</strong></span>
                                            <span class="badge {{ $badgeClass }} elemen-status-badge">{{ $badgeText }}</span>
                                            @if($required > 0)
                                            <span class="badge bg-dark ms-2 elemen-lkps-counter">{{ $reviewedCount }}/{{ $required }}</span>
                                            @else
                                            <span class="badge bg-secondary ms-2">Tidak ada indikator kuantitatif</span>
                                            @endif
                                        </button>
                                    </h2>
                                    <div id="c-lkps-{{ $elemen->id }}" class="accordion-collapse collapse" aria-labelledby="h-lkps-{{ $elemen->id }}" data-bs-parent="#acc-lkps-{{ $kriteria->id }}">
                                        <div class="accordion-body">
                                            @if($required === 0)
                                            <div class="alert alert-secondary mb-0">
                                                Tidak ada indikator kuantitatif untuk elemen ini.
                                            </div>
                                            @else
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
                                                    'elemenId' => $elemen->id, // untuk update counter per-elemen
                                                    ])
                                                </div>
                                            </div>
                                            @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
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
                        {{ $validation->isCompletelyReviewed() ? 'Review Lengkap' : 'Review Belum Lengkap' }}
                    </span>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Catatan Umum LED</label>
                            <textarea id="catatan_led" class="form-control" rows="3" placeholder="Catatan umum untuk LED...">{{ $validation->catatan_led }}</textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Catatan Umum Suplemen</label>
                            <textarea id="catatan_suplemen" class="form-control" rows="3" placeholder="Catatan umum untuk Suplemen...">{{ $validation->catatan_suplemen }}</textarea>
                        </div>
                        <div class="col-md-4 mb-3">
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
                            <i class="bi bi-check-circle"></i> Pilih Approve
                        </button>

                        <button type="button" class="btn btn-outline-warning" id="btnPickRevision">
                            <i class="bi bi-exclamation-triangle"></i> Pilih Request Revision
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
        const ASSIGNMENT_ID = @json($assignmentId);
        // =============== PICK ACTION (NO SUBMIT) ===============
        let pickedAction = null; // 'approve' | 'revision'

        const btnPickApprove = document.getElementById('btnPickApprove');
        const btnPickRevision = document.getElementById('btnPickRevision');
        const pickedBadge = document.getElementById('pickedActionBadge');
        const btnSubmitFinal = document.getElementById('btnSubmitFinal');

        renderPickedAction();

        // =============== TAB STATE ===============
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
        };

        document.querySelectorAll('#reviewTabs button[data-bs-toggle="tab"]').forEach(btn => {
            btn.addEventListener('shown.bs.tab', (e) => {
                const id = e.target.id;
                activeTab = tabMap[id] ? tabMap[id].tab : 'led';
                document.getElementById('activeTabLabel').textContent = 'Tab: ' + (tabMap[id] ? tabMap[id].label : 'LED');
                updateTabReviewedInfo();
            });
        });

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
            document.getElementById('tabReviewedInfo').textContent = `Elemen lengkap: ${complete}/${total}`;
        }
        updateTabReviewedInfo();

        // =============== EXPAND / COLLAPSE ALL (ONLY ACTIVE TAB) ===============
        function setAllCollapse(expand) {
            const paneId = activeTab === 'led' ? '#pane-led' : (activeTab === 'suplemen' ? '#pane-suplemen' : '#pane-lkps');
            document.querySelectorAll(`${paneId} .accordion-collapse`).forEach(el => {
                const inst = bootstrap.Collapse.getOrCreateInstance(el, {
                    toggle: false
                });
                expand ? inst.show() : inst.hide();
            });
        }

        document.getElementById('btnExpandAll').addEventListener('click', () => setAllCollapse(true));
        document.getElementById('btnCollapseAll').addEventListener('click', () => setAllCollapse(false));

        // =============== FINAL SUBMIT (JSON) ===============
        async function submitFinal(action) {
            const msg = action === 'approve' ?
                'Approve validasi? Semua item harus grade A.' :
                'Request revision? Item dengan grade B/C akan dikirim ke prodi.';
            if (!confirm(msg)) return;

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
                    alert(data.message || 'Gagal submit');
                    return;
                }

                alert(data.message || 'Berhasil');
                if (data.redirect) window.location.href = data.redirect;
            } catch (err) {
                console.error(err);
                alert('Terjadi error saat submit');
            }
        }

        // helper global dipakai partial utk update status elemen
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
                badge.textContent = isComplete ? 'Lengkap' : 'Belum Lengkap';
            }
            if (counter) {
                counter.textContent = `${reviewedCount}/${requiredCount}`;
            }

            updateTabReviewedInfo();
        };

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
                pickedBadge.textContent = 'Aksi terpilih: APPROVE';
                pickedBadge.className = 'badge bg-success';
                btnPickApprove.className = 'btn btn-success';
                btnPickRevision.className = 'btn btn-outline-warning';
            } else {
                pickedBadge.textContent = 'Aksi terpilih: REQUEST REVISION';
                pickedBadge.className = 'badge bg-warning text-dark';
                btnPickRevision.className = 'btn btn-warning';
                btnPickApprove.className = 'btn btn-outline-success';
            }
        }

        btnPickApprove.addEventListener('click', () => {
            pickedAction = 'approve';
            renderPickedAction();
        });

        btnPickRevision.addEventListener('click', () => {
            pickedAction = 'revision';
            renderPickedAction();
        })

        // =============== FINAL SUBMIT (ONLY HERE) ===============
        btnSubmitFinal.addEventListener('click', () => {
            if (!pickedAction) return;
            submitFinal(pickedAction); // gunakan fungsi submitFinal yang sudah ada
        });
    });

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

            // LED
            document.getElementById('count-led-reviewed').textContent = data.led.reviewed;
            document.getElementById('count-led-total').textContent = data.led.total;
            document.getElementById('percent-led').textContent = data.led.percentage;
            document.getElementById('bar-led').style.width = data.led.percentage + '%';

            // Suplemen
            document.getElementById('count-suplemen-reviewed').textContent = data.suplemen.reviewed;
            document.getElementById('count-suplemen-total').textContent = data.suplemen.total;
            document.getElementById('percent-suplemen').textContent = data.suplemen.percentage;
            document.getElementById('bar-suplemen').style.width = data.suplemen.percentage + '%';

            // LKPS
            document.getElementById('count-lkps-reviewed').textContent = data.lkps.reviewed;
            document.getElementById('count-lkps-total').textContent = data.lkps.total;
            document.getElementById('percent-lkps').textContent = data.lkps.percentage;
            document.getElementById('bar-lkps').style.width = data.lkps.percentage + '%';

            // TOTAL
            document.getElementById('percent-total').textContent = data.percentage;
            document.getElementById('bar-total').style.width = data.percentage + '%';
            document.getElementById('count-total-reviewed').textContent = data.reviewed;
            document.getElementById('count-total').textContent = data.total;

            // Final badge
            const badge = document.getElementById('finalStatusBadge');
            if (data.is_complete) {
                badge.textContent = 'Review Lengkap';
                badge.className = 'badge bg-success';
            } else {
                badge.textContent = 'Review Belum Lengkap';
                badge.className = 'badge bg-warning text-dark';
            }

        } catch (err) {
            console.error('refreshStats error:', err);
        }
    }

</script>
@endpush
@endsection
