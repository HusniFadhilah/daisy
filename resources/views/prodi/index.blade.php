@extends('layouts.template.app')

@section('title', 'Data Program Studi - Daisy')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Kelola Program Studi</h2>
        <a href="{{ route('study-programs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Program Studi
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="studyProgramsTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Program Studi</th>
                            <th>Jenjang</th>
                            <th>Universitas</th>
                            <th>Email</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studyPrograms as $program)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $program->code }}</td>
                            <td>{{ $program->name }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $program->degreeLevel->name ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $program->university->name ?? '-' }}</td>
                            <td>{{ $program->email ?? '-' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('study-programs.edit', $program->id) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('study-programs.destroy', $program->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus program studi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data program studi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#studyProgramsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            order: [[2, 'asc']]
        });
    });
</script>
@endpush
@endsection
