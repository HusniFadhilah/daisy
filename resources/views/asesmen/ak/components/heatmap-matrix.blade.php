<!-- Heatmap Matrix Component - Enhanced with Merged Cells -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-grid-3x3"></i> Matriks Visualisasi Penilaian
            </h5>
            <div class="btn-group btn-group-sm flex-wrap">
                <button type="button" class="btn btn-outline-info" id="btnViewComparison" data-id-asesmen="{{ $asesmen->id }}" data-jenis-asesmen="ak" title="Lihat Perbandingan Penilaian Antar Asesor">
                    <i class="bi bi-people"></i> Cek Split Asesor
                </button>
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
            <h6 class="mb-2">Keterangan Kategori Penilaian:</h6>
            <div class="d-flex flex-wrap gap-3">
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
                                <span class="fw-bold">{{ \App\Models\JenjangPenilaian::PEMENUHAN_STANDAR }}</span>
                            </div>
                        </th>

                        {{-- Kolom 2: Pelampauan Standar --}}
                        <th class="matrix-header-kriteria sticky-header">
                            <div class="kriteria-label">
                                <span class="fw-bold">{{ \App\Models\JenjangPenilaian::PELAMPAUAN_STANDAR }}</span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($kriterias as $kriteria)
                    @php
                    $jumlahElemen = $kriteria->elemenStandar->count();
                    $firstRow = true;
                    @endphp

                    @foreach($kriteria->elemenStandar as $elemen)
                    @php
                    // Get penilaianElemenAk for this elemen (not indikator!)
                    $penilaianElemenAk = $elemen->penilaianElemenAk->first(); // Assuming relation exists
                    $hasPenilaian = $penilaianElemenAk && $penilaianElemenAk->skor !== null;
                    @endphp
                    <tr>
                        {{-- CETAK MERGED CELL HANYA DI ROW PERTAMA --}}
                        @if($firstRow)
                        <th class="matrix-row sticky-col text-center" rowspan="{{ $jumlahElemen }}">
                            <span class="kriteria-badge">{{ $kriteria->kode_kriteria }}</span>
                        </th>
                        @php $firstRow = false; @endphp
                        @endif

                        {{-- Kolom Elemen --}}
                        <td class="matrix-header-row sticky-col" data-elemen-id="{{ $elemen->id }}" data-kriteria-id="{{ $kriteria->id }}">
                            {{ $elemen->kode_elemen }} – {{ Str::limit($elemen->pernyataan_elemen, 30) }}
                        </td>

                        {{-- Kolom Pemenuhan --}}
                        <td class="matrix-cell" data-elemen-id="{{ $elemen->id }}" data-kriteria-id="{{ $kriteria->id }}" data-col="pemenuhan" style="background-color: {{ $hasPenilaian ? $penilaianElemenAk->skor == 4 ? '#e0e0e0' : \App\Models\JenjangPenilaian::getSkorColor($penilaianElemenAk->skor) : '#e0e0e0' }}">
                        </td>

                        {{-- Kolom Pelampauan --}}
                        <td class="matrix-cell" data-elemen-id="{{ $elemen->id }}" data-kriteria-id="{{ $kriteria->id }}" data-col="pelampauan" style="background-color: {{ $hasPenilaian ? $penilaianElemenAk->skor == 4 ? \App\Models\JenjangPenilaian::getSkorColor(4) : '#e0e0e0' : '#e0e0e0' }}">
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/heatmap-matrix.css') }}">
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
            //updateMatrixStats();
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

            document.getElementById('summaryTotal').innerHTML = `<b>${total}</b>`;
            document.getElementById('summaryCompleted').innerHTML = `<b>${filled}</b>`;
            document.getElementById('summaryRemaining').innerHTML = `<b>${empty}</b>`;
            document.getElementById('summaryPercentage').innerHTML = `<b>${percent}%</b>`;
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
            try {
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
            } catch (error) {
                console.error('Error updating matrix cell:', error);
            }
        };
    });

</script>
@endpush
