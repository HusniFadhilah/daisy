@extends('layouts.template.app')

@section('title', 'Detail Kriteria')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detail Kriteria</h5>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Kode</dt>
                <dd class="col-sm-9">{{ $kriteria->kode_kriteria }}</dd>
                <dt class="col-sm-3">Nama</dt>
                <dd class="col-sm-9">{{ $kriteria->nama_kriteria }}</dd>
                <dt class="col-sm-3">Keterangan</dt>
                <dd class="col-sm-9">{{ $kriteria->keterangan ?? '-' }}</dd>
            </dl>
            <a href="{{ route('kriteria.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
@endsection
