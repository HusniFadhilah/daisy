@foreach($timeline as $period)
<div class="card timeline-card {{ $period['is_urgent'] ? 'timeline-urgent' : 'timeline-normal' }}">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <h6 class="fw-bold mb-0">{{ $period['label'] }}</h6>
            </div>
            @if($period['is_urgent'])
            <span class="badge bg-danger">
                <i class="bi bi-exclamation-triangle"></i> Urgent
            </span>
            @endif
        </div>
        <small class="text-muted">
            {{ \App\Libraries\Date::tglIndo($period['start_date'],false) }} - {{ \App\Libraries\Date::tglIndo($period['end_date'],false) }}
        </small>

        <div class="text-center my-3">
            <h2 class="mb-0 fw-bold {{ $period['count'] > 0 ? 'text-danger' : 'text-success' }}">
                {{ $period['count'] }}
            </h2>
            <small class="text-muted">Program Studi</small>
        </div>

        @if($period['count'] > 0)
        <button class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#periodModal{{ $period['period'] }}">
            <i class="bi bi-eye"></i> Lihat Detail
        </button>
        @else
        <div class="alert alert-success py-2 mb-0">
            <small><i class="bi bi-check-circle"></i> Tidak ada yang kedaluwarsa</small>
        </div>
        @endif
    </div>
</div>

<!-- Modal for Period Details -->
<div class="modal fade" id="periodModal{{ $period['period'] }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-list"></i> Program Studi - {{ $period['label'] }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">
                    Periode: {{ \App\Libraries\Date::tglIndo($period['start_date']) }} - {{ \App\Libraries\Date::tglIndo($period['end_date']) }}
                </p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Program Studi</th>
                                <th>Universitas</th>
                                <th>Status Akreditasi</th>
                                <th>Kedaluwarsa</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($period['programs'] as $index => $prog)
                            <tr>
                                <td>{{ $index + 1 }}</td>
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
                                    <small class="text-danger">
                                        {{ floor(now()->diffInDays($prog->tanggal_kedaluwarsa)) }} hari lagi
                                    </small>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#modalKirimPengingat" data-id-study-program="{{ $prog->id }}">
                                        <i class="bi bi-bell"></i> Kirim Pengingat
                                    </button>
                                    <a href="{{ route('de.pemetaan.show', $prog->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Detail
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
</div>
@endforeach
