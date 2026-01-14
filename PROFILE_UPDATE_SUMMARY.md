# Laporan Perubahan: Fitur Profil User (Terutama Admin Prodi)

## Tanggal: 14 Januari 2026

## 📋 Ringkasan Perubahan

Halaman profil user (`/profile/index`) telah dilengkapi dengan field tambahan, terutama untuk user dengan role **admin_prodi**. Perubahan mencakup penambahan field profil, relasi database, dan integrasi logo universitas.

---

## 🗄️ Perubahan Database

### 1. Tabel `users` - Penambahan Kolom Profil

**Migration**: `2026_01_14_215857_add_profile_fields_to_users_table.php`

**Kolom Baru**:
- `phone` (varchar 20, nullable) - Nomor telepon user
- `address` (text, nullable) - Alamat lengkap
- `institution` (varchar 255, nullable) - Nama institusi (fallback jika tidak punya relasi university)
- `id_university` (foreign key, nullable) - Relasi ke tabel universities
- `id_study_program` (foreign key, nullable) - Relasi ke tabel study_programs
- `position` (varchar 255, nullable) - Jabatan (contoh: Ketua Program Studi, Kaprodi)
- `avatar` (varchar 255, nullable) - Path foto profil user

**Foreign Keys**:
- `users.id_university` → `universities.id` (on delete: set null)
- `users.id_study_program` → `study_programs.id` (on delete: set null)

### 2. Tabel `universities` - Penambahan Logo

**Migration**: `2026_01_14_220624_add_logo_path_to_universities_table.php`

**Kolom Baru**:
- `logo_path` (varchar 255, nullable) - Path logo universitas

---

## 📁 Perubahan File

### 1. **Model: `app/Models/User.php`**

**Penambahan `$fillable`**:
```php
protected $fillable = [
    'name',
    'email',
    'password',
    'role',
    'roles',
    'role_selected',
    'is_multiple_role',
    'last_role_switch',
    'must_change_password',
    'phone',           // ✅ BARU
    'address',         // ✅ BARU
    'institution',     // ✅ BARU
    'id_university',   // ✅ BARU
    'id_study_program',// ✅ BARU
    'position',        // ✅ BARU
    'avatar',          // ✅ BARU
];
```

**Penambahan Relasi**:
```php
public function university()
{
    return $this->belongsTo(University::class, 'id_university');
}

public function studyProgram()
{
    return $this->belongsTo(StudyProgram::class, 'id_study_program');
}
```

### 2. **Model: `app/Models/University.php`**

**Penambahan `$fillable`**:
```php
protected $fillable = [
    'code',
    'name',
    'logo_path', // ✅ BARU
];
```

### 3. **Controller: `app/Http/Controllers/Profile/ProfileController.php`**

**Method `index()`** - Load relasi dan data dropdown:
```php
public function index()
{
    $user = Auth::user()->load(['university', 'studyProgram.degreeLevel', 'studyProgram.category']);
    
    // Load list untuk dropdown
    $universities = \App\Models\University::orderBy('name')->get();
    $studyPrograms = \App\Models\StudyProgram::with(['university', 'degreeLevel'])->orderBy('name')->get();
    
    return view('profile.index', compact('user', 'universities', 'studyPrograms'));
}
```

**Method `update()`** - Handle field baru:
```php
public function update(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'name' => 'required|min:3',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string',
        'institution' => 'nullable|string|max:255',
        'id_university' => 'nullable|exists:universities,id',
        'id_study_program' => 'nullable|exists:study_programs,id',
        'position' => 'nullable|string|max:255',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;
    $user->phone = $request->phone;
    $user->address = $request->address;
    $user->institution = $request->institution;
    $user->id_university = $request->id_university;
    $user->id_study_program = $request->id_study_program;
    $user->position = $request->position;

    $user->save();

    return back()->with('success', 'Profil berhasil diperbarui!');
}
```

**Method Baru `updateAvatar()`** - Upload foto profil:
```php
public function updateAvatar(Request $request)
{
    $request->validate([
        'avatar' => 'required|image|mimes:jpeg,jpg,png,gif|max:2048',
    ]);

    $user = Auth::user();

    // Delete old avatar if exists
    if ($user->avatar && \Storage::exists('public/' . $user->avatar)) {
        \Storage::delete('public/' . $user->avatar);
    }

    // Store new avatar
    $path = $request->file('avatar')->store('avatars', 'public');
    
    $user->avatar = $path;
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Avatar berhasil diupdate',
        'avatar_url' => asset('storage/' . $path),
    ]);
}
```

### 4. **View: `resources/views/profile/index.blade.php`**

**Perubahan di Kartu Avatar**:
- ✅ Menampilkan logo universitas untuk admin_prodi (jika ada)
- ✅ Menampilkan jabatan/position user
- ✅ Upload avatar dengan preview realtime

**Perubahan di Informasi Akun**:
- ✅ Menampilkan No. Telepon (jika ada)
- ✅ Menampilkan Universitas dengan relasi (jika ada)
- ✅ Menampilkan Program Studi + Jenjang (jika ada)
- ✅ Menampilkan Kategori Prodi (jika ada)

**Form Edit Profil - Field Baru**:
- ✅ No. Telepon (optional)
- ✅ Jabatan/Position (optional, contoh: Ketua Program Studi)
- ✅ Universitas (dropdown dengan relasi)
- ✅ Program Studi (dropdown dengan relasi + filter by university)
- ✅ Alamat (textarea)

**JavaScript Tambahan**:
- ✅ Filter dropdown Program Studi berdasarkan Universitas yang dipilih
- ✅ Upload avatar dengan AJAX

---

## 🎯 Fitur Khusus untuk Admin Prodi

### Tampilan Logo Universitas
Jika user adalah **admin_prodi** dan memiliki relasi `university` dengan `logo_path`, maka logo akan ditampilkan di atas avatar:

```blade
@if($user->role_selected === 'admin_prodi' && $user->university && $user->university->logo_path)
<div class="mb-3">
    <img src="{{ asset('storage/' . $user->university->logo_path) }}" 
         alt="Logo {{ $user->university->name }}" 
         style="max-height: 80px; max-width: 200px; object-fit: contain;">
</div>
@endif
```

### Informasi Lengkap Prodi
- Nama Program Studi
- Jenjang (S1/S2/D3/dst)
- Kategori Prodi (Sains & Teknologi, Kesehatan, dll)
- Universitas
- Logo Universitas

---

## 📊 Struktur Data Lengkap

### Tabel `users`
```
┌─────────────────────┬──────────────┬──────────┬──────────────────┐
│ Kolom               │ Tipe         │ Nullable │ Keterangan       │
├─────────────────────┼──────────────┼──────────┼──────────────────┤
│ id                  │ bigint       │ NO       │ PK               │
│ name                │ varchar(255) │ NO       │                  │
│ email               │ varchar(255) │ NO       │ Unique           │
│ phone               │ varchar(20)  │ YES      │ ✅ BARU          │
│ address             │ text         │ YES      │ ✅ BARU          │
│ institution         │ varchar(255) │ YES      │ ✅ BARU          │
│ id_university       │ bigint       │ YES      │ ✅ BARU (FK)     │
│ id_study_program    │ bigint       │ YES      │ ✅ BARU (FK)     │
│ position            │ varchar(255) │ YES      │ ✅ BARU          │
│ avatar              │ varchar(255) │ YES      │ ✅ BARU          │
│ role                │ enum         │ NO       │                  │
│ role_selected       │ enum         │ NO       │                  │
│ roles               │ json         │ YES      │                  │
│ password            │ varchar(255) │ NO       │                  │
│ ...                 │ ...          │ ...      │                  │
└─────────────────────┴──────────────┴──────────┴──────────────────┘
```

### Tabel `universities`
```
┌─────────────┬──────────────┬──────────┬──────────────┐
│ Kolom       │ Tipe         │ Nullable │ Keterangan   │
├─────────────┼──────────────┼──────────┼──────────────┤
│ id          │ bigint       │ NO       │ PK           │
│ code        │ varchar(255) │ NO       │              │
│ name        │ varchar(255) │ NO       │              │
│ logo_path   │ varchar(255) │ YES      │ ✅ BARU      │
│ created_at  │ timestamp    │ YES      │              │
│ updated_at  │ timestamp    │ YES      │              │
└─────────────┴──────────────┴──────────┴──────────────┘
```

---

## 🔄 Cara Penggunaan

### 1. Upload Logo Universitas (Admin)
Untuk menampilkan logo universitas di profil admin_prodi:

```php
// Di UniversityController atau seeder
$university = University::find(1);
$university->logo_path = 'universities/unhas-logo.png'; // Path di storage/app/public
$university->save();
```

### 2. Set Profile Admin Prodi
```php
$user = User::find(1);
$user->id_university = 1; // ID universitas
$user->id_study_program = 10; // ID program studi
$user->position = 'Ketua Program Studi';
$user->phone = '081234567890';
$user->address = 'Jl. Contoh No. 123, Makassar';
$user->save();
```

### 3. Upload Avatar
User bisa upload avatar langsung dari halaman profil dengan klik icon kamera.

---

## ✅ Testing Checklist

- [x] Migration berhasil dijalankan
- [x] Model User memiliki relasi university dan studyProgram
- [x] Model University memiliki field logo_path
- [x] Controller ProfileController load data university dan study_program
- [x] View menampilkan logo university untuk admin_prodi
- [x] Form profile memiliki dropdown university dan study_program
- [x] Dropdown study_program terfilter berdasarkan university yang dipilih
- [x] Upload avatar bekerja dengan baik
- [x] Data tersimpan ke database dengan benar

---

## 📝 Catatan Penting

1. **Logo Universitas**: Path logo harus disimpan di `storage/app/public/universities/`. Pastikan symbolic link sudah dibuat dengan `php artisan storage:link`

2. **Avatar User**: Tersimpan di `storage/app/public/avatars/`

3. **Validasi**: 
   - Avatar maksimal 2MB, format: jpeg, jpg, png, gif
   - Phone maksimal 20 karakter
   - Email harus unique

4. **Relasi Cascade**: 
   - Jika university dihapus, `id_university` pada user akan di-set `NULL`
   - Jika study_program dihapus, `id_study_program` pada user akan di-set `NULL`

5. **Backward Compatibility**: Semua field baru bersifat nullable, sehingga user lama yang belum melengkapi profil tetap bisa berfungsi normal.

---

## 🎨 Screenshot Fitur

### Informasi yang Ditampilkan:
- ✅ Logo Universitas (untuk admin_prodi)
- ✅ Avatar User (bisa diupload)
- ✅ Nama Lengkap
- ✅ Role & Jabatan
- ✅ Email & No. Telepon
- ✅ Universitas & Program Studi
- ✅ Kategori Prodi
- ✅ Alamat
- ✅ Tanggal Bergabung & Update

---

## 📌 Files Modified/Created

**Migrations**:
- ✅ `2026_01_14_215857_add_profile_fields_to_users_table.php`
- ✅ `2026_01_14_220624_add_logo_path_to_universities_table.php`

**Models**:
- ✅ `app/Models/User.php`
- ✅ `app/Models/University.php`

**Controllers**:
- ✅ `app/Http/Controllers/Profile/ProfileController.php`

**Views**:
- ✅ `resources/views/profile/index.blade.php`

**Documentation**:
- ✅ `PROFILE_UPDATE_SUMMARY.md` (this file)

---

**End of Report** 🎉
