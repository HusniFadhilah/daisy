{{-- resources/views/de/penetapan-hasil-akreditasi/components/table-content.blade.php --}}

<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-table"></i> Daftar Penetapan Hasil Akreditasi
        </h6>
        <span class="badge bg-primary">Total: {{ $pengajuans->total() }}</span>
    </div>

    <div class="card-body p-0">
        @if($pengajuans->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="18%">Permohonan Akreditasi</th>
                        <th width="10%" class="text-center">Skor Final</th>
                        <th width="15%" class="text-center">Status Akreditasi</th>
                        <th width="20%">Status Penetapan Hasil</th>
                        <th width="15%">Tanggal Penetapan</th>
                        <th width="5%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuans as $index => $pengajuan)
                    @php
                    $hasil = $pengajuan->asesmen->hasil ?? null;

                    // Status hasil config
                    if (!$hasil) {
                    $hasilConfig = [
                    'class' => 'secondary',
                    'icon' => 'dash-circle',
                    'text' => 'Belum dihitung',
                    ];
                    } else if ($pengajuan->tanggal_penetapan) {
                    $hasilConfig = [
                    'class' => 'success',
                    'icon' => 'check-circle-fill',
                    'text' => 'Ditetapkan',
                    ];
                    } else {
                    $hasilConfig = [
                    'class' => 'warning',
                    'icon' => 'clock',
                    'text' => 'Menunggu Penetapan',
                    ];
                    }

                    // Tanggal tampil (prioritas penetapan -> finalisasi AL -> created_at)
                    $tanggalLabel = null;
                    $tanggalKeterangan = null;

                    if ($pengajuan->tanggal_penetapan) {
                    $tanggalLabel = \Carbon\Carbon::parse($pengajuan->tanggal_penetapan)->locale('id')->translatedFormat('d M Y');
                    $tanggalKeterangan = 'Ditetapkan';
                    } else if ($hasil?->tanggal_finalisasi_al) {
                    $tanggalLabel = \Carbon\Carbon::parse($hasil->tanggal_finalisasi_al)->locale('id')->translatedFormat('d M Y');
                    $tanggalKeterangan = 'Hasil AL Difinalisasi';
                    } else {
                    $tanggalLabel = optional($pengajuan->created_at)->locale('id')->translatedFormat('d M Y');
                    $tanggalKeterangan = 'Dibuat';
                    }

                    // Badge peringkat
                    $peringkat = $hasil->peringkat_akreditasi_final ?? null;
                    @endphp

                    <tr>
                        <td>{{ $pengajuans->firstItem() + $index }}</td>

                        <td>
                            {!! $pengajuan->getPermohonanAkreditasiSectionFor('de') !!}
                        </td>

                        <td class="text-center">
                            @if($hasil && $hasil->skor_final)
                            @php
                            $peringkatFinal = $hasil->getPeringkatFromSkor($hasil->skor_final);
                            @endphp
                            <span class="badge bg-light text-dark fs-6 p-2 px-3">{{ number_format($hasil->skor_final, 2) }}</span>
                            {{-- <span class="badge p-1 px-2 my-2" style="background-color: {{ $hasil->getPeringkatColor($peringkatFinal) }}; color:#222">
                            {{ $peringkatFinal }}
                            </span> --}}
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($peringkat)
                            <span class="badge p-1 px-2 my-2" style="background-color: {{ $hasil->getPeringkatColor($peringkat) }}; color:#222">
                                {{ $peringkat }}
                            </span>
                            @else
                            <span class="text-muted">Belum ditetapkan</span>
                            @endif
                        </td>

                        <td>
                            <span class="badge bg-{{ $hasilConfig['class'] }}">
                                <i class="bi bi-{{ $hasilConfig['icon'] }}"></i> {{ $hasilConfig['text'] }}
                            </span>
                        </td>

                        <td>
                            <small><strong>{{ $tanggalLabel }}</strong></small><br>
                            <small class="text-muted">{{ $tanggalKeterangan }}</small>
                        </td>

                        <td class="text-center">
                            <a href="{{ route('de.penetapan-hasil-akreditasi.show', $pengajuan->id) }}" class="btn btn-sm btn-primary" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
            <p class="text-muted mt-3 mb-0">Tidak ada data penetapan hasil akreditasi</p>
        </div>
        @endif
    </div>

    @if($pengajuans->hasPages())
    <div class="card-footer bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                Menampilkan {{ $pengajuans->firstItem() }} - {{ $pengajuans->lastItem() }} dari {{ $pengajuans->total() }} data
            </div>
            <div>
                {{ $pengajuans->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
