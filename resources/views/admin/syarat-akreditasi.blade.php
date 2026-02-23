{{-- resources/views/admin/syarat-akreditasi/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Konfigurasi Syarat Akreditasi')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Konfigurasi Syarat Akreditasi</h4>
            <p class="text-muted small mb-0">
                Perubahan nilai akan langsung berlaku pada proses finalisasi AL berikutnya.
                Setiap perubahan tercatat di log audit.
            </p>
        </div>
        <a href="{{ route('admin.syarat.config.snapshot') }}" class="btn btn-sm btn-outline-secondary" target="_blank">
            <i class="bi bi-braces"></i> JSON Snapshot
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ── KELOMPOK: SKOR ── --}}
    @foreach([
    ['kelompok' => 'skor', 'label' => '🎯 Threshold Skor', 'icon' => 'bar-chart'],
    ['kelompok' => 'pelampauan', 'label' => '📋 Pelampauan Standar', 'icon' => 'check-circle'],
    ['kelompok' => 'rasio_dtps', 'label' => '👨‍🏫 Rasio DTPS:Mahasiswa', 'icon' => 'people'],
    ['kelompok' => 'jabatan', 'label' => '🏅 Jabatan Fungsional Dosen', 'icon' => 'person-badge'],
    ] as $group)
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light fw-semibold">
            <i class="bi bi-{{ $group['icon'] }} me-2"></i>{{ $group['label'] }}
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:30%">Syarat</th>
                        <th style="width:15%">Tipe</th>
                        <th style="width:25%">Nilai Aktif</th>
                        <th style="width:20%">Keterangan</th>
                        <th style="width:10%"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($syarats->get($group['kelompok'], collect()) as $syarat)
                    <tr>
                        <td>
                            <div class="fw-medium">{{ $syarat->label }}</div>
                            <div class="text-muted small">
                                <code>{{ $syarat->kelompok }}.{{ $syarat->kunci }}</code>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $syarat->tipe }}</span>
                        </td>
                        <td>
                            @if($syarat->tipe === 'array')
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($syarat->nilai_cast as $item)
                                <span class="badge bg-primary">{{ $item }}</span>
                                @endforeach
                            </div>
                            @else
                            <span class="fw-semibold text-dark">{{ $syarat->nilai }}</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ Str::limit($syarat->keterangan, 80) }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editModal" data-id="{{ $syarat->id }}" data-label="{{ $syarat->label }}" data-tipe="{{ $syarat->tipe }}" data-nilai="{{ $syarat->nilai }}" data-keterangan="{{ $syarat->keterangan }}" data-url="{{ route('admin.syarat.update', $syarat) }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="{{ route('admin.syarat.history', $syarat) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-clock-history"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">
                            Belum ada konfigurasi untuk kelompok ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>

{{-- ── MODAL EDIT ── --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editForm">
            @csrf @method('PATCH')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Syarat: <span id="modalLabel"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Keterangan --}}
                    <div class="alert alert-light border small mb-3" id="modalKeterangan"></div>

                    {{-- Nilai — single --}}
                    <div id="fieldSingle">
                        <label class="form-label fw-medium">Nilai Baru</label>
                        <input type="text" name="nilai" id="inputNilai" class="form-control" placeholder="Masukkan nilai baru">
                        <div class="form-text" id="tipeHint"></div>
                    </div>

                    {{-- Nilai — array (tag input) --}}
                    <div id="fieldArray" style="display:none">
                        <label class="form-label fw-medium">Nilai Baru</label>
                        <input type="text" name="nilai" id="inputArray" class="form-control" placeholder="Pisahkan dengan koma, contoh: lektor, lektor kepala">
                        <div class="form-text">Pisahkan setiap item dengan koma.</div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Alasan Perubahan</label>
                        <input type="text" name="alasan" class="form-control" placeholder="Opsional — dicatat di log audit">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            const tipe = btn.dataset.tipe;
            const nilai = btn.dataset.nilai;

            document.getElementById('modalLabel').textContent = btn.dataset.label;
            document.getElementById('modalKeterangan').textContent = btn.dataset.keterangan;
            document.getElementById('editForm').action = btn.dataset.url;

            const isArray = tipe === 'array';
            document.getElementById('fieldSingle').style.display = isArray ? 'none' : 'block';
            document.getElementById('fieldArray').style.display = isArray ? 'block' : 'none';

            if (isArray) {
                // Parse JSON array → comma-separated untuk display
                try {
                    const arr = JSON.parse(nilai);
                    document.getElementById('inputArray').value = Array.isArray(arr) ? arr.join(', ') : nilai;
                } catch {
                    document.getElementById('inputArray').value = nilai;
                }
            } else {
                document.getElementById('inputNilai').value = nilai;
                document.getElementById('tipeHint').textContent = {
                    integer: 'Masukkan bilangan bulat.'
                    , float: 'Masukkan angka desimal (gunakan titik).'
                    , boolean: 'Masukkan: true atau false.'
                } [tipe] || '';
            }
        });
    });

</script>
@endpush
@endsection
