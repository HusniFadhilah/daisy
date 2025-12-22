# DAISY - Sistem Manajemen Akreditasi Perguruan Tinggi

[![Laravel](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

## 📖 Tentang DAISY

DAISY (Digital Accreditation Information System) adalah sistem informasi manajemen akreditasi perguruan tinggi yang dirancang untuk membantu institusi pendidikan dalam mengelola proses akreditasi program studi secara efisien dan terstruktur.

## ✨ Fitur Utama

### 🎓 Master Data
- **Program Studi** - Manajemen 800+ data program studi dengan informasi akreditasi lengkap
- **Universitas** - Database perguruan tinggi di Indonesia
- **Degree Level** - Jenjang pendidikan (S1, S2, S3, D3, D4)
- **Bentuk PT** - Klasifikasi perguruan tinggi (Universitas, Institut, Sekolah Tinggi, Politeknik, Akademi)

### 📊 Manajemen Indikator
- **Kriteria** - Kriteria akreditasi sesuai standar
- **Elemen Standar** - Elemen standar akreditasi per kriteria
- **Indikator** - Indikator penilaian kualitatif dan kuantitatif
- **Jenis Indikator** - Kategorisasi jenis indikator

### ⚖️ Sistem Penilaian
- **Bobot Penilaian** - Pengaturan bobot penilaian per elemen standar dan kategori program studi
- **Perhitungan Nilai Berbobot** - Kalkulasi otomatis nilai akhir akreditasi
- **Asesmen Lapangan** - Manajemen proses asesmen lapangan
- **Asesmen Kecukupan** - Penilaian kelengkapan dokumen

### 👥 User Management
- **Multi-role Support** - Admin, Asesor, User
- **Authentication** - Login, logout, forgot password
- **Authorization** - Role-based access control
- **Profile Management** - Manajemen profil pengguna

### 📈 Reporting & Analytics
- **Heatmap Matrix** - Visualisasi peta nilai akreditasi
- **Dashboard Analytics** - Statistik dan grafik performa akreditasi
- **Export Data** - Export data ke Excel/PDF

### 🔍 Advanced Features
- **DataTables Server-side** - Pagination, search, dan sort untuk dataset besar
- **Real-time Search** - Pencarian data secara real-time
- **Responsive Design** - Tampilan optimal di desktop, tablet, dan mobile
- **Indonesian Language** - Interface dalam Bahasa Indonesia

## 🛠️ Teknologi

- **Backend**: Laravel 10.x
- **Frontend**: Bootstrap 5, jQuery
- **Database**: MySQL 8.0+
- **DataTables**: Yajra DataTables v12 (server-side processing)
- **Icons**: Bootstrap Icons
- **Charts**: Chart.js

## 📋 Requirements

- PHP >= 8.1
- Composer
- MySQL >= 8.0 atau MariaDB >= 10.3
- Node.js >= 16.x
- NPM atau Yarn

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/yourusername/daisy.git
cd daisy
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database

Edit file `.env` dan sesuaikan dengan konfigurasi database Anda:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=daisy_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations & Seeders

```bash
# Run migrations
php artisan migrate

# Run seeders (includes 800 study programs)
php artisan db:seed
```

### 6. Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Start Development Server

```bash
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

## 👤 Default User

Setelah seeding, Anda dapat login dengan:

- **Email**: admin@daisy.com
- **Password**: password

⚠️ **Penting**: Segera ubah kredensial default setelah login pertama!

## 📁 Struktur Database

### Tables
- `users` - Data pengguna sistem
- `roles` - Role pengguna (Admin, Asesor, User)
- `universities` - Data perguruan tinggi
- `degree_levels` - Jenjang pendidikan
- `study_program_categories` - Kategori program studi
- `study_programs` - Data program studi dengan akreditasi
- `kriteria` - Kriteria akreditasi
- `elemen_standar` - Elemen standar per kriteria
- `indikator` - Indikator penilaian
- `jenis_indikator` - Jenis indikator (Kualitatif/Kuantitatif)
- `bobot_penilaian` - Bobot penilaian per elemen
- `asesmens` - Data asesmen
- `asesmen_kecukupans` - Asesmen kecukupan
- `asesmen_lapangans` - Asesmen lapangan
- `pernyataans` - Pernyataan standar

## 🎯 Usage

### CRUD Operations

Semua modul CRUD menggunakan DataTables server-side processing untuk performa optimal:

1. **Search**: Gunakan search box untuk mencari data secara real-time
2. **Sort**: Klik header kolom untuk sorting
3. **Pagination**: Navigasi halaman di bagian bawah tabel
4. **Length Menu**: Pilih jumlah data per halaman (10, 25, 50, 100, Semua)

### Bobot Penilaian

1. Akses menu **Bobot Penilaian**
2. Tambah bobot untuk setiap elemen standar per kategori program studi
3. Gunakan filter untuk melihat bobot spesifik
4. Hitung nilai berbobot dengan memilih asesmen dan kategori

### Asesmen

1. Buat asesmen baru dari menu **Asesmen**
2. Isi data asesmen kecukupan
3. Lakukan asesmen lapangan
4. Lihat hasil dalam bentuk heatmap matrix

## 🔒 Security

- CSRF Protection enabled
- XSS Protection
- SQL Injection prevention via Eloquent ORM
- Password hashing dengan bcrypt
- Rate limiting pada authentication
- Input validation dan sanitization

## 📊 Performance

- **Server-side DataTables**: Efisien untuk dataset 800+ records
- **Query Optimization**: Eager loading untuk relasi
- **Caching**: Config, route, dan view caching
- **Asset Optimization**: Minified CSS dan JS

## 🧪 Testing

```bash
# Run PHPUnit tests
php artisan test

# Run specific test
php artisan test --filter=StudyProgramTest
```

## 📦 Production Deployment

Lihat [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md) untuk panduan lengkap deployment production.

### Quick Commands

```bash
# Optimize for production
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🤝 Contributing

1. Fork repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Laravel Framework
- Yajra DataTables
- Bootstrap Team
- Chart.js Team

## 📧 Contact

Project Link: [https://github.com/yourusername/daisy](https://github.com/yourusername/daisy)

---

Made with ❤️ for better accreditation management
