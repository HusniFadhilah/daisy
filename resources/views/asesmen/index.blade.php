@extends('layouts.template.app')

@section('title', 'Kelola Asesmen - Admin')


@push('styles')
<style>
    .table-responsive {
        max-height: 600px;
        overflow-y: auto;
    }

    .table thead {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .btn-group-sm>.btn {
        padding: 0.375rem 0.5rem;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-clipboard-data"></i> Kelola Asesmen</h2>
            <p class="text-muted mb-0">Atur asesmen akreditasi dan tugaskan asesor</p>
        </div>
        <a href="{{ route('asesmen.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Buat Asesmen Baru
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('asesmen.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari Asesmen</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama asesmen, perguruan tinggi..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif (Ada Asesor)</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block w-100">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <a href="{{ route('asesmen.index') }}" class="btn btn-secondary d-block w-100">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-outline-secondary d-block w-100" data-bs-toggle="tooltip" title="Download Data Excel">
                        <i class="bi bi-file-earmark-excel"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Asesmen Table -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Daftar Asesmen ({{ $asesmens->total() }})</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Nama Asesmen</th>
                            <th>Perguruan Tinggi</th>
                            <th style="width: 100px;">Kode Panel</th>
                            <th style="width: 120px;">Jumlah Peran</th>
                            <th style="width: 150px;">Tanggal</th>
                            <th style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asesmens as $index => $asesmen)
                        <tr>
                            <td>{{ $asesmens->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-semibold">{{ $asesmen->name }}</div>
                                @if($asesmen->description)
                                <small class="text-muted">{{ Str::limit($asesmen->description, 60) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($asesmen->studyProgram)
                                {{ $asesmen->studyProgram->university->name ?? '-' }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $asesmen->kode_panel ?? 'N/A' }}</span>
                            </td>
                            <td class="text-center">
                                @if($asesmen->user_roles_count > 0)
                                <span class="badge bg-success" style="font-size: 14px;">
                                    <i class="bi bi-people-fill"></i> {{ $asesmen->user_roles_count }}
                                </span>
                                @else
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-exclamation-triangle"></i> Belum Ada
                                </span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> {{ \App\Libraries\Date::tglIndo($asesmen->created_at) }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('asesmen.show', $asesmen->id) }}" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Detail & Tugaskan Asesor">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('asesmen.edit', $asesmen->id) }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Hapus" onclick="confirmDelete({{ $asesmen->id }}, '{{ $asesmen->name }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="text-muted mt-3">Belum ada asesmen. Klik "Buat Asesmen Baru" untuk memulai.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($asesmens->hasPages())
        <div class="card-footer bg-white">
            {{ $asesmens->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Confirm delete
    function confirmDelete(id, name) {
        if (confirm(`Apakah Anda yakin ingin menghapus asesmen "${name}"?\n\nPerhatian: Asesmen yang sudah ada penilaian tidak bisa dihapus.`)) {
            const form = document.getElementById('deleteForm');
            form.action = `/asesmen/${id}`;
            form.submit();
        }
    }

</script>
@endpush

@endsection
