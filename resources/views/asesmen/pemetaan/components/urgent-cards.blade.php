{{-- resources/views/asesmen/pemetaan/components/urgent-cards.blade.php --}}
@push('styles')
<style>
    /* ✅ DataTables Custom Styling */
    #programsDataTable_wrapper .dataTables_filter {
        display: none;
        /* Hide default search, we use sidebar search */
    }

    #programsDataTable_wrapper .dataTables_length {
        padding: 10px;
    }

    #programsDataTable_wrapper .dataTables_info {
        padding: 10px;
    }

    #programsDataTable_wrapper .dataTables_paginate {
        padding: 10px;
    }

    #programsDataTable tbody tr {
        transition: all 0.2s ease;
    }

    #programsDataTable tbody tr:hover {
        background-color: #f8f9fa !important;
    }

    #programsDataTable tbody tr.table-warning {
        background-color: #fff3cd !important;
    }

    #programsDataTable_processing {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* ✅ Urgent Section Styling */
    #urgentSection {
        margin-bottom: 1.5rem;
    }

    #urgentSection .card {
        border-left: 4px solid #ffc107;
    }

    /* ✅ Smooth DataTable Loading */
    .dataTables_wrapper {
        position: relative;
    }

    .dataTables_wrapper.loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
        z-index: 999;
    }

</style>
@endpush

{{-- Accordion Urgent Programs --}}
<div class="accordion mb-4" id="urgentAccordion">

    <div class="accordion-item">
        <h2 class="accordion-header" id="urgentHeading">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#urgentCollapse" aria-expanded="false" aria-controls="urgentCollapse">
                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                Program Studi Mendekati Kedaluwarsa ({{ $urgentPrograms->count() }})
            </button>
        </h2>

        <div id="urgentCollapse" class="accordion-collapse collapse" aria-labelledby="urgentHeading" data-bs-parent="#urgentAccordion">
            <div class="accordion-body">
                @if($urgentPrograms->count() > 0)
                <div class="row">
                    @foreach($urgentPrograms as $program)
                    @php
                    $daysLeft = floor(now()->diffInDays($program->tanggal_kedaluwarsa, false));
                    @endphp
                    <div class="col-md-6 mb-3">
                        <div class="card timeline-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $program->name }}</h6>
                                        <small class="text-muted">
                                            {{ $program->degreeLevel->name }} - {{ $program->university->name }}
                                        </small>
                                    </div>
                                    @if($program->peringkat_akreditasi)
                                    <span class="peringkat-badge peringkat-{{ strtolower(str_replace(' ', '-', $program->peringkat_akreditasi)) }}">
                                        {{ $program->peringkat_akreditasi }}
                                    </span>
                                    @endif
                                </div>
                                <div class="alert alert-warning alert-permanent mb-2 py-2">
                                    <small>
                                        <i class="bi bi-clock"></i>
                                        <strong>Kedaluwarsa:</strong> {{ \App\Libraries\Date::tglIndo($program->tanggal_kedaluwarsa) }}
                                        ({{ $daysLeft }} hari lagi)
                                    </small>
                                </div>
                                <a href="{{ route('de.pemetaan.show', $program->id) }}" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-eye"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> Tidak ada program studi yang segera kedaluwarsa dalam 6 bulan ke depan.
                </div>
            </div>
        </div>
    </div>
</div>
@endif
