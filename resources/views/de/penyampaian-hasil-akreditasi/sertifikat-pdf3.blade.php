<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <!-- Paksa viewport fixed 1122px — ukuran tidak berubah di layar apapun -->
    <meta name="viewport" content="width=1122">
    <title>Sertifikat Akreditasi LAMDEPILAR</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            width: 1122px;
            min-height: 794px;
            background: #cdc5ab;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 794px;
            padding: 20px 0;
        }

        /* ═══════════════════════════════════════
     CERTIFICATE WRAPPER — fixed 1082×754px
  ═══════════════════════════════════════ */
        .cert-wrap {
            width: 1082px;
            height: 750px;
            background: #fbf9f7;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 36px rgba(0, 0, 0, 0.28);
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

        /* posisi */
        .corner-tl {
            top: 6px;
            left: 6px;
        }

        .corner-tr {
            top: 6px;
            right: 6px;
            transform: scaleX(-1);
            /* mirror horizontal */
        }

        .corner-bl {
            bottom: 6px;
            left: 6px;
            transform: scaleY(-1);
            /* mirror vertical */
        }

        .corner-br {
            bottom: 6px;
            right: 6px;
            transform: scale(-1, -1);
            /* mirror dua arah */
        }

        /* ── Watermark ── */
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

        /* ── Deco pattern bottom-right ── */
        .deco-pattern {
            position: absolute;
            right: 18px;
            bottom: 170px;
            width: 185px;
            height: 155px;
            pointer-events: none;
            z-index: 1;
        }

        /* ═══════════════════════════════════════
     INNER CONTENT
  ═══════════════════════════════════════ */
        .cert-inner {
            position: relative;
            padding: 75px 52px 24px 52px;
            z-index: 5;
            height: 750px;
            display: flex;
            flex-direction: column;
        }

        /* ── Header row ── */
        .cert-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            flex-shrink: 0;
        }

        /* ── Logo left ── */
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

        .hr-status {
            border: none;
            border-top: 1px solid #C18A2A;
            margin: 0 auto 8px auto;
            width: 180px;
        }

        /* ── Header center ── */
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

        /* ── Diamond divider ── */
        .diamond-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0 0 0;
        }

        .diamond-divider .dline {
            height: 0.5px;
            width: 210px;
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

        /* ── Header right ── */
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

        /* ── Main title ── */
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

        /* ── Nomor line ── */
        .nomor-line {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 0px;
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

        /* ── Statement ── */
        .cert-statement {
            text-align: center;
            margin-top: 15px;
            font-size: 15px;
            color: #111;
            line-height: 1.55;
            flex-shrink: 0;
        }

        /* ── Info table ── */
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

        .info-row:first-child {
            border-top: none;
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
            background-position: center;
            background-size: 40vh auto;
            opacity: 0.06;
            -webkit-mask-image: linear-gradient(to bottom, black 30%, transparent 65%);
            mask-image: linear-gradient(to bottom, black 30%, transparent 65%);
            pointer-events: none;
            z-index: 0;
        }

        /* ── Status section ── */
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

        .padi-icon.flip {
            transform: scaleX(-1);
        }

        .status-text {
            font-family: 'Times New Roman', Times, serif;
            font-size: 40px;
            font-weight: 500;
            letter-spacing: 0px;
            color: #8E101A;
            line-height: 1;
            white-space: nowrap;
            z-index: 2;
        }

        /* ── Status divider ── */
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

        /* ── Validity ── */
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

        /* ── Bottom row ── */
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
            margin-top: auto;
            transform: translateX(-20px);
            padding-top: 0px;
            flex-shrink: 0;
        }

        /* ── QR section ── */
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

        /* ── Signature section ── */
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
     PRINT
  ═══════════════════════════════════════ */
        @media print {

            html,
            body {
                width: 297mm;
                min-height: 210mm;
                background: white;
                padding: 0;
            }

            body {
                display: block;
            }

            .cert-wrap {
                width: 277mm;
                height: 190mm;
                box-shadow: none;
                margin: 10mm auto;
            }

            @page {
                size: A4 landscape;
                margin: 0;
            }
        }

    </style>
</head>
<body>

    <div class="cert-wrap">

        <!-- ── Corner SVGs ── -->
        <svg class="cert-border-svg" viewBox="0 0 1082 700" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="
    M 14 38
    V 30 H 22 V 22 H 30 V 14 H 38
    H 1044
    V 22 H 1052 V 30 H 1060 V 38 H 1068
    V 662
    H 1060 V 670 H 1052 V 678 H 1044 V 686
    H 38
    V 678 H 30 V 670 H 22 V 662 H 14
    V 38 Z
  " stroke="#C18A2A" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter" fill="none" />
        </svg>

        <!-- ── Watermark ── -->
        <div class="watermark">
            <img src="{{ asset('assets/images/elemen_depilar.png') }}" alt="Watermark DEPILAR" style="width:100%;height:100%;object-fit:contain;">
        </div>

        <!-- ── Deco pattern bottom right ── -->
        <div class="deco-pattern">
            <img src="{{ asset('assets/images/elemen_depilar.png') }}" alt="Elemen Depilar" style="width:100%;height:100%;object-fit:contain;">
        </div>

        <!-- ══════════════════════════════
       INNER CONTENT
  ══════════════════════════════ -->
        <div class="cert-inner">
            <div class="watermark-logo"></div>
            <!-- HEADER -->
            <div class="cert-header">

                <!-- Logo Left -->
                <div class="logo-left">
                    <div class="logo-icon">
                        <img src="{{ asset('assets/images/logo-square.png') }}" alt="Logo DEPILAR">
                    </div>
                    <div class="logo-text">DEPILAR</div>
                </div>

                <!-- Header Center -->
                <div class="header-center">
                    <div class="line1">LEMBAGA AKREDITASI MANDIRI</div>
                    <div class="line2">DESAIN PERENCANAAN LINGKUNGAN ARSITEKTUR</div>
                    <div class="line3">(LAMDEPILAR)</div>
                    <div class="diamond-divider">
                        <span class="dline"></span>
                        <span class="dia"></span>
                        <span class="dline"></span>
                    </div>
                </div>

                <!-- Header Right -->
                <div class="header-right">
                    <div class="hrow">
                        <span class="hlabel">Nomor</span>
                        <span>&nbsp;: LAMDEPILAR/2026/004</span>
                    </div>
                    <div class="hrow">
                        <span class="hlabel">Tanggal</span>
                        <span>&nbsp;: 29 April 2026</span>
                    </div>
                </div>

            </div><!-- /.cert-header -->

            <!-- MAIN TITLE -->
            <div class="cert-title">SERTIFIKAT&nbsp;&nbsp;AKREDITASI</div>

            <!-- NOMOR LINE -->
            <div class="nomor-line">
                <span class="nline"></span>
                <span class="ntext">Nomor:&nbsp;<strong>LAMDEPILAR/2026/004</strong></span>
                <span class="nline"></span>
            </div>

            <!-- STATEMENT -->
            <div class="cert-statement">
                Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur<br>
                (LAMDEPILAR) dengan ini menyatakan bahwa:
            </div>

            <!-- INFO TABLE -->
            <div class="info-table">

                <div class="info-row">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Left page — melebar ke bawah -->
                            <path d="M12 5.5C10.3 4.5 8.4 4 6 4H4V17.5H6.5C8.8 17.5 10.7 18.1 12 19V5.5Z" stroke="#B8862B" stroke-width="1.2" stroke-linejoin="round" />
                            <!-- Right page — melebar ke bawah -->
                            <path d="M12 5.5C13.7 4.5 15.6 4 18 4H20V17.5H17.5C15.2 17.5 13.3 18.1 12 19V5.5Z" stroke="#B8862B" stroke-width="1.2" stroke-linejoin="round" />
                            <!-- Left outer edge — lebih lebar di bawah -->
                            <path d="M4 5.5H2.5V20.5H8C9.5 20.5 10.9 21 11.9 21.7" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                            <!-- Right outer edge — lebih lebar di bawah -->
                            <path d="M20 5.5H21.5V20.5H16C14.5 20.5 13.1 21 12.1 21.7" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="info-label">Program Studi</div>
                    <span class="info-vline"></span>
                    <div class="info-value">DEPILAR Desain S2</div>
                </div>

                <div class="info-row">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Cap top (diamond) -->
                            <path d="M2 9L12 4.5L22 9L12 13.5L2 9Z" stroke="#B8862B" stroke-width="1.2" stroke-linejoin="round" />
                            <!-- Cap band — lebih lebar & turun lebih dalam -->
                            <path d="M6.5 11.5V17C6 17 8.8 19.5 12 19.5C15.2 19.5 18 17 18 17V11.5" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                            <!-- Tassel string -->
                            <path d="M4 10V14" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" />
                            <!-- Tassel dot -->
                            <circle cx="4" cy="15.2" r="0.85" fill="#B8862B" />
                        </svg>
                    </div>
                    <div class="info-label">Jenjang</div>
                    <span class="info-vline"></span>
                    <div class="info-value">Magister (Strata 2)</div>
                </div>

                <div class="info-row">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Roof -->
                            <path d="M2.5 9L12 4L21.5 9" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                            <!-- Top bar -->
                            <path d="M3.5 9.8H20.5" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" />

                            <!-- Outer left wall -->
                            <path d="M5 10.5V17.5" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" />
                            <!-- Outer right wall -->
                            <path d="M19 10.5V17.5" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" />

                            <!-- 3 pilar tengah — jarak sama: 5→(9.67)→(14.33)→19, posisi pilar di 9.7, 12, 14.3 -->
                            <path d="M7.5 10.5V17.5" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" />
                            <path d="M11.5 10.5V17.5" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" />
                            <path d="M13.5 10.5V17.5" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" />
                            <path d="M17 10.5V17.5" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" />

                            <!-- Base line 1 -->
                            <path d="M3.5 18H20.5" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" />
                            <!-- Base line 2 -->
                            <path d="M2 19.8H22" stroke="#B8862B" stroke-width="1.2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="info-label">Perguruan Tinggi</div>
                    <span class="info-vline"></span>
                    <div class="info-value">Universitas DEPILAR</div>
                </div>

            </div><!-- /.info-table -->

            <!-- STATUS AKREDITASI -->
            <div class="status-section">
                <hr class="hr-status">

                <img class="padi-icon padi-left" src="{{ asset('assets/images/elemen_padi_no_bg.svg') }}" alt="Padi Kiri">
                <img class="padi-icon padi-right" src="{{ asset('assets/images/elemen_padi_no_bg.svg') }}" alt="Padi Kanan">

                <div class="status-label">STATUS AKREDITASI</div>

                <div class="status-row">
                    <div class="status-text">TERAKREDITASI UNGGUL</div>
                </div>

                <div class="status-divider">
                    <span class="sdline"></span>
                    <span class="sdia"></span>
                    <span class="sdline"></span>
                </div>
            </div>

            <!-- VALIDITY -->
            <div class="validity">
                <div class="v1">Sertifikat ini berlaku selama <strong>5 (lima) tahun</strong></div>
                <div class="v2">
                    <span>29 April 2026</span>
                    <span class="vline"></span>
                    <span>29 April 2031</span>
                </div>
            </div>

            <!-- BOTTOM ROW -->
            <div class="cert-bottom">

                <div class="qr-section">
                    <div class="qr-frame">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=https%3A%2F%2Flamdepilar.or.id%2Fverify" alt="QR Code Verifikasi">
                    </div>
                    <div class="qr-text">
                        Verifikasi sertifikat melalui<br>
                        <strong>lamdepilar.or.id/verify</strong>
                    </div>
                </div>

                <div class="sig-section">
                    <div class="sig-city">Jakarta, 29 April 2026</div>
                    <div class="sig-title">Ketua Dewan Eksekutif</div>
                    <svg class="sig-img" width="138" height="52" viewBox="0 0 138 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                        {{-- <path d="M18 40 Q28 18 38 28 Q48 38 57 20 Q66 8 77 26 Q88 40 99 33 Q110 24 119 36" stroke="#111" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M58 40 Q72 46 88 42" stroke="#111" stroke-width="1.4" fill="none" stroke-linecap="round" /> --}}
                    </svg>
                    <div class="sig-line">
                        <span class="sl"></span>
                        <span class="sd"></span>
                        <span class="sl"></span>
                    </div>
                    <div class="sig-name">Dr. Ar. Yulianto Purwono Prihatmaji, IPM., IAI</div>
                </div>

            </div><!-- /.cert-bottom -->

        </div><!-- /.cert-inner -->

    </div><!-- /.cert-wrap -->

</body>
</html>
