<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-table"></i> Daftar Penyampaian Hasil Akreditasi
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
                        <th width="10%" class="text-center">Skor AL</th>
                        <th width="15%" class="text-center">Status Akreditasi</th>
                        <th width="20%">Status Penyampaian Hasil</th>
                        <th width="15%">Tanggal Penyampaian</th>
                        <th width="5%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuans as $index => $pengajuan)
                    @php
                    $hasil = $pengajuan->asesmen->hasil ?? null;

                    // Status pengajuan badge (mengandalkan model map)
                    $statusBadgeClass = $pengajuan->status_badge_class ?? 'bg-secondary';
                    $statusLabel = $pengajuan->status_label ?? '-';

                    // Status hasil config
                    if (!$hasil) {
                    $hasilConfig = [
                    'class' => 'secondary',
                    'icon' => 'dash-circle',
                    'text' => 'Belum dihitung',
                    ];
                    } else if ($hasil->isAlFinalized()) {
                    $hasilConfig = [
                    'class' => 'success',
                    'icon' => 'check-circle-fill',
                    'text' => 'Difinalisasi',
                    ];
                    } else {
                    $hasilConfig = [
                    'class' => 'warning',
                    'icon' => 'clock',
                    'text' => 'Draft',
                    ];
                    }

                    // Tanggal tampil (prioritas finalisasi -> pengiriman hasil -> created_at)
                    $tanggalLabel = null;
                    $tanggalKeterangan = null;

                    if ($hasil?->tanggal_finalisasi_al) {
                    $tanggalLabel = \Carbon\Carbon::parse($hasil->tanggal_finalisasi_al)->locale('id')->translatedFormat('d M Y');
                    $tanggalKeterangan = 'Finalisasi';
                    } else if ($pengajuan->tanggal_hasil_akreditasi_dikirim) {
                    $tanggalLabel = \Carbon\Carbon::parse($pengajuan->tanggal_hasil_akreditasi_dikirim)->locale('id')->translatedFormat('d M Y');
                    $tanggalKeterangan = 'Hasil dikirim';
                    } else {
                    $tanggalLabel = optional($pengajuan->created_at)->locale('id')->translatedFormat('d M Y');
                    $tanggalKeterangan = 'Dibuat';
                    }

                    // Badge peringkat (kalau ingin konsisten, bisa pakai helper model)
                    $peringkat = $hasil->peringkat_akreditasi_hasil ?? null;
                    @endphp

                    <tr>
                        <td>{{ $pengajuans->firstItem() + $index }}</td>

                        <td>
                            {!! $pengajuan->getPermohonanAkreditasiSectionFor('de') !!}
                        </td>

                        <td class="text-center">
                            @if($hasil && $hasil->skor_al)
                            @php
                            $peringkatAL = $hasil->getPeringkatFromSkor((float)($hasil->skor_al ?? 0));
                            @endphp
                            <span class="badge bg-light text-dark fs-6 p-2 px-3">{{ number_format($hasil->skor_al, 0) }}</span>
                            {{-- <span class="badge p-1 px-2 my-2" style="background-color: {{ $hasil->getPeringkatColor($peringkatAL) }}; color:#222">{{ $peringkatAL }}</span> --}}
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($peringkat)
                            <span class="badge p-1 px-2 my-2" style="background-color: {{ $hasil->getPeringkatColor() }}; color:#222">{{ $peringkat }}</span>
                            @else
                            <span class="text-muted">Belum difinalisasi</span>
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
                            <a href="{{ route('de.penyampaian-hasil-akreditasi.show', $pengajuan->id) }}" class="btn btn-sm btn-primary" title="Lihat Detail">
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
            <p class="text-muted mt-3 mb-0">Tidak ada data penyampaian hasil akreditasi</p>
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
