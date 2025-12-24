@extends('layouts.template.app')

@section('title', 'Penawaran Asesmen')

@push('styles')
<style>
    .penawaran-card {
        transition: all 0.3s ease;
        border: 2px solid #e0e0e0;
    }

    .penawaran-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-color: #932136;
    }

    .status-badge {
        font-size: 14px;
        padding: 8px 16px;
    }

    .info-row {
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-row:last-child {
        border-bottom: none;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="welcome-section mb-4">
        <div class="welcome-content">
            <h2><i class="bi bi-envelope-open"></i> Penawaran Asesmen</h2>
            <p class="mb-0">
                Anda harus <b>menerima</b> penawaran ini sebelum dapat mengakses kertas kerja penilaian.
            </p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-warning text-dark py-3">
            <h5 class="mb-0">
                <i class="bi bi-bell-fill"></i> Penawaran untuk Asesmen Ini
            </h5>
        </div>
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card penawaran-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1 text-primary">{{ $asesmen->name }}</h5>
                                    <span class="badge bg-primary">{{ $penawaran->role->alias }}</span>
                                </div>

                                <span class="badge status-badge
                                    @if($penawaran->status_penawaran === 'accepted') bg-success
                                    @elseif($penawaran->status_penawaran === 'rejected') bg-danger
                                    @else bg-warning text-dark @endif">
                                    @if($penawaran->status_penawaran === 'accepted')
                                    <i class="bi bi-check-circle"></i> Diterima
                                    @elseif($penawaran->status_penawaran === 'rejected')
                                    <i class="bi bi-x-circle"></i> Ditolak
                                    @else
                                    <i class="bi bi-clock-history"></i> Pending
                                    @endif
                                </span>
                            </div>

                            <div class="mb-3">
                                <div class="info-row">
                                    <i class="bi bi-building text-muted me-2"></i>
                                    <strong>Perguruan Tinggi:</strong><br>
                                    <span class="ms-4">{{ $asesmen->studyProgram->university->name ?? '-' }}</span>
                                </div>
                                <div class="info-row">
                                    <i class="bi bi-tag text-muted me-2"></i>
                                    <strong>Kode Panel:</strong>
                                    <span class="badge bg-secondary ms-2">{{ $asesmen->kode_panel ?? 'N/A' }}</span>
                                </div>
                                <div class="info-row">
                                    <i class="bi bi-calendar text-muted me-2"></i>
                                    <strong>Periode:</strong><br>
                                    <span class="ms-4">
                                        @if($asesmen->tanggal_mulai && $asesmen->tanggal_selesai)
                                        {{ \Carbon\Carbon::parse($asesmen->tanggal_mulai)->format('d M Y') }} -
                                        {{ \Carbon\Carbon::parse($asesmen->tanggal_selesai)->format('d M Y') }}
                                        @else
                                        -
                                        @endif
                                    </span>
                                </div>
                                <div class="info-row">
                                    <i class="bi bi-clock text-muted me-2"></i>
                                    <strong>Ditawarkan:</strong>
                                    <span class="text-muted ms-2">{{ $penawaran->created_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            @if($asesmen->description)
                            <div class="alert alert-light alert-permanent mb-3">
                                <small><i class="bi bi-info-circle me-1"></i>
                                    {{ Str::limit($asesmen->description, 200) }}
                                </small>
                            </div>
                            @endif

                            <hr>

                            {{-- Tombol aksi hanya muncul kalau masih pending --}}
                            @if($penawaran->status_penawaran === null || $penawaran->status_penawaran === 'pending')
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-success" onclick="acceptPenawaran({{ $penawaran->id }}, '{{ $penawaran->role->alias }}')">
                                    <i class="bi bi-check-circle"></i> Terima Penawaran
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="rejectPenawaran({{ $penawaran->id }}, '{{ $asesmen->name }}')">
                                    <i class="bi bi-x-circle"></i> Tolak Penawaran
                                </button>
                            </div>
                            @elseif($penawaran->status_penawaran === 'accepted')
                            <div class="alert alert-success alert-permanent mb-0">
                                <i class="bi bi-check-circle me-1"></i>
                                Anda sudah menerima penawaran ini.
                                <a href="{{ route('ak.berkas.show', $asesmen->id) }}" class="alert-link">
                                    Buka Penilaian
                                </a>
                            </div>
                            @else
                            <div class="alert alert-danger alert-permanent mb-0">
                                <i class="bi bi-x-circle me-1"></i>
                                Anda telah menolak penawaran ini.
                            </div>
                            @endif
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('ak.berkas') }}" class="btn btn-link">
                                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Berkas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Accept Modal -->
<div class="modal fade" id="acceptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle"></i> Terima Penawaran
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="acceptForm">
                <div class="modal-body">
                    <p>Anda akan menerima penawaran sebagai <strong id="roleText"></strong> dan siap melakukan penilaian.</p>

                    <div class="alert alert-info alert-permanent">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            Setelah menerima, Anda akan dapat mengakses kertas kerja penilaian dan melakukan asesmen.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan (opsional):</label>
                        <textarea class="form-control" id="acceptNote" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Terima Penawaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle"></i> Tolak Penawaran
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm">
                <div class="modal-body">
                    <p>Anda akan menolak penawaran untuk: <strong id="asesmenText"></strong></p>

                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span>:</label>
                        <textarea class="form-control" id="rejectNote" rows="4" placeholder="Berikan alasan penolakan yang jelas..." required></textarea>
                        <small class="text-muted">Alasan penolakan wajib diisi</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Tolak Penawaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection


@push('scripts')
<script>
    let currentPenawaranId = '{{ $penawaran->id }}';

    function acceptPenawaran(id, role) {
        currentPenawaranId = id;
        document.getElementById('roleText').textContent = role;
        document.getElementById('acceptNote').value = '';
        new bootstrap.Modal(document.getElementById('acceptModal')).show();
    }

    function rejectPenawaran(id, asesmenName) {
        currentPenawaranId = id;
        document.getElementById('asesmenText').textContent = asesmenName;
        document.getElementById('rejectNote').value = '';
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }

    document.getElementById('acceptForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const note = document.getElementById('acceptNote').value;
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

        try {
            const response = await fetch(`/penawaran/${currentPenawaranId}/accept`, {
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
                    , confirmButtonColor: '#28a745'
                });

                location.reload();
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: error.message
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Terima Penawaran';
        }
    });

    document.getElementById('rejectForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const note = document.getElementById('rejectNote').value;

        if (!note.trim()) {
            Swal.fire({
                icon: 'warning'
                , title: 'Perhatian'
                , text: 'Mohon berikan alasan penolakan'
            });
            return;
        }

        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

        try {
            const response = await fetch(`/penawaran/${currentPenawaranId}/reject`, {
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
                    , confirmButtonColor: '#28a745'
                });

                location.reload();
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: error.message
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-x-circle"></i> Tolak Penawaran';
        }
    });

</script>
@endpush
