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
    <div class="col-md-6 col-xl-6">
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
                    <span class="badge bg-success"><i class="bi bi-check-lg"></i> Memenuhi</span>
                    @else
                    <span class="badge bg-danger"><i class="bi bi-x-lg"></i> Belum</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Kompetensi Dosen --}}
    <div class="col-md-6 col-xl-6">
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
    <div class="col-md-12 col-xl-12">
        <div class="syarat-item {{ ($lulusan['memenuhi'] ?? false) ? 'memenuhi' : 'tidak' }} p-3 bg-light rounded h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <strong>Capaian Lulusan ≥ {{ $lulusan['persen_minimum'] ?? 10 }}%</strong>
                    <div class="text-muted mt-1" style="font-size:.85rem">
                        {{ $lulusan['keterangan'] ?? '-' }}
                    </div>

                    @if(($lulusan['jumlah_mahasiswa'] ?? 0) > 0)
                    @php
                    $jmhTa = $lulusan['jumlah_mahasiswa'];
                    $jmhTerlibat = $lulusan['jumlah_mahasiswa_terlibat'] ?? 0;
                    $jmhKarya = $lulusan['jumlah_penelitian'] ?? 0;
                    $ratioTerlibat = $lulusan['ratio_mahasiswa_terlibat'] ?? ($lulusan['persen'] ?? 0);
                    $ratioPenelitian = $lulusan['ratio_jumlah_penelitian_mahasiswa'] ?? 0;
                    $minPersen = $lulusan['persen_minimum'] ?? 10;
                    @endphp

                    {{-- Ratio utama: mahasiswa terlibat (dipakai untuk syarat) --}}
                    <div class="mt-2" style="font-size:.78rem">
                        <div class="d-flex align-items-center gap-1 mb-1">
                            <span class="text-muted">Mahasiswa terlibat:</span>
                            <strong>{{ $jmhTerlibat }}</strong>
                            <span class="text-muted">/ {{ $jmhTa }} mhs TA</span>
                            <span class="badge {{ $lulusan['memenuhi'] ?? false ? 'bg-success' : 'bg-danger' }}">
                                {{ number_format($ratioTerlibat, 1) }}%
                            </span>
                            <span class="text-muted">≥ {{ $minPersen }}%?
                                {{ ($lulusan['memenuhi'] ?? false) ? '✓' : '✗' }}
                            </span>
                        </div>

                        {{-- Ratio informatif: jumlah karya/penelitian --}}
                        <div class="d-flex align-items-center gap-1 text-muted">
                            <span>Jumlah karya:</span>
                            <strong class="text-dark">{{ $jmhKarya }}</strong>
                            <span>/ {{ $jmhTa }} mhs TA</span>
                            <span class="badge bg-light text-muted border">
                                {{ number_format($ratioPenelitian, 1) }}%
                            </span>
                            <span class="fst-italic">(informatif)</span>
                        </div>
                    </div>

                    {{-- Tipe capaian --}}
                    <div class="text-muted mt-1" style="font-size:.75rem">
                        Tipe: <em>{{ $lulusan['tipe_capaian'] ?? '-' }}</em>
                    </div>

                    {{-- Detail per sheet --}}
                    @php
                    $dc = $lulusan['detail']['kolaborasi'] ?? [];
                    $dd = $lulusan['detail_per_sheet']['R.3.1.d'] ?? [];
                    $dm = $lulusan['detail']['mandiri'] ?? [];
                    @endphp
                    @if(!empty($dc) || !empty($dd) || !empty($dm))
                    <div class="mt-1 d-flex gap-2 flex-wrap" style="font-size:.75rem">
                        <span class="text-muted" title="R.3.1.c — Kolaborasi DTPS+Mahasiswa">
                            R.3.1.c:
                            {{ $dc['jumlah_mahasiswa_terlibat'] ?? $dc['jumlah_luaran'] ?? 0 }}
                            mhs / {{ $dc['jumlah_penelitian'] ?? 0 }} karya
                        </span>
                        <span class="text-muted" title="R.3.1.d">
                            R.3.1.d:
                            {{ $dd['jumlah_mahasiswa_terlibat'] ?? $dd['jumlah_luaran'] ?? 0 }}
                            mhs / {{ $dd['jumlah_penelitian'] ?? 0 }} karya
                        </span>
                        <span class="text-muted" title="R.3.1.e — Mahasiswa Mandiri">
                            R.3.1.e:
                            {{ $dm['jumlah_mahasiswa_terlibat'] ?? $dm['jumlah_luaran'] ?? 0 }}
                            mhs / {{ $dm['jumlah_penelitian'] ?? 0 }} karya
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
