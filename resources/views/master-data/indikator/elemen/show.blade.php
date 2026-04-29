@extends('layouts.template.app')

@section('title', 'Detail Elemen Standar')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detail Elemen Standar</h5>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Kriteria</dt>
                <dd class="col-sm-9">{{ $elemenStandar->kriteria->kode_kriteria ?? '-' }} - {{ $elemenStandar->kriteria->nama_kriteria ?? '-' }}</dd>
                <dt class="col-sm-3">Kode Elemen</dt>
                <dd class="col-sm-9">{{ $elemenStandar->kode_elemen }}</dd>
                <dt class="col-sm-3">Pernyataan Elemen</dt>
                <dd class="col-sm-9">{{ $elemenStandar->pernyataan_elemen }}</dd>
                <dt class="col-sm-3">Keterangan</dt>
                <dd class="col-sm-9">{{ $elemenStandar->keterangan ?? '-' }}</dd>
            </dl>
            <a href="{{ route('elemen-standar.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
@endsection
