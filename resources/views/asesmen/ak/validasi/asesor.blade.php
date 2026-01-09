@extends('layouts.template.app')

@section('title', 'Validasi Penilaian Asesor - ' . $asesmen->name)

@section('content')
<div class="container-fluid py-3">
    <!-- Header Card -->
    <div class="card mb-4 header-card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Validasi Penilaian Asesor</h3>
                    <p class="text-muted mb-0">{{ $asesmen->name }}</p>
                </div>
                <a href="{{ route('ak.validasi.index') }}" class="btn btn-outline-secondary">
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
                                <div class="progress mt-2" style="height: 20px;">
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
                                <div class="progress mt-2" style="height: 20px;">
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
        <div class="card-footer bg-white py-2">
            @if($isApproved)
            <div class="alert alert-success alert-permanent alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                Penilaian ini telah divalidasi oleh validator dan dinyatakan lolos untuk tahap Asesmen Lapangan (AL)
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    @if($allValidated && !$isApproved)
                    <button type="button" class="btn btn-success btn-md" id="btnApproveAll">
                        <i class="bi bi-check-all"></i> Setujui Semua Penilaian
                    </button>
                    @elseif($isApproved)
                    <button type="button" class="btn btn-success btn-md" disabled>
                        <i class="bi bi-check-circle"></i> Sudah Disetujui
                    </button>
                    @else
                    <button type="button" class="btn btn-secondary btn-md" disabled>
                        <i class="bi bi-hourglass-split"></i> Validasi Belum Lengkap
                    </button>
                    @endif

                    <button type="button" class="btn btn-outline-primary btn-md ms-lg-2 my-2" id="btnExportComparison">
                        <i class="bi bi-file-earmark-excel"></i> Download Perbandingan
                    </button>
                </div>

                <div class="d-flex align-items-center">
                    <span class="text-muted me-3">
                        Progress Validasi:
                        <strong>{{ $validatedCount }}/{{ $totalElemen }}</strong>
                    </span>
                    <div class="progress d-inline-block" style="height: 20px; width: 200px;">
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
                    <button type="button" class="btn btn-outline-success w-100" id="btnValidateAllAgreed" {{ $isApproved ? 'disabled' : '' }}>
                        <i class="bi bi-check-circle"></i>
                        <div>Validasi Semua yang Sama</div>
                        <small>Otomatis approve nilai yang sama</small>
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-warning w-100" id="btnReviewDifferences">
                        <i class="bi bi-exclamation-triangle"></i>
                        <div>Review Perbedaan</div>
                        <small>Lihat hanya yang berbeda</small>
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-info w-100" id="btnShowComments">
                        <i class="bi bi-chat-square-text"></i>
                        <div>Lihat Komentar</div>
                        <small>Tampilkan justifikasi asesor</small>
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-secondary w-100" id="btnExportNotes">
                        <i class="bi bi-file-text"></i>
                        <div>Download Catatan</div>
                        <small>Download catatan validasi</small>
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
                <div class="alert alert-warning alert-permanent alert-dismissible">
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

@include('asesmen.ak.components.modal-validasi-detail')
@include('asesmen.ak.components.modal-detail-validasi')
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
    const idAsesmen = "{{ $asesmen->id }}";
    const asesor1Id = "{{ $asesor1->id }}";
    const asesor2Id = "{{ $asesor2->id }}";
    let diffFilterActive = false;
    document.addEventListener('DOMContentLoaded', function() {
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
            const btnReviewDifferences = document.getElementById('btnReviewDifferences');
            let reviewDiffActive = false;

            if (btnReviewDifferences) {
                btnReviewDifferences.addEventListener('click', function() {
                    const allRows = document.querySelectorAll('.validator-row');
                    const diffRows = document.querySelectorAll('.validator-row[data-has-diff="true"]');

                    if (!reviewDiffActive) {
                        // MODE ON: baris beda jelas, baris lain dimute (opacity diturunkan)
                        allRows.forEach(row => {
                            if (row.dataset.hasDiff === 'true') {
                                row.classList.add('review-diff-focus');
                                row.classList.remove('review-diff-muted');
                            } else {
                                row.classList.add('review-diff-muted');
                                row.classList.remove('review-diff-focus');
                            }
                        });

                        reviewDiffActive = true;

                        Swal.fire({
                            icon: 'info'
                            , title: 'Review Perbedaan'
                            , text: `Menyorot ${diffRows.length} elemen dengan perbedaan penilaian.`
                            , timer: 2000
                        });
                    } else {
                        // MODE OFF: kembalikan tampilan normal
                        allRows.forEach(row => {
                            row.classList.remove('review-diff-muted', 'review-diff-focus');
                        });
                        reviewDiffActive = false;

                        Swal.fire({
                            icon: 'info'
                            , title: 'Filter Dimatikan'
                            , text: 'Semua elemen ditampilkan normal kembali.'
                            , timer: 1500
                        });
                    }
                });
            }

            // Export comparison
            const btnExportComparison = document.getElementById('btnExportComparison')
            if (btnExportComparison) btnExportComparison.addEventListener('click', function() {
                window.location.href = `/ak/validasi/${idAsesmen}/export-comparison/${asesor1Id}/${asesor2Id}`;
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
                    const response = await fetch(`/ak/validasi/${idAsesmen}/asesor/approve`, {
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

            // Show modal
            modal.show();

            // Show loading
            document.getElementById('loadingDetail').style.display = 'block';
            document.getElementById('detailContainer').style.display = 'none';

            try {
                // Fetch elemen detail
                const response = await fetch(`/ak/validasi/${idAsesmen}/elemen/${elemenId}`, {
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

            // Store elemen ID dan asesor IDs
            document.getElementById('validasiElemenId').value = elemen.id;
            document.getElementById('validasiAsesor1Id').value = asesor1.user.id;
            document.getElementById('validasiAsesor2Id').value = asesor2.user.id;

            // ✅ SET VALUE untuk radio buttons
            document.getElementById('chkAsesor1').value = asesor1.user.id;
            document.getElementById('chkAsesor2').value = asesor2.user.id;

            // Elemen Info
            document.getElementById('detailKriteria').textContent = elemen.kriteria ? `${elemen.kriteria.kode_kriteria} (${elemen.kriteria.nama_kriteria})` : '-';
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

            const skorAsesor1Container = document.getElementById('skorAsesor1Container')
            if (asesor1.penilaian) {
                const skor1 = asesor1.penilaian.skor;
                if (skorAsesor1Container) skorAsesor1Container.innerHTML =
                    `<div class="skor-display" style="background: ${getSkorColorJS(skor1)}; color: white;">${skor1}</div>`;
                const kategoriAsesor1 = document.getElementById('kategoriAsesor1')
                if (kategoriAsesor1) {
                    kategoriAsesor1.className = `badge ${getSkorBadgeClass(skor1)}`;
                    kategoriAsesor1.textContent = getSkorLabel(skor1);
                }
                const komentarAsesor1 = document.getElementById('komentarAsesor1')
                if (komentarAsesor1) komentarAsesor1.innerHTML =
                    asesor1.penilaian.komentar || '<em class="text-muted">Tidak ada justifikasi</em>';
            } else {
                if (skorAsesor1Container) skorAsesor1Container.innerHTML =
                    '<span class="text-muted">Belum dinilai</span>';
            }

            // Asesor 2
            document.getElementById('avatar2').textContent = asesor2.user.name.substring(0, 2).toUpperCase();
            document.getElementById('namaAsesor2').textContent = asesor2.user.name;

            const skorAsesor2Container = document.getElementById('skorAsesor2Container')
            if (asesor2.penilaian) {
                const skor2 = asesor2.penilaian.skor;
                if (skorAsesor2Container) skorAsesor2Container.innerHTML = `<div class="skor-display" style="background: ${getSkorColorJS(skor2)}; color: white;">${skor2}</div>`;
                const kategoriAsesor2 = document.getElementById('kategoriAsesor2')
                if (kategoriAsesor2) {
                    kategoriAsesor2.className = `badge ${getSkorBadgeClass(skor2)}`;
                    kategoriAsesor2.textContent = getSkorLabel(skor2);
                }
                const komentarAsesor2 = document.getElementById('komentarAsesor2')
                if (komentarAsesor2) komentarAsesor2.innerHTML = asesor2.penilaian.komentar || '<em class="text-muted">Tidak ada justifikasi</em>';
            } else {
                if (skorAsesor2Container) skorAsesor2Container.innerHTML =
                    '<span class="text-muted">Belum dinilai</span>';
            }

            // Difference indicator
            const rowDifference = document.getElementById('rowDifference')
            if (hasDifference && asesor1.penilaian && asesor2.penilaian) {
                if (rowDifference) rowDifference.style.display = '';
                const diff = Math.abs(asesor1.penilaian.skor - asesor2.penilaian.skor);
                const differenceMessage = document.getElementById('differenceMessage')
                if (differenceMessage) differenceMessage.textContent = ` Selisih ${diff} poin antara kedua asesor.`;
            } else {
                if (rowDifference) rowDifference.style.display = 'none';
            }

            // Setup quick select buttons
            setupQuickSelectButtons(asesor1.penilaian, asesor2.penilaian);

            // Reset form
            document.getElementById('formValidasi').reset();
            document.querySelectorAll('input[name="asesor_target_revisi[]"]').forEach(cb => {
                cb.checked = false;
            });
        }

        /**
         * ============================================
         * SETUP MODAL EVENT LISTENERS
         * ============================================
         */
        function setupModalEventListeners() {
            // Status validasi change
            const statusValidasi = document.getElementById('statusValidasi');
            if (statusValidasi) {
                statusValidasi.addEventListener('change', function() {
                    const btnRevision = document.getElementById('btnSaveRevision');
                    const btnValidasi = document.getElementById('btnSaveValidasi');
                    const alertRevisi = document.getElementById('alertRevisiInfo');
                    const labelRequired = document.getElementById('labelCatatanRequired');

                    if (this.value === 'revision_required') {
                        btnRevision.style.display = 'inline-block';
                        btnValidasi.style.display = 'none';
                        alertRevisi.style.display = 'block';
                        labelRequired.style.display = 'inline';

                        // ✅ Show radio buttons untuk pilih asesor
                        document.getElementById('rowAsesor1').classList.add('border-danger');
                        document.getElementById('rowAsesor2').classList.add('border-danger');
                    } else {
                        btnRevision.style.display = 'none';
                        btnValidasi.style.display = 'inline-block';
                        alertRevisi.style.display = 'none';
                        labelRequired.style.display = 'none';

                        // ✅ Hide radio buttons
                        document.getElementById('rowAsesor1').classList.remove('border-danger');
                        document.getElementById('rowAsesor2').classList.remove('border-danger');
                    }
                });
            }

            // Save validasi
            const btnSaveValidasi = document.getElementById('btnSaveValidasi');
            if (btnSaveValidasi) {
                btnSaveValidasi.addEventListener('click', function() {
                    submitValidasi('validated');
                });
            }

            // Save revision
            const btnSaveRevision = document.getElementById('btnSaveRevision');
            if (btnSaveRevision) {
                btnSaveRevision.addEventListener('click', function() {
                    submitValidasi('revision_required');
                });
            }
        }

        /**
         * ============================================
         * SETUP QUICK SELECT BUTTONS
         * ============================================
         */
        function setupQuickSelectButtons(penilaian1, penilaian2) {
            // Select Asesor 1 score
            const btnSelectAsesor1 = document.getElementById('btnSelectAsesor1')
            if (btnSelectAsesor1) btnSelectAsesor1.addEventListener('click', function() {
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
            const btnSelectAsesor2 = document.getElementById('btnSelectAsesor2')
            if (btnSelectAsesor2) btnSelectAsesor2.addEventListener('click', function() {
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
            const btnSelectAverage = document.getElementById('btnSelectAverage')
            if (btnSelectAverage) btnSelectAverage.addEventListener('click', function() {
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

            // ✅ VALIDASI: Jika revisi, harus pilih asesor
            let asesorIds = null;
            if (status === 'revision_required') {
                const selectedChecks = Array.from(
                    document.querySelectorAll('input[name="asesor_target_revisi[]"]:checked')
                );

                if (selectedChecks.length === 0) {
                    Swal.fire({
                        icon: 'warning'
                        , title: 'Pilih Asesor'
                        , text: 'Silakan centang asesor mana yang harus merevisi penilaian!'
                    });
                    return;
                }

                asesorIds = selectedChecks.map(cb => cb.value);

                if (!catatanValidator.trim()) {
                    Swal.fire({
                        icon: 'warning'
                        , title: 'Catatan Diperlukan'
                        , text: 'Silakan berikan catatan untuk revisi'
                    });
                    return;
                }
            }

            // Validation for validated status
            if (status === 'validated' && !skorFinal) {
                Swal.fire({
                    icon: 'warning'
                    , title: 'Skor Final Diperlukan'
                    , text: 'Silakan pilih skor final terlebih dahulu'
                });
                return;
            }

            // Confirm
            let confirmText = `Anda akan menyetujui penilaian dengan skor final: ${skorFinal}`;
            if (status === 'revision_required') {
                const selectedChecks = Array.from(
                    document.querySelectorAll('input[name="asesor_target_revisi[]"]:checked')
                );

                const names = selectedChecks.map(cb => {
                    // ambil nama asesor dari card terdekat
                    const card = cb.closest('.card');
                    return card ? card.querySelector('h6').textContent.trim() : 'Asesor';
                }).filter(Boolean);

                confirmText = `Anda akan meminta revisi kepada: ${names.join(' dan ')}`;
            }

            const confirmResult = await Swal.fire({
                icon: 'question'
                , title: status === 'validated' ? 'Setujui Penilaian?' : 'Minta Revisi?'
                , text: confirmText
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
                const response = await fetch(`/ak/validasi/${idAsesmen}/elemen/${elemenId}/validate`, {
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
                        , id_asesors: asesorIds // ✅ KIRIM ID ASESOR yang harus revisi
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
                    , text: result.message
                    , timer: 2000
                    , showConfirmButton: false
                });

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalValidasiDetail'));
                modal.hide();

                // Reload page
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
         * Validate All Agreed
         */
        async function validateAllAgreed() {
            showLoading();

            try {
                const response = await fetch(`/ak/validasi/${idAsesmen}/validate-agreed`, {
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
     * ============================================
     * SHOW KOMENTAR POPOVER
     * ============================================
     */
    function showKomentarPopover(element, namaAsesor, skor, komentar) {
        const skorLabel = {
            0: 'Tidak Memenuhi'
            , 1: 'Belum Memenuhi'
            , 2: 'Lemah'
            , 3: 'Memenuhi'
            , 4: 'Pelampauan'
        } [skor] || '-';

        Swal.fire({
            title: `💬 Komentar ${namaAsesor}`
            , html: `
            <div class="text-start">
                <div class="alert alert-light alert-permanent alert-dismissible">
                    <strong>Justifikasi:</strong>
                    <p class="mb-0 mt-2">${komentar || '<em>Tidak ada komentar</em>'}</p>
                </div>
            </div>
        `
            , icon: 'info'
            , confirmButtonText: 'Tutup'
            , width: '600px'
        });
    }

    /**
     * ============================================
     * SHOW VALIDASI DETAIL
     * ============================================
     */
    async function showValidasiDetail(elemenId, validasiId) {
        const modal = new bootstrap.Modal(document.getElementById('modalDetailValidasi'));
        modal.show();

        // Show loading
        document.getElementById('loadingDetailValidasi').style.display = 'block';
        document.getElementById('contentDetailValidasi').style.display = 'none';

        try {
            // Fetch validasi detail
            const response = await fetch(`/ak/validasi/${idAsesmen}/detail/${elemenId}`, {
                headers: {
                    'Accept': 'application/json'
                    , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Gagal memuat data');
            }

            // Populate modal
            populateValidasiDetail(result.data);

            // Show content
            document.getElementById('loadingDetailValidasi').style.display = 'none';
            document.getElementById('contentDetailValidasi').style.display = 'block';

        } catch (error) {
            console.error('Error loading validasi detail:', error);
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: error.message
            });
            modal.hide();
        }
    }

    function populateValidasiDetail(data) {
        const {
            elemen
            , validasi
            , penilaianAsesor
        } = data;

        // Elemen info
        document.getElementById('detailValidasiKode').textContent = elemen.kode_elemen;
        document.getElementById('detailValidasiElemen').textContent = elemen.pernyataan_elemen;

        // Validasi info
        const statusBadge = validasi.status_validasi === 'validated' ?
            '<span class="badge bg-success">✅ Disetujui</span>' :
            '<span class="badge bg-warning">⚠️ Perlu Revisi</span>';

        document.getElementById('detailValidasiStatus').innerHTML = statusBadge;

        const skorBadge = document.getElementById('badgeSkorFinal');
        skorBadge.textContent = validasi.skor_final || '-';
        skorBadge.className = `badge ${getSkorBadgeClass(validasi.skor_final)}`;

        document.getElementById('detailValidasiValidator').textContent = validasi.validator ? validasi.validator.name : '-';
        document.getElementById('detailValidasiTanggal').textContent = formatDateTime(validasi.validated_at);
        document.getElementById('detailValidasiCatatan').innerHTML = validasi.catatan_validator || '<em class="text-muted">Tidak ada catatan</em>';

        // Penilaian asesor
        let htmlPenilaian = '';
        penilaianAsesor.forEach(pen => {
            htmlPenilaian += `
            <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar-circle me-2" style="width: 30px; height: 30px; font-size: 12px;">
                        ${pen.asesor.name.substring(0, 2).toUpperCase()}
                    </div>
                    <strong>${pen.asesor.name}</strong>
                </div>
                <div class="row">
                    <div class="col-md-12 mt-2">
                        <small class="text-muted">Komentar:</small>
                        <p class="small mb-0">${pen.komentar || '<em>-</em>'}</p>
                    </div>
                </div>
            </div>
        `;
        });

        document.getElementById('detailPenilaianAsesor').innerHTML = htmlPenilaian;
    }

</script>
@endpush
