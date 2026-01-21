<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LED Perlu Revisi</title>
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
            background: linear-gradient(135deg, #932136, #870820);
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
            background: #fff3cd;
            border-left: 4px solid #ffc107;
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
            color: #932136;
        }

        .revision-list {
            background: #ffffff;
            border: 1px solid #dc3545;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .revision-list h3 {
            color: #dc3545;
            margin-top: 0;
            font-size: 16px;
        }

        .revision-list ol {
            margin: 10px 0;
            padding-left: 20px;
        }

        .revision-list li {
            margin: 8px 0;
            color: #495057;
        }

        .catatan-box {
            background: #e7f3ff;
            border: 1px solid #0d6efd;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .catatan-box strong {
            color: #0d6efd;
            display: block;
            margin-bottom: 8px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #932136;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }

        .btn:hover {
            background: #7a1b2d;
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
        <h1>🔄 LED Perlu Revisi</h1>
        <p>Lembar Evaluasi Diri + Suplemen dan LKPS</p>
    </div>

    <div class="content">
        <div class="alert">
            <strong>⚠️ Perhatian!</strong><br>
            Validator telah mereview LED yang Anda submit dan meminta beberapa revisi sebelum dapat dilanjutkan ke tahap selanjutnya.
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

        @if($catatanValidator)
        <div class="catatan-box">
            <strong>💬 Catatan Validator:</strong>
            <p>{{ $catatanValidator }}</p>
        </div>
        @endif

        @if($revisionPoints && count($revisionPoints) > 0)
        <div class="revision-list">
            <h3>📋 Poin Revisi yang Harus Diperbaiki:</h3>
            <ol>
                @foreach($revisionPoints as $point)
                <li>{{ $point }}</li>
                @endforeach
            </ol>
        </div>
        @endif

        <h3>📝 Langkah Selanjutnya:</h3>
        <ol>
            <li>Perbaiki LED sesuai dengan poin revisi di atas</li>
            <li>Perhatikan catatan dari validator dengan seksama</li>
            <li>Update LED melalui sistem borang online</li>
            <li>Submit kembali setelah semua revisi selesai</li>
        </ol>

        <center>
            <a href="{{ url('/permohonan-akreditasi/' . $pengajuan->id . '/borang-online') }}" class="btn">
                🔧 Perbaiki LED Sekarang
            </a>
        </center>

        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
            <p style="margin: 0; font-size: 13px; color: #6c757d;">
                <strong>💡 Tips:</strong> Pastikan semua poin revisi sudah diperbaiki sebelum submit kembali.
                Jika ada yang kurang jelas, Anda dapat menghubungi validator atau Desk Evaluator.
            </p>
        </div>
    </div>

    <div class="footer">
        <p>Email ini dikirim secara otomatis oleh sistem LAMDEPILAR</p>
        <p>&copy; {{ date('Y') }} Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)</p>
    </div>
</body>
</html>
