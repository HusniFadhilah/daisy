@extends('layouts.template.app')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-1">Hasil dan Berita Acara Asesmen Lapangan (AL)</h4>
            <div class="text-muted" style="font-size: 13px;">
                Kode Asesmen: <b>{{ $asesmen->code }}</b> —
                {{ $asesmen->studyProgram?->university?->name ?? '-' }} /
                {{ $asesmen->studyProgram?->full_name ?? $asesmen->studyProgram?->name ?? '-' }}
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('al.berkas.laporanPdf', $asesmen->id) }}" class="btn btn-primary">
                <i class="bi bi-file-earmark-pdf"></i>
                Lihat Laporan PDF
            </a>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <div class="fw-semibold">Hasil dan Berita Acara Asesmen Lapangan (AL) (PDF)</div>
                    {{-- <div class="text-muted" style="font-size: 13px;">
                        Anda bisa upload lebih dari 1 file. Urutan bisa diatur dengan drag & drop.
                    </div> --}}
                </div>
            </div>

            <form id="uploadForm" class="mt-3">
                @csrf
                <div class="row g-2 align-items-center">
                    <div class="col-md-8">
                        <input type="file" name="files[]" class="form-control" accept="application/pdf" multiple required>
                        <div class="form-text">Format: PDF, max 10MB per file.</div>
                    </div>
                    <div class="col-md-4 d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload"></i> Upload
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>

    {{-- <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div class="fw-semibold">
                Daftar Berita Acara
                <span class="text-muted" style="font-size: 13px;">(drag untuk ubah urutan)</span>
            </div>
            <button id="saveOrderBtn" class="btn btn-outline-primary btn-sm" disabled>
                <i class="bi bi-save"></i> Simpan Urutan
            </button>
        </div>

        <div class="card-body p-0">
            <div id="alertBox" class="p-3 d-none"></div>

            @if($docs->count() === 0)
            <div class="p-4 text-center text-muted">
                Belum ada file berita acara.
            </div>
            @else
            <ul id="docList" class="list-group list-group-flush">
                @foreach($docs as $doc)
                <li class="list-group-item d-flex justify-content-between align-items-center" data-id="{{ $doc->id }}">
    <div class="d-flex align-items-center gap-3">
        <span class="text-muted" style="cursor: grab;">
            <i class="bi bi-grip-vertical"></i>
        </span>

        <div>
            <div class="fw-semibold">
                <i class="bi bi-file-pdf text-danger"></i>
                {{ $doc->original_name }}
            </div>
            <div class="text-muted" style="font-size: 12px;">
                {{ number_format(($doc->file_size ?? 0)/1024/1024, 2) }} MB
                •
                <span class="badge {{ $doc->is_active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $doc->is_active ? 'Aktif (ikut merge)' : 'Nonaktif' }}
                </span>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('al.berkas.documents.download', [$asesmen->id, $doc->id]) }}">
            <i class="bi bi-download"></i>
        </a>

        <button class="btn btn-outline-warning btn-sm btnToggle" data-id="{{ $doc->id }}">
            <i class="bi bi-toggle2-on"></i>
            Toggle
        </button>

        <button class="btn btn-outline-danger btn-sm btnDelete" data-id="{{ $doc->id }}">
            <i class="bi bi-trash"></i>
        </button>
    </div>
    </li>
    @endforeach
    </ul>
    @endif
</div>
</div> --}}

</div>
@endsection

@section('scripts')
{{-- SortableJS (CDN) --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
    const asesmenId = @json($asesmen - > id);

    const uploadUrl = @json(route('al.berkas.documents.beritaAcara.upload', $asesmen - > id));
    const reorderUrl = @json(route('al.berkas.documents.reorder', $asesmen - > id));
    const toggleBase = @json(route('al.berkas.documents.toggle', [$asesmen - > id, 0])); // replace 0
    const deleteBase = @json(route('al.berkas.documents.destroy', [$asesmen - > id, 0])); // replace 0

    const alertBox = document.getElementById('alertBox');
    const saveOrderBtn = document.getElementById('saveOrderBtn');
    const docList = document.getElementById('docList');

    function showAlert(type, message) {
        alertBox.classList.remove('d-none');
        alertBox.className = 'p-3 alert alert-' + type;
        alertBox.innerHTML = message;
        setTimeout(() => alertBox.classList.add('d-none'), 4000);
    }

    // Upload multi file
    document.getElementById('uploadForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const res = await fetch(uploadUrl, {
                method: 'POST'
                , headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    , 'X-CSRF-TOKEN': @json(csrf_token())
                , }
                , body: formData
            });

            const json = await res.json();

            if (!res.ok || !json.success) {
                showAlert('danger', json.message || 'Upload gagal');
                return;
            }

            showAlert('success', json.message || 'Upload berhasil');
            window.location.reload();
        } catch (err) {
            showAlert('danger', 'Terjadi kesalahan saat upload');
        }
    });

    // Drag reorder
    if (docList) {
        const sortable = new Sortable(docList, {
            animation: 150
            , handle: '.bi-grip-vertical'
            , onEnd: function() {
                saveOrderBtn.disabled = false;
            }
        });

        saveOrderBtn ? .addEventListener('click', async function() {
            const items = [...docList.querySelectorAll('li[data-id]')];
            const orders = items.map((li, idx) => ({
                id: parseInt(li.getAttribute('data-id'))
                , sort_order: idx
            }));

            try {
                const res = await fetch(reorderUrl, {
                    method: 'PATCH'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'X-Requested-With': 'XMLHttpRequest'
                        , 'X-CSRF-TOKEN': @json(csrf_token())
                    , }
                    , body: JSON.stringify({
                        orders
                    })
                });

                const json = await res.json();
                if (!res.ok || !json.success) {
                    showAlert('danger', json.message || 'Gagal simpan urutan');
                    return;
                }

                showAlert('success', json.message || 'Urutan tersimpan');
                saveOrderBtn.disabled = true;
            } catch (err) {
                showAlert('danger', 'Terjadi kesalahan saat menyimpan urutan');
            }
        });
    }

    // Toggle aktif
    document.querySelectorAll('.btnToggle').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            const url = toggleBase.replace(/\/0\/toggle$/, '/' + id + '/toggle');

            try {
                const res = await fetch(url, {
                    method: 'PATCH'
                    , headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                        , 'X-CSRF-TOKEN': @json(csrf_token())
                    , }
                });

                const json = await res.json();
                if (!res.ok || !json.success) {
                    showAlert('danger', json.message || 'Gagal toggle');
                    return;
                }
                window.location.reload();
            } catch (err) {
                showAlert('danger', 'Terjadi kesalahan saat toggle');
            }
        });
    });

    // Delete file
    document.querySelectorAll('.btnDelete').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');

            if (!confirm('Hapus dokumen ini?')) return;

            const url = deleteBase.replace(/\/0$/, '/' + id);

            try {
                const res = await fetch(url, {
                    method: 'DELETE'
                    , headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                        , 'X-CSRF-TOKEN': @json(csrf_token())
                    , }
                });

                const json = await res.json();
                if (!res.ok || !json.success) {
                    showAlert('danger', json.message || 'Gagal hapus');
                    return;
                }

                showAlert('success', json.message || 'Dokumen dihapus');
                window.location.reload();
            } catch (err) {
                showAlert('danger', 'Terjadi kesalahan saat hapus dokumen');
            }
        });
    });

</script>
@endsection
