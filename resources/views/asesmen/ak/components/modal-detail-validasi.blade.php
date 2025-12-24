<div class="modal fade" id="modalDetailValidasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-eye"></i> Detail Hasil Validasi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Loading State -->
                <div id="loadingDetailValidasi" class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Memuat data...</p>
                </div>

                <!-- Content -->
                <div id="contentDetailValidasi" style="display: none;">
                    <!-- Elemen Info -->
                    <div class="card mb-3 bg-light">
                        <div class="card-body">
                            <h6 class="mb-2">📋 Elemen Standar</h6>
                            <p class="mb-1"><strong id="detailValidasiKode">-</strong></p>
                            <p class="mb-0" id="detailValidasiElemen">-</p>
                        </div>
                    </div>

                    <!-- Hasil Validasi -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
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
                                    <small class="text-muted">Kategori:</small>
                                    <p id="detailValidasiSkor" class="mb-0">
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
                                <div class="alert alert-light alert-permanent alert-dismissible mt-2" id="detailValidasiCatatan">
                                    -
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Penilaian Asesor -->
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-people"></i> Penilaian Asesor
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="detailPenilaianAsesor">
                                <!-- Will be populated by JavaScript -->
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
