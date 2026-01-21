<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #0d6efd;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .content {
            background: #f8f9fa;
            padding: 30px;
            border: 1px solid #dee2e6;
        }

        .info-box {
            background: white;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #0d6efd;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 12px;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🔔 Pengingat Akreditasi</h2>
        </div>

        <div class="content">
            <p>Kepada Yth,</p>
            <p><strong>{{ $pengajuan->studyProgram->name }}</strong></p>

            <div class="info-box">
                <p><strong>Nomor Permohonan:</strong> {{ $pengajuan->nomor_pengajuan }}</p>
                <p><strong>Tahun Akreditasi:</strong> {{ $pengajuan->tahun_akreditasi }}</p>
            </div>

            <p>{{ $pesanPengingat }}</p>

            <p>Untuk melanjutkan proses akreditasi, silakan:</p>
            <ol>
                <li>Login ke sistem</li>
                <li>Submit surat permohonan akreditasi</li>
                <li>Ikuti tahapan berikutnya sesuai panduan</li>
            </ol>

            <center>
                <a href="{{ route('pengajuan') }}" class="button">
                    Lihat Pengajuan
                </a>
            </center>

            <p style="margin-top: 30px;">Jika ada pertanyaan, hubungi Dewan Eksekutif (DE) LAMDEPILAR.</p>

            <p>Hormat kami,<br><strong>Tim Akreditasi</strong></p>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} Sistem Akreditasi. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
