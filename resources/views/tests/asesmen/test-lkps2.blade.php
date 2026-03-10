<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Spreadsheet Editor (Standalone)</title>

    <!-- jSpreadsheet CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jexcel/4.6.1/jexcel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jsuites/5.13.3/jsuites.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
        }

        #toolbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        #toolbar h1 {
            margin-bottom: 10px;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .btn-group {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }

        .btn:hover {
            background: #5568d3;
        }

        #spreadsheet {
            height: 500px;
        }

        #status {
            margin-top: 10px;
            padding: 10px;
            background: #e7f3ff;
            border-radius: 4px;
            font-size: 14px;
        }

    </style>
</head>
<body>
    <div id="toolbar">
        <h1>📊 Demo Spreadsheet Editor</h1>
        <p>Contoh implementasi spreadsheet editor dengan jSpreadsheet</p>
    </div>

    <div class="container">
        <div class="btn-group">
            <button class="btn" onclick="saveData()">💾 Simpan ke LocalStorage</button>
            <button class="btn" onclick="loadData()">📂 Load dari LocalStorage</button>
            <button class="btn" onclick="exportToCSV()">📥 Export CSV</button>
            <button class="btn" onclick="clearData()">🗑️ Clear</button>
        </div>

        <div id="spreadsheet"></div>

        <div id="status">Ready - Silakan edit spreadsheet!</div>
    </div>

    <!-- jSpreadsheet & Dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jexcel/4.6.1/jexcel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsuites/5.13.3/jsuites.js"></script>

    <script>
        // Sample data
        const sampleData = [
            ['No', 'Nama Mahasiswa', 'NIM', 'IPK', 'Status']
            , ['1', 'Ahmad Fadhil', '12345001', '3.75', 'Aktif']
            , ['2', 'Budi Santoso', '12345002', '3.50', 'Aktif']
            , ['3', 'Citra Dewi', '12345003', '3.90', 'Aktif']
            , ['4', 'Dimas Prakoso', '12345004', '3.25', 'Cuti']
            , ['5', 'Eka Putri', '12345005', '3.65', 'Aktif']
        , ];

        // Initialize spreadsheet
        let spreadsheet = jexcel(document.getElementById('spreadsheet'), {
            data: sampleData
            , columns: [{
                    type: 'text'
                    , width: 50
                    , title: 'No'
                }
                , {
                    type: 'text'
                    , width: 200
                    , title: 'Nama Mahasiswa'
                }
                , {
                    type: 'text'
                    , width: 120
                    , title: 'NIM'
                }
                , {
                    type: 'numeric'
                    , width: 80
                    , title: 'IPK'
                }
                , {
                    type: 'dropdown'
                    , width: 100
                    , title: 'Status'
                    , source: ['Aktif', 'Cuti', 'Lulus', 'DO']
                }
            ]
            , minDimensions: [5, 10]
            , allowInsertRow: true
            , allowInsertColumn: false
            , allowDeleteRow: true
            , allowDeleteColumn: false
            , contextMenu: true
            , onchange: function(instance, cell, x, y, value) {
                updateStatus('Data diubah di cell ' + cell);
            }
        });

        // Save to localStorage
        function saveData() {
            const data = spreadsheet.getData();
            localStorage.setItem('spreadsheetData', JSON.stringify(data));
            updateStatus('✓ Data berhasil disimpan ke LocalStorage!');
        }

        // Load from localStorage
        function loadData() {
            const savedData = localStorage.getItem('spreadsheetData');
            if (savedData) {
                spreadsheet.setData(JSON.parse(savedData));
                updateStatus('✓ Data berhasil dimuat dari LocalStorage!');
            } else {
                updateStatus('✗ Tidak ada data tersimpan');
            }
        }

        // Export to CSV
        function exportToCSV() {
            const csv = spreadsheet.copy(false, ',', true);
            const blob = new Blob([csv], {
                type: 'text/csv'
            });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'spreadsheet_export.csv';
            link.click();
            updateStatus('✓ CSV berhasil diexport!');
        }

        // Clear data
        async function clearData() {
            if (await swalConfirmSubmit('warning', 'Yakin ingin menghapus semua data?')) {
                spreadsheet.setData(sampleData);
                localStorage.removeItem('spreadsheetData');
                updateStatus('✓ Data dikembalikan ke sample data');
            }
        }

        // Update status message
        function updateStatus(message) {
            document.getElementById('status').textContent = message;
            setTimeout(() => {
                document.getElementById('status').textContent = 'Ready';
            }, 3000);
        }

        // Auto-save setiap 30 detik
        setInterval(() => {
            saveData();
        }, 30000);

        console.log('Spreadsheet editor ready!');
        console.log('Tips:');
        console.log('- Klik kanan untuk context menu');
        console.log('- Drag untuk select multiple cells');
        console.log('- Auto-save setiap 30 detik');

    </script>
</body>
</html>
