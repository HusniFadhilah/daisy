<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Akreditasi LAMDEPILAR</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ===== MULTI-PAGE SYSTEM ===== */
        html,
        body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            position: fixed;
            background: #cdc5ab;
            font-family: 'Montserrat', sans-serif;
        }

        .scaler-wrap {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .scaler {
            position: relative;
            width: 1122px;
            height: 794px;
            transform-origin: top left;
            flex-shrink: 0;
        }

        .page {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .page.active {
            display: flex;
        }

        /* ═══════════════════════════════════════
           CERTIFICATE WRAPPER — dari doc 4, TIDAK DIUBAH
        ═══════════════════════════════════════ */
        .cert-wrap {
            width: 1082px;
            height: 750px;
            background: #fbf9f7;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        .cert-border-svg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 20;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 320px;
            height: 320px;
            opacity: 0.055;
            pointer-events: none;
            z-index: 1;
        }

        .deco-pattern {
            width: 185px;
            bottom: 170px;
        }

        .deco-pattern,
        .deco-pattern2,
        .deco-pattern3 {
            position: absolute;
            right: 18px;
            height: 155px;
            pointer-events: none;
            z-index: 1;
        }

        .deco-pattern2,
        .deco-pattern3 {
            width: 105px;
            bottom: 12px;
            z-index: 5;
            opacity: 0.8;
        }

        .cert-inner {
            position: relative;
            padding: 75px 52px 24px 52px;
            z-index: 5;
            height: 750px;
            display: flex;
            flex-direction: column;
        }

        /* Padding lebih kecil untuk halaman lampiran (lebih banyak konten) */
        .cert-inner--app {
            padding-top: 50px;
        }

        /* ── Header ── */
        .cert-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-shrink: 0;
        }

        .logo-left {
            padding-left: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 210px;
            flex-shrink: 0;
        }

        .logo-icon img {
            width: 50px;
            height: 50px;
            display: block;
            object-fit: contain;
        }

        .logo-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 4px;
            color: #9B0F1B;
            line-height: 1;
            white-space: nowrap;
        }

        .header-center {
            text-align: center;
            flex: 1;
            padding-top: 2px;
        }

        .header-center .line1 {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.8px;
            color: #1a1a1a;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .header-center .line2 {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a1a;
            text-transform: uppercase;
            margin-top: 2px;
            white-space: nowrap;
        }

        .header-center .line3 {
            font-size: 14px;
            font-weight: 700;
            color: #9B0F1B;
            margin-top: 3px;
        }

        .diamond-divider,
        .diamond-divider2,
        .diamond-divider3 {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0 0 0;
        }

        .diamond-divider3,
        .p2-footer {
            transform: translateY(-20px);
        }

        .diamond-divider .dline,
        .diamond-divider2 .dline,
        .diamond-divider3 .dline {
            height: 0.5px;
            background: #C18A2A;
            display: block;
        }

        .diamond-divider .dline {
            width: 210px;
        }

        .diamond-divider2 .dline {
            width: 500px;
        }

        .diamond-divider3 .dline {
            width: 450px;
        }

        .diamond-divider .dia,
        .diamond-divider2 .dia,
        .diamond-divider3 .dia {
            width: 6px;
            height: 6px;
            background: #C18A2A;
            transform: rotate(45deg);
            margin: 0 6px;
            flex-shrink: 0;
            display: block;
        }

        .header-right {
            text-align: left;
            width: 210px;
            flex-shrink: 0;
            padding-top: 5px;
        }

        .header-right .hrow {
            display: flex;
            font-size: 11px;
            color: #111;
            line-height: 1.8;
            white-space: nowrap;
        }

        .header-right .hlabel {
            width: 58px;
            flex-shrink: 0;
        }

        /* ── Halaman 1: konten (TIDAK DIUBAH dari doc 4) ── */
        .hr-status {
            border: none;
            border-top: 1px solid #C18A2A;
            margin: 0 auto 8px auto;
            width: 180px;
        }

        .cert-title {
            text-align: center;
            margin-top: 10px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 62px;
            font-weight: 500;
            letter-spacing: 3px;
            color: #8E101A;
            line-height: 1.05;
            text-transform: uppercase;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .nomor-line {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 0;
            flex-shrink: 0;
        }

        .nomor-line .nline {
            height: 0.5px;
            width: 145px;
            background: #C18A2A;
            display: block;
            flex-shrink: 0;
        }

        .nomor-line .ntext {
            font-size: 14px;
            color: #111;
            white-space: nowrap;
        }

        .nomor-line .ntext strong {
            color: #222;
            font-weight: 700;
        }

        .cert-statement {
            text-align: center;
            margin-top: 15px;
            font-size: 15px;
            color: #111;
            line-height: 1.55;
            flex-shrink: 0;
        }

        .info-table {
            width: 598px;
            margin: 12px auto 0 auto;
            position: relative;
            z-index: 2;
            flex-shrink: 0;
        }

        .info-row {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 0.5px solid #C18A2A;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-icon {
            width: 36px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .info-icon svg {
            width: 21px;
            height: 21px;
        }

        .info-label {
            width: 190px;
            flex-shrink: 0;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 3px;
            color: #111;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .info-vline {
            width: 1px;
            height: 20px;
            background: #C18A2A;
            margin: 0 18px;
            flex-shrink: 0;
            display: block;
        }

        .info-value {
            flex: 1;
            font-size: 13px;
            font-weight: 700;
            color: #222;
        }

        .watermark-logo {
            position: absolute;
            inset: 0;
            background-image: url("{{ asset('assets/images/logo-square.png') }}");
            background-repeat: no-repeat;
            background-position: center 65%;
            background-size: 35% auto;
            opacity: 0.06;
            -webkit-mask-image: linear-gradient(to bottom, black 30%, transparent 65%);
            mask-image: linear-gradient(to bottom, black 30%, transparent 65%);
            pointer-events: none;
            z-index: 0;
        }

        .status-section {
            text-align: center;
            margin-top: 10px;
            position: relative;
            z-index: 3;
            flex-shrink: 0;
            width: 100%;
        }

        .status-label {
            font-size: 13.5px;
            font-weight: 600;
            letter-spacing: 3px;
            color: #C18A2A;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .status-row {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 3px;
        }

        .padi-icon {
            position: absolute;
            top: 50%;
            width: 35px;
            height: auto;
            object-fit: contain;
            z-index: 2;
        }

        .padi-left {
            right: 190px;
            transform: translateY(-50%);
        }

        .padi-right {
            left: 190px;
            transform: translateY(-50%) scaleX(-1);
        }

        .status-text {
            font-family: 'Times New Roman', Times, serif;
            font-size: 40px;
            font-weight: 500;
            color: #8E101A;
            line-height: 1;
            white-space: nowrap;
            z-index: 2;
        }

        .status-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px auto 5px auto;
            width: fit-content;
        }

        .status-divider .sdline {
            height: 0.1px;
            width: 140px;
            background: #C18A2A;
            display: block;
        }

        .status-divider .sdia {
            width: 6px;
            height: 6px;
            background: #C18A2A;
            transform: rotate(45deg);
            margin: 0 6px;
            flex-shrink: 0;
            display: block;
        }

        .validity {
            text-align: center;
            margin-top: 8px;
            flex-shrink: 0;
        }

        .validity .v1 {
            font-size: 13.5px;
            color: #444;
        }

        .validity .v1 strong {
            font-weight: 700;
            color: #222;
        }

        .validity .v2 {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 15.5px;
            font-weight: 700;
            color: #222;
            margin-top: 3px;
        }

        .validity .v2 .vline {
            height: 1.2px;
            width: 38px;
            background: #C18A2A;
            display: block;
        }

        .cert-bottom {
            position: absolute;
            bottom: 55px;
            left: 0;
            right: 0;
            width: 83%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0 auto;
            transform: translateX(-20px);
            flex-shrink: 0;
        }

        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        .qr-frame {
            border: 1.5px solid #C18A2A;
            border-radius: 5px;
            padding: 8px;
            background: white;
            display: inline-block;
        }

        .qr-frame img {
            width: 80px;
            height: 80px;
            display: block;
        }

        .qr-text {
            font-size: 11px;
            color: #555;
            text-align: center;
            line-height: 1.5;
        }

        .sig-section {
            text-align: center;
            width: 240px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sig-city {
            font-size: 13px;
            color: #111;
        }

        .sig-title {
            font-size: 13px;
            color: #111;
            font-weight: 600;
            margin-top: 1px;
        }

        .sig-img {
            margin: 5px auto;
            display: block;
        }

        .sig-line {
            display: flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
            margin: 2px auto;
        }

        .sig-line .sl {
            height: 0.5px;
            width: 90px;
            background: #C18A2A;
            display: block;
        }

        .sig-line .sd {
            width: 5.5px;
            height: 5.5px;
            background: #C18A2A;
            transform: rotate(45deg);
            margin: 0 5px;
            flex-shrink: 0;
            display: block;
        }

        .sig-name {
            font-size: 12.5px;
            font-weight: 700;
            color: #222;
            margin-top: 5px;
            white-space: nowrap;
        }

        /* ═══════════════════════════════════════
           HALAMAN LAMPIRAN (2 & 3) — KONTEN BERBEDA, FRAME SAMA
        ═══════════════════════════════════════ */

        /* Judul lampiran — lebih kecil dari halaman 1 agar konten muat */
        .app-title {
            text-align: center;
            margin-top: 10px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 46px;
            font-weight: 500;
            letter-spacing: 3px;
            color: #8E101A;
            line-height: 1.05;
            text-transform: uppercase;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .app-subtitle {
            text-align: center;
            font-family: 'Times New Roman', Times, serif;
            font-size: 26px;
            font-weight: 400;
            letter-spacing: 3px;
            color: #8E101A;
            line-height: 1.1;
            text-transform: uppercase;
            white-space: nowrap;
            flex-shrink: 0;
        }

        /* ── Halaman 2: Tabel Elemen ── */
        .elemen-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            flex: 1;
            overflow: hidden;
            position: relative;
            z-index: 2;
            margin-top: 10px;
        }

        .etable {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
            table-layout: fixed;
            border: 0.5px solid rgba(193, 138, 42, 0.5);
        }

        .etable thead tr {
            background: #8E101A;
        }

        .etable thead th {
            color: #fbf9f7;
            font-weight: 700;
            padding: 5px 4px;
            text-align: center;
            font-size: 7.5px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border: none;
        }

        .etable th:nth-child(1),
        .etable td:nth-child(1) {
            width: 13%;
        }

        .etable th:nth-child(2),
        .etable td:nth-child(2) {
            width: 52%;
        }

        .etable th:nth-child(3),
        .etable td:nth-child(3) {
            width: 35%;
        }

        .etable tbody tr:nth-child(even) {
            background: rgba(193, 138, 42, 0.05);
        }

        .etable td {
            padding: 3.5px 4px;
            border: 0.5px solid rgba(193, 138, 42, 0.25);
            vertical-align: middle;
            line-height: 1.35;
        }

        .etable .krit-cell {
            text-align: center;
            vertical-align: middle;
            background: rgba(142, 16, 26, 0.04);
        }

        .krit-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            background: #8E101A;
            color: #fbf9f7;
            font-weight: 800;
            font-size: 9px;
            border-radius: 3px;
            margin-bottom: 3px;
        }

        .krit-nama {
            font-size: 6px;
            font-weight: 600;
            color: #8E101A;
            line-height: 1.2;
            word-wrap: break-word;
        }

        .kode-el {
            color: #8E101A;
            font-weight: 700;
            margin-right: 2px;
        }

        .kat-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 2px;
            font-size: 6.5px;
            font-weight: 700;
            text-align: center;
            width: 100%;
            color: #222;
            letter-spacing: 0.3px;
        }

        /* ── Halaman 3: Resume ── */
        .resume-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex: 1;
            overflow: hidden;
            position: relative;
            z-index: 2;
            margin-top: 10px;
        }

        .resume-item {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .ri-icon-col {
            flex: 0 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .ri-icon {
            width: 46px;
            height: 46px;
            border: 1.5px solid #C18A2A;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(193, 138, 42, 0.06);
            flex-shrink: 0;
        }

        .ri-icon svg {
            width: 28px;
            height: 28px;
            fill: #8E101A;
        }

        .ri-dash {
            width: 2px;
            flex: 1;
            min-height: 8px;
            border-left: 2px dashed rgba(193, 138, 42, 0.35);
            margin-top: 3px;
            position: relative;
            /* WAJIB */
        }

        /* garis belok ke kanan */
        .ri-dash::before {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 18px;
            /* panjang ke kanan */
            border-top: 2px dashed rgba(193, 138, 42, 0.35);
        }

        /* diamond di ujung */
        .ri-dash::after {
            content: "";
            position: absolute;
            bottom: -3px;
            left: 18px;
            width: 6px;
            height: 6px;
            background: #C18A2A;
            transform: rotate(45deg);
        }

        .ri-content {
            flex: 1;
            margin-bottom: 8px;
        }

        .ri-title {
            font-family: 'Times New Roman', Times, serif;
            font-size: 15px;
            font-weight: 700;
            color: #8E101A;
            text-transform: uppercase;
            margin-bottom: 5px;
            padding-bottom: 4px;
        }

        .ri-text {
            font-size: 12px;
            color: #222;
            line-height: 1.65;
            text-align: justify;
        }

        /* ── Footer bawah lampiran ── */
        .app-bottom {
            position: absolute;
            bottom: 35px;
            left: 0;
            right: 0;
            width: 83%;
            margin: 0 auto;
            transform: translateX(-20px);
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding-top: 8px;
            border-top: 0.5px solid #C18A2A;
            z-index: 5;
        }

        .app-bottom-left {
            font-size: 9px;
            color: #555;
            line-height: 1.6;
        }

        .app-bottom-right {
            text-align: right;
        }

        .app-pg-num {
            font-size: 11px;
            font-weight: 700;
            color: #8E101A;
            letter-spacing: 2px;
        }

        /* ===== NAV BAR ===== */
        .cert-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: rgba(30, 20, 15, 0.85);
            backdrop-filter: blur(4px);
            z-index: 100;
        }

        .cert-nav button {
            padding: 8px 20px;
            background: #8E101A;
            color: #fbf9f7;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 13px;
            transition: background 0.2s;
        }

        .cert-nav button:hover {
            background: #6d0e15;
        }

        .cert-nav button:disabled {
            background: #555;
            cursor: not-allowed;
        }

        .cert-nav .pg-info {
            color: #fbf9f7;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Montserrat', sans-serif;
            padding: 6px 14px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 5px;
        }

        /* ===== PRINT ===== */
        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }

            html,
            body {
                overflow: visible !important;
                position: static !important;
                background: white !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .cert-loading,
            .cert-nav {
                display: none !important;
            }

            .scaler-wrap {
                position: static !important;
                display: block !important;
            }

            .scaler {
                transform: none !important;
                position: static !important;
                width: 297mm !important;
                height: auto !important;
                /* ← key fix: biarkan tumbuh sesuai 3 halaman */
                left: auto !important;
                top: auto !important;
            }

            .page {
                display: flex !important;
                /* ← tampilkan semua, bukan hanya .active */
                position: static !important;
                width: 297mm !important;
                height: 210mm !important;
                page-break-after: always !important;
                break-after: page !important;
                align-items: center !important;
                justify-content: center !important;
                overflow: hidden !important;
            }

            .page:last-of-type {
                page-break-after: auto !important;
                break-after: auto !important;
            }

            .cert-wrap {
                zoom: 1.038 !important;
            }
        }

        /* ===== PDF MODE ===== */
        @if($forPdf ?? false)
        html, body { overflow: visible !important; position: static !important; background: white !important; height: auto !important; }
        .scaler-wrap { position: static !important; display: block !important; }
        .scaler { transform: none !important; width: 1122px !important; height: 794px !important; position: static !important; }
        .page { display: flex !important; position: static !important; width: 1122px !important; height: 794px !important; page-break-after: always; break-after: page; }
        .page:last-of-type { page-break-after: auto !important; break-after: auto !important; }
        .cert-nav, .cert-loading { display: none !important; }
        @endif

        /* ===== LOADING OVERLAY ===== */
        .cert-loading {
            position: fixed;
            inset: 0;
            background: #cdc5ab;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.35s ease;
        }

        .cert-loading.fade-out {
            opacity: 0;
            pointer-events: none;
        }

        .cert-loading.hidden {
            display: none;
        }

        .cl-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
        }

        .cl-logo {
            width: 100px;
            height: auto;
            object-fit: contain;
            animation: cl-pulse 1.2s ease-in-out infinite alternate;
            filter: drop-shadow(0 2px 8px rgba(193, 138, 42, 0.35));
        }

        @keyframes cl-pulse {
            from {
                opacity: 0.5;
                transform: scale(0.92);
            }

            to {
                opacity: 1;
                transform: scale(1.05);
            }
        }

        .cl-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 2px;
            color: #8E101A;
            text-transform: uppercase;
        }

        /* ===== PAGE 2 REDESIGN ===== */
        .p2-header-hr {
            border: none;
            border-top: 0.5px solid #C18A2A;
            margin: 5px 0 3px;
            flex-shrink: 0;
        }

        .p2-lampiran {
            text-align: center;
            font-size: 11.5px;
            color: #555;
            margin: 10px;
            flex-shrink: 0;
        }

        .p2-separator {
            border: none;
            border-top: 1px solid #C18A2A;
            width: 880px;
            margin: 0 auto 5px;
            flex-shrink: 0;
        }

        .p2-main-title {
            text-align: center;
            font-family: 'Times New Roman', Times, serif;
            font-size: 30px;
            color: #8E101A;
            font-weight: 500;
            letter-spacing: 0px;
            text-transform: uppercase;
            white-space: nowrap;
            margin: 0 0 2px;
            flex-shrink: 0;
        }

        .p2-grid {
            display: flex;
            flex: 1;
            overflow: hidden;
            position: relative;
            z-index: 2;
            margin-top: 6px;
            gap: 8px;
        }

        .p2-grid-col {
            flex: 1;
            overflow: hidden;
        }

        .p2-grid-sep {
            display: none;
        }

        .p2-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 9px;
            table-layout: fixed;
            border: 1px solid rgba(193, 138, 42, 0.28);
            border-radius: 4px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.42);
        }

        .p2-table thead th {
            color: #B27618;
            font-weight: 700;
            padding: 5px 4px;
            font-size: 7px;
            letter-spacing: 1.25px;
            text-transform: uppercase;
            border-bottom: 1px solid rgba(193, 138, 42, 0.36);
            border-right: 1px solid rgba(193, 138, 42, 0.22);
            text-align: center;
            background: rgba(193, 138, 42, 0.06);
        }

        .p2-table thead th:last-child {
            border-right: none;
        }

        .p2-table th:nth-child(1),
        .p2-table td:nth-child(1) {
            width: 16%;
        }

        .p2-table th:nth-child(2),
        .p2-table td:nth-child(2) {
            width: 53%;
        }

        .p2-table th:nth-child(3),
        .p2-table td:nth-child(3) {
            width: 31%;
        }

        .p2-table td {
            padding: 3.2px;
            border-bottom: 1px solid rgba(193, 138, 42, 0.16);
            border-right: 1px solid rgba(193, 138, 42, 0.14);
            vertical-align: middle;
            line-height: 1.28;
            background: rgba(255, 255, 255, 0.26);
        }

        .p2-table td:last-child {
            border-right: none;
        }

        .p2-table tr:last-child td {
            border-bottom: none;
        }

        .p2-krit-cell {
            text-align: center;
            vertical-align: middle;
            padding-left: 3px;
            padding-right: 3px;
        }

        .p2-krit-badge {
            font-family: 'Times New Roman', Times, serif !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 25px;
            height: 25px;
            background: linear-gradient(180deg, #9B121D 0%, #760C14 100%);
            color: #fff;
            font-weight: 800;
            font-size: 13px;
            line-height: 1;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.18);
        }

        .p2-krit-nama {
            font-size: 7px;
            font-weight: 600;
            color: #3f3f3f;
            line-height: 1.25;
            text-align: center;
            word-break: normal;
            margin: 7px;
        }

        .p2-el-wrap {
            display: flex;
            align-items: center;
        }

        .p2-el-code {
            font-family: 'Times New Roman', Times, serif !important;
            font-weight: 800;
            color: #4A2B1A;
            margin-right: 4px;
            min-width: 20px;
        }

        .p2-el-text {
            flex: 1;
        }

        .p2-kat-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 21px;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            line-height: 1.2;
            text-align: center;
            width: 100%;
            color: #2f2f2f;
            border: 1px solid rgba(255, 255, 255, 0.65);
            box-sizing: border-box;
        }

        .p2-footer {
            text-align: center;
            font-size: 9.2px;
            color: #555;
            line-height: 1.45;
            margin-top: 5px;
            flex-shrink: 0;
            position: relative;
            z-index: 3;
        }

        .p2-footer strong {
            color: #222;
        }

        .ri-icon i {
            font-size: 22px;
            color: #8E101A;
        }

    </style>
</head>
<body>

    @php
    $peringkatFinal = $hasil->getPeringkatFromSkor((float)($hasil->skor_al ?? 0), 'al');
    $verifikasiUrl = url("/verifikasi-sertifikat/{$nomorSertifikat}");
    $verifikasiUrlShort = parse_url($verifikasiUrl, PHP_URL_HOST) . '/verifikasi-sertifikat';
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($verifikasiUrl);
    $tglPenetapan = \App\Libraries\Date::tglIndo($tanggalPenetapan);
    $tglMulai = \App\Libraries\Date::tglIndo($masaBerlaku['tanggal_mulai']);
    $tglAkhir = \App\Libraries\Date::tglIndo($masaBerlaku['tanggal_berakhir']);
    $masaTahun = $masaBerlaku['tahun'];
    $masaTerbilang = \App\Libraries\Fungsi::terbilang($masaTahun);

    $grouped = collect($elemenList)->groupBy('kode_kriteria');
    $halfCount = (int) ceil($grouped->count() / 2);
    $leftGroups = $grouped->take($halfCount);
    $rightGroups= $grouped->slice($halfCount);
    $defKat = ['label' => '-', 'color' => '#e9ecef'];

    $resumeBabs = isset($resume['bab']) && is_array($resume['bab']) ? $resume['bab'] : [];

    $resumeIcons = [
    // Proses Asesmen (clipboard + search)
    '<i class="bi bi-clipboard-check"></i>',

    // Hasil Asesmen (bar chart)
    '<i class="bi bi-bar-chart-line"></i>',

    // Rekomendasi (award/badge)
    '<i class="bi bi-award"></i>',
    ];

    $defaultIcon = '<svg viewBox="0 0 24 24">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" /></svg>';
    @endphp
    <div class="cert-loading" id="certLoading">
        <div class="cl-inner">
            <img src="{{ asset('assets/images/logo-square.png') }}" alt="Logo" class="cl-logo">
            <div class="cl-text">Memuat Sertifikat&hellip;</div>
        </div>
    </div>
    <div class="scaler-wrap">
        <div class="scaler" id="certScaler">

            {{-- ============================================================
                 HALAMAN 1 — SERTIFIKAT AKREDITASI
                 Desain dan konten IDENTIK dengan doc 4
            ============================================================ --}}
            <div class="page active" id="page1">
                <div class="cert-wrap">
                    <svg class="cert-border-svg" viewBox="0 0 1082 700" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M 14 38 V 30 H 22 V 22 H 30 V 14 H 38 H 1044 V 22 H 1052 V 30 H 1060 V 38 H 1068 V 662 H 1060 V 670 H 1052 V 678 H 1044 V 686 H 38 V 678 H 30 V 670 H 22 V 662 H 14 V 38 Z" stroke="#C18A2A" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter" fill="none" />
                    </svg>

                    <div class="watermark">
                        <img src="{{ asset('assets/images/elemen_depilar.png') }}" alt="" style="width:100%;height:100%;object-fit:contain;">
                    </div>
                    <div class="deco-pattern">
                        <img src="{{ asset('assets/images/elemen_depilar.png') }}" alt="" style="width:100%;height:100%;object-fit:contain;">
                    </div>

                    <div class="cert-inner">
                        <div class="watermark-logo"></div>

                        <div class="cert-header">
                            <div class="logo-left">
                                <div class="logo-icon"><img src="{{ asset('assets/images/logo-square.png') }}" alt="Logo"></div>
                                <div class="logo-text">DEPILAR</div>
                            </div>
                            <div class="header-center">
                                <div class="line1">LEMBAGA AKREDITASI MANDIRI</div>
                                <div class="line2">DESAIN PERENCANAAN LINGKUNGAN ARSITEKTUR</div>
                                <div class="line3">(LAMDEPILAR)</div>
                                <div class="diamond-divider">
                                    <span class="dline"></span><span class="dia"></span><span class="dline"></span>
                                </div>
                            </div>
                            <div class="header-right">
                                <div class="hrow"><span class="hlabel">Nomor</span><span>&nbsp;: {{ $nomorSertifikat }}</span></div>
                                <div class="hrow"><span class="hlabel">Tanggal</span><span>&nbsp;: {{ $tglPenetapan }}</span></div>
                            </div>
                        </div>

                        <div class="cert-title">SERTIFIKAT&nbsp;&nbsp;AKREDITASI</div>

                        <div class="nomor-line">
                            <span class="nline"></span>
                            <span class="ntext">Nomor:&nbsp;<strong>{{ $nomorSertifikat }}</strong></span>
                            <span class="nline"></span>
                        </div>

                        <div class="cert-statement">
                            Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur<br>
                            (LAMDEPILAR) dengan ini menyatakan bahwa:
                        </div>

                        <div class="info-table">
                            <div class="info-row">
                                <div class="info-icon">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M12 5.5C10.3 4.5 8.4 4 6 4H4V17.5H6.5C8.8 17.5 10.7 18.1 12 19V5.5Z" stroke="#B8862B" stroke-width="1.2" stroke-linejoin="round" />
                                        <path d="M12 5.5C13.7 4.5 15.6 4 18 4H20V17.5H17.5C15.2 17.5 13.3 18.1 12 19V5.5Z" stroke="#B8862B" stroke-width="1.2" stroke-linejoin="round" />
                                        <path d="M4 5.5H2.5V20.5H8C9.5 20.5 10.9 21 11.9 21.7" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M20 5.5H21.5V20.5H16C14.5 20.5 13.1 21 12.1 21.7" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div class="info-label">Program Studi</div>
                                <span class="info-vline"></span>
                                <div class="info-value">{{ $studyProgram->name }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-icon">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M2 9L12 4.5L22 9L12 13.5L2 9Z" stroke="#B8862B" stroke-width="1.2" stroke-linejoin="round" />
                                        <path d="M6.5 11.5V17C6 17 8.8 19.5 12 19.5C15.2 19.5 18 17 18 17V11.5" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M4 10V14" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" />
                                        <circle cx="4" cy="15.2" r="0.85" fill="#B8862B" />
                                    </svg>
                                </div>
                                <div class="info-label">Jenjang</div>
                                <span class="info-vline"></span>
                                <div class="info-value">{{ $studyProgram->degreeLevel->name ?? '-' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-icon">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M2.5 9L12 4L21.5 9" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M3.5 9.8H20.5" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" />
                                        <path d="M5 10.5V17.5M19 10.5V17.5M7.5 10.5V17.5M11.5 10.5V17.5M13.5 10.5V17.5M17 10.5V17.5" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" />
                                        <path d="M3.5 18H20.5M2 19.8H22" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" />
                                    </svg>
                                </div>
                                <div class="info-label">Perguruan Tinggi</div>
                                <span class="info-vline"></span>
                                <div class="info-value">{{ $university->name }}</div>
                            </div>
                        </div>

                        <div class="status-section">
                            <hr class="hr-status">
                            <img class="padi-icon padi-left" src="{{ asset('assets/images/elemen_padi_no_bg.svg') }}" alt="">
                            <img class="padi-icon padi-right" src="{{ asset('assets/images/elemen_padi_no_bg.svg') }}" alt="">
                            <div class="status-label">STATUS AKREDITASI</div>
                            <div class="status-row">
                                <div class="status-text">{{ strtoupper($peringkatFinal) }}</div>
                            </div>
                            <div class="status-divider">
                                <span class="sdline"></span><span class="sdia"></span><span class="sdline"></span>
                            </div>
                        </div>

                        <div class="validity">
                            <div class="v1">Sertifikat ini berlaku selama <strong>{{ $masaTahun }} ({{ $masaTerbilang }}) tahun</strong></div>
                            <div class="v2">
                                <span>{{ $tglMulai }}</span>
                                <span class="vline"></span>
                                <span>{{ $tglAkhir }}</span>
                            </div>
                        </div>

                        <div class="cert-bottom">
                            <div class="qr-section">
                                <div class="qr-frame"><img src="{{ $qrCodeUrl }}" alt="QR"></div>
                                <div class="qr-text">Verifikasi sertifikat melalui<br><strong>{{ $verifikasiUrlShort }}</strong></div>
                            </div>
                            <div class="sig-section">
                                <div class="sig-city">Jakarta, {{ $tglPenetapan }}</div>
                                <div class="sig-title">Ketua Dewan Eksekutif</div>
                                <svg class="sig-img" width="138" height="52" viewBox="0 0 138 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    {{-- <path d="M18 40 Q28 18 38 28 Q48 38 57 20 Q66 8 77 26 Q88 40 99 33 Q110 24 119 36" stroke="#111" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M58 40 Q72 46 88 42" stroke="#111" stroke-width="1.4" fill="none" stroke-linecap="round" /> --}}
                                </svg>
                                <div class="sig-line">
                                    <span class="sl"></span><span class="sd"></span><span class="sl"></span>
                                </div>
                                <div class="sig-name">Dr. Ar. Yulianto Purwono Prihatmaji, IPM., IAI</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>{{-- /page1 --}}

            {{-- ============================================================
                 HALAMAN 2 — SURAT KETERANGAN CAPAIAN AKREDITASI
                 Frame IDENTIK dengan halaman 1, isi berbeda
            ============================================================ --}}
            <div class="page" id="page2">
                <div class="cert-wrap">
                    <svg class="cert-border-svg" viewBox="0 0 1082 700" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M 14 38 V 30 H 22 V 22 H 30 V 14 H 38 H 1044 V 22 H 1052 V 30 H 1060 V 38 H 1068 V 662 H 1060 V 670 H 1052 V 678 H 1044 V 686 H 38 V 678 H 30 V 670 H 22 V 662 H 14 V 38 Z" stroke="#C18A2A" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter" fill="none" />
                    </svg>

                    <div class="watermark">
                        <img src="{{ asset('assets/images/elemen_depilar.png') }}" alt="" style="width:100%;height:100%;object-fit:contain;">
                    </div>
                    <div class="deco-pattern2">
                        <img src="{{ asset('assets/images/elemen_depilar.png') }}" alt="" style="width:100%;height:100%;object-fit:contain;">
                    </div>

                    <div class="cert-inner cert-inner--app">
                        <div class="watermark-logo"></div>

                        {{-- Header identik halaman 1 --}}
                        <div class="cert-header">
                            <div class="logo-left">
                                <div class="logo-icon"><img src="{{ asset('assets/images/logo-square.png') }}" alt="Logo"></div>
                                <div class="logo-text">DEPILAR</div>
                            </div>
                            <div class="header-center" style="padding-top:10px">
                                <div class="line1">LEMBAGA AKREDITASI MANDIRI</div>
                                <div class="line2">DESAIN PERENCANAAN LINGKUNGAN ARSITEKTUR</div>
                                <div class="line3">(LAMDEPILAR)</div>
                                <div class="p2-lampiran">Lampiran Sertifikat Akreditasi Program Studi</div>
                            </div>
                            <div class="header-right">
                                <div class="hrow"><span class="hlabel">Nomor</span><span>&nbsp;: {{ $nomorSertifikat }}</span></div>
                                <div class="hrow"><span class="hlabel">Tanggal</span><span>&nbsp;: {{ $tglPenetapan }}</span></div>
                            </div>
                        </div>

                        {{-- Garis + teks lampiran --}}
                        <hr class="p2-separator">

                        {{-- Judul utama --}}
                        <div class="p2-main-title">SURAT KETERANGAN CAPAIAN AKREDITASI</div>

                        {{-- Diamond kecil di bawah judul --}}
                        <div class="diamond-divider" style="margin: 3px 0 5px;">
                            <span class="dline" style="width:430px;"></span>
                            <span class="dia"></span>
                            <span class="dline" style="width:430px;"></span>
                        </div>

                        {{-- Tabel 2 kolom dengan pemisah emas vertikal --}}
                        @if(!empty($elemenList))
                        <div class="p2-grid">

                            {{-- Tabel kiri --}}
                            <div class="p2-grid-col">
                                <table class="p2-table">
                                    <thead>
                                        <tr>
                                            <th>Kriteria</th>
                                            <th>Pernyataan Elemen</th>
                                            <th>Kategori</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($leftGroups as $kodeKrit => $elemens)
                                        @php $rowspan = $elemens->count(); $namaKrit = $elemens->first()['nama_kriteria'] ?? ''; @endphp
                                        @foreach($elemens as $i => $el)
                                        @php $kat = $el['skor_kategori'] ?? $defKat; @endphp
                                        <tr>
                                            @if($i === 0)
                                            <td rowspan="{{ $rowspan }}" class="p2-krit-cell">
                                                <div class="p2-krit-badge">{{ $kodeKrit }}</div>
                                                <div class="p2-krit-nama">{{ $namaKrit }}</div>
                                            </td>
                                            @endif
                                            <td>
                                                <div class="p2-el-wrap">
                                                    <span class="p2-el-code">{{ $el['kode_elemen'] }}</span>
                                                    <span class="p2-el-text">{{ $el['nama_elemen'] }}</span>
                                                </div>
                                            </td>
                                            <td style="text-align:center;">
                                                <span class="p2-kat-badge" style="background-color:{{ $kat['color'] }};">
                                                    {{ $kat['label'] === 'Melampaui Standar' ? 'Melampaui' : $kat['label'] }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pemisah emas vertikal --}}
                            <div class="p2-grid-sep"></div>

                            {{-- Tabel kanan --}}
                            <div class="p2-grid-col">
                                <table class="p2-table">
                                    <thead>
                                        <tr>
                                            <th>Kriteria</th>
                                            <th>Pernyataan Elemen</th>
                                            <th>Kategori</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rightGroups as $kodeKrit => $elemens)
                                        @php $rowspan = $elemens->count(); $namaKrit = $elemens->first()['nama_kriteria'] ?? ''; @endphp
                                        @foreach($elemens as $i => $el)
                                        @php $kat = $el['skor_kategori'] ?? $defKat; @endphp
                                        <tr>
                                            @if($i === 0)
                                            <td rowspan="{{ $rowspan }}" class="p2-krit-cell">
                                                <div class="p2-krit-badge">{{ $kodeKrit }}</div>
                                                <div class="p2-krit-nama">{{ $namaKrit }}</div>
                                            </td>
                                            @endif
                                            <td>
                                                <div class="p2-el-wrap">
                                                    <span class="p2-el-code">{{ $el['kode_elemen'] }}</span>
                                                    <span class="p2-el-text">{{ $el['nama_elemen'] }}</span>
                                                </div>
                                            </td>
                                            <td style="text-align:center;">
                                                <span class="p2-kat-badge" style="background-color:{{ $kat['color'] }};">
                                                    {{ $kat['label'] === 'Melampaui Standar' ? 'Melampaui' : $kat['label'] }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </div>
                        @else
                        <div style="flex:1;display:flex;align-items:center;justify-content:center;font-size:11px;color:#999;">
                            Data elemen akreditasi belum tersedia.
                        </div>
                        @endif

                        {{-- Diamond bawah + footer --}}
                        <div class="diamond-divider3" style="margin: 4px 0 3px;">
                            <span class="dline"></span><span class="dia"></span><span class="dline"></span>
                        </div>
                        <div class="p2-footer">
                            Dokumen ini merupakan lampiran dari <strong style="font-weight: 500;">Sertifikat Akreditasi Nomor:</strong> {{ $nomorSertifikat }}<br>
                            Diterbitkan oleh LAMDEPILAR sebagai rincian capaian standar akreditasi program studi.
                        </div>

                    </div>
                </div>
            </div>{{-- /page2 --}}

            {{-- ============================================================
                 HALAMAN 3 — RESUME ASESMEN AKREDITASI
                 Frame IDENTIK dengan halaman 1, isi berbeda
            ============================================================ --}}
            <div class="page" id="page3">
                <div class="cert-wrap">
                    <svg class="cert-border-svg" viewBox="0 0 1082 700" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M 14 38 V 30 H 22 V 22 H 30 V 14 H 38 H 1044 V 22 H 1052 V 30 H 1060 V 38 H 1068 V 662 H 1060 V 670 H 1052 V 678 H 1044 V 686 H 38 V 678 H 30 V 670 H 22 V 662 H 14 V 38 Z" stroke="#C18A2A" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter" fill="none" />
                    </svg>

                    <div class="watermark">
                        <img src="{{ asset('assets/images/elemen_depilar.png') }}" alt="" style="width:100%;height:100%;object-fit:contain;">
                    </div>
                    <div class="deco-pattern3">
                        <img src="{{ asset('assets/images/elemen_depilar.png') }}" alt="" style="width:100%;height:100%;object-fit:contain;">
                    </div>

                    <div class="cert-inner cert-inner--app" style="padding:50px 42px 24px 42px">
                        <div class="watermark-logo"></div>

                        {{-- Header identik --}}
                        <div class="cert-header" style="padding:0 10px 0 10px">
                            <div class="logo-left">
                                <div class="logo-icon"><img src="{{ asset('assets/images/logo-square.png') }}" alt="Logo"></div>
                                <div class="logo-text">DEPILAR</div>
                            </div>
                            <div class="header-center" style="padding-top:10px">
                                <div class="line1">LEMBAGA AKREDITASI MANDIRI</div>
                                <div class="line2">DESAIN PERENCANAAN LINGKUNGAN ARSITEKTUR</div>
                                <div class="line3">(LAMDEPILAR)</div>
                                <div class="p2-lampiran">Lampiran Sertifikat Akreditasi Program Studi</div>
                            </div>
                            <div class="header-right">
                                <div class="hrow"><span class="hlabel">Nomor</span><span>&nbsp;: {{ $nomorSertifikat }}</span></div>
                                <div class="hrow"><span class="hlabel">Tanggal</span><span>&nbsp;: {{ $tglPenetapan }}</span></div>
                            </div>
                        </div>

                        {{-- Diamond kecil di bawah judul --}}
                        <div class="diamond-divider" style="margin: 3px 0 5px;">
                            <span class="dline" style="width:430px;"></span>
                            <span class="dia"></span>
                            <span class="dline" style="width:430px;"></span>
                        </div>

                        {{-- Judul --}}
                        <div class="p2-main-title">RESUME ASESMEN AKREDITASI</div>

                        <div class="diamond-divider" style="margin: 3px 0 5px;">
                            <span class="dline" style="width:430px;"></span>
                            <span class="dia"></span>
                            <span class="dline" style="width:430px;"></span>
                        </div>

                        <div class="cert-statement" style="margin-top:8px;font-size:13px;">
                            {{ $studyProgram->name }}&nbsp;—&nbsp;{{ $university->name }}
                        </div>

                        {{-- Resume --}}
                        <div class="resume-list">
                            @forelse($resumeBabs as $idx => $bab)
                            @php
                            $babTitle = isset($bab['title']) ? strtoupper($bab['title']) : 'BAGIAN ' . ($idx + 1);
                            $babContent = isset($bab['content']) ? strip_tags($bab['content']) : '';
                            $words = preg_split('/\s+/u', trim($babContent), -1, PREG_SPLIT_NO_EMPTY);
                            if (count($words) > 120) $babContent = implode(' ', array_slice($words, 0, 120)) . '…';
                            $iconHtml = $resumeIcons[$idx] ?? $defaultIcon;
                            $isLast = ($idx >= count($resumeBabs) - 1);
                            @endphp
                            <div class="resume-item">
                                <div class="ri-icon-col">
                                    <div class="ri-icon">{!! $iconHtml !!}</div>
                                    <div class="ri-dash"></div>
                                </div>
                                <div class="ri-content">
                                    <div class="ri-title">{{ $babTitle }}</div>
                                    <div class="ri-text">
                                        @if(!empty(trim($babContent)))
                                        {!! nl2br(e($babContent)) !!}
                                        @else
                                        <span style="color:#aaa;font-style:italic;">— belum diisi —</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div style="flex:1;display:flex;align-items:center;justify-content:center;font-size:11px;color:#999;">
                                Resume asesmen belum diisi.
                            </div>
                            @endforelse
                        </div>

                        {{-- Diamond bawah + footer --}}
                        <div class="diamond-divider3" style="margin: 4px 0 3px;">
                            <span class="dline"></span><span class="dia"></span><span class="dline"></span>
                        </div>
                        <div class="p2-footer">
                            Dokumen ini merupakan lampiran dari <strong style="font-weight: 500;">Sertifikat Akreditasi Nomor:</strong> {{ $nomorSertifikat }}<br>
                            Resume asesmen disusun berdasarkan hasil visitasi dan evaluasi asesor LAMDEPILAR.
                        </div>
                    </div>
                </div>
            </div>{{-- /page3 --}}

        </div>{{-- /scaler --}}
    </div>{{-- /scaler-wrap --}}

    <div class="cert-nav">
        <button id="btnPrev" onclick="goPage(currentPage - 1)" disabled>&#9664; Sebelumnya</button>
        <span class="pg-info" id="pgInfo">1 / 3</span>
        <button id="btnNext" onclick="goPage(currentPage + 1)">Berikutnya &#9654;</button>
        @if(isset($downloadUrl) && $downloadUrl)
        <button onclick="window.location.href='{{ $downloadUrl }}'" style="background:#C18A2A;">&#11015; Download PDF</button>
        @else
        <button onclick="window.print()" style="background:#C18A2A;">&#11015; Download PDF</button>
        @endif
    </div>

    @unless($forPdf ?? false)
    <script>
        let currentPage = 1;
        const totalPages = 3;
        const loading = document.getElementById('certLoading');
        let resizeTimer = null;

        function showLoading() {
            loading.classList.remove('hidden', 'fade-out');
        }

        function hideLoading() {
            loading.classList.add('fade-out');
            loading.addEventListener('transitionend', function handler() {
                loading.classList.add('hidden');
                loading.removeEventListener('transitionend', handler);
            });
        }

        function goPage(page) {
            if (page < 1 || page > totalPages) return;
            document.querySelectorAll('.page').forEach(el => el.classList.remove('active'));
            document.getElementById('page' + page).classList.add('active');
            currentPage = page;
            document.getElementById('btnPrev').disabled = currentPage === 1;
            document.getElementById('btnNext').disabled = currentPage === totalPages;
            document.getElementById('pgInfo').innerText = currentPage + ' / ' + totalPages;
        }

        function scaleCert() {
            const scaler = document.getElementById('certScaler');
            const pw = 1122
                , ph = 794
                , navH = 50;
            const availW = window.innerWidth;
            const availH = window.innerHeight - navH;
            const scale = Math.min(availW / pw, availH / ph);
            const offX = (availW - pw * scale) / 2;
            const offY = Math.max(0, (availH - ph * scale) / 2);
            scaler.style.transform = `scale(${scale})`;
            scaler.style.transformOrigin = 'top left';
            scaler.style.position = 'absolute';
            scaler.style.left = offX + 'px';
            scaler.style.top = offY + 'px';
        }

        /* Inisialisasi awal */
        window.addEventListener('load', () => {
            showLoading();
            scaleCert();
            goPage(1);
            /* Tunggu font & layout selesai, baru sembunyikan */
            setTimeout(hideLoading, 600);
        });

        /* Resize: tampilkan loading, debounce 300ms, lalu scale & sembunyikan */
        window.addEventListener('resize', () => {
            showLoading();
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                scaleCert();
                hideLoading();
            }, 300);
        });

    </script>
    @endunless

</body>
</html>
