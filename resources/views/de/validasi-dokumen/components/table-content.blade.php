{{-- resources/views/de/validasi-dokumen/components/table-content.blade.php --}}

<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-table"></i> Daftar Validasi Dokumen
            </h5>
            <span class="badge bg-primary">Total: {{ $assignments->total() }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th width="20%">Permohonan Akreditasi</th>
                        <th width="25%">Validator</th>
                        <th width="20%">Status Validasi Dokumen</th>
                        <th width="20%">Progress</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $index => $assignment)
                    @php
                    $pengajuan = $assignment->asesmen->pengajuan;
                    $validation = $assignment->borangValidation;
                    $progress = $validation ? $validation->getProgressPercentage() : ['percentage' => 0];
                    @endphp
                    <tr>
                        <td>{{ $assignments->firstItem() + $index }}</td>
                        <td>
                            {!! $pengajuan->getPermohonanAkreditasiSectionFor('de') !!}
                        </td>
                        <td>
                            <strong>{{ $assignment->user->name }}</strong>
                            <br>
                            <small class="text-muted text-wrap">{{ $assignment->user->email }}</small>

                            <br>
                            <small>Status Penawaran:</small>
                            @php
                            $penawaranConfig = [
                            'pending' => ['class' => 'warning', 'icon' => 'hourglass-split', 'text' => 'Menunggu'],
                            'accepted' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Diterima'],
                            'rejected' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Ditolak'],
                            ];
                            $penawaran = $penawaranConfig[$assignment->status_penawaran] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Unknown'];
                            @endphp
                            <span class="badge bg-{{ $penawaran['class'] }}">
                                <i class="bi bi-{{ $penawaran['icon'] }}"></i>
                                {{ $penawaran['text'] }}
                            </span>
                            @if($assignment->responded_at)
                            <br>
                            <small class="text-muted">
                                {{ $assignment->responded_at->locale('id')->translatedFormat('d M H:i') }}
                            </small>
                            @endif
                        </td>
                        <td>
                            {!! $pengajuan->getCustomBadgeLastStatus('validasi_dokumen','de','label_short_for') !!}
                        </td>
                        <td>
                            @if($validation && $assignment->status_penawaran === 'accepted')
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $progress['percentage'] >= 80 ? 'success' : 'warning' }}" style="width: {{ $progress['percentage'] }}%">
                                        {{ $progress['percentage'] }}%
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">
                                {{ $progress['reviewed'] }}/{{ $progress['total'] }} item
                            </small>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('de.validasi-dokumen.show', $assignment->id) }}" class="btn btn-sm btn-info" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>

                            {{-- @if($assignment->status_penawaran === 'accepted' && $validation)
                            <a href="{{ route('validator.borang.show', $assignment->id) }}" class="btn btn-sm btn-primary" title="Lihat Validasi" target="_blank">
                            <i class="bi bi-clipboard-check"></i>
                            </a>
                            @endif --}}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-3">Tidak ada data validasi</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($assignments->hasPages())
    <div class="card-footer">
        {{ $assignments->onEachSide(1)->links() }}
    </div>
    @endif
</div>
