@extends('layouts.template.app')

@section('title', 'Detail Penerimaan Dokumen - ' . $pengajuan->nomor_pengajuan)

@push('styles')
<style>
    .document-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .document-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }

    .document-card.uploaded {
        border-color: #28a745;
        background: #f8fff9;
    }

    .document-card.missing {
        border-color: #dc3545;
        background: #fff8f8;
    }

    .doc-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    /* Compact list item */
    .doc-item {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: .65rem .75rem;
        margin-bottom: .5rem;
        transition: .2s ease;
        background: #fff;
    }

    .doc-item:hover {
        border-color: #667eea;
        box-shadow: 0 2px 8px rgba(102, 126, 234, .12);
    }

    .doc-item.uploaded {
        border-color: #28a745;
        background: #f8fff9;
    }

    .doc-item.missing {
        border-color: #dc3545;
        background: #fff8f8;
    }

    /* Smaller icon */
    .doc-ico {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex: 0 0 34px;
    }

    /* Tight text */
    .doc-title {
        font-size: .92rem;
        margin: 0;
        line-height: 1.25;
    }

    .doc-meta {
        font-size: .78rem;
        color: #6c757d;
    }

    /* Compact progress */
    .doc-progress .progress {
        height: 12px;
        border-radius: 999px;
    }

    .doc-progress .progress-bar {
        font-size: .70rem;
        line-height: 12px;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="bi bi-file-earmark-text"></i> Detail Penerimaan Dokumen
                    </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('de.penerimaan-dokumen') }}">Penerimaan Dokumen</a>
                            </li>
                            <li class="breadcrumb-item active">{{ $pengajuan->nomor_pengajuan }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('de.penerimaan-dokumen') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- ROW 1: Daftar Dokumen (FULL WIDTH) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Dokumen</h5>

                        @if($docCompleteness['is_complete'])
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Lengkap
                        </span>
                        @else
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-exclamation-triangle"></i> Belum Lengkap
                        </span>
                        @endif
                    </div>

                    {{-- Progress menyatu di header --}}
                    <div class="doc-progress mt-2">
                        <div class="d-flex justify-content-between small">
                            <span>Kelengkapan</span>
                            <span class="fw-semibold">{{ $docCompleteness['percentage'] }}%</span>
                        </div>
                        <div class="progress mt-1">
                            <div class="progress-bar {{ $docCompleteness['is_complete'] ? 'bg-success' : 'bg-warning' }}" style="width: {{ $docCompleteness['percentage'] }}%" role="progressbar" aria-valuenow="{{ $docCompleteness['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Biar rapi di layar besar: 2 kolom --}}
                    <div class="row">
                        @foreach($docCompleteness['details'] as $jenis => $detail)
                        @php $dokumen = $uploadedDocuments[$jenis] ?? null; @endphp

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="doc-item {{ $detail['uploaded'] ? 'uploaded' : 'missing' }}">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="doc-ico {{ $detail['uploaded'] ? 'bg-success' : 'bg-danger' }} bg-opacity-10 text-{{ $detail['uploaded'] ? 'success' : 'danger' }}">
                                        <i class="bi bi-{{ $detail['uploaded'] ? 'check-lg' : 'x-lg' }}"></i>
                                    </div>

                                    <div class="flex-grow-1">
                                        <p class="doc-title fw-semibold">{{ $detail['label'] }}</p>

                                        @if($dokumen)
                                        <div class="doc-meta">
                                            <div class="text-truncate" title="{{ $dokumen->original_filename }}">
                                                {{ $dokumen->original_filename }}
                                            </div>
                                            <div>
                                                {{ $dokumen->file_size_formatted }} • {{ $dokumen->created_at->format('d M Y H:i') }}
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <a href="{{ $dokumen->download_url }}" class="btn btn-xs btn-success" target="_blank">
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                        </div>
                                        @else
                                        <span class="badge bg-danger mt-1">Belum Diunggah</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        {{-- Kalau kamu TETAP mau render suplemen terpisah, jangan double kalau sudah masuk details --}}
                        @if(!empty($uploadedDocuments['suplemen']) && empty($docCompleteness['details']['suplemen']))
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="doc-item uploaded">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="doc-ico bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-plus-lg"></i>
                                    </div>

                                    <div class="flex-grow-1">
                                        <p class="doc-title fw-semibold mb-0">Dokumen Suplemen</p>
                                        <div class="doc-meta">
                                            <div class="text-truncate" title="{{ $uploadedDocuments['suplemen']->original_filename }}">
                                                {{ $uploadedDocuments['suplemen']->original_filename }}
                                            </div>
                                            <div>{{ $uploadedDocuments['suplemen']->file_size_formatted }}</div>
                                        </div>

                                        <div class="mt-2">
                                            <a href="{{ $uploadedDocuments['suplemen']->download_url }}" class="btn btn-xs btn-info" target="_blank">
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 2: Validator / Tugaskan Validator (FULL WIDTH DI BAWAH) --}}
    <div class="row mb-4">
        <div class="col-12">
            @if(in_array($pengajuan->status, [
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
            ]) && $pengajuan->latestBorangImport)

            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-check"></i>
                            Validasi LED+Suplemen dan LKPS
                        </h5>

                        @if($canAssignValidator)
                        <a href="{{ route('de.penerimaan-dokumen.assign-validator.form', $pengajuan->id) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-person-plus"></i>
                            {{ $currentValidator ? 'Tugaskan Ulang Validator' : 'Tugaskan Validator' }}
                        </a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @if($currentValidator)
                    {{-- Validator Info (biarkan persis milikmu) --}}
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="avatar-circle mx-auto mb-2" style="width: 80px; height: 80px; font-size: 2rem;">
                                    {{ substr($currentValidator->user->name, 0, 1) }}
                                </div>
                                <h6 class="fw-bold">{{ $currentValidator->user->name }}</h6>
                                <small class="text-muted">{{ $currentValidator->user->email }}</small>
                            </div>
                        </div>

                        <div class="col-md-9">
                            {{-- ...lanjutan kode validator kamu 그대로... --}}
                        </div>
                    </div>
                    @else
                    {{-- No validator assigned --}}
                    <div class="text-center py-4">
                        <i class="bi bi-person-x" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3 mb-3">
                            Belum ada validator yang ditugaskan untuk review dokumen
                        </p>

                        @if($canAssignValidator)
                        <a href="{{ route('de.penerimaan-dokumen.assign-validator.form', $pengajuan->id) }}" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i>
                            Tugaskan Validator Sekarang
                        </a>
                        @else
                        <p class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Dokumen harus lengkap terlebih dahulu sebelum menugaskan validator
                        </p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            @endif
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8 mb-4">
            <!-- Informasi Pengajuan -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Pengajuan</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Nomor Pengajuan</th>
                            <td><strong>{{ $pengajuan->nomor_pengajuan }}</strong></td>
                        </tr>
                        <tr>
                            <th>Program Studi</th>
                            <td>
                                <strong>{{ $pengajuan->studyProgram->name }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $pengajuan->studyProgram->university->name }}
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <th>Jenjang</th>
                            <td>{{ $pengajuan->studyProgram->degreeLevel->name }}</td>
                        </tr>
                        <tr>
                            <th>Tahun Akreditasi</th>
                            <td>{{ $pengajuan->tahun_akreditasi }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Akreditasi</th>
                            <td>
                                <span class="badge bg-info">
                                    {{ ucwords(str_replace('-', ' ', $pengajuan->jenis_akreditasi)) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge {{ $pengajuan->status_badge_class }}">
                                    {{ $pengajuan->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Upload Dokumen</th>
                            <td>{{ $pengajuan->tanggal_draft_borang?->format('d F Y H:i') ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column - Actions -->
        <div class="col-lg-4">
            <!-- Timeline -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled timeline">
                        <li class="mb-3">
                            <i class="bi bi-circle-fill text-primary"></i>
                            <strong>Pengajuan Dibuat</strong>
                            <br>
                            <small class="text-muted">{{ $pengajuan->created_at->format('d F Y H:i') }}</small>
                        </li>

                        @if($pengajuan->tanggal_pembayaran)
                        <li class="mb-3">
                            <i class="bi bi-circle-fill text-success"></i>
                            <strong>Pembayaran Terverifikasi</strong>
                            <br>
                            <small class="text-muted">
                                {{ $pengajuan->pembayaran->tanggal_verifikasi?->format('d F Y H:i') }}
                            </small>
                        </li>
                        @endif

                        @if($pengajuan->tanggal_draft_borang)
                        <li class="mb-3">
                            <i class="bi bi-circle-fill text-info"></i>
                            <strong>Dokumen Diunggah</strong>
                            <br>
                            <small class="text-muted">{{ $pengajuan->tanggal_draft_borang->format('d F Y H:i') }}</small>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
