@echo off
chcp 65001 >nul
title PazarYeri - Veritabani Kurulumu (Hafta 1)
cd /d "%~dp0"

echo ======================================================================
echo    PAZARYERI E-TICARET PROJESI - VERITABANI VE SEEDER KURULUMU
echo ======================================================================
echo.

where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [HATA] PHP bulunamadi. PHP'yi PATH'e ekleyip tekrar deneyin.
    pause
    exit /b 1
)

if not exist .env copy .env.example .env
if not exist database\database.sqlite type nul > database\database.sqlite

echo SQLite veritabanina migration ve ornek veriler yukleniyor...
echo Komut: php artisan migrate --seed --force
php artisan migrate --seed --force

if %errorlevel% neq 0 (
    echo [HATA] Migration veya seeder yuklenirken bir sorun olustu.
    pause
    exit /b 1
)

echo.
echo ======================================================================
echo   TEBRIKLER! 1. HAFTA VERITABANI KURULUMU TAMAMLANDI!
echo ======================================================================
echo Admin, 5 Satici, 20 Musteri, 15 Kategori ve 100 Urun hazir.
echo Test hesaplarinin sifresi: password123
echo ======================================================================
pause
