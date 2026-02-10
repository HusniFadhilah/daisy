@extends('layouts.template.app')

@section('title', 'Cek Split Penilaian - ' . $asesmen->name)

@section('content')
<div class="container-fluid py-3">
    {{-- Header Card --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="mb-1">
                        <i class="bi bi-people"></i> Cek Split Penilaian Antar Asesor
                    </h4>
                    <p class="text-muted mb-0">{{ $asesmen->getName(false) }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('ak.berkas.export', ['idAsesmen'=>$asesmen->id,'mode'=>'split','color'=>false]) }}" class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-excel"></i> Download Split Penilaian
                    </a>
                    <a href="{{ route('ak.berkas') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Content Card --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
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
                <div class="p-3 bg-white border-bottom">
                    <div class="row">
                        <div class="col-md-8 mb-2">
                            <h6 class="mb-3">Keterangan Kategori Penilaian:</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <div class="legend-item">
                                    <span class="legend-box" style="background: #9e9e9e;"></span>
                                    <span class="legend-text">Belum Dinilai</span>
                                </div>
                                @foreach ($jenjangs as $jenjang)
                                <div class="legend-item">
                                    <span class="legend-box" style="background: {{ $jenjang->color }};"></span>
                                    <span class="legend-text"><small>{{ $jenjang->name }}</small></span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Comparison Matrix Table --}}
                <div class="comparison-matrix-wrapper" style="overflow-x: auto; overflow-y: auto;">
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
                                <small class="text-muted">Tidak Split</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h4 class="mb-0 text-danger" id="compStatDiff">0</h4>
                                <small class="text-muted">Split</small>
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

        {{-- Footer Buttons --}}
        <div class="card-footer bg-white">
            <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-outline-primary" id="btnToggleIndikator">
                    <i class="bi bi-list-ul"></i> Tampilkan Indikator
                </button>
                <button type="button" class="btn btn-outline-warning" id="btnHighlightSplit">
                    <i class="bi bi-exclamation-triangle"></i> Highlight Split
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
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
        min-width: 200px;
        max-width: 200px;
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

    /* Indikator Column - Default Hidden */
    .comp-indikator-col {
        max-width: 200px;
        display: none;
        /* Hidden by default */
    }

    .comp-indikator-content {
        font-size: 10px;
        line-height: 1.3;
    }

    /* Split Columns */
    .col-split {
        min-width: 80px;
    }

    .col-ket-split {
        width: 200px;
    }

    /* Split Highlight */
    .comparison-row[data-is-split="true"] {
        background: #fff9e6 !important;
    }

    .comparison-row[data-is-split="true"] .comp-score-cell {
        border: 0.5px solid #ccc;
    }

    .comparison-row.highlight-split {
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

    /* Show Indikator */
    #modalComparisonAsesor.show-indikator .comp-indikator-col {
        display: table-cell !important;
    }

    .kriteria-badge-inline {
        display: inline-block;
        padding: 3px 6px;
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
        color: white;
        border-radius: 3px;
        font-weight: 600;
        font-size: 10px;
        width: fit-content;
    }

</style>
@endpush

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
        min-width: 200px;
        max-width: 200px;
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

    /* Indikator Column - Default Hidden */
    .comp-indikator-col {
        max-width: 200px;
        display: none;
        /* Hidden by default */
    }

    .comp-indikator-content {
        font-size: 10px;
        line-height: 1.3;
    }

    /* Split Columns */
    .col-split {
        min-width: 80px;
    }

    .col-ket-split {
        min-width: 300px;
        max-width: 400px;
    }

    /* Split Highlight */
    .comparison-row[data-is-split="true"] {
        background: #fff9e6 !important;
    }

    .comparison-row[data-is-split="true"] .comp-score-cell {
        border: 0.5px solid #ccc;
    }

    .comparison-row.highlight-split {
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

    /* Show Indikator */
    .page-cek-split.show-indikator .comp-indikator-col {
        display: table-cell !important;
    }

    .kriteria-badge-inline {
        display: inline-block;
        padding: 3px 6px;
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
        color: white;
        border-radius: 3px;
        font-weight: 600;
        font-size: 10px;
        width: fit-content;
    }

    /* Legend Items */
    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .legend-box {
        width: 30px;
        height: 20px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        display: inline-block;
    }

</style>
@endpush

@push('scripts')
<script>
    const idAsesmen = "{{ $asesmen->id }}";
    window.COMPARISON_UI = window.COMPARISON_UI || {};
    if (typeof window.COMPARISON_UI.showIndikator === 'undefined') window.COMPARISON_UI.showIndikator = false;

    // Add page identifier class
    document.body.classList.add('page-cek-split');

    // Auto load on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadComparisonData();
    });

    /**
     * Load Comparison Data
     */
    async function loadComparisonData() {
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
            if (!result.success) throw new Error(result.message || 'Gagal memuat data');

            populateComparisonModal(result.data);

            document.getElementById('loadingComparison').style.display = 'none';
            document.getElementById('comparisonContainer').style.display = 'block';

        } catch (error) {
            console.error('Error loading comparison data:', error);
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: error.message || 'Gagal memuat data perbandingan'
            });
        }
    }

    /**
     * Calculate Split Status
     * Split = "Ya" jika selisih > 1
     */
    function calculateSplitStatus(skors) {
        if (skors.length < 2) return {
            isSplit: false
            , keterangan: ''
        };

        const validSkors = skors.filter(s => s.skor !== null && s.skor !== undefined);
        if (validSkors.length < 2) return {
            isSplit: false
            , keterangan: ''
        };

        // Find min and max scores
        const skorValues = validSkors.map(s => s.skor);
        const minSkor = Math.min(...skorValues);
        const maxSkor = Math.max(...skorValues);
        const selisih = maxSkor - minSkor;

        const isSplit = selisih > 1;

        // Generate keterangan
        let keterangan = '';
        if (isSplit) {
            const penilaianTexts = validSkors.map(item => {
                const label = getSkorLabelShort(item.skor, true);
                return `Asesor ${item.urutan} memberikan penilaian ${label}`;
            });

            keterangan = penilaianTexts.join('. Sementara ') + '.';
        }

        return {
            isSplit
            , keterangan
            , selisih
        };
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

        // ===== Build table header =====
        let headerHtml = `
        <tr>
            <th class="comp-header comp-sticky-col comp-sticky-header" style="left: 0; min-width: 100px; max-width: 100px; z-index: 35;">
                <div class="fw-bold">Kriteria</div>
            </th>
            <th class="comp-header comp-sticky-col comp-sticky-header" style="left: 100px; min-width: 50px; max-width: 50px; z-index: 35;">
                <div class="text-center fw-bold">Kode</div>
            </th>
            <th class="comp-header comp-sticky-col comp-sticky-header comp-elemen-col" style="left: 135px; max-width: 120px; z-index: 35;">
                <div class="fw-bold">Elemen Standar</div>
            </th>

            <!-- Indikator Penilaian (Hidden by default) -->
            <th class="comp-header comp-sticky-header comp-indikator-col" style="min-width: 180px; z-index: 30;">
                <div class="fw-bold">
                    <i class="bi bi-list-ul me-2"></i>Indikator Penilaian
                </div>
            </th>
        `;

        // Dynamic asesor columns (Mode Gabung - 1 kolom per asesor)
        asesors.forEach((asesor, index) => {
            const bgColor = colors[index % colors.length];
            headerHtml += `
            <th class="comp-header comp-sticky-header text-center"
                style="background: #932136; z-index: 30; min-width: 200px;">
                <div class="d-flex flex-column align-items-center">
                    <div class="fw-bold text-white">Penilaian Asesmen Kecukupan Asesor ${asesor.urutan_asesor}</div>
                    <small class="text-white">${asesor.user.name}</small>
                </div>
            </th>
        `;
        });

        // Split columns at the end
        headerHtml += `
        <th class="comp-header comp-sticky-header col-split" style="min-width: 90px; z-index: 30;">
            <div class="fw-bold text-center">Split</div>
        </th>
        <th class="comp-header comp-sticky-header col-ket-split" style="min-width: 200px; z-index: 30;">
            <div class="fw-bold">Keterangan Split</div>
        </th>
        `;

        headerHtml += `</tr>`;

        // No sub-header needed in merged mode
        document.querySelector('#comparisonMatrix thead').innerHTML = headerHtml;

        // ===== Build table body =====
        let bodyHtml = '';
        const warnaSkor = @json($pluckColorSkor);
        let totalSplit = 0;
        let totalNoSplit = 0;

        kriterias.forEach(kriteria => {
            const jumlahElemen = kriteria.elemen_standar.length;
            let firstRow = true;

            kriteria.elemen_standar.forEach(elemen => {
                // Collect all penilaian
                const penilaianData = asesors.map(asesor => {
                    const penilaian = elemen.penilaian_elemen_ak.find(p => p.id_asesor === asesor.id_user);
                    return {
                        skor: penilaian ? penilaian.skor : null
                        , urutan: asesor.urutan_asesor
                        , nama: asesor.user.name
                    };
                });

                // Calculate split status
                const splitInfo = calculateSplitStatus(penilaianData);
                const isSplit = splitInfo.isSplit;

                if (isSplit) totalSplit++;
                else totalNoSplit++;

                bodyHtml += `<tr class="comparison-row" data-elemen-id="${elemen.id}" data-is-split="${isSplit}">`;

                // Kriteria (merged) - WITH NAME - NARROWER & BADGE NOT FULL WIDTH
                if (firstRow) {
                    bodyHtml += `
                    <td class="comp-cell comp-sticky-col" style="left: 0; z-index: 15; min-width: 100px; max-width: 100px;" rowspan="${jumlahElemen}">
                        <div class="d-flex flex-column">
                            <span class="kriteria-badge-inline mb-1">${kriteria.kode_kriteria}</span>
                            <small class="text-dark fw-medium" style="line-height: 1.2; font-size: 10px; word-wrap: break-word;">${kriteria.nama_kriteria}</small>
                        </div>
                    </td>
                `;
                    firstRow = false;
                }

                // Kode Elemen - NARROWER
                bodyHtml += `
                <td class="comp-cell comp-sticky-col text-center" style="left: 100px; z-index: 15; min-width: 35px; max-width: 35px;">
                    <strong style="font-size: 10px;">${elemen.kode_elemen}</strong>
                </td>
                `;

                // Elemen Standar
                bodyHtml += `
                <td class="comp-cell comp-sticky-col comp-elemen-col" style="left: 135px; z-index: 15;">
                    <div class="comp-elemen-text">${elemen.pernyataan_elemen}</div>
                </td>
                `;

                // ===== Indikator (Hidden by default) =====
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

                // ===== Asesor scores (Mode Gabung - 1 kolom) =====
                asesors.forEach(asesor => {
                    const penilaian = elemen.penilaian_elemen_ak.find(p => p.id_asesor === asesor.id_user);
                    const skor = penilaian ? penilaian.skor : null;
                    const komentar = penilaian ? penilaian.komentar : '';
                    const safeKomentar = (komentar || '').replace(/'/g, "\\'");

                    const bgMerged = (skor !== null) ? (warnaSkor[skor] || '#e0e0e0') : '#e0e0e0';
                    const onclickMerged = (skor !== null) ?
                        `onclick="showKomentarPopover(this, '${asesor.user.name}', ${skor}, '${safeKomentar}')"` : '';

                    bodyHtml += `
                <td class="comp-cell comp-score-cell comp-score-merged" style="background: ${bgMerged};" ${onclickMerged}>
                    ${skor !== null ? `<small class="text-dark">${komentar || 'Tidak ada komentar'}</small>` : '<span class="text-muted">Belum dinilai</span>'}
                </td>
                `;
                });

                // ===== Split Column =====
                const splitBadge = isSplit ?
                    '<span class="badge bg-light text-dark" style="width:50px;font-size:13px">Ya</span>' :
                    '<span class="badge bg-light text-dark" style="width:50px;font-size:13px">Tidak</span>';

                bodyHtml += `
                <td class="comp-cell col-split text-center">
                    ${splitBadge}
                </td>
                `;

                // ===== Keterangan Split =====
                const ketSplitHtml = isSplit && splitInfo.keterangan ?
                    `<span>${splitInfo.keterangan}</span>` :
                    '<span class="text-muted fst-italic">Tidak terjadi split</span>';

                bodyHtml += `
                <td class="comp-cell col-ket-split">
                    ${ketSplitHtml}
                </td>
                `;

                bodyHtml += `</tr>`;
            });
        });

        document.querySelector('#comparisonMatrix tbody').innerHTML = bodyHtml;

        // Update statistics with split count
        document.getElementById('compStatAgreed').textContent = totalNoSplit;
        document.getElementById('compStatDiff').textContent = totalSplit;

        // Setup buttons
        setupToggleIndikatorButton();
        setupHighlightSplitButton();
    }

    /**
     * Setup Toggle Indikator Button
     */
    function setupToggleIndikatorButton() {
        const btn = document.getElementById('btnToggleIndikator');
        if (!btn) return;

        // Prevent double binding
        if (btn._bound) return;
        btn._bound = true;

        btn.addEventListener('click', function() {
            window.COMPARISON_UI.showIndikator = !window.COMPARISON_UI.showIndikator;

            if (window.COMPARISON_UI.showIndikator) {
                document.body.classList.add('show-indikator');
                this.innerHTML = '<i class="bi bi-list-ul"></i> Sembunyikan Indikator';
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
            } else {
                document.body.classList.remove('show-indikator');
                this.innerHTML = '<i class="bi bi-list-ul"></i> Tampilkan Indikator';
                this.classList.remove('btn-primary');
                this.classList.add('btn-outline-primary');
            }
        });
    }

    /**
     * Setup Highlight Split Button
     */
    function setupHighlightSplitButton() {
        const btnHighlight = document.getElementById('btnHighlightSplit');
        let highlightActive = false;

        if (btnHighlight) {
            // Prevent double binding
            if (btnHighlight._bound) return;
            btnHighlight._bound = true;

            btnHighlight.addEventListener('click', function() {
                const allRows = document.querySelectorAll('.comparison-row');
                const splitRows = document.querySelectorAll('.comparison-row[data-is-split="true"]');

                if (!highlightActive) {
                    // Activate highlight mode
                    allRows.forEach(row => {
                        if (row.dataset.isSplit === 'true') {
                            row.classList.add('review-diff-focus');
                            row.classList.remove('review-diff-muted');
                        } else {
                            row.classList.add('review-diff-muted');
                            row.classList.remove('review-diff-focus');
                        }
                    });

                    highlightActive = true;
                    this.innerHTML = '<i class="bi bi-x-circle"></i> Matikan Highlight';
                    this.classList.remove('btn-outline-warning');
                    this.classList.add('btn-warning');

                    Swal.fire({
                        icon: 'info'
                        , title: 'Highlight Split'
                        , text: `Menyorot ${splitRows.length} elemen dengan split penilaian.`
                        , timer: 2000
                    });

                } else {
                    // Deactivate highlight mode
                    allRows.forEach(row => {
                        row.classList.remove('review-diff-muted', 'review-diff-focus');
                    });

                    highlightActive = false;
                    this.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Highlight Split';
                    this.classList.remove('btn-warning');
                    this.classList.add('btn-outline-warning');
                }
            });
        }
    }

    /**
     * Show Komentar Popover
     */
    window.showKomentarPopover = function(element, namaAsesor, skor, komentar) {
        const skorLabel = getSkorLabelShort(skor, true);

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
