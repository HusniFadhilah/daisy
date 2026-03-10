{{-- resources/views/de/penugasan-banding/components/table-content.blade.php --}}
<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <input type="text" id="searchInput" class="form-control form-control-sm" style="width:200px;" placeholder="Cari nomor / prodi..." value="{{ request('search') }}">
            <select id="universityFilter" class="form-select form-select-sm" style="width:180px;">
                <option value="">Semua Universitas</option>
                @foreach(\App\Models\University::nonExample()->orderBy('name')->get() as $univ)
                <option value="{{ $univ->id }}" {{ request('university_id') == $univ->id ? 'selected' : '' }}>
                    {{ $univ->name }}
                </option>
                @endforeach
            </select>
            <select id="statusFilter" class="form-select form-select-sm" style="width:170px;">
                <option value="">Semua Status</option>
                <option value="belum_ditugaskan" {{ request('status_banding') === 'belum_ditugaskan' ? 'selected' : '' }}>Belum Ditugaskan</option>
                <option value="sudah_ditugaskan" {{ request('status_banding') === 'sudah_ditugaskan' ? 'selected' : '' }}>Sudah Ditugaskan</option>
                <option value="selesai" {{ request('status_banding') === 'selesai'          ? 'selected' : '' }}>Selesai</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>
        <span class="badge bg-primary">Total: {{ $pengajuans->total() }}</span>
    </div>
    <div class="card-body p-0">
        @if($pengajuans->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="24%">Permohonan</th>
                        <th width="18%">Penugasan</th>
                        <th width="16%">Status Banding</th>
                        <th width="15%">Tanggal Penugasan</th>
                        <th width="8%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuans as $index => $pengajuan)
                    @php
                    // Hitung asesor banding (tidak ada validator)
                    $assignments = $pengajuan->asesmen
                    ? $pengajuan->asesmen->asesmenUserRoles->where('jenis_asesmen', 'banding')
                    : collect();

                    $asesorCount = $assignments->filter(fn($a) =>
                    $a->role_selected && $a->role_selected->name === 'asesor_banding'
                    )->count();

                    $requirementsMet = $asesorCount >= 1;

                    $bandingStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
                    ];

                    $lastLog = $pengajuan->statusLog
                    ->whereIn('status_to', $bandingStatuses)
                    ->sortByDesc('changed_at')->first();

                    $currentStatus = $lastLog ? $lastLog->status_to : null;

                    $statusBadge = match($currentStatus) {
                    \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN => ['success', 'check-circle-fill', 'Selesai'],
                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN => ['info', 'hourglass-split', 'Dilaksanakan'],
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED => ['warning', 'clock', 'Ditugaskan'],
                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DITERIMA => ['danger', 'x-circle', 'Belum Ditugaskan'],
                    default => ['secondary', 'question-circle', 'Unknown'],
                    };
                    @endphp
                    <tr>
                        <td>{{ $pengajuans->firstItem() + $index }}</td>
                        <td>{!! $pengajuan->getPermohonanAkreditasiSectionFor('de') !!}</td>
                        <td>
                            @if($asesorCount > 0)
                            <div class="mb-1">
                                <small><i class="bi bi-person-badge"></i> Asesor Banding: <strong>{{ $asesorCount }}</strong></small>
                            </div>
                            <div class="mt-1">
                                @if(!$requirementsMet)
                                <span class="badge bg-warning"><i class="bi bi-exclamation-triangle"></i> Belum Memenuhi Syarat</span>
                                @else
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Syarat Terpenuhi</span>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">Belum ada penugasan</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusBadge[0] }}">
                                <i class="bi bi-{{ $statusBadge[1] }}"></i> {{ $statusBadge[2] }}
                            </span>
                        </td>
                        <td>
                            @if($pengajuan->tanggal_penugasan_banding)
                            <small>{{ $pengajuan->tanggal_penugasan_banding->locale('id')->translatedFormat('d M Y') }}</small><br>
                            <small class="text-muted">{{ $pengajuan->tanggal_penugasan_banding->diffForHumans() }}</small>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('de.penugasan-banding.show', $pengajuan->id) }}" class="btn btn-sm btn-primary" title="Lihat Detail & Tugaskan">
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
            <i class="bi bi-inbox" style="font-size:3rem; color:#dee2e6;"></i>
            <p class="text-muted mt-3 mb-0">Tidak ada permohonan banding ditemukan</p>
        </div>
        @endif
    </div>
    @if($pengajuans->hasPages())
    <div class="card-footer bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <div>Menampilkan {{ $pengajuans->firstItem() }} - {{ $pengajuans->lastItem() }} dari {{ $pengajuans->total() }} data</div>
            <div>{{ $pengajuans->links() }}</div>
        </div>
    </div>
    @endif
</div>
