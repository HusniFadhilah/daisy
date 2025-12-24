<!-- Urgent Programs Section -->
@if($urgentPrograms->count() > 0)
<div class="card mb-4" id="urgent-section">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">
            <i class="bi bi-alarm"></i> Program Studi Segera Kedaluwarsa (6 Bulan Ke Depan)
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($urgentPrograms as $program)
            @php
            $daysLeft = floor(now()->diffInDays($program->tanggal_kedaluwarsa, false));
            @endphp
            <div class="col-md-6 mb-3">
                <div class="card timeline-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold mb-1">{{ $program->name }}</h6>
                                <small class="text-muted">
                                    {{ $program->degreeLevel->name }} - {{ $program->university->name }}
                                </small>
                            </div>
                            @if($program->peringkat_akreditasi)
                            <span class="peringkat-badge peringkat-{{ strtolower(str_replace(' ', '-', $program->peringkat_akreditasi)) }}">
                                {{ $program->peringkat_akreditasi }}
                            </span>
                            @endif
                        </div>
                        <div class="alert alert-warning alert-permanent mb-2 py-2">
                            <small>
                                <i class="bi bi-clock"></i>
                                <strong>Kedaluwarsa:</strong> {{ $program->tanggal_kedaluwarsa->format('d M Y') }}
                                ({{ $daysLeft }} hari lagi)
                            </small>
                        </div>
                        <a href="{{ route('pemetaan.show', $program->id) }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-eye"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Programs Table -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            Daftar Program Studi (<span id="totalPrograms">{{ $studyPrograms->total() }}</span>)
        </h5>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-success" onclick="exportExcel()">
                <i class="bi bi-file-excel"></i> Download Data
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Program Studi</th>
                        <th style="width: 80px;">Jenjang</th>
                        <th style="width: 120px;">Peringkat</th>
                        <th style="width: 150px;">Status</th>
                        <th style="width: 150px;">Kedaluwarsa</th>
                        <th style="width: 100px;">Sisa Waktu</th>
                        <th style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($studyPrograms as $index => $program)
                    @php
                    $daysLeft = $program->tanggal_kedaluwarsa ? floor(now()->diffInDays($program->tanggal_kedaluwarsa, false)) : null;
                    $progressPercent = $daysLeft ? max(0, min(100, ($daysLeft / (5 * 365)) * 100)) : 0;
                    @endphp
                    <tr>
                        <td>{{ $studyPrograms->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-bold">{{ $program->name }}</div>
                            <small class="text-muted">{{ $program->university->name }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $program->degreeLevel->code }}</span>
                        </td>
                        <td>
                            @if($program->peringkat_akreditasi)
                            <span class="peringkat-badge peringkat-{{ strtolower(str_replace(' ', '-', $program->peringkat_akreditasi)) }}">
                                {{ $program->peringkat_akreditasi }}
                            </span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge status-{{ is_null($daysLeft) ? 'belum' : ($daysLeft < 0 ? 'kedaluwarsa' : 'aktif') }} {{ !is_null($daysLeft) && $daysLeft <= 90 && $daysLeft >= 0 ? 'status-urgent' : '' }}">{{ is_null($daysLeft) ? 'Belum Ditentukan' : ($daysLeft < 0 ? 'Kedaluwarsa' : 'Aktif') }}
                            </span>
                        </td>
                        <td>
                            @if($program->tanggal_kedaluwarsa)
                            <small>{{ $program->tanggal_kedaluwarsa->format('d M Y') }}</small>
                            @else
                            <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td>
                            @if($daysLeft !== null && $daysLeft > 0)
                            <div class="progress progress-custom">
                                <div class="progress-bar progress-bar-custom bg-{{ $progressPercent > 50 ? 'success' : ($progressPercent > 20 ? 'warning' : 'danger') }}" style="width: {{ $progressPercent }}%">
                                </div>
                            </div>
                            <small class="text-muted">{{ $daysLeft }} hari</small>
                            @elseif($daysLeft !== null && $daysLeft <= 0) <small class="text-danger fw-bold">Expired</small>
                                @else
                                <small class="text-muted">-</small>
                                @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('pemetaan.show', $program->id) }}" class="btn btn-outline-primary action-btn">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($program->status_kedaluwarsa != 'Aktif' || ($daysLeft && $daysLeft <= 180)) <a href="{{ route('pengajuan.create', ['study_program_id' => $program->id]) }}" class="btn btn-outline-success action-btn">
                                    <i class="bi bi-plus-circle"></i>
                                    </a>
                                    @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-3">Tidak ada data ditemukan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($studyPrograms->hasPages())
    <div class="card-footer bg-white">
        <nav aria-label="Page navigation">
            {{ $studyPrograms->onEachSide(2)->links('pagination::bootstrap-5') }}
        </nav>
    </div>
    @endif
</div>
