{{-- resources/views/lkps/preview.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Preview LKPS')

@push('styles')
<style>
    .excel-table th,
    .excel-table td {
        font-size: 13px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .excel-table thead th {
        text-align: center;
    }

    .excel-table tbody td {
        background: #fff;
    }

    .excel-table tbody tr:nth-child(even) td {
        background: #f8f9fa;
    }

    /* Card syarat */
    .syarat-card {
        transition: opacity .2s;
    }

    .syarat-card.loading {
        opacity: .5;
        pointer-events: none;
    }

    .syarat-item {
        border-left: 4px solid transparent;
        transition: border-color .2s;
    }

    .syarat-item.memenuhi {
        border-left-color: #198754;
    }

    .syarat-item.tidak {
        border-left-color: #dc3545;
    }

    .syarat-item.unknown {
        border-left-color: #adb5bd;
    }

</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="bi bi-file-spreadsheet me-2"></i>
            Preview LKPS – {{ $pengajuan->studyProgram->name ?? '' }}
        </h4>
        <div class="d-flex gap-2">
            <span class="badge bg-secondary">
                {{ $pengajuan->studyProgram->degreeLevel->nama ?? '-' }}
            </span>
            <a href="{{ route('pengajuan.borang.lkps.preview', $pengajuan->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-clockwise"></i> Reload
            </a>
        </div>
    </div>

    {{-- ======================================================= --}}
    {{-- PANEL: CEK SYARAT UNGGUL --}}
    {{-- ======================================================= --}}
    <div class="card mb-4 border-0 shadow-sm syarat-card" id="panelSyarat">
        <div class="card-header d-flex justify-content-between align-items-center" style="background:#343a40; color:#fff;">
            <h6 class="mb-0">
                <i class="bi bi-shield-check me-2"></i>
                Cek Syarat LKPS untuk Status Akreditasi Unggul
            </h6>
            <button class="btn btn-sm btn-outline-light" id="btnRefreshSyarat" data-url="{{ route('pengajuan.borang.lkps.cek-syarat', $pengajuan->id) }}">
                <i class="bi bi-arrow-repeat me-1"></i> Refresh Cek
            </button>
        </div>

        <div class="card-body" id="syaratContent">

            @if(is_null($cekHasil))
            {{-- Belum ada data LKPS --}}
            <div class="alert alert-light alert-permanent mb-0">
                <i class="bi bi-info-circle me-2"></i>
                Data LKPS belum tersedia. Upload file LKPS terlebih dahulu untuk mengecek syarat.
            </div>

            @elseif(isset($cekHasil['error']))
            {{-- Error saat cek --}}
            <div class="alert alert-danger mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Gagal membaca data:</strong> {{ $cekHasil['error'] }}
            </div>

            @else
            {{-- Hasil cek tersedia --}}
            @include('lkps.partials.syarat-result', ['cek' => $cekHasil])
            @endif

        </div>
    </div>

    {{-- ======================================================= --}}
    {{-- TAB SHEET --}}
    {{-- ======================================================= --}}
    <ul class="nav nav-tabs mb-4 flex-wrap">
        @foreach($data as $sheetName => $tables)
        <li class="nav-item">
            <a class="nav-link {{ $sheetName === $activeSheet ? 'active' : '' }}" href="{{ route('pengajuan.borang.lkps.preview', [$pengajuan->id, 'sheet' => $sheetName]) }}">
                {{ $sheetName }}
            </a>
        </li>
        @endforeach
    </ul>

    {{-- ======================================================= --}}
    {{-- CONTENT SHEET --}}
    {{-- ======================================================= --}}
    @if(isset($data[$activeSheet]))

    @foreach($data[$activeSheet] as $table)
    <div class="card mb-5">
        <div class="card-header bg-primary text-white">
            <strong>{{ $table->table_title ?? "Tabel {$table->table_index}" }}</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0 excel-table">

                    @if(!empty($table->headers))
                    <thead class="table-light">
                        @foreach($table->headers as $headerRow)
                        <tr>
                            @foreach($headerRow as $cell)
                            <th>{!! $cell !== null ? e($cell) : '&nbsp;' !!}</th>
                            @endforeach
                        </tr>
                        @endforeach
                    </thead>
                    @endif

                    <tbody>
                        @foreach($table->rows as $row)
                        <tr>
                            @foreach($row as $cell)
                            <td>{{ $cell ?? '' }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>
    @endforeach

    @else
    <div class="alert alert-warning">Tidak ada data untuk sheet ini.</div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    function badge(ok) {
        if (ok) {
            return '<span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Memenuhi</span>';
        }
        return '<span class="badge bg-danger"><i class="bi bi-x-lg me-1"></i>Belum Memenuhi</span>';
    }

    function syaratItem(ok, title, keterangan, detail) {
        var cls = 'tidak';
        if (ok) {
            cls = 'memenuhi';
        }

        var detailHtml = '';
        if (detail && detail !== '') {
            detailHtml = '<div class="text-muted mt-1" style="font-size:.8rem">' + detail + '</div>';
        }

        return '<div class="syarat-item ' + cls + ' p-3 mb-2 bg-light rounded">' +
            '<div class="d-flex justify-content-between align-items-start">' +
            '<div>' +
            '<strong>' + title + '</strong>' +
            '<div class="text-muted mt-1" style="font-size:.85rem">' + keterangan + '</div>' +
            detailHtml +
            '</div>' +
            '<div class="ms-3 flex-shrink-0">' + badge(ok) + '</div>' +
            '</div>' +
            '</div>';
    }

    function renderSyaratResult(cek) {

        if (cek.error) {
            content.innerHTML =
                '<div class="alert alert-danger mb-0">' +
                '<i class="bi bi-exclamation-triangle me-2"></i>' +
                cek.error +
                '</div>';
            return;
        }

        var rasio = {};
        if (cek.rasio) {
            rasio = cek.rasio;
        }

        var jabatan = {};
        if (cek.jabatan) {
            jabatan = cek.jabatan;
        }

        var lulusan = {};
        if (cek.lulusan) {
            lulusan = cek.lulusan;
        }

        var statusHtml = '';
        if (cek.semua_memenuhi) {
            statusHtml =
                '<div class="alert alert-success mb-3">' +
                '<i class="bi bi-shield-fill-check me-2"></i>' +
                '<strong>Semua syarat LKPS terpenuhi.</strong> Program studi berpotensi mendapat status Unggul (tergantung skor AL).' +
                '</div>';
        } else {
            statusHtml =
                '<div class="alert alert-warning mb-3">' +
                '<i class="bi bi-exclamation-triangle me-2"></i>' +
                '<strong>Belum semua syarat terpenuhi.</strong>' +
                '</div>';
        }

        // ===== RASIO DETAIL =====
        var rasioDetail = '';
        if (typeof rasio.rasio !== 'undefined' && rasio.rasio !== null) {
            var jumlahMhs = '-';
            if (typeof rasio.jumlah_mahasiswa !== 'undefined' && rasio.jumlah_mahasiswa !== null) {
                jumlahMhs = rasio.jumlah_mahasiswa;
            }

            rasioDetail =
                'DTPS: ' + rasio.jumlah_dtps +
                ' · Mahasiswa: ' + jumlahMhs +
                ' · Rumpun: ' + rasio.rumpun;
        } else {
            rasioDetail =
                '<span class="text-warning">' +
                '<i class="bi bi-exclamation-triangle"></i> Data P.1/E.2 belum tersedia' +
                '</span>';
        }

        // ===== JABATAN DETAIL =====
        var jabatanTipe = 'Jabatan Valid';
        if (jabatan.tipe === 'sertifikat_profesi') {
            jabatanTipe = 'Sertifikat Profesi';
        } else if (jabatan.label_jabatan) {
            jabatanTipe = jabatan.label_jabatan;
        }

        var jabatanDetail = '';
        if (jabatan.total_dtps && jabatan.total_dtps > 0) {
            jabatanDetail =
                jabatan.jumlah_valid + ' dari ' +
                jabatan.total_dtps + ' DTPS (' +
                jabatan.persen_valid + '%)';
        } else {
            jabatanDetail =
                '<span class="text-warning">' +
                '<i class="bi bi-exclamation-triangle"></i> Data P.1 belum tersedia' +
                '</span>';
        }

        // ===== LULUSAN DETAIL =====
        var lulusanDetail = '';
        if (lulusan.jumlah_mahasiswa && lulusan.jumlah_mahasiswa > 0) {
            lulusanDetail =
                lulusan.jumlah_luaran + ' dari ' +
                lulusan.jumlah_mahasiswa + ' mhs (' +
                lulusan.persen + '%)' +
                ' · Tipe: ' + lulusan.tipe_capaian;
        } else {
            lulusanDetail =
                '<span class="text-warning">' +
                '<i class="bi bi-exclamation-triangle"></i> Data R.3.1 belum tersedia' +
                '</span>';
        }

        var html =
            statusHtml +
            '<div class="row g-2">' +
            '<div class="col-md-6">' +
            syaratItem(rasio.memenuhi === true, 'Rasio DTPS : Mahasiswa', rasio.keterangan || '-', rasioDetail) +
            '</div>' +
            '<div class="col-md-6">' +
            syaratItem(jabatan.memenuhi === true
                , jabatanTipe + ' ≥ ' + (jabatan.persen_minimum || 50) + '%'
                , jabatan.keterangan || '-'
                , jabatanDetail) +
            '</div>' +
            '<div class="col-md-6">' +
            syaratItem(lulusan.memenuhi === true
                , 'Capaian Lulusan ≥ ' + (lulusan.persen_minimum || 10) + '%'
                , lulusan.keterangan || '-'
                , lulusanDetail) +
            '</div>' +
            '</div>' +
            '<div class="text-muted mt-2" style="font-size:.75rem">' +
            '<i class="bi bi-clock me-1"></i>Dicek pada: ' +
            (cek.checked_at || '-') +
            ' · Jenjang: ' +
            (cek.degree_level || '-') +
            ' · Rumpun: ' +
            (cek.rumpun || '-') +
            '</div>';

        content.innerHTML = html;
    }

</script>
@endpush
