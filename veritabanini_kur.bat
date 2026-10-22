@echo off
chcp 65001 >nul
title PazarYeri - Veritabani Kurulumu (Hafta 1)
cls

echo ======================================================================
echo    PAZARYERI E-TICARET PROJESI - VERITABANI VE SEEDER KURULUMU
echo ======================================================================
echo.

set PHP_BIN=C:\Users\gulte\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe
if not exist "%PHP_BIN%" (
    set PHP_BIN=php
)

echo [ADIM 1/3] PHP Kontrol Ediliyor...
"%PHP_BIN%" -v | findstr "PHP 8."
if %errorlevel% neq 0 (
    echo [HATA] PHP 8.3 bulunamadi!
    pause
    exit /b 1
)
echo PHP 8.3 hazir.
echo.

echo [ADIM 2/3] MySQL Baglantisi Kontrol Ediliyor (Port 3306)...
"%PHP_BIN%" -r "try { $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '', [PDO::ATTR_TIMEOUT => 3, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); echo '[OK] MySQL servisi calisiyor.' . PHP_EOL; $pdo->exec('CREATE DATABASE IF NOT EXISTS \`pazaryeri\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'); echo '[OK] pazaryeri veritabani hazir.' . PHP_EOL; } catch (Exception $e) { echo '[HATA] MySQL baglantisi basarisiz!' . PHP_EOL; echo 'Hata detayi: ' . $e->getMessage() . PHP_EOL; exit(1); }"

if %errorlevel% neq 0 (
    echo.
    echo ======================================================================
    echo  DIKKAT: MySQL'e baglanilamadi!
    echo  Lutfen XAMPP Control Panel'i acip MySQL'in yanindaki 'Start' tusuna basin.
    echo  MySQL acildiktan sonra bu pencereyi kapatip bat dosyasini tekrar calistirin.
    echo ======================================================================
    echo.
    pause
    exit /b 1
)
echo.

echo [ADIM 3/3] 20 Tablonun Olusturulmasi ve Seeder Verilerinin Yuklenmesi...
echo Komut: php artisan migrate:fresh --seed --force
echo.
"%PHP_BIN%" artisan migrate:fresh --seed --force

if %errorlevel% neq 0 (
    echo.
    echo [HATA] Migration veya Seeder yuklenirken bir sorun olustu.
    pause
    exit /b 1
)

echo.
echo ======================================================================
echo   🎉 TEBRIKLER! 1. HAFTA KABUL KRITERLERI BASARIYLA TAMAMLANDI!
echo ======================================================================
echo.
echo 20 Tablo, 1 Admin, 5 Satici, 20 Musteri, 15 Kategori ve 100 Urun yuklendi.
echo.
echo Test Giris Hesaplari (Sifre: password123):
echo  * Admin:   admin@pazaryeri.com
echo  * Satici:  satici1@pazaryeri.com
echo  * Musteri: musteri1@pazaryeri.com
echo ======================================================================
echo.
echo Kapatmak icin herhangi bir tusa basin...
pause >nul
