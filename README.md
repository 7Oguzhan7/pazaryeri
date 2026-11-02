# 🛒 PazarYeri - Çok Satıcılı E-Ticaret Platformu

Amazon ve Hepsiburada benzeri, çok satıcılı (multi-vendor) mimariye sahip modern bir e-ticaret platformudur. Proje Laravel 13, PHP 8.3+ ve Livewire kullanılarak geliştirilmektedir. Yeni kurulumlarda SQLite kullanılır; istenirse `.env` üzerinden MySQL veya PostgreSQL yapılandırılabilir.

---

## 📌 1. Hafta Teslimat Özeti
* **ER Diyagramı:** 20 tabloluk veritabanı modeli tasarlandı ve `docs/eticaret.drawio.pdf` olarak hazırlandı.
* **Migration & Modeller:** 20 tablonun migration'ları (`database/migrations/`) ve Eloquent modelleri (`app/Models/`) eksiksiz kodlandı.
* **Seeder (Kabul Kriteri):** 1 admin, 5 satıcı, 20 müşteri, 15 kategori ve 100 ürün `database/seeders/DatabaseSeeder.php` içerisine eklendi.
* **Wireframe:** Ana sayfa arayüz mimarisi [docs/anasayfa_wireframe.html](docs/anasayfa_wireframe.html) olarak Figma standardında tasarlandı.
* **Sunum Notları:** 10 dakikalık sunum kılavuzu [docs/1_hafta_sunum_notlari.md](docs/1_hafta_sunum_notlari.md) dosyasına eklendi.

---

## 🚀 Kurulum Adımları (Setup Guide)

### Gereksinimler:
* PHP 8.3+ (WinGet veya sistem PHP'si)
* Composer 2.x
* PHP PDO SQLite eklentisi
* Node.js LTS

### Adım Adım Kurulum:

1. **Depoyu Klonlayın ve geliştirme dalına geçin:**
   ```bash
   git clone https://github.com/7Oguzhan7/pazaryeri.git
   cd pazaryeri
   git checkout develop
   ```

2. **Bağımlılıkları Yükleyin:**
   ```bash
   composer install
   npm install
   ```

3. **Ortam Dosyasını Hazırlayın:**
   `.env.example` SQLite ile gelir. `.env` dosyasını oluşturup uygulama anahtarını üretin:
   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

4. **Veritabanını ve Örnek Verileri Tek Seferde Yükleyin (Kabul Kriteri):**
   * **Windows:** Proje ana dizinindeki `veritabanini_kur.bat` veya `veritabanini_kur.ps1` dosyasını çalıştırın.
   * **Veya komut satırından:**
     ```bash
     php artisan migrate:fresh --seed
     ```
   `migrate:fresh` mevcut tüm tabloları siler. Dolu bir veritabanını sıfırlamak istemiyorsanız `php artisan migrate --seed` kullanın.

5. **Uygulamayı Başlatın:**
   ```bash
   php artisan serve
   ```
   Tarayıcınızda `http://127.0.0.1:8000` adresini açın.

---

## 🔑 Demo & Test Kullanıcı Hesapları

Tüm test hesaplarının şifresi varsayılan olarak **`password123`** olarak ayarlanmıştır:

| Rol | E-Posta | Şifre | Açıklama |
| :--- | :--- | :--- | :--- |
| **Yönetici (Admin)** | `admin@pazaryeri.com` | `password123` | Tüm platform, satıcı onayları ve komisyon yönetimi |
| **Satıcı (Seller 1)** | `satici1@pazaryeri.com` | `password123` | Teknoloji Dünyası A.Ş. mağazası |
| **Satıcı (Seller 2)** | `satici2@pazaryeri.com` | `password123` | Moda & Trend Mağazacılık |
| **Müşteri (Customer 1)**| `musteri1@pazaryeri.com` | `password123` | Kayıtlı adresli örnek müşteri |
| **Müşteri (Customer 2-20)**| `musteri2@pazaryeri.com`... | `password123` | Test siparişleri ve sepet senaryoları için |

---

## 📅 2. Hafta: Kimlik Doğrulama, Roller ve Kategoriler

* Kayıt olan hesaplara `customer` rolü otomatik atanır; e-posta doğrulama bağlantısı gönderilir.
* Roller `admin`, `seller` ve `customer` adlarıyla tutulur. Eski Türkçe rol kayıtları `RoleSeeder` tarafından güvenle bu adlara taşınır.
* `/admin` ve `/admin/categories` yalnızca yöneticiye; `/satici` ise yalnızca onaylanmış satıcıya açıktır.
* Kategori yönetimi üst kategori, slug, sıralama ve komisyon oranı alanlarını destekler. Ürünü veya alt kategorisi olan kayıtlar silinemez.
* Ana sayfa, giriş/kayıt ve oturum açmış sayfalar ortak mağaza başlığını ve alt bilgisini kullanır; kategori menüsü veritabanından yüklenir.

### E-posta doğrulamayı Mailpit ile deneme

Geliştirme ortamı varsayılan olarak e-postaları uygulama loguna yazar. Gerçek SMTP akışını yerel olarak görmek için [Mailpit'i kurun](https://mailpit.axllent.org/docs/install/) ve ayrı bir terminalde `mailpit` komutunu çalıştırın. Ardından `.env` içinde şu ayarları yapın:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_SCHEME=null
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

`php artisan config:clear` komutunu çalıştırıp uygulamada yeni bir hesap oluşturun. Gelen doğrulama e-postasını `http://127.0.0.1:8025` adresindeki Mailpit arayüzünde açabilirsiniz. Otomatik testler dış SMTP servisine bağlanmadan doğrulama bildiriminin gönderilmesini sınar.

---

## 🏗️ Mimari ve Tasarım Kuralları
1. **Fiyat Hassasiyeti:** Para alanları asla `float` olamaz; `decimal(10,2)` kullanılır.
2. **Alt Sipariş Mantığı:** Bir sepette 3 farklı satıcının ürünü varsa, 1 `orders` ve her satıcı için ayrı 1 `sub_orders` oluşturulur.
3. **Stok Güvenliği:** Stoklar ana üründe değil `product_variants` tablosundadır ve sipariş onayında `lockForUpdate()` ile rezerve edilir.
4. **Git Kuralları:** Doğrudan `main` dalına push yapılmaz; geliştirmeler `develop` dalı üzerinden feature branch'ler ile yürütülür.
