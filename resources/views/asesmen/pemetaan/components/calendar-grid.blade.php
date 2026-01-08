@foreach($calendarData as $month)
<div class="calendar-month {{ $month['count'] > 0 ? 'has-expiring' : 'no-expiring' }}" data-bs-toggle="modal" data-bs-target="#monthModal{{ $month['month_num'] }}{{ $month['year'] }}">
    <div class="fw-bold">{{ $month['month'] }}</div>
    <div class="calendar-count {{ $month['count'] > 0 ? 'text-danger' : 'text-success' }}">
        {{ $month['count'] }}
    </div>
    <small class="text-muted">
        {{ $month['count'] > 0 ? 'Prodi Kedaluwarsa' : 'Tidak Ada' }}
    </small>
</div>

<!-- Modal for Month Details -->
@if($month['count'] > 0)
<div class="modal fade" id="monthModal{{ $month['month_num'] }}{{ $month['year'] }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-event"></i> Program Studi Kedaluwarsa - {{ $month['month'] }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    @foreach($month['programs'] as $prog)
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold mb-1">{{ $prog->name }}</h6>
                                <p class="mb-1 text-muted">
                                    {{ $prog->university->name }} - {{ $prog->degreeLevel->alias }}
                                </p>
                                <small class="text-danger">
                                    <i class="bi bi-calendar-x"></i>
                                    Kedaluwarsa: {{ $prog->tanggal_kedaluwarsa->format('d F Y') }}
                                </small>
                            </div>
                            <div>
                                @if($prog->peringkat_akreditasi)
                                <span class="badge bg-primary mb-2">{{ $prog->peringkat_akreditasi }}</span><br>
                                @endif
                                <a href="{{ route('pemetaan.show', $prog->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach
