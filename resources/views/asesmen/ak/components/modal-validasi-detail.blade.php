<div class="modal fade" id="modalValidasiDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clipboard-check"></i> Validasi Penilaian Elemen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Loading State -->
                <div id="loadingDetail" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3">Memuat data...</p>
                </div>

                <!-- Content Container -->
                <div id="detailContainer" style="display: none;">
                    <!-- Elemen Info -->
                    <div class="card mb-4 bg-light">
                        <div class="card-body">
                            <h6 class="mb-3">
                                <i class="bi bi-info-circle"></i> Informasi Elemen
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Kriteria:</small>
                                    <p class="mb-2" id="detailKriteria">-</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Kode Elemen:</small>
                                    <p class="mb-2"><strong id="detailKodeElemen">-</strong></p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Pernyataan Elemen:</small>
                                    <p id="detailElemenStandar">-</p>
                                </div>
                            </div>

                            <!-- Indikator List -->
                            <div class="mt-3">
                                <h6 class="mb-2">
                                    <i class="bi bi-list-ul"></i> Indikator Penilaian
                                </h6>
                                <div id="detailIndikator" class="small">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Perbandingan Penilaian Asesor -->
                    <div class="row">
                        <!-- Asesor 1 -->
                        <div class="col-md-6">
                            <div class="card border-primary h-100" id="rowAsesor1">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2" id="avatar1">A1</div>
                                        <div>
                                            <h6 class="mb-0" id="namaAsesor1">Asesor 1</h6>
                                            <small>Penilaian Pertama</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Skor:</label>
                                        <div id="skorAsesor1Container">-</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Kategori:</label>
                                        <div>
                                            <span class="badge" id="kategoriAsesor1">-</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label fw-bold">Komentar/Justifikasi:</label>
                                        <div class="alert alert-light alert-permanent alert-dismissible" id="komentarAsesor1">-</div>
                                    </div>

                                    <!-- ✅ TAMBAH: Radio Button untuk Select Asesor -->
                                    <div class="mt-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="asesor_target_revisi" id="radioAsesor1" value="asesor1_id">
                                            <label class="form-check-label text-danger" for="radioAsesor1">
                                                <i class="bi bi-arrow-repeat"></i>
                                                <strong>Minta asesor ini merevisi</strong>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Asesor 2 -->
                        <div class="col-md-6">
                            <div class="card border-warning h-100" id="rowAsesor2">
                                <div class="card-header bg-warning">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2 bg-warning" id="avatar2">A2</div>
                                        <div>
                                            <h6 class="mb-0" id="namaAsesor2">Asesor 2</h6>
                                            <small>Penilaian Kedua</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Skor:</label>
                                        <div id="skorAsesor2Container">-</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Kategori:</label>
                                        <div>
                                            <span class="badge" id="kategoriAsesor2">-</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label fw-bold">Komentar/Justifikasi:</label>
                                        <div class="alert alert-light alert-permanent alert-dismissible" id="komentarAsesor2">-</div>
                                    </div>

                                    <!-- ✅ TAMBAH: Radio Button untuk Select Asesor -->
                                    <div class="mt-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="asesor_target_revisi" id="radioAsesor2" value="asesor2_id">
                                            <label class="form-check-label text-danger" for="radioAsesor2">
                                                <i class="bi bi-arrow-repeat"></i>
                                                <strong>Minta asesor ini merevisi</strong>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ TAMBAH: Alert Perbedaan -->
                    <div id="rowDifference" class="alert alert-warning alert-permanent alert-dismissible mt-3" style="display: none;">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Perbedaan Penilaian Terdeteksi!</strong>
                        <span id="differenceMessage"></span>
                    </div>

                    <!-- Form Validasi -->
                    <div class="card mt-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-check-circle"></i> Form Validasi
                            </h6>
                        </div>
                        <div class="card-body">
                            <form id="formValidasi">
                                <input type="hidden" id="validasiElemenId" name="elemen_id">
                                <input type="hidden" id="validasiAsesor1Id" name="asesor1_id">
                                <input type="hidden" id="validasiAsesor2Id" name="asesor2_id">

                                <!-- Quick Select Buttons -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-lightning"></i> Quick Select:
                                    </label>
                                    <div class="btn-group w-100">
                                        <button type="button" class="btn btn-outline-primary" id="btnSelectAsesor1">
                                            <i class="bi bi-1-circle"></i> Pilih Skor Asesor 1
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" id="btnSelectAsesor2">
                                            <i class="bi bi-2-circle"></i> Pilih Skor Asesor 2
                                        </button>
                                        <button type="button" class="btn btn-outline-info" id="btnSelectAverage">
                                            <i class="bi bi-calculator"></i> Rata-rata
                                        </button>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                Status Validasi <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="statusValidasi" name="status" required>
                                                <option value="">-- Pilih Status --</option>
                                                <option value="validated">✅ Disetujui</option>
                                                <option value="revision_required">⚠️ Perlu Revisi</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                Skor Final <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="skorFinal" name="skor_final" required>
                                                <option value="">-- Pilih Skor --</option>
                                                <option value="0">0 - Not Met</option>
                                                <option value="1">1 - Not Met</option>
                                                <option value="2">2 - Weakness</option>
                                                <option value="3">3 - Met</option>
                                                <option value="4">4 - Exceeding</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        Catatan Validator
                                        <span class="text-danger" id="labelCatatanRequired" style="display: none;">*</span>
                                    </label>
                                    <textarea class="form-control" id="catatanValidator" name="catatan_validator" rows="4" placeholder="Berikan catatan validasi atau alasan revisi..."></textarea>
                                    <small class="text-muted">
                                        Catatan wajib diisi jika meminta revisi
                                    </small>
                                </div>

                                <!-- ✅ TAMBAH: Alert untuk Revisi -->
                                <div id="alertRevisiInfo" class="alert alert-info" style="display: none;">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Info:</strong> Silakan pilih asesor mana yang harus merevisi penilaiannya dengan mencentang radio button di atas.
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
                    <i class="bi bi-arrow-repeat"></i> Minta Revisi
                </button>
                <button type="button" class="btn btn-success" id="btnSaveValidasi">
                    <i class="bi bi-check-circle"></i> Setujui
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .avatar-circle-sm {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
    }

    .skor-display {
        font-size: 32px;
        font-weight: 700;
        padding: 10px 20px;
        border-radius: 8px;
        display: inline-block;
        min-width: 80px;
    }

    #rowAsesor1.highlight {
        background: #e3f2fd !important;
        border-left: 4px solid #2196f3;
    }

    #rowAsesor2.highlight {
        background: #fff3e0 !important;
        border-left: 4px solid #ff9800;
    }

</style>
@endpush

@push('scripts')
<script>
    // This script is included in the main page
    // See validator-modal-handler.js for the full implementation

</script>
@endpush
