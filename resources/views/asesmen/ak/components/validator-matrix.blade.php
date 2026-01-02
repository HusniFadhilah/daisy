<div class="card mb-4 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
            <h5 class="mb-0 text-wrap text-break flex-grow-1">
                <i class="bi bi-clipboard-check"></i> Kertas Kerja Validator - Perbandingan Penilaian
                <span class="badge bg-primary ms-2 mt-md-2">{{ $asesors->count() }} Asesor</span>
            </h5>
            <div class="btn-group btn-group-sm ms-md-auto">
                <button type="button" class="btn btn-outline-secondary" id="btnHighlightDiff" title="Highlight Perbedaan">
                    <i class="bi bi-search"></i> Highlight Perbedaan
                </button>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
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

        {{-- Validator Matrix Table --}}
        <div class="validator-matrix-wrapper" style="overflow-x: auto; overflow-y: auto; max-height: 800px;">
            <table class="validator-matrix-table" id="validatorMatrix">
                <thead>
                    <tr>
                        {{-- Fixed Columns --}}
                        <th class="vm-header sticky-col sticky-header bg-primary" style="left: 0; min-width: 40px; z-index: 35;">
                            <div class="text-center fw-bold">Kriteria</div>
                        </th>
                        <th class="vm-header sticky-col sticky-header bg-primary" style="left: 40px; min-width: 50px; z-index: 35;">
                            <div class="text-center fw-bold">Kode<br>Elemen</div>
                        </th>
                        <th class="vm-header sticky-col sticky-header elemen-col bg-primary" style="left: 70px; max-width: 120px; z-index: 35;">
                            <div class="fw-bold">Elemen Standar</div>
                        </th>

                        {{-- Collapsible Indikator Column --}}
                        <th class="vm-header sticky-header indikator-col bg-primary" style="min-width: 180px; z-index: 30;" id="indikatorHeader">
                            <div class="fw-bold">
                                <i class="bi bi-list-ul me-2"></i>Indikator Penilaian
                            </div>
                        </th>

                        {{-- Dynamic Asesor Columns --}}
                        @foreach($asesors as $index => $asesor)
                        @php
                        $colors = ['#e3f2fd', '#fff3e0', '#e8f5e9', '#f3e5f5'];
                        $bgColor = $colors[$index % count($colors)];
                        @endphp
                        <th class="vm-header sticky-header text-center" colspan="2" style="background: {{ $bgColor }}; z-index: 30;">
                            <div class="d-flex flex-column align-items-center">
                                <div class="avatar-circle-sm mb-1" style="background: linear-gradient(135deg, #932136, #870820);">
                                    {{ substr($asesor->user->name, 0, 2) }}
                                </div>
                                <div class="fw-bold text-dark">Asesor {{ $asesor->urutan_asesor }}</div>
                                <small class="text-muted">{{ $asesor->user->name }}</small>
                            </div>
                        </th>
                        @endforeach

                        {{-- Validasi Column --}}
                        <th class="vm-header sticky-header text-center" style="background: #e8f5e9; min-width: 120px; z-index: 30;">
                            <div class="fw-bold text-dark">Validasi</div>
                        </th>
                    </tr>

                    {{-- Sub-header for Pemenuhan/Pelampauan --}}
                    <tr>
                        <th class="vm-subheader sticky-col sticky-header" style="left: 0; z-index: 34;"></th>
                        <th class="vm-subheader sticky-col sticky-header" style="left: 40px; z-index: 34;"></th>
                        <th class="vm-subheader sticky-col sticky-header" style="left: 70px; z-index: 34;"></th>
                        <th class="vm-subheader sticky-header indikator-col" style="z-index: 29;"></th>

                        {{-- Dynamic Asesor Sub-headers --}}
                        @foreach($asesors as $index => $asesor)
                        @php
                        $colors = ['#e3f2fd', '#fff3e0', '#e8f5e9', '#f3e5f5'];
                        $bgColor = $colors[$index % count($colors)];
                        @endphp
                        <th class="vm-subheader sticky-header text-center" style="background: {{ $bgColor }}; min-width: 100px; z-index: 29;">
                            <small class="fw-bold">Pemenuhan</small>
                        </th>
                        <th class="vm-subheader sticky-header text-center" style="background: {{ $bgColor }}; min-width: 100px; z-index: 29;">
                            <small class="fw-bold">Pelampauan</small>
                        </th>
                        @endforeach

                        {{-- Validasi --}}
                        <th class="vm-subheader sticky-header text-center" style="background: #e8f5e9; z-index: 29;">
                            <small class="fw-bold">Status</small>
                        </th>
                    </tr>
                </thead>

                @php
                // Preload colors
                $warnaSkor = \App\Models\JenjangPenilaian::pluck('color', 'skor');

                // Helper function untuk render cell skor
                function renderScoreCellDynamic($penilaian, $asesor, $asesorNum, $warnaSkor) {
                $skor = $penilaian->skor ?? null;
                $komentar = $penilaian->komentar ?? '';

                // Pemenuhan (skor != 4)
                $bgPemenuhan = (isset($skor) && $skor != 4) ? ($warnaSkor[$skor] ?? '') : '#e0e0e0';
                $onclickPemenuhan = (isset($skor) && $skor != 4)
                ? "onclick=\"showKomentarPopover(this, '{$asesor->name}', $skor, '".addslashes($komentar)."')\" title='Klik untuk lihat komentar'"
                : '';
                $textColor = $skor == 2 ? 'dark' : 'white';

                $cellPemenuhan = "<td class='vm-cell vm-score-cell vm-clickable' style='background: $bgPemenuhan;' data-asesor='$asesorNum' data-type='pemenuhan' data-skor='$skor' $onclickPemenuhan>"
                    . (isset($skor) && $skor != 4 ? "<small class='text-$textColor'>$komentar</small>" : '')
                    . "</td>";

                // Pelampauan (skor == 4)
                $bgPelampauan = ($skor == 4) ? ($warnaSkor[4] ?? '') : '#e0e0e0';
                $onclickPelampauan = ($skor == 4)
                ? "onclick=\"showKomentarPopover(this, '{$asesor->name}', 4, '".addslashes($komentar)."')\" title='Klik untuk lihat komentar'"
                : '';

                $cellPelampauan = "<td class='vm-cell vm-score-cell vm-clickable' style='background: $bgPelampauan;' data-asesor='$asesorNum' data-type='pelampauan' data-skor='$skor' $onclickPelampauan>"
                    . ($skor == 4 ? "<small class='text-white'>$komentar</small>" : '')
                    . "</td>";

                return $cellPemenuhan . $cellPelampauan;
                }
                @endphp

                @foreach($kriterias as $kriteria)
                @php
                $jumlahElemen = $kriteria->elemenStandar->count();
                $firstRow = true;

                // Check if any elemen has differences
                $groupHasDiff = $kriteria->elemenStandar->some(function($elemen) use ($asesors) {
                $skors = [];
                foreach ($asesors as $asesor) {
                $penilaian = $elemen->penilaian->where('id_asesor', $asesor->id_user)->first();
                if ($penilaian && $penilaian->skor !== null) {
                $skors[] = $penilaian->skor;
                }
                }
                return count(array_unique($skors)) > 1;
                });
                @endphp

                <tbody class="validator-group" data-has-diff="{{ $groupHasDiff ? 'true' : 'false' }}">
                    @foreach($kriteria->elemenStandar as $elemen)
                    @php
                    // Collect all penilaian from all asesors
                    $penilaians = [];
                    $skors = [];

                    foreach ($asesors as $asesor) {
                    $penilaian = $elemen->penilaian->where('id_asesor', $asesor->id_user)->first();
                    $penilaians[$asesor->id_user] = $penilaian;
                    if ($penilaian && $penilaian->skor !== null) {
                    $skors[] = $penilaian->skor;
                    }
                    }

                    $hasDifference = count(array_unique($skors)) > 1;
                    $validasi = $elemen->penilaian->where('status_validasi', '!=', 'not_validated')->first();
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
                        <td class="vm-cell sticky-col text-center" style="left: 40px; z-index: 15;">
                            <strong>{{ $elemen->kode_elemen }}</strong>
                        </td>

                        {{-- Elemen Standar --}}
                        <td class="vm-cell sticky-col elemen-col" style="left: 70px; z-index: 15;">
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
                                        {!! nl2br(e($indikator->deskripsi_indikator)) !!}
                                    </li>
                                    @endforeach
                                </ul>
                                @else
                                <small class="text-muted">-</small>
                                @endif
                            </div>
                        </td>

                        {{-- Dynamic Asesor Skor Columns --}}
                        @foreach($asesors as $asesor)
                        {!! renderScoreCellDynamic($penilaians[$asesor->id_user] ?? new stdClass(), $asesor->user, $asesor->urutan_asesor, $warnaSkor) !!}
                        @endforeach

                        {{-- Validasi Status --}}
                        <td class="vm-cell vm-validasi-cell text-center">
                            @if($validasi)
                            @if($validasi->status_validasi == 'validated' || $validasi->status_validasi == 'approved')
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Disetujui
                            </span>
                            <button class="btn btn-sm btn-outline-info d-block w-100 mt-2" onclick="showValidasiDetail({{ $elemen->id }}, {{ $validasi->id }})">
                                <i class="bi bi-eye"></i> Detail
                            </button>
                            @elseif($validasi->status_validasi == 'revision_required')
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-exclamation-triangle"></i> Revisi
                            </span>
                            <button class="btn btn-sm btn-outline-warning d-block w-100 mt-2 btn-validate" data-elemen-id="{{ $elemen->id }}">
                                <i class="bi bi-eye"></i> Lihat
                            </button>
                            @endif
                            @else
                            <button class="btn btn-sm btn-primary btn-validate w-100" data-elemen-id="{{ $elemen->id }}">
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

        {{-- Summary Statistics --}}
        <div class="p-3 bg-light border-top">
            <div class="row text-center">
                <div class="col-md-4 col-lg-3 my-2">
                    <div class="stat-box">
                        <h4 class="mb-0" id="statTotalElemen">0</h4>
                        <small class="text-muted">Total Elemen</small>
                    </div>
                </div>
                <div class="col-md-4 col-lg-3 my-2">
                    <div class="stat-box">
                        <h4 class="mb-0 text-danger" id="statDifferences">0</h4>
                        <small class="text-muted">Perbedaan Penilaian</small>
                    </div>
                </div>
                <div class="col-md-4 col-lg-3 my-2">
                    <div class="stat-box">
                        <h4 class="mb-0 text-warning" id="statPending">0</h4>
                        <small class="text-muted">Belum Validasi</small>
                    </div>
                </div>
                <div class="col-md-4 col-lg-3 my-2">
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
    /* Matrix Table Styles */
    .validator-matrix-table {
        width: max-content;
        min-width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 11px;
        background: white;
    }

    /* Review Modes */
    .validator-row.review-diff-muted {
        opacity: 0.25;
        filter: grayscale(0.7);
        transition: all 0.2s ease;
    }

    .validator-row.review-diff-focus {
        position: relative;
        z-index: 2;
        box-shadow: 0 0 0 3px #ff9800 inset;
        background-color: #fffbe6;
    }

    /* Elemen Column */
    .elemen-col {
        max-width: 80px;
    }

    .elemen-text {
        font-size: 11px;
        line-height: 1.4;
        font-weight: 500;
        word-wrap: break-word;
        white-space: normal;
    }

    /* Header Cells */
    .vm-header {
        padding: 10px 8px;
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
        color: white;
        border: 1px solid #dee2e6;
        font-weight: 600;
        text-align: center;
    }

    .vm-subheader {
        padding: 6px 8px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        font-weight: 500;
    }

    /* Regular Cells */
    .vm-cell {
        padding: 8px;
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

    .vm-validasi-cell {
        min-width: 120px;
        background: #f8f9fa;
    }

    .vm-clickable:hover::after {
        content: '💬';
        position: absolute;
        top: 5px;
        right: 5px;
        font-size: 14px;
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
        z-index: 40;
    }

    /* Indikator Column */
    .indikator-col {
        max-width: 200px;
    }

    .indikator-content {
        font-size: 10px;
        line-height: 1.3;
    }

    /* Kriteria Badge */
    .kriteria-badge {
        background: #932136;
        color: white;
        padding: 4px 10px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 11px;
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
        padding: 12px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .stat-box h4 {
        font-size: 24px;
        font-weight: 700;
    }

    .avatar-circle-sm {
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
    document.addEventListener('DOMContentLoaded', function() {
        initializeValidatorMatrix();

        function initializeValidatorMatrix() {
            setupHighlightDifferences();
            setupValidationButtons();
            updateValidatorStats();
        }

        function setupHighlightDifferences() {
            const btnHighlight = document.getElementById('btnHighlightDiff');
            if (!btnHighlight) return;

            btnHighlight.addEventListener('click', function() {
                const diffRows = document.querySelectorAll('.validator-row[data-has-diff="true"]');

                if (diffRows.length === 0) {
                    Swal.fire({
                        icon: 'info'
                        , title: 'Tidak Ada Perbedaan'
                        , text: 'Semua penilaian asesor sudah sama.'
                        , timer: 2000
                    });
                    return;
                }

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
            });
        }

        function setupValidationButtons() {
            document.querySelectorAll('.btn-validate').forEach(btn => {
                btn.addEventListener('click', function() {
                    const elemenId = this.dataset.elemenId;
                    openValidationModal(elemenId);
                });
            });
        }

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
