<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-table"></i> Daftar Pelaporan Dokumen
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
                        <th width="20%">Validator</th>
                        <th width="20%">Status Pelaporan Dokumen</th>
                        <th width="20%">Tanggal Pelaporan</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuans as $index => $pengajuan)
                    @php
                    // Get validator info
                    $validator = $pengajuan->asesmen?->asesmenUserRoles->first();
                    $validatorName = $validator?->user?->name ?? '-';

                    // Get laporan validasi
                    $laporan = $pengajuan->asesmen?->documents
                    ->where('type', 'laporan_validasi_borang')
                    ->where('is_active', true)
                    ->first();

                    $hasLaporan = (bool) $laporan;

                    // ===============================
                    // STATUS berbasis STATUS LOG
                    // ===============================
                    $pelaporanStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
                    ];

                    // Ambil log terakhir yg termasuk 2 status ini (asumsi statusLog sudah desc by changed_at)
                    $lastPelaporanLog = $pengajuan->statusLog
                    ?->firstWhere(fn($log) => in_array($log->status_to, $pelaporanStatuses, true));

                    $lastStatus = $lastPelaporanLog?->status_to;

                    $isReported = $lastStatus === \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN;
                    $isSelesai = $lastStatus === \App\Models\PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED;

                    // ===============================
                    // Badge Status Upload (berdasar dokumen)
                    // ===============================
                    $statusUpload = $hasLaporan
                    ? ['class' => 'success', 'icon' => 'check-circle-fill', 'text' => 'Sudah Upload']
                    : ['class' => 'danger', 'icon' => 'x-circle-fill', 'text' => 'Belum Upload'];

                    // ===============================
                    // Badge Status Pelaporan (berdasar log)
                    // ===============================
                    if ($isReported) {
                    $statusPelaporan = ['class' => 'success', 'icon' => 'check-circle-fill', 'text' => 'Selesai'];
                    } elseif ($hasLaporan || $isSelesai) {
                    // Kalau ada file ATAU sudah validasi selesai tapi belum dilaporkan -> menunggu finalisasi
                    $statusPelaporan = ['class' => 'warning', 'icon' => 'hourglass-split', 'text' => 'Menunggu Finalisasi'];
                    } else {
                    $statusPelaporan = ['class' => 'danger', 'icon' => 'x-circle-fill', 'text' => 'Belum Selesai'];
                    }

                    // ===============================
                    // Tanggal tampil
                    // Prioritas:
                    // 1) kalau sudah dilaporkan -> changed_at log DILAPORKAN
                    // 2) kalau belum -> uploaded_at (kalau ada laporan)
                    // 3) fallback '-'
                    // ===============================
                    if ($isReported && $lastPelaporanLog?->changed_at) {
                    $tanggal = \Carbon\Carbon::parse($lastPelaporanLog->changed_at)->locale('id')->translatedFormat('d M Y H:i');
                    } elseif ($hasLaporan && $laporan?->uploaded_at) {
                    $tanggal = $laporan->uploaded_at->locale('id')->translatedFormat('d M Y H:i');
                    } else {
                    $tanggal = '-';
                    }
                    @endphp
                    <tr>
                        <td>{{ $pengajuans->firstItem() + $index }}</td>
                        <td>
                            {!! $pengajuan->getPermohonanAkreditasiSectionFor('de') !!}
                        </td>
                        <td>
                            @if($validator)
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2" style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold;">
                                    {{ strtoupper(substr($validatorName, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size: 13px;">{{ $validatorName }}</div>
                                    <small class="text-muted">Validator</small>
                                </div>
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            {!! $pengajuan->getCustomBadgeLastStatus('pelaporan_dokumen','de','label_short_for') !!}
                        </td>
                        <td>
                            <small>{{ $tanggal }}</small>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('de.pelaporan-dokumen.show', $pengajuan->id) }}" class="btn btn-sm btn-primary action-btn" title="Lihat Detail">
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
            <p class="text-muted mt-3">Tidak ada data permohonan akreditasi</p>
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
