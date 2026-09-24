{{-- Keterangan Batasan Skor Akreditasi — dipakai sekretariat dan asesor --}}
@php
    $rentangSkor = $rentangSkor ?? [];
    $skorMinimum = $skorMinimum ?? ($validationSummary['skor_minimum'] ?? null);
    $kriteriaKode = $kriteriaKode ?? (isset($validationSummary['kriteria_status'])
        ? array_keys($validationSummary['kriteria_status'])
        : ['D', 'E', 'P', 'I', 'L', 'A', 'R']);
    $pengajuanId = $pengajuanId ?? ($pengajuan->id ?? null);
    $showLkpsLink = $showLkpsLink ?? false;
    $currentSkor = $currentSkor ?? null;
@endphp

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-secondary text-white border-0">
        <h5 class="mb-0">
            <i class="bi bi-info-circle"></i>
            Keterangan Batasan Skor Akreditasi
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-2">
            @foreach($rentangSkor as $rentang)
            @php
                $makna = $rentang['makna'] ?? null;
                $inRange = $currentSkor !== null
                    && $currentSkor >= ($rentang['skor_min'] ?? 0)
                    && $currentSkor <= ($rentang['skor_max'] ?? 0);
            @endphp
            <div class="col-md-6">
                <div class="d-flex align-items-start p-3 rounded h-100 border {{ $inRange ? 'border-2 border-dark' : '' }}" style="background-color: {{ $rentang['warna'] ?? '#fff' }}">
                    <div class="me-3 text-nowrap pt-1">
                        <strong class="text-dark">
                            {{ $rentang['skor_min'] }} – {{ $rentang['skor_max'] }}
                        </strong>
                        @if(isset($rentang['persen_min']))
                        <div class="text-muted" style="font-size:.75rem">
                            {{ $rentang['persen_min'] }}–{{ $rentang['persen_max'] }}%
                        </div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <strong class="text-dark">{{ $rentang['status'] }}</strong>
                        @if(isset($rentang['siklus_tahun']))
                        <span class="badge bg-secondary ms-1" style="font-size:.7rem">
                            {{ $rentang['siklus_tahun'] }} Tahun
                        </span>
                        @endif
                        @if($inRange)
                        <span class="badge bg-dark ms-1" style="font-size:.7rem">Skor saat ini</span>
                        @endif
                        @if($makna)
                        <div class="mt-1">
                            @if(is_array($makna))
                            <ul class="mb-0 ps-3" style="font-size:.8rem">
                                @foreach($makna as $m)
                                <li class="text-muted">{{ $m }}</li>
                                @endforeach
                            </ul>
                            @else
                            <small class="text-muted">{{ $makna }}</small>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="alert alert-light alert-permanent mt-3 mb-0">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Catatan:</strong> Status akreditasi <strong>Unggul</strong> memerlukan dua lapis syarat:
            <div class="row mt-2 g-2">
                <div class="col-md-6">
                    <div class="border rounded p-2" style="font-size:.83rem">
                        <strong class="d-block mb-1">
                            <i class="bi bi-key me-1"></i> Syarat Kunci
                        </strong>
                        @if($showLkpsLink && $pengajuanId)
                        <a href="{{ route('pengajuan.borang.lkps.preview', $pengajuanId) }}">Lihat Detail LKPS</a>
                        @endif
                        <ul class="mb-0 ps-3 text-muted">
                            <li>Skor ≥ {{ $skorMinimum ?? '—' }}</li>
                            <li>Rasio DTPS : Mahasiswa sesuai batas rumpun</li>
                            <li>Jabatan/sertifikasi dosen memenuhi persentase minimum</li>
                            <li>Capaian lulusan (publikasi/inovasi) memenuhi persentase minimum</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-2" style="font-size:.83rem">
                        <strong class="d-block mb-1">
                            <i class="bi bi-stars me-1"></i> Syarat Perlu
                        </strong>
                        <ul class="mb-0 ps-3 text-muted">
                            <li>
                                Minimal <strong>1 elemen</strong> berkategori
                                <em>"{{ \App\Models\JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI }}"</em> di <strong>setiap</strong> kriteria:
                                <span class="fw-semibold">
                                    {{ implode(', ', $kriteriaKode) }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
