@extends('layouts.template.app')

@section('title', 'Data Universitas - Daisy')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Kelola Universitas</h2>
        <a href="{{ route('universities.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Universitas
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
                <table class="table table-hover" id="universitiesTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Universitas</th>
                            <th>Jumlah Prodi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($universities as $university)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $university->code }}</td>
                            <td>{{ $university->name }}</td>
                            <td>{{ $university->studyPrograms->count() }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('universities.edit', $university->id) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('universities.destroy', $university->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus universitas ini?')">
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
                            <td colspan="5" class="text-center">Belum ada data universitas</td>
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
        $('#universitiesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            order: [[2, 'asc']]
        });
    });
</script>
@endpush
@endsection
