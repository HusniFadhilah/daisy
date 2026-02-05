@extends('layouts.template.app')

@section('title', 'Validasi AK')

@push('styles')
<style>
    .filter-section {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .search-box {
        position: relative;
    }

    .search-box .bi-search {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }

    .search-box input {
        padding-left: 36px;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="welcome-section mb-4">
        <div class="welcome-content">
            <h2>
                <i class="bi bi-check2-square text-white"></i>
                Validasi AK
            </h2>
            <p class="mb-0">Validasi penilaian asesor untuk Asesmen Kecukupan (AK)</p>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}" class="text-link">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
            </li>
            <li class="breadcrumb-item active">Validasi AK</li>
        </ol>
    </nav>

    <!-- Stats Cards (template pelaporan style) -->
    <div class="row mb-4 row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
        <div class="col">
            <x-stat-card title="Asesor Sudah Submit" :value="$stats['total_asesor_submitted']" icon="people" mode="white" description="" color="warning" />
        </div>
        <div class="col">
            <x-stat-card title="Asesor Belum Submit" :value="$stats['total_asesor_pending']" icon="exclamation-triangle" mode="white" description="" color="danger" />
        </div>
        <div class="col">
            <x-stat-card title="Asesmen Aktif" :value="$stats['total_needs_validation']" icon="clipboard-check" mode="white" description="" color="primary" />
        </div>
        <div class="col">
            <x-stat-card title="Selesai Divalidasi" :value="$stats['total_validated']" icon="check-circle" mode="white" description="" color="success" />
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-check"></i> Daftar Validasi AK
                </h5>
                <small class="text-muted">
                    Total: {{ count($needsValidation) + count($validated) }}
                </small>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="35%">Asesmen</th>
                            <th width="20%">Program Studi</th>
                            <th width="20%">Status Validasi</th>
                            <th width="15%">Tanggal Validasi</th>
                            <th width="5%" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php $no = 1; @endphp

                        {{-- ================= BELUM DIVALIDASI ================= --}}
                        @foreach($needsValidation as $item)
                        @php
                        $asesmen = $item['asesmen'];

                        $prodi = '-';
                        $univ = '-';
                        if ($asesmen->studyProgram) {
                        $prodi = $asesmen->studyProgram->full_name;
                        if ($asesmen->studyProgram->university) {
                        $univ = $asesmen->studyProgram->university->name;
                        }
                        }

                        $hasMinAsesor = $item['total_accepted_asesors'] >= 2;
                        $allSubmitted = $item['asesors_pending']->isEmpty() && $item['asesors']->count() >= 2;
                        @endphp

                        <tr>
                            <td>{{ $no++ }}</td>

                            <td>
                                <p class="mb-0">{{ $asesmen->name }}</p>
                                <small class="text-muted">
                                    Kode Panel:
                                    {{ isset($asesmen->kode_panel) && $asesmen->kode_panel ? $asesmen->kode_panel : '-' }}
                                </small>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark">{{ $asesmen->studyProgram->name ?? '-' }}</span>
                                <small class="text-muted small d-block">{{ $asesmen->studyProgram->university->name ?? '-' }}</small>
                            </td>

                            <td>
                                @if(!$hasMinAsesor)
                                <span class="badge bg-secondary">
                                    <i class="bi bi-exclamation-triangle"></i> Kurang Asesor
                                </span>
                                @elseif(!$allSubmitted)
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock"></i> Menunggu Asesor Submit
                                </span>
                                @else
                                <span class="badge bg-primary">
                                    <i class="bi bi-check2-square"></i> Siap Divalidasi
                                </span>
                                @endif
                            </td>

                            <td>
                                <span class="text-muted">-</span>
                            </td>

                            <td class="text-center">
                                @if($hasMinAsesor && $allSubmitted)
                                <a href="{{ route('ak.validasi.asesor', ['idAsesmen' => $asesmen->id, 'jenisAsesmen' => 'ak']) }}" class="btn btn-sm btn-primary" title="Validasi">
                                    <i class="bi bi-check2-square"></i>
                                </a>
                                @else
                                <button class="btn btn-sm btn-secondary" disabled>
                                    <i class="bi bi-lock"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach

                        {{-- ================= SUDAH DIVALIDASI ================= --}}
                        @foreach($validated as $item)
                        @php
                        $asesmen = $item['asesmen'];

                        $prodi = '-';
                        $univ = '-';
                        if ($asesmen->studyProgram) {
                        $prodi = $asesmen->studyProgram->full_name;
                        if ($asesmen->studyProgram->university) {
                        $univ = $asesmen->studyProgram->university->name;
                        }
                        }

                        $approvedAt = '-';
                        if ($item['assignment'] && $item['assignment']->approved_at) {
                        $approvedAt = \App\Libraries\Date::tglIndo($item['assignment']->approved_at);
                        }
                        @endphp

                        <tr>
                            <td>{{ $no++ }}</td>

                            <td>
                                <p class="mb-0">{{ $asesmen->name }}</p>
                                <small class="text-muted">
                                    Kode Panel:
                                    {{ isset($asesmen->kode_panel) && $asesmen->kode_panel ? $asesmen->kode_panel : '-' }}
                                </small>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark">{{ $asesmen->studyProgram->name ?? '-' }}</span>
                                <small class="text-muted small d-block">{{ $asesmen->studyProgram->university->name ?? '-' }}</small>
                            </td>

                            <td>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Telah Divalidasi
                                </span>
                            </td>

                            <td>
                                <small>{{ $approvedAt }}</small>
                            </td>

                            <td class="text-center">
                                <a href="{{ route('ak.validasi.asesor', ['idAsesmen' => $asesmen->id, 'jenisAsesmen' => 'ak']) }}" class="btn btn-sm btn-outline-success" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Asesor yang Sudah Submit (tetap dipakai dari file lama) -->
<div class="modal fade" id="modalAsesorSubmitted" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-people-fill"></i> Daftar Asesor yang Sudah Submit
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @foreach($needsValidation as $item)
                @if($item['asesors']->count() > 0)
                <h6 class="fw-bold mb-2">{{ $item['asesmen']->name }}</h6>
                @foreach($item['asesors'] as $asesor)
                <div class="p-2 mb-2 border rounded" style="background:#fff9e6;border-color:#ffc107;">
                    <strong>{{ $asesor->user->name }}</strong>
                    <br><small class="text-muted d-block text-break">{{ $asesor->user->email }}</small>
                </div>
                @endforeach
                <hr>
                @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal: Asesor yang Belum Submit (tetap dipakai dari file lama) -->
<div class="modal fade" id="modalAsesorPending" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill"></i> Daftar Asesor yang Belum Submit
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @forelse($asesorsPendingList as $pending)
                <div class="p-2 mb-2 border rounded" style="background:#fff3cd;border-color:#ffc107;">
                    <strong>{{ $pending['asesor']->name }}</strong>
                    <br><small class="text-muted">{{ $pending['asesor']->email }}</small>
                    <br><small><strong>Asesmen:</strong> {{ $pending['asesmen'] }}</small>
                </div>
                @empty
                <p class="text-center text-muted mb-0">Semua asesor sudah submit! 🎉</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('searchInput');
        var filterStatus = document.getElementById('filterStatus');
        var resetBtn = document.getElementById('resetFilter');

        var tbody = document.getElementById('needsValidationTbody');
        var noResults = document.getElementById('noResults');

        if (!tbody) return;

        var rows = Array.prototype.slice.call(tbody.querySelectorAll('.js-need-row'));

        function applyFilters() {
            var searchTerm = searchInput.value.toLowerCase();
            var statusFilter = filterStatus.value;

            var visible = rows.filter(function(row) {
                var hay = row.dataset.search || '';
                var status = row.dataset.status || '';

                var matchSearch = !searchTerm || hay.indexOf(searchTerm) !== -1;
                var matchStatus = !statusFilter || status === statusFilter;

                return matchSearch && matchStatus;
            });

            rows.forEach(function(r) {
                r.classList.add('d-none');
            });

            if (visible.length > 0) {
                visible.forEach(function(r) {
                    r.classList.remove('d-none');
                });
                noResults.classList.add('d-none');
            } else {
                noResults.classList.remove('d-none');
            }
        }

        searchInput.addEventListener('input', applyFilters);
        filterStatus.addEventListener('change', applyFilters);

        resetBtn.addEventListener('click', function() {
            searchInput.value = '';
            filterStatus.value = '';
            applyFilters();
        });
    });

</script>
@endpush
