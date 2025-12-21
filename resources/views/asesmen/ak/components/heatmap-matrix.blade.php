<!-- Heatmap Matrix Component - Enhanced with Merged Cells -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-grid-3x3"></i> Matriks Visualisasi Penilaian
            </h5>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-primary" id="btnZoomIn" title="Perbesar">
                    <i class="bi bi-zoom-in"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnZoomOut" title="Perkecil">
                    <i class="bi bi-zoom-out"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnResetZoom" title="Reset Zoom">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnToggleMatrix" title="Sembunyikan/Tampilkan">
                    <i class="bi bi-eye-slash"></i> Sembunyikan
                </button>
            </div>
        </div>
    </div>

    <div class="card-body p-0" id="matrixContainer">
        <!-- Legend -->
        <div class="p-3 bg-light border-bottom">
            <h6 class="mb-2">Legenda Kategori Penilaian:</h6>
            <div class="d-flex flex-wrap gap-3">
                <div class="legend-item">
                    <span class="legend-box" style="background: #9e9e9e;"></span>
                    <span class="legend-text">Belum Dinilai</span>
                </div>
                <div class="legend-item">
                    <span class="legend-box" style="background: #f44336;"></span>
                    <span class="legend-text">Tidak Memenuhi (Not Met)</span>
                </div>
                <div class="legend-item">
                    <span class="legend-box" style="background: #ff9800;"></span>
                    <span class="legend-text">Belum Memenuhi (Not Met)</span>
                </div>
                <div class="legend-item">
                    <span class="legend-box" style="background: #ffeb3b;"></span>
                    <span class="legend-text">Lemah (Weakness/Couse of Concern)</span>
                </div>
                <div class="legend-item">
                    <span class="legend-box" style="background: #8bc34a;"></span>
                    <span class="legend-text">Memenuhi (Met)</span>
                </div>
                <div class="legend-item">
                    <span class="legend-box" style="background: #4caf50;"></span>
                    <span class="legend-text">Pelampauan Standar</span>
                </div>
            </div>
        </div>

        <!-- Matrix Table -->
        <div class="matrix-wrapper" style="overflow-x: auto; overflow-y: auto; max-height: 600px;">
            <table class="matrix-table" id="heatmapMatrix">
                <thead>
                    <tr class="header-row-kriteria">
                        {{-- Corner kiri atas --}}
                        <th class="matrix-header-kriteria sticky-header">#</th>
                        <th class="matrix-header-corner sticky-col sticky-header">
                            <div class="corner-label">
                                <small class="text-white fw-bold">Kriteria</small>
                                <i class="bi bi-arrow-down-right text-white"></i>
                                <small class="text-white fw-bold">Elemen</small>
                            </div>
                        </th>

                        {{-- Kolom 1: Pemenuhan Standar --}}
                        <th class="matrix-header-kriteria sticky-header">
                            <div class="kriteria-label">
                                <span class="fw-bold">Pemenuhan Standar</span>
                            </div>
                        </th>

                        {{-- Kolom 2: Pelampauan Standar --}}
                        <th class="matrix-header-kriteria sticky-header">
                            <div class="kriteria-label">
                                <span class="fw-bold">Pelampauan Standar</span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($kriterias as $kriteriaRow)
                    @php
                    $jumlahElemen = $kriteriaRow->elemenStandar->count();
                    $firstRow = true;
                    @endphp

                    @foreach($kriteriaRow->elemenStandar as $elemen)
                    <tr>
                        {{-- CETAK MERGED CELL HANYA DI ROW PERTAMA --}}
                        @if($firstRow)
                        <th class="matrix-row sticky-col text-center" rowspan="{{ $jumlahElemen }}">
                            <span class="kriteria-badge">{{ $kriteriaRow->kode_kriteria }}</span>
                        </th>
                        @php $firstRow = false; @endphp
                        @endif

                        {{-- Kolom Elemen --}}
                        <td class="matrix-header-row sticky-col" data-elemen-id="{{ $elemen->id_elemen }}" data-kriteria-id="{{ $kriteriaRow->id_kriteria }}">
                            {{ $elemen->kode_elemen }} – {{ Str::limit($elemen->pernyataan_elemen, 30) }}
                        </td>

                        {{-- Kolom Pemenuhan --}}
                        <td class="matrix-cell" data-elemen-id="{{ $elemen->id_elemen }}" data-kriteria-id="{{ $kriteriaRow->id_kriteria }}" data-col="pemenuhan" style="background-color: {{ getSkorColor($elemen->penilaian->first()->skor ?? null) }}">
                        </td>

                        {{-- Kolom Pelampauan --}}
                        <td class="matrix-cell" data-elemen-id="{{ $elemen->id_elemen }}" data-kriteria-id="{{ $kriteriaRow->id_kriteria }}" data-col="pelampauan" style="background-color: {{ $elemen->penilaian->first()->skor == 4 ? getSkorColor(4) : '#e0e0e0' }}">
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary Stats -->
        <div class="p-3 bg-light border-top">
            <h6 class="text-center mb-3 fw-bold">📊 Ringkasan Statistik</h6>
            <div class="row text-center g-3">
                <div class="col-6 col-md-3">
                    <div class="stat-box">
                        <h4 class="mb-0 fw-bold" id="statTotal">0</h4>
                        <small class="text-muted">Total Isian</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-box">
                        <h4 class="mb-0 fw-bold text-success" id="statFilled">0</h4>
                        <small class="text-muted">Terisi</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-box">
                        <h4 class="mb-0 fw-bold text-warning" id="statEmpty">0</h4>
                        <small class="text-muted">Belum</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-box">
                        <h4 class="mb-0 fw-bold text-primary" id="statPercentage">0%</h4>
                        <small class="text-muted">Progress</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
/**
* Helper function to get color based on score
*/
function getSkorColor($skor) {
$colors = [
0 => '#f44336', // Red - Not Met
1 => '#ff9800', // Orange - Not Met
2 => '#ffeb3b', // Yellow - Weakness
3 => '#8bc34a', // Light Green - Met
4 => '#4caf50', // Dark Green - Exceeding
];

return $colors[$skor] ?? '#e0e0e0';
}
@endphp

@push('styles')
<style>
    /* ============================================ */
    /* MATRIX TABLE BASE                           */
    /* ============================================ */
    .matrix-table {
        width: max-content;
        min-width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
        background: white;
    }

    /* ============================================ */
    /* STICKY POSITIONING                          */
    /* ============================================ */
    .sticky-header {
        position: sticky;
        top: 0;
        background: white;
    }

    .header-row-kriteria .sticky-header {
        top: 0;
        z-index: 25;
    }

    .header-row-elemen .sticky-header {
        top: 50px;
        /* Height of kriteria header */
        z-index: 24;
    }

    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 10;
        background: white;
    }

    .sticky-col.sticky-header {
        z-index: 30;
    }

    /* ============================================ */
    /* CORNER HEADER (TOP-LEFT CELL)               */
    /* ============================================ */
    .matrix-header-corner {
        min-width: 180px;
        max-width: 180px;
        padding: 12px;
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
        border: 1px solid #dee2e6;
        text-align: center;
        font-weight: 600;
    }

    .corner-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }

    .corner-label i {
        font-size: 18px;
        margin: 5px 0;
    }

    /* ============================================ */
    /* KRITERIA HEADER (MERGED ROW)                */
    /* ============================================ */
    .matrix-header-kriteria {
        padding: 10px 8px;
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
        color: white;
        border: 1px solid #dee2e6;
        text-align: center;
        vertical-align: middle;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 700;
        height: 50px;
    }

    .matrix-header-kriteria:hover {
        background: linear-gradient(135deg, #7a1b2c 0%, #6d0619 100%);
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(147, 33, 54, 0.4);
    }

    .kriteria-label {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .kriteria-label span {
        font-size: 14px;
    }

    .kriteria-label small {
        font-size: 10px;
        opacity: 0.9;
        font-weight: 500;
    }

    /* ============================================ */
    /* ELEMEN HEADER                               */
    /* ============================================ */
    .matrix-header-elemen {
        min-width: 60px;
        max-width: 60px;
        padding: 8px 4px;
        background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
        color: white;
        border: 1px solid #dee2e6;
        text-align: center;
        vertical-align: middle;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
        height: 45px;
    }

    .matrix-header-elemen:hover {
        background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.4);
    }

    .elemen-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .elemen-code {
        font-size: 11px;
        font-weight: 700;
    }

    /* ============================================ */
    /* ROW HEADERS                                 */
    /* ============================================ */
    .matrix-row {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
    }

    .matrix-header-row {
        min-width: 180px;
        max-width: 180px;
        padding: 10px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 1px solid #dee2e6;
        text-align: left;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .matrix-header-row:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        transform: translateX(3px);
        box-shadow: 3px 0 8px rgba(0, 0, 0, 0.1);
    }

    .row-label {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .kriteria-badge {
        background: #932136;
        color: white;
        padding: 3px 8px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 11px;
        flex-shrink: 0;
    }

    .kriteria-name {
        font-size: 11px;
        color: #333;
        font-weight: 500;
        line-height: 1.3;
    }

    /* ============================================ */
    /* MATRIX CELLS                                */
    /* ============================================ */
    .matrix-cell {
        min-width: 60px;
        max-width: 60px;
        height: 50px;
        border: 1px solid #dee2e6;
        text-align: center;
        vertical-align: middle;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .matrix-cell:hover {
        transform: scale(1.15);
        z-index: 5;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        border: 3px solid #333;
    }

    .matrix-cell.has-score {
        border: 2px solid #333;
    }

    .matrix-cell.has-score:hover {
        border: 3px solid #000;
    }

    .cell-content {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .score-badge {
        background: rgba(255, 255, 255, 0.95);
        color: #333;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 14px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* ============================================ */
    /* LEGEND                                      */
    /* ============================================ */
    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        background: white;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        transition: all 0.2s ease;
    }

    .legend-item:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .legend-box {
        width: 24px;
        height: 24px;
        border-radius: 4px;
        border: 2px solid #333;
        display: inline-block;
    }

    .legend-text {
        font-size: 11px;
        font-weight: 600;
        color: #333;
    }

    /* ============================================ */
    /* STATISTICS BOXES                            */
    /* ============================================ */
    .stat-box {
        padding: 12px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.2s ease;
    }

    .stat-box:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .stat-box h4 {
        font-size: 32px;
        margin-bottom: 4px;
    }

    .stat-box small {
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 10px;
        font-weight: 600;
    }

    /* ============================================ */
    /* ZOOM CONTROLS                               */
    /* ============================================ */
    .matrix-table.zoom-out {
        transform: scale(0.75);
        transform-origin: top left;
    }

    .matrix-table.zoom-in {
        transform: scale(1.25);
        transform-origin: top left;
    }

    /* ============================================ */
    /* TOGGLE VISIBILITY                           */
    /* ============================================ */
    #matrixContainer.hidden {
        display: none !important;
    }

    /* ============================================ */
    /* ANIMATIONS                                  */
    /* ============================================ */
    @keyframes cellPulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.15);
        }
    }

    .matrix-cell.updating {
        animation: cellPulse 0.5s ease;
    }

    @keyframes highlightFade {
        0% {
            box-shadow: 0 0 0 0 rgba(147, 33, 54, 0.7);
        }

        50% {
            box-shadow: 0 0 0 10px rgba(147, 33, 54, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(147, 33, 54, 0);
        }
    }

    .matrix-cell.highlight {
        animation: highlightFade 1s ease;
    }

    /* ============================================ */
    /* RESPONSIVE DESIGN                           */
    /* ============================================ */
    @media (max-width: 768px) {

        .matrix-header-corner,
        .matrix-header-row {
            min-width: 140px;
            max-width: 140px;
            font-size: 10px;
            padding: 6px;
        }

        .matrix-header-kriteria {
            padding: 8px 4px;
            height: 40px;
        }

        .matrix-header-elemen,
        .matrix-cell {
            min-width: 50px;
            max-width: 50px;
            height: 45px;
        }

        .score-badge {
            font-size: 12px;
            padding: 3px 6px;
        }

        .kriteria-name {
            display: none;
        }

        .kriteria-label small {
            display: none;
        }

        .legend-item {
            font-size: 10px;
            padding: 4px 6px;
        }

        .legend-box {
            width: 20px;
            height: 20px;
        }

        .stat-box h4 {
            font-size: 24px;
        }
    }

    @media (max-width: 576px) {

        .matrix-header-elemen,
        .matrix-cell {
            min-width: 45px;
            max-width: 45px;
            height: 40px;
        }

        .header-row-elemen .sticky-header {
            top: 40px;
        }
    }

</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initializeHeatmapMatrix();

        /**
         * ============================================
         * INITIALIZE HEATMAP MATRIX
         * ============================================
         */
        function initializeHeatmapMatrix() {
            updateMatrixStats();
            setupMatrixInteractions();
            setupZoomControls();
            setupToggleButton();
        }

        /**
         * ============================================
         * UPDATE MATRIX STATISTICS
         * ============================================
         */
        function updateMatrixStats() {
            const allCells = document.querySelectorAll('.matrix-cell');
            const filledCells = document.querySelectorAll('.matrix-cell.has-score');

            const allElemen = new Set();
            const filledElemen = new Set();

            allCells.forEach(c => c.dataset.elemenId && allElemen.add(c.dataset.elemenId));
            filledCells.forEach(c => c.dataset.elemenId && filledElemen.add(c.dataset.elemenId));

            const total = allElemen.size;
            const filled = filledElemen.size;
            const empty = total - filled;
            const percent = total ? Math.round((filled / total) * 100) : 0;

            document.getElementById('statTotal').textContent = total;
            document.getElementById('statFilled').textContent = filled;
            document.getElementById('statEmpty').textContent = empty;
            document.getElementById('statPercentage').textContent = percent + '%';
        }

        /**
         * ============================================
         * SETUP MATRIX INTERACTIONS (ENHANCED)
         * ============================================
         */
        function setupMatrixInteractions() {
            // Cell click → Jump to elemen & auto-expand accordions
            document.querySelectorAll('.matrix-header-row, .matrix-cell').forEach(cell => {
                cell.addEventListener('click', function() {
                    const elemenId = this.dataset.elemenId;
                    const kriteriaId = this.dataset.kriteriaId;

                    if (elemenId) {
                        // 1. Expand kriteria accordion
                        const kriteriaCollapse = document.querySelector(`#collapse-kriteria-${kriteriaId}`);
                        if (kriteriaCollapse && !kriteriaCollapse.classList.contains('show')) {
                            new bootstrap.Collapse(kriteriaCollapse, {
                                show: true
                            });
                        }

                        // 2. Wait for kriteria to expand, then expand elemen
                        setTimeout(() => {
                            const elemenCollapse = document.querySelector(`#collapse-elemen-${elemenId}`);
                            if (elemenCollapse && !elemenCollapse.classList.contains('show')) {
                                new bootstrap.Collapse(elemenCollapse, {
                                    show: true
                                });
                            }

                            // 3. Scroll to elemen card
                            setTimeout(() => {
                                const elemenCard = document.querySelector(`.elemen-card[data-elemen-id="${elemenId}"]`);
                                if (elemenCard) {
                                    elemenCard.scrollIntoView({
                                        behavior: 'smooth'
                                        , block: 'center'
                                    });

                                    // Highlight card with pulse effect
                                    elemenCard.style.boxShadow = '0 0 30px rgba(147, 33, 54, 0.6)';
                                    elemenCard.style.transform = 'scale(1.02)';
                                    elemenCard.style.transition = 'all 0.3s ease';

                                    setTimeout(() => {
                                        elemenCard.style.boxShadow = '';
                                        elemenCard.style.transform = '';
                                    }, 2000);
                                }
                            }, 350); // Wait for elemen to expand
                        }, 350); // Wait for kriteria to expand
                    }
                });
            });

            // Kriteria header click → Expand all elemen in that kriteria
            document.querySelectorAll('.matrix-header-kriteria').forEach(header => {
                header.addEventListener('click', function() {
                    const kriteriaId = this.dataset.kriteriaId;
                    const kriteriaCollapse = document.querySelector(`#collapse-kriteria-${kriteriaId}`);

                    if (kriteriaCollapse) {
                        if (kriteriaCollapse.classList.contains('show')) {
                            // If already open, expand all elemen
                            const elemenCollapses = kriteriaCollapse.querySelectorAll('.elemen-collapse');
                            elemenCollapses.forEach(collapse => {
                                new bootstrap.Collapse(collapse, {
                                    show: true
                                });
                            });
                        } else {
                            // Open kriteria
                            new bootstrap.Collapse(kriteriaCollapse, {
                                show: true
                            });

                            // Then expand all elemen
                            setTimeout(() => {
                                const elemenCollapses = kriteriaCollapse.querySelectorAll('.elemen-collapse');
                                elemenCollapses.forEach(collapse => {
                                    new bootstrap.Collapse(collapse, {
                                        show: true
                                    });
                                });
                            }, 350);
                        }

                        // Scroll to kriteria
                        setTimeout(() => {
                            kriteriaCollapse.scrollIntoView({
                                behavior: 'smooth'
                                , block: 'start'
                            });
                        }, 400);
                    }
                });
            });

            // Elemen header click → Expand that specific elemen
            document.querySelectorAll('.matrix-header-elemen').forEach(header => {
                header.addEventListener('click', function() {
                    const kriteriaId = this.dataset.kriteriaId;
                    const elemenId = this.dataset.elemenId;

                    // 1. Expand kriteria first
                    const kriteriaCollapse = document.querySelector(`#collapse-kriteria-${kriteriaId}`);
                    if (kriteriaCollapse && !kriteriaCollapse.classList.contains('show')) {
                        new bootstrap.Collapse(kriteriaCollapse, {
                            show: true
                        });
                    }

                    // 2. Then expand elemen
                    setTimeout(() => {
                        const elemenCollapse = document.querySelector(`#collapse-elemen-${elemenId}`);
                        if (elemenCollapse) {
                            new bootstrap.Collapse(elemenCollapse, {
                                show: true
                            });

                            // Scroll to elemen
                            setTimeout(() => {
                                elemenCollapse.scrollIntoView({
                                    behavior: 'smooth'
                                    , block: 'start'
                                });
                            }, 350);
                        }
                    }, 350);
                });
            });
        }

        /**
         * ============================================
         * SETUP ZOOM CONTROLS
         * ============================================
         */
        function setupZoomControls() {
            const matrixTable = document.getElementById('heatmapMatrix');

            document.getElementById('btnZoomIn').addEventListener('click', function() {
                matrixTable.classList.remove('zoom-out');
                matrixTable.classList.add('zoom-in');
            });

            document.getElementById('btnZoomOut').addEventListener('click', function() {
                matrixTable.classList.remove('zoom-in');
                matrixTable.classList.add('zoom-out');
            });

            document.getElementById('btnResetZoom').addEventListener('click', function() {
                matrixTable.classList.remove('zoom-in', 'zoom-out');
            });
        }

        /**
         * ============================================
         * SETUP TOGGLE BUTTON
         * ============================================
         */
        function setupToggleButton() {
            const btnToggle = document.getElementById('btnToggleMatrix');
            const matrixContainer = document.getElementById('matrixContainer');

            btnToggle.addEventListener('click', function() {
                matrixContainer.classList.toggle('hidden');

                if (matrixContainer.classList.contains('hidden')) {
                    this.innerHTML = '<i class="bi bi-eye"></i> Tampilkan';
                } else {
                    this.innerHTML = '<i class="bi bi-eye-slash"></i> Sembunyikan';
                }
            });
        }

        /**
         * ============================================
         * UPDATE MATRIX CELL (Called after save)
         * ============================================
         */
        window.updateMatrixCell = function(elemenId, skor) {
            skor = parseInt(skor);
            if (isNaN(skor)) return;

            // ambil semua cell untuk elemen ini (pemenuhan + pelampauan)
            const cells = document.querySelectorAll(`.matrix-cell[data-elemen-id="${elemenId}"]`);
            if (!cells.length) return;

            cells.forEach(cell => {
                const colType = cell.dataset.col; // 'pemenuhan' / 'pelampauan'

                // logika: skor 4 → isi hanya pelampauan, skor 0–3 → isi hanya pemenuhan
                const shouldFill =
                    (skor === 4 && colType === 'pelampauan') ||
                    (skor !== 4 && colType === 'pemenuhan');

                cell.classList.toggle('has-score', shouldFill);
                cell.dataset.skor = shouldFill ? skor : '';
                cell.style.backgroundColor = shouldFill ? getSkorColorJS(skor) : '#e0e0e0';

                // animasi kecil
                cell.classList.add('updating');
                setTimeout(() => cell.classList.remove('updating'), 500);
            });

            // update statistik ringkasan
            updateMatrixStats();
        };

        /**
         * ============================================
         * GET COLOR FOR SCORE (JavaScript version)
         * ============================================
         */
        function getSkorColorJS(skor) {
            const colors = {
                0: '#f44336', // Red
                1: '#ff9800', // Orange
                2: '#ffeb3b', // Yellow
                3: '#8bc34a', // Light Green
                4: '#4caf50', // Dark Green
            };

            return colors[skor] || '#e0e0e0';
        }
    });

</script>
@endpush
