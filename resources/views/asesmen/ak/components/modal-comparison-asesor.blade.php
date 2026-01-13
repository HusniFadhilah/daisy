{{-- Modal: Comparison Asesor --}}
<div class="modal fade" id="modalComparisonAsesor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-people"></i> Perbandingan Penilaian Antar Asesor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                {{-- Loading State --}}
                <div id="loadingComparison" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat data perbandingan...</p>
                </div>

                {{-- Content Container --}}
                <div id="comparisonContainer" style="display: none;">
                    {{-- Legend --}}
                    <div class="p-3 bg-light border-bottom">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <h6 class="mb-2">Legenda Kategori Penilaian:</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="legend-item">
                                        <span class="legend-box" style="background: #9e9e9e;"></span>
                                        <span class="legend-text">Belum Dinilai</span>
                                    </div>
                                    @foreach ($jenjangs as $jenjang)
                                    <div class="legend-item">
                                        <span class="legend-box" style="background: {{ $jenjang->color }};"></span>
                                        <span class="legend-text">{{ $jenjang->name }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <h6 class="mb-2">Perbandingan Penilaian:</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="legend-item">
                                        <span class="legend-box validator-diff" style="background: #fff3e0; border-color: #ff9800;"></span>
                                        <span class="legend-text">Perbedaan Nilai</span>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-box" style="background: #e8f5e9; border-color: #4caf50;"></span>
                                        <span class="legend-text">Nilai Sama</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Comparison Matrix Table --}}
                    <div class="comparison-matrix-wrapper" style="overflow-x: auto; overflow-y: auto; max-height: calc(100vh - 300px);">
                        <table class="comparison-matrix-table" id="comparisonMatrix">
                            <thead>
                                {{-- Will be populated by JavaScript --}}
                            </thead>
                            <tbody>
                                {{-- Will be populated by JavaScript --}}
                            </tbody>
                        </table>
                    </div>

                    {{-- Summary Statistics --}}
                    <div class="p-3 bg-light border-top">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="stat-box">
                                    <h4 class="mb-0" id="compStatTotal">0</h4>
                                    <small class="text-muted">Total Elemen</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box">
                                    <h4 class="mb-0 text-success" id="compStatAgreed">0</h4>
                                    <small class="text-muted">Nilai Sama</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box">
                                    <h4 class="mb-0 text-danger" id="compStatDiff">0</h4>
                                    <small class="text-muted">Nilai Berbeda</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box">
                                    <h4 class="mb-0 text-warning" id="compStatPending">0</h4>
                                    <small class="text-muted">Belum Dinilai</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnHighlightDiffComparison">
                    <i class="bi bi-search"></i> Highlight Perbedaan
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Comparison Matrix Styles */
    .comparison-matrix-table {
        width: max-content;
        min-width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 11px;
        background: white;
    }

    .comp-header {
        padding: 10px 8px;
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
        color: white;
        border: 1px solid #dee2e6;
        font-weight: 600;
        text-align: center;
    }

    .comp-subheader {
        padding: 6px 8px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        font-weight: 500;
    }

    .comp-cell {
        padding: 8px;
        border: 1px solid #dee2e6;
        background: white;
        vertical-align: top;
    }

    .comp-score-cell {
        min-width: 100px;
        max-width: 100px;
        height: 60px;
        vertical-align: middle;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .comp-score-cell:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        z-index: 5;
    }

    .comp-score-cell:hover::after {
        content: '💬';
        position: absolute;
        top: 5px;
        right: 5px;
        font-size: 14px;
    }

    /* Sticky Columns */
    .comp-sticky-col {
        position: sticky;
        z-index: 10;
    }

    .comp-sticky-header {
        position: sticky;
        top: 0;
        z-index: 20;
    }

    .comp-sticky-col.comp-sticky-header {
        z-index: 40;
    }

    /* Elemen Column */
    .comp-elemen-col {
        max-width: 120px;
    }

    .comp-elemen-text {
        font-size: 11px;
        line-height: 1.4;
        font-weight: 500;
        word-wrap: break-word;
        white-space: normal;
    }

    /* Indikator Column */
    .comp-indikator-col {
        max-width: 200px;
    }

    .comp-indikator-content {
        font-size: 10px;
        line-height: 1.3;
    }

    /* Difference Highlight */
    .comparison-row[data-has-diff="true"] {
        background: #fff9e6 !important;
    }

    .comparison-row[data-has-diff="true"] .comp-score-cell {
        border: 2px solid #ff9800;
    }

    .comparison-row.highlight-diff {
        animation: compRowPulse 1s ease;
    }

    @keyframes compRowPulse {

        0%,
        100% {
            background: #fff9e6;
        }

        50% {
            background: #ffe0b2;
        }
    }

    /* Review Mode */
    .comparison-row.review-diff-muted {
        opacity: 0.25;
        filter: grayscale(0.7);
        transition: all 0.2s ease;
    }

    .comparison-row.review-diff-focus {
        position: relative;
        z-index: 2;
        box-shadow: 0 0 0 3px #ff9800 inset;
        background-color: #fffbe6;
    }

    /* Avatar Circle */
    .avatar-circle-comp {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 13px;
    }

</style>
@endpush

@push('scripts')
<script>
    /**
     * ============================================
     * COMPARISON MODAL
     * ============================================
     */
    const btnViewComparison = document.getElementById('btnViewComparison');
    if (btnViewComparison) {
        btnViewComparison.addEventListener('click', function() {
            const idAsesmen = this.dataset.idAsesmen;
            const jenisAsesmen = this.dataset.jenisAsesmen;
            openComparisonModal(idAsesmen);
        });
    }

    /**
     * Open Comparison Modal
     */
    async function openComparisonModal(idAsesmen) {
        const modal = showModalById('modalComparisonAsesor');

        // Show loading
        document.getElementById('loadingComparison').style.display = 'block';
        document.getElementById('comparisonContainer').style.display = 'none';

        try {
            const response = await fetch(`/ak/berkas/${idAsesmen}/comparison-data`, {
                headers: {
                    'Accept': 'application/json'
                    , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Gagal memuat data');
            }

            populateComparisonModal(result.data);

            // Hide loading, show content
            document.getElementById('loadingComparison').style.display = 'none';
            document.getElementById('comparisonContainer').style.display = 'block';

        } catch (error) {
            console.error('Error loading comparison data:', error);
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: error.message || 'Gagal memuat data perbandingan'
            });
            modal.hide();
        }
    }

    /**
     * Populate Comparison Modal
     */
    function populateComparisonModal(data) {
        const {
            asesors
            , kriterias
            , statistics
        } = data;
        const colors = ['#e3f2fd', '#fff3e0', '#e8f5e9', '#f3e5f5', '#fce4ec'];

        // Update statistics
        document.getElementById('compStatTotal').textContent = statistics.total;
        document.getElementById('compStatAgreed').textContent = statistics.agreed;
        document.getElementById('compStatDiff').textContent = statistics.diff;
        document.getElementById('compStatPending').textContent = statistics.pending;

        // Build table header
        let headerHtml = `
            <tr>
                <th class="comp-header comp-sticky-col comp-sticky-header" style="left: 0; min-width: 40px; z-index: 35;">
                    <div class="text-center fw-bold">Kriteria</div>
                </th>
                <th class="comp-header comp-sticky-col comp-sticky-header" style="left: 40px; min-width: 50px; z-index: 35;">
                    <div class="text-center fw-bold">Kode<br>Elemen</div>
                </th>
                <th class="comp-header comp-sticky-col comp-sticky-header comp-elemen-col" style="left: 70px; max-width: 120px; z-index: 35;">
                    <div class="fw-bold">Elemen Standar</div>
                </th>
                <th class="comp-header comp-sticky-header comp-indikator-col" style="min-width: 180px; z-index: 30;">
                    <div class="fw-bold">
                        <i class="bi bi-list-ul me-2"></i>Indikator Penilaian
                    </div>
                </th>
        `;

        // Dynamic asesor columns
        asesors.forEach((asesor, index) => {
            const bgColor = colors[index % colors.length];
            headerHtml += `
                <th class="comp-header comp-sticky-header text-center" colspan="2" style="background: ${bgColor}; z-index: 30;">
                    <div class="d-flex flex-column align-items-center">
                        <div class="avatar-circle-comp mb-1" style="background: linear-gradient(135deg, #932136, #870820);">
                            ${asesor.user.name.substring(0, 2).toUpperCase()}
                        </div>
                        <div class="fw-bold text-dark">Asesor ${asesor.urutan_asesor}</div>
                        <small class="text-muted">${asesor.user.name}</small>
                    </div>
                </th>
            `;
        });

        headerHtml += `</tr>`;

        // Sub-header
        let subHeaderHtml = `
            <tr>
                <th class="comp-subheader comp-sticky-col comp-sticky-header" style="left: 0; z-index: 34;"></th>
                <th class="comp-subheader comp-sticky-col comp-sticky-header" style="left: 40px; z-index: 34;"></th>
                <th class="comp-subheader comp-sticky-col comp-sticky-header" style="left: 70px; z-index: 34;"></th>
                <th class="comp-subheader comp-sticky-header comp-indikator-col" style="z-index: 29;"></th>
        `;

        asesors.forEach((asesor, index) => {
            const bgColor = colors[index % colors.length];
            subHeaderHtml += `
                <th class="comp-subheader comp-sticky-header text-center" style="background: ${bgColor}; min-width: 100px; z-index: 29;">
                    <small class="fw-bold">Pemenuhan</small>
                </th>
                <th class="comp-subheader comp-sticky-header text-center" style="background: ${bgColor}; min-width: 100px; z-index: 29;">
                    <small class="fw-bold">Pelampauan</small>
                </th>
            `;
        });

        subHeaderHtml += `</tr>`;

        document.querySelector('#comparisonMatrix thead').innerHTML = headerHtml + subHeaderHtml;

        // Build table body
        let bodyHtml = '';
        const warnaSkor = @json($pluckColorSkor);
        kriterias.forEach(kriteria => {
            const jumlahElemen = kriteria.elemen_standar.length;
            let firstRow = true;

            kriteria.elemen_standar.forEach(elemen => {
                // Check if has difference
                const skors = [];
                asesors.forEach(asesor => {
                    const penilaian = elemen.penilaian_elemen_ak.find(p => p.id_asesor === asesor.id_user);
                    if (penilaian && penilaian.skor !== null) {
                        skors.push(penilaian.skor);
                    }
                });

                const hasDifference = new Set(skors).size > 1;

                bodyHtml += `<tr class="comparison-row" data-elemen-id="${elemen.id}" ${hasDifference ? 'data-has-diff="true"' : ''}>`;

                // Kriteria (merged cell)
                if (firstRow) {
                    bodyHtml += `
                        <td class="comp-cell comp-sticky-col" style="left: 0; z-index: 15;" rowspan="${jumlahElemen}">
                            <span class="kriteria-badge">${kriteria.kode_kriteria}</span>
                        </td>
                    `;
                    firstRow = false;
                }

                // Kode Elemen
                bodyHtml += `
                    <td class="comp-cell comp-sticky-col text-center" style="left: 40px; z-index: 15;">
                        <strong>${elemen.kode_elemen}</strong>
                    </td>
                `;

                // Elemen Standar
                bodyHtml += `
                    <td class="comp-cell comp-sticky-col comp-elemen-col" style="left: 70px; z-index: 15;">
                        <div class="comp-elemen-text">${elemen.pernyataan_elemen}</div>
                    </td>
                `;

                // Indikator
                let indikatorHtml = '';
                if (elemen.indikator && elemen.indikator.length > 0) {
                    indikatorHtml = '<ul class="mb-0 ps-3">';
                    elemen.indikator.forEach(ind => {
                        indikatorHtml += `
                            <li class="small">
                                <strong>${ind.kode_indikator}:</strong> ${ind.deskripsi_indikator}
                            </li>
                        `;
                    });
                    indikatorHtml += '</ul>';
                } else {
                    indikatorHtml = '<small class="text-muted">-</small>';
                }

                bodyHtml += `
                    <td class="comp-cell comp-indikator-col">
                        <div class="comp-indikator-content">${indikatorHtml}</div>
                    </td>
                `;

                // Asesor scores
                asesors.forEach(asesor => {
                    const penilaian = elemen.penilaian_elemen_ak.find(p => p.id_asesor === asesor.id_user);
                    const skor = penilaian ? penilaian.skor : null;
                    const komentar = penilaian ? penilaian.komentar : '';

                    // Pemenuhan (skor != 4)
                    const bgPemenuhan = (skor !== null && skor != 4) ? (warnaSkor[skor] || '') : '#e0e0e0';
                    const onclickPemenuhan = (skor !== null && skor != 4) ?
                        `onclick="showKomentarPopover(this, '${asesor.user.name}', ${skor}, '${komentar.replace(/'/g, "\\'")}')"` :
                        '';

                    bodyHtml += `
                        <td class="comp-cell comp-score-cell" style="background: ${bgPemenuhan};" ${onclickPemenuhan}>
                            ${(skor !== null && skor != 4) ? `<small class="text-${skor == 2 ? 'dark' : 'white'}">${komentar.substring(0, 50)}...</small>` : ''}
                        </td>
                    `;

                    // Pelampauan (skor == 4)
                    const bgPelampauan = (skor == 4) ? (warnaSkor[4] || '') : '#e0e0e0';
                    const onclickPelampauan = (skor == 4) ?
                        `onclick="showKomentarPopover(this, '${asesor.user.name}', 4, '${komentar.replace(/'/g, "\\'")}')"` :
                        '';

                    bodyHtml += `
                        <td class="comp-cell comp-score-cell" style="background: ${bgPelampauan};" ${onclickPelampauan}>
                            ${skor == 4 ? `<small class="text-white">${komentar.substring(0, 50)}...</small>` : ''}
                        </td>
                    `;
                });

                bodyHtml += `</tr>`;
            });
        });

        document.querySelector('#comparisonMatrix tbody').innerHTML = bodyHtml;

        // Setup highlight button
        setupHighlightDiffButton();
    }

    /**
     * Setup Highlight Difference Button
     */
    function setupHighlightDiffButton() {
        const btnHighlight = document.getElementById('btnHighlightDiffComparison');
        let highlightActive = false;

        if (btnHighlight) {
            btnHighlight.addEventListener('click', function() {
                const allRows = document.querySelectorAll('.comparison-row');
                const diffRows = document.querySelectorAll('.comparison-row[data-has-diff="true"]');

                if (!highlightActive) {
                    // Activate highlight mode
                    allRows.forEach(row => {
                        if (row.dataset.hasDiff === 'true') {
                            row.classList.add('review-diff-focus');
                            row.classList.remove('review-diff-muted');
                        } else {
                            row.classList.add('review-diff-muted');
                            row.classList.remove('review-diff-focus');
                        }
                    });

                    highlightActive = true;
                    this.innerHTML = '<i class="bi bi-x-circle"></i> Matikan Highlight';
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-primary');

                    if (diffRows.length === 0) {
                        Swal.fire({
                            icon: 'info'
                            , title: 'Tidak Ada Perbedaan'
                            , text: 'Semua penilaian asesor sudah sama.'
                            , timer: 2000
                        });
                    }

                } else {
                    // Deactivate highlight mode
                    allRows.forEach(row => {
                        row.classList.remove('review-diff-muted', 'review-diff-focus');
                    });

                    highlightActive = false;
                    this.innerHTML = '<i class="bi bi-search"></i> Highlight Perbedaan';
                    this.classList.remove('btn-primary');
                    this.classList.add('btn-outline-primary');
                }
            });
        }
    }

    /**
     * Show Komentar Popover (reuse from validator)
     */
    window.showKomentarPopover = function(element, namaAsesor, skor, komentar) {
        const skorLabel = getSkorLabel(skor);

        Swal.fire({
            title: `💬 Komentar ${namaAsesor}`
            , html: `
                <div class="text-start">
                    <div class="mb-2">
                        <span class="badge ${getSkorBadgeClass(skor)}">${skorLabel}</span>
                    </div>
                    <div class="alert alert-light alert-permanent">
                        <strong>Justifikasi:</strong>
                        <p class="mb-0 mt-2">${komentar || '<em>Tidak ada komentar</em>'}</p>
                    </div>
                </div>
            `
            , icon: 'info'
            , confirmButtonText: 'Tutup'
            , width: '600px'
        });
    };

</script>
@endpush
