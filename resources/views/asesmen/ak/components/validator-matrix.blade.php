<div class="card mb-4 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-clipboard-check"></i> Kertas Kerja Validator - Perbandingan Penilaian
            </h5>
            <div class="btn-group btn-group-sm">
                {{-- <button type="button" class="btn btn-outline-primary" id="btnToggleIndikator" title="Sembunyikan/Tampilkan Indikator">
                    <i class="bi bi-layout-sidebar"></i> <span id="toggleIndikatorText">Sembunyikan</span> Indikator
                </button> --}}
                {{-- <button type="button" class="btn btn-outline-secondary" id="btnExpandIndikator" title="Perlebar Kolom Indikator">
                    <i class="bi bi-arrows-expand"></i> Perlebar
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnCollapseIndikator" title="Persempit Kolom Indikator">
                    <i class="bi bi-arrows-collapse"></i> Persempit
                </button> --}}
                <button type="button" class="btn btn-outline-secondary" id="btnHighlightDiff" title="Highlight Perbedaan">
                    <i class="bi bi-search"></i> Highlight Penilaian yang Beda
                </button>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <!-- Legend -->
        <div class="p-3 bg-light border-bottom">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-2">Legenda Kategori Penilaian:</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="legend-item">
                            <span class="legend-box" style="background: #9e9e9e;"></span>
                            <span class="legend-text">Belum Dinilai</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-box" style="background: #f44336;"></span>
                            <span class="legend-text">0 - Not Met</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-box" style="background: #ff9800;"></span>
                            <span class="legend-text">1 - Not Met</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-box" style="background: #ffeb3b;"></span>
                            <span class="legend-text">2 - Weakness</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-box" style="background: #8bc34a;"></span>
                            <span class="legend-text">3 - Met</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-box" style="background: #4caf50;"></span>
                            <span class="legend-text">4 - Exceeding</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-2">Status Validasi:</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="legend-item">
                            <span class="legend-box" style="background: #e3f2fd; border-color: #2196f3;"></span>
                            <span class="legend-text">Belum Divalidasi</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-box" style="background: #fff3e0; border-color: #ff9800;"></span>
                            <span class="legend-text">Perlu Revisi</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-box" style="background: #e8f5e9; border-color: #4caf50;"></span>
                            <span class="legend-text">Disetujui</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-box validator-diff"></span>
                            <span class="legend-text">Perbedaan Nilai</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Validator Matrix Table -->
        <div class="validator-matrix-wrapper" style="overflow-x: auto; overflow-y: auto; max-height: 800px;">
            <table class="validator-matrix-table" id="validatorMatrix">
                <thead>
                    <tr>
                        {{-- Fixed Columns --}}
                        <th class="vm-header sticky-col sticky-header bg-primary" style="left: 0; min-width: 60px; z-index: 35;">
                            <div class="text-center fw-bold">Kriteria</div>
                        </th>
                        <th class="vm-header sticky-col sticky-header bg-primary" style="left: 60px; min-width: 60px; z-index: 35;">
                            <div class="text-center fw-bold">Kode<br>Elemen</div>
                        </th>
                        <th class="vm-header sticky-col sticky-header elemen-col bg-primary" style="left: 120px; z-index: 35;">
                            <div class="fw-bold">Elemen Standar</div>
                        </th>

                        {{-- Collapsible Indikator Column --}}
                        <th class="vm-header sticky-header indikator-col bg-primary" style="min-width: 240px; z-index: 30;" id="indikatorHeader">
                            <div class="fw-bold">
                                <i class="bi bi-list-ul me-2"></i>Indikator Penilaian
                            </div>
                        </th>

                        {{-- Asesor 1 Columns --}}
                        <th class="vm-header sticky-header text-center" colspan="2" style="background: #e3f2fd; z-index: 30;">
                            <div class="fw-bold text-dark">Penilaian Asesor 1</div>
                            <div class="small text-muted">{{ $asesor1->name ?? 'Asesor 1' }}</div>
                        </th>

                        {{-- Asesor 2 Columns --}}
                        <th class="vm-header sticky-header text-center" colspan="2" style="background: #fff3e0; z-index: 30;">
                            <div class="fw-bold text-dark">Penilaian Asesor 2</div>
                            <div class="small text-muted">{{ $asesor2->name ?? 'Asesor 2' }}</div>
                        </th>

                        {{-- Validasi Column --}}
                        <th class="vm-header sticky-header text-center" style="background: #e8f5e9; min-width: 120px; z-index: 30;">
                            <div class="fw-bold text-dark">Validasi</div>
                        </th>
                    </tr>

                    {{-- Sub-header for Pemenuhan/Pelampauan --}}
                    <tr>
                        <th class="vm-subheader sticky-col sticky-header" style="left: 0; z-index: 34;"></th>
                        <th class="vm-subheader sticky-col sticky-header" style="left: 60px; z-index: 34;"></th>
                        <th class="vm-subheader sticky-col sticky-header" style="left: 120px; z-index: 34;"></th>
                        <th class="vm-subheader sticky-header indikator-col" style="z-index: 29;"></th>

                        {{-- Asesor 1 --}}
                        <th class="vm-subheader sticky-header text-center" style="background: #e3f2fd; min-width: 100px; z-index: 29;">
                            <small class="fw-bold">Pemenuhan</small>
                        </th>
                        <th class="vm-subheader sticky-header text-center" style="background: #e3f2fd; min-width: 100px; z-index: 29;">
                            <small class="fw-bold">Pelampauan</small>
                        </th>

                        {{-- Asesor 2 --}}
                        <th class="vm-subheader sticky-header text-center" style="background: #fff3e0; min-width: 100px; z-index: 29;">
                            <small class="fw-bold">Pemenuhan</small>
                        </th>
                        <th class="vm-subheader sticky-header text-center" style="background: #fff3e0; min-width: 100px; z-index: 29;">
                            <small class="fw-bold">Pelampauan</small>
                        </th>

                        {{-- Validasi --}}
                        <th class="vm-subheader sticky-header text-center" style="background: #e8f5e9; z-index: 29;">
                            <small class="fw-bold">Status</small>
                        </th>
                    </tr>
                </thead>

                @php
                // Preload semua warna skor agar tidak query berkali-kali
                $warnaSkor = \App\Models\JenjangPenilaian::pluck('color', 'skor');

                // Helper function untuk render cell skor
                function renderScoreCell($penilaian, $asesor, $asesorNum, $warnaSkor) {
                $skor = $penilaian->skor ?? null;
                $komentar = $penilaian->komentar ?? '';

                // Pemenuhan (skor != 4)
                $bgPemenuhan = (isset($skor) && $skor != 4) ? ($warnaSkor[$skor] ?? '') : '#e0e0e0';
                $onclickPemenuhan = (isset($skor) && $skor != 4)
                ? "onclick=\"showKomentarPopover(this, '{$asesor->name}', $skor, '".addslashes($komentar)."')\" title='Klik untuk lihat komentar'"
                : '';
                $textColor = $skor == 2 ? 'dark' : 'white';
                $cellPemenuhan = "<td class='vm-cell vm-score-cell vm-clickable' style='background: $bgPemenuhan;' data-asesor='$asesorNum' data-type='pemenuhan' data-skor='$skor' data-komentar='$komentar' $onclickPemenuhan>"
                    . (isset($skor) && $skor != 4 ? "<small class='text-$textColor text-left align-content-start'>$komentar</small>" : '')
                    . "</td>";

                // Pelampauan (skor == 4)
                $bgPelampauan = ($skor == 4) ? ($warnaSkor[4] ?? '') : '#e0e0e0';
                $onclickPelampauan = ($skor == 4)
                ? "onclick=\"showKomentarPopover(this, '{$asesor->name}', 4, '".addslashes($komentar)."')\" title='Klik untuk lihat komentar'"
                : '';
                $cellPelampauan = "<td class='vm-cell vm-score-cell vm-clickable' style='background: $bgPelampauan;' data-asesor='$asesorNum' data-type='pelampauan' data-skor='$skor' data-komentar='$komentar' $onclickPelampauan>"
                    . ($skor == 4 ? "<small class='text-white text-left align-content-start'>$komentar</small>" : '')
                    . "</td>";

                return $cellPemenuhan . $cellPelampauan;
                }
                @endphp

                @foreach($kriterias as $kriteria)
                @php
                $jumlahElemen = $kriteria->elemenStandar->count();
                $firstRow = true;

                // cek apakah di kriteria ini ada elemen yang beda
                $groupHasDiff = $kriteria->elemenStandar->some(function($e) use ($asesor1, $asesor2) {
                $p1 = $e->penilaian->where('id_asesor', $asesor1->id)->first();
                $p2 = $e->penilaian->where('id_asesor', $asesor2->id)->first();
                return $p1 && $p2 && $p1->skor != $p2->skor;
                });
                @endphp

                <tbody class="validator-group" data-has-diff="{{ $groupHasDiff ? 'true' : 'false' }}">
                    @foreach($kriteria->elemenStandar as $elemen)
                    @php
                    $penilaian1 = $elemen->penilaian->where('id_asesor', $asesor1->id)->first();
                    $penilaian2 = $elemen->penilaian->where('id_asesor', $asesor2->id)->first();
                    $validasi = $elemen->penilaian->where('status_validasi', '!=', 'not_validated')->first();
                    $hasDifference = $penilaian1 && $penilaian2 && $penilaian1->skor != $penilaian2->skor;
                    @endphp

                    <tr class="validator-row" data-elemen-id="{{ $elemen->id }}" @if($hasDifference) data-has-diff="true" @endif>

                        {{-- Kriteria --}}
                        @if($firstRow)
                        <td class="vm-cell sticky-col" style="left: 0; z-index: 15;" rowspan="{{ $jumlahElemen }}">
                            <span class="kriteria-badge">{{ $kriteria->kode_kriteria }}</span>
                        </td>
                        @php $firstRow = false; @endphp
                        @endif

                        {{-- Kode Elemen --}}
                        <td class="vm-cell sticky-col text-center" style="left: 60px; z-index: 15;">
                            <strong>{{ $elemen->kode_elemen }}</strong>
                        </td>

                        {{-- Elemen Standar --}}
                        <td class="vm-cell sticky-col elemen-col" style="left: 120px; z-index: 15;">
                            <div class="elemen-text">{{ $elemen->pernyataan_elemen }}</div>
                        </td>

                        {{-- Indikator --}}
                        <td class="vm-cell indikator-col">
                            <div class="indikator-content">
                                @if($elemen->indikator->count() > 0)
                                <ul class="mb-0 ps-3">
                                    @foreach($elemen->indikator as $indikator)
                                    <li class="small">
                                        <strong>{{ $indikator->kode_indikator }}:</strong>
                                        {!! nl2br(e(str_replace("\r\n", "\n",$indikator->deskripsi_indikator))) !!}
                                    </li>
                                    @endforeach
                                </ul>
                                @else
                                <small class="text-muted">-</small>
                                @endif
                            </div>
                        </td>

                        {{-- Skor Asesor --}}
                        {!! renderScoreCell($penilaian1, $asesor1, 1, $warnaSkor) !!}
                        {!! renderScoreCell($penilaian2, $asesor2, 2, $warnaSkor) !!}

                        {{-- Validasi Status --}}
                        <td class="vm-cell vm-validasi-cell text-center">
                            @if($validasi)
                            @if($validasi->status_validasi == 'validated')
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Disetujui
                            </span>
                            <button class="btn btn-sm btn-outline-success d-block w-100 mt-2" onclick="showValidasiDetail({{ $elemen->id }}, {{ $validasi->id }})">
                                <i class="bi bi-eye"></i> Detail
                            </button>
                            @elseif($validasi->status_validasi == 'revision_required')
                            <span class="badge bg-warning btn-validate">
                                <i class="bi bi-exclamation-triangle"></i> Revisi
                            </span>
                            @else
                            <span class="badge bg-info">
                                <i class="bi bi-hourglass"></i> Pending
                            </span>
                            @endif
                            @else
                            <button class="btn btn-sm btn-outline-primary btn-validate" data-elemen-id="{{ $elemen->id }}">
                                <i class="bi bi-check"></i> Validasi
                            </button>
                            @endif

                            {{-- Difference indicator --}}
                            @if($hasDifference)
                            <div class="mt-1">
                                <span class="badge bg-danger badge-sm">
                                    <i class="bi bi-exclamation-circle"></i> Beda
                                </span>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                @endforeach
            </table>
        </div>

        <!-- Summary Statistics -->
        <div class="p-3 bg-light border-top">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="stat-box">
                        <h4 class="mb-0" id="statTotalElemen">0</h4>
                        <small class="text-muted">Total Elemen</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <h4 class="mb-0 text-danger" id="statDifferences">0</h4>
                        <small class="text-muted">Perbedaan Penilaian</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <h4 class="mb-0 text-warning" id="statPending">0</h4>
                        <small class="text-muted">Belum Validasi</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <h4 class="mb-0 text-success" id="statValidated">0</h4>
                        <small class="text-muted">Sudah Validasi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* ============================================ */
    /* VALIDATOR MATRIX TABLE                      */
    /* ============================================ */
    .validator-matrix-table {
        width: max-content;
        min-width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
        background: white;
    }

    .validator-row.review-diff-muted {
        opacity: 0.25;
        filter: grayscale(0.7);
        transition: opacity 0.2s ease, filter 0.2s ease;
    }

    .validator-row.review-diff-focus {
        position: relative;
        z-index: 2;
        box-shadow: 0 0 0 2px #ff9800 inset;
        background-color: #fffbe6;
    }

    /* Kolom Elemen Standar dipersempit */
    .elemen-col {
        min-width: 100px;
        /* sebelumnya 150px inline, sekarang bisa lebih kecil */
        max-width: 150px;
        /* batasi supaya tidak melebar */
    }

    /* Biar teks elemen tetap rapi walau kolom sempit */
    .elemen-text {
        font-size: 11px;
        line-height: 1.4;
        font-weight: 500;
        word-wrap: break-word;
        white-space: normal;
    }

    /* Header Cells */
    .vm-header {
        padding: 12px 8px;
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
        color: white;
        border: 1px solid #dee2e6;
        font-weight: 600;
        text-align: center;
    }

    .vm-subheader {
        padding: 8px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        font-weight: 500;
    }

    /* Regular Cells */
    .vm-cell {
        padding: 10px;
        border: 1px solid #dee2e6;
        background: white;
        vertical-align: top;
    }

    /* Score Cells */
    .vm-score-cell {
        min-width: 100px;
        max-width: 100px;
        height: 60px;
        vertical-align: middle;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .vm-score-cell:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        z-index: 5;
    }

    /* Validasi Cell */
    .vm-validasi-cell {
        min-width: 120px;
        background: #f8f9fa;
    }

    .vm-clickable {
        cursor: pointer;
        position: relative;
    }

    .vm-clickable:hover::after {
        content: '💬';
        position: absolute;
        top: 5px;
        right: 5px;
        font-size: 16px;
    }

    .score-info {
        position: relative;
    }

    /* Sticky Positioning */
    .sticky-col {
        position: sticky;
        background: white;
        z-index: 10;
    }

    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 20;
    }

    .sticky-col.sticky-header {
        z-index: 30;
    }

    /* Indikator Column */
    .indikator-col {
        max-width: 200px;
        transition: all 0.3s ease;
    }

    .indikator-col.collapsed {
        max-width: 0;
        min-width: 0;
        padding: 0;
        border: none;
        overflow: hidden;
    }

    .indikator-col.expanded {
        max-width: 500px;
    }

    .indikator-content {
        font-size: 11px;
        line-height: 1.4;
    }

    /* Elemen Text */
    .elemen-text {
        font-size: 12px;
        line-height: 1.4;
        font-weight: 500;
    }

    /* Kriteria Badge */
    .kriteria-badge {
        background: #932136;
        color: white;
        padding: 4px 10px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 12px;
        display: inline-block;
    }

    /* Score Badge */
    .score-badge {
        background: rgba(255, 255, 255, 0.95);
        color: #333;
        padding: 6px 12px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 16px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        display: inline-block;
    }

    /* Difference Highlight */
    .validator-row[data-has-diff="true"] {
        background: #fff9e6 !important;
    }

    .validator-row[data-has-diff="true"] .vm-score-cell {
        border: 2px solid #ff9800;
    }

    .validator-row.highlight-diff {
        animation: rowPulse 1s ease;
    }

    @keyframes rowPulse {

        0%,
        100% {
            background: #fff9e6;
        }

        50% {
            background: #ffe0b2;
        }
    }

    /* Legend */
    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px;
        background: white;
        border-radius: 4px;
        font-size: 11px;
    }

    .legend-box {
        width: 20px;
        height: 20px;
        border-radius: 3px;
        border: 2px solid #333;
        display: inline-block;
    }

    .validator-diff {
        background: repeating-linear-gradient(45deg,
                #ff9800,
                #ff9800 10px,
                #ffc107 10px,
                #ffc107 20px);
    }

    /* Stat Box */
    .stat-box {
        padding: 15px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .stat-box h4 {
        font-size: 28px;
        font-weight: 700;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .validator-matrix-wrapper {
            font-size: 10px;
        }

        .vm-score-cell {
            min-width: 80px;
        }

        .score-badge {
            font-size: 14px;
            padding: 4px 8px;
        }
    }

</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initializeValidatorMatrix();

        function initializeValidatorMatrix() {
            setupToggleIndikator();
            setupResizeIndikator();
            setupHighlightDifferences();
            setupValidationButtons();
            updateValidatorStats();
        }

        /**
         * Toggle Indikator Column
         */
        function setupToggleIndikator() {
            const btnToggle = document.getElementById('btnToggleIndikator');
            const indikatorCols = document.querySelectorAll('.indikator-col');
            const toggleText = document.getElementById('toggleIndikatorText');

            let isVisible = true;
            if (btnToggle)
                btnToggle.addEventListener('click', function() {
                    isVisible = !isVisible;
                    if (indikatorCols)
                        indikatorCols.forEach(col => {
                            if (isVisible) {
                                col.classList.remove('collapsed');
                                toggleText.textContent = 'Sembunyikan';
                            } else {
                                col.classList.add('collapsed');
                                col.classList.remove('expanded');
                                toggleText.textContent = 'Tampilkan';
                            }
                        });
                });
        }

        /**
         * Resize Indikator Column
         */
        function setupResizeIndikator() {
            const btnExpand = document.getElementById('btnExpandIndikator');
            const btnCollapse = document.getElementById('btnCollapseIndikator');
            const indikatorCols = document.querySelectorAll('.indikator-col');

            if (btnExpand)
                btnExpand.addEventListener('click', function() {
                    indikatorCols.forEach(col => {
                        col.classList.add('expanded');
                        col.classList.remove('collapsed');
                    });
                });
            if (btnCollapse)
                btnCollapse.addEventListener('click', function() {
                    indikatorCols.forEach(col => {
                        col.classList.remove('expanded');
                    });
                });
        }

        /**
         * Highlight Differences
         */
        function setupHighlightDifferences() {
            const btnHighlight = document.getElementById('btnHighlightDiff');

            btnHighlight.addEventListener('click', function() {
                const diffRows = document.querySelectorAll('.validator-row[data-has-diff="true"]');

                diffRows.forEach((row, index) => {
                    setTimeout(() => {
                        row.classList.add('highlight-diff');
                        row.scrollIntoView({
                            behavior: 'smooth'
                            , block: 'center'
                        });

                        setTimeout(() => {
                            row.classList.remove('highlight-diff');
                        }, 1000);
                    }, index * 500);
                });

                if (diffRows.length === 0) {
                    Swal.fire({
                        icon: 'info'
                        , title: 'Tidak Ada Perbedaan'
                        , text: 'Semua penilaian asesor sudah sama.'
                        , timer: 2000
                    });
                }
            });
        }

        /**
         * Setup Validation Buttons
         */
        function setupValidationButtons() {
            document.querySelectorAll('.btn-validate').forEach(btn => {
                btn.addEventListener('click', function() {
                    const elemenId = this.dataset.elemenId;
                    showValidationModal(elemenId);
                });
            });
        }

        /**
         * Show Validation Modal
         */
        function showValidationModal(elemenId) {
            // Implementation will be added in separate modal component
            console.log('Validate elemen:', elemenId);
        }

        /**
         * Update Statistics
         */
        function updateValidatorStats() {
            const totalElemen = document.querySelectorAll('.validator-row').length;
            const differences = document.querySelectorAll('.validator-row[data-has-diff="true"]').length;
            const pending = document.querySelectorAll('.btn-validate').length;
            const validated = totalElemen - pending;

            document.getElementById('statTotalElemen').textContent = totalElemen;
            document.getElementById('statDifferences').textContent = differences;
            document.getElementById('statPending').textContent = pending;
            document.getElementById('statValidated').textContent = validated;
        }
    });

</script>
@endpush
