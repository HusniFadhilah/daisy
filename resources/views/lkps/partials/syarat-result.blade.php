{{-- resources/views/lkps/partials/syarat-result.blade.php --}}
{{-- $cek = hasil dari LkpsDataReaderService::cekSemuaSyarat() --}}

@php
$rasio = $cek['rasio'] ?? [];
$jabatan = $cek['jabatan'] ?? [];
$lulusan = $cek['lulusan'] ?? [];

$tipeJabatan = ($jabatan['tipe'] ?? '') === 'sertifikat_profesi'
? 'Sertifikat Profesi'
: ($jabatan['label_jabatan'] ?? 'Jabatan Valid');
@endphp

{{-- Status keseluruhan --}}
@if($cek['semua_memenuhi'])
<div class="alert alert-success alert-permanent mb-3">
    <i class="bi bi-shield-fill-check me-2"></i>
    <strong>Semua syarat LKPS terpenuhi.</strong>
    Program studi berpotensi mendapat status Unggul (tergantung hasil skor AL).
</div>
@else
<div class="alert alert-warning alert-permanent mb-3">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Belum semua syarat terpenuhi.</strong>
    Perbaiki data LKPS pada bagian yang ditandai di bawah ini.
</div>
@endif

{{-- Tiga kartu syarat --}}
<div class="row g-2 mb-2">

    {{-- Rasio DTPS --}}
    <div class="col-md-6 col-xl-4">
        <div class="syarat-item {{ $rasio['memenuhi'] ? 'memenuhi' : 'tidak' }} p-3 bg-light rounded h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <strong>Rasio DTPS : Mahasiswa</strong>
                    <div class="text-muted mt-1" style="font-size:.85rem">
                        {{ $rasio['keterangan'] ?? '-' }}
                    </div>
                    @if(!is_null($rasio['rasio'] ?? null))
                    <div class="text-muted mt-1" style="font-size:.78rem">
                        DTPS: {{ $rasio['jumlah_dtps'] ?? '?' }}
                        &middot; Mahasiswa: {{ $rasio['jumlah_mahasiswa'] ?? '—' }}
                        &middot; Rumpun: <em>{{ $rasio['rumpun'] ?? '-' }}</em>
                    </div>
                    @else
                    <div class="text-warning mt-1" style="font-size:.78rem">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Data P.1/E.2 belum tersedia
                    </div>
                    @endif
                </div>
                <div class="ms-2 flex-shrink-0">
                    @if($rasio['memenuhi'])
                    <span class="badge bg-success">
                        <i class="bi bi-check-lg"></i> Memenuhi
                    </span>
                    @else
                    <span class="badge bg-danger">
                        <i class="bi bi-x-lg"></i> Belum
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Kompetensi Dosen --}}
    <div class="col-md-6 col-xl-4">
        <div class="syarat-item {{ $jabatan['memenuhi'] ? 'memenuhi' : 'tidak' }} p-3 bg-light rounded h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <strong>{{ $tipeJabatan }} ≥ {{ $jabatan['persen_minimum'] ?? 50 }}%</strong>
                    <div class="text-muted mt-1" style="font-size:.85rem">
                        {{ $jabatan['keterangan'] ?? '-' }}
                    </div>
                    @if(($jabatan['total_dtps'] ?? 0) > 0)
                    <div class="text-muted mt-1" style="font-size:.78rem">
                        {{ $jabatan['jumlah_valid'] ?? 0 }} dari {{ $jabatan['total_dtps'] }} DTPS
                        ({{ $jabatan['persen_valid'] ?? 0 }}%)
                        @if($jabatan['filter_dtps_aktif'] ?? false)
                        &middot; <span class="text-success">Filter P.1.3 aktif</span>
                        @endif
                    </div>
                    @else
                    <div class="text-warning mt-1" style="font-size:.78rem">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Data P.1 belum tersedia
                    </div>
                    @endif
                </div>
                <div class="ms-2 flex-shrink-0">
                    @if($jabatan['memenuhi'])
                    <span class="badge bg-success"><i class="bi bi-check-lg"></i> Memenuhi</span>
                    @else
                    <span class="badge bg-danger"><i class="bi bi-x-lg"></i> Belum</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Capaian Lulusan --}}
    <div class="col-md-6 col-xl-4">
        <div class="syarat-item {{ ($lulusan['memenuhi'] ?? false) ? 'memenuhi' : 'tidak' }} p-3 bg-light rounded h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <strong>Capaian Lulusan ≥ {{ $lulusan['persen_minimum'] ?? 10 }}%</strong>
                    <div class="text-muted mt-1" style="font-size:.85rem">
                        {{ $lulusan['keterangan'] ?? '-' }}
                    </div>
                    @if(($lulusan['jumlah_mahasiswa'] ?? 0) > 0)
                    <div class="text-muted mt-1" style="font-size:.78rem">
                        {{ $lulusan['jumlah_luaran'] ?? 0 }} dari {{ $lulusan['jumlah_mahasiswa'] }} mhs
                        ({{ number_format($lulusan['persen'] ?? 0, 1) }}%)
                        &middot; Tipe: <em>{{ $lulusan['tipe_capaian'] ?? '-' }}</em>
                    </div>

                    {{-- Detail per sheet --}}
                    @php
                    $dc = $lulusan['detail']['kolaborasi'] ?? [];
                    $dd = $lulusan['detail_per_sheet']['R.3.1.d'] ?? [];
                    $dm = $lulusan['detail']['mandiri'] ?? [];
                    @endphp
                    @if(!empty($dc) || !empty($dd) || !empty($dm))
                    <div class="mt-1 d-flex gap-2 flex-wrap" style="font-size:.75rem">
                        <span class="text-muted">
                            R.3.1.c: {{ $dc['jumlah_luaran'] ?? 0 }}/{{ $dc['jumlah_mahasiswa'] ?? 0 }}
                        </span>
                        <span class="text-muted">
                            R.3.1.d: {{ $dd['jumlah_luaran'] ?? 0 }}/{{ $dd['jumlah_mahasiswa'] ?? 0 }}
                        </span>
                        <span class="text-muted">
                            R.3.1.e: {{ $dm['jumlah_luaran'] ?? 0 }}/{{ $dm['jumlah_mahasiswa'] ?? 0 }}
                        </span>
                    </div>
                    @endif
                    @else
                    <div class="text-warning mt-1" style="font-size:.78rem">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Data R.3.1 belum tersedia
                    </div>
                    @endif
                </div>
                <div class="ms-2 flex-shrink-0">
                    @if($lulusan['memenuhi'] ?? false)
                    <span class="badge bg-success"><i class="bi bi-check-lg"></i> Memenuhi</span>
                    @else
                    <span class="badge bg-danger"><i class="bi bi-x-lg"></i> Belum</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<div class="text-muted" style="font-size:.75rem">
    <i class="bi bi-clock me-1"></i>
    Dicek pada: {{ $cek['checked_at'] ?? '-' }}
    &middot; Jenjang: {{ $cek['degree_level'] ?? '-' }}
    &middot; Rumpun: {{ $cek['rumpun'] ?? '-' }}
</div>
