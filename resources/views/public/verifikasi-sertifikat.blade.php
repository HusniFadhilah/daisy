<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Sertifikat Akreditasi — LAMDEPILAR</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f5f0e8;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .verify-card {
            background: #fbf9f7;
            border: 1.5px solid #C18A2A;
            border-radius: 12px;
            max-width: 600px;
            width: 100%;
            padding: 2.5rem 2rem;
            box-shadow: 0 8px 32px rgba(193, 138, 42, 0.12);
        }

        .brand-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e8d9b0;
        }

        .brand-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        .brand-text {
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: 3px;
            color: #9B0F1B;
            line-height: 1;
        }

        .brand-sub {
            font-size: 0.7rem;
            color: #666;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .status-badge-valid {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #d4edda;
            border: 1.5px solid #28a745;
            color: #155724;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            letter-spacing: 1px;
        }

        .status-badge-expired {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff3cd;
            border: 1.5px solid #ffc107;
            color: #856404;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            letter-spacing: 1px;
        }

        .status-badge-invalid {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f8d7da;
            border: 1.5px solid #dc3545;
            color: #721c24;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            letter-spacing: 1px;
        }

        .info-table td {
            padding: 0.5rem 0.75rem;
            font-size: 0.88rem;
        }

        .info-table td:first-child {
            color: #666;
            white-space: nowrap;
            width: 45%;
        }

        .info-table td:last-child {
            font-weight: 600;
            color: #222;
        }

        .info-table tr:not(:last-child) td {
            border-bottom: 1px solid #f0e8d5;
        }

        .peringkat-display {
            font-family: 'Times New Roman', Times, serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: #8E101A;
            letter-spacing: 1px;
        }

        .divider-gold {
            border: none;
            border-top: 1px solid #C18A2A;
            opacity: 0.5;
            margin: 1.2rem 0;
        }

        .footer-note {
            font-size: 0.72rem;
            color: #888;
            text-align: center;
            margin-top: 1.5rem;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="verify-card">
        {{-- Header --}}
        <div class="brand-header">
            <img src="{{ asset('assets/images/logo-square.png') }}" alt="Logo" class="brand-logo">
            <div>
                <div class="brand-text">DEPILAR</div>
                <div class="brand-sub">LEMBAGA AKREDITASI MANDIRI — LAMDEPILAR</div>
            </div>
        </div>

        <h5 class="mb-3" style="font-weight:700; color:#222; letter-spacing:0.5px;">
            <i class="bi bi-patch-check"></i> Verifikasi Sertifikat Akreditasi
        </h5>

        @if(!$valid)
        {{-- TIDAK DITEMUKAN --}}
        <div class="text-center py-4">
            <div class="status-badge-invalid mb-3">
                <i class="bi bi-x-circle-fill"></i> TIDAK VALID
            </div>
            <p class="text-muted mt-3 mb-1">Sertifikat dengan nomor berikut tidak ditemukan dalam sistem.</p>
            <code class="text-danger">{{ $nomorSertifikat }}</code>
            <p class="text-muted small mt-3">
                Jika Anda yakin nomor ini benar, hubungi LAMDEPILAR untuk konfirmasi.
            </p>
        </div>

        @else
        {{-- STATUS BADGE --}}
        <div class="text-center mb-4">
            @if($masihBerlaku === true)
            <div class="status-badge-valid">
                <i class="bi bi-shield-check-fill"></i> SERTIFIKAT VALID
            </div>
            @elseif($masihBerlaku === false)
            <div class="status-badge-expired">
                <i class="bi bi-clock-history"></i> MASA BERLAKU HABIS
            </div>
            @else
            <div class="status-badge-valid">
                <i class="bi bi-check-circle-fill"></i> SERTIFIKAT DITEMUKAN
            </div>
            @endif
        </div>

        <hr class="divider-gold">

        {{-- PERINGKAT --}}
        <div class="text-center mb-3">
            <small class="text-muted d-block" style="font-size:.75rem; letter-spacing:2px; text-transform:uppercase;">STATUS AKREDITASI</small>
            <div class="peringkat-display mt-1">{{ strtoupper($peringkat) }}</div>
        </div>

        <hr class="divider-gold">

        {{-- INFO TABLE --}}
        <table class="info-table w-100">
            <tr>
                <td>Nomor Sertifikat</td>
                <td>{{ $nomorSertifikat }}</td>
            </tr>
            <tr>
                <td>Program Studi</td>
                <td>{{ $pengajuan->studyProgram->name }}</td>
            </tr>
            <tr>
                <td>Jenjang</td>
                <td>{{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Perguruan Tinggi</td>
                <td>{{ $pengajuan->studyProgram->university->name }}</td>
            </tr>
            <tr>
                <td>Tanggal Diterbitkan</td>
                <td>{{ $tanggalSertifikat ? \App\Libraries\Date::tglIndo($tanggalSertifikat) : '-' }}</td>
            </tr>
            @if($masaBerlakuTahun)
            <tr>
                <td>Masa Berlaku</td>
                <td>{{ $masaBerlakuTahun }} Tahun</td>
            </tr>
            @endif
            @if($tanggalBerakhir)
            <tr>
                <td>Berlaku Sampai</td>
                <td>
                    {{ \App\Libraries\Date::tglIndo($tanggalBerakhir) }}
                    @if($masihBerlaku === false)
                    <span class="badge bg-warning text-dark ms-1" style="font-size:.7rem;">Habis</span>
                    @endif
                </td>
            </tr>
            @endif
            <tr>
                <td>Jenis Akreditasi</td>
                <td>{{ $pengajuan->jenis_akreditasi_title ?? '-' }}</td>
            </tr>
        </table>
        @endif

        <p class="footer-note">
            Verifikasi ini dilakukan secara otomatis berdasarkan data sistem LAMDEPILAR.<br>
            Diakses pada {{ \App\Libraries\Date::tglIndo(now()) }}<br>
            <strong>lamdepilar.or.id</strong>
        </p>
    </div>
</body>
</html>
