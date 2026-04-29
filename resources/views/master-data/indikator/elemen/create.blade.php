@extends('layouts.template.app')

@section('title', 'Tambah Elemen Standar')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Tambah Elemen Standar</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('elemen-standar.store') }}" method="POST">
                @csrf
                @include('master-data.indikator.elemen.partials.form')
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('elemen-standar.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
