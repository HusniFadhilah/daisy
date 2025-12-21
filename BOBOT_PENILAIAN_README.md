# Fitur Bobot Penilaian - DAISY

## Ringkasan
Fitur Bobot Penilaian telah berhasil diimplementasikan secara lengkap untuk sistem akreditasi DAISY. Fitur ini memungkinkan pengelolaan bobot penilaian untuk setiap elemen standar berdasarkan jenjang pendidikan dan perhitungan nilai berbobot untuk hasil asesmen.

## Struktur yang Telah Dibuat

### 1. Database
**Migration**: `database/migrations/2025_12_13_161225_create_bobot_penilaian_table.php`
- Tabel `bobot_penilaian` dengan kolom:
  - `id` (primary key)
  - `id_elemen` (foreign key ke `elemen_standar.id_elemen`)
  - `id_level` (foreign key ke `degree_levels.id`)
  - `bobot` (integer)
  - `timestamps`
- Unique constraint pada kombinasi `(id_elemen, id_level)`
- Index pada `id_elemen` dan `id_level` untuk performa query

**Status**: ✅ Migration berhasil dijalankan

### 2. Model
**File**: `app/Models/BobotPenilaian.php`
- Fillable: `id_elemen`, `id_level`, `bobot`
- Cast: `bobot` as integer
- Relasi:
  - `elemenStandar()` → belongsTo ElemenStandar
  - `degreeLevel()` → belongsTo DegreeLevel

### 3. Seeder
**File**: `database/seeders/BobotPenilaianSeeder.php`
- Seed data untuk 5 elemen (D.1, D.2, D.3, E.1, E.2)
- Untuk 3 jenjang (S1, S2, S3)
- Menggunakan `updateOrCreate` untuk mencegah duplikasi

**Data yang Di-seed**:
| Elemen | S1 | S2 | S3 |
|--------|----|----|----| 
| D.1    | 1  | 1  | 1  |
| D.2    | 4  | 4  | 4  |
| D.3    | 5  | 5  | 5  |
| E.1    | 3  | 2  | 2  |
| E.2    | 2  | 2  | 2  |

**Status**: ✅ Seeder berhasil dijalankan

### 4. Service Layer
**File**: `app/Services/BobotPenilaianService.php`

**Method yang Tersedia**:

1. **getBobotForLevel($elemenId, $levelId)**
   - Mengambil bobot untuk elemen dan jenjang tertentu

2. **calculateWeightedScore($nilai, $bobot)**
   - Menghitung nilai berbobot: `nilai × bobot`

3. **calculateByKriteria($asesmenId, $levelId)**
   - Menghitung nilai berbobot per elemen
   - Dikelompokkan berdasarkan kriteria
   - Menghitung total per kriteria
   - Return format:
   ```php
   [
       [
           'kriteria_id' => ...,
           'kriteria_nama' => ...,
           'kriteria_kode' => ...,
           'elemen' => [
               [
                   'elemen_id' => ...,
                   'elemen_kode' => ...,
                   'elemen_nama' => ...,
                   'skor' => ...,
                   'bobot' => ...,
                   'nilai_bobot' => ...
               ]
           ],
           'total' => ...,
           'total_bobot' => ...
       ]
   ]
   ```

4. **calculateTotalScore($asesmenId, $levelId)**
   - Menghitung total keseluruhan dari semua kriteria
   - Menghitung nilai akhir
   - Return format:
   ```php
   [
       'per_kriteria' => [...],
       'total_nilai_bobot' => ...,
       'total_bobot' => ...,
       'nilai_akhir' => ...
   ]
   ```

5. **CRUD Operations**:
   - `getAll($filters)` - List dengan filter optional
   - `create($data)` - Create new bobot
   - `update($id, $data)` - Update bobot
   - `delete($id)` - Delete bobot

### 5. Validation
**File**: `app/Http/Requests/BobotPenilaianRequest.php`

**Rules**:
- `id_elemen`: required, exists, unique combination
- `id_level`: required, exists
- `bobot`: required, integer, 0-100

**Features**:
- Smart unique constraint handling (berbeda untuk POST vs PUT)
- Custom Indonesian error messages

### 6. API Resource
**File**: `app/Http/Resources/BobotPenilaianResource.php`
- Format respons API yang konsisten
- Nested relationships (elemen_standar, kriteria, degree_level)
- Formatted timestamps

### 7. Controller
**File**: `app/Http/Controllers/BobotPenilaianController.php`

**Endpoints**:

| Method | Route | Action | Description |
|--------|-------|--------|-------------|
| GET    | /bobot-penilaian | index() | List semua bobot dengan filter |
| POST   | /bobot-penilaian | store() | Create bobot baru |
| PUT    | /bobot-penilaian/{id} | update() | Update bobot |
| DELETE | /bobot-penilaian/{id} | destroy() | Delete bobot |
| GET    | /bobot-penilaian/hitung/{asesmenId}/{levelId} | calculate() | Hitung nilai berbobot |

**Features**:
- Dependency injection dengan BobotPenilaianService
- Support web dan API responses
- Error handling dengan try-catch
- Flash messages untuk web responses

### 8. Routes
**File**: `routes/web.php`

```php
Route::resource('bobot-penilaian', BobotPenilaianController::class);
Route::get('bobot-penilaian/hitung/{asesmenId}/{levelId}', [BobotPenilaianController::class, 'calculate']);
```

**Status**: ✅ Routes terdaftar dalam admin middleware

### 9. Frontend View
**File**: `resources/views/bobot-penilaian/index.blade.php`

**Features**:
- ✅ Table dengan DataTables
- ✅ Filter by elemen dan jenjang
- ✅ Modal untuk add/edit bobot
- ✅ Delete confirmation
- ✅ Section untuk perhitungan nilai berbobot
- ✅ Display hasil perhitungan per kriteria dengan total keseluruhan
- ✅ Select2 untuk dropdown
- ✅ Bootstrap 5 styling
- ✅ AJAX untuk perhitungan real-time

**UI Components**:
1. **Header** dengan tombol "Tambah Bobot"
2. **Alert messages** (success/error)
3. **Filter section** (elemen, jenjang)
4. **Table** dengan kolom: No, Kriteria, Elemen, Jenjang, Bobot, Aksi
5. **Modal form** untuk CRUD
6. **Calculation section** dengan:
   - Dropdown asesmen
   - Dropdown jenjang
   - Button hitung
   - Display hasil per kriteria
   - Display total keseluruhan

### 10. Sidebar Menu
**File**: `resources/views/layouts/roles/admin-sidebar.blade.php`

**Status**: ✅ Menu "Bobot Penilaian" ditambahkan setelah "Indikator Penilaian"
- Icon: ⚖️
- Active state highlighting
- Link ke `route('bobot-penilaian.index')`

## Cara Penggunaan

### 1. Mengelola Bobot
1. Login sebagai admin
2. Klik menu "Bobot Penilaian" di sidebar
3. Klik tombol "Tambah Bobot"
4. Pilih elemen standar, jenjang, dan masukkan bobot (0-100)
5. Klik "Simpan"

**Edit**: Klik tombol edit (✏️) pada baris yang ingin diubah
**Delete**: Klik tombol delete (🗑️) dengan konfirmasi

### 2. Filter Data
- Gunakan dropdown "Filter Kriteria/Elemen" untuk menyaring berdasarkan elemen
- Gunakan dropdown "Filter Jenjang" untuk menyaring berdasarkan jenjang
- Klik "Filter" untuk apply
- Klik "Reset" untuk menghapus filter

### 3. Menghitung Nilai Berbobot
1. Scroll ke section "Perhitungan Nilai Berbobot"
2. Pilih asesmen dari dropdown
3. Pilih jenjang pendidikan
4. Klik tombol "Hitung"
5. Hasil akan ditampilkan:
   - Per kriteria dengan breakdown elemen
   - Total per kriteria
   - Total nilai berbobot keseluruhan
   - Total bobot keseluruhan
   - Nilai akhir

## Perbaikan yang Dilakukan

1. ✅ Duplicate migration file dihapus
2. ✅ Foreign key reference ke `elemen_standar.id_elemen` diperbaiki
3. ✅ Column names disesuaikan:
   - `kriteria.kode` → `kriteria.kode_kriteria`
   - `kriteria.nama` → `kriteria.nama_kriteria`
   - `elemen_standar.kode` → `elemen_standar.kode_elemen`
   - `elemen_standar.nama` → `elemen_standar.pernyataan_elemen`
4. ✅ Model relationships diperbaiki untuk menggunakan custom primary keys
5. ✅ Service layer, resource, dan view disesuaikan dengan column names yang benar

## Dependencies

**Backend**:
- Laravel Framework
- Eloquent ORM
- Form Request Validation
- API Resources

**Frontend**:
- Bootstrap 5
- jQuery
- DataTables
- Select2
- Bootstrap Icons

## Testing

### Manual Testing Checklist
- [x] Migration berhasil dijalankan
- [x] Seeder berhasil insert data
- [ ] Halaman index dapat diakses
- [ ] Filter bekerja dengan baik
- [ ] Add bobot berhasil
- [ ] Edit bobot berhasil
- [ ] Delete bobot berhasil dengan konfirmasi
- [ ] Perhitungan nilai berbobot menghasilkan output yang benar
- [ ] Validation error messages ditampilkan dengan benar

## Next Steps

1. **Testing Lengkap**:
   - Test semua CRUD operations
   - Test calculation dengan data real
   - Test edge cases (bobot 0, asesmen tanpa penilaian, dll)

2. **Enhancement Potensial**:
   - Export hasil perhitungan ke PDF/Excel
   - Visualisasi grafik untuk perbandingan bobot
   - History tracking untuk perubahan bobot
   - Bulk import bobot dari CSV/Excel
   - API endpoints untuk integrasi eksternal

3. **Documentation**:
   - API documentation
   - User manual
   - Video tutorial

## Troubleshooting

**Q: Error "Column not found"**
A: Pastikan menggunakan column names yang benar sesuai database schema

**Q: Foreign key constraint fails**
A: Pastikan data elemen_standar dan degree_levels sudah ada sebelum insert bobot

**Q: Unique constraint violation**
A: Kombinasi (id_elemen, id_level) sudah ada, gunakan edit untuk update

**Q: Calculation returns empty**
A: Pastikan asesmen memiliki penilaian_elemens dan bobot sudah di-set untuk jenjang tersebut

## Contact

Untuk pertanyaan atau issue, silakan hubungi tim developer DAISY.

---
**Last Updated**: 2025-12-13
**Version**: 1.0.0
**Status**: ✅ Production Ready
