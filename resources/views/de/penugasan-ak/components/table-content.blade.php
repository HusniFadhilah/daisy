{{-- resources\views\de\penugasan-ak\components\table-content.blade.php --}}
<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-table"></i> Daftar Penugasan Asesor AK
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
                        <th width="15%">Penugasan</th>
                        {{-- <th width="15%">Progress</th> --}}
                        <th width="15%">Status Penugasan Asesor AK</th>
                        <th width="15%">Tanggal Penugasan</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuans as $index => $pengajuan)
                    @php

                    // Penugasan counts (tetap dari asesmen)
                    $asesmenKecukupan = $pengajuan->asesmen?->asesmenKecukupan;
                    $asesorCount = $asesmenKecukupan?->asesors->count() ?? 0;
                    $validatorCount = $asesmenKecukupan?->validators->count() ?? 0;
                    $requirementsMet = $asesorCount >= 2 && $validatorCount >= 1;

                    // ===== Status AK berdasarkan STATUS LOG =====
                    $akStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                    //\App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
                    //\App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
                    //\App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
                    ];

                    // log AK terakhir (yang paling baru di fase AK)
                    $lastAkLog = $pengajuan->statusLog()
                    ->whereIn('status_to', $akStatuses)
                    ->orderByDesc('changed_at') // atau created_at, tapi changed_at lebih “event time”
                    ->first();

                    // karena statusLog di-controller sudah orderBy(changed_at desc),
                    // firstWhere(...) akan ngambil yang paling baru.

                    $akStatus = $lastAkLog?->status_to; // bisa null kalau belum pernah masuk fase AK

                    // fallback "belum ditugaskan" berdasarkan riwayat (bukan status current)
                    $preAkStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
                    ];

                    $hasReachedPreAk = $pengajuan->statusLog
                    ?->contains(fn($log) => in_array($log->status_to, $preAkStatuses, true)) ?? false;

                    // ===== Badge Status AK =====
                    if ($akStatus === App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN) {
                    $statusAK = ['class' => 'success', 'icon' => 'check-circle-fill', 'text' => 'Selesai'];
                    } elseif ($akStatus === App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI) {
                    $statusAK = ['class' => 'success', 'icon' => 'check-circle', 'text' => 'AK Selesai'];
                    } elseif ($akStatus === App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION) {
                    $statusAK = ['class' => 'warning', 'icon' => 'shield-check', 'text' => 'Sedang Validasi'];
                    } elseif ($akStatus === App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS) {
                    $statusAK = ['class' => 'info', 'icon' => 'hourglass-split', 'text' => 'Sedang Proses'];
                    } elseif ($akStatus === App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED) {
                    $statusAK = ['class' => 'warning', 'icon' => 'clock', 'text' => 'Ditugaskan'];
                    } elseif ($hasReachedPreAk) {
                    $statusAK = ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Belum Ditugaskan'];
                    } else {
                    $statusAK = ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Unknown'];
                    }

                    // ===== Progress berdasarkan status log AK terakhir =====
                    // (pakai angka yang sama seperti sebelumnya)
                    $progress = null;
                    if ($akStatus === App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN) {
                    $progress = ['class' => 'success', 'width' => 100, 'text' => '100%'];
                    } elseif ($akStatus === App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI) {
                    $progress = ['class' => 'success', 'width' => 90, 'text' => '90%'];
                    } elseif ($akStatus === App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION) {
                    $progress = ['class' => 'warning', 'width' => 75, 'text' => '75%'];
                    } elseif ($akStatus === App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS) {
                    $progress = ['class' => 'info', 'width' => 50, 'text' => '50%'];
                    } elseif ($akStatus === App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED) {
                    $progress = ['class' => 'secondary', 'width' => 10, 'text' => '10%'];
                    }
                    @endphp
                    <tr>
                        <td>{{ $pengajuans->firstItem() + $index }}</td>
                        <td>
                            {!! $pengajuan->getPermohonanAkreditasiSectionFor('de') !!}
                        </td>
                        <td>
                            @if($asesorCount > 0 || $validatorCount > 0)
                            <div class="mb-1">
                                <small><i class="bi bi-person"></i> Asesor: <strong>{{ $asesorCount }}</strong></small>
                            </div>
                            <div>
                                <small><i class="bi bi-person-check"></i> Validator: <strong>{{ $validatorCount }}</strong></small>
                            </div>
                            @if(!$requirementsMet)
                            <div class="mt-1">
                                <span class="badge bg-warning"><i class="bi bi-exclamation-triangle"></i> Belum Memenuhi Syarat</span>
                            </div>
                            @else
                            <div class="mt-1">
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Syarat Terpenuhi</span>
                            </div>
                            @endif
                            @else
                            <span class="text-muted">Belum ada penugasan</span>
                            @endif
                        </td>
                        <td>
                            {!! $pengajuan->getCustomBadgeLastStatus('penugasan_asesor_ak','de','label_short_for') !!}
                        </td>
                        <td>
                            @if($pengajuan->tanggal_penugasan_asesor_ak)
                            <small>{{ $pengajuan->tanggal_penugasan_asesor_ak->locale('id')->translatedFormat('d M Y') }}</small>
                            <br>
                            <small class="text-muted">
                                {{ $pengajuan->tanggal_penugasan_asesor_ak->diffForHumans() }}
                            </small>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('de.penugasan-ak.show', $pengajuan->id) }}" class="btn btn-sm btn-primary" title="Lihat Detail & Tugaskan">
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
            <p class="text-muted mt-3 mb-0">Tidak ada permohonan akreditasi yang sudah berada pada tahap AK</p>
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
