<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sertifikat Akreditasi - {{ $studyProgram->name }}</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}">
    <style>
        :root {
            --primary-color: #932136;
            --primary-soft: rgba(147, 33, 54, 0.08);
            --primary-border: rgba(147, 33, 54, 0.35);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            position: fixed;
        }

        body {
            display: block;
            font-family: 'Montserrat', sans-serif;
            color: #000;
            background: #e5e7eb;
        }

        /* Container wrapper */
        .certificate-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 5px;
        }

        /* Page 1 specific - visible on screen */
        .page-1-wrapper {
            position: fixed;
            inset: 0;
        }

        /* Page 2 specific - hidden on screen, visible on print */
        .page-2-wrapper {
            display: none;
        }

        /* Mobile: Portrait orientation, fit to height */
        .certificate-container {
            width: 100%;
            height: 100%;
            max-width: calc(100vh * 0.707);
            max-height: 100vh;
            position: relative;
        }

        /* Tablet landscape and above: Landscape orientation */
        @media (min-width: 768px) and (orientation: landscape),
        (min-width: 1024px) {
            .certificate-wrapper {
                padding: 15px;
            }

            .certificate-container {
                max-width: calc(100vh * 1.414);
                max-height: calc(100vw / 1.414);
            }
        }

        /* Desktop: larger */
        @media (min-width: 1024px) {
            .certificate-wrapper {
                padding: 20px;
            }
        }

        /* Frame sertifikat */
        .certificate-frame {
            position: absolute;
            inset: 0;
            padding: 1.2vh;
            border-radius: 0.6vh;
            background: #fff;
            border: 0.4vh solid #9aa3b2;
            display: flex;
            overflow: hidden;
        }

        @media (min-width: 768px) {
            .certificate-frame {
                padding: 1.8vmin;
                border-radius: 1vmin;
                border-width: 0.6vmin;
            }
        }

        .certificate-frame:before {
            content: "";
            position: absolute;
            inset: 0.6vh;
            border-radius: 0.4vh;
            border: 0.2vh solid #c7cbd4;
            pointer-events: none;
        }

        @media (min-width: 768px) {
            .certificate-frame:before {
                inset: 1vmin;
                border-radius: 0.8vmin;
                border-width: 0.3vmin;
            }
        }

        .certificate-inner {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.5vh 2vh;
            border-radius: 0.5vh;
            border: 0.15vh solid #932136;
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
        }

        @media (min-width: 768px) {
            .certificate-inner {
                padding: 2.2vmin 2.8vmin 1.8vmin 2.8vmin;
                border-radius: 0.8vmin;
                border-width: 0.2vmin;
            }
        }

        /* Custom scrollbar */
        .certificate-inner::-webkit-scrollbar {
            width: 3px;
        }

        .certificate-inner::-webkit-scrollbar-track {
            background: transparent;
        }

        .certificate-inner::-webkit-scrollbar-thumb {
            background: rgba(147, 33, 54, 0.3);
            border-radius: 2px;
        }

        .certificate-inner>* {
            position: relative;
            z-index: 1;
        }

        /* watermark logo */
        .watermark-logo {
            position: absolute;
            inset: 0;
            background-image: url('https://daisy.sp3stab.id/assets/images/logo-square.png');
            background-repeat: no-repeat;
            background-position: center;
            background-size: 45vh auto;
            opacity: 0.06;
            pointer-events: none;
            z-index: 0;
        }

        @media (min-width: 768px) {
            .watermark-logo {
                background-size: 45vmin auto;
            }
        }

        /* ===== Header 3 kolom ===== */
        .header {
            display: table;
            width: 100%;
            margin-top: 0.4vh;
            padding-bottom: 1vh;
            border-bottom: 0.15vh solid #932136;
        }

        @media (min-width: 768px) {
            .header {
                margin-top: 0.6vmin;
                padding-bottom: 1.4vmin;
                border-bottom-width: 0.2vmin;
            }
        }

        .header-col {
            display: table-cell;
            vertical-align: middle;
        }

        .header-left {
            width: 15%;
        }

        .header-mid {
            width: 67%;
            text-align: center;
        }

        .header-right {
            width: 18%;
            text-align: right;
            font-size: 0.9vh;
            color: #333;
        }

        @media (min-width: 768px) {
            .header-right {
                font-size: 1.4vmin;
            }
        }

        .logo-wrap {
            width: 13vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        @media (min-width: 768px) {
            .logo-wrap {
                width: 17vmin;
            }
        }

        .logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .inst-name {
            font-size: 1.2vh;
            font-weight: bold;
            color: #932136;
            line-height: 1.25;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        @media (min-width: 768px) {
            .inst-name {
                font-size: 1.8vmin;
                letter-spacing: 0.6px;
            }
        }

        .inst-sub {
            margin-top: 0.3vh;
            font-size: 1vh;
            color: #111;
            line-height: 1.3;
        }

        @media (min-width: 768px) {
            .inst-sub {
                margin-top: 0.4vmin;
                font-size: 1.5vmin;
            }
        }

        /* ===== Judul besar ===== */
        .title {
            text-align: center;
            margin: 1.5vh 0 0.8vh 0;
        }

        @media (min-width: 768px) {
            .title {
                margin: 2.2vmin 0 1vmin 0;
            }
        }

        .title h1 {
            font-size: 2.8vh;
            font-weight: 800;
            color: #932136;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        @media (min-width: 768px) {
            .title h1 {
                font-size: 4.2vmin;
                letter-spacing: 2px;
            }
        }

        .title .no {
            margin-top: 0.6vh;
            font-size: 1vh;
            color: #333;
        }

        @media (min-width: 768px) {
            .title .no {
                margin-top: 0.8vmin;
                font-size: 1.55vmin;
            }
        }

        /* ===== Isi ===== */
        .body {
            margin-top: 1.2vh;
            text-align: center;
        }

        @media (min-width: 768px) {
            .body {
                margin-top: 1.6vmin;
            }
        }

        .intro {
            font-size: 1.2vh;
            line-height: 1.6;
            margin: 0.6vh 0 1vh 0;
        }

        @media (min-width: 768px) {
            .intro {
                font-size: 1.8vmin;
                line-height: 1.75;
                margin: 0.8vmin 0 1.4vmin 0;
            }
        }

        .details {
            width: 92%;
            margin: 0 auto;
            border: 0.15vh solid #932136;
            border-radius: 0.8vh;
            padding: 1.4vh 1.4vh 0.8vh 1.4vh;
            background: #fbfcff;
            text-align: left;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        @media (min-width: 768px) {
            .details {
                border-width: 0.2vmin;
                border-radius: 1vmin;
                padding: 1.8vmin 1.8vmin 1vmin 1.8vmin;
            }
        }

        .row {
            display: table;
            width: 100%;
            margin-bottom: 0.8vh;
            font-size: 1.2vh;
        }

        @media (min-width: 768px) {
            .row {
                margin-bottom: 1vmin;
                font-size: 1.75vmin;
            }
        }

        .lbl {
            display: table-cell;
            width: 28%;
            font-weight: bold;
            padding-right: 0.8vh;
        }

        @media (min-width: 768px) {
            .lbl {
                width: 26%;
                padding-right: 1vmin;
            }
        }

        .col {
            display: table-cell;
            width: 2%;
        }

        .val {
            display: table-cell;
            width: 70%;
        }

        @media (min-width: 768px) {
            .val {
                width: 72%;
            }
        }

        /* ===== Badge akreditasi ===== */
        .badge {
            width: 45vh;
            margin: 1.4vh auto 0 auto;
            border-radius: 0.8vh;
            border: 0.2vh solid #1f1801ff;
            /* background: linear-gradient(135deg, #ffe58a 0%, #ffd24d 45%, #fff1b8 100%); */
            padding: 1.1vh 1.1vh;
            text-align: center;
            box-shadow: 0 0.15vh 0.6vh rgba(12, 12, 12, 0.08);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        @media (min-width: 768px) {
            .badge {
                width: 70vmin;
                margin-top: 1.8vmin;
                border-radius: 1vmin;
                border-width: 0.25vmin;
                padding: 1.4vmin 1.4vmin;
            }
        }

        .badge .badge-label {
            font-size: 1vh;
            letter-spacing: 0.6px;
            font-weight: bold;
            color: #4a3a00;
            text-transform: uppercase;
            margin-bottom: 0.5vh;
        }

        @media (min-width: 768px) {
            .badge .badge-label {
                font-size: 1.5vmin;
                letter-spacing: 0.8px;
                margin-bottom: 0.6vmin;
            }
        }

        .badge .badge-rank {
            font-size: 2.5vh;
            font-weight: 900;
            color: #932136;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        @media (min-width: 768px) {
            .badge .badge-rank {
                font-size: 4vmin;
                letter-spacing: 1px;
            }
        }

        .validity {
            margin-top: 1.2vh;
            font-size: 1.15vh;
            color: #222;
            line-height: 1.65;
        }

        @media (min-width: 768px) {
            .validity {
                margin-top: 1.6vmin;
                font-size: 1.7vmin;
                line-height: 1.7;
            }
        }

        /* ===== Area bawah ===== */
        .bottom {
            margin-top: 2vh;
            display: table;
            width: 100%;
        }

        @media (min-width: 768px) {
            .bottom {
                margin-top: 2.6vmin;
            }
        }

        .bottom-left {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
            padding-left: 0.6vh;
        }

        @media (min-width: 768px) {
            .bottom-left {
                padding-left: 0.8vmin;
            }
        }

        .bottom-right {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
            text-align: center;
        }

        .seal-row {
            display: flex;
            gap: 1vh;
            align-items: flex-end;
        }

        @media (min-width: 768px) {
            .seal-row {
                gap: 1.2vmin;
            }
        }

        .seal {
            width: 7.5vh;
            height: 7.5vh;
            border-radius: 50%;
            border: 0.15vh solid #b8860b;
            background: radial-gradient(circle at 30% 30%, #fff6bf 0%, #ffd24d 40%, #e2b600 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85vh;
            font-weight: bold;
            color: #4a3a00;
            text-align: center;
            padding: 0.6vh;
            flex-shrink: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        @media (min-width: 768px) {
            .seal {
                width: 9.2vmin;
                height: 9.2vmin;
                border-width: 0.2vmin;
                font-size: 1.25vmin;
                padding: 0.8vmin;
            }
        }

        .qr {
            width: 9vh;
            height: 9vh;
            border: 0.1vh solid #c7cbd4;
            border-radius: 0.5vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #fff;
            flex-shrink: 0;
            padding: 0.3vh;
        }

        @media (min-width: 768px) {
            .qr {
                width: 10.8vmin;
                height: 10.8vmin;
                border-width: 0.1vmin;
                border-radius: 0.6vmin;
                padding: 0.4vmin;
            }
        }

        .qr img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .sig-loc {
            font-size: 1.05vh;
            margin-bottom: 0.5vh;
        }

        @media (min-width: 768px) {
            .sig-loc {
                font-size: 1.55vmin;
                margin-bottom: 0.6vmin;
            }
        }

        .sig-role {
            font-size: 1.1vh;
            font-weight: bold;
            margin-bottom: 5vh;
        }

        @media (min-width: 768px) {
            .sig-role {
                font-size: 1.65vmin;
                margin-bottom: 6.2vmin;
            }
        }

        .sig-name {
            display: inline-block;
            font-size: 1.2vh;
            font-weight: bold;
            border-bottom: 0.15vh solid #000;
            padding: 0 0.6vh 0.5vh 0.6vh;
            line-height: 1.2;
        }

        @media (min-width: 768px) {
            .sig-name {
                font-size: 1.75vmin;
                border-bottom-width: 0.18vmin;
                padding: 0 0.8vmin 0.6vmin 0.8vmin;
            }
        }

        .footer {
            margin-top: 1.2vh;
            padding-top: 0.8vh;
            border-top: 0.1vh solid #932136;
            text-align: center;
            font-size: 0.9vh;
            color: #444;
            line-height: 1.5;
        }

        @media (min-width: 768px) {
            .footer {
                margin-top: 1.6vmin;
                padding-top: 1vmin;
                border-top-width: 0.1vmin;
                font-size: 1.3vmin;
            }
        }

        /* ===== Page 2 Styles ===== */
        .page-break {
            display: none;
            page-break-after: always;
            page-break-inside: avoid;
            break-after: page;
            height: 0;
            margin: 0;
            padding: 0;
        }

        .page-2-header {
            text-align: center;
            padding: 0.7vh 0 0.5vh 0 !important;
            margin-bottom: 0.7vh !important;
            border-bottom: 0.2vh solid #932136;
        }

        @media (min-width: 768px) {
            .page-2-header {
                padding: 2vmin 0 1.3vmin 0;
                border-bottom-width: 0.25vmin;
                margin-bottom: 2vmin;
            }
        }

        .page-2-title {
            font-size: 2.5vh !important;
            font-weight: 700;
            color: #932136;
            text-transform: uppercase;
            letter-spacing: 0.5px !important;
            margin-bottom: 1vmin;
        }

        @media (min-width: 768px) {
            .page-2-title {
                font-size: 3.2vmin;
                letter-spacing: 1.5px;
                margin-bottom: 0.7vmin;
            }
        }

        .page-2-subtitle {
            font-size: 1.1vh;
            color: #333;
            margin-top: 0.3vh;
        }

        @media (min-width: 768px) {
            .page-2-subtitle {
                font-size: 1.6vmin;
                margin-top: 0.5vmin;
            }
        }

        .page-2-info {
            background: #f8f9fa;
            border: 0.15vh solid #dee2e6;
            border-radius: 0.5vh;
            padding: 1.2vh;
            margin-bottom: 1.5vh;
            font-size: 1vh;
            color: #333;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        @media (min-width: 768px) {
            .page-2-info {
                border-width: 0.18vmin;
                border-radius: 0.7vmin;
                padding: 1.6vmin;
                margin-bottom: 2vmin;
                font-size: 1.45vmin;
            }
        }

        .page-2-info strong {
            color: #000;
        }

        .elemen-table-wrapper {
            margin-top: 0.5vh !important;
            overflow-x: auto;
        }

        /* wrapper 2 kolom */
        .elemen-two-cols {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.8vh;
            align-items: start;
        }

        /* tabel dibuat lebih rapat supaya muat 1 halaman */
        .elemen-two-cols .elemen-table {
            font-size: 0.88vh;
            /* kecilkan sedikit */
            table-layout: fixed;
            /* penting biar kolom stabil */
        }

        .elemen-two-cols .elemen-table th,
        .elemen-two-cols .elemen-table td {
            padding: 0.35vh 0.4vh;
            /* rapatkan padding */
        }

        .elemen-two-cols .elemen-table th:nth-child(1),
        .elemen-two-cols .elemen-table td:nth-child(1) {
            width: 12%;
        }

        .elemen-two-cols .elemen-table th:nth-child(2),
        .elemen-two-cols .elemen-table td:nth-child(2) {
            width: 53%;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }

        .elemen-two-cols .elemen-table th:nth-child(3),
        .elemen-two-cols .elemen-table td:nth-child(3) {
            width: 35%;
        }

        /* badge kategori diperkecil biar muat */
        .elemen-two-cols .kategori-badge {
            min-width: 0 !important;
            width: 100%;
            font-size: 0.72vh;
            padding: 0.3vh 0.5vh;
        }

        @media (min-width: 768px) {
            .elemen-table-wrapper {
                margin-top: 1.5vmin;
            }
        }

        .elemen-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95vh;
            background: #fff;
        }

        @media (min-width: 768px) {
            .elemen-table {
                font-size: 1.35vmin;
            }
        }

        .elemen-table thead {
            background: #f8f9fa;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        .elemen-table th,
        .elemen-table td {
            padding: 0.7vh 0.6vh;
            border: 0.1vh solid #dee2e6;
            text-align: left;
            vertical-align: middle;
        }

        @media (min-width: 768px) {

            .elemen-table th,
            .elemen-table td {
                padding: 1vmin 0.8vmin;
                border-width: 0.12vmin;
            }
        }

        .elemen-table th {
            font-weight: 600;
            color: #333;
        }

        .elemen-table thead th {
            text-align: center;
        }

        .elemen-table .kriteria-cell {
            text-align: center;
            font-weight: 700;
            padding: 0.4vh 0.8vh !important;
            vertical-align: middle;
        }

        .elemen-table .kode-elemen {
            color: #932136;
            font-weight: 700;
            font-family: monospace;
            margin-right: 0.5vh;
            font-size: 1.5vh;
        }

        .elemen-table .nama-elemen {
            font-size: 1.2vh;
        }

        @media (min-width: 768px) {
            .elemen-table .kode-elemen {
                margin-right: 0.7vmin;
            }
        }

        .elemen-table .kategori-badge {
            display: inline-block;
            padding: 0.45vh 0.9vh;
            border-radius: 0.3vh;
            font-weight: 600;
            font-size: 0.8vh;
            text-align: center;
            min-width: 14vh;
            color: #222;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        @media (min-width: 768px) {
            .elemen-table .kategori-badge {
                padding: 0.65vmin 1.2vmin;
                border-radius: 0.4vmin;
                font-size: 1.2vmin;
                min-width: 18vmin;
            }
        }

        .kriteria-badge {
            background: #6c757d;
            color: #fff;
            padding: 0.4vh 0.8vh;
            border-radius: 0.3vh;
            display: block;
            margin: 0 auto 0.3vh auto;
            width: fit-content;
            font-size: 1.05vh;
            font-weight: 600;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        .nama-kriteria {
            font-size: 0.7vh;
            line-height: 1.15;
            text-align: center;
            font-weight: 600;
            color: #333;
            margin-top: 1vh;
            word-wrap: break-word;
            hyphens: auto;
        }

        @media (min-width: 768px) {
            .kriteria-badge {
                padding: 0.6vmin 1.1vmin;
                border-radius: 0.4vmin;
                font-size: 1.45vmin;
            }
        }

        /* Navigation buttons for screen view */
        .page-nav {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }

        .page-nav button {
            padding: 12px 24px;
            background: #932136;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(147, 33, 54, 0.3);
            transition: all 0.3s ease;
        }

        .page-nav button:hover {
            background: #7a1b2d;
            box-shadow: 0 4px 12px rgba(147, 33, 54, 0.4);
        }

        .page-nav button:disabled {
            background: #ccc;
            cursor: not-allowed;
            box-shadow: none;
        }



        .page-2-wrapper .footer {
            margin-top: 0.8vh !important;
            padding-top: 0.6vh !important;
            font-size: 1vh !important;
            line-height: 1.3;
        }

        @media print {

            html,
            body {
                overflow: visible;
                position: static;
                background: #fff;
                margin: 0;
                padding: 0;
                height: auto;
            }

            /* Force colors to print */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            /* Show both pages when printing */
            .page-1-wrapper,
            .page-2-wrapper {
                display: block !important;
                position: static !important;
                page-break-inside: avoid;
            }

            .certificate-wrapper {
                padding: 0;
                height: auto;
                page-break-inside: avoid;
            }

            .certificate-container {
                max-width: 100%;
                max-height: none;
                height: auto;
                page-break-inside: avoid;
            }

            .certificate-frame {
                position: static;
                page-break-inside: avoid;
            }

            .certificate-inner {
                overflow: visible;
                page-break-inside: avoid;
            }

            /* Force page break between pages */
            .page-break {
                display: block !important;
                page-break-after: always !important;
                break-after: page !important;
                height: 0 !important;
                margin: 0 !important;
            }

            /* Ensure second page starts on new page */
            .page-2-wrapper {
                page-break-before: always !important;
                break-before: page !important;
            }

            /* Hide navigation on print */
            .page-nav {
                display: none !important;
            }
        }

        @font-face {
            font-family: 'Montserrat';
            src: url('{{ public_path('assets/fonts/Montserrat/static/Montserrat-Regular.ttf') }}') format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'Montserrat';
            src: url('{{ public_path('assets/fonts/Montserrat/static/Montserrat-Bold.ttf') }}') format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        @font-face {
            font-family: 'Montserrat';
            src: url('{{ public_path('assets/fonts/Montserrat/static/Montserrat-SemiBold.ttf') }}') format('truetype');
            font-weight: 600;
            font-style: normal;
        }

        .title h1,
        .inst-name {
            font-weight: 700;
        }

        .details,
        .intro,
        .validity {
            font-weight: 400;
        }

    </style>
</head>
<body>
    {{-- ===== HALAMAN 1: SERTIFIKAT AKREDITASI ===== --}}
    <div class="certificate-wrapper page-1-wrapper">
        <div class="certificate-container">
            <div class="certificate-frame">
                <div class="certificate-inner">
                    <div class="watermark-logo"></div>

                    {{-- ===== HEADER ===== --}}
                    <div class="header">
                        <div class="header-col header-left">
                            <div class="logo-wrap">
                                <img src="https://daisy.sp3stab.id/assets/images/logo.png" alt="Logo LAMDEPILAR">
                            </div>
                        </div>

                        <div class="header-col header-mid">
                            <div class="inst-name">
                                Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur
                                (LAMDEPILAR)
                            </div>
                            <div class="inst-sub">
                                Sertifikat Akreditasi Program Studi
                            </div>
                        </div>

                        <div class="header-col header-right">
                            <div><strong>Nomor</strong></div>
                            <div>{{ $nomorSertifikat }}</div>
                            <div style="margin-top:6px;"><strong>Tanggal</strong></div>
                            <div>{{ \App\Libraries\Date::tglIndo($tanggalPenetapan) }}</div>
                        </div>
                    </div>

                    {{-- ===== TITLE ===== --}}
                    <div class="title">
                        <h1>SERTIFIKAT AKREDITASI</h1>
                        <div class="no">Nomor: <strong>{{ $nomorSertifikat }}</strong></div>
                    </div>

                    {{-- ===== BODY ===== --}}
                    <div class="body">
                        <div class="intro">
                            Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)
                            dengan ini menyatakan bahwa:
                        </div>

                        <div class="details">
                            <div class="row">
                                <div class="lbl">Program Studi</div>
                                <div class="col">:</div>
                                <div class="val"><strong>{{ $studyProgram->name }}</strong></div>
                            </div>
                            <div class="row">
                                <div class="lbl">Jenjang</div>
                                <div class="col">:</div>
                                <div class="val"><strong>{{ $studyProgram->degreeLevel->name ?? '-' }}</strong></div>
                            </div>
                            <div class="row">
                                <div class="lbl">Perguruan Tinggi</div>
                                <div class="col">:</div>
                                <div class="val"><strong>{{ $university->name }}</strong></div>
                            </div>
                        </div>
                        @php
                        $peringkatFinal = $hasil->getPeringkatFromSkor((float)($hasil->skor_al ?? 0));
                        @endphp
                        <div class="badge" style="background-color: {{ $hasil->getPeringkatColor($peringkatFinal) }}; color:#222">
                            <div class="badge-label">Status Akreditasi</div>
                            <div class="badge-rank">{{ strtoupper($peringkatFinal) }}</div>
                        </div>

                        <div class="validity">
                            Sertifikat akreditasi ini berlaku selama
                            <strong>{{ $masaBerlaku['tahun'] }} ({{ \App\Libraries\Fungsi::terbilang($masaBerlaku['tahun']) }}) tahun</strong><br>
                            terhitung sejak tanggal
                            <strong>{{ \App\Libraries\Date::tglIndo($masaBerlaku['tanggal_mulai']) }}</strong><br>
                            sampai dengan tanggal
                            <strong>{{ \App\Libraries\Date::tglIndo($masaBerlaku['tanggal_berakhir']) }}</strong>
                        </div>
                    </div>

                    {{-- ===== BOTTOM: SEAL/QR + SIGNATURE ===== --}}
                    <div class="bottom">
                        <div class="bottom-left">
                            <div class="seal-row">
                                <div class="seal">
                                    CAP /<br> STEMPEL<br> RESMI
                                </div>
                                <div class="qr">
                                    @php
                                    // Generate URL verifikasi sertifikat
                                    $verifikasiUrl = url("/verifikasi-sertifikat/{$nomorSertifikat}");
                                    // Encode URL untuk QR code menggunakan Google Charts API
                                    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($verifikasiUrl);
                                    @endphp
                                    <img src="{{ $qrCodeUrl }}" alt="QR Verifikasi Sertifikat">
                                </div>
                            </div>
                        </div>

                        <div class="bottom-right">
                            <div class="sig-loc">
                                Jakarta, {{ \App\Libraries\Date::tglIndo($tanggalPenetapan) }}
                            </div>
                            <div class="sig-role">Ketua Dewan Eksekutif</div>
                            <div class="sig-name">
                                Dr. Ar. Yulianto Purwono Prihatmaji, IPM., IAI
                            </div>
                        </div>
                    </div>

                    {{-- ===== FOOTER ===== --}}
                    <div class="footer">
                        Sertifikat ini diterbitkan secara resmi oleh LAMDEPILAR sebagai bukti pemenuhan standar akreditasi.<br>
                        (Informasi verifikasi dapat disesuaikan dengan sistem/laman verifikasi LAMDEPILAR.)
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Navigation buttons --}}
    <div class="page-nav">
        <button id="prevBtn" onclick="showPage(1)" disabled>Halaman 1</button>
        <button id="nextBtn" onclick="showPage(2)">Halaman 2</button>
    </div>

    {{-- ===== PAGE BREAK ===== --}}
    <div class="page-break"></div>

    {{-- ===== HALAMAN 2: SURAT KETERANGAN CAPAIAN AKREDITASI ===== --}}
    <div class="certificate-wrapper page-2-wrapper">
        <div class="certificate-container">
            <div class="certificate-frame">
                <div class="certificate-inner">
                    <div class="watermark-logo"></div>

                    {{-- ===== HEADER ===== --}}
                    <div class="header">
                        <div class="header-col header-left">
                            <div class="logo-wrap">
                                <img src="https://daisy.sp3stab.id/assets/images/logo.png" alt="Logo LAMDEPILAR">
                            </div>
                        </div>

                        <div class="header-col header-mid">
                            <div class="inst-name">
                                Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur
                                (LAMDEPILAR)
                            </div>
                            <div class="inst-sub">
                                Lampiran Sertifikat Akreditasi Program Studi
                            </div>
                        </div>

                        <div class="header-col header-right">
                            <div><strong>Nomor</strong></div>
                            <div>{{ $nomorSertifikat }}</div>
                            <div style="margin-top:3px;"><strong>Tanggal</strong></div>
                            <div>{{ \App\Libraries\Date::tglIndo($tanggalPenetapan) }}</div>
                        </div>
                    </div>

                    {{-- ===== PAGE 2 HEADER ===== --}}
                    <div class="page-2-header">
                        <div class="page-2-title">
                            Surat Keterangan Capaian Akreditasi
                        </div>
                    </div>

                    {{-- ===== TABEL ELEMEN ===== --}}
                    @if(!empty($elemenList))

                    @php
                    $grouped = collect($elemenList)->groupBy('kode_kriteria');

                    $halfGroupCount = (int) ceil($grouped->count() / 2);
                    $leftGroups = $grouped->take($halfGroupCount);
                    $rightGroups = $grouped->slice($halfGroupCount);

                    $defaultKategori = ['label' => '-', 'color' => '#e9ecef'];
                    @endphp

                    <div class="elemen-table-wrapper">
                        <div class="elemen-two-cols">

                            {{-- TABEL KIRI --}}
                            <table class="elemen-table">
                                <thead>
                                    <tr>
                                        <th>Kriteria</th>
                                        <th>Pernyataan Elemen</th>
                                        <th>Kategori</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leftGroups as $kodeKriteria => $elemens)
                                    @php
                                    $rowspan = $elemens->count();
                                    $namaKriteria = $elemens->first()['nama_kriteria'] ?? '';
                                    @endphp

                                    @foreach($elemens as $i => $elemen)
                                    @php
                                    $kategori = $elemen['skor_kategori'] ?? $defaultKategori;
                                    @endphp

                                    <tr>
                                        @if($i === 0)
                                        <td rowspan="{{ $rowspan }}" class="kriteria-cell">
                                            <span class="kriteria-badge">{{ $kodeKriteria }}</span>
                                            <div class="nama-kriteria">{{ $namaKriteria }}</div>
                                        </td>
                                        @endif

                                        <td>
                                            <span class="kode-elemen">{{ $elemen['kode_elemen'] }}</span>
                                            <span class="nama-elemen">{{ $elemen['nama_elemen'] }}</span>
                                        </td>

                                        <td style="text-align:center;">
                                            <span class="kategori-badge" style="background-color: {{ $kategori['color'] }};">
                                                {{ $kategori['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endforeach
                                </tbody>
                            </table>

                            {{-- TABEL KANAN --}}
                            <table class="elemen-table">
                                <thead>
                                    <tr>
                                        <th>Kriteria</th>
                                        <th>Pernyataan Elemen</th>
                                        <th>Kategori</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rightGroups as $kodeKriteria => $elemens)
                                    @php
                                    $rowspan = $elemens->count();
                                    $namaKriteria = $elemens->first()['nama_kriteria'] ?? '';
                                    @endphp

                                    @foreach($elemens as $i => $elemen)
                                    @php
                                    $kategori = $elemen['skor_kategori'] ?? $defaultKategori;
                                    @endphp

                                    <tr>
                                        @if($i === 0)
                                        <td rowspan="{{ $rowspan }}" class="kriteria-cell">
                                            <span class="kriteria-badge">{{ $kodeKriteria }}</span>
                                            <div class="nama-kriteria">{{ $namaKriteria }}</div>
                                        </td>
                                        @endif

                                        <td>
                                            <span class="kode-elemen">{{ $elemen['kode_elemen'] }}</span>
                                            <span class="nama-elemen">{{ $elemen['nama_elemen'] }}</span>
                                        </td>

                                        <td style="text-align:center;">
                                            <span class="kategori-badge" style="background-color: {{ $kategori['color'] }};">
                                                {{ $kategori['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                    @endif

                    {{-- ===== FOOTER ===== --}}
                    <div class="footer">
                        Dokumen ini merupakan lampiran dari Sertifikat Akreditasi Nomor: {{ $nomorSertifikat }}<br>
                        Diterbitkan oleh LAMDEPILAR sebagai rincian capaian standar akreditasi program studi.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;

        function showPage(pageNum) {
            const page1 = document.querySelector('.page-1-wrapper');
            const page2 = document.querySelector('.page-2-wrapper');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            if (pageNum === 1) {
                page1.style.display = 'flex';
                page2.style.display = 'none';
                prevBtn.disabled = true;
                nextBtn.disabled = false;
                currentPage = 1;
            } else if (pageNum === 2) {
                page1.style.display = 'none';
                page2.style.display = 'flex';
                prevBtn.disabled = false;
                nextBtn.disabled = true;
                currentPage = 2;
            }
        }

        // Initialize - show page 1
        showPage(1);

    </script>
</body>
</html>
