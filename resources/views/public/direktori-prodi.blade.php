{{-- resources/views/public/direktori-prodi.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direktori Program Studi – LAMDEPILAR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <style>
        :root {
            --ink: #1a1a2e;
            --ink-mid: #3d3d5c;
            --ink-light: #7b7b9d;
            --surface: #f7f6f3;
            --surface-2: #eeecea;
            --accent: #c0392b;
            --accent-2: #e74c3c;
            --gold: #d4a017;
            --green: #1a7a4a;
            --blue: #1a4a7a;
            --radius: 12px;
            --radius-lg: 20px;
            --shadow: 0 2px 16px rgba(26, 26, 46, .08);
            --shadow-lg: 0 8px 40px rgba(26, 26, 46, .14);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Montserrat', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--surface);
            color: var(--ink);
            font-size: 15px;
            line-height: 1.6;
        }

        /* ── HEADER ── */
        .site-header {
            background: var(--ink);
            color: #fff;
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, .3);
        }

        .header-inner {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 32px;
        }

        .header-logo {
            font-size: 22px;
            color: #fff;
            letter-spacing: -.3px;
        }

        .header-logo span {
            color: var(--gold);
        }

        .header-subtitle {
            font-size: 12px;
            color: rgba(255, 255, 255, .55);
            margin-top: 2px;
        }

        .header-badge {
            margin-left: auto;
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .2);
            color: rgba(255, 255, 255, .8);
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }

        /* ── HERO ── */
        .hero {
            background: linear-gradient(135deg, var(--ink) 0%, #2d2d52 60%, #16213e 100%);
            color: #fff;
            padding: 56px 32px 48px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(192, 57, 43, .25) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -60px;
            left: 200px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(212, 160, 23, .12) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 12px;
        }

        .hero-title {
            font-size: clamp(28px, 4vw, 48px);
            line-height: 1.15;
            margin-bottom: 16px;
        }

        .hero-desc {
            font-size: 15px;
            color: rgba(255, 255, 255, .65);
            max-width: 700px;
            margin-bottom: 0;
        }

        .hero-stats {
            display: flex;
            gap: 32px;
            margin-top: 32px;
            flex-wrap: wrap;
        }

        .hero-stat-item {
            text-align: center;
        }

        .hero-stat-num {
            font-size: 36px;
            color: #fff;
            line-height: 1;
        }

        .hero-stat-label {
            font-size: 11px;
            color: rgba(255, 255, 255, .5);
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: .8px;
        }

        .hero-stat-divider {
            width: 1px;
            background: rgba(255, 255, 255, .15);
            align-self: stretch;
        }

        /* ── STAT CARDS ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            padding: 32px;
        }

        .stat-card {
            background: #fff;
            border-radius: var(--radius);
            padding: 20px 22px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .stat-body {
            flex: 1;
            min-width: 0;
        }

        .stat-num {
            font-size: 28px;
            line-height: 1;
            color: var(--ink);
        }

        .stat-label {
            font-size: 12px;
            color: var(--ink-light);
            margin-top: 4px;
            line-height: 1.3;
        }

        /* ── SECTION ── */
        .section {
            padding: 0 32px 32px;
        }

        .section-title {
            font-size: 22px;
            color: var(--ink);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--surface-2);
        }

        /* ── CHART CARDS ── */
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .chart-card {
            background: #fff;
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow);
        }

        .chart-card-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink-mid);
            text-transform: uppercase;
            letter-spacing: .8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chart-card-title i {
            font-size: 14px;
            color: var(--accent);
        }

        .chart-wrap {
            position: relative;
        }

        /* ── FILTER PANEL ── */
        .filter-panel {
            background: #fff;
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }

        .filter-panel-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--surface-2);
        }

        .filter-panel-title {
            font-weight: 600;
            font-size: 15px;
            color: var(--ink);
            flex: 1;
        }

        .filter-toggle {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--ink-light);
            font-size: 18px;
            padding: 0;
            transition: transform .2s;
        }

        .filter-toggle.collapsed {
            transform: rotate(180deg);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .filter-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-mid);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .filter-control {
            width: 100%;
            border: 1.5px solid var(--surface-2);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            color: var(--ink);
            background: var(--surface);
            transition: border-color .2s;
            outline: none;
        }

        .filter-control:focus {
            border-color: var(--accent);
            background: #fff;
        }

        .search-wrap {
            position: relative;
            grid-column: 1 / -1;
        }

        .search-input {
            width: 100%;
            padding: 12px 44px 12px 16px;
            border: 2px solid var(--surface-2);
            border-radius: 10px;
            font-size: 15px;
            color: var(--ink);
            background: var(--surface);
            outline: none;
            transition: border-color .2s, background .2s;
        }

        .search-input:focus {
            border-color: var(--accent);
            background: #fff;
        }

        .search-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--ink-light);
            font-size: 18px;
            pointer-events: none;
        }

        .btn-filter-apply {
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 9px 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, transform .15s;
        }

        .btn-filter-apply:hover {
            background: var(--accent-2);
            transform: translateY(-1px);
        }

        .btn-filter-reset {
            background: var(--surface-2);
            color: var(--ink-mid);
            border: none;
            border-radius: 8px;
            padding: 9px 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }

        .btn-filter-reset:hover {
            background: #ddd;
        }

        /* Active filter chips */
        .filter-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .filter-chip {
            background: rgba(192, 57, 43, .1);
            color: var(--accent);
            border: 1px solid rgba(192, 57, 43, .2);
            border-radius: 20px;
            padding: 3px 10px 3px 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .filter-chip button {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--accent);
            padding: 0;
            font-size: 14px;
            line-height: 1;
            display: flex;
        }

        /* ── TABLE ── */
        .table-card {
            background: #fff;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .table-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--surface-2);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .table-card-title {
            font-weight: 600;
            font-size: 16px;
            color: var(--ink);
            flex: 1;
        }

        .result-count {
            font-size: 13px;
            color: var(--ink-light);
            background: var(--surface);
            padding: 3px 10px;
            border-radius: 20px;
        }

        #prodiTable {
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0;
        }

        #prodiTable thead th {
            background: var(--surface);
            color: var(--ink-mid);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            padding: 12px 16px;
            border-bottom: 2px solid var(--surface-2);
            white-space: nowrap;
        }

        #prodiTable tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--surface-2);
            vertical-align: middle;
            font-size: 14px;
        }

        #prodiTable tbody tr:last-child td {
            border-bottom: none;
        }

        #prodiTable tbody tr:hover td {
            background: rgba(192, 57, 43, .03);
        }

        .prodi-name {
            font-weight: 600;
            color: var(--ink);
            font-size: 14px;
        }

        .prodi-univ {
            font-size: 12px;
            color: var(--ink-light);
            margin-top: 2px;
        }

        .badge-jenjang {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            background: rgba(26, 74, 122, .1);
            color: var(--blue);
        }

        .badge-peringkat {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .p-unggul {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
        }

        .p-baik-sekali {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: #fff;
        }

        .p-baik {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: #fff;
        }

        .p-c {
            background: linear-gradient(135deg, #fa709a, #fee140);
            color: #fff;
        }

        .p-none {
            background: var(--surface-2);
            color: var(--ink-light);
        }

        .status-dot {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-dot::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-aktif {
            color: var(--green);
        }

        .status-aktif::before {
            background: var(--green);
        }

        .status-kedaluwarsa {
            color: var(--accent);
        }

        .status-kedaluwarsa::before {
            background: var(--accent);
        }

        .status-belum {
            color: var(--ink-light);
        }

        .status-belum::before {
            background: var(--ink-light);
        }

        .days-bar-wrap {
            width: 100px;
        }

        .days-bar {
            height: 4px;
            border-radius: 2px;
            background: var(--surface-2);
            overflow: hidden;
            margin-bottom: 3px;
        }

        .days-bar-fill {
            height: 100%;
            border-radius: 2px;
            transition: width .4s;
        }

        .days-label {
            font-size: 11px;
            color: var(--ink-light);
        }

        /* DataTables override */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--accent) !important;
            color: #fff !important;
            border-color: var(--accent) !important;
            border-radius: 6px !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--surface-2) !important;
            color: var(--ink) !important;
            border-color: var(--surface-2) !important;
            border-radius: 6px !important;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_paginate {
            font-size: 13px;
            color: var(--ink-light);
            padding: 16px 24px;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1.5px solid var(--surface-2);
            border-radius: 6px;
            padding: 4px 8px;
            color: var(--ink);
            background: var(--surface);
        }

        /* ── FOOTER ── */
        .site-footer {
            background: #932136;
            color: rgba(255, 255, 255, .45);
            text-align: center;
            padding: 24px 32px;
            font-size: 13px;
            margin-top: 48px;
        }

        .site-footer strong {
            color: rgba(255, 255, 255, .75);
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .header-inner {
                padding: 12px 16px;
            }

            .hero {
                padding: 36px 16px 32px;
            }

            .hero-stats {
                gap: 20px;
            }

            .stat-grid {
                padding: 16px;
                gap: 12px;
            }

            .section {
                padding: 0 16px 24px;
            }

            .filter-panel,
            .chart-card {
                padding: 16px;
            }

            .chart-grid {
                grid-template-columns: 1fr;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ── LOADING ── */
        .skeleton {
            background: linear-gradient(90deg, var(--surface-2) 25%, #e8e6e2 50%, var(--surface-2) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 6px;
        }

        @keyframes shimmer {
            to {
                background-position: -200% 0;
            }
        }

        /* Select2 overrides */
        .select2-container--bootstrap-5 .select2-selection {
            border: 1.5px solid var(--surface-2) !important;
            border-radius: 8px !important;
            background: var(--surface) !important;
            font-size: 13px !important;
        }

        .select2-container--bootstrap-5 .select2-selection:focus-within {
            border-color: var(--accent) !important;
            background: #fff !important;
        }

    </style>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/template.css') }}">
</head>
<body>

    @include('layouts.template.navbar')

    <!-- ── HERO ── -->
    <section class="hero">
        <div style="max-width:1000px;margin:0 auto;position:relative;z-index:1;">
            <div class="hero-label mb-5">
                {{-- <i class="bi bi-mortarboard"></i> Direktori Program Studi --}}
            </div>
            <h2 class="hero-title">Data Akreditasi Program Studi</h2>
            <p class="hero-desc">
                Temukan informasi status akreditasi program studi di seluruh perguruan tinggi yang terdata di LAMDEPILAR.
                Data diperbarui secara berkala.
            </p>
            <div class="hero-stats">
                <div class="hero-stat-item">
                    <div class="hero-stat-num" id="heroTotal">{{ $stats['total'] }}</div>
                    <div class="hero-stat-label">Program Studi</div>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat-item">
                    <div class="hero-stat-num">{{ $stats['total_universitas'] }}</div>
                    <div class="hero-stat-label">Perguruan Tinggi</div>
                </div>
                {{-- <div class="hero-stat-divider"></div> --}}
                {{-- <div class="hero-stat-item">
                    <div class="hero-stat-num">{{ $stats['aktif'] }}
            </div>
            <div class="hero-stat-label">Terakreditasi Aktif</div>
        </div> --}}
        <div class="hero-stat-divider"></div>
        <div class="hero-stat-item">
            <div class="hero-stat-num">{{ $stats['unggul'] }}</div>
            <div class="hero-stat-label">Terakreditasi Unggul</div>
        </div>
        </div>
        </div>
    </section>

    <!-- ── STAT CARDS ── -->
    <div class="stat-grid" style="max-width:1400px;margin:0 auto;">
        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(192,57,43,.1);">
                <i class="bi bi-check-circle-fill" style="color:var(--accent);"></i>
            </div>
            <div class="stat-body">
                <div class="stat-num">{{ $stats['aktif'] }}</div>
                <div class="stat-label">Akreditasi Aktif</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(212,160,23,.1);">
                <i class="bi bi-exclamation-triangle-fill" style="color:var(--gold);"></i>
            </div>
            <div class="stat-body">
                <div class="stat-num">{{ $stats['segera_berakhir'] }}</div>
                <div class="stat-label">Berakhir &lt;6 Bulan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(231,76,60,.1);">
                <i class="bi bi-x-circle-fill" style="color:#e74c3c;"></i>
            </div>
            <div class="stat-body">
                <div class="stat-num">{{ $stats['kedaluwarsa'] }}</div>
                <div class="stat-label">Kedaluwarsa</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(103,126,234,.1);">
                <i class="bi bi-trophy-fill" style="color:#667eea;"></i>
            </div>
            <div class="stat-body">
                <div class="stat-num">{{ $stats['unggul'] }}</div>
                <div class="stat-label">Terakreditasi Unggul</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(17,153,142,.1);">
                <i class="bi bi-star-fill" style="color:#11998e;"></i>
            </div>
            <div class="stat-body">
                <div class="stat-num">{{ $stats['baik_sekali'] }}</div>
                <div class="stat-label">Baik Sekali</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(79,172,254,.1);">
                <i class="bi bi-hand-thumbs-up-fill" style="color:#4facfe;"></i>
            </div>
            <div class="stat-body">
                <div class="stat-num">{{ $stats['baik'] }}</div>
                <div class="stat-label">Baik</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(108,117,125,.1);">
                <i class="bi bi-hourglass-split" style="color:#6c757d;"></i>
            </div>
            <div class="stat-body">
                <div class="stat-num">{{ $stats['belum_terakreditasi'] }}</div>
                <div class="stat-label">Belum Terakreditasi</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(26,26,46,.08);">
                <i class="bi bi-building" style="color:var(--ink-mid);"></i>
            </div>
            <div class="stat-body">
                <div class="stat-num">{{ $stats['total_universitas'] }}</div>
                <div class="stat-label">Perguruan Tinggi</div>
            </div>
        </div>
    </div>

    <!-- ── CHARTS ── -->
    <div class="section" style="max-width:1400px;margin:0 auto;">
        <h2 class="section-title"><i class="bi bi-bar-chart-line-fill" style="color:var(--accent);font-size:18px;"></i> Statistik & Visualisasi</h2>

        <div class="chart-grid">
            <!-- Donut: Distribusi Peringkat -->
            <div class="chart-card">
                <div class="chart-card-title"><i class="bi bi-pie-chart-fill"></i> Distribusi Peringkat</div>
                <div class="chart-wrap" style="height:240px;">
                    <canvas id="chartPeringkat"></canvas>
                </div>
                <div id="legendPeringkat" style="margin-top:16px;display:flex;flex-wrap:wrap;gap:8px;"></div>
            </div>

            <!-- Bar: Distribusi Jenjang -->
            <div class="chart-card">
                <div class="chart-card-title"><i class="bi bi-mortarboard"></i> Sebaran Jenjang</div>
                <div class="chart-wrap" style="height:240px;">
                    <canvas id="chartJenjang"></canvas>
                </div>
            </div>

            <!-- Bar horizontal: Top Universitas -->
            <div class="chart-card">
                <div class="chart-card-title"><i class="bi bi-building"></i> Top 10 Perguruan Tinggi</div>
                <div class="chart-wrap" style="height:240px;">
                    <canvas id="chartUniversitas"></canvas>
                </div>
            </div>

            <!-- Line: Tren Kedaluwarsa per Bulan (12 bulan ke depan) -->
            <div class="chart-card">
                <div class="chart-card-title"><i class="bi bi-calendar-week"></i> Kedaluwarsa 12 Bulan ke Depan</div>
                <div class="chart-wrap" style="height:240px;">
                    <canvas id="chartTren"></canvas>
                </div>
            </div>

            <!-- Donut: Status Akreditasi -->
            <div class="chart-card">
                <div class="chart-card-title"><i class="bi bi-patch-check"></i> Status Akreditasi</div>
                <div class="chart-wrap" style="height:240px;">
                    <canvas id="chartStatus"></canvas>
                </div>
                <div id="legendStatus" style="margin-top:16px;display:flex;flex-wrap:wrap;gap:8px;"></div>
            </div>

            <!-- Bar: Sebaran per Rumpun -->
            {{-- <div class="chart-card">
                <div class="chart-card-title"><i class="bi bi-diagram-3"></i> Sebaran Rumpun</div>
                <div class="chart-wrap" style="height:240px;">
                    <canvas id="chartRumpun"></canvas>
                </div>
            </div> --}}
        </div>
    </div>

    <!-- ── FILTER + TABLE ── -->
    <div class="section" style="max-width:1400px;margin:0 auto;">
        <h2 class="section-title"><i class="bi bi-table" style="color:var(--accent);font-size:18px;"></i> Daftar Program Studi</h2>

        <!-- Filter panel -->
        <div class="filter-panel">
            <div class="filter-panel-header">
                <i class="bi bi-funnel-fill" style="color:var(--accent);"></i>
                <span class="filter-panel-title">Filter & Pencarian</span>
                <span id="activeFilterCount" class="result-count d-none">0 filter aktif</span>
                <button class="filter-toggle" id="filterToggle" onclick="toggleFilter()">
                    <i class="bi bi-chevron-up"></i>
                </button>
            </div>

            <div id="filterBody">
                <div class="filter-grid">
                    <!-- Search -->
                    <div class="search-wrap">
                        <input type="text" id="globalSearch" class="search-input" placeholder="Cari nama prodi, perguruan tinggi, kode..." autocomplete="off">
                        <i class="bi bi-search search-icon"></i>
                    </div>

                    <!-- Universitas -->
                    <div>
                        <div class="filter-label">Perguruan Tinggi</div>
                        <select id="filterUniv" class="filter-control" multiple>
                            @foreach($universities as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Jenjang -->
                    <div>
                        <div class="filter-label">Jenjang</div>
                        <select id="filterJenjang" class="filter-control" multiple>
                            @foreach($degreeLevels as $d)
                            <option value="{{ $d->id }}">{{ $d->alias }} – {{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Peringkat -->
                    <div>
                        <div class="filter-label">Status Akreditasi</div>
                        <select id="filterPeringkat" class="filter-control" multiple>
                            @foreach($peringkatList as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                            <option value="(Belum)">Belum Ada</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <div class="filter-label">Status Kedaluwarsa</div>
                        <select id="filterStatus" class="filter-control">
                            <option value="">Semua Status</option>
                            <option value="Aktif">Aktif</option>
                            <option value="Kedaluwarsa">Kedaluwarsa</option>
                            <option value="Belum Terakreditasi">Belum Terakreditasi</option>
                        </select>
                    </div>

                    <!-- Tahun Kedaluwarsa -->
                    <div>
                        <div class="filter-label">Tahun Kedaluwarsa</div>
                        <select id="filterTahun" class="filter-control" multiple>
                            @php $cy = now()->year; @endphp
                            @for($y = $cy - 2; $y <= $cy + 10; $y++) <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                        </select>
                    </div>

                    <!-- Rumpun -->
                    <div>
                        <div class="filter-label">Rumpun</div>
                        <select id="filterRumpun" class="filter-control">
                            <option value="">Semua Rumpun</option>
                            <option value="desain">Desain</option>
                            <option value="perencanaan">Perencanaan</option>
                            <option value="lingkungan">Lingkungan</option>
                            <option value="arsitektur">Arsitektur</option>
                        </select>
                    </div>
                </div>

                <!-- Action buttons -->
                <div style="display:flex;gap:10px;margin-top:20px;align-items:center;flex-wrap:wrap;">
                    <button class="btn-filter-apply" onclick="applyFilter()">
                        <i class="bi bi-search"></i> Terapkan Filter
                    </button>
                    <button class="btn-filter-reset" onclick="resetFilter()">
                        <i class="bi bi-x-circle"></i> Reset
                    </button>
                </div>

                <!-- Active chips -->
                <div class="filter-chips" id="filterChips"></div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-card">
            <div class="table-card-header">
                <span class="table-card-title">
                    <i class="bi bi-list-ul" style="color:var(--accent);"></i> Hasil Pencarian
                </span>
                <span class="result-count" id="resultCount">Memuat...</span>
            </div>
            <div style="overflow-x:auto;">
                <table id="prodiTable" class="table mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Program Studi</th>
                            <th>Jenjang</th>
                            <th>Status Akreditasi</th>
                            {{-- <th>Status</th> --}}
                            <th>Kedaluwarsa</th>
                            <th>Sisa Waktu</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── FOOTER ── -->
    <footer class="site-footer">
        <strong>LAMDEPILAR</strong> &nbsp;·&nbsp; Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur
        &nbsp;·&nbsp; Data diperbarui: {{ now()->translatedFormat('d F Y') }}
    </footer>

    <!-- ── SCRIPTS ── -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <script>
        var _lastJson = null;
        // =========================================================
        // DATA DARI CONTROLLER (inject via Blade)
        // =========================================================
        var CHART_DATA = {
            peringkat: @json($chartData['peringkat'])
            , jenjang: @json($chartData['jenjang'])
            , universitas: @json($chartData['universitas'])
            , tren: @json($chartData['tren'])
            , status: @json($chartData['status'])
            , rumpun: @json($chartData['rumpun'])
        };

        var ROUTE_AJAX = '{{ route("public.prodi.ajax") }}';

        // =========================================================
        // CHART.JS DEFAULTS
        // =========================================================
        Chart.defaults.font.family = "'DM Sans', sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#7b7b9d';
        Chart.defaults.plugins.legend.display = false;

        var PALETTE = {
            unggul: '#764ba2'
            , baikSekali: '#11998e'
            , baik: '#4facfe'
            , c: '#fa709a'
            , none: '#d1d1e0'
            , aktif: '#1a7a4a'
            , kedaluwarsa: '#c0392b'
            , belum: '#adb5bd'
            , accent: '#c0392b'
            , gold: '#d4a017'
            , blue: '#1a4a7a'
        };

        function buildLegend(containerId, labels, colors) {
            var el = document.getElementById(containerId);
            if (!el) {
                return;
            }
            el.innerHTML = '';
            labels.forEach(function(label, i) {
                var item = document.createElement('div');
                item.style.cssText = 'display:flex;align-items:center;gap:6px;font-size:12px;color:#3d3d5c;';
                item.innerHTML =
                    '<span style="width:10px;height:10px;border-radius:50%;background:' + colors[i] + ';flex-shrink:0;display:block;"></span>' +
                    label;
                el.appendChild(item);
            });
        }

        // ── Peringkat Donut ──
        (function() {
            var d = CHART_DATA.peringkat;
            var labels = Object.keys(d);
            var values = Object.values(d);
            var colors = labels.map(function(l) {
                if (l === 'Unggul') {
                    return PALETTE.unggul;
                }
                if (l === 'Baik Sekali') {
                    return PALETTE.baikSekali;
                }
                if (l === 'Baik') {
                    return PALETTE.baik;
                }
                if (l === 'C') {
                    return PALETTE.c;
                }
                return PALETTE.none;
            });

            new Chart(document.getElementById('chartPeringkat'), {
                type: 'doughnut'
                , data: {
                    labels: labels
                    , datasets: [{
                        data: values
                        , backgroundColor: colors
                        , borderWidth: 3
                        , borderColor: '#fff'
                        , hoverOffset: 6
                    }]
                }
                , options: {
                    cutout: '65%'
                    , plugins: {
                        legend: {
                            display: false
                        }
                        , tooltip: {
                            callbacks: {
                                label: function(c) {
                                    return ' ' + c.label + ': ' + c.raw + ' PS';
                                }
                            }
                        }
                    }
                    , maintainAspectRatio: false
                }
            });

            buildLegend('legendPeringkat', labels, colors);
        })();

        // ── Jenjang Bar ──
        (function() {
            var d = CHART_DATA.jenjang;
            new Chart(document.getElementById('chartJenjang'), {
                type: 'bar'
                , data: {
                    labels: Object.keys(d)
                    , datasets: [{
                        data: Object.values(d)
                        , backgroundColor: '#1a4a7a'
                        , borderRadius: 6
                        , borderSkipped: false
                    }]
                }
                , options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                    , scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        }
                        , y: {
                            grid: {
                                color: '#f0ede8'
                            }
                            , ticks: {
                                precision: 0
                            }
                        }
                    }
                    , maintainAspectRatio: false
                }
            });
        })();

        // ── Top Universitas Horizontal Bar ──
        (function() {
            var d = CHART_DATA.universitas;
            var labels = d.map(function(x) {
                var name = x.name;
                return name.length > 30 ? name.substring(0, 28) + '…' : name;
            });
            var values = d.map(function(x) {
                return x.total;
            });

            new Chart(document.getElementById('chartUniversitas'), {
                type: 'bar'
                , data: {
                    labels: labels
                    , datasets: [{
                        data: values
                        , backgroundColor: 'rgba(192,57,43,.75)'
                        , borderRadius: 4
                        , borderSkipped: false
                    }]
                }
                , options: {
                    indexAxis: 'y'
                    , plugins: {
                        legend: {
                            display: false
                        }
                    }
                    , scales: {
                        x: {
                            grid: {
                                color: '#f0ede8'
                            }
                            , ticks: {
                                precision: 0
                            }
                        }
                        , y: {
                            grid: {
                                display: false
                            }
                            , ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                    , maintainAspectRatio: false
                }
            });
        })();

        // ── Tren Kedaluwarsa Line ──
        (function() {
            var d = CHART_DATA.tren;
            new Chart(document.getElementById('chartTren'), {
                type: 'line'
                , data: {
                    labels: d.map(function(x) {
                        return x.label;
                    })
                    , datasets: [{
                        data: d.map(function(x) {
                            return x.total;
                        })
                        , borderColor: PALETTE.accent
                        , backgroundColor: 'rgba(192,57,43,.08)'
                        , borderWidth: 2.5
                        , pointRadius: 4
                        , pointBackgroundColor: '#fff'
                        , pointBorderColor: PALETTE.accent
                        , pointBorderWidth: 2
                        , tension: .35
                        , fill: true
                    }]
                }
                , options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                    , scales: {
                        x: {
                            grid: {
                                display: false
                            }
                            , ticks: {
                                font: {
                                    size: 10
                                }
                            }
                        }
                        , y: {
                            grid: {
                                color: '#f0ede8'
                            }
                            , ticks: {
                                precision: 0
                            }
                        }
                    }
                    , maintainAspectRatio: false
                }
            });
        })();

        // ── Status Donut ──
        (function() {
            var d = CHART_DATA.status;
            var labels = Object.keys(d);
            var values = Object.values(d);
            var colors = labels.map(function(l) {
                if (l === 'Aktif') {
                    return PALETTE.aktif;
                }
                if (l === 'Kedaluwarsa') {
                    return PALETTE.kedaluwarsa;
                }
                if (l === 'Belum Terakreditasi') {
                    return PALETTE.belum;
                }
                return PALETTE.none;
            });

            new Chart(document.getElementById('chartStatus'), {
                type: 'doughnut'
                , data: {
                    labels: labels
                    , datasets: [{
                        data: values
                        , backgroundColor: colors
                        , borderWidth: 3
                        , borderColor: '#fff'
                        , hoverOffset: 6
                    }]
                }
                , options: {
                    cutout: '65%'
                    , plugins: {
                        legend: {
                            display: false
                        }
                        , tooltip: {
                            callbacks: {
                                label: function(c) {
                                    return ' ' + c.label + ': ' + c.raw + ' PS';
                                }
                            }
                        }
                    }
                    , maintainAspectRatio: false
                }
            });

            buildLegend('legendStatus', labels, colors);
        })();

        // ── Rumpun Bar ──
        (function() {
            var chartRumpun = document.getElementById('chartRumpun');
            if (!chartRumpun) {
                return;
            }

            var d = CHART_DATA.rumpun;
            var colors = ['#764ba2', '#11998e', '#d4a017', '#c0392b'];
            new Chart(chartRumpun, {
                type: 'bar'
                , data: {
                    labels: Object.keys(d).map(function(k) {
                        return k.charAt(0).toUpperCase() + k.slice(1);
                    })
                    , datasets: [{
                        data: Object.values(d)
                        , backgroundColor: colors
                        , borderRadius: 6
                        , borderSkipped: false
                    }]
                }
                , options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                    , scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        }
                        , y: {
                            grid: {
                                color: '#f0ede8'
                            }
                            , ticks: {
                                precision: 0
                            }
                        }
                    }
                    , maintainAspectRatio: false
                }
            });
        })();

        // =========================================================
        // SELECT2
        // =========================================================
        $('#filterUniv, #filterJenjang, #filterPeringkat, #filterTahun').select2({
            theme: 'bootstrap-5'
            , placeholder: 'Pilih...'
            , allowClear: true
            , width: '100%'
            , closeOnSelect: false
            , language: {
                noResults: function() {
                    return 'Tidak ada hasil';
                }
                , searching: function() {
                    return 'Mencari...';
                }
            }
        });

        // =========================================================
        // DATATABLE
        // =========================================================
        var table = null;

        function emptyParams() {
            return {
                search_text: ''
                , university_id: []
                , degree_level_id: []
                , peringkat: []
                , status: ''
                , tahun: []
                , rumpun: ''
            };
        }

        function initTable(params) {
            activeParams = Object.assign(emptyParams(), params || {});

            if (table) {
                table.destroy();
                table = null;
            }

            table = $('#prodiTable').DataTable({
                processing: true
                , serverSide: true
                , ajax: {
                    url: ROUTE_AJAX
                    , type: 'GET'
                    , data: function(d) {
                        var params = Object.assign(emptyParams(), activeParams || {});

                        if (params.search_text) {
                            d.search_text = params.search_text;
                        }
                        if (params.university_id.length) {
                            d.university_id = params.university_id;
                        }
                        if (params.degree_level_id.length) {
                            d.degree_level_id = params.degree_level_id;
                        }
                        if (params.peringkat.length) {
                            d.peringkat = params.peringkat;
                        }
                        if (params.status) {
                            d.status = params.status;
                        }
                        if (params.tahun.length) {
                            d.tahun = params.tahun;
                        }
                        if (params.rumpun) {
                            d.rumpun = params.rumpun;
                        }

                        return d;
                    }
                    , dataSrc: function(json) {
                        _lastJson = json; // simpan, update setelah draw
                        return json.data;
                    }
                }
                , columns: [{
                        data: 'number'
                        , orderable: false
                        , searchable: false
                        , className: 'text-center text-muted'
                        , width: '40px'
                    }
                    , {
                        data: 'name'
                        , render: function(data, type, row) {
                            return '<div class="prodi-name">' + row.name + '</div>' +
                                '<div class="prodi-univ"><i class="bi bi-building" style="font-size:10px;"></i> ' + row.university + '</div>';
                        }
                    }
                    , {
                        data: 'jenjang'
                        , render: function(data) {
                            return '<span class="badge-jenjang">' + (data || '-') + '</span>';
                        }
                    }
                    , {
                        data: 'peringkat'
                        , render: function(data) {
                            if (!data || data === '-') {
                                return '<span class="badge-peringkat p-none">—</span>';
                            }
                            var cls = 'p-none';
                            if (data === 'Unggul') {
                                cls = 'p-unggul';
                            } else if (data === 'Baik Sekali') {
                                cls = 'p-baik-sekali';
                            } else if (data === 'Baik') {
                                cls = 'p-baik';
                            } else if (data === 'C') {
                                cls = 'p-c';
                            }
                            return '<span class="badge-peringkat ' + cls + '">' + data + '</span>';
                        }
                    }
                    , {
                        data: 'tanggal_kedaluwarsa'
                        , render: function(data) {
                            if (!data || data === '-') {
                                return '<small class="text-muted">—</small>';
                            }
                            return '<small>' + data + '</small>';
                        }
                    }
                    , {
                        data: 'sisa_hari'
                        , orderable: false
                        , render: function(data, type, row) {
                            if (data === null) {
                                return '<small class="text-muted">—</small>';
                            }
                            if (data < 0) {
                                return '<small class="text-danger fw-semibold">Expired</small>';
                            }

                            var pct = Math.min(100, Math.max(0, row.progress));
                            var color = data > 180 ? '#1a7a4a' : data > 60 ? '#d4a017' : '#c0392b';
                            var label = data > 365 ?
                                Math.floor(data / 365) + ' thn ' + Math.floor((data % 365) / 30) + ' bln' :
                                data + ' hari';

                            return '<div class="days-bar-wrap">' +
                                '<div class="days-bar"><div class="days-bar-fill" style="width:' + pct + '%;background:' + color + ';"></div></div>' +
                                '<div class="days-label">' + label + '</div>' +
                                '</div>';
                        }
                    }
                ]
                , order: [
                    [5, 'asc']
                ]
                , pageLength: 25
                , lengthMenu: [
                    [10, 25, 50, 100]
                    , [10, 25, 50, 100]
                ]
                , language: {
                    processing: 'Memuat data...'
                    , search: ''
                    , searchPlaceholder: 'Cari...'
                    , lengthMenu: 'Tampilkan _MENU_ data'
                    , info: 'Menampilkan _START_–_END_ dari _TOTAL_ program studi'
                    , infoEmpty: 'Tidak ada data'
                    , infoFiltered: '(difilter dari _MAX_ total)'
                    , paginate: {
                        next: 'Selanjutnya'
                        , previous: 'Sebelumnya'
                    }
                    , zeroRecords: 'Tidak ada program studi yang cocok'
                    , emptyTable: 'Tidak ada data'
                }
                , drawCallback: function() {
                    if (!_lastJson) {
                        return;
                    }

                    var info = this.api().page.info();
                    var dari = info.recordsDisplay === 0 ? 0 : info.start + 1;
                    var sampai = info.end;

                    updateResultCount(_lastJson, dari, sampai);
                }
                , initComplete: function() {
                    // Sembunyikan kolom search bawaan DataTables
                    var wrap = document.querySelector('.dataTables_filter');
                    if (wrap) {
                        wrap.style.display = 'none';
                    }
                }
            });
        }

        function updateResultCount(json, dari, sampai) {
            var el = document.getElementById('resultCount');
            if (!el) {
                return;
            }

            var filtered = json.recordsFiltered || 0;
            var total = json.recordsTotal || 0;

            if (filtered === 0) {
                el.textContent = '0 program studi ditemukan';
                return;
            }

            var teks = 'Menampilkan ' + dari + '\u2013' + sampai + ' dari ' + filtered + ' program studi';

            if (filtered < total) {
                teks += ' (difilter dari ' + total + ')';
            }

            el.textContent = teks;
        }

        // =========================================================
        // FILTER LOGIC
        // =========================================================
        var activeParams = emptyParams();
        var filterVisible = true;
        var suppressFilterApply = false;

        function toggleFilter() {
            filterVisible = !filterVisible;
            var body = document.getElementById('filterBody');
            var toggle = document.getElementById('filterToggle');
            body.style.display = filterVisible ? '' : 'none';
            toggle.classList.toggle('collapsed', !filterVisible);
        }

        function getParams() {
            return {
                search_text: document.getElementById('globalSearch').value.trim()
                , university_id: $('#filterUniv').val() || []
                , degree_level_id: $('#filterJenjang').val() || []
                , peringkat: $('#filterPeringkat').val() || []
                , status: $('#filterStatus').val() || ''
                , tahun: $('#filterTahun').val() || []
                , rumpun: $('#filterRumpun').val() || ''
            };
        }

        function applyFilter() {
            activeParams = getParams();
            updateChips();
            updateActiveCount();

            if (table) {
                table.ajax.reload();
            } else {
                initTable(activeParams);
            }
        }

        function resetFilter() {
            suppressFilterApply = true;
            document.getElementById('globalSearch').value = '';
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterRumpun').value = '';
            $('#filterUniv, #filterJenjang, #filterPeringkat, #filterTahun').val(null).trigger('change');
            suppressFilterApply = false;

            activeParams = emptyParams();
            updateChips();
            updateActiveCount();

            if (table) {
                table.ajax.reload();
            } else {
                initTable({});
            }
        }

        function updateActiveCount() {
            var p = getParams();
            var count = 0;
            if (p.search_text) {
                count++;
            }
            if (p.university_id.length) {
                count++;
            }
            if (p.degree_level_id.length) {
                count++;
            }
            if (p.peringkat.length) {
                count++;
            }
            if (p.status) {
                count++;
            }
            if (p.tahun.length) {
                count++;
            }
            if (p.rumpun) {
                count++;
            }

            var badge = document.getElementById('activeFilterCount');
            if (count > 0) {
                badge.textContent = count + ' filter aktif';
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        }

        function updateChips() {
            var p = getParams();
            var chips = document.getElementById('filterChips');
            var html = '';

            if (p.search_text) {
                html += makeChip('"' + p.search_text + '"', 'search_text');
            }
            if (p.status) {
                html += makeChip(p.status, 'status');
            }
            if (p.rumpun) {
                html += makeChip(p.rumpun.charAt(0).toUpperCase() + p.rumpun.slice(1), 'rumpun');
            }
            if (p.university_id.length) {
                html += makeChip(p.university_id.length + ' Perguruan Tinggi', 'university_id');
            }
            if (p.degree_level_id.length) {
                html += makeChip(p.degree_level_id.length + ' Jenjang', 'degree_level_id');
            }
            if (p.peringkat.length) {
                html += makeChip(p.peringkat.length + ' Peringkat', 'peringkat');
            }
            if (p.tahun.length) {
                html += makeChip(p.tahun.length + ' Tahun', 'tahun');
            }

            chips.innerHTML = html;
        }

        function makeChip(label, key) {
            return '<span class="filter-chip">' +
                label +
                '<button onclick="clearChip(\'' + key + '\')" title="Hapus filter">' +
                '<i class="bi bi-x"></i>' +
                '</button>' +
                '</span>';
        }

        function clearChip(key) {
            if (key === 'search_text') {
                document.getElementById('globalSearch').value = '';
            } else if (key === 'status') {
                document.getElementById('filterStatus').value = '';
            } else if (key === 'rumpun') {
                document.getElementById('filterRumpun').value = '';
            } else if (key === 'university_id') {
                $('#filterUniv').val(null).trigger('change');
            } else if (key === 'degree_level_id') {
                $('#filterJenjang').val(null).trigger('change');
            } else if (key === 'peringkat') {
                $('#filterPeringkat').val(null).trigger('change');
            } else if (key === 'tahun') {
                $('#filterTahun').val(null).trigger('change');
            }

            applyFilter();
        }

        // ── Search debounce ──
        var searchDebounce;
        document.getElementById('globalSearch').addEventListener('input', function() {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(function() {
                applyFilter();
            }, 450);
        });

        // ── Pilihan lain auto-apply ──
        ['filterStatus', 'filterRumpun'].forEach(function(id) {
            document.getElementById(id).addEventListener('change', function() {
                if (suppressFilterApply) {
                    return;
                }

                applyFilter();
            });
        });
        $('#filterUniv, #filterJenjang, #filterPeringkat, #filterTahun').on('change', function() {
            if (suppressFilterApply) {
                return;
            }

            applyFilter();
        });

        // ── Init on load ──
        document.addEventListener('DOMContentLoaded', function() {
            suppressFilterApply = true;
            document.getElementById('globalSearch').value = '';
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterRumpun').value = '';
            $('#filterUniv, #filterJenjang, #filterPeringkat, #filterTahun').val(null).trigger('change');
            suppressFilterApply = false;

            activeParams = emptyParams();
            updateChips();
            updateActiveCount();
            initTable(emptyParams());
        });

    </script>
</body>
</html>
