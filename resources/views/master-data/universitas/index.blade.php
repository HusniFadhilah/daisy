@extends('layouts.template.app')

@section('title', 'Data Universitas - Daisy LAMDEPILAR')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <h2>Kelola Universitas</h2>
        <a href="{{ route('universities.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Tambah Universitas
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="universitiesTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Universitas</th>
                            <th>Email</th>
                            <th>Jumlah Prodi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($universities as $university)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $university->code }}</td>
                            <td>
                                <a href="{{ route('universities.show', $university->id) }}">
                                    {{ $university->name }}
                                </a>
                            </td>
                            <td>{{ $university->email ?? '-' }}</td>
                            <td>{{ $university->studyPrograms->count() }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('universities.show', $university->id) }}" class="btn btn-sm btn-info text-white" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('universities.edit', $university->id) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form id="form-universitas-{{ $university->id }}" action="{{ route('universities.destroy', $university->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger tombol-hapus" data-id-form="form-universitas-{{ $university->id }}" data-text="universitas" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data universitas</td>
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
            }
            , order: [
                [2, 'asc']
            ]
        });
    });

</script>
@endpush
@endsection
