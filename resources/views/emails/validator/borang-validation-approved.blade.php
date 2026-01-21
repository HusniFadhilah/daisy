<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LED Disetujui</title>
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
            background: linear-gradient(135deg, #198754, #146c43);
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

        .success-badge {
            background: #d1e7dd;
            border-left: 4px solid #198754;
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
            color: #198754;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 20px 0;
        }

        .stat-card {
            background: #e7f3e9;
            padding: 15px;
            text-align: center;
            border-radius: 4px;
            border: 1px solid #198754;
        }

        .stat-card h3 {
            margin: 0;
            color: #198754;
            font-size: 24px;
        }

        .stat-card p {
            margin: 5px 0 0 0;
            font-size: 12px;
            color: #146c43;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #198754;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }

        .btn:hover {
            background: #146c43;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #6c757d;
            font-size: 12px;
        }

        .catatan-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

    </style>
</head>
<body>
    <div class="header">
        <h1>✅ LED Disetujui</h1>
        <p>Lembar Evaluasi Diri + Suplemen dan LKPS</p>
    </div>

    <div class="content">
        <div class="success-badge">
            <strong>🎉 Selamat!</strong><br>
            LED Anda telah divalidasi dan disetujui oleh validator. Permohonan akreditasi dapat dilanjutkan ke tahap berikutnya.
        </div>

        <div class="info-box">
            <p><strong>Nomor Permohonan:</strong> {{ $pengajuan->nomor_pengajuan }}</p>
            <p><strong>Program Studi:</strong> {{ $studyProgram->name }}</p>
            <p><strong>Jenjang:</strong> {{ $studyProgram->degreeLevel->name }}</p>
            <p><strong>Tahun Akreditasi:</strong> {{ $pengajuan->tahun_akreditasi }}</p>
        </div>

        @if($validator)
        <div class="info-box">
            <p><strong>Validator:</strong> {{ $validator->name }}</p>
            <p><strong>Email:</strong> {{ $validator->email }}</p>
        </div>
        @endif

        <h3>📊 Hasil Review:</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>{{ $validation->reviewed_led }}/{{ $validation->total_elemen_led }}</h3>
                <p>LED</p>
            </div>
            <div class="stat-card">
                <h3>{{ $validation->reviewed_suplemen }}/{{ $validation->total_elemen_suplemen }}</h3>
                <p>Suplemen</p>
            </div>
            <div class="stat-card">
                <h3>{{ $validation->reviewed_lkps }}/{{ $validation->total_indikator_lkps }}</h3>
                <p>LKPS</p>
            </div>
        </div>

        @if($validation->catatan_validator)
        <div class="catatan-box">
            <strong>💬 Catatan Validator:</strong>
            <p style="margin-top: 10px;">{{ $validation->catatan_validator }}</p>
        </div>
        @endif

        @if($validation->catatan_led || $validation->catatan_suplemen || $validation->catatan_lkps)
        <h3>📝 Catatan Detail:</h3>

        @if($validation->catatan_led)
        <div class="info-box">
            <strong>LED:</strong>
            <p>{{ $validation->catatan_led }}</p>
        </div>
        @endif

        @if($validation->catatan_suplemen)
        <div class="info-box">
            <strong>Suplemen:</strong>
            <p>{{ $validation->catatan_suplemen }}</p>
        </div>
        @endif

        @if($validation->catatan_lkps)
        <div class="info-box">
            <strong>LKPS:</strong>
            <p>{{ $validation->catatan_lkps }}</p>
        </div>
        @endif
        @endif

        <h3>🎯 Langkah Selanjutnya:</h3>
        <ol>
            <li>Validator akan melapor hasil validasi ke Dewan Eksekutif (DE)</li>
            <li>Pastikan pembayaran sudah dilakukan</li>
            <li>Tunggu approval untuk lanjut ke tahap Asesmen Kecukupan (AK)</li>
        </ol>

        <center>
            <a href="{{ url('/permohonan-akreditasi/' . $pengajuan->id) }}" class="btn">
                📋 Lihat Detail Permohonan Akreditasi
            </a>
        </center>

        <div style="margin-top: 20px; padding: 15px; background: #d1ecf1; border-left: 4px solid #0dcaf0; border-radius: 4px;">
            <p style="margin: 0; font-size: 13px; color: #055160;">
                <strong>ℹ️ Informasi:</strong> LED Anda telah memenuhi standar validasi.
                Proses akreditasi akan dilanjutkan setelah pembayaran diverifikasi dan approval dari Desk Evaluator.
            </p>
        </div>
    </div>

    <div class="footer">
        <p>Email ini dikirim secara otomatis oleh sistem LAMDEPILAR</p>
        <p>&copy; {{ date('Y') }} Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)</p>
    </div>
</body>
</html>
