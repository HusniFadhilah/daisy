@extends('layouts.template.app')

@section('title', 'Univ & Prodi - Daisy')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Univ & Prodi</h2>
        <p class="text-muted">Kelola data universitas dan program studi</p>
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

    <!-- Nav Tabs -->
    <ul class="nav nav-tabs mb-4" id="masterDataTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $tab == 'universities' ? 'active' : '' }}" 
                    id="universities-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#universities" 
                    type="button" 
                    role="tab">
                <i class="bi bi-building"></i> Universitas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $tab == 'study-programs' ? 'active' : '' }}" 
                    id="study-programs-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#study-programs" 
                    type="button" 
                    role="tab">
                <i class="bi bi-mortarboard"></i> Program Studi
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="masterDataTabContent">
        <!-- Universities Tab -->
        <div class="tab-pane fade {{ $tab == 'universities' ? 'show active' : '' }}" 
             id="universities" 
             role="tabpanel">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Daftar Universitas</h4>
                <a href="{{ route('universities.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Universitas
                </a>
            </div>

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
                                    <td>
                                        <span class="badge bg-info">{{ $university->study_programs_count }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('universities.edit', $university->id) }}" 
                                               class="btn btn-sm btn-warning text-white" 
                                               title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('universities.destroy', $university->id) }}" 
                                                  method="POST" 
                                                  class="d-inline" 
                                                  onsubmit="return confirm('Yakin ingin menghapus universitas ini?')">
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

        <!-- Study Programs Tab -->
        <div class="tab-pane fade {{ $tab == 'study-programs' ? 'show active' : '' }}" 
             id="study-programs" 
             role="tabpanel">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Daftar Program Studi</h4>
                <a href="{{ route('study-programs.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Program Studi
                </a>
            </div>

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
                                    <th>Peringkat</th>
                                    <th>Status</th>
                                    <th>Tanggal Kadaluarsa</th>
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
                                    <td>
                                        @if($program->peringkat_akreditasi)
                                            @php
                                                $badgeColor = match(strtolower($program->peringkat_akreditasi)) {
                                                    'unggul', 'a' => 'success',
                                                    'baik sekali', 'b' => 'primary',
                                                    'baik' => 'warning',
                                                    'c' => 'secondary',
                                                    default => 'info'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $badgeColor }}">
                                                {{ $program->peringkat_akreditasi }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($program->status_kadaluarsa)
                                            @php
                                                $statusLower = strtolower($program->status_kadaluarsa);
                                                $badgeColor = 'secondary';
                                                if (str_contains($statusLower, 'berlaku')) {
                                                    $badgeColor = 'success';
                                                } elseif (str_contains($statusLower, 'kadaluarsa') || str_contains($statusLower, 'hari lagi')) {
                                                    $badgeColor = 'warning';
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $badgeColor }}">{{ $program->status_kadaluarsa }}</span>
                                        @else
                                            <span class="badge bg-secondary">Belum Terakreditasi</span>
                                        @endif
                                    </td>
                                    <td>{{ $program->tanggal_kadaluarsa ? $program->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('study-programs.edit', $program->id) }}" 
                                               class="btn btn-sm btn-warning text-white" 
                                               title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('study-programs.destroy', $program->id) }}" 
                                                  method="POST" 
                                                  class="d-inline" 
                                                  onsubmit="return confirm('Yakin ingin menghapus program studi ini?')">
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
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTables
        $('#universitiesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            order: [[2, 'asc']],
            pageLength: 25
        });

        $('#studyProgramsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            order: [[2, 'asc']],
            pageLength: 25
        });

        // Handle tab change to update URL
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr('data-bs-target');
            var tabName = target.replace('#', '');
            
            // Update URL without page reload
            var url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
        });
    });
</script>
@endpush
@endsection
