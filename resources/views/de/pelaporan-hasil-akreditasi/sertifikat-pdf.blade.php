{{-- resources/views/de/pelaporan-hasil-akreditasi/sertifikat-pdf.blade.php --}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Akreditasi - {{ $studyProgram->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            color: #000;
        }

        /* ===== Frame ornamen seperti contoh ===== */
        .certificate-frame {
            position: relative;
            padding: 18px;
            border-radius: 10px;
            background: #fff;
            border: 6px solid #9aa3b2;
        }

        .certificate-frame:before {
            content: "";
            position: absolute;
            inset: 10px;
            border-radius: 8px;
            border: 3px solid #c7cbd4;
            pointer-events: none;
        }

        .certificate-inner {
            position: relative;
            padding: 22px 28px 18px 28px;
            border-radius: 8px;
            border: 2px solid #932136;
            min-height: 860px;
        }

        .certificate-inner>* {
            position: relative;
            z-index: 1;
        }

        /* watermark halus */
        .watermark {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 92px;
            font-weight: bold;
            color: rgba(31, 74, 168, 0.06);
            transform: rotate(-25deg);
            pointer-events: none;
            user-select: none;
        }

        /* ===== Header 3 kolom (logo - judul lembaga - info kecil) ===== */
        .header {
            display: table;
            width: 100%;
            margin-top: 6px;
            padding-bottom: 14px;
            border-bottom: 2px solid #932136;
        }

        .header-col {
            display: table-cell;
            vertical-align: middle;
        }

        .header-left {
            width: 18%;
        }

        .header-mid {
            width: 64%;
            text-align: center;
        }

        .header-right {
            width: 18%;
            text-align: right;
            font-size: 11px;
            color: #333;
        }

        .logo-wrap {
            width: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .inst-name {
            font-size: 16px;
            font-weight: bold;
            color: #932136;
            line-height: 1.25;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .inst-sub {
            margin-top: 4px;
            font-size: 12px;
            color: #111;
            line-height: 1.3;
        }

        /* ===== Judul besar seperti contoh ===== */
        .title {
            text-align: center;
            margin: 22px 0 10px 0;
        }

        .title h1 {
            font-size: 34px;
            font-weight: 800;
            color: #932136;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .title .no {
            margin-top: 8px;
            font-size: 12.5px;
            color: #333;
        }

        /* ===== Isi ===== */
        .body {
            margin-top: 16px;
            text-align: center;
        }

        .intro {
            font-size: 14.5px;
            line-height: 1.75;
            margin: 8px 0 14px 0;
        }

        .details {
            width: 92%;
            margin: 0 auto;
            border: 2px solid #932136;
            border-radius: 10px;
            padding: 18px 18px 10px 18px;
            background: #fbfcff;
            text-align: left;
        }

        .row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .lbl {
            display: table-cell;
            width: 34%;
            font-weight: bold;
            padding-right: 10px;
        }

        .col {
            display: table-cell;
            width: 3%;
        }

        .val {
            display: table-cell;
            width: 63%;
        }

        /* ===== Badge akreditasi mirip contoh (kotak tengah) ===== */
        .badge {
            width: 400px;
            margin: 18px auto 0 auto;
            border-radius: 10px;
            border: 2.5px solid #a67c00;
            background: linear-gradient(135deg, #ffe58a 0%, #ffd24d 45%, #fff1b8 100%);
            padding: 14px 14px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .badge .badge-label {
            font-size: 12px;
            letter-spacing: .8px;
            font-weight: bold;
            color: #4a3a00;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .badge .badge-rank {
            font-size: 34px;
            font-weight: 900;
            color: #932136;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .validity {
            margin-top: 16px;
            font-size: 13.5px;
            color: #222;
            line-height: 1.7;
        }

        /* ===== Area bawah: cap + QR + tanda tangan kanan ===== */
        .bottom {
            margin-top: 26px;
            display: table;
            width: 100%;
        }

        .bottom-left {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
            padding-left: 8px;
        }

        .bottom-right {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
            text-align: center;
        }

        .seal-row {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .seal {
            width: 92px;
            height: 92px;
            border-radius: 50%;
            border: 2px solid #b8860b;
            background: radial-gradient(circle at 30% 30%, #fff6bf 0%, #ffd24d 40%, #e2b600 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            color: #4a3a00;
            text-align: center;
            padding: 8px;
        }

        .qr {
            width: 108px;
            height: 108px;
            border: 1px solid #c7cbd4;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #fff;
        }

        .qr img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .sig-loc {
            font-size: 12.5px;
            margin-bottom: 6px;
        }

        .sig-role {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 62px;
        }

        .sig-name {
            display: inline-block;
            font-size: 14px;
            font-weight: bold;
            border-bottom: 1.8px solid #000;
            padding: 0 8px 6px 8px;
            line-height: 1.2;
        }

        .footer {
            margin-top: 16px;
            padding-top: 10px;
            border-top: 1px solid #932136;
            text-align: center;
            font-size: 10.5px;
            color: #444;
            line-height: 1.5;
        }

        @media print {
            body {
                padding: 0;
            }
        }

        .watermark-logo {
            position: absolute;
            inset: 0;
            background-image: url('https://daisy.sp3stab.id/assets/images/logo-square.png');
            background-repeat: no-repeat;
            background-position: center;
            background-size: 420px auto;
            /* ukuran emboss */
            opacity: 0.06;
            /* efek emboss */
            pointer-events: none;
            z-index: 0;
        }

        @font-face {
            font-family: 'Montserrat';
            src: url('{{ public_path('assets/fonts/Montserrat/static/Montserrat-Regular.ttf') }}') format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'Montserrat';
            src: url('{{ public_path('assets/fonts/Montserrat/static/Montserrat-Bold.ttf') }}') format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        @font-face {
            font-family: 'Montserrat';
            src: url('{{ public_path('assets/fonts/Montserrat/static/Montserrat-SemiBold.ttf') }}') format('truetype');
            font-weight: 600;
            font-style: normal;
        }

        .title h1,
        .inst-name {
            font-weight: 700;
            letter-spacing: 2px;
        }

        .details,
        .intro,
        .validity {
            font-weight: 400;
        }

    </style>
</head>
<body>
    <div class="certificate-frame">
        <div class="certificate-inner">
            <div class="watermark-logo"></div>

            {{-- ===== HEADER ===== --}}
            <div class="header">
                <div class="header-col header-left">
                    {{-- Logo: ganti path sesuai aset Anda --}}
                    {{-- <div class="logo-wrap">
                        <img src="{{ public_path('images/logo-lamdepilar.png') }}" alt="Logo LAMDEPILAR">
                </div> --}}
                <div class="logo-wrap">
                    {{-- <img src="{{ public_path('assets/images/logo.png') }}" alt="Logo LAMDEPILAR"> --}}
                    <img src="https://daisy.sp3stab.id/assets/images/logo.png" alt="Logo LAMDEPILAR">
                </div>
            </div>

            <div class="header-col header-mid">
                <div class="inst-name">
                    Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur
                    (LAMDEPILAR)
                </div>
                <div class="inst-sub">
                    Sertifikat Akreditasi Program Studi
                </div>
            </div>

            <div class="header-col header-right">
                <div><strong>Nomor</strong></div>
                <div>{{ $nomorSertifikat }}</div>
                <div style="margin-top:6px;"><strong>Tanggal</strong></div>
                <div>{{ \App\Libraries\Date::tglIndo($tanggalPenetapan) }}</div>
            </div>
        </div>

        {{-- ===== TITLE ===== --}}
        <div class="title">
            <h1>SERTIFIKAT AKREDITASI</h1>
            <div class="no">Nomor: <strong>{{ $nomorSertifikat }}</strong></div>
        </div>

        {{-- ===== BODY ===== --}}
        <div class="body">
            <div class="intro">
                Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)
                dengan ini menyatakan bahwa:
            </div>

            <div class="details">
                <div class="row">
                    <div class="lbl">Program Studi</div>
                    <div class="col">:</div>
                    <div class="val"><strong>{{ $studyProgram->name }}</strong></div>
                </div>
                <div class="row">
                    <div class="lbl">Jenjang</div>
                    <div class="col">:</div>
                    <div class="val"><strong>{{ $studyProgram->degreeLevel->name ?? '-' }}</strong></div>
                </div>
                <div class="row">
                    <div class="lbl">Perguruan Tinggi</div>
                    <div class="col">:</div>
                    <div class="val"><strong>{{ $university->name }}</strong></div>
                </div>
            </div>

            <div class="badge">
                <div class="badge-label">Peringkat Akreditasi</div>
                <div class="badge-rank">{{ strtoupper($hasil->peringkat_akreditasi) }}</div>
            </div>

            <div class="validity">
                Sertifikat akreditasi ini berlaku selama
                <strong>{{ $masaBerlaku['tahun'] }} ({{ \App\Libraries\Fungsi::terbilang($masaBerlaku['tahun']) }}) tahun</strong><br>
                terhitung sejak tanggal
                <strong>{{ \App\Libraries\Date::tglIndo($masaBerlaku['tanggal_mulai']) }}</strong><br>
                sampai dengan tanggal
                <strong>{{ \App\Libraries\Date::tglIndo($masaBerlaku['tanggal_berakhir']) }}</strong>
            </div>
        </div>

        {{-- ===== BOTTOM: SEAL/QR + SIGNATURE ===== --}}
        <div class="bottom">
            <div class="bottom-left">
                <div class="seal-row">
                    <div class="seal">
                        CAP /<br> STEMPEL<br> RESMI
                    </div>

                    {{-- QR (opsional): jika Anda punya url/path qr, pasang di sini --}}
                    {{-- <div class="qr">
                            <img src="{{ public_path('storage/qr/'.$qrFileName) }}" alt="QR Verifikasi">
                </div> --}}
                <div class="qr">
                    <div style="font-size:10px; color:#666; text-align:center;">
                        QR<br>VERIFIKASI
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom-right">
            <div class="sig-loc">
                Jakarta, {{ \App\Libraries\Date::tglIndo($tanggalPenetapan) }}
            </div>
            <div class="sig-role">Ketua Dewan Eksekutif</div>
            <div class="sig-name">
                Dr. Ar. Yulianto Purwono Prihatmaji, IPM., IAI
            </div>
        </div>
    </div>

    {{-- ===== FOOTER ===== --}}
    <div class="footer">
        Sertifikat ini diterbitkan secara resmi oleh LAMDEPILAR sebagai bukti pemenuhan standar akreditasi.<br>
        (Informasi verifikasi dapat disesuaikan dengan sistem/laman verifikasi LAMDEPILAR.)
    </div>
    </div>
    </div>
</body>
</html>
