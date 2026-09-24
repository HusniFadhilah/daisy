{{-- Validasi Syarat Unggul — dipakai sekretariat dan asesor --}}
@if(!empty($validationSummary['skor_memenuhi']))
@php $syaratP1 = $validationSummary['syarat_p1']; @endphp
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">
            <i class="bi bi-{{ $validationSummary['dapat_unggul'] ? 'shield-check' : 'exclamation-triangle' }}"></i>
            Validasi Syarat Status Akreditasi UNGGUL
        </h5>
    </div>
    <div class="card-body">
        <h6 class="text-muted mb-2 mt-1">
            <i class="bi bi-key me-1"></i> Syarat Kunci
        </h6>
        <div class="row mb-4 g-3">
            <div class="col-md-6">
                <div class="d-flex align-items-start p-3 bg-light rounded h-100">
                    <i class="bi bi-check-circle-fill text-dark fs-4 me-3 mt-1 flex-shrink-0"></i>
                    <div>
                        <strong>Skor Memenuhi Syarat</strong>
                        <div class="text-muted" style="font-size:.85rem">
                            Skor {{ number_format($validationSummary['skor'], 0) }}
                            ≥ {{ $validationSummary['skor_minimum'] }} ✓
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="d-flex align-items-start p-3 bg-light rounded h-100">
                    <i class="bi bi-{{ $syaratP1['rasio']['memenuhi'] ? 'check-circle-fill text-dark' : 'x-circle-fill text-danger' }} fs-4 me-3 mt-1 flex-shrink-0"></i>
                    <div>
                        <strong>Rasio DTPS : Mahasiswa</strong>
                        <div class="text-muted" style="font-size:.85rem">
                            {{ $syaratP1['rasio']['keterangan'] }}
                        </div>
                        @if(!is_null($syaratP1['rasio']['rasio'] ?? null))
                        <div class="text-muted mt-1" style="font-size:.78rem">
                            DTPS: {{ $syaratP1['rasio']['jumlah_dtps'] }}
                            &middot; Mahasiswa: {{ $syaratP1['rasio']['jumlah_mahasiswa'] ?? '—' }}
                            &middot; Rumpun: <em>{{ $syaratP1['rasio']['rumpun'] }}</em>
                        </div>
                        @else
                        <div class="text-warning mt-1" style="font-size:.78rem">
                            <i class="bi bi-exclamation-triangle me-1"></i>Data P.1/E.2 belum tersedia
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="d-flex align-items-start p-3 bg-light rounded h-100">
                    <i class="bi bi-{{ $syaratP1['jabatan']['memenuhi'] ? 'check-circle-fill text-dark' : 'x-circle-fill text-danger' }} fs-4 me-3 mt-1 flex-shrink-0"></i>
                    <div>
                        <strong>
                            {{ ucfirst($syaratP1['jabatan']['label_jabatan'] ?? 'Jabatan Valid') }}
                            ≥ {{ $syaratP1['jabatan']['persen_minimum'] ?? 50 }}%
                        </strong>
                        <div class="text-muted" style="font-size:.85rem">
                            {{ $syaratP1['jabatan']['keterangan'] }}
                        </div>
                        @if(($syaratP1['jabatan']['total_dtps'] ?? 0) > 0)
                        <div class="text-muted mt-1" style="font-size:.78rem">
                            {{ $syaratP1['jabatan']['jumlah_valid'] }}
                            dari {{ $syaratP1['jabatan']['total_dtps'] }} DTPS
                            ({{ $syaratP1['jabatan']['persen_valid'] }}%)
                            @if($syaratP1['jabatan']['filter_dtps_aktif'] ?? false)
                            &middot; <span class="text-success">Filter P.1.3 aktif</span>
                            @endif
                        </div>
                        @else
                        <div class="text-warning mt-1" style="font-size:.78rem">
                            <i class="bi bi-exclamation-triangle me-1"></i>Data P.1 belum tersedia
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="d-flex align-items-start p-3 bg-light rounded h-100">
                    <i class="bi bi-{{ $syaratP1['lulusan']['memenuhi'] ? 'check-circle-fill text-dark' : 'x-circle-fill text-danger' }} fs-4 me-3 mt-1 flex-shrink-0"></i>
                    <div>
                        <strong>
                            Capaian Lulusan ≥ {{ $syaratP1['lulusan']['persen_minimum'] ?? 10 }}%
                        </strong>
                        <div class="text-muted" style="font-size:.85rem">
                            {{ $syaratP1['lulusan']['keterangan'] }}
                        </div>
                        @if(($syaratP1['lulusan']['jumlah_mahasiswa'] ?? 0) > 0)
                        @php $lulusan = $syaratP1['lulusan']; @endphp
                        <div class="text-muted mt-1" style="font-size:.78rem">
                            <strong>Mahasiswa terlibat:</strong>
                            {{ $lulusan['jumlah_mahasiswa_terlibat'] }}
                            dari {{ $lulusan['jumlah_mahasiswa'] }} mahasiswa TA
                            &nbsp;
                            <span class="badge {{ $lulusan['memenuhi'] ? 'bg-secondary' : 'bg-light text-danger border' }}">
                                {{ number_format($lulusan['ratio_mahasiswa_terlibat'], 1) }}%
                            </span>
                            &nbsp;≥ {{ $lulusan['persen_minimum'] }}% ?
                            {{ $lulusan['memenuhi'] ? '✓' : '✗' }}
                        </div>
                        <div class="text-muted mt-1" style="font-size:.78rem">
                            <strong>Jumlah karya/penelitian:</strong>
                            {{ $lulusan['jumlah_penelitian'] }}
                            dari {{ $lulusan['jumlah_mahasiswa'] }} mahasiswa TA
                            &nbsp;
                            <span class="badge bg-light text-muted border">
                                {{ number_format($lulusan['ratio_jumlah_penelitian_mahasiswa'], 1) }}%
                            </span>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.78rem">
                            Tipe: <em>{{ $lulusan['tipe_capaian'] ?? '-' }}</em>
                        </div>
                        @else
                        <div class="text-warning mt-1" style="font-size:.78rem">
                            <i class="bi bi-exclamation-triangle me-1"></i>Data R.3.1 belum tersedia
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-3">

        <h6 class="text-muted mb-2">
            <i class="bi bi-stars me-1"></i> Syarat Perlu — Pelampauan Standar per Kriteria
        </h6>

        <div class="mb-3">
            @if($validationSummary['pelampauan_memenuhi'])
            <span class="badge bg-light text-dark border">
                <i class="bi bi-check-circle me-1"></i>
                Semua {{ count($validationSummary['kriteria_status']) }} kriteria terpenuhi
            </span>
            @else
            <span class="badge bg-light text-danger border">
                <i class="bi bi-x-circle me-1"></i>
                {{ count($validationSummary['missing_kriteria']) }} dari {{ count($validationSummary['kriteria_status']) }} kriteria belum terpenuhi
            </span>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="12%">Kriteria</th>
                        <th class="text-center" width="22%">Status Pelampauan</th>
                        <th class="text-center" width="18%">Jumlah Elemen {{ \App\Models\JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($validationSummary['kriteria_status'] as $kode => $status)
                    <tr class="{{ !$status['has_pelampauan'] ? 'table-light' : '' }}">
                        <td class="text-center">
                            <strong>{{ $kode }}</strong>
                        </td>
                        <td class="text-center">
                            @if($status['has_pelampauan'])
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-check-circle"></i> Terpenuhi
                            </span>
                            @else
                            <span class="badge bg-light text-danger border">
                                <i class="bi bi-x-circle"></i> Belum Terpenuhi
                            </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $status['jumlah_elemen_skor_4'] > 0 ? 'bg-secondary' : 'bg-light text-muted border' }}">
                                {{ $status['jumlah_elemen_skor_4'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(!$validationSummary['dapat_unggul'])
        <div class="alert alert-light border-start border-dark border-2 alert-permanent mt-3 mb-0">
            <strong>⚠️ Perhatian:</strong>
            Meskipun skor ≥ {{ $validationSummary['skor_minimum'] }},
            status <strong>UNGGUL tidak dapat ditetapkan</strong> karena syarat berikut belum terpenuhi:
            <ul class="mb-0 mt-2" style="font-size:.9rem">
                @if(!$syaratP1['rasio']['memenuhi'])
                <li>
                    <span class="badge bg-light text-dark border me-1">Syarat Kunci</span>
                    {{ $syaratP1['rasio']['keterangan'] }}
                </li>
                @endif
                @if(!$syaratP1['jabatan']['memenuhi'])
                <li>
                    <span class="badge bg-light text-dark border me-1">Syarat Kunci</span>
                    {{ $syaratP1['jabatan']['keterangan'] }}
                </li>
                @endif
                @if(!$syaratP1['lulusan']['memenuhi'])
                <li>
                    <span class="badge bg-light text-dark border me-1">Syarat Kunci</span>
                    {{ $syaratP1['lulusan']['keterangan'] }}
                </li>
                @endif
                @if(!$validationSummary['pelampauan_memenuhi'])
                <li>
                    <span class="badge bg-light text-dark border me-1">Syarat Perlu</span>
                    Kriteria <strong>{{ implode(', ', $validationSummary['missing_kriteria']) }}</strong>
                    belum memiliki minimal 1 elemen {{ \App\Models\JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI }}.
                </li>
                @endif
            </ul>
        </div>
        @else
        <div class="alert alert-light border-start border-success border-3 alert-permanent mt-3 mb-0">
            <i class="bi bi-shield-check me-2"></i>
            <strong>Semua syarat Terakreditasi Unggul terpenuhi.</strong>
            Syarat kunci (rasio DTPS, jabatan dosen, capaian lulusan) dan syarat perlu
            (pelampauan standar di semua kriteria) telah terpenuhi.
        </div>
        @endif
    </div>
</div>
@endif
