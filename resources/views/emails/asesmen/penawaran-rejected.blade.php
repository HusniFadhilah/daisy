@component('mail::message')
# ❌ Penawaran Ditolak

Yth. Administrator,

Penawaran asesmen berikut telah **ditolak** oleh yang bersangkutan:

@component('mail::panel')
**User:** {{ $user->name }} ({{ $user->email }})

**Role:** {{ $role->alias }}

**Asesmen:** {{ $asesmen->name }}

**Jenis:** {{ $jenisAsesmen }}
@endcomponent

## Detail Penolakan

📅 **Waktu Penolakan:** {{ \Carbon\Carbon::parse($assignment->responded_at)->locale('id')->translatedFormat('d F Y H:i') }}

**Alasan Penolakan:**
> {{ $assignment->response_note ?? 'Tidak ada catatan' }}

## Tindakan yang Diperlukan

⚠️ **Silakan assign pengganti** untuk melanjutkan proses asesmen.

@component('mail::button', ['url' => $reassignUrl, 'color' => 'warning'])
Assign Pengganti
@endcomponent

---

**Rekomendasi:**
- Cari asesor/validator pengganti yang tersedia
- Pastikan memenuhi kualifikasi yang sama
- Segera lakukan re-assignment agar proses tidak terhambat

Terima kasih.

Hormat Kami<br>
Sekretariat LAMDEPILAR
@endcomponent
