<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>LKPS Excel-like</title>

    <!-- Handsontable CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/handsontable/16.1.1/handsontable.full.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        body {
            font-family: system-ui, Arial, sans-serif;
            margin: 16px;
        }

        .tabs {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .tabs button {
            padding: 6px 10px;
            cursor: pointer;
            border: 1px solid #ddd;
            background: #fff;
        }

        .tabs button.active {
            border-color: #444;
            font-weight: 600;
        }

        #status {
            margin-left: 8px;
            opacity: .8;
        }

        /* renderer class dari Excel fill */
        .xl-fill-FFFF00 {
            background: #fff59d !important;
        }

        /* kuning */
        .xl-fill-FFCC99 {
            background: #ffcc99 !important;
        }

        .xl-bold {
            font-weight: 700;
        }

        .xl-center {
            text-align: center;
        }

        .xl-right {
            text-align: right;
        }

        .xl-wrap {
            white-space: normal !important;
        }

    </style>
</head>
<body>

    <div class="tabs" id="tabs"></div>
    <div id="hot" style="width:100%; height:75vh;"></div>

    <!-- Handsontable JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/handsontable/16.1.1/handsontable.full.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- HyperFormula (formula engine) -->
    <script src="https://cdn.jsdelivr.net/npm/hyperformula@2.7.1/dist/hyperformula.full.min.js"></script>

    <script>
        (function() {
            const container = document.getElementById('hot');
            const tabsEl = document.getElementById('tabs');

            function debounce(fn, ms = 600) {
                let t;
                return (...args) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...args), ms);
                };
            }

            // ====== 0) Ambil model dari Laravel ======
            // Model JSON format yang diharapkan:
            // {
            //   "workbookName":"...",
            //   "sheets": {
            //     "E.2": {
            //       "data": [[...],[...]],
            //       "merges": [{"row":2,"col":0,"rowspan":2,"colspan":1}, ...],  // 0-index
            //       "colWidths": [80,120,...],
            //       "rowHeights": [24,24,...],
            //       "cellMeta": { "r:c": {"className":"xl-fill-FFFF00 xl-wrap","readOnly":true,"type":"numeric"} },
            //       "editableRanges": ["B6:L10","B21:F25","C11:K11"]  // A1 notation
            //     },
            //     "E.3": { ... }
            //   }
            // }
            async function loadModel() {
                const res = await fetch('/lkps/model');
                if (!res.ok) throw new Error('Gagal load /lkps/model');
                return await res.json();
            }

            // ====== 1) Formula engine ======
            const hf = HyperFormula.buildEmpty({
                licenseKey: 'gpl-v3'
            });

            // ====== 2) Helpers: A1 range -> list of editable cells ======
            function colToIndex(colStr) {
                // "A"->0, "B"->1, "AA"->26
                let n = 0;
                for (let i = 0; i < colStr.length; i++) {
                    n = n * 26 + (colStr.charCodeAt(i) - 64);
                }
                return n - 1;
            }

            function a1ToRC(a1) {
                const m = String(a1).match(/^([A-Z]+)(\d+)$/i);
                if (!m) return null;
                const col = colToIndex(m[1].toUpperCase());
                const row = parseInt(m[2], 10) - 1;
                return {
                    row
                    , col
                };
            }

            function expandRange(a1Range) {
                // "B6:L10" -> array of keys "r:c"
                const [a, b] = a1Range.split(':');
                const p1 = a1ToRC(a.trim());
                const p2 = a1ToRC((b || a).trim());
                if (!p1 || !p2) return [];
                const rMin = Math.min(p1.row, p2.row)
                    , rMax = Math.max(p1.row, p2.row);
                const cMin = Math.min(p1.col, p2.col)
                    , cMax = Math.max(p1.col, p2.col);
                const keys = [];
                for (let r = rMin; r <= rMax; r++) {
                    for (let c = cMin; c <= cMax; c++) {
                        keys.push(`${r}:${c}`);
                    }
                }
                return keys;
            }

            // ====== 3) Render ======
            let model = null;
            let hot = null;
            let currentSheet = null;
            let editableSet = new Set();

            const sendPatch = debounce(async (patch) => {
                if (!patch.length) return;
                try {
                    // ganti sesuai kebutuhan (mis. tambah lkps_id)
                    await fetch('/lkps/cells', {
                        method: 'POST'
                        , headers: {
                            'Content-Type': 'application/json'
                        }
                        , body: JSON.stringify({
                            patch
                        })
                    });
                } catch (e) {
                    console.error('Save patch error:', e);
                }
            }, 700);

            function ensureSheetInHF(sheetName, data2D) {
                if (hf.getSheetId(sheetName) === undefined) hf.addSheet(sheetName);
                const id = hf.getSheetId(sheetName);
                hf.setSheetContent(id, data2D);
            }

            function buildTabs(sheetNames) {
                tabsEl.innerHTML = '';
                const status = document.createElement('span');
                status.id = 'status';
                status.textContent = '';
                sheetNames.forEach((name) => {
                    const btn = document.createElement('button');
                    btn.textContent = name;
                    btn.addEventListener('click', () => renderSheet(name));
                    btn.dataset.sheet = name;
                    tabsEl.appendChild(btn);
                });
                tabsEl.appendChild(status);
            }

            function setActiveTab(sheetName) {
                [...tabsEl.querySelectorAll('button')].forEach(b => {
                    b.classList.toggle('active', b.dataset.sheet === sheetName);
                });
                const st = document.getElementById('status');
                st.textContent = `Sheet aktif: ${sheetName}`;
            }

            function renderSheet(sheetName) {
                currentSheet = sheetName;
                const sh = model.sheets[sheetName];

                // editableSet dari editableRanges (A1)
                editableSet = new Set();
                (sh.editableRanges || []).forEach(rng => {
                    expandRange(rng).forEach(k => editableSet.add(k));
                });

                // register/update ke formula engine
                ensureSheetInHF(sheetName, sh.data);

                if (hot) hot.destroy();

                hot = new Handsontable(container, {
                    data: sh.data
                    , rowHeaders: true
                    , colHeaders: true
                    , stretchH: 'all'
                    , width: '100%'
                    , height: '75vh',

                    // persis layout: merges, widths, heights
                    mergeCells: sh.merges || []
                    , colWidths: sh.colWidths || undefined
                    , rowHeights: sh.rowHeights || undefined,

                    // formula: cross-sheet SUM/VLOOKUP dll
                    formulas: {
                        engine: hf
                        , sheetName
                    },

                    // cell meta (fill/bold/alignment/readOnly/type) + editable override
                    cells: (row, col) => {
                        const key = `${row}:${col}`;
                        const props = {};
                        const meta = (sh.cellMeta && sh.cellMeta[key]) ? sh.cellMeta[key] : null;

                        // default: read-only (seperti template)
                        props.readOnly = true;

                        // apply meta dari excel
                        if (meta) {
                            if (meta.className) props.className = meta.className;
                            if (typeof meta.readOnly === 'boolean') props.readOnly = meta.readOnly;
                            if (meta.type) props.type = meta.type; // "numeric" / "text"
                        }

                        // editableRanges menang (ini yang bikin “sel kuning/input” bisa diisi)
                        if (editableSet.has(key)) {
                            props.readOnly = false;
                            // kalau mau paksa numeric utk sel input angka, set dari backend meta.type = "numeric"
                        }

                        return props;
                    },

                    licenseKey: 'non-commercial-and-evaluation'
                , });

                hot.addHook('afterChange', (changes, source) => {
                    if (!changes || source === 'loadData') return;
                    const patch = [];
                    for (const [row, col, oldVal, newVal] of changes) {
                        patch.push({
                            sheet: sheetName
                            , row: row + 1
                            , col: col + 1
                            , value: newVal
                        });
                    }
                    // update formula engine untuk sheet ini (biar recalculation konsisten)
                    const id = hf.getSheetId(sheetName);
                    hf.setSheetContent(id, model.sheets[sheetName].data);

                    sendPatch(patch);
                });

                setActiveTab(sheetName);
            }

            // ====== 4) Boot ======
            (async function boot() {
                model = await loadModel();

                // register semua sheets ke engine dulu (penting untuk cross-sheet reference)
                Object.entries(model.sheets).forEach(([name, sh]) => ensureSheetInHF(name, sh.data));

                const sheetNames = Object.keys(model.sheets);
                buildTabs(sheetNames);

                // buka sheet pertama
                renderSheet(sheetNames[0]);
            })().catch(err => {
                console.error(err);
                tabsEl.innerHTML = `<span style="color:red">Gagal load model: ${err.message}</span>`;
            });

        })();

    </script>
</body>
</html>
