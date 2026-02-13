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
            background: {
                    {
                    $review->hasil_review==='siap'? '#28a745': '#dc3545'
                }
            }

            ;
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

        .result-badge {
            display: inline-block;
            padding: 15px 30px;

            background: {
                    {
                    $review->hasil_review==='siap'? '#28a745': '#dc3545'
                }
            }

            ;
            color: white;
            border-radius: 5px;
            font-weight: bold;
            font-size: 18px;
            margin: 20px 0;
        }

        .info-box {
            background: white;
            padding: 20px;
            margin: 20px 0;

            border-left: 4px solid {
                    {
                    $review->hasil_review==='siap'? '#28a745': '#dc3545'
                }
            }

            ;
        }

        .checklist {
            background: white;
            padding: 15px;
            margin: 15px 0;
        }

        .checklist li {
            margin: 8px 0;
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
            <h2>📋 Hasil Review Kesiapan Borang</h2>
        </div>

        <div class="content">
            <p>Yth. Tim {{ $pengajuan->studyProgram->name }},</p>

            <p>Review kesiapan borang akreditasi Anda telah selesai dilakukan.</p>

            <center>
                <div class="result-badge">
                    @if($review->hasil_review === 'siap')
                    ✅ BORANG SIAP LANJUT KE TAHAP AK
                    @else
                    ⚠️ BORANG BELUM SIAP - PERLU PERBAIKAN
                    @endif
                </div>
            </center>

            <div class="info-box">
                <p><strong>Nomor Permohonan:</strong> {{ $pengajuan->nomor_pengajuan }}</p>
                <p><strong>Program Studi:</strong> {{ $pengajuan->studyProgram->name }}</p>
                <p><strong>Versi Review:</strong> {{ $review->versi_review }}</p>
                <p><strong>Tanggal Review:</strong> {{ $review->tanggal_review->locale('id')->translatedFormat('d F Y H:i') }}</p>
                <p><strong>Reviewer:</strong> {{ $review->reviewer->name }}</p>
            </div>

            <div style="background: white; padding: 20px; margin: 20px 0;">
                <p><strong>Catatan Validasi:</strong></p>
                <p style="white-space: pre-line;">{{ $review->catatan_review }}</p>
            </div>

            @if($review->checklist_kesiapan && count($review->checklist_kesiapan) > 0)
            <div class="checklist">
                <p><strong>Checklist Kesiapan:</strong></p>
                <ul>
                    @foreach($review->checklist_kesiapan as $item)
                    <li>✓ {{ $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($review->hasil_review === 'siap')
            <div style="background: #d1ecf1; padding: 15px; border-left: 4px solid #0dcaf0; margin: 20px 0;">
                <p><strong>Langkah Selanjutnya:</strong></p>
                <ol>
                    <li>Lakukan pembayaran akreditasi sesuai invoice yang telah dikirimkan</li>
                    <li>Upload bukti pembayaran</li>
                    <li>Setelah pembayaran diverifikasi, upload borang final</li>
                    <li>Permohonan akreditasi akan dilanjutkan ke tahap AK/Asesmen Dokumen</li>
                </ol>
            </div>
            @else
            <div style="background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0;">
                <p><strong>Langkah Selanjutnya:</strong></p>
                <ol>
                    <li>Perbaiki borang sesuai catatan validasi</li>
                    <li>Upload draft LED yang telah diperbaiki</li>
                    <li>Menunggu Validasi kembali dari LAMDEPILAR</li>
                </ol>
            </div>
            @endif

            <center>
                <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="button">
                    Lihat Detail Pengajuan
                </a>
            </center>

            <p style="margin-top: 30px;">Hormat kami,<br><strong>{{ $review->reviewer->name }}</strong><br>Sekretariat LAMDEPILAR</p>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} Daisy - DEPILAR Accreditation Information System</p>
        </div>
    </div>
</body>
</html>
