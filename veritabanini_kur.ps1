Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host "   PAZARYERI E-TICARET PROJESI - VERITABANI VE SEEDER KURULUMU" -ForegroundColor Cyan
Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host ""

$phpBin = "C:\Users\gulte\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
if (-not (Test-Path $phpBin)) {
    $phpCommand = Get-Command php -ErrorAction SilentlyContinue
    if ($null -eq $phpCommand) {
        Write-Host "[HATA] PHP bulunamadi. PHP'yi PATH'e ekleyip tekrar deneyin." -ForegroundColor Red
        exit 1
    }

    $phpBin = $phpCommand.Source
}

Set-Location $PSScriptRoot

Write-Host "[1/2] PHP kontrol ediliyor..." -ForegroundColor Yellow
& $phpBin -v
Write-Host ""

if (-not (Test-Path '.env')) {
    Copy-Item '.env.example' '.env'
}

if (-not (Test-Path 'database\database.sqlite')) {
    New-Item -Path 'database\database.sqlite' -ItemType File | Out-Null
}

Write-Host "[2/2] SQLite veritabanina migration ve ornek veriler yukleniyor..." -ForegroundColor Yellow
Write-Host "Komut: php artisan migrate --seed --force" -ForegroundColor DarkGray
Write-Host ""

& $phpBin artisan migrate --seed --force

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "======================================================================" -ForegroundColor Green
    Write-Host "   TEBRIKLER! 1. HAFTA VERITABANI KURULUMU TAMAMLANDI!" -ForegroundColor Green
    Write-Host "======================================================================" -ForegroundColor Green
    Write-Host "Test Hesaplari:" -ForegroundColor White
    Write-Host "  * Admin: admin@pazaryeri.com       / Sifre: password123" -ForegroundColor White
    Write-Host "  * Satici: satici1@pazaryeri.com    / Sifre: password123" -ForegroundColor White
    Write-Host "  * Musteri: musteri1@pazaryeri.com  / Sifre: password123" -ForegroundColor White
    Write-Host ""
    Write-Host "1 Admin, 5 Satici, 20 Musteri, 15 Kategori ve 100 Urun hazir." -ForegroundColor Cyan
} else {
    Write-Host "[HATA] Bir sorun olustu." -ForegroundColor Red
}

Write-Host "Kurulum tamamlandi."
