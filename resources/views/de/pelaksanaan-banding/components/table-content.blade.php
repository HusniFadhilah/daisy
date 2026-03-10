{{-- resources/views/de/pelaksanaan-banding/components/table-content.blade.php --}}
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
            <select id="statusFilter" class="form-select form-select-sm" style="width:190px;">
                <option value="">Semua Status</option>
                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED }}" {{ request('status_pelaksanaan') === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED ? 'selected' : '' }}>
                    Ditugaskan
                </option>
                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN }}" {{ request('status_pelaksanaan') === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN ? 'selected' : '' }}>
                    Dilaksanakan
                </option>
                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN }}" {{ request('status_pelaksanaan') === \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN ? 'selected' : '' }}>
                    Dilaporkan
                </option>
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
                        <th width="20%">Asesor Banding</th>
                        <th width="14%">Status</th>
                        <th width="17%">Periode Banding</th>
                        <th width="12%">Dokumen</th>
                        <th width="8%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuans as $index => $pengajuan)
                    @php
                    $assignments = $pengajuan->asesmen
                    ? $pengajuan->asesmen->asesmenUserRoles->where('jenis_asesmen', 'banding')
                    : collect();

                    $lastLog = $pengajuan->statusLog
                    ->whereIn('status_to', [
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
                    ])
                    ->sortByDesc('changed_at')->first();

                    $currentStatus = $lastLog ? $lastLog->status_to : null;

                    $statusBadge = match($currentStatus) {
                    \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN => ['success', 'check-circle-fill', 'Dilaporkan'],
                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN => ['info', 'hourglass-split', 'Dilaksanakan'],
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED => ['warning', 'clock', 'Ditugaskan'],
                    default => ['secondary', 'question-circle', '-'],
                    };

                    $asesmenBanding = $pengajuan->asesmen ? $pengajuan->asesmen->asesmenBanding : null;
                    @endphp
                    <tr>
                        <td>{{ $pengajuans->firstItem() + $index }}</td>
                        <td>{!! $pengajuan->getPermohonanAkreditasiSectionFor('de') !!}</td>
                        <td>
                            @if($assignments->count() > 0)
                            @foreach($assignments as $a)
                            <div class="d-flex align-items-center gap-1 mb-1">
                                <span class="badge bg-light text-dark border">#{{ $a->urutan_asesor }}</span>
                                <small>{{ $a->user->name }}</small>
                                @if($a->status_penawaran === 'accepted')
                                <i class="bi bi-check-circle-fill text-success" title="Diterima" style="font-size:10px;"></i>
                                @elseif($a->status_penawaran === 'pending')
                                <i class="bi bi-clock text-warning" title="Pending" style="font-size:10px;"></i>
                                @else
                                <i class="bi bi-x-circle text-danger" title="Ditolak" style="font-size:10px;"></i>
                                @endif
                            </div>
                            @endforeach
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusBadge[0] }}">
                                <i class="bi bi-{{ $statusBadge[1] }}"></i> {{ $statusBadge[2] }}
                            </span>
                        </td>
                        <td>
                            @if($asesmenBanding && $asesmenBanding->tanggal_mulai)
                            <small>
                                {{ \Carbon\Carbon::parse($asesmenBanding->tanggal_mulai)->locale('id')->translatedFormat('d M Y') }}<br>
                                s/d {{ $asesmenBanding->tanggal_selesai
                                    ? \Carbon\Carbon::parse($asesmenBanding->tanggal_selesai)->locale('id')->translatedFormat('d M Y')
                                    : '—' }}
                            </small>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                            $laporanAda = $pengajuan->dokumen
                            ->where('jenis_dokumen', 'laporan_banding')
                            ->where('is_latest', true)->first();
                            $baAda = $pengajuan->dokumen
                            ->where('jenis_dokumen', 'berita_acara_banding')
                            ->where('is_latest', true)->first();
                            @endphp
                            <div class="d-flex gap-1 flex-column">
                                <small>
                                    <i class="bi bi-{{ $laporanAda ? 'check-circle-fill text-success' : 'circle text-muted' }}"></i>
                                    Laporan
                                </small>
                                <small>
                                    <i class="bi bi-{{ $baAda ? 'check-circle-fill text-success' : 'circle text-muted' }}"></i>
                                    Berita Acara
                                </small>
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('de.pelaksanaan-banding.show', $pengajuan->id) }}" class="btn btn-sm btn-primary" title="Detail Pelaksanaan">
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
            <p class="text-muted mt-3 mb-0">Tidak ada data pelaksanaan banding</p>
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
