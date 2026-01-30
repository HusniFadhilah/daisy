@component('mail::message')
# Penawaran Validasi LED - {{ $pengajuan->nomor_pengajuan ?? '-' }}

Yth. **{{ optional($assignment->user)->name ?? 'Validator' }}**,

Anda telah ditunjuk untuk **memvalidasi borang** pada Permohonan akreditasi berikut:

@component('mail::panel')
**{{ optional($pengajuan)->studyProgram->name ?? '-' }}**
Nomor Pengajuan: **{{ $pengajuan->nomor_pengajuan ?? '-' }}**

@if($assignment->urutan_asesor)
Urutan: **Asesor {{ $assignment->urutan_asesor }}**
@endif
@endcomponent

## Detail Pengajuan

- **Program Studi:** {{ optional($pengajuan->studyProgram)->name ?? '-' }}
- **Universitas:** {{ optional($pengajuan->studyProgram->university)->name ?? '-' }}
- **Borang Terakhir:** {{ optional($pengajuan->latestBorangImport)->file_name ?? '-' }}

## Langkah Selanjutnya

Silakan gunakan akun **{{ optional($assignment->user)->email ?? '-' }}** untuk meninjau dan menanggapi penawaran ini melalui tombol di bawah:

@component('mail::button', ['url' => $acceptUrl ?? '#', 'color' => 'success'])
Lihat Detail & Respond
@endcomponent

@if($catatanDe)
**Catatan DE:**
{{ $catatanDe }}
@endif

---

**Catatan Penting:**
- Harap berikan respons dalam waktu 3x24 jam
- Jika menolak, mohon berikan alasan yang jelas
- Setelah menerima, Anda dapat langsung memulai validasi

Terima kasih atas perhatian dan kerjasamanya.

Hormat Kami,<br>
Sekretariat LAMDEPILAR
@endcomponent
