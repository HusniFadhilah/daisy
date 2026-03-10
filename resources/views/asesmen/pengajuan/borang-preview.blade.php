@extends('layouts.template.app')

@section('title', 'Preview LED - ' . $pengajuan->studyProgram->name)

@push('styles')
<style>
    .preview-section {
        page-break-inside: avoid;
        margin-bottom: 2rem;
    }

    .preview-header {
        background: linear-gradient(135deg, #932136 0%, #6d1829 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 8px 8px 0 0;
    }

    .preview-content {
        border: 1px solid #dee2e6;
        border-top: none;
        padding: 1.5rem;
        background: white;
        border-radius: 0 0 8px 8px;
    }

    .elemen-preview {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .elemen-preview:last-child {
        border-bottom: none;
    }

    .table-preview {
        overflow-x: auto;
        margin-top: 1rem;
    }

    .table-preview table {
        font-size: 0.875rem;
    }

    .narasi-content {
        background: #f8f9fa;
        padding: 1rem;
        border-left: 4px solid #932136;
        border-radius: 4px;
        line-height: 1.8;
        white-space: pre-wrap;
    }

    .field-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .field-value {
        background: #ffffff;
        padding: 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        min-height: 50px;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .preview-section {
            page-break-inside: avoid;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    {{-- Header Actions --}}
    <div class="card mb-4 no-print">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                <div>
                    <h4 class="mb-1">Preview Laporan Evaluasi Diri</h4>
                    <p class="text-muted mb-0">
                        {{ $pengajuan->studyProgram->name }} - {{ $pengajuan->nomor_pengajuan }}
                    </p>
                </div>

                <div class="btn-group">
                    <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Cetak
                    </button>
                    <button type="button" class="btn btn-success" id="btnExportPDF">
                        <i class="bi bi-file-pdf"></i> Export PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Document Header --}}
    <div class="card mb-4">
        <div class="preview-header text-center">
            <h3 class="mb-2">LAPORAN EVALUASI DIRI</h3>
            <h5 class="mb-3">{{ $pengajuan->studyProgram->full_name }}</h5>
            <div class="row text-center">
                <div class="col-md-4">
                    <small>Universitas</small>
                    <p class="mb-0"><strong>{{ $pengajuan->studyProgram->university->name }}</strong></p>
                </div>
                <div class="col-md-4">
                    <small>Jenjang</small>
                    <p class="mb-0"><strong>{{ $pengajuan->studyProgram->degreeLevel->name }}</strong></p>
                </div>
                <div class="col-md-4">
                    <small>Tahun Akreditasi</small>
                    <p class="mb-0"><strong>{{ $pengajuan->tahun_akreditasi }}</strong></p>
                </div>
            </div>
        </div>

        <div class="preview-content">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">Nomor Permohonan Akreditasi</th>
                            <td>: {{ $pengajuan->nomor_pengajuan }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Akreditasi</th>
                            <td>: {{ $pengajuan->jenis_akreditasi_label }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Permohonan Akreditasi</th>
                            <td>: {{ \App\Libraries\Date::tglIndo($pengajuan->tanggal_pengajuan) }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">Kode Program Studi</th>
                            <td>: {{ $pengajuan->studyProgram->code }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>:
                                <span class="badge {{ $pengajuan->status_badge_class }}">
                                    {{ $pengajuan->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Diajukan oleh</th>
                            <td>: {{ $pengajuan->pengaju->name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Content Sections --}}
    @if($import && $import->sections->count() > 0)
    @foreach($import->sections as $section)
    <div class="preview-section">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <span class="badge bg-primary me-2">{{ $section->kode_section }}</span>
                    {{ $section->judul_section }}
                </h5>
            </div>

            <div class="card-body">
                {{-- Konten Narasi --}}
                @if($section->konten_narasi)
                <div class="mb-4">
                    <h6 class="field-label">
                        <i class="bi bi-file-text"></i> Deskripsi/Narasi
                    </h6>
                    <div class="narasi-content">
                        {{ $section->konten_narasi }}
                    </div>
                </div>
                @endif

                {{-- Tables --}}
                @if($section->tables->count() > 0)
                <div class="mb-3">
                    <h6 class="field-label">
                        <i class="bi bi-table"></i> Data Tabel
                    </h6>

                    @foreach($section->tables as $table)
                    <div class="table-preview mb-4">
                        <p class="mb-2">
                            <strong>{{ $table->kode_tabel }}</strong> - {{ $table->judul_tabel }}
                        </p>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                @if($table->headers)
                                <thead class="table-light">
                                    <tr>
                                        @foreach($table->headers as $header)
                                        <th>{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                @endif

                                <tbody>
                                    @if($table->data && count($table->data) > 1)
                                    @foreach(array_slice($table->data, 1) as $row)
                                    <tr>
                                        @foreach($row as $cell)
                                        <td>{{ $cell }}</td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr>
                                        <td colspan="{{ count($table->headers ?? []) }}" class="text-center text-muted">
                                            <em>Belum ada data</em>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Dataset Fields (from online form) --}}
                @if($section->elemen && $section->elemen->datasetBorang->count() > 0)
                <div class="mt-4">
                    <h6 class="field-label">
                        <i class="bi bi-list-check"></i> Data Pendukung
                    </h6>

                    <div class="row">
                        @foreach($section->elemen->datasetBorang as $dataset)
                        @php
                        $value = \App\Models\BorangData::where('id_borang_import', $import->id)
                        ->where('dataset_id', $dataset->kode)
                        ->value('nilai');
                        @endphp

                        @if($value)
                        <div class="col-md-6 mb-3">
                            <div class="field-label">{{ $dataset->nama }}</div>
                            <div class="field-value">
                                @if($dataset->tipe_field === 'file')
                                <a href="{{ Storage::url($value) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark"></i> Lihat File
                                </a>
                                @else
                                {{ $value }}
                                @endif
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
    @else
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>Belum ada data borang.</strong> Silakan upload draft LED atau isi melalui form online.
    </div>
    @endif

    {{-- Footer / Signature Section --}}
    <div class="card mt-5 no-print">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Ketua Program Studi,</strong></p>
                    <div style="height: 80px;"></div>
                    <p class="mb-0">_________________________</p>
                    <small class="text-muted">Nama & Tanda Tangan</small>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1"><strong>Tanggal:</strong> {{ \App\Libraries\Date::tglIndo(now()) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Export PDF (could use jsPDF or similar library)
        const btnExportPDF = document.getElementById('btnExportPDF')
        if (btnExportPDF) btnExportPDF.addEventListener('click', function() {
            Swal.fire({
                icon: 'info'
                , title: 'Export PDF'
                , text: 'Fitur export PDF dalam pengembangan. Silakan gunakan Print to PDF dari browser.'
                , confirmButtonColor: '#932136'
            });
        });
    });

</script>
@endpush
