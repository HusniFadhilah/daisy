{{-- resources/views/asesmen/lha-asesor/pdf.blade.php --}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hasil Asesmen Lapangan</title>
    <style>
        @page {
            margin: 1.5cm 2cm 2cm 2cm;
        }

        body {
            font-family: 'Montserrat', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }

        /* ========== KOP SURAT ========== */
        .header {
            margin-bottom: 35px;
        }

        .kop-wrapper {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .kop-logo-section {
            display: table-cell;
            width: 95px;
            vertical-align: middle;
            padding-right: 12px;
        }

        .kop-logo-img {
            width: 85px;
            height: auto;
        }

        .kop-text-section {
            display: table-cell;
            vertical-align: middle;
        }

        .institution-name {
            font-size: 15pt;
            font-weight: bold;
            color: #1a1a1a;
            margin: 0 0 3px 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .institution-full {
            font-size: 11pt;
            color: #2c3e50;
            margin: 2px 0;
            line-height: 1.4;
        }

        .kop-divider-main {
            border: none;
            height: 3px;
            background: linear-gradient(to right, #2c3e50 0%, #3498db 50%, #2c3e50 100%);
            margin: 0;
        }

        .kop-divider-secondary {
            border: none;
            height: 1px;
            background-color: #95a5a6;
            margin: 2px 0 0 0;
        }

        .document-title-section {
            text-align: center;
            margin: 25px 0 20px 0;
        }

        .document-title-main {
            font-size: 15pt;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }

        /* ========== INFO TABLE ========== */
        .info-table {
            width: 100%;
            margin-bottom: 25px;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 6px 8px;
            vertical-align: top;
            font-size: 11pt;
        }

        .info-table td:first-child {
            width: 35%;
            font-weight: bold;
        }

        /* ========== SECTION ========== */
        .section {
            margin-bottom: 28px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 12px;
            padding: 10px 12px;
            background-color: #e9ecef;
            border-left: 6px solid #2c3e50;
        }

        .section-content {
            text-align: justify;
            margin-left: 15px;
            margin-right: 10px;
            white-space: pre-wrap;
            font-size: 11pt;
            line-height: 1.7;
        }

        /* ========== FOOTER ========== */
        .footer {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .footer-date {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .footer-team-title {
            font-size: 11pt;
            margin-bottom: 8px;
            color: #2c3e50;
        }

        .footer-team-list {
            margin: 0 0 20px 0;
            padding-left: 20px;
        }

        .footer-team-list li {
            font-size: 11pt;
            margin-bottom: 3px;
            color: #2c3e50;
        }

        .footer-approval {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 25px;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #27ae60;
            color: #2c3e50;
        }

        .footer-approval.pending {
            border-left-color: #f39c12;
            background-color: #fef9e7;
        }

        strong {
            font-weight: bold;
        }

        u {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <!-- KOP SURAT -->
    <div class="header">
        <div class="kop-wrapper">
            <div class="kop-logo-section">
                <img src="{{ public_path('assets/images/logo.png') }}" alt="Logo LAMDEPILAR" class="kop-logo-img">
            </div>
            <div class="kop-text-section">
                <div class="institution-name">LAMDEPILAR</div>
                <div class="institution-full">
                    Lembaga Akreditasi Mandiri Desain, Perencanaan Lingkungan, Arsitektur
                </div>
            </div>
        </div>

        <hr class="kop-divider-main">
        <hr class="kop-divider-secondary">

        <!-- Judul Dokumen -->
        <div class="document-title-section">
            <div class="document-title-main">Laporan Hasil Asesmen Lapangan (LHA)</div>
        </div>
    </div>

    <!-- Informasi Umum -->
    <table class="info-table">
        <tr>
            <td>Program Studi</td>
            <td>: {{ $asesmen->pengajuan->studyProgram->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Perguruan Tinggi</td>
            <td>: {{ $asesmen->pengajuan->studyProgram->university->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jenjang</td>
            <td>: {{ $asesmen->pengajuan->studyProgram->degreeLevel->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Nomor Permohonan</td>
            <td>: {{ $asesmen->pengajuan->nomor_pengajuan ?? '-' }}</td>
        </tr>
        @if($asesmen->asesmenLapangan && $asesmen->asesmenLapangan->scheduled_date)
        <tr>
            <td>Tanggal Pelaksanaan AL</td>
            <td>: {{ $asesmen->asesmenLapangan->scheduled_date->locale('id')->isoFormat('D MMMM Y') }}</td>
        </tr>
        @endif
    </table>

    <!-- 1. Pendahuluan -->
    <div class="section">
        <div class="section-title">I. PENDAHULUAN</div>
        <div class="section-content">{{ $lha->pendahuluan }}</div>
    </div>

    <!-- 2. Proses AL -->
    <div class="section">
        <div class="section-title">II. PROSES ASESMEN LAPANGAN</div>
        <div class="section-content">{{ $lha->proses_al }}</div>
    </div>

    <!-- 3. Hasil AL -->
    <div class="section">
        <div class="section-title">III. HASIL ASESMEN LAPANGAN</div>
        <div class="section-content">{{ $lha->hasil_al }}</div>
    </div>

    <!-- 4. Rekomendasi PS -->
    <div class="section">
        <div class="section-title">IV. REKOMENDASI UNTUK PROGRAM STUDI</div>
        <div class="section-content">{{ $lha->rekomendasi_ps }}</div>
    </div>

    <!-- 5. Rekomendasi LAMDEPILAR -->
    <div class="section">
        <div class="section-title">V. REKOMENDASI UNTUK LAMDEPILAR</div>
        <div class="section-content">{{ $lha->rekomendasi_lamdepilar }}</div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <!-- Tanggal LHA Dibuat -->
        <div class="footer-date">
            Tanggal LHA Dibuat: {{ $lha->created_at ? $lha->created_at->locale('id')->isoFormat('D MMMM Y') : now()->locale('id')->isoFormat('D MMMM Y') }}
        </div>

        <!-- Tim Asesor -->
        <div class="footer-team-title">
            Laporan Hasil Asesmen Lapangan (LHA) disusun oleh tim asesor:
        </div>
        <ol class="footer-team-list">
            @php
            $asesorList = $asesmen->asesorAL()
            ->where('status_penawaran', 'accepted')
            ->with('user')
            ->get();
            @endphp

            @forelse($asesorList as $asesor)
            <li>{{ $asesor->user->name ?? '-' }}</li>
            @empty
            <li>{{ $lha->creator->name ?? Auth::user()->name }}</li>
            @endforelse
        </ol>

        <!-- Pernyataan Persetujuan -->
        @php
        // Ambil dokumen LHA yang sudah diupload
        $lhaDocument = $asesmen->documents()
        ->where('type', 'lha_asesor')
        ->where('is_active', true)
        ->latest('uploaded_at')
        ->first();

        $isApproved = $lhaDocument && $lhaDocument->status_persetujuan_prodi === 'approved';
        @endphp

        @if($isApproved)
        <div class="footer-approval">
            {{ $asesmen->pengajuan->studyProgram->university->name ?? 'Universitas' }}
            Program Studi {{ $asesmen->pengajuan->studyProgram->name ?? '' }}
            menyatakan menyetujui Laporan Hasil Asesmen Lapangan (LHA)
        </div>
        @else
        {{-- <div class="footer-approval pending">
            {{ $asesmen->pengajuan->studyProgram->university->name ?? 'Universitas' }}
        Program Studi {{ $asesmen->pengajuan->studyProgram->name ?? '' }}
        menyatakan menyetujui Laporan Hasil Asesmen Lapangan (LHA)
    </div> --}}
    @endif
    </div>
</body>
</html>
