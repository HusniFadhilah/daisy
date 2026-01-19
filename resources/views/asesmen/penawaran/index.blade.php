@extends('layouts.template.app')

@section('title', 'Daftar Penawaran Asesmen')

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

@php
$authUser = Auth::user();
@endphp

@section('content')
<div class="container-fluid py-3">
    <!-- Welcome Section -->
    <div class="welcome-section mb-4">
        <div class="welcome-content">
            <h2><i class="bi bi-envelope-open"></i> Daftar Penawaran Asesmen</h2>
            <p class="mb-0">Kelola penawaran penilaian akreditasi yang ditawarkan kepada Anda</b></p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4 col-lg-3 mb-2">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h3 class="text-warning mb-0">{{ $penawarans->count() }}</h3>
                    <small class="text-muted">Menunggu Respon</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-3 mb-2">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h3 class="text-success mb-0">{{ $assignments->where('status_penawaran', 'accepted')->count() }}</h3>
                    <small class="text-muted">Diterima</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-3 mb-2">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h3 class="text-danger mb-0">{{ $assignments->where('status_penawaran', 'rejected')->count() }}</h3>
                    <small class="text-muted">Ditolak</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-3 mb-2">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h3 class="text-primary mb-0">{{ $assignments->where('status_pekerjaan', 'submitted')->count() }}</h3>
                    <small class="text-muted">Sudah Submit</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Penawaran -->
    @if($penawarans->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark py-3">
            <h5 class="mb-0">
                <i class="bi bi-bell-fill"></i> Penawaran Baru - Perlu Respon ({{ $penawarans->count() }})
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($penawarans as $penawaran)
                <div class="col-md-6 mb-4">
                    <div class="card penawaran-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1 text-primary">{{ $penawaran->asesmen->name }}</h5>
                                    <span class="badge bg-primary">{{ $penawaran->role->alias.' '.$penawaran->jenis_asesmen_label }}</span>
                                </div>
                                <span class="badge bg-warning status-badge">
                                    <i class="bi bi-clock-history"></i> Pending
                                </span>
                            </div>

                            <div class="mb-3">
                                <div class="info-row">
                                    <i class="bi bi-building text-muted me-2"></i>
                                    <strong>Perguruan Tinggi:</strong><br>
                                    <span class="ms-4">{{ $penawaran->asesmen->studyProgram->university->name ?? '-' }}</span>
                                </div>
                                <div class="info-row">
                                    <i class="bi bi-tag text-muted me-2"></i>
                                    <strong>Kode Panel:</strong>
                                    <span class="badge bg-secondary ms-2">{{ $penawaran->asesmen->kode_panel ?? 'N/A' }}</span>
                                </div>
                                <div class="info-row">
                                    <i class="bi bi-calendar text-muted me-2"></i>
                                    <strong>Periode:</strong><br>
                                    <span class="ms-4">
                                        @if($penawaran->asesmen->tanggal_mulai && $penawaran->asesmen->tanggal_selesai)
                                        {{ \App\Libraries\Date::tglIndo($penawaran->asesmen->tanggal_mulai) }} -
                                        {{ \App\Libraries\Date::tglIndo($penawaran->asesmen->tanggal_selesai) }}
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

                            @if($penawaran->asesmen->description)
                            <div class="alert alert-light alert-permanent alert-dismissible mb-3">
                                <small><i class="bi bi-info-circle me-1"></i> {{ Str::limit($penawaran->asesmen->description, 150) }}</small>
                            </div>
                            @endif

                            <!-- Links dari DE -->
                            @if($penawaran->kertas_kerja_link || $penawaran->panduan_link)
                            <div class="mb-3">
                                <small class="text-muted"><strong>Dokumen dari Admin:</strong></small>
                                @if($penawaran->kertas_kerja_link)
                                <div>
                                    <a href="{{ $penawaran->kertas_kerja_link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark-text"></i> Kertas Kerja
                                    </a>
                                </div>
                                @endif
                                @if($penawaran->panduan_link)
                                <div class="mt-1">
                                    <a href="{{ $penawaran->panduan_link }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-book"></i> Panduan Penilaian
                                    </a>
                                </div>
                                @endif
                            </div>
                            @endif

                            <hr>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-success" onclick="acceptPenawaran('{{ $penawaran->token }}', '{{ $penawaran->role->alias }}')">
                                    <i class="bi bi-check-circle"></i> Terima Penawaran
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="rejectPenawaran('{{ $penawaran->token }}', '{{ $penawaran->asesmen->name }}')">
                                    <i class="bi bi-x-circle"></i> Tolak Penawaran
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-info alert-permanent alert-dismissible">
        <i class="bi bi-info-circle me-2"></i>
        Tidak ada penawaran baru saat ini. Silakan tunggu penawaran dari DE LAMDEPILAR.
    </div>
    @endif

    <!-- Riwayat Penawaran -->
    @if($assignments->count() > 0)
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Penawaran</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Asesmen</th>
                            <th>Role</th>
                            <th>Status Penawaran</th>
                            <th>Status Pekerjaan</th>
                            <th>Catatan</th>
                            <th>Tanggal Respon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $key=> $assignment)
                        @php
                        $idAsesors = $assignment->where('id_asesmen',$assignment->id_asesmen)->whereNot('id_user', $authUser->id)->pluck('id_user');
                        $jenisAsesmen = $assignment->jenis_asesmen; // dokumen | ak | al
                        $asesmen = $assignment->asesmen;
                        $pengajuan = $asesmen->pengajuan;
                        $badgePelaporan = $asesmen->pengajuan? $asesmen->pengajuan->getPelaporanBadge($assignment->jenis_asesmen): null;
                        @endphp
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $pengajuan ? $pengajuan->judul : $asesmen->name }}</div>
                                <small class="text-muted text-block">
                                    <i class="bi bi-building"></i>
                                    {{ $asesmen->studyProgram->full_name ?? 'N/A' }}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $assignment->role->alias.' '.$assignment->jenis_asesmen_label }}</span>
                            </td>
                            <td>
                                @if($assignment->status_penawaran === 'accepted')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Diterima
                                </span>
                                @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle"></i> Ditolak
                                </span>
                                @endif
                            </td>
                            <td>
                                @if($assignment->status_pekerjaan && $assignment->status_penawaran === 'accepted')
                                <span class="badge bg-{{ $assignment->status_badge }}">
                                    {{ $assignment->status_label }}
                                </span>
                                @else
                                <span class="text-muted">-</span>
                                @endif

                                @if($badgePelaporan)
                                <span class="badge bg-success text-wrap mt-2">
                                    <i class="bi bi-check-circle"></i>
                                    {{ $badgePelaporan }}
                                </span>
                                @endif
                            </td>
                            <td>
                                @if($assignment->response_note)
                                <small>{{ Str::limit($assignment->response_note, 50) }}</small>
                                @else
                                <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                <small>{{ $assignment->responded_at ? \App\Libraries\Date::tglWaktu($assignment->responded_at) : '-' }}</small>
                            </td>
                            <td>
                                @if($assignment->status_penawaran === 'accepted')
                                @if($authUser->role_selected == 'asesor')
                                <a href="{{ route($jenisAsesmen.'.berkas.show',$assignment->id_asesmen) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-arrow-right"></i> Lihat Penilaian
                                </a>
                                @elseif($authUser->role_selected == 'validator')
                                @if ($jenisAsesmen == 'ak')
                                <a href="{{ route($jenisAsesmen.'.validasi.asesor', ['idAsesmen' => $assignment['asesmen']->id, 'jenisAsesmen' => 'ak']) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-arrow-right"></i> Lihat Penilaian
                                </a>
                                @elseif ($jenisAsesmen == 'dokumen')
                                <a href="{{ route('validator.borang.show',$assignment->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-arrow-right"></i> Lihat Penilaian
                                </a>
                                @endif

                                @if($asesmen->pengajuan?->canBeReported($assignment->jenis_asesmen))
                                <button type="button" class="btn btn-sm btn-success mt-2 js-open-pelaporan" data-type="{{ $assignment->jenis_asesmen }}" data-assignment-id="{{ $assignment->id }}" data-nomor="{{ $asesmen->pengajuan->nomor_pengajuan ?? $asesmen->code }}">
                                    <i class="bi bi-file-earmark-text"></i>
                                    Pelaporan {{ $assignment->jenis_asesmen_label }}
                                </button>
                                @endif

                                @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Accept Modal -->
<div class="modal fade" id="acceptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
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

                    <div class="alert alert-info">
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
    <div class="modal-dialog modal-dialog-centered">
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

@push('scripts')
<script src="{{ asset('assets/js/pelaporan.js') }}"></script>
<script>
    let currentPenawaranId = null;

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

    document.addEventListener('click', async function(e) {
        const btn = e.target.closest('.js-open-pelaporan');
        if (!btn) return;

        const assignmentId = btn.dataset.assignmentId;
        const jenis = btn.dataset.type;
        const nomor = btn.dataset.nomor ? btn.dataset.nomor : '';

        const CONFIG = {
            dokumen: {
                label: 'Laporan Validasi LED + Suplemen & LKPS'
                , upload: @json(route('pelaporan.borang.upload', ['assignment' => '__ID__']))
                , finalize: @json(route('pelaporan.borang.finalize', ['assignment' => '__ID__']))
            , }
            , ak: {
                label: 'Laporan Validasi Asesmen Kecukupan (AK)'
                , upload: @json(route('pelaporan.validasiAk.upload', ['assignment' => '__ID__']))
                , finalize: @json(route('pelaporan.validasiAk.finalize', ['assignment' => '__ID__']))
            , }
            , al: {
                label: 'Laporan Asesmen Lapangan (AL)'
                , upload: @json(route('pelaporan.al.upload', ['assignment' => '__ID__']))
                , finalize: @json(route('pelaporan.al.finalize', ['assignment' => '__ID__']))
            , }
        , };

        if (!CONFIG[jenis]) return;

        const uploadUrl = CONFIG[jenis].upload.replace('__ID__', assignmentId);
        const finalizeUrl = CONFIG[jenis].finalize.replace('__ID__', assignmentId);

        // ===== STEP 1: UPLOAD =====
        const file = await pickPdf(CONFIG[jenis].label);
        if (!file) return;

        const uploadResult = await uploadFile(uploadUrl, file);
        if (!uploadResult.success) {
            Swal.fire('Gagal', uploadResult.message, 'error');
            return;
        }

        // ===== STEP 2: FINALIZE =====
        const ok = await confirmFinalize(uploadResult.filename, nomor);
        if (!ok) {
            Swal.fire('Tersimpan', 'File sudah diunggah. Anda bisa finalisasi nanti.', 'info');
            return;
        }

        const finalizeResult = await post(finalizeUrl);
        if (!finalizeResult.success) {
            Swal.fire('Gagal', finalizeResult.message, 'error');
            return;
        }

        Swal.fire('Berhasil', finalizeResult.message, 'success')
            .then(() => location.reload());
    });

    // =======================
    // HELPERS
    // =======================

    async function pickPdf(label) {
        const res = await Swal.fire({
            title: 'Pelaporan'
            , html: `
            <div class="text-start">
                <p>Upload <b>${label}</b></p>
                <input id="plFile" type="file" class="form-control" accept="application/pdf">
                <small class="text-muted">PDF, maksimal 5MB</small>
            </div>
        `
            , showCancelButton: true
            , confirmButtonText: 'Upload'
            , cancelButtonText: 'Batal'
            , preConfirm: function() {
                const f = document.getElementById('plFile').files[0];
                if (!f) return Swal.showValidationMessage('Silahkan pilih file Laporan dalam bentuk PDF');
                if (f.type !== 'application/pdf') return Swal.showValidationMessage('File laporan harus berformat PDF');
                if (f.size > 5 * 1024 * 1024) return Swal.showValidationMessage('File laporan maksimal 5MB');
                return f;
            }
        });

        return res.value;
    }

    async function uploadFile(url, file) {
        const fd = new FormData();
        fd.append('file', file);

        const res = await fetch(url, {
            method: 'POST'
            , headers: {
                'X-CSRF-TOKEN': @json(csrf_token())
                , 'Accept': 'application/json'
            }
            , body: fd
        });

        const json = await res.json();
        return {
            success: res.ok && json.success
            , message: json.message ? json.message : 'Upload gagal'
            , filename: json.doc && json.doc.original_name ? json.doc.original_name : 'laporan'
        };
    }

    async function confirmFinalize(filename, nomor) {
        const res = await Swal.fire({
            icon: 'question'
            , title: 'Finalisasi Pelaporan?'
            , html: `
            <div class="text-start">
                <p>File <b>${filename}</b> berhasil diunggah.</p>
                <p>Status <b>${nomor}</b> akan diperbarui.</p>
            </div>
        `
            , showCancelButton: true
            , confirmButtonText: 'Finalisasi'
            , cancelButtonText: 'Nanti'
        });

        return res.isConfirmed === true;
    }

    async function post(url) {
        const res = await fetch(url, {
            method: 'POST'
            , headers: {
                'X-CSRF-TOKEN': @json(csrf_token())
                , 'Accept': 'application/json'
            }
        });

        const json = await res.json();
        return {
            success: res.ok && json.success
            , message: json.message ? json.message : 'Gagal'
        };
    }

</script>
@endpush

@endsection
