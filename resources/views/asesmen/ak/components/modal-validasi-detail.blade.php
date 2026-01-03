<div class="modal fade" id="modalValidasiDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clipboard-check"></i> Validasi Penilaian Elemen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                {{-- Loading State --}}
                <div id="loadingDetail" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 text-muted">Memuat detail penilaian...</p>
                </div>

                {{-- Content Container --}}
                <div id="detailContainer" style="display: none;">
                    {{-- Elemen Info --}}
                    <div class="card mb-3 bg-light">
                        <div class="card-body">
                            <h6 class="mb-3">
                                <i class="bi bi-info-circle"></i> Informasi Elemen
                            </h6>
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <small class="text-muted">Kriteria:</small>
                                    <div id="detailKriteria" class="fw-semibold">-</div>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <small class="text-muted">Kode Elemen:</small>
                                    <div><strong id="detailKodeElemen">-</strong></div>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <small class="text-muted">Pernyataan Elemen:</small>
                                    <div id="detailElemenStandar">-</div>
                                </div>
                                <div class="col-md-12">
                                    <small class="text-muted">Indikator:</small>
                                    <div id="detailIndikator">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Penilaian dari Semua Asesor (Dynamic Grid) --}}
                    <div class="row mb-3" id="penilaianAsesorsContainer">
                        {{-- Will be populated by JavaScript dynamically --}}
                    </div>

                    {{-- Difference Alert --}}
                    <div id="rowDifference" class="alert alert-warning alert-permanent" style="display: none;">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Perhatian:</strong>
                        <span id="differenceMessage"></span>
                    </div>

                    {{-- Form Validasi --}}
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-check-circle"></i> Form Validasi
                            </h6>
                        </div>
                        <div class="card-body">
                            <form id="formValidasi">
                                <input type="hidden" id="validasiElemenId">

                                {{-- Quick Select Buttons (Dynamic) --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-lightning"></i> Quick Select Kategori:
                                    </label>
                                    <div class="btn-group w-100" role="group" id="quickSelectButtons">
                                        {{-- Populated by JS --}}
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">
                                            Status Validasi <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="statusValidasi" required>
                                            <option value="">-- Pilih Status --</option>
                                            <option value="validated">✅ Setujui Penilaian</option>
                                            <option value="revision_required">⚠️ Minta Revisi</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">
                                            Preferensi Kategori
                                            <span class="text-danger" id="labelSkorRequired">*</span>
                                        </label>
                                        <select class="form-select" id="skorFinal">
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach ($jenjangs as $jenjang)
                                            <option value="{{ $jenjang->skor }}">{{ $jenjang->skor }} - {{ $jenjang->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Revision Section (shown when revision_required selected) --}}
                                <div id="revisionSection" style="display: none;">
                                    <div class="alert alert-warning alert-permanent mb-3" id="alertRevisiInfo">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <strong>Pilih asesor yang harus merevisi penilaian:</strong>
                                    </div>

                                    <div class="row" id="asesorCheckboxes">
                                        {{-- Populated by JS --}}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        Catatan Validator
                                        <span class="text-danger" id="labelCatatanRequired" style="display: none;">*</span>
                                    </label>
                                    <textarea class="form-control" id="catatanValidator" rows="4" placeholder="Berikan catatan validasi atau alasan revisi..."></textarea>
                                    <small class="text-muted">
                                        Catatan wajib diisi jika meminta revisi
                                    </small>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
                <button type="button" class="btn btn-warning" id="btnSaveRevision" style="display: none;">
                    <i class="bi bi-arrow-counterclockwise"></i> Minta Revisi
                </button>
                <button type="button" class="btn btn-success" id="btnSaveValidasi">
                    <i class="bi bi-check-circle"></i> Setujui Penilaian
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .asesor-card {
        transition: all 0.3s ease;
    }

    .asesor-card.highlight {
        background: #fff3e0;
        border-color: #ff9800 !important;
        border-width: 3px !important;
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
    }

    .asesor-card-header {
        padding: 10px 15px;
    }

    .avatar-circle-modal {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 16px;
    }

    .skor-display {
        font-size: 28px;
        font-weight: 700;
        padding: 8px 16px;
        border-radius: 8px;
        display: inline-block;
        min-width: 70px;
        text-align: center;
    }

</style>
@endpush
