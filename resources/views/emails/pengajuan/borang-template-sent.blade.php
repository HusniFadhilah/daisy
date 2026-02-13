{{-- resources/views/emails/permohonan-akreditasi/borang-template-sent.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Templat Dokumen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .content {
            background: #f9f9f9;
            padding: 30px;
            border: 1px solid #e0e0e0;
        }

        .info-box {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
            border-radius: 4px;
        }

        .info-box h3 {
            margin-top: 0;
            color: #667eea;
        }

        .info-row {
            margin: 10px 0;
        }

        .info-label {
            font-weight: bold;
            color: #666;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }

        .button:hover {
            background: #5568d3;
        }

        .alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .steps {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .steps ol {
            margin: 10px 0;
            padding-left: 20px;
        }

        .steps li {
            margin: 8px 0;
        }

        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">📄 Templat Dokumen</h1>
        <p style="margin: 10px 0 0 0;">{{ config('app.name') }}</p>
    </div>

    <div class="content">
        <p>Halo <strong>{{ $pengajuan->pengaju->name }}</strong>,</p>

        <p>
            Desk Evaluator (DE) telah mengirimkan Templat Dokumen untuk permohonan akreditasi program studi Anda.
        </p>

        {{-- Detail Permohonan akreditasi --}}
        <div class="info-box">
            <h3>📋 Detail Permohonan Akreditasi</h3>
            <div class="info-row">
                <span class="info-label">Nomor Permohonan Akreditasi:</span>
                {{ $pengajuan->nomor_pengajuan }}
            </div>
            <div class="info-row">
                <span class="info-label">Program Studi:</span>
                {{ $pengajuan->studyProgram->name }}
            </div>
            <div class="info-row">
                <span class="info-label">Jenjang:</span>
                {{ $pengajuan->studyProgram->degreeLevel->name }}
            </div>
            <div class="info-row">
                <span class="info-label">Universitas:</span>
                {{ $pengajuan->studyProgram->university->name }}
            </div>
        </div>

        {{-- Templat Link atau File --}}
        @if($metode === 'link')
        <div class="info-box">
            <h3>🔗 Link Templat</h3>
            <p>Akses Templat Dokumen melalui link berikut:</p>
            <p style="text-align: center;">
                <a href="{{ $dokumen->template_link }}" class="button">
                    📥 Download Templat Dokumen
                </a>
            </p>
            <div class="info-row">
                <span class="info-label">Link:</span>
                <a href="{{ $dokumen->template_link }}">{{ $dokumen->template_link }}</a>
            </div>
        </div>
        @else
        <div class="info-box">
            <h3>📄 File Templat</h3>
            <p>File templat telah diupload dan dapat Anda download:</p>
            <p style="text-align: center;">
                <a href="{{ route('pengajuan.dokumen.download', $dokumen->id) }}" class="button">
                    📥 Download File Templat
                </a>
            </p>
            <div class="info-row">
                <span class="info-label">Nama File:</span>
                {{ $dokumen->original_filename }}
            </div>
            <div class="info-row">
                <span class="info-label">Ukuran:</span>
                {{ number_format($dokumen->file_size / 1024, 2) }} KB
            </div>
        </div>
        @endif

        {{-- Keterangan --}}
        @if($dokumen->keterangan)
        <div class="info-box">
            <h3>📝 Keterangan dari DE</h3>
            <p>{{ $dokumen->keterangan }}</p>
        </div>
        @endif

        {{-- Langkah Selanjutnya --}}
        <div class="steps">
            <h3>✅ Langkah Selanjutnya</h3>
            <ol>
                <li>Download Templat Dokumen</li>
                <li>Lengkapi data sesuai panduan</li>
                <li>Upload draft LED melalui sistem</li>
            </ol>
            <p style="text-align: center; margin-top: 20px;">
                <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="button">
                    🔗 Akses Pengajuan
                </a>
            </p>
        </div>

        {{-- Catatan Penting --}}
        <div class="alert">
            <strong>⚠️ Catatan Penting:</strong>
            <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                <li>Pastikan semua data diisi dengan lengkap dan benar</li>
                <li>Gunakan format yang telah ditentukan</li>
                <li>Hubungi DE jika ada pertanyaan</li>
            </ul>
        </div>

        <p>Terima kasih atas perhatian dan kerjasamanya.</p>

        <p>
            Hormat kami,<br>
            <strong>{{ config('app.name') }}</strong>
        </p>
    </div>

    <div class="footer">
        <p>
            Email ini dikirim secara otomatis oleh sistem.<br>
            Mohon tidak membalas email ini.
        </p>
        <p>
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
    </div>
</body>
</html>
