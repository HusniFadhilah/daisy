@extends('layouts.template.app')

@section('title', 'Detail Validasi Dokumen')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="bi bi-clipboard-check"></i> Detail Validasi Dokumen
                    </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('de.validasi-dokumen') }}">Validasi Dokumen</a>
                            </li>
                            <li class="breadcrumb-item active">{{ $pengajuan->nomor_pengajuan }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('de.validasi-dokumen') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Progress Summary -->
    @if($progress)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Progress Validasi</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="text-muted small">LED</label>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-{{ $progress['led_percentage'] >= 80 ? 'success' : 'warning' }}" style="width: {{ $progress['led_percentage'] }}%">
                                    {{ $progress['led_percentage'] }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Suplemen</label>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-{{ $progress['suplemen_percentage'] >= 80 ? 'success' : 'warning' }}" style="width: {{ $progress['suplemen_percentage'] }}%">
                                    {{ $progress['suplemen_percentage'] }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">LKPS</label>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-{{ $progress['lkps_percentage'] >= 80 ? 'success' : 'warning' }}" style="width: {{ $progress['lkps_percentage'] }}%">
                                    {{ $progress['lkps_percentage'] }}%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <span class="badge bg-{{ $progress['percentage'] >= 100 ? 'success' : 'warning' }}">
                            Total Progress: {{ $progress['percentage'] }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8 mb-4">
            <!-- Assignment Info -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Penugasan</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Validator</th>
                            <td>
                                <strong>{{ $assignment->user->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $assignment->user->email }}</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Program Studi</th>
                            <td>
                                <strong>{{ $pengajuan->studyProgram->name }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $pengajuan->studyProgram->university->name }} -
                                    {{ $pengajuan->studyProgram->degreeLevel->name }}
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <th>Status Penawaran</th>
                            <td>
                                @php
                                $penawaranConfig = [
                                'pending' => ['class' => 'warning', 'text' => 'Menunggu Konfirmasi'],
                                'accepted' => ['class' => 'success', 'text' => 'Diterima'],
                                'rejected' => ['class' => 'danger', 'text' => 'Ditolak'],
                                ];
                                $penawaran = $penawaranConfig[$assignment->status_penawaran] ?? ['class' => 'secondary', 'text' => 'Unknown'];
                                @endphp
                                <span class="badge bg-{{ $penawaran['class'] }}">
                                    {{ $penawaran['text'] }}
                                </span>
                            </td>
                        </tr>
                        @if($assignment->status_penawaran === 'accepted')
                        <tr>
                            <th>Status Pekerjaan</th>
                            <td>
                                <span class="badge bg-info">
                                    {{ $assignment->status_label }}
                                </span>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th>Ditugaskan</th>
                            <td>{{ $assignment->created_at->format('d F Y H:i') }}</td>
                        </tr>
                        @if($assignment->responded_at)
                        <tr>
                            <th>Dikonfirmasi</th>
                            <td>{{ $assignment->responded_at->format('d F Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>

                    @if($assignment->status_penawaran === 'accepted')
                    <div class="mt-3">
                        <a href="{{ route('validator.borang.show', $assignment->id) }}" class="btn btn-primary" target="_blank">
                            <i class="bi bi-clipboard-check"></i> Lihat Detail Validasi
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Revision Details -->
            @if($revisionDetails && ($revisionDetails['led'] || $revisionDetails['suplemen'] || $revisionDetails['lkps']))

            <div class="card mb-4" id="validationCard">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check"></i> Hasil Validasi LED+Suplemen dan LKPS
                    </h5>
                    <span class="badge bg-secondary" id="validationBadge">Memuat...</span>
                </div>
                <div class="card-body">
                    <div id="validationLoading" class="text-muted">
                        <span class="spinner-border spinner-border-sm me-2"></span> Memuat data validasi...
                    </div>

                    <div id="validationContent" class="d-none">
                        <div class="mb-2">
                            <small class="text-muted">Validator</small>
                            <div class="fw-semibold" id="validatorName">-</div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <div class="small text-muted">LED</div>
                                    <div class="fw-bold" id="valLedCount">-</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <div class="small text-muted">Suplemen</div>
                                    <div class="fw-bold" id="valSuplemenCount">-</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <div class="small text-muted">LKPS</div>
                                    <div class="fw-bold" id="valLkpsCount">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Total Progress</small>
                            <div class="progress">
                                <div class="progress-bar" id="valTotalBar" style="width:0%"></div>
                            </div>
                            <div class="small text-muted mt-1">
                                <span id="valTotalText">0%</span> • terakhir update <span id="valUpdatedAt">-</span>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-2">
                            <h6 class="mb-2"><i class="bi bi-list-check"></i> Poin Revisi</h6>

                            <div id="valRevisionSkeleton" class="d-none">
                                <div class="sk-card">
                                    <div class="skeleton sk-line lg sk-w-40"></div>
                                    <div class="skeleton sk-line sk-w-75"></div>
                                    <div class="skeleton sk-line sm sk-w-60"></div>
                                </div>
                                <div class="sk-card">
                                    <div class="skeleton sk-line lg sk-w-30"></div>
                                    <div class="skeleton sk-line sk-w-90"></div>
                                    <div class="skeleton sk-line sm sk-w-60"></div>
                                </div>
                                <div class="sk-card">
                                    <div class="skeleton sk-line lg sk-w-20"></div>
                                    <div class="skeleton sk-line sk-w-75"></div>
                                    <div class="skeleton sk-line sm sk-w-60"></div>
                                </div>
                            </div>

                            <div id="valRevisionList" class="d-none"></div>
                            <div id="valRevisionEmpty" class="text-muted d-none">Tidak ada poin revisi.</div>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Catatan Validator (Keseluruhan)</small>
                            <div class="border rounded p-2 bg-white" id="valNoteAll">-</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Catatan LED</small>
                            <div class="border rounded p-2 bg-white" id="valNoteLed">-</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Catatan Suplemen</small>
                            <div class="border rounded p-2 bg-white" id="valNoteSuplemen">-</div>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted">Catatan LKPS</small>
                            <div class="border rounded p-2 bg-white" id="valNoteLkps">-</div>
                        </div>
                    </div>

                    <div id="validationEmpty" class="d-none text-muted">
                        Belum ada hasil validasi.
                    </div>

                    <div id="validationError" class="d-none alert alert-danger alert-permanent">
                        Gagal memuat hasil validasi.
                    </div>
                </div>
            </div>
            {{-- <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle"></i> Item yang Perlu Revisi
                    </h5>
                </div>
                <div class="card-body">
                    @if($revisionDetails['led'])
                    <h6 class="fw-bold">LED:</h6>
                    <ul>
                        @foreach($revisionDetails['led'] as $item)
                        <li>
                            @if(isset($item['elemen']->kode_elemen))
                            <strong>{{ $item['elemen']->kode_elemen }}</strong>: {{ $item['elemen']->pernyataan_elemen }}
            @endif
            <br>
            <span class="badge bg-{{ $item['grade'] === 'A' ? 'danger' : 'warning' }}">
                Grade {{ $item['grade'] }}
            </span>
            @if($item['catatan'])
            <br>
            <small class="text-muted">{{ $item['catatan'] }}</small>
            @endif
            </li>
            @endforeach
            </ul>
            @endif

            @if($revisionDetails['suplemen'])
            <h6 class="fw-bold mt-3">Suplemen:</h6>
            <ul>
                @foreach($revisionDetails['suplemen'] as $item)
                <li>
                    @if(isset($item['elemen']->kode_elemen))
                    <strong>{{ $item['elemen']->kode_elemen }}</strong>: {{ $item['elemen']->pernyataan_elemen }}
                    @endif
                    <br>
                    <span class="badge bg-{{ $item['grade'] === 'A' ? 'danger' : 'warning' }}">
                        Grade {{ $item['grade'] }}
                    </span>
                    @if($item['catatan'])
                    <br>
                    <small class="text-muted">{{ $item['catatan'] }}</small>
                    @endif
                </li>
                @endforeach
            </ul>
            @endif

            @if($revisionDetails['lkps'])
            <h6 class="fw-bold mt-3">LKPS:</h6>
            <ul>
                @foreach($revisionDetails['lkps'] as $item)
                <li>
                    <strong>{{ $item['indikator']->kode_indikator }}</strong>
                    <br>
                    <span class="badge bg-{{ $item['grade'] === 'A' ? 'danger' : 'warning' }}">
                        Grade {{ $item['grade'] }}
                    </span>
                    @if($item['catatan'])
                    <br>
                    <small class="text-muted">{{ $item['catatan'] }}</small>
                    @endif
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div> --}}
    @endif
</div>

<!-- Right Column - Timeline -->
<div class="col-lg-4">
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i> Timeline
            </h5>
        </div>
        <div class="card-body">
            <ul class="list-unstyled timeline">
                <li class="mb-3">
                    <i class="bi bi-circle-fill text-primary"></i>
                    <strong>Validator Ditugaskan</strong>
                    <br>
                    <small class="text-muted">{{ $assignment->created_at->format('d F Y H:i') }}</small>
                </li>

                @if($assignment->responded_at)
                <li class="mb-3">
                    <i class="bi bi-circle-fill text-{{ $assignment->status_penawaran === 'accepted' ? 'success' : 'danger' }}"></i>
                    <strong>
                        {{ $assignment->status_penawaran === 'accepted' ? 'Penawaran Diterima' : 'Penawaran Ditolak' }}
                    </strong>
                    <br>
                    <small class="text-muted">{{ $assignment->responded_at->format('d F Y H:i') }}</small>
                </li>
                @endif

                @if($assignment->submitted_at)
                <li class="mb-3">
                    <i class="bi bi-circle-fill text-info"></i>
                    <strong>Validasi Disubmit</strong>
                    <br>
                    <small class="text-muted">{{ $assignment->submitted_at->format('d F Y H:i') }}</small>
                </li>
                @endif

                @if($assignment->approved_at)
                <li class="mb-3">
                    <i class="bi bi-circle-fill text-success"></i>
                    <strong>Validasi Disetujui</strong>
                    <br>
                    <small class="text-muted">{{ $assignment->approved_at->format('d F Y H:i') }}</small>
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>
</div>
</div>
@endsection
