{{-- resources/views/components/pelaporan/pelaporan-card.blade.php --}}

@php
$pengajuan = $assignment->asesmen->pengajuan;

$typeConfig = [
'dokumen' => [
'color' => 'primary',
'icon' => 'file-earmark-check',
'label' => 'Pelaporan Dokumen',
],
'ak' => [
'color' => 'info',
'icon' => 'clipboard-check',
'label' => 'Pelaporan AK',
],
'al' => [
'color' => 'success',
'icon' => 'building-check',
'label' => 'Pelaporan AL',
],
];

$config = $typeConfig[$type] ?? $typeConfig['dokumen'];
@endphp

<div class="card pelaporan-card h-100">
    <div class="card-body">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="flex-grow-1">
                <h6 class="card-title mb-1">
                    {{ $pengajuan ? $pengajuan->judul : $assignment->asesmen->name }}
                </h6>
                <small class="text-muted">
                    <i class="bi bi-building"></i>
                    {{ $assignment->asesmen->studyProgram->university->name ?? 'N/A' }}
                </small>
            </div>
            @if($isReported)
            <span class="status-indicator completed" title="Pelaporan Selesai"></span>
            @elseif($canReport)
            <span class="status-indicator pending" title="Menunggu Pelaporan"></span>
            @endif
        </div>

        <!-- Info -->
        <div class="mb-3">
            <div class="info-row">
                <small class="text-muted">Program Studi:</small>
                <div class="mt-1">
                    <span class="badge bg-light text-dark">
                        {{ $assignment->asesmen->studyProgram->name ?? 'N/A' }}
                    </span>
                </div>
            </div>

            @if($pengajuan)
            <div class="info-row">
                <small class="text-muted">Nomor Permohonan Akreditasi:</small>
                <div class="mt-1">
                    <code class="small">{{ $pengajuan->nomor_pengajuan }}</code>
                </div>
            </div>

            <div class="info-row">
                <small class="text-muted">Status Permohonan Akreditasi:</small>
                <div class="mt-1">
                    <span class="badge bg-secondary text-wrap">
                        {{ $pengajuan->status_label }}
                    </span>
                </div>
            </div>
            @endif
        </div>

        <!-- Status Alert -->
        @if($isReported)
        <div class="alert alert-success alert-permanent mb-3">
            <small>
                <i class="bi bi-check-circle"></i>
                Pelaporan selesai
                @if($reportedAt)
                pada {{ \App\Libraries\Date::tglWaktu($reportedAt) }}
                @endif
            </small>
        </div>
        @elseif(!$canReport)
        <div class="alert alert-warning alert-permanent mb-3">
            <small>
                <i class="bi bi-info-circle"></i>
                Belum dapat membuat pelaporan. Tunggu status permohonan akreditasi sesuai.
            </small>
        </div>
        @endif

        <!-- Actions -->
        <div class="d-grid gap-2">
            @if($canReport && !$isReported)
            <button type="button" class="btn btn-{{ $config['color'] }} js-open-pelaporan" data-type="{{ $type }}" data-assignment-id="{{ $assignment->id }}" data-nomor="{{ $pengajuan->nomor_pengajuan ?? $assignment->asesmen->code }}">
                <i class="bi bi-upload"></i> Upload {{ $config['label'] }}
            </button>
            @else
            <a href="{{ $type === 'dokumen'
                        ? route('validator.borang.show', $assignment->id)
                        : ($type === 'ak'
                            ? route('ak.validasi.asesor', ['idAsesmen' => $assignment->asesmen->id, 'jenisAsesmen' => 'ak'])
                            : '#') }}" class="btn btn-outline-{{ $config['color'] }}">
                <i class="bi bi-eye"></i> Lihat Detail
            </a>
            @endif
        </div>
    </div>
</div>

<style>
    .pelaporan-card {
        transition: all 0.3s ease;
        border: 2px solid #e0e0e0;
    }

    .pelaporan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-color: #932136;
    }

    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }

    .status-indicator.pending {
        background-color: #ffc107;
        animation: pulse 2s infinite;
    }

    .status-indicator.completed {
        background-color: #28a745;
    }

    .info-row {
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

</style>
