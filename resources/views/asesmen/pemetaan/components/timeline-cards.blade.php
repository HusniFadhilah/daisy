@foreach($timeline as $period)
<div class="card timeline-card {{ $period['is_urgent'] ? 'timeline-urgent' : 'timeline-normal' }}">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <h6 class="fw-bold mb-1">{{ $period['label'] }}</h6>
                <small class="text-muted">
                    {{ $period['start_date']->format('d M') }} - {{ $period['end_date']->format('d M Y') }}
                </small>
            </div>
            @if($period['is_urgent'])
            <span class="badge bg-danger">
                <i class="bi bi-exclamation-triangle"></i> Urgent
            </span>
            @endif
        </div>

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
                    Periode: {{ $period['start_date']->format('d M Y') }} - {{ $period['end_date']->format('d M Y') }}
                </p>
                <div class="table-responsive">
                    <table class="table table-hover">
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
                            @foreach($period['programs'] as $index => $prog)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold">{{ $prog->name }}</div>
                                    <small class="text-muted">{{ $prog->degreeLevel->code }}</small>
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
                                    {{ $prog->tanggal_kedaluwarsa->format('d M Y') }}
                                    <br>
                                    <small class="text-danger">
                                        {{ floor(now()->diffInDays($prog->tanggal_kedaluwarsa)) }} hari lagi
                                    </small>
                                </td>
                                <td>
                                    <a href="{{ route('pemetaan.show', $prog->id) }}" class="btn btn-sm btn-primary">
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
</div>
@endforeach
