@echo off
setlocal
cd /d "%~dp0"
title TIKLAAL - SITEYI BASLAT
set "URL=http://127.0.0.1:8000"

where php >nul 2>nul
if errorlevel 1 (
    echo [HATA] PHP bulunamadi. PHP 8.3 kurulu ve PATH'e ekli olmali.
    pause
    exit /b 1
)

if not exist ".env" (
    echo [HATA] .env dosyasi bulunamadi. Once proje kurulum adimlarini tamamlayin.
    pause
    exit /b 1
)

if not exist "vendor\autoload.php" (
    echo [HATA] PHP bagimliliklari bulunamadi. Proje klasorunde composer install calistirin.
    pause
    exit /b 1
)

echo TIKLAAL baslatiliyor. Lutfen bekleyin...
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
    "$url = '%URL%';" ^
    "$ready = $false;" ^
    "try { $response = Invoke-WebRequest -Uri $url -TimeoutSec 2 -UseBasicParsing; $ready = ($response.StatusCode -ge 200 -and $response.StatusCode -lt 400 -and $response.Content -match 'csrf-token') } catch { };" ^
    "if (-not $ready) { Start-Process -FilePath 'cmd.exe' -ArgumentList '/k','php artisan serve --host=127.0.0.1 --port=8000' -WorkingDirectory '%~dp0' };" ^
    "$lastError = 'Sunucudan yanit alinamadi.';" ^
    "for ($attempt = 0; $attempt -lt 60; $attempt++) { Start-Sleep -Seconds 1; try { $response = Invoke-WebRequest -Uri $url -TimeoutSec 2 -UseBasicParsing; if ($response.StatusCode -ge 200 -and $response.StatusCode -lt 400 -and $response.Content -match 'csrf-token') { Start-Process $url; Write-Host ('Site hazir: ' + $url) -ForegroundColor Green; exit 0 } else { $lastError = '8000 portunda beklenen Laravel sayfasi bulunamadi.' } } catch { $lastError = $_.Exception.Message } };" ^
    "Write-Error ('Site 60 saniye icinde acilamadi. ' + $lastError); exit 1"

if errorlevel 1 (
    echo.
    echo [HATA] Site baslatilamadi. Laravel sunucu penceresindeki hata mesajini kontrol edin.
    pause
    exit /b 1
)

echo.
echo Site tarayicida acildi. Sunucu penceresini kapatmayin.
echo Siteyi durdurmak icin Laravel sunucu penceresinde Ctrl+C basin.
pause
endlocal
