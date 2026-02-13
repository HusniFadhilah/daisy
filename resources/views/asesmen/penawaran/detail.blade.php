@extends('layouts.template.app')

@section('title', 'Detail Penawaran Asesmen')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-envelope-check fs-3 text-primary"></i>
                        </div>
                        <div>
                            <h4 class="mb-0">Detail Penawaran Asesmen</h4>
                            <p class="text-muted mb-0 small">
                                Status:
                                @if($assignment->status_penawaran === 'pending')
                                <span class="badge bg-warning">Menunggu Respons</span>
                                @elseif($assignment->status_penawaran === 'accepted')
                                <span class="badge bg-success">Diterima</span>
                                @else
                                <span class="badge bg-danger">Ditolak</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asesmen Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Asesmen</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="200"><strong>Nama Asesmen</strong></td>
                            <td>{{ $asesmen->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Kode</strong></td>
                            <td>{{ $asesmen->code }}</td>
                        </tr>
                        <tr>
                            <td><strong>Program Studi</strong></td>
                            <td>{{ $asesmen->studyProgram->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Jenis Asesmen</strong></td>
                            <td>
                                <span class="badge bg-info">{{ strtoupper($assignment->jenis_asesmen) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Role Anda</strong></td>
                            <td>
                                <span class="badge bg-primary">{{ $assignment->role->alias }}</span>
                                @if($assignment->urutan_asesor)
                                (Asesor {{ $assignment->urutan_asesor }})
                                @endif
                            </td>
                        </tr>
                        @if($asesmen->tanggal_mulai)
                        <tr>
                            <td><strong>Tanggal Mulai</strong></td>
                            <td>{{ \App\Libraries\Date::tglIndo($asesmen->tanggal_mulai) }}</td>
                        </tr>
                        @endif
                        @if($asesmen->tanggal_selesai)
                        <tr>
                            <td><strong>Tanggal Selesai</strong></td>
                            <td>{{ \App\Libraries\Date::tglIndo($asesmen->tanggal_selesai) }}</td>
                        </tr>
                        @endif
                    </table>

                    @if($asesmen->description)
                    <div class="alert alert-light border alert-permanent">
                        <strong>Deskripsi:</strong><br>
                        {{ $asesmen->description }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons (Only if pending) -->
            @if($assignment->status_penawaran === 'pending')
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-hand-index me-2"></i>Respons Penawaran</h5>
                </div>
                <div class="card-body">
                    <form id="formResponse">
                        <div class="mb-3">
                            <label class="form-label">Catatan <small class="text-muted">(opsional)</small></label>
                            <textarea class="form-control" name="response_note" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success" id="btnAccept">
                                <i class="bi bi-check-circle me-1"></i> Terima Penawaran
                            </button>
                            <button type="button" class="btn btn-danger" id="btnReject">
                                <i class="bi bi-x-circle me-1"></i> Tolak Penawaran
                            </button>
                            <a href="{{ route('penawaran') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Response History (if already responded) -->
            @if($assignment->responded_at)
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Respons</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Waktu Respons:</strong>
                        {{ \Carbon\Carbon::parse($assignment->responded_at)->locale('id')->translatedFormat('d F Y H:i') }}
                    </p>
                    @if($assignment->response_note)
                    <p class="mb-0">
                        <strong>Catatan:</strong><br>
                        <em>{{ $assignment->response_note }}</em>
                    </p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    const btnAccept = document.getElementById('btnAccept')
    const btnReject = document.getElementById('btnReject')
    if (btnAccept) btnAccept.addEventListener('click', async function() {
        const note = document.querySelector('[name="response_note"]').value;

        const result = await Swal.fire({
            icon: 'question'
            , title: 'Terima Penawaran?'
            , text: 'Anda akan ditugaskan untuk melakukan penilaian asesmen ini.'
            , showCancelButton: true
            , confirmButtonText: 'Ya, Terima'
            , cancelButtonText: 'Batal'
            , confirmButtonColor: '#28a745'
        });

        if (!result.isConfirmed) return;

        try {
            const token = '{{ \App\Helpers\RouteHelper::encryptId($assignment->id) }}';
            const response = await fetch(`/penawaran/${token}/accept`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    , 'Accept': 'application/json'
                }
                , body: JSON.stringify({
                    response_note: note
                })
            });

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil!'
                    , text: data.message
                    , timer: 2000
                });

                // Redirect ke berkas
                const route = '{{ $assignment->jenis_asesmen }}' === 'dokumen' ?
                    "{{ route('validator.borang.show', $assignment->id) }}" :
                    ('{{ $assignment->jenis_asesmen }}' === 'ak' ?
                        "{{ route('ak.berkas.show', $asesmen->id) }}" :
                        "{{ route('al.berkas.show', $asesmen->id) }}");
                window.location.href = route;
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error'
                , title: 'Gagal'
                , text: error.message
            });
        }
    });

    if (btnReject) btnReject.addEventListener('click', async function() {
        const note = document.querySelector('[name="response_note"]').value;

        if (!note.trim()) {
            Swal.fire({
                icon: 'warning'
                , title: 'Catatan Wajib'
                , text: 'Mohon berikan alasan penolakan pada kolom catatan.'
            });
            return;
        }

        const result = await Swal.fire({
            icon: 'warning'
            , title: 'Tolak Penawaran?'
            , text: 'Pastikan alasan penolakan sudah jelas.'
            , showCancelButton: true
            , confirmButtonText: 'Ya, Tolak'
            , cancelButtonText: 'Batal'
            , confirmButtonColor: '#dc3545'
        });

        if (!result.isConfirmed) return;

        try {
            const token = '{{ \App\Helpers\RouteHelper::encryptId($assignment->id) }}';

            const response = await fetch(`/penawaran/${token}/reject`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    , 'Accept': 'application/json'
                }
                , body: JSON.stringify({
                    response_note: note
                })
            });

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success'
                    , title: 'Penawaran Ditolak'
                    , text: data.message
                    , timer: 2000
                });

                window.location.href = "{{ route('penawaran') }}";
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error'
                , title: 'Gagal'
                , text: error.message
            });
        }
    });

</script>
@endpush
@endsection
