{{-- ✅ Modal LKPS Online --}}
<div class="modal fade" id="modalLkpsOnline" tabindex="-1" style="--bs-modal-width: 92vw;">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Pengisian LKPS Secara Online
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">

                {{-- Loading --}}
                <div id="onlineLkpsLoading" class="text-center p-5">
                    <span class="spinner-border text-primary"></span>
                    <div class="mt-2 text-muted">Memuat definisi tabel...</div>
                </div>

                {{-- Content: Tab per Elemen --}}
                <div id="onlineLkpsContent" class="d-none">
                    <div class="d-flex">
                        {{-- Sidebar navigasi elemen --}}
                        <div class="border-end bg-light" style="min-width:160px; max-width:160px;">
                            <ul class="nav flex-column py-2" id="onlineLkpsSidebar"></ul>
                        </div>

                        {{-- Panel tabel per elemen --}}
                        <div class="flex-grow-1 p-3 overflow-auto" id="onlineLkpsPanels"></div>
                    </div>
                </div>

                <div id="onlineLkpsEmpty" class="d-none text-center p-4 text-muted">
                    Tidak ada definisi tabel ditemukan.
                </div>
            </div>
            <div class="modal-footer justify-content-start">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i>
                    Data tersimpan per tabel. Klik <strong>Simpan</strong> di setiap tabel.
                </small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let pengajuanId = "{{ $pengajuan->id }}";
    // ✅ Inisialisasi tombol Isi Online
    const btnIsianOnlineLkps = document.getElementById('btnIsianOnlineLkps')
    if (btnIsianOnlineLkps) btnIsianOnlineLkps.addEventListener('click', function() {
        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('modalLkpsOnline')
        ).show();
        loadOnlineLkpsDefinitions();
    });

    // ✅ Load definisi + existing data
    async function loadOnlineLkpsDefinitions() {
        const loading = document.getElementById('onlineLkpsLoading');
        const content = document.getElementById('onlineLkpsContent');
        const empty = document.getElementById('onlineLkpsEmpty');
        const sidebar = document.getElementById('onlineLkpsSidebar');
        const panels = document.getElementById('onlineLkpsPanels');

        loading.classList.remove('d-none');
        content.classList.add('d-none');
        empty.classList.add('d-none');

        try {
            const res = await fetch(
                `/permohonan-akreditasi/${pengajuanId}/borang/lkps-online/definitions`, {
                    headers: {
                        Accept: 'application/json'
                    }
                }
            );
            const data = await res.json();
            console.log(data)

            loading.classList.add('d-none');

            const defs = data.definitions || {};
            const keys = Object.keys(defs);

            if (!keys.length) {
                empty.classList.remove('d-none');
                return;
            }

            sidebar.innerHTML = '';
            panels.innerHTML = '';

            keys.forEach((elemenKode, i) => {
                const group = defs[elemenKode];
                const panelId = `online-panel-${elemenKode.replace(/\./g, '-')}`;
                const isFirst = i === 0;

                // Sidebar item
                sidebar.innerHTML += `
                <li class="nav-item">
                    <a class="nav-link py-1 px-3 small ${isFirst ? 'active fw-bold' : 'text-dark'}"
                        href="#" data-panel="${panelId}"
                        onclick="switchOnlinePanel(event, '${panelId}')">
                        ${elemenKode}
                        <span class="badge bg-secondary ms-1">${group.tables.length}</span>
                    </a>
                </li>`;

                // Panel untuk elemen ini
                let tablesHtml = group.tables.map(t => buildOnlineTableEditor(t)).join('');

                panels.innerHTML += `
                <div id="${panelId}" class="online-panel ${isFirst ? '' : 'd-none'}">
                    <h6 class="mb-3 border-bottom pb-2">
                        <span class="badge bg-primary me-2">${elemenKode}</span>
                        Data Kuantitatif
                    </h6>
                    ${tablesHtml}
                </div>`;
            });

            content.classList.remove('d-none');

        } catch (e) {
            console.error(e);
            loading.classList.add('d-none');
            empty.classList.remove('d-none');
        }
    }

    // ✅ Build editor untuk satu tabel
    function buildOnlineTableEditor(tableDef) {
        const headers = [];
        if (tableDef.headers && tableDef.headers[0]) {
            headers = tableDef.headers[0];
        }
        const existing = tableDef.existing;
        const rows = [
            []
        ];
        if (existing && existing.rows) {
            rows = existing.rows;
        }
        const dbId = tableDef.dataset_borang_id;

        // Header row HTML
        const thHtml = headers.map(h =>
            `<th class="small" style="min-width:100px">${escapeHtml(h)}</th>`
        ).join('') + '<th style="width:40px"></th>';

        // Data rows HTML
        const rowsHtml = rows.map((row, ri) =>
            buildDataRow(headers, row, ri)
        ).join('');

        var rowCount = 0;
        if (existing && existing.rows) {
            rowCount = existing.rows.length;
        }

        const savedInfo = existing ?
            `<small class="text-success">
               <i class="bi bi-check-circle"></i>
               ${rowCount} baris — ${existing.updated_at}
           </small>` :
            `<small class="text-muted">Belum ada data</small>`;

        return `
    <div class="card mb-3" id="online-table-${dbId}">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
            <div>
                <strong class="small">${escapeHtml(tableDef.table_title)}</strong>
                <div id="online-status-${dbId}">${savedInfo}</div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary"
                    onclick="addOnlineRow(${dbId}, ${headers.length})">
                    <i class="bi bi-plus"></i> Baris
                </button>
                <button class="btn btn-sm btn-primary"
                    onclick="saveOnlineTable(${dbId})">
                    <i class="bi bi-save"></i> Simpan
                </button>
            </div>
        </div>
        ${tableDef.keterangan
            ? `<div class="px-3 pt-2">
                   <small class="text-muted fst-italic">
                       <i class="bi bi-info-circle"></i> ${escapeHtml(tableDef.keterangan)}
                   </small>
               </div>`
            : ''}
        <div class="card-body p-0 overflow-auto">
            <table class="table table-bordered table-sm mb-0"
                id="online-tbl-${dbId}" data-col-count="${headers.length}">
                <thead class="table-light">
                    <tr>${thHtml}</tr>
                </thead>
                <tbody id="online-tbody-${dbId}">
                    ${rowsHtml || buildDataRow(headers, [], 0)}
                </tbody>
            </table>
        </div>
    </div>`;
    }

    // ✅ Build satu baris data (editable)
    function buildDataRow(headers, rowData, rowIndex) {
        const cells = headers.map((_, ci) => {
            const val = '';
            if (rowData && typeof rowData[ci] !== 'undefined' && rowData[ci] !== null) {
                val = rowData[ci];
            }
            return `<td>
            <input type="text" class="form-control form-control-sm border-0 p-1"
                value="${escapeHtml(String(val))}"
                placeholder="-">
        </td>`;
        }).join('');

        return `<tr>
        ${cells}
        <td class="text-center align-middle">
            <button class="btn btn-sm btn-link text-danger p-0"
                onclick="removeOnlineRow(this)" title="Hapus baris">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>`;
    }

    // ✅ Tambah baris kosong
    window.addOnlineRow = function(dbId, colCount) {
        const tbody = document.getElementById(`online-tbody-${dbId}`);
        const headers = Array(colCount).fill('');
        tbody.insertAdjacentHTML('beforeend', buildDataRow(headers, [], tbody.rows.length));
    };

    // ✅ Hapus baris
    window.removeOnlineRow = function(btn) {
        btn.closest('tr').remove();
    };

    // ✅ Simpan tabel ke server
    window.saveOnlineTable = async function(dbId) {
        const tbody = document.getElementById(`online-tbody-${dbId}`);
        const statusEl = document.getElementById(`online-status-${dbId}`);
        const rows = [];

        tbody.querySelectorAll('tr').forEach(tr => {
            const cells = [...tr.querySelectorAll('input')]
                .map(inp => inp.value.trim() || null);
            rows.push(cells);
        });

        statusEl.innerHTML = `<small class="text-warning">
        <span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...
    </small>`;

        try {
            const res = await fetch(
                `/permohonan-akreditasi/${pengajuanId}/borang/lkps-online/save`, {
                    method: 'POST'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                    , }
                    , body: JSON.stringify({
                        dataset_borang_id: dbId
                        , rows
                    })
                , }
            );
            const data = await res.json();

            if (data.success) {
                statusEl.innerHTML = `<small class="text-success">
                <i class="bi bi-check-circle"></i>
                ${data.row_count} baris — ${data.updated_at}
            </small>`;
            } else {
                throw new Error(data.message);
            }
        } catch (e) {
            statusEl.innerHTML = `<small class="text-danger">
            <i class="bi bi-x-circle"></i> Gagal: ${e.message}
        </small>`;
        }
    };

    // ✅ Switch panel sidebar
    window.switchOnlinePanel = function(event, panelId) {
        event.preventDefault();

        document.querySelectorAll('.online-panel').forEach(p => p.classList.add('d-none'));
        const panelIdElemen = document.getElementById(panelId)
        if (panelIdElemen) panelIdElemen.classList.remove('d-none');

        document.querySelectorAll('#onlineLkpsSidebar .nav-link').forEach(a => {
            a.classList.remove('active', 'fw-bold');
            a.classList.add('text-dark');
        });
        event.target.classList.add('active', 'fw-bold');
        event.target.classList.remove('text-dark');
    };

</script>
@endpush
