{{-- resources/views/de/validasi-dokumen/components/table-content.blade.php --}}

<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-table"></i> Daftar Validasi
            </h5>
            <span class="badge bg-primary">Total: {{ $assignments->total() }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Nomor Pengajuan Akreditasi</th>
                        <th width="20%">Program Studi</th>
                        <th width="15%">Validator</th>
                        <th width="12%">Status Penawaran</th>
                        <th width="12%">Status Pekerjaan</th>
                        <th width="10%">Progress</th>
                        <th width="11%" class="text-center">Aksi</th>
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
                            <p>{{ $pengajuan->judul }}</p>
                            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $pengajuan->studyProgram->name }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $pengajuan->studyProgram->university->name }}
                                </small>
                                <br>
                                <span class="badge bg-info">
                                    {{ $pengajuan->studyProgram->degreeLevel->name }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <strong>{{ $assignment->user->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $assignment->user->email }}</small>
                        </td>
                        <td>
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
                                {{ $assignment->responded_at->format('d M H:i') }}
                            </small>
                            @endif
                        </td>
                        <td>
                            @if($assignment->status_penawaran === 'accepted')
                            @php
                            $pekerjaanConfig = [
                            'not_started' => ['class' => 'secondary', 'icon' => 'clock', 'text' => 'Belum Mulai'],
                            'in_progress' => ['class' => 'info', 'icon' => 'arrow-repeat', 'text' => 'Sedang Dikerjakan'],
                            'submitted' => ['class' => 'primary', 'icon' => 'upload', 'text' => 'Sudah Submit'],
                            'revision_required' => ['class' => 'danger', 'icon' => 'exclamation-triangle', 'text' => 'Perlu Revisi'],
                            'approved' => ['class' => 'success', 'icon' => 'check2-circle', 'text' => 'Disetujui'],
                            ];
                            $pekerjaan = $pekerjaanConfig[$assignment->status_pekerjaan] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Unknown'];
                            @endphp
                            <span class="badge bg-{{ $pekerjaan['class'] }}">
                                <i class="bi bi-{{ $pekerjaan['icon'] }}"></i>
                                {{ $pekerjaan['text'] }}
                            </span>
                            @if($assignment->submitted_at)
                            <br>
                            <small class="text-muted">
                                {{ $assignment->submitted_at->format('d M H:i') }}
                            </small>
                            @endif
                            @else
                            <span class="text-muted">-</span>
                            @endif
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

                            @if($assignment->status_penawaran === 'accepted' && $validation)
                            <a href="{{ route('validator.borang.show', $assignment->id) }}" class="btn btn-sm btn-primary" title="Lihat Validasi" target="_blank">
                                <i class="bi bi-clipboard-check"></i>
                            </a>
                            @endif
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
        {{ $assignments->links() }}
    </div>
    @endif
</div>
