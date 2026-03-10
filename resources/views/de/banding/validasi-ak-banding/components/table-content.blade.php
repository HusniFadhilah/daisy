<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-table"></i> Daftar Validasi AK Banding
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
                        <th width="15%">Status Validasi</th>
                        <th width="15%">Progres Validasi</th>
                        <th width="15%">Tanggal Validasi AK</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuans as $index => $pengajuan)
                    @php
                    // ===== Validator list =====
                    $validators = $pengajuan->asesmen?->asesmenUserRoles->filter(function ($aur) {
                    return $aur->role_selected->name === 'validator';
                    }) ?? collect();

                    // ===== STATUS AK dari STATUS LOG =====
                    $akStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_BANDING_IN_PROGRESS,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_BANDING_ON_VALIDATION,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_BANDING_SELESAI,
                    //\App\Models\PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN,
                    ];

                    // statusLog sudah di-order by changed_at DESC dari controller
                    $lastAkLog = $pengajuan->statusLog
                    ?->firstWhere(fn ($log) => in_array($log->status_to, $akStatuses, true));

                    $akStatus = $lastAkLog?->status_to;

                    // ===== Badge status =====
                    $statusConfig = match ($akStatus) {
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED => [
                    'class' => 'secondary',
                    'icon' => 'hourglass',
                    'text' => 'Belum Mulai',
                    ],
                    \App\Models\PengajuanAkreditasi::STATUS_AK_BANDING_IN_PROGRESS => [
                    'class' => 'info',
                    'icon' => 'pencil-square',
                    'text' => 'Menunggu Penilaian AK Selesai',
                    ],
                    \App\Models\PengajuanAkreditasi::STATUS_AK_BANDING_ON_VALIDATION => [
                    'class' => 'warning',
                    'icon' => 'shield-check',
                    'text' => 'Sedang Validasi',
                    ],
                    \App\Models\PengajuanAkreditasi::STATUS_AK_BANDING_SELESAI => [
                    'class' => 'success',
                    'icon' => 'check-circle',
                    'text' => 'Validasi Selesai',
                    ],
                    \App\Models\PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN => [
                    'class' => 'primary',
                    'icon' => 'file-earmark-check',
                    'text' => 'Validasi Dilaporkan',
                    ],
                    default => [
                    'class' => 'secondary',
                    'icon' => 'question-circle',
                    'text' => 'Unknown',
                    ],
                    };

                    // ===== Progres validasi =====
                    // (disarankan: $totalElements dipindah ke luar loop)
                    $totalElements = $totalElements ?? \DB::table('elemen_standar')->count();

                    $validatedCount = \DB::table('penilaian_elemen_ak_banding')
                    ->where('id_asesmen', $pengajuan->asesmen->id ?? 0)
                    ->whereNotNull('validated_at')
                    ->distinct('id_elemen')
                    ->count('id_elemen');

                    $progressPercentage = $totalElements > 0
                    ? round(($validatedCount / $totalElements) * 100, 1)
                    : 0;

                    $progressColor = $progressPercentage == 100
                    ? 'success'
                    : ($progressPercentage >= 50 ? 'info' : 'warning');
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
                                    @if($validator->status_penawaran === 'accepted')
                                    <span class="badge bg-success badge-sm">✓</span>
                                    @elseif($validator->status_penawaran === 'pending')
                                    <span class="badge bg-warning badge-sm">⏳</span>
                                    @endif
                                </small>
                            </div>
                            @endforeach
                            @else
                            <small class="text-muted">Belum ada validator</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusConfig['class'] }}">
                                <i class="bi bi-{{ $statusConfig['icon'] }}"></i> {{ $statusConfig['text'] }}
                            </span>
                        </td>
                        <td>
                            <div class="progress mb-1" style="height: 20px;">
                                <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ $progressPercentage }}%">
                                    {{ $progressPercentage }}%
                                </div>
                            </div>
                            <small class="text-muted">{{ $validatedCount }}/{{ $totalElements }} elemen</small>
                        </td>
                        <td>
                            @if($pengajuan->tanggal_validasi_ak)
                            <small>
                                {{ $pengajuan->tanggal_validasi_ak->locale('id')->translatedFormat('d M Y') }}
                            </small>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('de.banding.validasi-ak-banding.show', $pengajuan->id) }}" class="btn btn-sm btn-primary" title="Lihat Detail">
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
            <p class="text-muted mt-3 mb-0">Tidak ada data validasi AK banding</p>
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
