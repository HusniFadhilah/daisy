<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status Permohonan Akreditasi</title>
</head>

<body style="margin:0;padding:0;background:#f1f3f5;font-family:Arial,Helvetica,sans-serif;color:#333;">

    <!-- Preheader (hidden) -->
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
        Status permohonan akreditasi {{ $pengajuan->nomor_pengajuan }} telah diperbarui.
    </div>

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f3f5;padding:30px 0;">
        <tr>
            <td align="center">

                <!-- CONTAINER -->
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                    <!-- HEADER (PAKEM) -->
                    <tr>
                        <td align="center" style="background:#932136;padding:25px;">
                            <img src="https://daisy.lamdepilar.or.id/assets/images/logo.png" alt="Logo Daisy" style="max-width:90px;height:auto;display:block;margin:0 auto 10px auto;background:#fff;padding:8px;border-radius:6px;">
                            <h2 style="margin:0;color:#ffffff;font-size:20px;line-height:1.3;">
                                Update Status Permohonan Akreditasi
                            </h2>
                        </td>
                    </tr>

                    <!-- CONTENT -->
                    <tr>
                        <td style="padding:30px;">
                            <p style="margin:0 0 12px 0;">Kepada Yth,</p>

                            <p style="font-size:16px;margin:0 0 10px 0;">
                                <strong>{{ $pengajuan->studyProgram->name }}</strong>
                            </p>

                            <!-- INFO BOX (PAKEM) -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #932136;margin:20px 0;">
                                <tr>
                                    <td style="padding:15px;">
                                        <p style="margin:0;font-size:14px;line-height:1.5;">
                                            Status permohonan akreditasi Anda telah diperbarui.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- DETAIL PERMOHONAN (PAKEM BOX) -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #932136;margin:20px 0;">
                                <tr>
                                    <td style="padding:15px;">
                                        <p style="margin:0 0 10px 0;font-size:14px;line-height:1.5;">
                                            <strong>Nomor Permohonan:</strong> {{ $pengajuan->nomor_pengajuan }}
                                        </p>
                                        <p style="margin:0 0 10px 0;font-size:14px;line-height:1.5;">
                                            <strong>Program Studi:</strong> {{ $pengajuan->studyProgram->name }}
                                        </p>
                                        <p style="margin:0;font-size:14px;line-height:1.5;">
                                            <strong>Status Baru:</strong> {{ str_replace('_', ' ', strtoupper($statusBaru)) }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- PESAN (PAKEM BOX) -->
                            @if($pesan)
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #932136;margin:20px 0;">
                                <tr>
                                    <td style="padding:15px;">
                                        <p style="margin:0;font-size:14px;line-height:1.5;">
                                            {{ $pesan }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <p style="margin:0 0 10px 0;">Untuk melihat detail pengajuan, silakan:</p>
                            <ol style="padding-left:18px;margin:0 0 20px 0;line-height:1.6;">
                                <li>Login ke sistem</li>
                                <li>Buka menu pengajuan akreditasi</li>
                                <li>Periksa detail status dan instruksi lanjutan</li>
                            </ol>

                            <!-- BUTTON (PAKEM) -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('pengajuan.show', $pengajuan->id) }}" style="background:#932136;color:#ffffff;text-decoration:none;padding:12px 32px;border-radius:6px;font-size:14px;display:inline-block;">
                                            Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 16px 0;">
                                Jika ada pertanyaan, silakan menghubungi<br>
                                <strong>Sekretariat LAMDEPILAR</strong>
                            </p>

                            <p style="margin:0;">
                                Hormat kami,<br>
                                <strong>Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- FOOTER (PAKEM) -->
                    <tr>
                        <td align="center" style="background:#f8f9fa;padding:20px;font-size:12px;color:#6c757d;">
                            <p style="margin:0;">
                                Email ini dikirim secara otomatis. Mohon tidak membalas email ini.
                            </p>
                            <p style="margin:5px 0 0 0;">
                                &copy; {{ date('Y') }} Daisy - DEPILAR Accreditation Information System
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
