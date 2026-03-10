@php
$pengajuanId = $pengajuan->id;
$allowed = [
\App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
\App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
];

$log = $pengajuan->latestRelevantStatusLog($allowed);
$isShowHasilValidasiBorang = in_array($log?->status_to,$allowed);
@endphp

@if ($isShowHasilValidasiBorang)
<div class="accordion mb-4" id="validationAccordion">
    <div class="accordion-item border-0 border-secondary shadow-sm">

        <h2 class="accordion-header" id="headingValidation">
            <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseValidation" aria-expanded="false" aria-controls="collapseValidation">

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center w-100 me-3">
                    <div class="my-2">
                        <i class="bi bi-clipboard-check"></i>
                        <strong>Hasil Validasi Dokumen</strong>
                    </div>
                    <span class="badge bg-secondary" id="validationBadge">
                        Memuat...
                    </span>
                </div>

            </button>
        </h2>

        <div id="collapseValidation" class="accordion-collapse collapse" aria-labelledby="headingValidation" data-bs-parent="#validationAccordion">

            <div class="accordion-body">

                <!-- LOADING -->
                <div id="validationLoading" class="text-muted">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Memuat data validasi...
                </div>

                <!-- CONTENT -->
                <div id="validationContent" class="d-none">

                    <div class="mb-2">
                        <small class="text-muted">Validator</small>
                        <div class="fw-semibold" id="validatorName">-</div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <div class="small text-muted">LED</div>
                                <div class="fw-bold" id="valLedCount">-</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <div class="small text-muted">Suplemen</div>
                                <div class="fw-bold" id="valSuplemenCount">-</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <div class="small text-muted">LKPS</div>
                                <div class="fw-bold" id="valLkpsCount">-</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Total Progres</small>
                        <div class="progress">
                            <div class="progress-bar" id="valTotalBar" style="width:0%"></div>
                        </div>
                        <div class="small text-muted mt-1">
                            <span id="valTotalText">0%</span> • terakhir update
                            <span id="valUpdatedAt">-</span>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-2">
                        <i class="bi bi-list-check"></i> Poin Revisi
                    </h6>

                    <div id="valRevisionList" class="d-none"></div>
                    <div id="valRevisionEmpty" class="text-muted d-none">
                        Tidak ada poin revisi.
                    </div>

                    <hr>

                    <div class="mb-2">
                        <small class="text-muted">Catatan Validator (Keseluruhan)</small>
                        <div class="border rounded p-2 bg-white" id="valNoteAll">-</div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Catatan LED</small>
                        <div class="border rounded p-2 bg-white" id="valNoteLed">-</div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Catatan Suplemen</small>
                        <div class="border rounded p-2 bg-white" id="valNoteSuplemen">-</div>
                    </div>

                    <div>
                        <small class="text-muted">Catatan LKPS</small>
                        <div class="border rounded p-2 bg-white" id="valNoteLkps">-</div>
                    </div>

                </div>

                <!-- EMPTY -->
                <div id="validationEmpty" class="d-none text-muted">
                    Belum ada hasil validasi.
                </div>

                <!-- ERROR -->
                <div id="validationError" class="d-none alert alert-danger alert-permanent">
                    Gagal memuat hasil validasi.
                </div>

            </div>
        </div>

    </div>
</div>
@endif


@push('scripts')
<script src="{{ asset('assets/js/validasi-dokumen.js') }}"></script>
<script>
    const showHasilValidasiBorang = @json($isShowHasilValidasiBorang);
    const urlValidationSummary = @json(route('pengajuan.borang.validation-summary', $pengajuanId));
    const urlValidationDetails = @json(route('pengajuan.borang.validation-details', $pengajuanId));
    if (showHasilValidasiBorang) {
        fetchValidationSummary(urlValidationSummary).then(() => fetchValidationDetails(urlValidationDetails)).catch(console.error);
    }

</script>
@endpush
