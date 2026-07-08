{{-- resources/views/asesmen/pemetaan/components/reminder-detail-table.blade.php --}}
@push('styles')
<style>
    /* ✅ Modal Reminder Scrollability - FORCE */
    #reminderModal .modal-body {
        max-height: 80vh !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        -webkit-overflow-scrolling: touch !important;
    }

    #reminderModal .modal-dialog {
        max-height: 95vh !important;
    }

    #reminderModal .modal-content {
        max-height: 95vh !important;
        display: flex !important;
        flex-direction: column !important;
    }

    #reminderModal .modal-header {
        flex-shrink: 0 !important;
    }

    #reminderModal .modal-footer {
        flex-shrink: 0 !important;
    }

    /* Pastikan tidak ada overflow hidden dari parent */
    #reminderModal .modal-dialog,
    #reminderModal .modal-content,
    #reminderModal {
        overflow: visible !important;
    }

    /* Pastikan Select2 tidak menghalangi */
    .select2-container--open {
        z-index: 1056 !important;
    }

    .select2-dropdown {
        z-index: 1056 !important;
    }

    /* Fix untuk backdrop yang mungkin menghalangi */
    .modal-backdrop {
        z-index: 1050 !important;
    }

</style>
@endpush


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
    <table class="table table-hover align-middle datatable">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Program Studi</th>
                <th>Universitas</th>
                <th>Status Akreditasi</th>
                <th>Nomor SK</th>
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
                    @if(!$prog->is_active)
                    <br>
                    <small class="text-muted">Prodi tidak aktif</small>
                    @endif
                </td>
                <td>{{ $prog->no_sk ?: '-' }}</td>
                <td>
                    @if($prog->tanggal_kedaluwarsa)
                    {{ \App\Libraries\Date::tglIndo($prog->tanggal_kedaluwarsa) }}
                    <br>
                    <small class="{{ now()->diffInDays($prog->tanggal_kedaluwarsa, false) < 0 ? 'text-muted' : 'text-danger' }}">
                        @php
                        $diff = now()->diff($prog->tanggal_kedaluwarsa);
                        @endphp

                        {{ $diff->y }} tahun {{ $diff->m }} bulan {{ $diff->d }} hari
                    </small>
                    @else
                    <span class="text-muted">-</span>
                    <br>
                    <small class="text-muted">Tanggal belum tersedia</small>
                    @endif
                </td>
                <td>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalKirimPengingat" data-id-study-program="{{ $prog->id }}" data-text-study-program="{{ $prog->full_name ?? $prog->name }}">
                        <i class="bi bi-bell"></i> Kirim Pengingat
                    </button>

                    <a href="{{ route('de.pemetaan.show', $prog->id) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye"></i> Detail
                    </a>
                    @php
                    $countPengingat = $prog->pengingatAkreditasi->count();
                    @endphp
                    @if($countPengingat > 0)
                    <br><small>Telah diingatkan {{ $countPengingat }} kali</small>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
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
