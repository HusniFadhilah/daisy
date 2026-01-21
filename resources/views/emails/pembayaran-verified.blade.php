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
                    $isVerified ? '#28a745': '#dc3545'
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

        .info-box {
            background: white;
            padding: 15px;
            margin: 20px 0;

            border-left: 4px solid {
                    {
                    $isVerified ? '#28a745': '#dc3545'
                }
            }

            ;
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
            <h2>
                @if($isVerified)
                ✅ Pembayaran Diverifikasi
                @else
                ❌ Pembayaran Ditolak
                @endif
            </h2>
        </div>

        <div class="content">
            <p>Yth. Tim {{ $pengajuan->studyProgram->name }},</p>

            @if($isVerified)
            <p>Pembayaran akreditasi Anda telah <strong>DIVERIFIKASI</strong> oleh DE.</p>

            <div class="info-box">
                <p><strong>Nomor Pengajuan:</strong> {{ $pengajuan->nomor_pengajuan }}</p>
                <p><strong>Invoice:</strong> {{ $pengajuan->pembayaran->nomor_invoice }}</p>
                <p><strong>Jumlah Pembayaran:</strong> Rp {{ number_format($pengajuan->pembayaran->jumlah_pembayaran, 0, ',', '.') }}</p>
                <p><strong>Status:</strong> <span style="color: #28a745;">VERIFIED</span></p>
            </div>

            <div style="background: #d1ecf1; padding: 15px; border-left: 4px solid #0dcaf0; margin: 20px 0;">
                <p><strong>Langkah Selanjutnya:</strong></p>
                <ol>
                    <li>Login ke sistem</li>
                    <li>Buka detail Permohonan Akreditasi Anda</li>
                    <li>Upload <strong>Borang Final</strong> yang telah lengkap</li>
                    <li><strong>PENTING:</strong> Pastikan tidak ada revisi data kuantitatif/kualitatif</li>
                </ol>
            </div>

            <div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
                <p><strong>⚠️ Catatan Penting:</strong></p>
                <p>Borang final yang diupload harus sudah final dan tidak boleh ada perubahan data setelah ini. Pastikan semua data telah sesuai sebelum upload!</p>
            </div>

            @else
            <p>Pembayaran akreditasi Anda <strong>DITOLAK</strong> oleh DE.</p>

            <div class="info-box">
                <p><strong>Nomor Permohonan:</strong> {{ $pengajuan->nomor_pengajuan }}</p>
                <p><strong>Invoice:</strong> {{ $pengajuan->pembayaran->nomor_invoice }}</p>
                <p><strong>Status:</strong> <span style="color: #dc3545;">DITOLAK</span></p>
            </div>

            @if($pengajuan->pembayaran->alasan_penolakan)
            <div style="background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0;">
                <p><strong>Alasan Penolakan:</strong></p>
                <p>{{ $pengajuan->pembayaran->alasan_penolakan }}</p>
            </div>
            @endif

            <div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
                <p><strong>Langkah Selanjutnya:</strong></p>
                <ol>
                    <li>Periksa alasan penolakan di atas</li>
                    <li>Lakukan pembayaran ulang dengan benar</li>
                    <li>Upload bukti pembayaran yang valid</li>
                </ol>
            </div>
            @endif

            <center>
                <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="button">
                    Lihat Detail Pengajuan
                </a>
            </center>

            <p style="margin-top: 30px;">Hormat kami,<br><strong>Dewan Eksekutif LAMDEPILAR</strong></p>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} Sistem Akreditasi. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
