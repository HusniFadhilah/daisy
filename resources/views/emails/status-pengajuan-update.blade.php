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
            background: #28a745;
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

        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            background: #0d6efd;
            color: white;
            border-radius: 20px;
            font-weight: bold;
            margin: 10px 0;
        }

        .info-box {
            background: white;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
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
            <h2>📢 Update Status Permohonan</h2>
        </div>

        <div class="content">
            <p>Yth. Tim {{ $pengajuan->studyProgram->name }},</p>

            <p>Status permohonan akreditasi Anda telah diupdate:</p>

            <div class="info-box">
                <p><strong>Nomor Permohonan:</strong> {{ $pengajuan->nomor_pengajuan }}</p>
                <p><strong>Program Studi:</strong> {{ $pengajuan->studyProgram->name }}</p>
                <p><strong>Status Baru:</strong>
                    <span class="status-badge">{{ str_replace('_', ' ', strtoupper($statusBaru)) }}</span>
                </p>
            </div>

            @if($pesan)
            <div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
                <p><strong>Pesan dari Dewan Eksekutif (DE) LAMDEPILAR:</strong></p>
                <p>{{ $pesan }}</p>
            </div>
            @endif

            <center>
                <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="button">
                    Lihat Detail Pengajuan
                </a>
            </center>

            <p style="margin-top: 30px;">Hormat kami,<br><strong>Dewan Eksekutif (DE) LAMDEPILAR</strong></p>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} Sistem Akreditasi. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
