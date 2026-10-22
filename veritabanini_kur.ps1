Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host "   PAZARYERI E-TICARET PROJESI - VERITABANI VE SEEDER KURULUMU" -ForegroundColor Cyan
Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host ""

$phpBin = "C:\Users\gulte\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
if (-not (Test-Path $phpBin)) {
    $phpBin = "php"
}

Write-Host "[1/3] PHP 8.3 Kontrol Ediliyor..." -ForegroundColor Yellow
& $phpBin -v
Write-Host ""

Write-Host "[2/3] MySQL Veritabani 'pazaryeri' hazirlaniyor..." -ForegroundColor Yellow
& $phpBin -r "try { `$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', ''); `$pdo->exec('CREATE DATABASE IF NOT EXISTS \`pazaryeri\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'); echo 'Veritabani pazaryeri hazir.' . PHP_EOL; } catch (Exception `$e) { echo 'Baglanti hatasi: ' . `$e->getMessage() . PHP_EOL; exit(1); }"

if ($LASTEXITCODE -ne 0) {
    Write-Host "[HATA] MySQL'e baglanilamadi! Lutfen XAMPP'tan MySQL'i baslatin." -ForegroundColor Red
    Read-Host "Devam etmek icin Enter'a basin..."
    exit $LASTEXITCODE
}
Write-Host ""

Write-Host "[3/3] 20 Tablonun Migration'lari ve Seeder'lar yukleniyor..." -ForegroundColor Yellow
Write-Host "Komut: php artisan migrate:fresh --seed" -ForegroundColor DarkGray
Write-Host ""

& $phpBin artisan migrate:fresh --seed

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "======================================================================" -ForegroundColor Green
    Write-Host "   TEBRIKLER! 8. VE 9. ADIM BASARIYLA TAMAMLANDI!" -ForegroundColor Green
    Write-Host "======================================================================" -ForegroundColor Green
    Write-Host "Test Hesaplari:" -ForegroundColor White
    Write-Host "  * Admin: admin@pazaryeri.com       / Sifre: password123" -ForegroundColor White
    Write-Host "  * Satici: satici1@pazaryeri.com    / Sifre: password123" -ForegroundColor White
    Write-Host "  * Musteri: musteri1@pazaryeri.com  / Sifre: password123" -ForegroundColor White
    Write-Host ""
    Write-Host "20 Tablo, 1 Admin, 5 Satici, 20 Musteri, 15 Kategori ve 100 Urun hazir." -ForegroundColor Cyan
} else {
    Write-Host "[HATA] Bir sorun olustu." -ForegroundColor Red
}

Read-Host "Kapatmak icin Enter tusuna basin..."
