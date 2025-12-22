# Production Deployment Checklist - DAISY

## ✅ Pre-Deployment Checklist

### 1. Environment Configuration
- [ ] Copy `.env.example` to `.env`
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate `APP_KEY` dengan `php artisan key:generate`
- [ ] Update `APP_URL` dengan domain production
- [ ] Configure database credentials
- [ ] Set `LOG_LEVEL=error`
- [ ] Configure mail settings (SMTP/SendGrid)

### 2. Database
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Run seeders jika diperlukan: `php artisan db:seed --force`
- [ ] Backup database sebelum deploy
- [ ] Verify bentuk_pt data sudah terisi (800 records)
- [ ] Test database connections

### 3. Security
- [ ] Update all dependencies: `composer update`
- [ ] Audit packages: `composer audit`
- [ ] Set proper file permissions (755 for folders, 644 for files)
- [ ] Set storage and bootstrap/cache to 775
- [ ] Configure CORS properly
- [ ] Enable HTTPS/SSL certificate
- [ ] Update CSRF token settings
- [ ] Configure rate limiting
- [ ] Review authentication middleware

### 4. Performance
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Enable OPcache in php.ini
- [ ] Optimize composer: `composer install --optimize-autoloader --no-dev`
- [ ] Compile assets: `npm run build`
- [ ] Configure queue workers
- [ ] Set up cron for scheduled tasks

### 5. Testing
- [ ] Test all CRUD operations (StudyProgram, ElemenStandar, Indikator, BobotPenilaian)
- [ ] Test DataTables server-side pagination
- [ ] Test search functionality
- [ ] Test user authentication & authorization
- [ ] Test file uploads
- [ ] Test form validations
- [ ] Test error pages (404, 500)
- [ ] Load testing dengan 800+ records

### 6. Monitoring & Logging
- [ ] Configure error logging
- [ ] Set up application monitoring
- [ ] Configure log rotation
- [ ] Test error reporting
- [ ] Set up backup schedule

### 7. Documentation
- [ ] Update README.md dengan production setup
- [ ] Document API endpoints (jika ada)
- [ ] Document deployment process
- [ ] Create user manual
- [ ] Document maintenance procedures

## ✅ Cleanup Done

### File Debugging Dipindahkan ke `debug_files/`
- ✅ analisis_akreditasi.php
- ✅ analisis_prodi_kadaluarsa.php
- ✅ cek_5_data.php
- ✅ check_admin.php
- ✅ check_data.php
- ✅ check_missing_data.php
- ✅ check_null_bentuk_pt.php
- ✅ check_pernyataan.php
- ✅ check_unhas.php
- ✅ count_elements.php
- ✅ debug_calculation.php
- ✅ list_all_elements.php
- ✅ test_calculation.php
- ✅ update_bentuk_pt.php
- ✅ validate_refactoring.php
- ✅ verify_bentuk_pt.php
- ✅ hasil_akreditasi*.txt (3 files)
- ✅ BOBOT_PENILAIAN_README.md
- ✅ REFACTORING_SUMMARY.md

### Code Cleanup
- ✅ Removed `console.log()` from production views
- ✅ Cleaned up debug comments
- ✅ Updated .env.example for production
- ✅ No dd(), dump(), var_dump() in controllers

## 🚀 Deployment Commands

```bash
# 1. Clone repository
git clone <repository-url>
cd daisy

# 2. Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# 3. Environment setup
cp .env.example .env
php artisan key:generate
# Edit .env with production values

# 4. Database setup
php artisan migrate --force
php artisan db:seed --force

# 5. Optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 7. Setup supervisor for queues (optional)
php artisan queue:work --daemon
```

## 🔒 Security Headers (Add to .htaccess or nginx config)

```apache
# Apache
Header set X-Frame-Options "SAMEORIGIN"
Header set X-Content-Type-Options "nosniff"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"
```

```nginx
# Nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

## 📊 Production Features Status

### ✅ Implemented & Tested
- [x] Study Program CRUD with DataTables
- [x] Elemen Standar CRUD with DataTables
- [x] Indikator CRUD with DataTables
- [x] Bobot Penilaian CRUD with DataTables
- [x] Bentuk PT auto-detection (800 records)
- [x] Server-side pagination
- [x] Search & filter functionality
- [x] Indonesian language support
- [x] Authentication & Authorization
- [x] Form validation

### 📝 Notes
- Debug files tersimpan di `debug_files/` untuk keperluan development
- File debug tidak di-commit ke git (sudah ada .gitignore)
- DataTables menggunakan CDN untuk production
- Database seeder lengkap dengan 800 program studi

## 🆘 Troubleshooting

### Jika terjadi error 500:
1. Check `storage/logs/laravel.log`
2. Verify file permissions
3. Clear all caches: `php artisan optimize:clear`
4. Check database connection

### Jika DataTables tidak load:
1. Check CDN connectivity
2. Verify route permissions
3. Check browser console for JS errors
4. Test Ajax endpoints

### Performance issues:
1. Enable OPcache
2. Use database indexing
3. Configure queue workers
4. Enable response caching
