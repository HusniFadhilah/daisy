<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan Review Ulang LED</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-top: none;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }

        .alert {
            background: #d1ecf1;
            border-left: 4px solid #0dcaf0;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .info-box p {
            margin: 5px 0;
        }

        .info-box strong {
            color: #0d6efd;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }

        .btn:hover {
            background: #0a58ca;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #6c757d;
            font-size: 12px;
        }

    </style>
</head>
<body>
    <div class="header">
        <h1>🔄 Review Ulang LED</h1>
        <p>Permintaan Validasi Ulang</p>
    </div>

    <div class="content">
        <p>Yth. Bapak/Ibu Validator,</p>

        <div class="alert">
            <strong>📢 Pemberitahuan</strong><br>
            Prodi telah menyelesaikan revisi LED yang Anda minta. Mohon untuk melakukan review ulang.
        </div>

        <div class="info-box">
            <p><strong>Nomor Permohonan:</strong> {{ $pengajuan->nomor_pengajuan }}</p>
            <p><strong>Program Studi:</strong> {{ $studyProgram->name }}</p>
            <p><strong>Jenjang:</strong> {{ $studyProgram->degreeLevel->name }}</p>
            <p><strong>Universitas:</strong> {{ $studyProgram->university->name }}</p>
            <p><strong>Tahun Akreditasi:</strong> {{ $pengajuan->tahun_akreditasi }}</p>
        </div>

        <h3>📝 Informasi:</h3>
        <ul>
            <li>Prodi telah memperbaiki LED sesuai dengan poin revisi yang Anda berikan</li>
            <li>LED telah di-update di sistem dan siap untuk direview ulang</li>
            <li>Mohon untuk melakukan validasi ulang sesegera mungkin</li>
        </ul>

        <h3>✅ Tugas Anda:</h3>
        <ol>
            <li>Login ke sistem LAMDEPILAR</li>
            <li>Periksa LED yang telah direvisi</li>
            <li>Verifikasi bahwa semua poin revisi telah diperbaiki</li>
            <li>Approve atau request revisi lanjutan jika diperlukan</li>
        </ol>

        <center>
            <a href="{{ url('/validator/borang') }}" class="btn">
                📋 Lihat LED & Mulai Review
            </a>
        </center>

        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
            <p style="margin: 0; font-size: 13px; color: #856404;">
                <strong>⏰ Reminder:</strong> Mohon untuk menyelesaikan review dalam waktu 7 hari kerja
                agar proses akreditasi dapat berjalan sesuai jadwal.
            </p>
        </div>
    </div>

    <div class="footer">
        <p>Email ini dikirim secara otomatis oleh sistem LAMDEPILAR</p>
        <p>&copy; {{ date('Y') }} Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)</p>
    </div>
</body>
</html>
