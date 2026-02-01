<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pengingat Masa Akreditasi</title>
</head>
<body style="margin:0;padding:0;background:#f1f3f5;font-family:Arial,Helvetica,sans-serif;color:#333;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f3f5;padding:30px 0;">
        <tr>
            <td align="center">

                <!-- CONTAINER -->
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                    <!-- HEADER -->
                    <tr>
                        <td align="center" style="background:#932136;padding:25px;">
                            <img src="https://daisy.sp3stab.id/assets/images/logo.png" alt="Logo" style="max-width:90px;height:auto;display:block;margin:0 auto 10px auto;background:#fff;padding:8px;border-radius:6px;">
                            <h2 style="margin:0;color:#ffffff;font-size:20px;">
                                Pengingat Masa Akreditasi
                            </h2>
                        </td>
                    </tr>

                    <!-- CONTENT -->
                    <tr>
                        <td style="padding:30px;">
                            <p style="margin-top:0;">Kepada Yth,</p>

                            <p style="font-size:16px;">
                                <strong>{{ $studyProgram->name }}</strong>
                            </p>

                            <!-- INFO BOX -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #0d6efd;margin:20px 0;">
                                <tr>
                                    <td style="padding:15px;">
                                        <p style="margin:0;font-size:14px;">
                                            {{ $pesanPengingat }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p>Untuk melanjutkan proses akreditasi, silakan:</p>
                            <ol style="padding-left:18px;">
                                <li>Login ke sistem</li>
                                <li>Submit permohonan akreditasi</li>
                                <li>Ikuti tahapan selanjutnya sesuai panduan</li>
                            </ol>

                            <!-- BUTTON -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('pengajuan') }}" style="
                                               background:#932136;
                                               color:#ffffff;
                                               text-decoration:none;
                                               padding:12px 32px;
                                               border-radius:6px;
                                               font-size:14px;
                                               display:inline-block;
                                           ">
                                            Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin-bottom:0;">
                                Jika ada pertanyaan, silakan menghubungi<br>
                                <strong>Sekretariat LAMDEPILAR</strong>
                            </p>

                            <p style="margin-top:20px;">
                                Hormat kami,<br>
                                <strong>Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td align="center" style="background:#f8f9fa;padding:20px;font-size:12px;color:#6c757d;">
                            <p style="margin:0;">
                                Email ini dikirim secara otomatis. Mohon tidak membalas email ini.
                            </p>
                            <p style="margin:5px 0 0 0;">
                                &copy; {{ date('Y') }} Sistem Akreditasi. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
                <!-- END CONTAINER -->

            </td>
        </tr>
    </table>

</body>
</html>
