<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-table"></i> Daftar Penugasan AL
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
                        <th width="12%">Nomor Permohonan</th>
                        <th width="25%">Program Studi</th>
                        <th width="13%">Status</th>
                        <th width="20%">Asesor</th>
                        <th width="15%">Jadwal Visitasi</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuans as $index => $pengajuan)
                    @php
                    // Get asesor assignments
                    $asesors = $pengajuan->asesmen?->asesmenUserRoles ?? collect();

                    // Get jadwal from asesmen lapangan
                    $jadwal = $pengajuan->asesmen?->asesmenLapangan;

                    // Determine status badge
                    $statusConfig = match($pengajuan->status) {
                    \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI => [
                    'class' => 'success',
                    'icon' => 'check-circle',
                    'text' => 'Siap AL'
                    ],
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED => [
                    'class' => 'info',
                    'icon' => 'person-check',
                    'text' => 'Sudah Ditugaskan'
                    ],
                    \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS => [
                    'class' => 'primary',
                    'icon' => 'gear',
                    'text' => 'Sedang Berlangsung'
                    ],
                    \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI => [
                    'class' => 'success',
                    'icon' => 'check-circle-fill',
                    'text' => 'Selesai'
                    ],
                    \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN => [
                    'class' => 'dark',
                    'icon' => 'file-earmark-check',
                    'text' => 'Dilaporkan'
                    ],
                    default => [
                    'class' => 'secondary',
                    'icon' => 'question-circle',
                    'text' => 'Unknown'
                    ]
                    };
                    @endphp
                    <tr>
                        <td>{{ $pengajuans->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $pengajuan->nomor_pengajuan }}</strong>
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
                            <span class="badge bg-{{ $statusConfig['class'] }}">
                                <i class="bi bi-{{ $statusConfig['icon'] }}"></i> {{ $statusConfig['text'] }}
                            </span>
                        </td>
                        <td>
                            @if($asesors->count() > 0)
                            @foreach($asesors->sortBy('urutan_asesor') as $asesor)
                            <div class="mb-1">
                                <small>
                                    <span class="badge bg-secondary">#{{ $asesor->urutan_asesor }}</span>
                                    {{ $asesor->user->name }}
                                    @if($asesor->status_penawaran === 'accepted')
                                    <span class="badge bg-success badge-sm">✓</span>
                                    @elseif($asesor->status_penawaran === 'pending')
                                    <span class="badge bg-warning badge-sm">⏳</span>
                                    @endif
                                </small>
                            </div>
                            @endforeach
                            @else
                            <span class="text-muted">Belum ada</span>
                            @endif
                        </td>
                        <td>
                            @if($jadwal && $jadwal->tanggal_mulai)
                            <small>
                                <i class="bi bi-calendar-event"></i>
                                {{ \Carbon\Carbon::parse($jadwal->tanggal_mulai)->format('d M') }} -
                                {{ \Carbon\Carbon::parse($jadwal->tanggal_selesai)->format('d M Y') }}
                                <br>
                                <i class="bi bi-geo-alt"></i>
                                <span class="text-muted">{{ Str::limit($jadwal->lokasi_visitasi, 30) }}</span>
                            </small>
                            @else
                            <span class="text-muted">Belum dijadwalkan</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('de.penugasan-al.show', $pengajuan->id) }}" class="btn btn-sm btn-primary" title="Lihat Detail">
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
            <p class="text-muted mt-3 mb-0">Tidak ada data penugasan AL</p>
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
