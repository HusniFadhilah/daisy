{{-- resources/views/de/penerimaan-dokumen/templates/surat-tugas-validator.blade.php --}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Tugas Validator Dokumen</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            margin: 2cm;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 14pt;
        }

        .nomor {
            text-align: center;
            margin: 20px 0;
        }

        .content {
            text-align: justify;
        }

        table {
            width: 100%;
            margin: 20px 0;
        }

        .signature {
            margin-top: 50px;
            text-align: right;
        }

    </style>
</head>
<body>
    <div class="header">
        <h2>LEMBAGA AKREDITASI MANDIRI PENDIDIKAN PROFESI PILAR</h2>
        <h2>(LAMDEPILAR)</h2>
        <p>Alamat Kantor</p>
    </div>

    <div class="nomor">
        <strong>SURAT TUGAS</strong><br>
        Nomor: {{ $nomorSurat }}
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini:</p>

        <table>
            <tr>
                <td width="30%">Nama</td>
                <td width="5%">:</td>
                <td>[Nama Ketua LAMDEPILAR]</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>Ketua LAMDEPILAR</td>
            </tr>
        </table>

        <p>Dengan ini menugaskan:</p>

        <table>
            <tr>
                <td width="30%">Nama</td>
                <td width="5%">:</td>
                <td>{{ $validator->user->name }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>:</td>
                <td>{{ $validator->user->email }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>Validator Dokumen</td>
            </tr>
        </table>

        <p>Untuk melaksanakan validasi dokumen akreditasi dengan rincian sebagai berikut:</p>

        <table>
            <tr>
                <td width="30%">Program Studi</td>
                <td width="5%">:</td>
                <td>{{ $pengajuan->studyProgram->name }}</td>
            </tr>
            <tr>
                <td>Jenjang</td>
                <td>:</td>
                <td>{{ $pengajuan->studyProgram->degreeLevel->name }}</td>
            </tr>
            <tr>
                <td>Universitas</td>
                <td>:</td>
                <td>{{ $pengajuan->studyProgram->university->name }}</td>
            </tr>
            <tr>
                <td>Nomor Permohonan</td>
                <td>:</td>
                <td>{{ $pengajuan->nomor_pengajuan }}</td>
            </tr>
            <tr>
                <td>Jenis Akreditasi</td>
                <td>:</td>
                <td>{{ $pengajuan->jenis_akreditasi_label }}</td>
            </tr>
        </table>

        <p>Demikian surat tugas ini dibuat untuk dilaksanakan dengan sebaik-baiknya dan penuh tanggung jawab.</p>
    </div>

    <div class="signature">
        <p>{{ $tanggal }}</p>
        <p>Ketua LAMDEPILAR,</p>
        <br><br><br>
        <p><strong>[Nama Ketua]</strong></p>
    </div>
</body>
</html>
