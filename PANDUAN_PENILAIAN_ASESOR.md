# 📋 Panduan Penilaian Asesor - Sistem DAISY

## Struktur Skala Penilaian

Sistem menggunakan **skala skor 0-4** untuk setiap elemen standar:

| Skor | Kategori | Deskripsi |
|------|----------|-----------|
| **0** | Not Met | Tidak ada aspek yang dipenuhi atau dokumen/bukti tidak valid atau kedaluwarsa |
| **1** | Not Met | 1 atau 2 aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik |
| **2** | Weakness | 4 aspek terpenuhi namun masih terdapat kekurangan atau ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan |
| **3** | Met | 4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik |
| **4** | Exceeds | Terdapat pengakuan eksternal/pelampauan standar (sertifikasi, penghargaan, kemitraan, dll) |

---

## Struktur Database Penilaian

### Tabel: `penilaian_elemen_ak`

| Field | Tipe | Keterangan |
|-------|------|------------|
| `id_asesmen` | Foreign Key | ID Asesmen yang dinilai |
| `id_asesor` | Foreign Key | ID User (Asesor) yang menilai |
| `id_elemen` | Foreign Key | ID Elemen Standar yang dinilai |
| `skor` | Integer (0-4) | **Skor penilaian** |
| `komentar` | Text | **Justifikasi/deskripsi penilaian asesor** |
| `status` | Enum | 'draft' atau 'submitted' |
| `status_validasi` | Enum | Status validasi oleh validator |

---

## Kriteria Penilaian Berdasarkan Elemen

### D - Diferensiasi Misi
- **D.1** - Legalitas Program dan Tata Pamong
- **D.2** - Visi, Misi, Tujuan
- **D.3** - Kesesuaian Visi Keilmuan
- **D.4** - Kurikulum

### E - Edukasi, Sistem Evaluasi, dan Capaian Pembelajaran
- **E.1** - Admisi Mahasiswa
- **E.2** - Proses dan Siklus Pembelajaran
- **E.3** - Penilaian dan Evaluasi
- **E.4** - Kompetensi Lulusan dan Capaian Pembelajaran
- **E.5** - Kompetensi Lulusan dan Capaian Pembelajaran (lanjutan)

### P - Pengembangan Sumber Daya Manusia
- **P.1** - Dosen dan Tenaga Kependidikan
- **P.2** - Sarana dan Prasarana
- **P.3** - Pengembangan Kapasitas
- **P.4** - Kesejahteraan Kerja

### I - Internalisasi Penjaminan Mutu
- **I.1** - Sistem Penjaminan Mutu Internal
- **I.2** - Implementasi Perbaikan Berkelanjutan
- **I.3** - Keterlibatan Pemangku Kepentingan dan Penjaminan Mutu Eksternal

### L - Lingkungan dan Sumber Belajar serta Pendukung Mahasiswa
- **L.1** - Sarana dan Prasarana
- **L.2** - Sumber Pengetahuan
- **L.3** - Kepuasan Mahasiswa dan Alumni
- **L.4** - Lulusan, Kajian Telusur, dan Kepuasan Pengguna

### A - Akuntabilitas, Tata Kelola, dan Kerjasama
- **A.1** - Organisasi dan Tata Kelola
- **A.2** - Kerjasama dan Kemitraan
- **A.3** - Sistem dan Manajemen Informasi
- **A.4** - Keselamatan dan Kesehatan Kerja serta Kelestarian Lingkungan
- **A.5** - Keuangan, Keberlanjutan, dan Mitigasi Risiko

### R - Riset, Pengabdian, dan Suasana Ilmiah
- **R.1** - Kebijakan Penelitian
- **R.2** - Proses Penelitian
- **R.3** - Luaran dan Dampak Penelitian
- **R.4** - Kebijakan Pengabdian kepada Masyarakat
- **R.5** - Proses Pengabdian kepada Masyarakat
- **R.6** - Luaran dan Dampak Pengabdian kepada Masyarakat

---

## Cara Menggunakan Seeder

### 1. Jalankan Seeder Penilaian Dummy

```bash
php artisan db:seed --class=PenilaianAsesorDummySeeder
```

### 2. Seeder akan membuat penilaian untuk:
- **Asesor 1** (asesor1@daisy.lamdepilar.or.id)
- **Asesor 2** (asesor2@daisy.lamdepilar.or.id)

### 3. Karakteristik Data Seeder:
- Skor realistis berdasarkan rubrik penilaian
- Komentar/justifikasi lengkap untuk setiap elemen
- Ada sedikit perbedaan antara Asesor 1 dan 2 (untuk testing konsistensi)
- Status: `submitted` (sudah disubmit oleh asesor)
- Status validasi: `not_validated` (belum divalidasi)

---

## Contoh Insert Manual via Tinker

Jika ingin memasukkan data manual via Laravel Tinker:

```php
php artisan tinker
```

```php
use App\Models\PenilaianElemenAk;
use App\Models\Asesmen;
use App\Models\User;
use App\Models\ElemenStandar;

// Ambil data referensi
$asesmen = Asesmen::first();
$asesor = User::where('email', 'asesor1@daisy.lamdepilar.or.id')->first();
$elemen = ElemenStandar::where('kode_elemen', 'D.1')->first();

// Insert penilaian
PenilaianElemenAk::create([
    'id_asesmen' => $asesmen->id,
    'id_asesor' => $asesor->id,
    'id_elemen' => $elemen->id,
    'skor' => 3,  // Skor 0-4
    'komentar' => 'Program studi memiliki legalitas program dan tata pamong yang jelas, visi misi tercatat dengan baik, struktur organisasi lengkap, dan dokumen pendukung valid serta mudah diverifikasi.',
    'status' => 'submitted',
    'status_validasi' => 'not_validated',
]);
```

---

## Validasi Penilaian

Sistem akan melakukan validasi:

1. **Completeness Check**: 
   - Semua elemen harus dinilai sebelum submit
   - Setiap elemen harus memiliki skor DAN komentar

2. **Consistency Check**:
   - Jika ada perbedaan skor >1 antara 2 asesor → perlu rekonsiliasi
   - Validator akan mereview dan memberikan preferensi skor

3. **Lock Mechanism**:
   - Setelah divalidasi, penilaian akan dikunci (`is_locked = 1`)
   - Tidak bisa diubah tanpa unlock oleh admin

---

## Tips Menulis Komentar Penilaian

### ✅ BAIK
```
"Program studi memiliki sistem penjaminan mutu internal yang berjalan efektif. 
Terdapat dokumen SPMI lengkap yang mencakup standar, prosedur, dan instrumen 
audit. Siklus PPEPP (Penetapan-Pelaksanaan-Evaluasi-Pengendalian-Peningkatan) 
terdokumentasi dengan baik melalui laporan audit internal tahunan."
```

### ❌ KURANG BAIK
```
"Sudah bagus"
"Memenuhi syarat"
"OK"
```

### Template Komentar Berdasarkan Skor:

**Skor 0:**
> "Tidak ditemukan dokumen pendukung yang valid untuk elemen [nama elemen]. Dokumen yang disampaikan tidak lengkap dan/atau kedaluwarsa."

**Skor 1:**
> "Ditemukan hanya [1-2] aspek yang terpenuhi dari 4 aspek yang dipersyaratkan untuk elemen [nama elemen]. Dokumentasi yang ada belum dapat dijadikan landasan penilaian yang memadai."

**Skor 2:**
> "Sebagian besar (4 aspek) sudah terpenuhi untuk elemen [nama elemen], namun masih terdapat kekurangan dalam [sebutkan aspek]. Implementasi perlu diperkuat terutama pada [area tertentu]."

**Skor 3:**
> "Seluruh aspek (4 aspek) terpenuhi dengan baik untuk elemen [nama elemen]. Didukung dokumen yang valid, lengkap, transparan, dan mudah diverifikasi. [Sebutkan bukti spesifik]."

**Skor 4:**
> "Selain memenuhi seluruh aspek standar, program studi menunjukkan pelampauan standar melalui [sebutkan pengakuan eksternal seperti: sertifikasi, penghargaan, kemitraan internasional, publikasi, dll]. [Sebutkan bukti konkret]."

---

## Workflow Penilaian

```
1. DE menugaskan Asesor
   ↓
2. Asesor menerima penugasan (status: accepted)
   ↓
3. Asesor mengisi penilaian (status: draft)
   ↓
4. Asesor submit penilaian (status: submitted)
   ↓
5. Validator mereview konsistensi 2 asesor
   ↓
6. Jika ada perbedaan → rekonsiliasi
   ↓
7. Validator approve (status_validasi: validated)
   ↓
8. Penilaian locked
   ↓
9. Perhitungan skor final
```

---

## Referensi Elemen Standar

Lihat file: `database/seeders/IndikatorPenilaianElemenSeeder.php` untuk daftar lengkap indikator penilaian setiap elemen.

---

## Troubleshooting

### Penilaian tidak muncul di dashboard
- Pastikan `status_penawaran = 'accepted'` di tabel `asesmen_user_roles`
- Pastikan `id_asesmen` sesuai dengan asesmen yang aktif
- Cek apakah elemen sudah di-seed dengan benar

### Error "Penilaian belum lengkap"
- Pastikan SEMUA elemen memiliki skor DAN komentar
- Cek dengan query:
```sql
SELECT COUNT(*) FROM penilaian_elemen_ak 
WHERE id_asesmen = [id] 
AND id_asesor = [id] 
AND skor IS NOT NULL 
AND komentar IS NOT NULL;
```

### Tidak bisa submit
- Pastikan status masih `draft`, bukan `submitted`
- Pastikan `is_locked = 0`

---

**Dibuat:** Januari 2026  
**Versi:** 1.0  
**Update terakhir:** {{ date('d-m-Y') }}
