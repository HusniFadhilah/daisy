{{-- resources\views\asesmen\ak\components\modal-detail-validasi.blade.php --}}
<div class="modal fade" id="modalDetailValidasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-eye"></i> Detail Hasil Validasi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                {{-- Loading State --}}
                <div id="loadingDetailValidasi" class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-3">Memuat data...</p>
                </div>

                {{-- Content --}}
                <div id="contentDetailValidasi" style="display: none;">
                    {{-- Elemen Info --}}
                    <div class="card mb-3 bg-light">
                        <div class="card-body">
                            <h6 class="mb-3">
                                <i class="bi bi-info-circle"></i> Informasi Elemen
                            </h6>
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <small class="text-muted">Kriteria:</small>
                                    <div id="detailValidasiKriteria" class="fw-semibold">-</div>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <small class="text-muted">Kode Elemen:</small>
                                    <p class="mb-0"><strong id="detailValidasiKode">-</strong></p>
                                </div>
                                <div class="col-md-12">
                                    <small class="text-muted">Pernyataan Elemen:</small>
                                    <p id="detailValidasiElemen" class="mb-0">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hasil Validasi --}}
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-check-circle"></i> Hasil Validasi
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <small class="text-muted">Status:</small>
                                    <p id="detailValidasiStatus" class="mb-0">-</p>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-none detailValidasiSkor">Kategori:</small>
                                    <p id="detailValidasiSkor" class="mb-0 d-none detailValidasiSkor">
                                        <span class="badge" id="badgeSkorFinal">-</span>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Validator:</small>
                                    <p id="detailValidasiValidator" class="mb-0">-</p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">Tanggal Validasi:</small>
                                <p id="detailValidasiTanggal" class="mb-0">-</p>
                            </div>

                            <div class="mb-0">
                                <small class="text-muted">Catatan Validator:</small>
                                <div class="alert alert-light alert-permanent mt-2" id="detailValidasiCatatan">-</div>
                            </div>
                        </div>
                    </div>

                    {{-- Penilaian dari Semua Asesor (Dynamic) --}}
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-people"></i> Penilaian Asesor
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="detailPenilaianAsesor">
                                {{-- Will be populated by JavaScript --}}
                            </div>
                        </div>
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

@push('styles')
<style>
    .asesor-penilaian-card {
        border-left: 4px solid;
        margin-bottom: 15px;
    }

    .avatar-circle-detail {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
    }

</style>
@endpush
