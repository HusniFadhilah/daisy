<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Capaian Akreditasi – LAMDEPILAR</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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

        html,
        body {
            width: 100%;
            min-height: 100%;
            background: #cdc5ab;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* ── Scaler ── */
        .scaler-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .cert-wrap {
            width: 1082px;
            height: 760px;
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

        /* Watermark */
        .watermark-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 300px;
            opacity: .06;
            pointer-events: none;
            z-index: 1;
        }

        /* Deco right-bottom */
        .deco-pattern {
            position: absolute;
            right: 14px;
            bottom: 130px;
            width: 160px;
            height: 130px;
            opacity: .15;
            pointer-events: none;
            z-index: 1;
        }

        /* ── Inner ── */
        .cert-inner {
            position: relative;
            padding: 40px 52px 18px 52px;
            z-index: 5;
            height: 760px;
            display: flex;
            flex-direction: column;
        }

        /* ── Header ── */
        .cert-header {
            display: flex;
            align-items: flex-start;
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

        .logo-text-block {
            display: flex;
            flex-direction: column;
        }

        .logo-title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 4px;
            color: #9B0F1B;
            line-height: 1;
        }

        .header-center {
            text-align: center;
            flex: 1;
            padding-top: 2px;
        }

        .header-center .line1 {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .8px;
            color: #1a1a1a;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .header-center .line2 {
            font-size: 14px;
            font-weight: 700;
            color: #1a1a1a;
            text-transform: uppercase;
            margin-top: 2px;
            white-space: nowrap;
        }

        .header-center .line3 {
            font-size: 13px;
            font-weight: 700;
            color: #9B0F1B;
            margin-top: 3px;
        }

        .diamond-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 8px 0 0 0;
        }

        .diamond-divider .dline {
            height: .5px;
            width: 190px;
            background: #C18A2A;
            display: block;
        }

        .diamond-divider .dia {
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
            width: 200px;
            flex-shrink: 0;
            padding-top: 4px;
        }

        .header-right .hrow {
            display: flex;
            font-size: 11px;
            color: #111;
            line-height: 1.9;
            white-space: nowrap;
        }

        .header-right .hlabel {
            width: 58px;
            flex-shrink: 0;
        }

        /* ── Divider after header ── */
        .header-sep {
            width: 100%;
            border: none;
            border-top: .5px solid #C18A2A;
            margin: 8px 0;
            flex-shrink: 0;
        }

        /* ── Subtitle ── */
        .doc-subtitle {
            text-align: center;
            font-size: 12px;
            color: #444;
            letter-spacing: .5px;
            flex-shrink: 0;
            margin-bottom: 2px;
        }

        /* ── Main Title ── */
        .main-title {
            text-align: center;
            font-family: 'Times New Roman', Times, serif;
            font-size: 44px;
            font-weight: 500;
            letter-spacing: 6px;
            color: #8E101A;
            text-transform: uppercase;
            white-space: nowrap;
            flex-shrink: 0;
            margin-top: 2px;
            line-height: 1.1;
        }

        /* ── Gold divider with diamond under title ── */
        .title-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 6px auto 8px auto;
            width: fit-content;
        }

        .title-divider .tl {
            height: .5px;
            width: 300px;
            background: #C18A2A;
            display: block;
        }

        .title-divider .td {
            width: 7px;
            height: 7px;
            background: #C18A2A;
            transform: rotate(45deg);
            margin: 0 7px;
            flex-shrink: 0;
            display: block;
        }

        /* ── Two-column table grid ── */
        .elemen-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            flex: 1;
            overflow: hidden;
            position: relative;
            z-index: 2;
        }

        /* ── Table ── */
        .etable {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            table-layout: fixed;
            border: .5px solid rgba(193, 138, 42, .4);
        }

        .etable thead tr th {
            color: #8E101A;
            font-weight: 700;
            padding: 5px 4px;
            text-align: center;
            font-size: 7.5px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border: none;
            background: #fbf9f7;
            border-bottom: 1px solid rgba(193, 138, 42, .5);
        }

        .etable thead tr th:first-child {
            width: 17%;
        }

        .etable thead tr th:nth-child(2) {
            width: 55%;
        }

        .etable thead tr th:last-child {
            width: 28%;
        }

        .etable tbody tr:nth-child(even) {
            background: rgba(193, 138, 42, .04);
        }

        .etable td {
            padding: 4px 5px;
            border: .5px solid rgba(193, 138, 42, .2);
            vertical-align: middle;
            line-height: 1.35;
        }

        /* Kriteria cell */
        .krit-cell {
            text-align: center;
            vertical-align: middle;
            background: rgba(142, 16, 26, .03);
        }

        .krit-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            background: #8E101A;
            color: #fbf9f7;
            font-weight: 800;
            font-size: 10px;
            border-radius: 4px;
            margin-bottom: 3px;
        }

        .krit-nama {
            font-size: 6px;
            font-weight: 600;
            color: #8E101A;
            line-height: 1.2;
            word-wrap: break-word;
        }

        /* Elemen cell */
        .el-code {
            color: #8E101A;
            font-weight: 700;
            margin-right: 2px;
        }

        /* Category badge */
        .kat-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .kat-badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 700;
            text-align: center;
            width: 100%;
            color: #333;
            letter-spacing: .2px;
        }

        /* ── Footer ── */
        .cert-footer {
            flex-shrink: 0;
            border-top: .5px solid #C18A2A;
            margin-top: 8px;
            padding-top: 6px;
            text-align: center;
        }

        .footer-text {
            font-size: 9.5px;
            color: #444;
            line-height: 1.7;
        }

        .footer-text strong {
            font-weight: 700;
            color: #222;
        }

        /* ── Print ── */
        @media print {

            html,
            body {
                background: white;
                padding: 0;
                display: block;
            }

            .scaler-wrap {
                display: block;
            }

            .cert-wrap {
                width: 297mm;
                height: 210mm;
            }

            @page {
                size: A4 landscape;
                margin: 0;
            }
        }

    </style>
</head>
<body>
    <div class="scaler-wrap">
        <div class="cert-wrap">

            <!-- Border SVG -->
            <svg class="cert-border-svg" viewBox="0 0 1082 700" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M 14 38 V 30 H 22 V 22 H 30 V 14 H 38 H 1044 V 22 H 1052 V 30 H 1060 V 38 H 1068 V 662 H 1060 V 670 H 1052 V 678 H 1044 V 686 H 38 V 678 H 30 V 670 H 22 V 662 H 14 V 38 Z" stroke="#C18A2A" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter" fill="none" />
            </svg>

            <!-- Watermark center (DEPILAR logo shape) -->
            <div class="watermark-center">
                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;opacity:1;">
                    <!-- Stylized building/arch shape -->
                    <path d="M100 20 L160 60 L160 160 L140 160 L140 80 L100 55 L60 80 L60 160 L40 160 L40 60 Z" fill="#8E101A" />
                    <rect x="72" y="90" width="12" height="70" fill="#8E101A" />
                    <rect x="94" y="90" width="12" height="70" fill="#8E101A" />
                    <rect x="116" y="90" width="12" height="70" fill="#8E101A" />
                    <rect x="38" y="158" width="124" height="8" rx="2" fill="#8E101A" />
                    <rect x="55" y="163" width="90" height="5" rx="1" fill="#8E101A" />
                </svg>
            </div>

            <!-- Deco pattern right-bottom -->
            <div class="deco-pattern">
                <svg viewBox="0 0 160 130" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;opacity:1;">
                    <g fill="none" stroke="#8E101A" stroke-width="1.2">
                        <path d="M10 10 L50 10 L50 50 L10 50 Z" />
                        <path d="M20 20 L40 20 L40 40 L20 40 Z" />
                        <path d="M60 10 L100 10 L100 50 L60 50 Z" />
                        <path d="M70 20 L90 20 L90 40 L70 40 Z" />
                        <path d="M110 10 L150 10 L150 50 L110 50 Z" />
                        <path d="M120 20 L140 20 L140 40 L120 40 Z" />
                        <path d="M10 60 L50 60 L50 100 L10 100 Z" />
                        <path d="M20 70 L40 70 L40 90 L20 90 Z" />
                        <path d="M60 60 L100 60 L100 100 L60 100 Z" />
                        <path d="M70 70 L90 70 L90 90 L70 90 Z" />
                        <path d="M110 60 L150 60 L150 100 L110 100 Z" />
                        <path d="M120 70 L140 70 L140 90 L120 90 Z" />
                    </g>
                </svg>
            </div>

            <div class="cert-inner">

                <!-- HEADER -->
                <div class="cert-header">
                    <!-- Logo left -->
                    <div class="logo-left">
                        <div class="logo-icon"><img src="{{ asset('assets/images/logo-square.png') }}" alt="Logo"></div>
                        <div class="logo-text">DEPILAR</div>
                    </div>

                    <!-- Center -->
                    <div class="header-center">
                        <div class="line1">LEMBAGA AKREDITASI MANDIRI</div>
                        <div class="line2">DESAIN PERENCANAAN LINGKUNGAN ARSITEKTUR</div>
                        <div class="line3">(LAMDEPILAR)</div>
                    </div>

                    <!-- Right -->
                    <div class="header-right">
                        <div class="hrow"><span class="hlabel">Nomor</span><span>&nbsp;: LAMDEPILAR/2026/004</span></div>
                        <div class="hrow"><span class="hlabel">Tanggal</span><span>&nbsp;: 29 April 2026</span></div>
                    </div>
                </div>

                <!-- Subtitle -->
                <div class="doc-subtitle">Lampiran Sertifikat Akreditasi Program Studi</div>

                <hr class="header-sep">
                <!-- Main Title -->
                <div class="main-title">SURAT KETERANGAN CAPAIAN AKREDITASI</div>

                <!-- Gold divider -->
                <div class="title-divider">
                    <span class="tl"></span><span class="td"></span><span class="tl"></span>
                </div>

                <!-- TWO-COLUMN TABLE -->
                <div class="elemen-grid">

                    <!-- LEFT TABLE -->
                    <table class="etable">
                        <thead>
                            <tr>
                                <th>KRITERIA</th>
                                <th>PERNYATAAN ELEMEN</th>
                                <th>KATEGORI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- D: Tata Pamong -->
                            <tr>
                                <td rowspan="3" class="krit-cell">
                                    <div class="krit-badge">D</div>
                                    <div class="krit-nama">Tata Pamong, Visi, Misi, Tujuan, dan Strategi</div>
                                </td>
                                <td><span class="el-code">D.1</span>Legalitas Program dan Tata Pamong</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#cdd9c8;">Memenuhi Standar</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">D.2</span>Visi, Misi, Tujuan, dan Strategi</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">D.3</span>Kesesuaian Visi Keilmuan</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <!-- E: Edukasi -->
                            <tr>
                                <td rowspan="5" class="krit-cell">
                                    <div class="krit-badge">E</div>
                                    <div class="krit-nama">Edukasi, Kurikulum, dan Capaian Pembelajaran</div>
                                </td>
                                <td><span class="el-code">E.1</span>Kurikulum</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">E.2</span>Admisi Mahasiswa</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">E.3</span>Proses dan Siklus Pembelajaran</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">E.4</span>Penilaian dan Evaluasi</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#cdd9c8;">Memenuhi Standar</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">E.5</span>Kompetensi Lulusan dan Capaian Pembelajaran</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#cdd9c8;">Memenuhi Standar</span></td>
                            </tr>
                            <!-- P: Pengembangan SDM -->
                            <tr>
                                <td rowspan="4" class="krit-cell">
                                    <div class="krit-badge">P</div>
                                    <div class="krit-nama">Pengembangan Sumber Daya Manusia</div>
                                </td>
                                <td><span class="el-code">P.1</span>Dosen dan Tenaga Kependidikan</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">P.2</span>Sarana dan Prasarana Kerja</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">P.3</span>Pengembangan Kapasitas</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">P.4</span>Kesejahteraan Kerja</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#cdd9c8;">Memenuhi Standar</span></td>
                            </tr>
                            <!-- I: Internasionalisasi -->
                            <tr>
                                <td rowspan="3" class="krit-cell">
                                    <div class="krit-badge">I</div>
                                    <div class="krit-nama">Internasionalisasi Penjaminan Mutu</div>
                                </td>
                                <td><span class="el-code">I.1</span>Sistem Penjaminan Mutu Internal</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">I.2</span>Implementasi Perbaikan Berkelanjutan</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#cdd9c8;">Memenuhi Standar</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">I.3</span>Keterlibatan Pengampu Kepentingan dan Penjaminan Mutu Eksternal</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#cdd9c8;">Memenuhi Standar</span></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- RIGHT TABLE -->
                    <table class="etable">
                        <thead>
                            <tr>
                                <th>KRITERIA</th>
                                <th>PERNYATAAN ELEMEN</th>
                                <th>KATEGORI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- L: Lingkungan -->
                            <tr>
                                <td rowspan="4" class="krit-cell">
                                    <div class="krit-badge">L</div>
                                    <div class="krit-nama">Lingkungan dan Sumber Daya untuk Mendukung Pembelajaran</div>
                                </td>
                                <td><span class="el-code">L.1</span>Sarana dan Prasarana Belajar</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">L.2</span>Sumber Pengetahuan</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f5e07a;">Lemah</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">L.3</span>Kepuasan Mahasiswa dan Alumni</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">L.4</span>Lulusan, Kajian Telusur, dan Kepuasan Pengguna</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#cdd9c8;">Memenuhi Standar</span></td>
                            </tr>
                            <!-- A: Akuntabilitas -->
                            <tr>
                                <td rowspan="5" class="krit-cell">
                                    <div class="krit-badge">A</div>
                                    <div class="krit-nama">Akuntabilitas, Tata Kelola, dan Kerjasama</div>
                                </td>
                                <td><span class="el-code">A.1</span>Organisasi dan Tata Kelola</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#cdd9c8;">Memenuhi Standar</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">A.2</span>Kerja Sama dan Kemitraan</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#cdd9c8;">Memenuhi Standar</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">A.3</span>Sistem dan Manajemen Informasi</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">A.4</span>Keselamatan dan Kesehatan Kerja serta Kelestarian Lingkungan</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#cdd9c8;">Memenuhi Standar</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">A.5</span>Keuangan, Keberlanjutan, dan Mitigasi Risiko</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <!-- R: Riset -->
                            <tr>
                                <td rowspan="6" class="krit-cell">
                                    <div class="krit-badge">R</div>
                                    <div class="krit-nama">Riset, Pengabdian, dan Suasana Ilmiah</div>
                                </td>
                                <td><span class="el-code">R.1</span>Kebijakan Penelitian</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#cdd9c8;">Memenuhi Standar</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">R.2</span>Proses Penelitian</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">R.3</span>Luaran dan Dampak Penelitian</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">R.4</span>Kebijakan Pengabdian kepada Masyarakat</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f5e07a;">Lemah</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">R.5</span>Proses Pengabdian kepada Masyarakat</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#f0ebe0;">Memenuhi</span></td>
                            </tr>
                            <tr>
                                <td><span class="el-code">R.6</span>Luaran dan Dampak Pengabdian kepada Masyarakat</td>
                                <td class="kat-wrap"><span class="kat-badge" style="background:#cdd9c8;">Memenuhi Standar</span></td>
                            </tr>
                        </tbody>
                    </table>

                </div><!-- /elemen-grid -->

                <!-- FOOTER -->
                <div class="cert-footer">
                    <div class="footer-text">
                        Dokumen ini merupakan lampiran dari <strong>Sertifikat Akreditasi Nomor: LAMDEPILAR/2026/004</strong><br>
                        Diterbitkan oleh LAMDEPILAR sebagai rincian capaian standar akreditasi program studi.
                    </div>
                </div>

            </div><!-- /cert-inner -->
        </div><!-- /cert-wrap -->
    </div><!-- /scaler-wrap -->

    <script>
        (function() {
            function scale() {
                var wrap = document.querySelector('.cert-wrap');
                var sw = window.innerWidth - 40
                    , sh = window.innerHeight - 40;
                var s = Math.min(sw / 1082, sh / 760);
                wrap.style.transform = 'scale(' + s + ')';
                wrap.style.transformOrigin = 'top left';
                var ow = (sw - 1082 * s) / 2
                    , oh = (sh - 760 * s) / 2;
                wrap.style.marginLeft = Math.max(0, ow) + 'px';
                wrap.style.marginTop = Math.max(0, oh) + 'px';
            }
            window.addEventListener('load', scale);
            window.addEventListener('resize', scale);
        })();

    </script>
</body>
</html>
