@extends('layouts.template.app')

@section('title', 'Preview Borang - ' . $pengajuan->nomor_pengajuan)

@push('styles')
<style>
    .borang-container {
        background: white;
        padding: 40px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-top: 40px;
        margin-bottom: 25px;
        position: relative;
        overflow: hidden;
    }

    .section-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .section-header h3 {
        margin: 0;
        position: relative;
        z-index: 1;
    }

    .table-header {
        background: #f8f9fa;
        padding: 12px 20px;
        border-left: 5px solid #0d6efd;
        margin-bottom: 15px;
        font-weight: 600;
        border-radius: 5px;
    }

    .borang-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
        font-size: 14px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .borang-table th {
        background: #343a40;
        color: white;
        padding: 14px 12px;
        text-align: left;
        font-weight: 600;
        border: 1px solid #dee2e6;
        font-size: 13px;
    }

    .borang-table td {
        padding: 12px;
        border: 1px solid #dee2e6;
        vertical-align: top;
        background: white;
    }

    .borang-table tbody tr:nth-child(even) td {
        background: #f8f9fa;
    }

    .borang-table tbody tr:hover td {
        background: #e9ecef;
        transition: background 0.2s;
    }

    .konten-narasi {
        background: linear-gradient(to right, #f1f3f5 0%, #ffffff 100%);
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        line-height: 1.9;
        white-space: pre-wrap;
        border-left: 4px solid #6c757d;
    }

    .badge-mapping {
        font-size: 11px;
        padding: 5px 10px;
        border-radius: 4px;
    }

    .stats-card {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .stats-card h3 {
        font-size: 2.5rem;
        margin-bottom: 5px;
        font-weight: 700;
    }

    .empty-table-warning {
        background: #fff3cd;
        padding: 20px;
        border-left: 5px solid #ffc107;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .cover-page {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        margin: -40px;
        margin-bottom: 40px;
        padding: 40px;
        text-align: center;
    }

    .cover-page h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .cover-page h2 {
        font-size: 2rem;
        margin-bottom: 15px;
    }

    .cover-page h3 {
        font-size: 1.5rem;
        margin-bottom: 30px;
        opacity: 0.9;
    }

    .section-toc {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
    }

    .section-toc ul {
        list-style: none;
        padding-left: 0;
    }

    .section-toc li {
        padding: 8px 0;
        border-bottom: 1px solid #dee2e6;
    }

    .section-toc li:last-child {
        border-bottom: none;
    }

    .section-toc a {
        text-decoration: none;
        color: #495057;
        display: flex;
        justify-content: space-between;
    }

    .section-toc a:hover {
        color: #0d6efd;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .borang-container {
            box-shadow: none;
            padding: 20px;
        }

        .section-header {
            background: #667eea !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .cover-page {
            page-break-after: always;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .section-wrapper {
            page-break-inside: avoid;
        }

        .table-wrapper {
            page-break-inside: avoid;
        }
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header - No Print -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h2>
                <i class="bi bi-file-earmark-richtext"></i>
                Preview Borang Evaluasi
            </h2>
            <p class="text-muted mb-0">
                {{ $pengajuan->studyProgram->name }} - {{ $pengajuan->nomor_pengajuan }}
            </p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print / PDF
            </button>
            <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Stats Cards - No Print -->
    <div class="row mb-4 no-print">
        <div class="col-md-3">
            <div class="stats-card">
                <h3>{{ $import->total_sections }}</h3>
                <small>Total Bagian</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h3>{{ $import->total_tables }}</h3>
                <small>Total Tabel</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h3>{{ $import->parsed_tables }}/{{ $import->total_tables }}</h3>
                <small>Tabel Terproses</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h3>{{ $import->completion_percentage }}%</h3>
                <small>Kelengkapan</small>
            </div>
        </div>
    </div>

    <!-- Info Alert - No Print -->
    <div class="alert alert-info alert-permanent no-print">
        <i class="bi bi-info-circle"></i>
        <strong>Informasi:</strong> Preview ini dihasilkan dari proses otomatis dokumen DOCX Anda.
        Pastikan semua data sudah benar sebelum melanjutkan. Gunakan tombol "Print" di atas untuk export ke PDF.
    </div>

    <!-- Main Content -->
    <div class="borang-container">
        <!-- Cover Page -->
        <div class="cover-page">
            <div style="max-width: 800px;">
                <h1 class="mb-4">EVALUASI DIRI</h1>
                <h2 class="mb-3">{{ $pengajuan->studyProgram->name }}</h2>
                <h3 class="mb-4">{{ $pengajuan->studyProgram->university->name }}</h3>
                <div class="mt-5">
                    <p class="mb-1" style="font-size: 1.2rem;">
                        {{ $pengajuan->studyProgram->degreeLevel->name }}
                    </p>
                    <p style="font-size: 1.1rem; opacity: 0.8;">
                        {{ $import->imported_at->format('F Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Table of Contents - No Print in production -->
        <div class="section-toc no-print">
            <h4 class="mb-3">
                <i class="bi bi-list-ul"></i> Daftar Isi
            </h4>
            <ul>
                @foreach($import->sections as $section)
                <li>
                    <a href="#section-{{ $section->kode_section }}">
                        <span>{{ $section->kode_section }} {{ $section->judul_section }}</span>
                        <span class="badge bg-secondary">{{ count($section->tables) }} tabel</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        <hr class="my-5">

        <!-- Sections Loop -->
        @forelse($import->sections as $section)
        <div class="section-wrapper mb-5" id="section-{{ $section->kode_section }}">
            <!-- Section Header -->
            <div class="section-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3>
                        {{ $section->kode_section }} {{ $section->judul_section }}
                    </h3>
                    <div class="no-print">
                        @if($section->elemen)
                        <span class="badge bg-light text-dark badge-mapping">
                            <i class="bi bi-check-circle text-success"></i> Mapped: {{ $section->elemen->kode_elemen }}
                        </span>
                        @else
                        <span class="badge bg-warning badge-mapping">
                            <i class="bi bi-exclamation-triangle"></i> Unmapped
                        </span>
                        @endif
                        <span class="badge bg-light text-dark badge-mapping">
                            <i class="bi bi-table"></i> {{ count($section->tables) }} Tabel
                        </span>
                    </div>
                </div>
            </div>

            <!-- Konten Narasi -->
            @if($section->konten_narasi)
            <div class="konten-narasi">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-file-text"></i> Pernyataan Standar / Uraian
                </h6>
                {{ $section->konten_narasi }}
            </div>
            @endif

            <!-- Tables -->
            @forelse($section->tables as $table)
            <div class="table-wrapper mb-4">
                <!-- Table Header -->
                <div class="table-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-table"></i>
                            <strong>{{ $table->kode_tabel }}</strong> - {{ $table->judul_tabel }}
                        </div>
                        <div class="no-print">
                            @if($table->dataset)
                            <span class="badge bg-success badge-mapping">
                                <i class="bi bi-database"></i> {{ $table->dataset->nama }}
                            </span>
                            @else
                            <span class="badge bg-secondary badge-mapping">
                                <i class="bi bi-question-circle"></i> No Dataset
                            </span>
                            @endif
                            <span class="badge bg-info badge-mapping">
                                {{ $table->row_count }} rows × {{ $table->col_count }} cols
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Table Data -->
                @if($table->data && count($table->data) > 1)
                <div class="table-responsive">
                    <table class="borang-table">
                        <thead>
                            <tr>
                                @foreach($table->headers ?? [] as $header)
                                <th>{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($table->data, 1) as $row)
                            <tr>
                                @foreach($row as $cell)
                                <td>{{ $cell ?: '-' }}</td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="empty-table-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Tabel kosong atau tidak ada data</strong>
                    <p class="mb-0 mt-2 small text-muted">
                        Tabel ini terdeteksi dalam dokumen tetapi tidak memiliki data atau hanya memiliki header.
                    </p>
                </div>
                @endif
            </div>
            @empty
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-info-circle"></i>
                Tidak ada tabel yang ditemukan untuk bagian ini.
            </div>
            @endforelse
        </div>
        @empty
        <div class="alert alert-danger alert-permanent">
            <i class="bi bi-x-circle"></i>
            <strong>Tidak ada data yang berhasil diproses!</strong><br>
            Pastikan dokumen DOCX mengikuti format yang benar dengan struktur:
            <ul class="mt-2 mb-0">
                <li>Bagian diawali dengan kode (contoh: <code>D.1 Legalitas Program</code>)</li>
                <li>Tabel didahului marker <strong>"Mohon isi di sini"</strong></li>
                <li>Header tabel format: <code>Tabel E.1.1 - Nama Tabel</code></li>
            </ul>
        </div>
        @endforelse
    </div>

    <!-- Footer Info Card - No Print -->
    <div class="card mt-4 no-print">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-info-circle"></i> Informasi Pembacaan Data
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-file-earmark"></i> Detail File
                    </h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="180"><i class="bi bi-file-word text-primary"></i> File:</td>
                            <td><strong>{{ $import->original_filename }}</strong></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-person text-info"></i> Diupload oleh:</td>
                            <td>{{ $import->importer->name }}</td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-clock text-warning"></i> Waktu:</td>
                            <td>{{ $import->imported_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-check-circle text-success"></i> Status:</td>
                            <td>
                                <span class="badge bg-{{ $import->status === 'success' ? 'success' : ($import->status === 'failed' ? 'danger' : 'warning') }}">
                                    {{ strtoupper($import->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-bar-chart"></i> Statistik Pembacaan Data
                    </h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="180"><i class="bi bi-folder text-primary"></i> Total Bagian:</td>
                            <td><strong>{{ $import->total_sections }}</strong></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-table text-info"></i> Total Tabel:</td>
                            <td><strong>{{ $import->total_tables }}</strong></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-check2-square text-success"></i> Tabel Terproses:</td>
                            <td><strong>{{ $import->parsed_tables }}/{{ $import->total_tables }}</strong></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-pie-chart text-warning"></i> Kelengkapan:</td>
                            <td>
                                <div class="progress" style="height: 25px; width: 250px;">
                                    <div class="progress-bar {{ $import->completion_percentage >= 80 ? 'bg-success' : ($import->completion_percentage >= 50 ? 'bg-warning' : 'bg-danger') }}" role="progressbar" style="width: {{ $import->completion_percentage }}%" aria-valuenow="{{ $import->completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
                                        <strong>{{ $import->completion_percentage }}%</strong>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($import->parsing_notes)
            <hr>
            <h6 class="fw-bold mb-2">
                <i class="bi bi-journal-text"></i> Catatan Proses
            </h6>
            <div class="alert alert-info alert-permanent mb-0">
                {{ $import->parsing_notes }}
            </div>
            @endif

            @if($import->parsing_errors)
            <hr>
            <h6 class="fw-bold mb-2">
                <i class="bi bi-bug text-danger"></i> Error Log
            </h6>
            <div class="alert alert-danger alert-permanent mb-0">
                <pre class="mb-0" style="font-size: 12px; white-space: pre-wrap;">{{ json_encode($import->parsing_errors, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Navigation - No Print -->
    <div class="no-print" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
        <div class="btn-group-vertical shadow-lg" role="group">
            <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="btn btn-primary" title="Back to Top">
                <i class="bi bi-arrow-up"></i>
            </button>
            <button onclick="window.print()" class="btn btn-success" title="Print">
                <i class="bi bi-printer"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Smooth scroll for TOC links
    document.querySelectorAll('.section-toc a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                    , block: 'start'
                });
            }
        });
    });

    // Print event
    window.addEventListener('beforeprint', function() {
        console.log('Preparing to print...');
    });

    window.addEventListener('afterprint', function() {
        console.log('Print completed');
    });

</script>
@endpush
