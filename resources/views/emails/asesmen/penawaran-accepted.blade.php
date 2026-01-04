@component('mail::message')
# ✅ Penawaran Diterima

Yth. **{{ $user->name }}**,

Terima kasih telah menerima penawaran sebagai **{{ $role->alias }}** untuk:

@component('mail::panel')
**{{ $asesmen->name }}**

Jenis: **{{ $jenisAsesmen }}**
@endcomponent

## Status Penerimaan

✅ **Status:** Diterima<br>
📅 **Waktu Respons:** {{ \Carbon\Carbon::parse($assignment->responded_at)->format('d F Y H:i') }}

@if($assignment->response_note)
**Catatan Anda:**
> {{ $assignment->response_note }}
@endif

## Langkah Selanjutnya

Anda sekarang dapat memulai penilaian melalui dashboard:

@component('mail::button', ['url' => $dashboardUrl, 'color' => 'primary'])
Mulai Penilaian
@endcomponent

---

**Panduan:**
1. Login ke sistem
2. Akses menu Berkas Penilaian
3. Lakukan penilaian sesuai elemen standar
4. Submit penilaian setelah selesai

Jika ada pertanyaan, hubungi admin di {{ $adminEmail }}.

Salam,<br>
{{ config('app.name') }}
@endcomponent
