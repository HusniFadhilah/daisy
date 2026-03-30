{{-- resources\views\asesmen\ak\components\finalisasi-button.blade.php --}}
@php
$submitBlockReason = match(true) {
!$isComplete => 'incomplete',
$hasRevisionRequests => 'has_revision',
!$split['allComplete'] => 'other_not_done',
$hasSplit => 'has_split',
default => 'none',
};
$isRouteBerkasShow = request()->routeIs('ak.berkas.show*');
$submitDisabled = $submitBlockReason !== 'none';
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
    @if(!$isSubmittedOnly && !$isApproved)
    <div>
        {{-- ── Tombol Finalisasi ──────────────────────────────────── --}}
        <button class="btn btn-success" id="btnSubmit" {{ $submitDisabled ? 'disabled' : '' }}>
            <i class="bi bi-check-circle"></i> Finalisasi dan Kirim
        </button>
    </div>

    @elseif($isSubmittedOnly)
    <button class="btn btn-secondary" disabled>
        <i class="bi bi-clock-history"></i> Menunggu Validasi
    </button>
    @else
    <button class="btn btn-success" disabled>
        <i class="bi bi-check-all"></i> Penilaian Disetujui
    </button>
    @endif

    @if($isRouteBerkasShow)
    <!-- Excel Buttons -->
    <div class="btn-group">
        <!-- Download -->
        <button class="btn btn-primary dropdown-toggle flex-grow-1 flex-md-grow-0" data-bs-toggle="dropdown">
            <i class="bi bi-download"></i> Download Excel
        </button>

        <ul class="dropdown-menu">
            <li class="dropdown-header">
                <i class="bi bi-file-earmark-excel"></i> Pilih Jenis Excel
            </li>
            <li>
                <hr class="dropdown-divider">
            </li>

            <!-- Download Templat -->
            <li>
                <a class="dropdown-item" href="{{ route('ak.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'template']) }}" id="btnDownloadTemplate">
                    <i class="bi bi-file-earmark-text text-info"></i> Download Templat
                    <small class="d-block text-muted">Format Excel sebagai templat</small>
                </a>
            </li>

            <!-- Hasil Penilaian - Lengkap -->
            <li>
                <a class="dropdown-item btnDownloadData" data-mode="full" href="{{ route('ak.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'full']) }}">
                    <i class="bi bi-file-earmark-spreadsheet text-primary"></i> Hasil Penilaian Lengkap
                    <small class="d-block text-muted">Menu + Kertas Kerja + Semua Asesor</small>
                </a>
            </li>

            <!-- Hasil Penilaian - Personal -->
            <li>
                <a class="dropdown-item btnDownloadData" data-mode="personal" href="{{ route('ak.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'personal']) }}">
                    <i class="bi bi-person-check text-success"></i> Hasil Penilaian Anda
                    <small class="d-block text-muted">Hanya Sheet Penilaian Anda</small>
                </a>
            </li>
        </ul>

        <!-- Upload -->
        <div class="btn-group">
            <button class="btn btn-outline-primary" id="btnImport" {{ ($isSubmittedOnly || $isApproved || $hasRevisionRequests) ? 'disabled' : '' }} title="{{ $hasRevisionRequests ? 'Selesaikan revisi terlebih dahulu' : '' }}">
                <i class="bi bi-upload"></i> Upload Excel
            </button>

            <!-- History -->
            <button class="btn btn-outline-secondary" id="btnImportHistory">
                <i class="bi bi-clock-history"></i>
            </button>

            <!-- Reset -->
            <button class="btn btn-outline-danger" id="btnResetAll" {{ $isSubmittedOnly || $isApproved ? 'disabled' : '' }}>
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
    @else
    <a href="{{ route('ak.berkas.show', $asesmen->id) }}" class="btn btn-outline-primary">
        <i class="bi bi-eye"></i> Lihat Detail Penilaian
    </a>
    @endif
</div>

@include('asesmen.ak.components.split-table-container')
