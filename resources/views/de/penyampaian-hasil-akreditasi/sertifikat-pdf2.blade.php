<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sertifikat Akreditasi - DEPILAR Desain S1</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #932136;
            --primary-soft: rgba(147, 33, 54, 0.08);
            --primary-border: rgba(147, 33, 54, 0.35);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            position: fixed;
            background: #e5e7eb;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            color: #000;
        }

        .certificate-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 5px;
        }

        .certificate-container {
            width: 100%;
            height: 100%;
            max-width: calc(100vh * 0.707);
            max-height: 100vh;
            position: relative;
        }

        @media (min-width: 768px) and (orientation: landscape),
        (min-width: 1024px) {
            .certificate-wrapper {
                padding: 15px;
            }

            .certificate-container {
                max-width: calc(100vh * 1.414);
                max-height: calc(100vw / 1.414);
            }
        }

        @media (min-width: 1024px) {
            .certificate-wrapper {
                padding: 20px;
            }
        }

        .certificate-frame {
            position: absolute;
            inset: 0;
            padding: 1.2vh;
            border-radius: 0.6vh;
            background: #fff;
            border: 0.4vh solid #9aa3b2;
            display: flex;
            overflow: hidden;
        }

        @media (min-width: 768px) {
            .certificate-frame {
                padding: 1.8vmin;
                border-radius: 1vmin;
                border-width: 0.6vmin;
            }
        }

        .certificate-frame::before {
            content: "";
            position: absolute;
            inset: 0.6vh;
            border-radius: 0.4vh;
            border: 0.2vh solid #c7cbd4;
            pointer-events: none;
            z-index: 0;
        }

        @media (min-width: 768px) {
            .certificate-frame::before {
                inset: 1vmin;
                border-radius: 0.8vmin;
                border-width: 0.3vmin;
            }
        }

        .certificate-inner {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.5vh 2vh;
            border-radius: 0.5vh;
            border: 0.15vh solid #932136;
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
        }

        @media (min-width: 768px) {
            .certificate-inner {
                padding: 2.2vmin 2.8vmin 1.8vmin 2.8vmin;
                border-radius: 0.8vmin;
                border-width: 0.2vmin;
            }
        }

        .certificate-inner::-webkit-scrollbar {
            width: 3px;
        }

        .certificate-inner::-webkit-scrollbar-track {
            background: transparent;
        }

        .certificate-inner::-webkit-scrollbar-thumb {
            background: rgba(147, 33, 54, 0.3);
            border-radius: 2px;
        }

        .certificate-inner>* {
            position: relative;
            z-index: 1;
        }

        /* WATERMARK */
        .watermark-logo {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.06;
            pointer-events: none;
            z-index: 0;
        }

        .watermark-logo svg {
            width: 45vh;
            height: auto;
        }

        @media (min-width: 768px) {
            .watermark-logo svg {
                width: 45vmin;
            }
        }

        /* HEADER */
        .header {
            display: table;
            width: 100%;
            margin-top: 0.4vh;
            padding-bottom: 1vh;
            border-bottom: 0.15vh solid #932136;
        }

        @media (min-width: 768px) {
            .header {
                margin-top: 0.6vmin;
                padding-bottom: 1.4vmin;
                border-bottom-width: 0.2vmin;
            }
        }

        .header-col {
            display: table-cell;
            vertical-align: middle;
        }

        .header-left {
            width: 12%;
        }

        .header-left-garuda {
            width: 9%;
        }

        .header-mid {
            width: 64%;
            text-align: center;
        }

        .header-right {
            width: 15%;
            text-align: right;
            font-size: 0.9vh;
            color: #333;
        }

        @media (min-width: 768px) {
            .header-right {
                font-size: 1.4vmin;
            }
        }

        .logo-wrap {
            width: 12vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (min-width: 768px) {
            .logo-wrap {
                width: 15vmin;
            }
        }

        .logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Garuda placeholder (SVG inline) */
        .garuda-wrap {
            width: 7vh;
            height: 7vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (min-width: 768px) {
            .garuda-wrap {
                width: 9vmin;
                height: 9vmin;
            }
        }

        .garuda-wrap svg {
            width: 100%;
            height: 100%;
        }

        .inst-name {
            font-size: 1.15vh;
            font-weight: 700;
            color: #932136;
            line-height: 1.25;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        @media (min-width: 768px) {
            .inst-name {
                font-size: 1.75vmin;
                letter-spacing: 0.6px;
            }
        }

        .inst-sub {
            margin-top: 0.3vh;
            font-size: 0.95vh;
            color: #111;
            line-height: 1.3;
        }

        @media (min-width: 768px) {
            .inst-sub {
                margin-top: 0.4vmin;
                font-size: 1.45vmin;
            }
        }

        /* TITLE */
        .title {
            text-align: center;
            margin: 1.5vh 0 0.8vh 0;
        }

        @media (min-width: 768px) {
            .title {
                margin: 2.2vmin 0 1vmin 0;
            }
        }

        .title h1 {
            font-size: 2.8vh;
            font-weight: 800;
            color: #932136;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        @media (min-width: 768px) {
            .title h1 {
                font-size: 4.2vmin;
                letter-spacing: 2px;
            }
        }

        .title .no {
            margin-top: 0.6vh;
            font-size: 1vh;
            color: #333;
        }

        @media (min-width: 768px) {
            .title .no {
                margin-top: 0.8vmin;
                font-size: 1.55vmin;
            }
        }

        /* BODY */
        .body {
            margin-top: 1.2vh;
            text-align: center;
        }

        @media (min-width: 768px) {
            .body {
                margin-top: 1.6vmin;
            }
        }

        .intro {
            font-size: 1.15vh;
            line-height: 1.6;
            margin: 0.6vh 0 1vh 0;
        }

        @media (min-width: 768px) {
            .intro {
                font-size: 1.75vmin;
                line-height: 1.75;
                margin: 0.8vmin 0 1.4vmin 0;
            }
        }

        /* ========== DETAILS — TANPA KOTAK ========== */
        .details {
            width: 80%;
            margin: 0 auto;
            text-align: center;
        }

        .details-divider {
            width: 40%;
            height: 0.1vh;
            background: rgba(147, 33, 54, 0.2);
            margin: 0.6vh auto;
        }

        @media (min-width: 768px) {
            .details-divider {
                margin: 0.8vmin auto;
            }
        }

        .details-row {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 0.5vh;
            margin-bottom: 0.5vh;
            font-size: 1.15vh;
            flex-wrap: wrap;
        }

        @media (min-width: 768px) {
            .details-row {
                font-size: 1.7vmin;
                margin-bottom: 0.6vmin;
                gap: 0.7vmin;
            }
        }

        .details-label {
            color: #555;
            font-weight: 600;
            font-size: 0.95vh;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @media (min-width: 768px) {
            .details-label {
                font-size: 1.35vmin;
            }
        }

        .details-sep {
            color: #932136;
            font-weight: 700;
        }

        .details-value {
            font-weight: 700;
            color: #111;
        }

        /* BADGE AKREDITASI */
        .badge {
            width: 45vh;
            margin: 1.4vh auto 0 auto;
            border-radius: 0.8vh;
            border: 0.2vh solid #1f1801;
            padding: 1.1vh;
            text-align: center;
            box-shadow: 0 0.15vh 0.6vh rgba(12, 12, 12, 0.08);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        @media (min-width: 768px) {
            .badge {
                width: 70vmin;
                margin-top: 1.8vmin;
                border-radius: 1vmin;
                border-width: 0.25vmin;
                padding: 1.4vmin;
            }
        }

        .badge .badge-label {
            font-size: 1vh;
            letter-spacing: 0.6px;
            font-weight: 700;
            color: #4a3a00;
            text-transform: uppercase;
            margin-bottom: 0.5vh;
        }

        @media (min-width: 768px) {
            .badge .badge-label {
                font-size: 1.5vmin;
                margin-bottom: 0.6vmin;
            }
        }

        .badge .badge-rank {
            font-size: 2.5vh;
            font-weight: 900;
            color: #932136;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        @media (min-width: 768px) {
            .badge .badge-rank {
                font-size: 4vmin;
            }
        }

        /* VALIDITY */
        .validity {
            margin-top: 1.2vh;
            font-size: 1.15vh;
            color: #222;
            line-height: 1.65;
        }

        @media (min-width: 768px) {
            .validity {
                margin-top: 1.6vmin;
                font-size: 1.7vmin;
                line-height: 1.7;
            }
        }

        /* BOTTOM */
        .bottom {
            margin-top: 2vh;
            display: table;
            width: 100%;
        }

        @media (min-width: 768px) {
            .bottom {
                margin-top: 2.6vmin;
            }
        }

        .bottom-left {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
            padding-left: 0.6vh;
        }

        .bottom-right {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
            text-align: center;
        }

        .seal-row {
            display: flex;
            gap: 1vh;
            align-items: flex-end;
        }

        .qr {
            width: 9vh;
            height: 9vh;
            border: 0.1vh solid #c7cbd4;
            border-radius: 0.5vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #fff;
            flex-shrink: 0;
            padding: 0.3vh;
        }

        @media (min-width: 768px) {
            .qr {
                width: 10.8vmin;
                height: 10.8vmin;
                border-radius: 0.6vmin;
                padding: 0.4vmin;
            }
        }

        .qr img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .sig-loc {
            font-size: 1.05vh;
            margin-bottom: 0.5vh;
        }

        @media (min-width: 768px) {
            .sig-loc {
                font-size: 1.55vmin;
                margin-bottom: 0.6vmin;
            }
        }

        .sig-role {
            font-size: 1.1vh;
            font-weight: 700;
            margin-bottom: 5vh;
        }

        @media (min-width: 768px) {
            .sig-role {
                font-size: 1.65vmin;
                margin-bottom: 6.2vmin;
            }
        }

        .sig-name {
            display: inline-block;
            font-size: 1.2vh;
            font-weight: 700;
            border-bottom: 0.15vh solid #000;
            padding: 0 0.6vh 0.5vh 0.6vh;
            line-height: 1.2;
        }

        @media (min-width: 768px) {
            .sig-name {
                font-size: 1.75vmin;
                padding: 0 0.8vmin 0.6vmin 0.8vmin;
            }
        }

        /* FOOTER */
        .footer {
            margin-top: 1.2vh;
            padding-top: 0.8vh;
            border-top: 0.1vh solid #932136;
            text-align: center;
            font-size: 0.85vh;
            color: #444;
            line-height: 1.5;
        }

        @media (min-width: 768px) {
            .footer {
                margin-top: 1.6vmin;
                padding-top: 1vmin;
                font-size: 1.3vmin;
            }
        }

        @media print {

            html,
            body {
                overflow: visible;
                position: static;
                background: #fff;
                height: auto;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .certificate-wrapper {
                padding: 0;
                height: auto;
            }

            .certificate-container {
                max-width: 100%;
                max-height: none;
                height: auto;
            }

            .certificate-frame {
                position: static;
            }

            .certificate-inner {
                overflow: visible;
            }
        }

    </style>
</head>
<body>
    <div class="certificate-wrapper">
        <div class="certificate-container">
            <div class="certificate-frame">
                <div class="certificate-inner">

                    <!-- WATERMARK GARUDA (samar di belakang) -->
                    <div class="watermark-logo">
                        <!-- Garuda Pancasila SVG watermark -->
                        <svg viewBox="0 0 200 220" xmlns="http://www.w3.org/2000/svg">
                            <g fill="#932136">
                                <!-- Badan burung -->
                                <ellipse cx="100" cy="115" rx="28" ry="38" />
                                <!-- Kepala -->
                                <circle cx="100" cy="68" r="18" />
                                <!-- Paruh atas -->
                                <path d="M100 80 Q108 88 104 95 Q100 90 96 95 Q92 88 100 80Z" />
                                <!-- Mahkota -->
                                <path d="M86 62 L90 50 L94 62Z" />
                                <path d="M94 60 L98 47 L102 60Z" />
                                <path d="M102 62 L106 50 L110 62Z" />
                                <!-- Sayap kiri -->
                                <path d="M72 110 Q40 95 18 85 Q25 100 30 115 Q50 108 72 118Z" />
                                <path d="M70 118 Q38 112 15 110 Q20 125 28 135 Q50 122 70 128Z" />
                                <path d="M69 128 Q40 130 20 135 Q28 148 38 154 Q56 138 69 138Z" />
                                <!-- Sayap kanan -->
                                <path d="M128 110 Q160 95 182 85 Q175 100 170 115 Q150 108 128 118Z" />
                                <path d="M130 118 Q162 112 185 110 Q180 125 172 135 Q150 122 130 128Z" />
                                <path d="M131 128 Q160 130 180 135 Q172 148 162 154 Q144 138 131 138Z" />
                                <!-- Ekor -->
                                <path d="M85 148 Q88 165 82 180 L88 178 Q92 165 92 150Z" />
                                <path d="M92 150 Q93 168 90 183 L96 183 Q98 168 100 150Z" />
                                <path d="M100 150 Q102 168 104 183 L110 183 Q107 168 108 150Z" />
                                <path d="M108 150 Q112 165 118 178 L112 180 Q108 165 115 148Z" />
                                <!-- Kaki -->
                                <path d="M88 152 Q82 162 76 166 L80 168 Q86 162 92 155Z" />
                                <path d="M112 152 Q118 162 124 166 L120 168 Q114 162 108 155Z" />
                                <!-- Cakar kiri -->
                                <path d="M76 166 Q70 170 65 168 L66 172 Q71 174 77 170Z" />
                                <path d="M74 168 Q68 174 64 176 L66 179 Q71 177 75 172Z" />
                                <path d="M73 171 Q68 178 66 182 L69 183 Q72 179 76 174Z" />
                                <!-- Cakar kanan -->
                                <path d="M124 166 Q130 170 135 168 L134 172 Q129 174 123 170Z" />
                                <path d="M126 168 Q132 174 136 176 L134 179 Q129 177 125 172Z" />
                                <path d="M127 171 Q132 178 134 182 L131 183 Q128 179 124 174Z" />
                                <!-- Tameng/Perisai -->
                                <path d="M88 110 Q88 95 100 90 Q112 95 112 110 L112 135 Q100 145 88 135Z" fill="none" stroke="#932136" stroke-width="2" />
                                <!-- Garis tameng horizontal -->
                                <line x1="88" y1="117" x2="112" y2="117" stroke="#932136" stroke-width="1.5" />
                                <!-- Bintang di tameng -->
                                <polygon points="100,96 102,103 109,103 103,107 105,114 100,110 95,114 97,107 91,103 98,103" fill="#932136" />
                            </g>
                        </svg>
                    </div>

                    <!-- HEADER -->
                    <div class="header">
                        <!-- Garuda di kiri -->
                        <div class="header-col header-left-garuda">
                            <div class="garuda-wrap">
                                <svg viewBox="0 0 200 220" xmlns="http://www.w3.org/2000/svg">
                                    <g fill="#932136">
                                        <ellipse cx="100" cy="115" rx="28" ry="38" />
                                        <circle cx="100" cy="68" r="18" />
                                        <path d="M100 80 Q108 88 104 95 Q100 90 96 95 Q92 88 100 80Z" />
                                        <path d="M86 62 L90 50 L94 62Z" />
                                        <path d="M94 60 L98 47 L102 60Z" />
                                        <path d="M102 62 L106 50 L110 62Z" />
                                        <path d="M72 110 Q40 95 18 85 Q25 100 30 115 Q50 108 72 118Z" />
                                        <path d="M70 118 Q38 112 15 110 Q20 125 28 135 Q50 122 70 128Z" />
                                        <path d="M69 128 Q40 130 20 135 Q28 148 38 154 Q56 138 69 138Z" />
                                        <path d="M128 110 Q160 95 182 85 Q175 100 170 115 Q150 108 128 118Z" />
                                        <path d="M130 118 Q162 112 185 110 Q180 125 172 135 Q150 122 130 128Z" />
                                        <path d="M131 128 Q160 130 180 135 Q172 148 162 154 Q144 138 131 138Z" />
                                        <path d="M85 148 Q88 165 82 180 L88 178 Q92 165 92 150Z" />
                                        <path d="M92 150 Q93 168 90 183 L96 183 Q98 168 100 150Z" />
                                        <path d="M100 150 Q102 168 104 183 L110 183 Q107 168 108 150Z" />
                                        <path d="M108 150 Q112 165 118 178 L112 180 Q108 165 115 148Z" />
                                        <path d="M88 152 Q82 162 76 166 L80 168 Q86 162 92 155Z" />
                                        <path d="M112 152 Q118 162 124 166 L120 168 Q114 162 108 155Z" />
                                        <path d="M76 166 Q70 170 65 168 L66 172 Q71 174 77 170Z" />
                                        <path d="M74 168 Q68 174 64 176 L66 179 Q71 177 75 172Z" />
                                        <path d="M73 171 Q68 178 66 182 L69 183 Q72 179 76 174Z" />
                                        <path d="M124 166 Q130 170 135 168 L134 172 Q129 174 123 170Z" />
                                        <path d="M126 168 Q132 174 136 176 L134 179 Q129 177 125 172Z" />
                                        <path d="M127 171 Q132 178 134 182 L131 183 Q128 179 124 174Z" />
                                        <path d="M88 110 Q88 95 100 90 Q112 95 112 110 L112 135 Q100 145 88 135Z" fill="none" stroke="#932136" stroke-width="2" />
                                        <line x1="88" y1="117" x2="112" y2="117" stroke="#932136" stroke-width="1.5" />
                                        <polygon points="100,96 102,103 109,103 103,107 105,114 100,110 95,114 97,107 91,103 98,103" fill="#932136" />
                                    </g>
                                </svg>
                            </div>
                        </div>

                        <!-- Logo LAMDEPILAR (tengah-kiri) -->
                        <div class="header-col header-left" style="width:12%;">
                            <div class="logo-wrap">
                                <!-- Placeholder logo LAMDEPILAR -->
                                <svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="60" cy="60" r="55" fill="none" stroke="#932136" stroke-width="4" />
                                    <circle cx="60" cy="60" r="45" fill="none" stroke="#932136" stroke-width="2" />
                                    <text x="60" y="52" font-family="Montserrat,sans-serif" font-size="10" font-weight="700" fill="#932136" text-anchor="middle">LAM</text>
                                    <text x="60" y="65" font-family="Montserrat,sans-serif" font-size="7" font-weight="600" fill="#932136" text-anchor="middle">DEPILAR</text>
                                    <!-- checkmark -->
                                    <path d="M44 75 L54 85 L76 60" fill="none" stroke="#932136" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>

                        <!-- Tengah: nama lembaga -->
                        <div class="header-col header-mid">
                            <div class="inst-name">
                                Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur
                                (LAMDEPILAR)
                            </div>
                            <div class="inst-sub">
                                Sertifikat Akreditasi Program Studi
                            </div>
                        </div>

                        <!-- Kanan: nomor & tanggal -->
                        <div class="header-col header-right">
                            <div><strong>Nomor</strong></div>
                            <div>0241222</div>
                            <div style="margin-top:4px;"><strong>Tanggal</strong></div>
                            <div>13 Desember 2024</div>
                        </div>
                    </div>

                    <!-- TITLE -->
                    <div class="title">
                        <h1>SERTIFIKAT AKREDITASI</h1>
                        <div class="no">Nomor: <strong>0241222</strong></div>
                    </div>

                    <!-- BODY -->
                    <div class="body">
                        <div class="intro">
                            Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)
                            dengan ini menyatakan bahwa:
                        </div>

                        <!-- DETAILS — TANPA KOTAK, LANGSUNG TAMPIL -->
                        <div class="details">
                            <div class="details-row">
                                <span class="details-label">Program Studi</span>
                                <span class="details-sep">:</span>
                                <span class="details-value">DEPILAR Desain S1</span>
                            </div>
                            <div class="details-divider"></div>
                            <div class="details-row">
                                <span class="details-label">Jenjang</span>
                                <span class="details-sep">:</span>
                                <span class="details-value">Sarjana (Strata 1)</span>
                            </div>
                            <div class="details-divider"></div>
                            <div class="details-row">
                                <span class="details-label">Perguruan Tinggi</span>
                                <span class="details-sep">:</span>
                                <span class="details-value">Universitas DEPILAR</span>
                            </div>
                        </div>

                        <!-- Badge akreditasi -->
                        <div class="badge" style="background: linear-gradient(135deg,#ffe58a 0%,#ffd24d 45%,#fff1b8 100%);">
                            <div class="badge-label">Status Akreditasi</div>
                            <div class="badge-rank">Unggul</div>
                        </div>

                        <div class="validity">
                            Sertifikat akreditasi ini berlaku selama
                            <strong>5 (lima) tahun</strong><br>
                            terhitung sejak tanggal <strong>13 Desember 2024</strong><br>
                            sampai dengan tanggal <strong>13 Desember 2029</strong>
                        </div>
                    </div>

                    <!-- BOTTOM: QR + TANDA TANGAN -->
                    <div class="bottom">
                        <div class="bottom-left">
                            <div class="seal-row">
                                <div class="qr">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://lamdepilar.id/verifikasi/0241222" alt="QR Verifikasi">
                                </div>
                            </div>
                        </div>

                        <div class="bottom-right">
                            <div class="sig-loc">Jakarta, 13 Desember 2024</div>
                            <div class="sig-role">Ketua Dewan Eksekutif</div>
                            <div class="sig-name">
                                Dr. Ar. Yulianto Purwono Prihatmaji, IPM., IAI
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER -->
                    <div class="footer">
                        Sertifikat ini diterbitkan secara resmi oleh LAMDEPILAR sebagai bukti pemenuhan standar akreditasi.<br>
                        Verifikasi keaslian sertifikat dapat dilakukan melalui laman resmi LAMDEPILAR.
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>
</html>
