@extends('layouts.template.app')

@section('title', 'Validasi Penilaian Asesor - ' . $asesmen->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header Card -->
    <div class="card mb-4 header-card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Validasi Penilaian Asesor</h3>
                    <p class="text-muted mb-0">{{ $asesmen->name }}</p>
                </div>
                <a href="{{ route('ak.validasi.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>

            <!-- Asesor Info -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-person"></i> Asesor 1
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3">
                                    {{ substr($asesor1->name, 0, 2) }}
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $asesor1->name }}</h6>
                                    <small class="text-muted">{{ $asesor1->email }}</small>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between">
                                    <span>Progress Penilaian:</span>
                                    <strong>{{ $progress1['completed'] }}/{{ $progress1['total'] }}</strong>
                                </div>
                                <div class="progress mt-2">
                                    <div class="progress-bar bg-primary" style="width: {{ $progress1['percentage'] }}%">
                                        {{ $progress1['percentage'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-warning">
                        <div class="card-header bg-warning">
                            <h6 class="mb-0">
                                <i class="bi bi-person"></i> Asesor 2
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3 bg-warning">
                                    {{ substr($asesor2->name, 0, 2) }}
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $asesor2->name }}</h6>
                                    <small class="text-muted">{{ $asesor2->email }}</small>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between">
                                    <span>Progress Penilaian:</span>
                                    <strong>{{ $progress2['completed'] }}/{{ $progress2['total'] }}</strong>
                                </div>
                                <div class="progress mt-2">
                                    <div class="progress-bar bg-warning" style="width: {{ $progress2['percentage'] }}%">
                                        {{ $progress2['percentage'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    @if($allValidated)
                    <button type="button" class="btn btn-success btn-lg" id="btnApproveAll">
                        <i class="bi bi-check-all"></i> Setujui Semua Penilaian
                    </button>
                    @else
                    <button type="button" class="btn btn-secondary btn-lg" disabled>
                        <i class="bi bi-hourglass-split"></i> Validasi Belum Lengkap
                    </button>
                    @endif

                    <button type="button" class="btn btn-outline-primary btn-lg ms-2" id="btnExportComparison">
                        <i class="bi bi-file-earmark-excel"></i> Export Perbandingan
                    </button>
                </div>

                <div>
                    <span class="text-muted me-3">
                        Progress Validasi:
                        <strong>{{ $validatedCount }}/{{ $totalElemen }}</strong>
                    </span>
                    <div class="progress d-inline-block" style="width: 200px; vertical-align: middle;">
                        <div class="progress-bar bg-success" style="width: {{ $validationPercentage }}%">
                            {{ $validationPercentage }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Validator Matrix -->
    @include('asesmen.ak.components.validator-matrix')

    <!-- Quick Actions Panel -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h6 class="mb-0">
                <i class="bi bi-lightning"></i> Quick Actions
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-success w-100" id="btnValidateAllAgreed">
                        <i class="bi bi-check-circle"></i>
                        <div>Validasi Semua yang Sama</div>
                        <small class="text-muted">Otomatis approve nilai yang sama</small>
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-warning w-100" id="btnReviewDifferences">
                        <i class="bi bi-exclamation-triangle"></i>
                        <div>Review Perbedaan</div>
                        <small class="text-muted">Lihat hanya yang berbeda</small>
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-info w-100" id="btnShowComments">
                        <i class="bi bi-chat-square-text"></i>
                        <div>Lihat Komentar</div>
                        <small class="text-muted">Tampilkan justifikasi asesor</small>
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-secondary w-100" id="btnExportNotes">
                        <i class="bi bi-file-text"></i>
                        <div>Export Catatan</div>
                        <small class="text-muted">Download catatan validasi</small>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detail Penilaian & Validasi -->
<div class="modal fade" id="validationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clipboard-check"></i> Validasi Penilaian Elemen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="validationContent">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
                <button type="button" class="btn btn-warning" id="btnRequestRevision">
                    <i class="bi bi-arrow-counterclockwise"></i> Minta Revisi
                </button>
                <button type="button" class="btn btn-success" id="btnApproveElement">
                    <i class="bi bi-check-circle"></i> Setujui
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Approve All Confirmation -->
<div class="modal fade" id="approveAllModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-all"></i> Konfirmasi Persetujuan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin <strong>menyetujui semua penilaian</strong> kedua asesor?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Perhatian:</strong> Setelah disetujui, asesor tidak dapat mengubah penilaian mereka.
                </div>
                <p>Total yang akan disetujui: <strong>{{ $totalElemen }} elemen</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="confirmApproveAll">
                    <i class="bi bi-check-circle"></i> Ya, Setujui Semua
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .avatar-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #932136;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
    }

    .avatar-circle.bg-warning {
        background: #ff9800;
    }

    .header-card {
        border: none;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }

</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const asesmenId = "{{ $asesmen->id }}";
        const asesor1Id = "{{ $asesor1->id }}";
        const asesor2Id = "{{ $asesor2->id }}";

        // Initialize all handlers
        initializeQuickActions();
        initializeApproveAll();
        initializeValidationModal();

        /**
         * Quick Actions
         */
        function initializeQuickActions() {
            // Validate all agreed
            const btnValidateAllAgreed = document.getElementById('btnValidateAllAgreed')
            if (btnValidateAllAgreed) btnValidateAllAgreed.addEventListener('click', async function() {
                const confirmed = await Swal.fire({
                    icon: 'question'
                    , title: 'Validasi Otomatis?'
                    , text: 'Sistem akan otomatis menyetujui semua penilaian yang nilainya sama dari kedua asesor.'
                    , showCancelButton: true
                    , confirmButtonText: 'Ya, Lanjutkan'
                    , cancelButtonText: 'Batal'
                });

                if (confirmed.isConfirmed) {
                    validateAllAgreed();
                }
            });

            // Review differences only
            document.getElementById('btnReviewDifferences')?.addEventListener('click', function() {
                const diffRows = document.querySelectorAll('.validator-row[data-has-diff="true"]');

                // Hide all rows
                document.querySelectorAll('.validator-row').forEach(row => {
                    row.style.display = 'none';
                });

                // Show only differences
                diffRows.forEach(row => {
                    row.style.display = '';
                });

                Swal.fire({
                    icon: 'info'
                    , title: 'Filter Aktif'
                    , text: `Menampilkan ${diffRows.length} elemen dengan perbedaan penilaian.`
                    , timer: 2000
                });
            });

            // Export comparison
            const btnExportComparison = document.getElementById('btnExportComparison')
            if (btnExportComparison) btnExportComparison.addEventListener('click', function() {
                window.location.href = `/ak/validasi/${asesmenId}/export-comparison`;
            });
        }

        /**
         * Approve All
         */
        function initializeApproveAll() {
            const btnApproveAll = document.getElementById('btnApproveAll')
            if (btnApproveAll) btnApproveAll.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('approveAllModal'));
                modal.show();
            });

            const confirmApproveAll = document.getElementById('confirmApproveAll')
            if (confirmApproveAll) confirmApproveAll.addEventListener('click', async function() {
                const modal = bootstrap.Modal.getInstance(document.getElementById('approveAllModal'));
                modal.hide();

                showLoading();

                try {
                    const response = await fetch(`/ak/validasi/${asesmenId}/asesor/${asesor1Id}/approve`, {
                        method: 'POST'
                        , headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            , 'Accept': 'application/json'
                            , 'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();
                    hideLoading();

                    if (data.success) {
                        await Swal.fire({
                            icon: 'success'
                            , title: 'Berhasil!'
                            , text: data.message
                            , confirmButtonColor: '#28a745'
                        });

                        window.location.reload();
                    } else {
                        throw new Error(data.message);
                    }
                } catch (error) {
                    hideLoading();
                    Swal.fire({
                        icon: 'error'
                        , title: 'Gagal'
                        , text: error.message
                    });
                }
            });
        }

        /**
         * ============================================
         * INITIALIZE VALIDATION MODAL
         * ============================================
         */
        function initializeValidationModal() {
            // Setup event listeners for validate buttons in matrix
            document.querySelectorAll('.btn-validate').forEach(btn => {
                btn.addEventListener('click', function() {
                    const elemenId = this.dataset.elemenId;
                    openValidationModal(elemenId);
                });
            });

            // Setup modal event listeners
            setupModalEventListeners();
        }

        /**
         * ============================================
         * OPEN VALIDATION MODAL
         * ============================================
         */
        async function openValidationModal(elemenId) {
            const modal = new bootstrap.Modal(document.getElementById('modalValidasiDetail'));
            const asesmenId = document.querySelector('[data-asesmen-id]')
            if (asesmenId)
                asesmenId.dataset.asesmenId || window.asesmenId;

            // Show modal
            modal.show();

            // Show loading
            document.getElementById('loadingDetail').style.display = 'block';
            document.getElementById('detailContainer').style.display = 'none';

            try {
                // Fetch elemen detail
                const response = await fetch(`/ak/validasi/${asesmenId}/elemen/${elemenId}`, {
                    headers: {
                        'Accept': 'application/json'
                        , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Gagal memuat data');
                }

                // Populate modal with data
                populateModalContent(result.data);

                // Hide loading, show content
                document.getElementById('loadingDetail').style.display = 'none';
                document.getElementById('detailContainer').style.display = 'block';

            } catch (error) {
                console.error('Error loading elemen detail:', error);

                Swal.fire({
                    icon: 'error'
                    , title: 'Error'
                    , text: error.message || 'Gagal memuat detail penilaian'
                });

                modal.hide();
            }
        }

        /**
         * ============================================
         * POPULATE MODAL CONTENT
         * ============================================
         */
        function populateModalContent(data) {
            const {
                elemen
                , asesor1
                , asesor2
                , hasDifference
            } = data;

            // Store elemen ID
            document.getElementById('validasiElemenId').value = elemen.id_elemen;

            // Elemen Info
            document.getElementById('detailKriteria').textContent = elemen.kriteria?.kode_kriteria || '-';
            document.getElementById('detailKodeElemen').textContent = elemen.kode_elemen;
            document.getElementById('detailElemenStandar').textContent = elemen.pernyataan_elemen;

            // Indikator
            const indikatorContainer = document.getElementById('detailIndikator');
            if (elemen.indikator && elemen.indikator.length > 0) {
                indikatorContainer.innerHTML = '<ul class="mb-0">' +
                    elemen.indikator.map(ind =>
                        `<li><strong>${ind.kode_indikator}:</strong> ${ind.deskripsi_indikator}</li>`
                    ).join('') + '</ul>';
            } else {
                indikatorContainer.innerHTML = '<p class="text-muted mb-0">Tidak ada indikator</p>';
            }

            // Asesor 1
            document.getElementById('avatar1').textContent = asesor1.user.name.substring(0, 2).toUpperCase();
            document.getElementById('namaAsesor1').textContent = asesor1.user.name;

            if (asesor1.penilaian) {
                const skor1 = asesor1.penilaian.skor;
                document.getElementById('skorAsesor1Container').innerHTML =
                    `<div class="skor-display" style="background: ${getSkorColor(skor1)}; color: white;">${skor1}</div>`;
                document.getElementById('kategoriAsesor1').className = `badge ${getSkorBadgeClass(skor1)}`;
                document.getElementById('kategoriAsesor1').textContent = getSkorLabel(skor1);
                document.getElementById('justifikasiAsesor1').innerHTML =
                    asesor1.penilaian.justifikasi || '<em class="text-muted">Tidak ada justifikasi</em>';
            } else {
                document.getElementById('skorAsesor1Container').innerHTML =
                    '<span class="text-muted">Belum dinilai</span>';
            }

            // Asesor 2
            document.getElementById('avatar2').textContent = asesor2.user.name.substring(0, 2).toUpperCase();
            document.getElementById('namaAsesor2').textContent = asesor2.user.name;

            if (asesor2.penilaian) {
                const skor2 = asesor2.penilaian.skor;
                document.getElementById('skorAsesor2Container').innerHTML =
                    `<div class="skor-display" style="background: ${getSkorColor(skor2)}; color: white;">${skor2}</div>`;
                document.getElementById('kategoriAsesor2').className = `badge ${getSkorBadgeClass(skor2)}`;
                document.getElementById('kategoriAsesor2').textContent = getSkorLabel(skor2);
                document.getElementById('justifikasiAsesor2').innerHTML =
                    asesor2.penilaian.justifikasi || '<em class="text-muted">Tidak ada justifikasi</em>';
            } else {
                document.getElementById('skorAsesor2Container').innerHTML =
                    '<span class="text-muted">Belum dinilai</span>';
            }

            // Difference indicator
            if (hasDifference && asesor1.penilaian && asesor2.penilaian) {
                document.getElementById('rowDifference').style.display = '';
                const diff = Math.abs(asesor1.penilaian.skor - asesor2.penilaian.skor);
                document.getElementById('differenceMessage').textContent =
                    ` Selisih ${diff} poin antara kedua asesor.`;
            } else {
                document.getElementById('rowDifference').style.display = 'none';
            }

            // Setup quick select buttons
            setupQuickSelectButtons(asesor1.penilaian, asesor2.penilaian);

            // Reset form
            document.getElementById('formValidasi').reset();
        }

        /**
         * ============================================
         * SETUP MODAL EVENT LISTENERS
         * ============================================
         */
        function setupModalEventListeners() {
            // Status validasi change
            document.getElementById('statusValidasi')?.addEventListener('change', function() {
                const btnRevision = document.getElementById('btnSaveRevision');
                const btnValidasi = document.getElementById('btnSaveValidasi');

                if (this.value === 'revision_needed') {
                    btnRevision.style.display = 'inline-block';
                    btnValidasi.style.display = 'none';
                } else {
                    btnRevision.style.display = 'none';
                    btnValidasi.style.display = 'inline-block';
                }
            });

            // Save validasi
            document.getElementById('btnSaveValidasi')?.addEventListener('click', function() {
                submitValidasi('validated');
            });

            document.getElementById('btnSaveRevision')?.addEventListener('click', function() {
                submitValidasi('revision_needed');
            });
        }

        /**
         * ============================================
         * SETUP QUICK SELECT BUTTONS
         * ============================================
         */
        function setupQuickSelectButtons(penilaian1, penilaian2) {
            // Select Asesor 1 score
            document.getElementById('btnSelectAsesor1')?.addEventListener('click', function() {
                if (penilaian1) {
                    document.getElementById('skorFinal').value = penilaian1.skor;
                    document.getElementById('statusValidasi').value = 'validated';
                    document.getElementById('rowAsesor1').classList.add('highlight');
                    document.getElementById('rowAsesor2').classList.remove('highlight');

                    setTimeout(() => {
                        document.getElementById('rowAsesor1').classList.remove('highlight');
                    }, 2000);
                }
            });

            // Select Asesor 2 score
            document.getElementById('btnSelectAsesor2')?.addEventListener('click', function() {
                if (penilaian2) {
                    document.getElementById('skorFinal').value = penilaian2.skor;
                    document.getElementById('statusValidasi').value = 'validated';
                    document.getElementById('rowAsesor2').classList.add('highlight');
                    document.getElementById('rowAsesor1').classList.remove('highlight');

                    setTimeout(() => {
                        document.getElementById('rowAsesor2').classList.remove('highlight');
                    }, 2000);
                }
            });

            // Select Average (if applicable)
            document.getElementById('btnSelectAverage')?.addEventListener('click', function() {
                if (penilaian1 && penilaian2) {
                    const avg = Math.round((penilaian1.skor + penilaian2.skor) / 2);
                    document.getElementById('skorFinal').value = avg;
                    document.getElementById('statusValidasi').value = 'validated';

                    Swal.fire({
                        icon: 'info'
                        , title: 'Rata-rata Dipilih'
                        , text: `Skor rata-rata: ${avg}`
                        , timer: 2000
                        , showConfirmButton: false
                    });
                }
            });
        }

        /**
         * ============================================
         * SUBMIT VALIDASI
         * ============================================
         */
        async function submitValidasi(status) {
            const elemenId = document.getElementById('validasiElemenId').value;
            const skorFinal = document.getElementById('skorFinal').value;
            const catatanValidator = document.getElementById('catatanValidator').value;
            const asesmenId = document.querySelector('[data-asesmen-id]')?.dataset.asesmenId || window.asesmenId;

            // Validation
            if (!skorFinal) {
                Swal.fire({
                    icon: 'warning'
                    , title: 'Validasi Tidak Lengkap'
                    , text: 'Silakan pilih skor final terlebih dahulu'
                });
                return;
            }

            if (status === 'revision_needed' && !catatanValidator) {
                Swal.fire({
                    icon: 'warning'
                    , title: 'Catatan Diperlukan'
                    , text: 'Silakan berikan catatan untuk revisi'
                });
                return;
            }

            // Confirm
            const confirmResult = await Swal.fire({
                icon: 'question'
                , title: status === 'validated' ? 'Setujui Penilaian?' : 'Minta Revisi?'
                , text: status === 'validated' ?
                    `Anda akan menyetujui penilaian dengan skor final: ${skorFinal}` : 'Asesor akan diminta untuk merevisi penilaian mereka'
                , showCancelButton: true
                , confirmButtonText: status === 'validated' ? 'Ya, Setujui' : 'Ya, Minta Revisi'
                , cancelButtonText: 'Batal'
            });

            if (!confirmResult.isConfirmed) return;

            // Show loading
            Swal.fire({
                title: 'Menyimpan...'
                , allowOutsideClick: false
                , didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch(`/ak/validasi/${asesmenId}/elemen/${elemenId}/validate`, {
                    method: 'POST'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'Accept': 'application/json'
                        , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                    , body: JSON.stringify({
                        status: status
                        , skor_final: skorFinal
                        , catatan_validator: catatanValidator
                    })
                });

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Gagal menyimpan validasi');
                }

                // Success
                await Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil!'
                    , text: result.message || 'Validasi berhasil disimpan'
                    , timer: 2000
                    , showConfirmButton: false
                });

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalValidasiDetail'));
                modal.hide();

                // Reload page to update matrix
                window.location.reload();

            } catch (error) {
                console.error('Error submitting validation:', error);

                Swal.fire({
                    icon: 'error'
                    , title: 'Error'
                    , text: error.message || 'Gagal menyimpan validasi'
                });
            }
        }

        /**
         * ============================================
         * HELPER FUNCTIONS
         * ============================================
         */

        function getSkorColor(skor) {
            const colors = {
                0: '#f44336'
                , 1: '#ff9800'
                , 2: '#ffeb3b'
                , 3: '#8bc34a'
                , 4: '#4caf50'
            };
            return colors[skor] || '#9e9e9e';
        }

        function getSkorLabel(skor) {
            const labels = {
                0: 'Tidak Memenuhi'
                , 1: 'Belum Memenuhi'
                , 2: 'Lemah'
                , 3: 'Memenuhi'
                , 4: 'Melampaui'
            };
            return labels[skor] || '-';
        }

        function getSkorBadgeClass(skor) {
            const classes = {
                0: 'bg-danger'
                , 1: 'bg-warning'
                , 2: 'bg-warning'
                , 3: 'bg-success'
                , 4: 'bg-success'
            };
            return classes[skor] || 'bg-secondary';
        }

        /**
         * Validate All Agreed
         */
        async function validateAllAgreed() {
            showLoading();

            try {
                const response = await fetch(`/ak/validasi/${asesmenId}/validate-agreed`, {
                    method: 'POST'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                        , 'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                hideLoading();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Berhasil!'
                        , html: `<p>${data.validated_count} elemen yang sama telah divalidasi otomatis.</p>`
                        , confirmButtonColor: '#28a745'
                    });

                    window.location.reload();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                hideLoading();
                Swal.fire({
                    icon: 'error'
                    , title: 'Gagal'
                    , text: error.message
                });
            }
        }

        /**
         * Loading helpers
         */
        function showLoading() {
            Swal.fire({
                title: 'Processing...'
                , allowOutsideClick: false
                , didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        function hideLoading() {
            Swal.close();
        }
    });

</script>
@endpush
