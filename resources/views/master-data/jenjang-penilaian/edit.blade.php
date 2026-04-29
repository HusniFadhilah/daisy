@extends('layouts.template.app')

@section('title', 'Edit Jenjang Penilaian')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Edit Jenjang Penilaian</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('jenjang-penilaian.update', $jenjang->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('master-data.jenjang-penilaian.partials.form', ['jenjang' => $jenjang])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('jenjang-penilaian.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
