@extends('layouts.template.app')

@section('title', 'Monitoring BAN-PT - DAISY LAMDEPILAR')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h2 class="mb-1">Monitoring BAN-PT</h2>
            <p class="text-muted mb-0">Pantau perubahan data akreditasi dari BAN-PT Bianglala</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            @if($lastRun)
                <small class="text-muted">
                    Sync terakhir:
                    <strong>{{ $lastRun->started_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                    <span class="badge bg-{{ $lastRun->status === 'success' ? 'success' : ($lastRun->status === 'running' ? 'warning' : 'danger') }}">
                        {{ $lastRun->status }}
                    </span>
                </small>
            @endif
            <form method="POST" action="{{ route('admin.monitoring-banpt.sync-now') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm"
                    onclick="return confirm('Jalankan sinkronisasi BAN-PT sekarang? Proses ini memerlukan beberapa menit.')"
                    {{ optional($lastRun)->status === 'running' ? 'disabled' : '' }}>
                    <i class="bi bi-arrow-repeat"></i> Sync Sekarang
                </button>
            </form>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle"></i> {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show"><i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-x-circle"></i> {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'pending' ? 'active' : '' }}" href="{{ route('admin.monitoring-banpt.index', ['tab' => 'pending']) }}">
                Menunggu
                @if($pendingCount > 0)
                    <span class="badge bg-warning text-dark">{{ $pendingCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'conflict' ? 'active' : '' }}" href="{{ route('admin.monitoring-banpt.index', ['tab' => 'conflict']) }}">
                Konflik
                @if($conflictCount > 0)
                    <span class="badge bg-danger">{{ $conflictCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'applied' ? 'active' : '' }}" href="{{ route('admin.monitoring-banpt.index', ['tab' => 'applied']) }}">
                Diterapkan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'ignored' ? 'active' : '' }}" href="{{ route('admin.monitoring-banpt.index', ['tab' => 'ignored']) }}">
                Diabaikan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'runs' ? 'active' : '' }}" href="{{ route('admin.monitoring-banpt.index', ['tab' => 'runs']) }}">
                Riwayat Sync
            </a>
        </li>
    </ul>

    {{-- Tabel perubahan --}}
    @if($tab !== 'runs' && $changes !== null)
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Perguruan Tinggi</th>
                            <th>Program Studi</th>
                            <th>Jenjang</th>
                            <th>Peringkat</th>
                            <th>Kedaluwarsa</th>
                            <th>Status Kedal.</th>
                            <th>Terdeteksi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($changes as $change)
                        <tr>
                            <td>
                                <small>{{ $change->studyProgram?->university?->name ?? $change->old_university_name ?? '-' }}</small>
                            </td>
                            <td>
                                <strong>{{ $change->studyProgram?->name ?? $change->old_program_studi ?? '-' }}</strong>
                            </td>
                            <td>
                                <small>{{ $change->studyProgram?->degreeLevel?->alias ?? $change->old_jenjang ?? '-' }}</small>
                            </td>
                            <td>
                                @if($change->old_peringkat_akreditasi !== $change->new_peringkat_akreditasi)
                                    <span class="text-muted text-decoration-line-through">{{ $change->old_peringkat_akreditasi ?? '-' }}</span>
                                    <i class="bi bi-arrow-right text-primary mx-1"></i>
                                    <strong class="text-primary">{{ $change->new_peringkat_akreditasi ?? '-' }}</strong>
                                @else
                                    {{ $change->new_peringkat_akreditasi ?? '-' }}
                                @endif
                            </td>
                            <td>
                                @php
                                    $oldTgl = $change->old_tanggal_kedaluwarsa?->format('d/m/Y');
                                    $newTgl = $change->new_tanggal_kedaluwarsa?->format('d/m/Y');
                                @endphp
                                @if($oldTgl !== $newTgl)
                                    <span class="text-muted text-decoration-line-through">{{ $oldTgl ?? '-' }}</span>
                                    <i class="bi bi-arrow-right text-primary mx-1"></i>
                                    <strong class="text-primary">{{ $newTgl ?? '-' }}</strong>
                                @else
                                    {{ $newTgl ?? '-' }}
                                @endif
                            </td>
                            <td>
                                @php $statusBadge = match($change->new_status_kedaluwarsa) {
                                    'Aktif' => 'success',
                                    'Kedaluwarsa' => 'danger',
                                    default => 'secondary'
                                }; @endphp
                                <span class="badge bg-{{ $statusBadge }}">{{ $change->new_status_kedaluwarsa ?? '-' }}</span>
                            </td>
                            <td><small>{{ $change->detected_at->format('d/m/Y H:i') }}</small></td>
                            <td>
                                <span class="badge bg-{{ $change->status_badge_class }}">{{ $change->status_label }}</span>
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-nowrap">
                                    <a href="{{ route('admin.monitoring-banpt.show', $change) }}"
                                        class="btn btn-sm btn-outline-secondary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($change->isPending())
                                    <form method="POST" action="{{ route('admin.monitoring-banpt.apply', $change) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success"
                                            title="Terapkan"
                                            onclick="return confirm('Terapkan perubahan ini ke data program studi?')">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.monitoring-banpt.ignore', $change) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning"
                                            title="Abaikan"
                                            onclick="return confirm('Abaikan perubahan ini?')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Tandai Konflik"
                                        data-bs-toggle="modal" data-bs-target="#conflictModal{{ $change->id }}">
                                        <i class="bi bi-flag"></i>
                                    </button>
                                    @endif
                                </div>

                                {{-- Modal Konflik --}}
                                @if($change->isPending())
                                <div class="modal fade" id="conflictModal{{ $change->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.monitoring-banpt.conflict', $change) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Tandai Konflik</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <label class="form-label">Alasan konflik (opsional)</label>
                                                    <textarea name="conflict_reason" class="form-control" rows="3"
                                                        placeholder="Tuliskan alasan jika ada..."></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger">Tandai Konflik</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Tidak ada data untuk tab ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($changes->hasPages())
        <div class="card-footer">
            {{ $changes->appends(['tab' => $tab])->links() }}
        </div>
        @endif
    </div>
    @endif

    {{-- Riwayat Sync --}}
    @if($tab === 'runs' && $syncRuns !== null)
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Dimulai</th>
                            <th>Selesai</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th>Diperiksa</th>
                            <th>Perubahan</th>
                            <th>Error</th>
                            <th>Pesan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($syncRuns as $run)
                        <tr>
                            <td>{{ $run->id }}</td>
                            <td><small>{{ $run->started_at?->format('d/m/Y H:i:s') ?? '-' }}</small></td>
                            <td><small>{{ $run->finished_at?->format('d/m/Y H:i:s') ?? '-' }}</small></td>
                            <td>{{ $run->duration ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $run->status === 'success' ? 'success' : ($run->status === 'running' ? 'warning' : 'danger') }}">
                                    {{ $run->status }}
                                </span>
                            </td>
                            <td>{{ $run->checked_count }}</td>
                            <td>{{ $run->changed_count }}</td>
                            <td>
                                @if($run->error_count > 0)
                                    <span class="badge bg-danger">{{ $run->error_count }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td><small>{{ $run->message ?? '-' }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Belum ada riwayat sinkronisasi.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($syncRuns->hasPages())
        <div class="card-footer">{{ $syncRuns->links() }}</div>
        @endif
    </div>
    @endif

</div>
@endsection
