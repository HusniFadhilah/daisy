<!-- Modal Structure - Add this to asesor.blade.php -->
<div class="modal fade" id="validationDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clipboard-check"></i> Detail Penilaian & Validasi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading State -->
                <div id="modalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat data...</p>
                </div>

                <!-- Content Container -->
                <div id="validationDetailContent" style="display: none;">

                    <!-- Elemen Info -->
                    <div class="card mb-4 border-primary">
                        <div class="card-header bg-primary bg-opacity-10">
                            <h6 class="mb-0 text-primary">
                                <i class="bi bi-info-circle"></i> Informasi Elemen
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Kriteria:</strong>
                                    <p id="modalKriteria" class="mb-2">-</p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Kode Elemen:</strong>
                                    <p id="modalKodeElemen" class="mb-2">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Elemen Standar:</strong>
                                    <p id="modalElemenStandar" class="mb-2">-</p>
                                </div>
                            </div>

                            <!-- Indikator -->
                            <div class="mt-3">
                                <strong>Indikator Penilaian:</strong>
                                <div id="modalIndikator" class="mt-2">
                                    <!-- Will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comparison Table -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">
                                <i class="bi bi-people"></i> Perbandingan Penilaian Asesor
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 20%;">Asesor</th>
                                            <th style="width: 15%;" class="text-center">Skor</th>
                                            <th style="width: 50%;">Justifikasi</th>
                                            <th style="width: 15%;" class="text-center">Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalComparisonBody">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Validation Form -->
                    <div class="card border-warning">
                        <div class="card-header bg-warning bg-opacity-10">
                            <h6 class="mb-0 text-dark">
                                <i class="bi bi-pencil-square"></i> Form Validasi
                            </h6>
                        </div>
                        <div class="card-body">
                            <form id="validationForm">
                                <!-- Status Decision -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Keputusan Validasi</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="status" id="statusApprove" value="validated" required>
                                        <label class="btn btn-outline-success" for="statusApprove">
                                            <i class="bi bi-check-circle"></i> Disetujui
                                        </label>

                                        <input type="radio" class="btn-check" name="status" id="statusRevision" value="revision_needed">
                                        <label class="btn btn-outline-warning" for="statusRevision">
                                            <i class="bi bi-arrow-counterclockwise"></i> Perlu Revisi
                                        </label>
                                    </div>
                                </div>

                                <!-- Score Selection (for Approved) -->
                                <div class="mb-4" id="skorFinalGroup" style="display: none;">
                                    <label class="form-label fw-bold">Skor Final <span class="text-danger">*</span></label>
                                    <div class="row g-2">
                                        <div class="col">
                                            <input type="radio" class="btn-check" name="skor_final" id="skor0" value="0">
                                            <label class="btn btn-outline-danger w-100" for="skor0">
                                                <div class="fw-bold">0</div>
                                                <small>Not Met</small>
                                            </label>
                                        </div>
                                        <div class="col">
                                            <input type="radio" class="btn-check" name="skor_final" id="skor1" value="1">
                                            <label class="btn btn-outline-warning w-100" for="skor1">
                                                <div class="fw-bold">1</div>
                                                <small>Not Met</small>
                                            </label>
                                        </div>
                                        <div class="col">
                                            <input type="radio" class="btn-check" name="skor_final" id="skor2" value="2">
                                            <label class="btn btn-outline-warning w-100" for="skor2">
                                                <div class="fw-bold">2</div>
                                                <small>Weakness</small>
                                            </label>
                                        </div>
                                        <div class="col">
                                            <input type="radio" class="btn-check" name="skor_final" id="skor3" value="3">
                                            <label class="btn btn-outline-success w-100" for="skor3">
                                                <div class="fw-bold">3</div>
                                                <small>Met</small>
                                            </label>
                                        </div>
                                        <div class="col">
                                            <input type="radio" class="btn-check" name="skor_final" id="skor4" value="4">
                                            <label class="btn btn-outline-success w-100" for="skor4">
                                                <div class="fw-bold">4</div>
                                                <small>Exceeding</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-text">Pilih skor final berdasarkan penilaian kedua asesor</div>
                                </div>

                                <!-- Notes -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Catatan Validator</label>
                                    <textarea class="form-control" name="catatan_validator" id="catatanValidator" rows="4" placeholder="Tambahkan catatan, alasan, atau instruksi revisi..."></textarea>
                                    <div class="form-text">Opsional: Berikan catatan untuk asesor jika diperlukan</div>
                                </div>

                                <!-- Quick Notes Templates -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Template Catatan Cepat</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary quick-note" data-note="Penilaian sudah sesuai dengan standar dan bukti yang disediakan.">
                                            Sesuai Standar
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary quick-note" data-note="Kedua asesor memberikan penilaian yang sama dan konsisten.">
                                            Konsisten
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary quick-note" data-note="Perlu dilengkapi bukti pendukung yang lebih kuat.">
                                            Perlu Bukti
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary quick-note" data-note="Justifikasi kurang jelas, mohon diperjelas.">
                                            Perlu Justifikasi
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
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
                <button type="button" class="btn btn-success" id="btnSaveValidation">
                    <i class="bi bi-check-circle"></i> Simpan Validasi
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const asesmenId = "{{ $asesmen->id ?? '' }}";
        let currentElemenId = null;

        // Initialize modal triggers
        initializeValidationModal();

        /**
         * Initialize Modal Triggers
         */
        function initializeValidationModal() {
            // Attach click handler to validate buttons in matrix
            document.querySelectorAll('.btn-validate').forEach(btn => {
                btn.addEventListener('click', function() {
                    const elemenId = this.dataset.elemenId;
                    openValidationModal(elemenId);
                });
            });

            // Status radio change
            document.querySelectorAll('input[name="status"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const skorGroup = document.getElementById('skorFinalGroup');
                    if (this.value === 'validated') {
                        skorGroup.style.display = 'block';
                        document.querySelectorAll('input[name="skor_final"]').forEach(s => s.required = true);
                    } else {
                        skorGroup.style.display = 'none';
                        document.querySelectorAll('input[name="skor_final"]').forEach(s => {
                            s.required = false;
                            s.checked = false;
                        });
                    }
                });
            });

            // Quick notes
            document.querySelectorAll('.quick-note').forEach(btn => {
                btn.addEventListener('click', function() {
                    const note = this.dataset.note;
                    const textarea = document.getElementById('catatanValidator');
                    textarea.value += (textarea.value ? '\n' : '') + note;
                });
            });

            // Save button
            document.getElementById('btnSaveValidation')?.addEventListener('click', saveValidation);
        }

        /**
         * Open Modal and Load Data
         */
        async function openValidationModal(elemenId) {
            currentElemenId = elemenId;

            const modal = new bootstrap.Modal(document.getElementById('validationDetailModal'));
            modal.show();

            // Show loading
            document.getElementById('modalLoading').style.display = 'block';
            document.getElementById('validationDetailContent').style.display = 'none';

            try {
                const response = await fetch(`/ak/validasi/${asesmenId}/elemen/${elemenId}/detail`);
                const data = await response.json();

                if (data.success) {
                    populateModalContent(data.data);

                    // Hide loading, show content
                    document.getElementById('modalLoading').style.display = 'none';
                    document.getElementById('validationDetailContent').style.display = 'block';
                }
            } catch (error) {
                console.error('Error loading elemen detail:', error);
                Swal.fire({
                    icon: 'error'
                    , title: 'Error'
                    , text: 'Gagal memuat detail elemen'
                });
            }
        }

        /**
         * Populate Modal Content
         */
        function populateModalContent(data) {
            const elemen = data.elemen;
            const penilaian = data.penilaian;

            // Elemen info
            document.getElementById('modalKriteria').textContent = elemen.kriteria.kode_kriteria + ' - ' + elemen.kriteria.nama_kriteria;
            document.getElementById('modalKodeElemen').textContent = elemen.kode_elemen;
            document.getElementById('modalElemenStandar').textContent = elemen.pernyataan_elemen;

            // Indikator
            const indikatorHtml = elemen.indikator.map(ind => `
            <div class="alert alert-light mb-2">
                <strong>${ind.kode_indikator}:</strong> ${ind.deskripsi_indikator}
            </div>
        `).join('');
            document.getElementById('modalIndikator').innerHTML = indikatorHtml || '<p class="text-muted">Tidak ada indikator</p>';

            // Comparison table
            const comparisonHtml = penilaian.map(p => {
                const skorColor = getSkorColorClass(p.skor);
                return `
                <tr>
                    <td>
                        <strong>${p.user.name}</strong>
                        <br><small class="text-muted">${p.user.email}</small>
                    </td>
                    <td class="text-center">
                        <span class="badge ${skorColor} fs-6">${p.skor}</span>
                    </td>
                    <td>${p.justifikasi || '<em class="text-muted">Tidak ada justifikasi</em>'}</td>
                    <td class="text-center">
                        <small>${formatDateTime(p.updated_at)}</small>
                    </td>
                </tr>
            `;
            }).join('');
            document.getElementById('modalComparisonBody').innerHTML = comparisonHtml || '<tr><td colspan="4" class="text-center text-muted">Belum ada penilaian</td></tr>';

            // Pre-fill form if already validated
            if (penilaian.length > 0 && penilaian[0].status_validasi !== 'not_validated') {
                const validasi = penilaian[0];
                document.querySelector(`input[name="status"][value="${validasi.status_validasi}"]`).checked = true;
                document.querySelector(`input[name="status"][value="${validasi.status_validasi}"]`).dispatchEvent(new Event('change'));

                if (validasi.skor_final !== null) {
                    document.getElementById('skor' + validasi.skor_final).checked = true;
                }

                document.getElementById('catatanValidator').value = validasi.catatan_validator || '';
            }
        }

        /**
         * Save Validation
         */
        async function saveValidation() {
            const form = document.getElementById('validationForm');
            const formData = new FormData(form);

            // Validate
            if (!formData.get('status')) {
                Swal.fire({
                    icon: 'warning'
                    , title: 'Pilih Status'
                    , text: 'Harap pilih status validasi terlebih dahulu'
                });
                return;
            }

            if (formData.get('status') === 'validated' && !formData.get('skor_final')) {
                Swal.fire({
                    icon: 'warning'
                    , title: 'Pilih Skor Final'
                    , text: 'Harap pilih skor final untuk penilaian yang disetujui'
                });
                return;
            }

            const confirmed = await Swal.fire({
                icon: 'question'
                , title: 'Konfirmasi Validasi'
                , text: 'Apakah Anda yakin dengan validasi ini?'
                , showCancelButton: true
                , confirmButtonText: 'Ya, Simpan'
                , cancelButtonText: 'Batal'
            });

            if (!confirmed.isConfirmed) return;

            try {
                const response = await fetch(`/ak/validasi/${asesmenId}/elemen/${currentElemenId}/validate`, {
                    method: 'POST'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                        , 'Content-Type': 'application/json'
                    }
                    , body: JSON.stringify({
                        status: formData.get('status')
                        , skor_final: formData.get('skor_final')
                        , catatan_validator: formData.get('catatan_validator')
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Berhasil!'
                        , text: data.message
                        , timer: 2000
                    });

                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('validationDetailModal')).hide();

                    // Reload page
                    window.location.reload();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error'
                    , title: 'Gagal'
                    , text: error.message
                });
            }
        }

        /**
         * Helpers
         */
        function getSkorColorClass(skor) {
            const colors = {
                0: 'bg-danger'
                , 1: 'bg-warning'
                , 2: 'bg-warning'
                , 3: 'bg-success'
                , 4: 'bg-success'
            };
            return colors[skor] || 'bg-secondary';
        }

        function formatDateTime(datetime) {
            if (!datetime) return '-';
            const date = new Date(datetime);
            return date.toLocaleString('id-ID', {
                day: '2-digit'
                , month: 'short'
                , year: 'numeric'
                , hour: '2-digit'
                , minute: '2-digit'
            });
        }
    });

</script>
@endpush
