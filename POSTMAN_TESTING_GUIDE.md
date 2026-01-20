# 🧪 Panduan Testing API Pemetaan Akreditasi dengan Postman

## 📋 Daftar Isi
1. [Perubahan yang Dilakukan](#perubahan-yang-dilakukan)
2. [Setup Postman](#setup-postman)
3. [Endpoint Testing](#endpoint-testing)
4. [Contoh Response](#contoh-response)

---

## 🔧 Perubahan yang Dilakukan

### 1. **Route Baru (routes/web.php)**
Ditambahkan route khusus untuk testing **TANPA AUTENTIKASI**:

```php
// Sebelum: Semua route pemetaan butuh login
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('pemetaan')->name('pemetaan.')->group(function () {
        // ... routes memerlukan login
    });
});

// Sesudah: Ada route testing tanpa login
Route::prefix('api/test')->name('api.test.')->group(function () {
    Route::get('/pemetaan/stats', [PemetaanAkreditasiController::class, 'getStatsForTesting']);
    Route::get('/pemetaan/timeline/{periode?}', [PemetaanAkreditasiController::class, 'getTimelineForTesting']);
    Route::get('/pemetaan/calendar', [PemetaanAkreditasiController::class, 'getCalendarForTesting']);
    Route::get('/pemetaan/programs', [PemetaanAkreditasiController::class, 'getProgramsForTesting']);
});
```

**Perbedaan Utama:**
- ❌ Route lama: Di dalam `middleware(['auth'])` → PERLU LOGIN
- ✅ Route testing: Di luar middleware → TIDAK PERLU LOGIN
- ✅ Prefix `/api/test` → Mudah dikenali sebagai testing endpoint
- ✅ Return JSON murni → Cocok untuk Postman

### 2. **Method Baru di Controller**
Ditambahkan 4 method testing baru di `PemetaanAkreditasiController.php`:

```php
// Method baru untuk testing (tanpa HTML view)
1. getStatsForTesting()      → Statistik keseluruhan
2. getTimelineForTesting()    → Data timeline per periode
3. getCalendarForTesting()    → Data kalender 12 bulan
4. getProgramsForTesting()    → List program studi dengan filter
```

**Perbedaan dengan Method Lama:**
| Aspek | Method Lama | Method Testing |
|-------|-------------|----------------|
| **Return** | HTML View | JSON Response |
| **Auth** | Required ✅ | Not Required ❌ |
| **Transform** | Raw Collection | Formatted Array |
| **Timestamp** | Tidak ada | ISO8601 Format |
| **Success Flag** | Tidak ada | Ada (`success: true/false`) |

### 3. **Urutan Route Diperbaiki**
```php
// Sebelum: Route dinamis di awal (SALAH!)
Route::get('/{id}', ...);              // ❌ Akan catch "timeline" sebagai ID
Route::get('/timeline/ajax', ...);     // ❌ Tidak akan pernah diakses

// Sesudah: Route spesifik dulu (BENAR!)
Route::get('/timeline/ajax', ...);     // ✅ Diakses dulu
Route::get('/calendar/ajax', ...);     // ✅ Diakses dulu
Route::get('/{id}', ...);              // ✅ Catch ID terakhir
```

### 4. **Bug Fix Carbon Date**
```php
// Sebelum: Tanpa copy() → Memodifikasi object yang sama
$startRange = now()->startOfDay();
$endRange = now()->addYears(5)->endOfDay(); // ❌ Memodifikasi $startRange juga!

// Sesudah: Dengan copy() → Object terpisah
$startRange = now()->startOfDay();
$endRange = now()->copy()->addYears(5)->endOfDay(); // ✅ Object baru
```

---

## 🚀 Setup Postman

### 1. Pastikan Laravel Server Running
```bash
php artisan serve
```
Server akan berjalan di: `http://127.0.0.1:8000`

### 2. Buka Postman
- Download: https://www.postman.com/downloads/
- Atau gunakan web version: https://web.postman.com/

### 3. Buat Collection Baru (Opsional)
- Klik **"New Collection"**
- Nama: `DAISY - Pemetaan Akreditasi Testing`

---

## 📡 Endpoint Testing

### 1️⃣ **GET Statistik Keseluruhan**

**Endpoint:** `GET http://127.0.0.1:8000/api/test/pemetaan/stats`

**Cara Test di Postman:**
1. Method: **GET**
2. URL: `http://127.0.0.1:8000/api/test/pemetaan/stats`
3. Headers: **TIDAK PERLU** (no auth, no token)
4. Klik **Send**

**Response yang Diharapkan:**
```json
{
    "success": true,
    "message": "Statistik berhasil diambil",
    "data": {
        "total_program_studi": 800,
        "status": {
            "aktif": 712,
            "kedaluwarsa": 10,
            "belum_terakreditasi": 78
        },
        "segera_kedaluwarsa": {
            "dalam_3_bulan": 39,
            "dalam_6_bulan": 93,
            "dalam_12_bulan": 150
        },
        "by_peringkat": {
            "A": 50,
            "B": 300,
            "C": 200,
            "Unggul": 100
        },
        "persentase": {
            "aktif": 89.0,
            "kedaluwarsa": 1.25,
            "belum_terakreditasi": 9.75
        }
    },
    "timestamp": "2025-12-23T12:34:56+07:00"
}
```

**Interpretasi:**
- ✅ `success: true` → Query berhasil
- 📊 Total 800 program studi di database
- 🟢 712 aktif (89%)
- 🔴 10 kedaluwarsa (perlu reakreditasi)
- ⚪ 78 belum terakreditasi

---

### 2️⃣ **GET Timeline Data per Periode**

**Endpoint:** `GET http://127.0.0.1:8000/api/test/pemetaan/timeline/{periode}`

**Parameter Periode (opsional):**
- `1bulan` → Per bulan
- `3bulan` → Per 3 bulan (default)
- `4bulan` → Per 4 bulan
- `6bulan` → Per 6 bulan (semester)
- `12bulan` → Per tahun

**Cara Test di Postman:**
1. Method: **GET**
2. URL: `http://127.0.0.1:8000/api/test/pemetaan/timeline/3bulan`
3. Klik **Send**

**Test dengan Periode Berbeda:**
```
http://127.0.0.1:8000/api/test/pemetaan/timeline/1bulan
http://127.0.0.1:8000/api/test/pemetaan/timeline/6bulan
http://127.0.0.1:8000/api/test/pemetaan/timeline/12bulan
```

**Response yang Diharapkan:**
```json
{
    "success": true,
    "message": "Timeline data berhasil diambil",
    "data": {
        "selected_periode": "3bulan",
        "periode_label": "Per 3 Bulan (Triwulan)",
        "total_periods": 20,
        "timeline": [
            {
                "period": 1,
                "label": "Triwulan 1 - 2025",
                "start_date": "2025-12-23",
                "end_date": "2026-03-23",
                "count": 12,
                "is_urgent": true,
                "programs": [
                    {
                        "id": 1,
                        "name": "Teknik Informatika",
                        "university": "Universitas Indonesia",
                        "degree_level": "S1",
                        "peringkat": "A",
                        "tanggal_kedaluwarsa": "2026-01-15",
                        "status": "Aktif"
                    }
                ]
            }
        ]
    },
    "timestamp": "2025-12-23T12:34:56+07:00"
}
```

**Interpretasi:**
- 🔥 `is_urgent: true` → Periode mendesak (2 periode pertama)
- 📅 Timeline 5 tahun ke depan
- 📊 Jumlah prodi per periode
- 📝 Detail program studi yang kedaluwarsa di periode tersebut

---

### 3️⃣ **GET Calendar Data (12 Bulan)**

**Endpoint:** `GET http://127.0.0.1:8000/api/test/pemetaan/calendar`

**Cara Test di Postman:**
1. Method: **GET**
2. URL: `http://127.0.0.1:8000/api/test/pemetaan/calendar`
3. Klik **Send**

**Response yang Diharapkan:**
```json
{
    "success": true,
    "message": "Calendar data berhasil diambil (12 bulan ke depan)",
    "data": {
        "total_months": 12,
        "calendar": [
            {
                "month": "Desember 2025",
                "month_num": 12,
                "year": 2025,
                "count": 12,
                "programs": [
                    {
                        "id": 1,
                        "name": "Teknik Informatika",
                        "university": "Universitas Indonesia",
                        "degree_level": "S1",
                        "peringkat": "A",
                        "tanggal_kedaluwarsa": "2025-12-31",
                        "status": "Aktif"
                    }
                ]
            },
            {
                "month": "Januari 2026",
                "month_num": 1,
                "year": 2026,
                "count": 14,
                "programs": [...]
            }
        ]
    },
    "timestamp": "2025-12-23T12:34:56+07:00"
}
```

**Interpretasi:**
- 📆 Data 12 bulan mulai dari bulan sekarang
- 📊 Jumlah prodi yang kedaluwarsa per bulan
- 📝 Detail program studi per bulan

---

### 4️⃣ **GET Programs dengan Filter**

**Endpoint:** `GET http://127.0.0.1:8000/api/test/pemetaan/programs`

**Query Parameters (semua opsional):**
| Parameter | Contoh | Deskripsi |
|-----------|--------|-----------|
| `status` | `Aktif` | Status: Aktif, Kedaluwarsa, Belum Terakreditasi |
| `peringkat` | `A` | Peringkat: A, B, C, Unggul |
| `university_id` | `1` | ID Universitas |
| `degree_level_id` | `5` | ID Jenjang (1=D3, 5=S1, dll) |
| `search` | `informatika` | Search nama program studi |
| `sort_by` | `name` | Sort by: name, tanggal_kedaluwarsa |
| `sort_order` | `asc` | asc atau desc |
| `limit` | `10` | Jumlah data (default 10) |

**Contoh Test di Postman:**

**1. Filter Status Aktif, Limit 5:**
```
http://127.0.0.1:8000/api/test/pemetaan/programs?status=Aktif&limit=5
```

**2. Filter Peringkat A:**
```
http://127.0.0.1:8000/api/test/pemetaan/programs?peringkat=A&limit=20
```

**3. Search "Informatika":**
```
http://127.0.0.1:8000/api/test/pemetaan/programs?search=informatika&limit=10
```

**4. Filter Kedaluwarsa, Sort by Name:**
```
http://127.0.0.1:8000/api/test/pemetaan/programs?status=Kedaluwarsa&sort_by=name&sort_order=asc
```

**5. Kombinasi Multiple Filter:**
```
http://127.0.0.1:8000/api/test/pemetaan/programs?status=Aktif&peringkat=B&university_id=1&limit=15
```

**Cara Test di Postman:**
1. Method: **GET**
2. URL: `http://127.0.0.1:8000/api/test/pemetaan/programs`
3. Klik tab **Params**
4. Tambahkan parameter:
   - Key: `status` | Value: `Aktif`
   - Key: `limit` | Value: `5`
5. Klik **Send**

**Response yang Diharapkan:**
```json
{
    "success": true,
    "message": "Program studi berhasil diambil",
    "data": {
        "total": 5,
        "limit": 5,
        "filters_applied": {
            "status": "Aktif",
            "peringkat": null,
            "university_id": null,
            "degree_level_id": null,
            "search": null,
            "sort_by": "tanggal_kedaluwarsa",
            "sort_order": "asc"
        },
        "programs": [
            {
                "id": 1,
                "name": "Teknik Informatika",
                "code": "N/A",
                "university": {
                    "id": 1,
                    "name": "Universitas Indonesia",
                    "code": "001"
                },
                "degree_level": {
                    "id": 5,
                    "name": "Sarjana",
                    "code": "S1"
                },
                "category": {
                    "id": 1,
                    "name": "Akreditasi",
                    "code": "AK"
                },
                "bentuk_pt": "Universitas",
                "email": "ti@ui.ac.id",
                "akreditasi": {
                    "peringkat": "A",
                    "tanggal_kedaluwarsa": "2026-03-15",
                    "status": "Aktif",
                    "hari_tersisa": 82
                }
            }
        ]
    },
    "timestamp": "2025-12-23T12:34:56+07:00"
}
```

**Interpretasi:**
- 🔍 `filters_applied` → Menunjukkan filter yang digunakan
- 📊 `total` → Jumlah hasil query
- 📝 `programs` → Array detail program studi
- ⏰ `hari_tersisa` → Sisa hari sebelum kedaluwarsa (negatif = sudah kedaluwarsa)

---

## 🎯 Tips Testing di Postman

### 1. **Buat Environment**
Environment > Add > Nama: `DAISY Local`
- Variable: `base_url`
- Initial Value: `http://127.0.0.1:8000`
- Current Value: `http://127.0.0.1:8000`

Gunakan: `{{base_url}}/api/test/pemetaan/stats`

### 2. **Save Request ke Collection**
- Setelah test berhasil, klik **Save**
- Pilih collection yang sudah dibuat
- Beri nama descriptive: "Get Stats", "Get Timeline 3 Bulan", dll

### 3. **Buat Test Script (Opsional)**
Di tab **Tests**, tambahkan:
```javascript
// Test status code
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

// Test response time < 500ms
pm.test("Response time is less than 500ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(500);
});

// Test success flag
pm.test("Success is true", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.eql(true);
});

// Test data exists
pm.test("Data exists", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.exist;
});
```

### 4. **Export Collection**
- Klik Collection > ... > Export
- Pilih Collection v2.1
- Share dengan tim!

---

## 🐛 Troubleshooting

### Error: "Route not found"
```bash
php artisan route:clear
php artisan route:cache
```

### Error: "Server not running"
```bash
php artisan serve
```

### Error: "Column not found: status_kedaluwarsa"
```bash
php artisan migrate
```

### Response 500 Internal Server Error
- Cek `storage/logs/laravel.log`
- Pastikan database connection benar di `.env`

---

## 📊 Rangkuman Perubahan

| Aspek | Sebelum | Sesudah |
|-------|---------|---------|
| **Auth Required** | ✅ Ya (login wajib) | ❌ Tidak (testing mode) |
| **Response Type** | HTML View | JSON API |
| **Endpoint Prefix** | `/pemetaan` | `/api/test/pemetaan` |
| **Route Order** | ❌ Salah (dinamis duluan) | ✅ Benar (spesifik dulu) |
| **Carbon Bug** | ❌ Ada (no copy) | ✅ Fixed (with copy) |
| **Query Statistik** | ❌ Bug SQL binding | ✅ Fixed (CASE WHEN) |
| **Testing Tools** | ❌ Tidak ada | ✅ 4 endpoint testing |

---

## 🎓 Kesimpulan

✅ **Route testing sudah dibuat dan bisa diakses tanpa login**
✅ **4 endpoint tersedia untuk testing berbagai aspek pemetaan**
✅ **Response format JSON standar dengan success flag dan timestamp**
✅ **Bug di query statistik dan Carbon date sudah diperbaiki**
✅ **Urutan route sudah diperbaiki untuk mencegah conflict**

**Happy Testing!** 🚀
