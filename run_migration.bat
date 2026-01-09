@echo off
echo Menjalankan migration untuk menambahkan kolom akreditasi...
echo.
php artisan migrate
echo.
echo Migration selesai!
echo Silakan restart server dengan: php artisan serve
pause
