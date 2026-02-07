{{-- resources/views/asesmen/pemetaan/components/reminder-detail-table.blade.php --}}

<div class="accordion mb-3" id="accordionFilterReminder">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFilter">
            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                <i class="bi bi-funnel me-2"></i> Filter Pencarian
            </button>
        </h2>

        <div id="collapseFilter" class="accordion-collapse collapse" aria-labelledby="headingFilter" data-bs-parent="#accordionFilterReminder">
            <div class="accordion-body bg-light">
                <div class="row g-2">
                    {{-- Search --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">
                            Cari Prodi / Universitas
                        </label>
                        <input type="text" class="form-control form-control-sm" id="reminderSearchInput" placeholder="Ketik nama prodi atau universitas...">
                    </div>

                    {{-- Peringkat --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">
                            Peringkat Akreditasi
                        </label>
                        <select class="form-select form-select-sm select2" id="reminderPeringkatFilter" multiple>
                            <option value="Unggul">Unggul</option>
                            <option value="Baik Sekali">Baik Sekali</option>
                            <option value="Baik">Baik</option>
                            <option value="C">C</option>
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">
                            Status Akreditasi
                        </label>
                        <select class="form-select form-select-sm select2" id="reminderStatusFilter" multiple>
                            <option value="Aktif">Aktif</option>
                            <option value="Kedaluwarsa">Kedaluwarsa</option>
                            <option value="Belum Terakreditasi">Belum Terakreditasi</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button class="btn btn-primary btn-sm" onclick="applyReminderFilters()">
                        <i class="bi bi-search"></i> Terapkan Filter
                    </button>

                    <button class="btn btn-secondary btn-sm" onclick="resetReminderFilters()">
                        <i class="bi bi-x-circle"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h6 class="fw-bold mb-0">Program Studi - {{ $label }}</h6>
        <small class="text-muted">
            Periode: {{ \App\Libraries\Date::tglIndo($start) }} - {{ \App\Libraries\Date::tglIndo($end) }}
            • Target: {{ $targetMonths }} bulan dari sekarang
            • Window: {{ $windowMonths }} bulan
        </small>
    </div>
    <span class="badge bg-primary">{{ $programs->total() }} PS</span>
</div>

<div class="table-responsive">
    <table class="table table-hover datatable">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Program Studi</th>
                <th>Universitas</th>
                <th>Peringkat</th>
                <th>Kedaluwarsa</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($programs as $index => $prog)
            <tr>
                <td>{{ $programs->firstItem() + $index }}</td>
                <td>
                    <div class="fw-bold">{{ $prog->name }}</div>
                    <small class="text-muted">{{ $prog->degreeLevel->alias }}</small>
                </td>
                <td>{{ $prog->university->name }}</td>
                <td>
                    @if($prog->peringkat_akreditasi)
                    <span class="badge bg-primary">{{ $prog->peringkat_akreditasi }}</span>
                    @else
                    -
                    @endif
                </td>
                <td>
                    {{ \App\Libraries\Date::tglIndo($prog->tanggal_kedaluwarsa) }}
                    <br>
                    <small class="{{ now()->diffInDays($prog->tanggal_kedaluwarsa, false) < 0 ? 'text-muted' : 'text-danger' }}">
                        @php
                        $diff = now()->diff($prog->tanggal_kedaluwarsa);
                        @endphp

                        {{ $diff->y }} tahun {{ $diff->m }} bulan {{ $diff->d }} hari
                    </small>
                </td>
                <td>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalKirimPengingat" data-id-study-program="{{ $prog->id }}" data-text-study-program="{{ $prog->full_name ?? $prog->name }}">
                        <i class="bi bi-bell"></i> Kirim Pengingat
                    </button>

                    <a href="{{ route('de.pemetaan.show', $prog->id) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye"></i> Detail
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    Tidak ada program studi pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Custom Pagination --}}
@if($programs->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted small">
        Showing {{ $programs->firstItem() }} to {{ $programs->lastItem() }} of {{ $programs->total() }} results
    </div>

    <nav aria-label="Page navigation">
        <ul class="pagination pagination-sm mb-0">
            {{-- Previous Page Link --}}
            @if ($programs->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link">« Sebelumnya</span>
            </li>
            @else
            <li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="loadReminderDetail({{ $programs->currentPage() - 1 }})">
                    « Sebelumnya
                </a>
            </li>
            @endif

            {{-- Pagination Elements --}}
            @php
            $start = max(1, $programs->currentPage() - 2);
            $end = min($programs->lastPage(), $programs->currentPage() + 2);
            @endphp

            @if($start > 1)
            <li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="loadReminderDetail(1)">1</a>
            </li>
            @if($start > 2)
            <li class="page-item disabled"><span class="page-link">...</span></li>
            @endif
            @endif

            @for ($i = $start; $i <= $end; $i++) @if ($i==$programs->currentPage())
                <li class="page-item active" aria-current="page">
                    <span class="page-link">{{ $i }}</span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="javascript:void(0)" onclick="loadReminderDetail({{ $i }})">
                        {{ $i }}
                    </a>
                </li>
                @endif
                @endfor

                @if($end < $programs->lastPage())
                    @if($end < $programs->lastPage() - 1)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="javascript:void(0)" onclick="loadReminderDetail({{ $programs->lastPage() }})">
                                {{ $programs->lastPage() }}
                            </a>
                        </li>
                        @endif

                        {{-- Next Page Link --}}
                        @if ($programs->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="javascript:void(0)" onclick="loadReminderDetail({{ $programs->currentPage() + 1 }})">
                                Berikutnya »
                            </a>
                        </li>
                        @else
                        <li class="page-item disabled">
                            <span class="page-link">Berikutnya »</span>
                        </li>
                        @endif
        </ul>
    </nav>
</div>
@endif
