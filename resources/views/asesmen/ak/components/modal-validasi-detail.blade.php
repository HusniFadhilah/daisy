{{--
    ============================================
    MODAL: DETAIL VALIDASI ELEMEN
    ============================================

    Modal untuk menampilkan detail penilaian dari 2 asesor
    dan form validasi

    Location: resources/views/asesmen/ak/components/modal-validasi-detail.blade.php
--}}

<!-- Modal: Detail & Validasi -->
<div class="modal fade" id="modalValidasiDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clipboard-check"></i> Validasi Penilaian Elemen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="validasiDetailContent">
                <!-- Loading State -->
                <div class="text-center py-5" id="loadingDetail">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat detail penilaian...</p>
                </div>

                <!-- Content Container -->
                <div id="detailContainer" style="display: none;">
                    <!-- Elemen Info -->
                    <div class="card mb-4 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-info-circle"></i> Informasi Elemen
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Kriteria:</strong>
                                    <div id="detailKriteria" class="mt-1"></div>
                                </div>
                                <div class="col-md-3">
                                    <strong>Kode Elemen:</strong>
                                    <div id="detailKodeElemen" class="mt-1"></div>
                                </div>
                                <div class="col-md-6">
                                    <strong>Elemen Standar:</strong>
                                    <div id="detailElemenStandar" class="mt-1"></div>
                                </div>
                            </div>

                            <!-- Indikator -->
                            <div class="mt-3">
                                <strong>Indikator Penilaian:</strong>
                                <div id="detailIndikator" class="mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Comparison Table -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">
                                <i class="bi bi-people"></i> Perbandingan Penilaian
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="15%">Asesor</th>
                                            <th width="15%">Skor</th>
                                            <th width="15%">Kategori</th>
                                            <th width="55%">Justifikasi / Komentar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Asesor 1 -->
                                        <tr id="rowAsesor1">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle-sm bg-primary me-2" id="avatar1"></div>
                                                    <div>
                                                        <strong id="namaAsesor1"></strong>
                                                        <br>
                                                        <small class="text-muted">Asesor 1</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div id="skorAsesor1Container"></div>
                                            </td>
                                            <td>
                                                <span id="kategoriAsesor1" class="badge"></span>
                                            </td>
                                            <td>
                                                <div id="justifikasiAsesor1" class="small"></div>
                                            </td>
                                        </tr>

                                        <!-- Asesor 2 -->
                                        <tr id="rowAsesor2">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle-sm bg-warning me-2" id="avatar2"></div>
                                                    <div>
                                                        <strong id="namaAsesor2"></strong>
                                                        <br>
                                                        <small class="text-muted">Asesor 2</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div id="skorAsesor2Container"></div>
                                            </td>
                                            <td>
                                                <span id="kategoriAsesor2" class="badge"></span>
                                            </td>
                                            <td>
                                                <div id="justifikasiAsesor2" class="small"></div>
                                            </td>
                                        </tr>

                                        <!-- Difference Indicator -->
                                        <tr id="rowDifference" style="display: none;">
                                            <td colspan="4" class="bg-warning bg-opacity-10">
                                                <div class="alert alert-warning mb-0">
                                                    <i class="bi bi-exclamation-triangle"></i>
                                                    <strong>Perbedaan Penilaian Terdeteksi!</strong>
                                                    <span id="differenceMessage"></span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Validation Form -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-check-circle"></i> Form Validasi
                            </h6>
                        </div>
                        <div class="card-body">
                            <form id="formValidasi">
                                <input type="hidden" id="validasiElemenId">

                                <div class="row">
                                    <!-- Status Validasi -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-check-square"></i> Status Validasi *
                                        </label>
                                        <select class="form-select" id="statusValidasi" required>
                                            <option value="">-- Pilih Status --</option>
                                            <option value="validated">✓ Disetujui</option>
                                            <option value="revision_needed">⚠ Perlu Revisi</option>
                                        </select>
                                    </div>

                                    <!-- Skor Final -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-star"></i> Skor Final *
                                        </label>
                                        <select class="form-select" id="skorFinal" required>
                                            <option value="">-- Pilih Skor --</option>
                                            <option value="0">0 - Tidak Memenuhi (Not Met)</option>
                                            <option value="1">1 - Belum Memenuhi (Not Met)</option>
                                            <option value="2">2 - Lemah (Weakness)</option>
                                            <option value="3">3 - Memenuhi (Met)</option>
                                            <option value="4">4 - Melampaui (Exceeding)</option>
                                        </select>
                                        <small class="text-muted">
                                            Jika ada perbedaan, pilih skor yang paling sesuai
                                        </small>
                                    </div>

                                    <!-- Catatan Validator -->
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-chat-square-text"></i> Catatan Validator
                                        </label>
                                        <textarea class="form-control" id="catatanValidator" rows="4" placeholder="Berikan catatan atau penjelasan mengenai keputusan validasi Anda..."></textarea>
                                        <small class="text-muted">
                                            Catatan ini akan dikirim ke kedua asesor jika status "Perlu Revisi"
                                        </small>
                                    </div>
                                </div>

                                <!-- Quick Select Buttons -->
                                <div class="mt-3 p-3 bg-light rounded">
                                    <strong class="d-block mb-2">Quick Actions:</strong>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnSelectAsesor1">
                                            <i class="bi bi-arrow-left"></i> Pilih Skor Asesor 1
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" id="btnSelectAsesor2">
                                            Pilih Skor Asesor 2 <i class="bi bi-arrow-right"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSelectAverage">
                                            <i class="bi bi-calculator"></i> Rata-rata (jika memungkinkan)
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Batal
                </button>
                <button type="button" class="btn btn-warning" id="btnSaveRevision" style="display: none;">
                    <i class="bi bi-arrow-counterclockwise"></i> Simpan & Minta Revisi
                </button>
                <button type="button" class="btn btn-success" id="btnSaveValidasi">
                    <i class="bi bi-check-circle"></i> Simpan Validasi
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
