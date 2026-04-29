@extends('layouts.template.app')

@section('title', 'Jenjang Penilaian')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Jenjang Penilaian</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('jenjang-penilaian.sync-confirmation') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-repeat"></i> Sinkronisasi Hasil
            </a>
            <a href="{{ route('jenjang-penilaian.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Jenjang
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="10%">Skor</th>
                            <th>Nama</th>
                            <th width="15%">Warna</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jenjangPenilaians as $jenjang)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $jenjang->skor }}</span></td>
                            <td>{{ $jenjang->name }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ $jenjang->color }}; color: #000;">
                                    {{ $jenjang->color }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('jenjang-penilaian.edit', $jenjang->id) }}" class="btn btn-sm btn-warning text-white">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('jenjang-penilaian.destroy', $jenjang->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger tombol-hapus" data-text="jenjang penilaian">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada data jenjang penilaian</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
