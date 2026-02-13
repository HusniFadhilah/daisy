@component('mail::message')
# Penawaran {{ $role->alias }} - {{ $jenisAsesmen }}

Yth. **{{ $user->name }}**,

Anda telah ditunjuk sebagai **{{ $role->alias }}** untuk asesmen berikut:

@component('mail::panel')
**{{ $asesmen->name }}**

Jenis Asesmen: **{{ $jenisAsesmen }}**

@if($assignment->urutan_asesor)
Urutan: **Asesor {{ $assignment->urutan_asesor }}**
@endif
@endcomponent

## Detail Asesmen

- **Kode:** {{ $asesmen->code }}
- **Program Studi:** {{ $asesmen->studyProgram->name ?? '-' }}
@if($asesmen->tanggal_mulai)
- **Tanggal Mulai:** {{ \App\Libraries\Date::tglIndo($asesmen->tanggal_mulai) }}
@endif
@if($asesmen->tanggal_selesai)
- **Tanggal Selesai:** {{ \App\Libraries\Date::tglIndo($asesmen->tanggal_selesai) }}
@endif

## Langkah Selanjutnya

Silakan gunakan akun {{ $user->email }} yang telah diberikan sebelumnya untuk **terima atau tolak** penawaran ini melalui tombol di bawah:

@component('mail::button', ['url' => $acceptUrl, 'color' => 'success'])
Lihat Detail & Respond
@endcomponent

{{-- **Atau klik link berikut:**
{{ $acceptUrl }} --}}

---

**Catatan Penting:**
- Harap berikan respons dalam waktu 3x24 jam
- Jika menolak, mohon berikan alasan yang jelas
- Setelah menerima, Anda dapat langsung memulai penilaian

Terima kasih atas perhatian dan kerjasamanya.

Hormat Kami<br>
Sekretariat LAMDEPILAR
@endcomponent
