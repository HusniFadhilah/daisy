<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-table"></i> Daftar Pelaporan AK
        </h6>
        <span class="badge bg-primary">Total: {{ $pengajuans->total() }}</span>
    </div>
    <div class="card-body p-0">
        @if($pengajuans->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="20%">Permohonan Akreditasi</th>
                        <th width="15%">Validator</th>
                        <th width="20%">Status Pelaporan AK</th>
                        <th width="20%">Tanggal Pelaporan AK</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuans as $index => $pengajuan)
                    @php
                    // Get validators
                    $validators = $pengajuan->asesmen?->asesmenUserRoles->filter(function($aur) {
                    return $aur->role_selected->name === 'validator';
                    }) ?? collect();

                    // Get laporan documents (laporan_ak aktif sudah di-load dari controller)
                    $laporanDocs = $pengajuan->asesmen?->asesmenDocuments ?? collect();
                    $hasLaporan = $laporanDocs->count() > 0;

                    // ===== Status Pelaporan berbasis STATUS LOG =====
                    $pelaporanStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
                    ];

                    // statusLog harus sudah urut changed_at desc dari controller
                    $lastPelaporanLog = $pengajuan->statusLog
                    ?->firstWhere(fn($log) => in_array($log->status_to, $pelaporanStatuses, true));

                    $lastStatus = $lastPelaporanLog?->status_to;

                    $isReported = $lastStatus === \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN;

                    // Badge Status (kolom "Status")
                    if ($isReported) {
                    $statusConfig = [
                    'class' => 'success',
                    'icon' => 'check-circle-fill',
                    'text' => 'Sudah Dilaporkan'
                    ];
                    } else {
                    // kalau belum pernah masuk AK_SELESAI pun, fallback tetap "Validasi Selesai" hanya bila memang ada log AK_SELESAI
                    $statusConfig = [
                    'class' => 'warning',
                    'icon' => 'clock',
                    'text' => ($lastStatus === \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI)
                    ? 'Validasi Selesai'
                    : 'Unknown'
                    ];
                    }

                    // Badge Status Pelaporan (kolom "Status Pelaporan") tetap berdasar ada/tidaknya dokumen
                    if ($isReported) {
                    $pelaporanConfig = [
                    'class' => 'success',
                    'icon' => 'file-earmark-check-fill',
                    'text' => 'Sudah Upload'
                    ];
                    } else {
                    $pelaporanConfig = $hasLaporan ? [
                    'class' => 'info',
                    'icon' => 'file-earmark-arrow-up',
                    'text' => 'Ada Laporan'
                    ] : [
                    'class' => 'danger',
                    'icon' => 'x-circle',
                    'text' => 'Belum Upload'
                    ];
                    }

                    // ===== Tanggal tampil berbasis log =====
                    // Prioritas:
                    // 1) kalau reported -> tanggal dari log AK_DILAPORKAN (changed_at)
                    // 2) kalau belum -> tanggal dari log AK_SELESAI (changed_at)
                    // 3) fallback created_at
                    $tanggalTampil = $lastPelaporanLog?->changed_at
                    ? \Carbon\Carbon::parse($lastPelaporanLog->changed_at)->format('d M Y')
                    : $pengajuan->created_at->format('d M Y');

                    $labelTanggal = $isReported ? 'Dilaporkan' : (($lastStatus === \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI) ? 'Validasi Selesai' : null);
                    @endphp
                    <tr>
                        <td>{{ $pengajuans->firstItem() + $index }}</td>
                        <td>
                            {!! $pengajuan->getPermohonanAkreditasiSectionFor('de') !!}
                        </td>
                        <td>
                            @if($validators->count() > 0)
                            @foreach($validators as $validator)
                            <div class="mb-1">
                                <small>
                                    <i class="bi bi-person-check"></i> {{ $validator->user->name }}
                                </small>
                            </div>
                            @endforeach
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            {!! $pengajuan->getCustomBadgeLastStatus('pelaporan_ak', 'de', 'label_short_for') !!}
                        </td>
                        <td>
                            <small><strong>{{ $tanggalTampil }}</strong></small>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('de.pelaporan-ak.show', $pengajuan->id) }}" class="btn btn-sm btn-primary" title="Lihat Detail">
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
            <p class="text-muted mt-3 mb-0">Tidak ada data pelaporan AK</p>
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
                {{ $pengajuans->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
