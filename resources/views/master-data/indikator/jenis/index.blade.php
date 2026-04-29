@extends('layouts.template.app')

@section('title', 'Jenis Indikator')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Jenis Indikator</h2>
        <a href="{{ route('jenis-indikator.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Jenis
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nama Jenis</th>
                            <th>Keterangan</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jenisIndikators as $jenis)
                        <tr>
                            <td>{{ $jenis->nama_jenis }}</td>
                            <td>{{ $jenis->keterangan ?? '-' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('jenis-indikator.edit', $jenis->id) }}" class="btn btn-sm btn-warning text-white">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('jenis-indikator.destroy', $jenis->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger tombol-hapus" data-text="jenis indikator">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Belum ada data jenis indikator</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
