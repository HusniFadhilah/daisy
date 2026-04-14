<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-table"></i> Daftar Pelaksanaan AL
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
                        <th width="15%">Permohonan Akreditasi</th>
                        <th width="13%">Asesor</th>
                        <th width="13%">Validator</th>
                        {{-- <th width="15%">Progress</th> --}}
                        <th width="17%">Status Pelaksanaan AL</th>
                        <th width="17%">Tanggal AL Selesai</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuans as $index => $pengajuan)
                    @php
                    // Count asesor & validator
                    $asesorCount = 0;
                    $validatorCount = 0;
                    if ($pengajuan->asesmen) {
                    foreach ($pengajuan->asesmen->asesmenUserRoles as $aur) {
                    if ($aur->role_selected->name === 'asesor') {
                    $asesorCount++;
                    } elseif ($aur->role_selected->name === 'validator') {
                    $validatorCount++;
                    }
                    }
                    }

                    // Determine status
                    if ($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN) {
                    $statusBadge = ['class' => 'success', 'icon' => 'check-circle-fill', 'text' => 'Dilaporkan'];
                    }elseif ($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI) {
                    // Check if has validator
                    if ($validatorCount > 0) {
                    $statusBadge = ['class' => 'warning', 'icon' => 'person-check', 'text' => 'Validator Assigned'];
                    } else {
                    $statusBadge = ['class' => 'warning', 'icon' => 'exclamation-triangle', 'text' => 'Perlu Validator'];
                    }
                    } elseif ($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS) {
                    $statusBadge = ['class' => 'primary', 'icon' => 'hourglass-split', 'text' => 'Visitasi Berlangsung'];
                    } elseif ($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED) {
                    $statusBadge = ['class' => 'secondary', 'icon' => 'clock', 'text' => 'Asesor Ditugaskan'];
                    } else {
                    $statusBadge = ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Unknown'];
                    }
                    @endphp
                    <tr>
                        <td>{{ $pengajuans->firstItem() + $index }}</td>
                        <td>
                            {!! $pengajuan->getPermohonanAkreditasiSectionFor('de') !!}
                        </td>
                        <td>
                            @if($asesorCount > 0)
                            <div>
                                <i class="bi bi-people"></i> <strong>{{ $asesorCount }}</strong> asesor
                            </div>
                            @else
                            <span class="text-muted">Belum ada</span>
                            @endif
                        </td>
                        <td>
                            @if($validatorCount > 0)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> {{ $validatorCount }} validator
                            </span>
                            @else
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-exclamation-triangle"></i> Belum ada
                            </span>
                            @endif
                        </td>
                        <td>
                            {!! $pengajuan->getCustomBadgeLastStatus('pelaksanaan_al','de','label_short_for') !!}
                            {{-- <span class="badge bg-{{ $statusBadge['class'] }}">
                            <i class="bi bi-{{ $statusBadge['icon'] }}"></i> {{ $statusBadge['text'] }}
                            </span> --}}
                        </td>
                        <td>
                            @if($pengajuan->tanggal_al_selesai)
                            <small>{{ $pengajuan->tanggal_al_selesai->locale('id')->translatedFormat('d M Y') }}</small>
                            <br>
                            <small class="text-muted">
                                {{ $pengajuan->tanggal_al_selesai->diffForHumans() }}
                            </small>
                            @else
                            <span class="text-muted">-</span>
                            @endif

                            {{-- @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN)
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: 100%">100%</div>
                            </div>
                            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI)
                            <div class="progress" style="height: 20px;">
                                @if($validatorCount > 0)
                                <div class="progress-bar bg-warning" style="width: 60%">60%</div>
                                @else
                                <div class="progress-bar bg-primary" style="width: 50%">50%</div>
                                @endif
                            </div>
                            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS)
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-secondary" style="width: 30%">30%</div>
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif --}}
                        </td>
                        <td class="text-center">
                            <a href="{{ route('de.pelaksanaan-al.show', $pengajuan->id) }}" class="btn btn-sm btn-primary" title="Lihat Detail">
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
            <p class="text-muted mt-3 mb-0">Tidak ada AL yang sedang berlangsung</p>
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
