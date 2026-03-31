<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Notifikasi' }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="py-4" style="margin:0;background:#f1f3f5;font-family:Montserrat,Helvetica,sans-serif;color:#333;">

    {{-- Preheader (hidden) --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
        {{ $preheader ?? '' }}
    </div>

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f3f5;padding:30px 0;">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                    {{-- HEADER --}}
                    <tr>
                        <td align="center" style="background:#932136;padding:25px;">
                            <img src="https://daisy.lamdepilar.or.id/assets/images/logo.png" alt="Logo Daisy" style="max-width:90px;height:auto;display:block;margin:0 auto 10px auto;background:#fff;padding:8px;border-radius:6px;">
                            <h2 style="margin:0;color:#ffffff;font-size:20px;line-height:1.3;font-weight:bold">
                                {{ $headerTitle ?? 'Notifikasi' }}
                            </h2>
                        </td>
                    </tr>

                    {{-- CONTENT --}}
                    <tr>
                        <td style="padding:30px;">
                            @yield('content')

                            {{-- FOOT NOTE --}}
                            <small style="margin:20px 0 0 0;">
                                Jika ada pertanyaan, silakan menghubungi<br>
                                <strong>Sekretariat LAMDEPILAR</strong>
                            </small>

                            <br><br><small style="margin:16px 0 0 0;">
                                Hormat kami,<br>
                                <strong>Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)</strong>
                            </small>
                        </td>
                    </tr>

                    {{-- FOOTER --}}
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

            </td>
        </tr>
    </table>

</body>
</html>
