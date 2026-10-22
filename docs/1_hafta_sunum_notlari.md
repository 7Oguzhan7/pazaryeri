# PazarYeri Çok Satıcılı E-Ticaret Platformu
## 1. Hafta Proje Sunumu ve Değerlendirme Notları (10 Dakika)

Bu kılavuz, 1. hafta teslimi ve hoca karşısında yapılacak 10 dakikalık sunum için adım adım konuşma planını, teknik savunma argümanlarını ve canlı demo senaryosunu içermektedir.

---

### ⏱️ Sunum Süre Planı (Toplam: 10 Dakika)

| Dakika | Bölüm | Konuşulacak Temel Başlıklar |
| :---: | :--- | :--- |
| **00:00 - 02:00** | **1. Giriş & Rol Dağılımı** | Projenin amacı, çok satıcılı mimari, tek kişilik ekipte profesyonel rol yönetimi. |
| **02:00 - 05:00** | **2. Veritabanı & ER Mimarisi** | 20 tablonun ilişkileri, kurallar (decimal para, enum, sub-order yapısı). |
| **05:00 - 07:30** | **3. Canlı Demo (Kabul Kriteri)** | `php artisan migrate:fresh --seed` komutunu tek seferde çalıştırıp veritabanını göstermek. |
| **07:30 - 09:00** | **4. UI/UX & Wireframe** | Hazırlanan Ana Sayfa tel kafes (wireframe) şeması ve bileşen kurgusu. |
| **09:00 - 10:00** | **5. Git Akışı & 2. Hafta Planı**| GitHub Kanban panosu, branch stratejisi (`main`/`develop`) ve soru-cevap. |

---

### 🎤 Bölüm Bölüm Konuşma Metni ve İpuçları

#### 1. Bölüm: Giriş & Ekip Rolleri (2 Dk)
* **Ne Söyleyeceksin:**
  > *"Hocam merhaba. Projemiz; birden fazla bağımsız mağazanın kendi ürünlerini listeleyip satabildiği, komisyon ve alt sipariş mantığıyla çalışan modern bir Çok Satıcılı Pazaryeri platformudur.*
  > *Projeyi tek kişi yürütüyor olmama rağmen, kurumsal yazılım süreçlerini simüle edebilmek adına 3 temel şapkayı ayrı ayrı yönettim:*
  > *1. **Proje Yöneticisi:** GitHub Projects üzerinde Kanban panosu kurdum, 1. haftanın tüm görevlerini kartlara böldüm.*
  > *2. **Backend Mimarı:** Laravel 11/12/13 standartlarında 20 tabloluk ilişkisel veri modelini ve Eloquent ilişkilerini tasarladım.*
  > *3. **Kalite & Test Sorumlusu:** Veritabanı tutarlılığını sağlamak için seeder ve migration doğrulama testlerini tamamladım."*

#### 2. Bölüm: Veritabanı Tasarımı & Kritik Kurallar (3 Dk)
* **Hocaya Gösterilecek Dosya:** `docs/eticaret.drawio.pdf`
* **Vurgulanacak 4 Kritik Kural:**
  1. **Para Formatı (Hassasiyet):** Para alanlarında yuvarlama hatalarını önlemek için asla `float` kullanılmadı, şartnamede istendiği gibi **`decimal(10,2)`** tanımlandı.
  2. **Çok Satıcılı Sipariş (Sub-Order):** Tek bir sepette farklı satıcıların ürünleri olduğunda ana sipariş (`orders`), satıcı bazında alt siparişlere (`sub_orders`) bölünür. Böylece her satıcı sadece kendi siparişini onaylayıp kargolar.
  3. **Stok Yönetimi:** Stoklar ana üründe değil `product_variants` tablosunda tutulur ve eşzamanlı satın almalarda yarış durumunu (race condition) önlemek için `lockForUpdate()` ile rezerve edilir.
  4. **Kategori & Medya Esnekliği:** Kategoriler kendi kendine referans veren (`parent_id`) sonsuz ağaç yapısındadır. Medya tablosu ise polimorfik (`morphs`) olarak hem ürünlere hem markalara hizmet eder.

#### 3. Bölüm: Canlı Demo (Kabul Kriteri) (2.5 Dk)
* **Yapılacak Hareket:** Terminalde veya hazırlanan başlatıcı üzerinden tek komut çalıştırmak:
  ```bash
  php artisan migrate:fresh --seed
  ```
* **Hocaya Söylenecek:**
  > *"Hocam, şartnamede belirtilen 1. hafta kabul kriterimiz: tek bir komutla tüm veritabanının sıfırlanıp 1 admin, 5 satıcı, 20 müşteri, 15 kategori ve 100 ürünün hatasız oluşturulmasıydı. Şimdi canlı olarak çalıştırıyorum..."*
* Komut bittiğinde konsoldaki yeşil tamamlandı yazılarını ve phpMyAdmin / MySQL tablosunu göster.
* **Test Giriş Bilgileri:**
  * Yönetici: `admin@pazaryeri.com` (Şifre: `password123`)
  * Satıcı: `satici1@pazaryeri.com` (Şifre: `password123`)
  * Müşteri: `musteri1@pazaryeri.com` (Şifre: `password123`)

#### 4. Bölüm: Wireframe ve UI Mimarisi (1.5 Dk)
* **Hocaya Gösterilecek Dosya:** `docs/anasayfa_wireframe.md`
* Header, Arama çubuğu, 15 kategorilik mega menü, Kampanya Banner'ı, Günün Fırsatları ve Öne Çıkan Satıcı Mağazaları bölümlerinin veritabanındaki hangi tablolarla konuştuğunu açıkla.

#### 5. Bölüm: Kapanış & 2. Hafta Hedefleri (1 Dk)
* Git akışında doğrudan `main` dala commit atılmadığını, tüm geliştirmelerin `develop` üzerinde yapıldığını ve GitHub'a pushlandığını göster.
* 2. Hafta hedefi: Kimlik Doğrulama (Auth), Rol & İzin Yönetimi (Spatie), Satıcı Başvuru ve Onay Paneli.

---

### ❓ Hocadan Gelebilecek Muhtemel Sorular ve Hazır Cevaplar

1. **Soru:** *"Tek bir siparişte iki farklı satıcıdan ürün alınırsa kargo takibini nasıl yapıyorsun?"*
   * **Cevap:** *"Hocam, `orders` tablosu genel ödemeyi ve müşteri sepetini tutar. Hemen altında `sub_orders` tablomuz var. Sipariş satıcı sayısına göre alt siparişlere bölünür. Her alt siparişin kendi `tracking_number` (kargo takip no) ve `status` (kargoya verildi, teslim edildi) alanı bulunur."*

2. **Soru:** *"Satıcı komisyonunu ürün bazında mı kategori bazında mı alıyorsun?"*
   * **Cevap:** *"Sistemimizde çift kademeli komisyon mimarisi kurduk. Hem `categories` tablosunda kategoriye özel oran (örn: elektronikte %8, giyimde %12), hem de `sellers` tablosunda satıcıya özel anlaşma oranı bulunur. Satış anında hesaplanan net komisyon tutarı `sub_orders.commission_amount` alanına snapshot olarak kilitlenir."*
