<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-table"></i> Daftar Penugasan Asesor AL
        </h6>
        <span class="badge bg-primary">Total: {{ $pengajuans->total() }}</span>
    </div>
    <div class="card-body p-0">
        @if($pengajuans->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="12%">Permohonan Akreditasi</th>
                        <th width="22%">Program Studi</th>
                        <th width="12%">Status AL</th>
                        <th width="15%">Penugasan</th>
                        <th width="15%">Jadwal Visitasi</th>
                        <th width="12%">Progress</th>
                        <th width="7%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuans as $index => $pengajuan)
                    @php
                    // Get asesor count
                    $asesorCount = $pengajuan->asesmen?->asesmenUserRoles->count() ?? 0;

                    // Get jadwal
                    $jadwal = $pengajuan->asesmen?->asesmenLapangan;

                    // Determine status AL
                    if ($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN) {
                    $statusAL = ['class' => 'success', 'icon' => 'check-circle-fill', 'text' => 'Dilaporkan'];
                    } elseif ($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI) {
                    $statusAL = ['class' => 'success', 'icon' => 'check-circle', 'text' => 'AL Selesai'];
                    } elseif ($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS) {
                    $statusAL = ['class' => 'info', 'icon' => 'hourglass-split', 'text' => 'Sedang Proses'];
                    } elseif ($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED) {
                    $statusAL = ['class' => 'warning', 'icon' => 'clock', 'text' => 'Ditugaskan'];
                    } elseif ($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN) {
                    $statusAL = ['class' => 'danger', 'icon' => 'check-circle', 'text' => 'Siap AL'];
                    } else {
                    $statusAL = ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Unknown'];
                    }

                    // Check requirements
                    $requirementsMet = $asesorCount >= 2;
                    @endphp
                    <tr>
                        <td>{{ $pengajuans->firstItem() + $index }}</td>
                        <td>
                            <p>{{ $pengajuan->judul }}</p>
                            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
                        </td>
                        <td>
                            <div class="mb-1">
                                <strong>{{ $pengajuan->studyProgram->name }}</strong>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-building"></i> {{ $pengajuan->studyProgram->university->name }}
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusAL['class'] }}">
                                <i class="bi bi-{{ $statusAL['icon'] }}"></i> {{ $statusAL['text'] }}
                            </span>
                        </td>
                        <td>
                            @if($asesorCount > 0)
                            <div class="mb-1">
                                <small><i class="bi bi-person"></i> Asesor: <strong>{{ $asesorCount }}</strong></small>
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
                            @if($jadwal && $jadwal->tanggal_mulai)
                            <small>
                                <i class="bi bi-calendar-event"></i>
                                {{ \Carbon\Carbon::parse($jadwal->tanggal_mulai)->format('d M') }} -
                                {{ \Carbon\Carbon::parse($jadwal->tanggal_selesai)->format('d M Y') }}
                                <br>
                                <i class="bi bi-geo-alt text-danger"></i>
                                <span class="text-muted">{{ Str::limit($jadwal->lokasi_visitasi, 25) }}</span>
                            </small>
                            @else
                            <span class="text-muted">Belum dijadwalkan</span>
                            @endif
                        </td>
                        <td>
                            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN)
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: 100%">100%</div>
                            </div>
                            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI)
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: 90%">90%</div>
                            </div>
                            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS)
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-info" style="width: 50%">50%</div>
                            </div>
                            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED)
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-secondary" style="width: 10%">10%</div>
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('de.penugasan-al.show', $pengajuan->id) }}" class="btn btn-sm btn-primary" title="Lihat Detail & Tugaskan">
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
            <p class="text-muted mt-3 mb-0">Tidak ada permohonan akreditasi yang siap untuk AL</p>
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
