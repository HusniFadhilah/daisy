<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') - @yield('title') | DAISY</title>

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/favicon-16x16.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/template.css') }}">

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f9fafb;
            display: flex;
            justify-content: center;
            padding: 30px;
        }

        .error-container {
            max-width: 480px;
            width: 100%;
            text-align: center;
            margin-top: 20px;
        }

        .error-logo img {
            max-width: 130px;
            margin-bottom: 10px;
        }

        .error-code {
            font-size: 4.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.3rem;
        }

        .error-illustration img {
            max-width: 260px;
            width: 80%;
            height: auto;
            margin: 8px 0 18px 0;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .error-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .error-message {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 1.2rem;
        }

        /* Wrapper tombol */
        .error-actions {
            display: flex;
            gap: .75rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: .5rem;
        }

        .error-meta {
            margin-top: 1.2rem;
            font-size: 0.8rem;
            color: #999;
        }

        .error-meta a {
            color: var(--primary);
            text-decoration: none;
        }

        .error-meta a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .error-code {
                font-size: 3.5rem;
            }

            .error-illustration img {
                max-width: 200px;
            }
        }

    </style>

    @stack('styles')
</head>
<body>
    <div class="error-container">

        {{-- Logo --}}
        <div class="error-logo">
            <a href="{{ url('/') }}">
                <img src="{{ asset('assets/images/logo.png') }}" alt="DAISY Logo">
            </a>
        </div>

        {{-- Code --}}
        <div class="error-code">@yield('code')</div>

        {{-- Illustration --}}
        <div class="error-illustration">
            <img src="@yield('illustration')" alt="Error Illustration">
        </div>

        {{-- Title --}}
        <h1 class="error-title">@yield('title')</h1>

        {{-- Message --}}
        <p class="error-message">@yield('message')</p>

        {{-- Actions (tombol) --}}
        <div class="error-actions">
            @yield('actions')
        </div>

        <div class="error-meta">
            @yield('meta', 'Jika masalah berlanjut, hubungi')
            <a href="mailto:sekretariat@lamdepilar.or.id">sekretariat@lamdepilar.or.id</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
