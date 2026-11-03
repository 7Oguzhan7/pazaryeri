# PazarYeri — 2. ve 3. Hafta Sunum Rehberi

Bu rehber, proje gereksinim PDF'sindeki 2. ve 3. hafta hedeflerini; bu haftalarda
uygulanan işleri, ilgili kod dosyalarını, doğrulama adımlarını ve öğretmene
yapılabilecek canlı sunum akışını bir arada açıklar. Sonraki haftalar
tamamlandıkça aynı dosyaya, en sondaki şablon kullanılarak yeni bir bölüm
eklenecektir. Önceki haftaların kayıtları silinmemelidir.

> **Sunumda doğru kapsamı anlatın:** Bu proje Laravel 13, Inertia.js ve React
> kullanır. PDF'deki Livewire ile varyant güncelleme kabul kriteri, mevcut React
> mimarisinde React durumu ile karşılanmıştır. PDF'de sonraki haftalara bırakılan
> özellikler tamamlanmış gibi sunulmamalıdır.

## İçindekiler

1. [Kısa proje anlatımı](#kısa-proje-anlatımı)
2. [2. hafta — Kimlik doğrulama, roller ve kategoriler](#2-hafta--kimlik-doğrulama-roller-ve-kategoriler)
3. [3. hafta — Ürün kataloğu ve vitrin](#3-hafta--ürün-kataloğu-ve-vitrin)
4. [Öğretmene yapılacak demo akışı](#öğretmene-yapılacak-demo-akışı)
5. [Muhtemel sorular ve cevapları](#muhtemel-sorular-ve-cevapları)
6. [Doğrulama sonuçları ve sınırlar](#doğrulama-sonuçları-ve-sınırlar)
7. [Gelecek haftalar için kayıt şablonu](#gelecek-haftalar-için-kayıt-şablonu)

## Kısa proje anlatımı

> “PazarYeri, birden fazla mağazanın ürün listeleyebildiği çok satıcılı bir
> e-ticaret projesidir. Laravel tarafı veritabanı, doğrulama, roller ve iş
> kurallarını yönetir. Kullanıcı arayüzü React, Inertia.js ve Vite ile çalışır.
> İkinci haftada kimlik doğrulama, rol bazlı erişim ve kategori yönetimini;
> üçüncü haftada ise gezilebilir ürün vitrini, varyantlar, görseller ve sepete
> ürün ekleme akışını geliştirdim.”

**Teknoloji özeti:** PHP 8.3, Laravel 13, Inertia.js, React 19, Vite ve
Tailwind CSS 4. Proje geliştirme dalı `develop`'tur; `main` dalına doğrudan
geliştirme gönderilmez.

## 2. hafta — Kimlik doğrulama, roller ve kategoriler

PDF'deki hedef, kullanıcıların kimlikleri ve rolleri doğrultusunda sisteme
girebilmesi; yöneticinin kategori yönetebilmesi ve ortak mağaza düzeninin
kullanılmasıdır.

### Uygulama adımları

1. **Kimlik doğrulama akışları korundu.** Kayıt, giriş, çıkış, e-posta
   doğrulama, şifre sıfırlama ve doğrulanmış kullanıcı işlemleri Laravel
   tarafında yürütülür. İstek ve doğrulama kuralları sunucuda kalır; arayüz
   Inertia sayfalarına taşınmıştır.
   - Sunucu tarafı: `app/Http/Controllers/Auth/`,
     `app/Http/Controllers/ProfileController.php`,
     `routes/auth.php`
   - React sayfaları: `resources/js/Pages/Auth/`,
     `resources/js/Pages/Profile/Edit.jsx`
   - Ortak Inertia verileri: `app/Http/Middleware/HandleInertiaRequests.php`

2. **Üç temel rol kullanıldı:** `admin`, `seller` ve `customer`. Kayıt olan
   kullanıcıya müşteri rolü verilir. Roller Spatie Permission ile yönetilir;
   rol seed işlemi tekrar çalıştırılabilir şekilde tasarlanmıştır.
   - `app/Models/User.php`
   - `app/Listeners/AssignCustomerRole.php`
   - `database/seeders/RoleSeeder.php`
   - `database/seeders/DatabaseSeeder.php`

3. **Yönetici ve onaylı satıcı erişimleri sınırlandı.** Yönetim alanındaki
   rotalar yönetici rolü ister. Satıcı paneli kimlik doğrulama ve e-posta
   doğrulamasına ek olarak satıcının onaylı durumda olmasını denetler.
   - `routes/web.php`
   - `bootstrap/app.php`
   - `app/Http/Middleware/EnsureApprovedSeller.php`

4. **Kategori CRUD işlemleri tamamlandı.** Yönetici kategori ekleyebilir,
   düzenleyebilir, listeleyebilir ve güvenli koşullarda silebilir. Üst kategori,
   slug, sıralama ve komisyon oranı alanları desteklenir. İstek doğrulaması
   Form Request sınıflarındadır; controller işlemleri kategori Action
   sınıflarına devredilir.
   - `app/Http/Controllers/Admin/CategoryController.php`
   - `app/Http/Requests/Admin/`
   - `app/Actions/Categories/`
   - React yönetim sayfaları: `resources/js/Pages/Admin/Categories/`

5. **Ortak mağaza arayüzü ve kategori menüsü kuruldu.** Kategori hiyerarşisi
   veritabanından okunur ve Inertia sayfalarına aktarılır. Böylece menüye yeni
   kategori eklemek için JSX içinde sabit veri değiştirmek gerekmez.
   - `app/Http/Middleware/HandleInertiaRequests.php`
   - `resources/js/Components/Layout.jsx`

6. **Doğrulama metinleri Türkçeleştirildi.** Kullanıcıya gösterilen form ve
   framework doğrulama mesajları `lang/tr/` ve `lang/tr.json` içinden gelir.

7. **Davranışlar test edildi.** Kategori oluşturma/düzenleme/silme, erişim
   kontrolü, kimlik doğrulama ve menü davranışı feature testleriyle sınanır.
   Başlıca test konumları `tests/Feature/Admin/`,
   `tests/Feature/Auth/`, `tests/Feature/RoleAccessTest.php` ve
   `tests/Feature/StorefrontMegaMenuTest.php` dizin/dosyalarıdır.

### PDF kapsamı hakkında

2. hafta için bu projede satıcı başvurusu yönetimi değil, PDF'deki gibi
`/satici` alanına **yalnızca onaylı satıcıların erişmesi** uygulanmıştır.
Satıcı başvurusu formu ve admin onayı PDF'nin 4. hafta planındadır.

## 3. hafta — Ürün kataloğu ve vitrin

PDF'nin hedefi, ziyaretçinin ürünleri ana sayfa, kategori sayfası ve ürün
detayından gezebilmesidir. Ürün ve varyant tabloları önceki veritabanı
çalışmasında zaten bulunduğundan, mevcut şema tekrar oluşturulmamış; vitrin
özellikleri bu modellere bağlanmıştır.

### Uygulama adımları

1. **Ürün görselleri Media Library'ye bağlandı.** `Product` modeli Spatie
   Media Library kullanır. `products` koleksiyonuna yüklenen görseller için
   `thumb` ve `preview` dönüşümleri üretilir. Projedeki mevcut `media` tablosu
   uyumlu olduğundan ikinci bir media migration'ı eklenmemiştir.
   - `app/Models/Product.php`
   - Paket gereksinimi: `composer.json` ve `composer.lock`

2. **Çoklu görsel yükleme sınırlandırıldı.** Bir istekte en fazla 8 görsel,
   her görsel için en fazla 5 MB ve en fazla 5000 × 5000 piksel kabul edilir.
   İçerik ve dosya uzantısı birlikte doğrulanır. Yalnızca yöneticiler ve kendi
   ürününün sahibi olan onaylı satıcılar görsel yükleyebilir.
   - `app/Http/Requests/StoreProductImagesRequest.php`
   - `app/Policies/ProductPolicy.php`
   - `app/Http/Controllers/Storefront/ProductMediaController.php`
   - Rota: `routes/web.php`

3. **Ana sayfaya veritabanı destekli vitrin eklendi.**
   - Aktif banner'lar sıralı gösterilir; diskte olmayan banner görseli kırık
     görsel üretmez.
   - Ürün bulunan kategoriler öne çıkarılır.
   - “Çok satanlar” yalnızca ödenmiş ve iptal/iade edilmemiş satışlardan
     hesaplanır; satış yoksa uydurma popülerlik gösterilmez.
   - “Yeni gelenler” aktif, onaylı satıcıya ait ve stokta varyantı olan
     ürünlerden oluşur.
   - Sunucu sorguları: `app/Http/Controllers/Storefront/HomeController.php`
   - React arayüzü: `resources/js/Pages/Storefront/Home.jsx`
   - Kart bileşeni: `resources/js/Components/ProductCard.jsx`

4. **Kategori sayfası oluşturuldu.** `/kategori/{slug}` adresinde üst
   kategorinin ürünleri ve bir alt seviyedeki kategorilerin ürünleri birlikte
   listelenir. Sayfa başına 24 ürün gösterilir. Sıralama seçenekleri sunucu
   tarafında izin listesiyle sınırlandırılır:
   - `price_asc`: stoktaki varyantların indirim varsa indirimli fiyatına göre
     artan sıra.
   - `price_desc`: aynı etkin fiyata göre azalan sıra.
   - `newest`: en yeni ürün önce.
   - `rating`: en fazla onaylı değerlendirmeye sahip ürün önce; eşitlikte
     ortalama puan kullanılır.
   - `app/Http/Controllers/Storefront/CategoryPageController.php`
   - `resources/js/Pages/Storefront/Categories/Show.jsx`

5. **Ürün detay sayfası oluşturuldu.** `/urun/{slug}` adresi ürün galerisi,
   açıklama, kategori, satıcı adı, onaylı yorumlar, varyant, etkin fiyat ve
   stok bilgisini sunar.
   - `app/Http/Controllers/Storefront/ProductPageController.php`
   - `resources/js/Pages/Storefront/Products/Show.jsx`

6. **Varyant seçimi sayfa yenilemeden çalışır.** React seçilen varyantı
   istemci durumunda tutar; varyant değişince fiyat, indirim ve stok bilgisi
   ekranda hemen güncellenir. Bu, PDF'deki Livewire kabul kriterinin projedeki
   React + Inertia mimarisine uyarlanmış karşılığıdır.

7. **Sepete ekleme çalışır hale getirildi.** Kullanıcı sepeti veya oturum
   kimliğine bağlı misafir sepeti kullanılır. Aynı varyant tekrar eklenirse
   miktar artırılır. Ürün durumu, satıcı onayı ve stok sunucuda yeniden
   doğrulanır.
   - `app/Http/Controllers/Storefront/CartItemController.php`
   - Rota: `POST /cart/items`

8. **N+1 sorgu riskini azaltmak için ilişkiler önceden yüklenir.** Ürün
   varyantı, medya, satıcı, kategori ve yorum verileri ilgili sorgularda
   eager loading ve aggregate sorgularıyla hazırlanır. Kategori ürün sayısı
   değişince sorgu adedi başına ürün artmamalıdır.
   - Feature testi: `tests/Feature/StorefrontCatalogTest.php`

### PDF kapsamı hakkında

Sepete ürün ekleme bu çalışmada ürün sayfasından kullanılabilir; tam sepet
görüntüleme, sepet birleştirme, favoriler, adres/kupon, checkout ve ödeme
akışları sonraki haftaların kapsamıdır. Görselleri olmayan seed ürünler
arayüzde yer tutucu gösterir; bu, gerçek ürün görseli değildir.

## Öğretmene yapılacak demo akışı

Önerilen süre: **10 dakika**. Sunumdan önce uygulamayı ve veritabanını
hazırlayın; sunum sırasında çalışan yerel veritabanında `migrate:fresh`
çalıştırmayın, çünkü bu komut mevcut tabloları siler.

### Sunumdan önce

1. `develop` dalındaki son commit'i alın:
   ```powershell
   git checkout develop
   git pull origin develop
   ```
2. Gerekirse bağımlılıkları ve derlenmiş arayüzü hazırlayın:
   ```powershell
   composer install
   npm install
   npm run build
   ```
3. İlk kurulumda `.env` ve uygulama anahtarı hazırlandıktan sonra veritabanını
   yükleyin. `migrate:fresh` veritabanını sıfırladığı için yalnızca demo/test
   veritabanında kullanın:
   ```powershell
   php artisan migrate:fresh --seed
   ```
4. Uygulamayı başlatın:
   ```powershell
   php artisan serve
   ```
   Tarayıcı adresi: `http://127.0.0.1:8000`
5. Önceden yüklenmiş build dosyalarıyla çalışılacaksa ayrıca Vite dev sunucusu
   gerekmez. Arayüz üzerinde geliştirme yapılacaksa ikinci terminalde
   `npm run dev` çalıştırın.

### 10 dakikalık konuşma ve gösterim

| Süre | Gösterilecek bölüm | Anlatılacak nokta |
|---|---|---|
| 00:00–01:00 | Ana sayfa | Çok satıcılı pazaryeri amacı ve Laravel + React/Inertia mimarisi |
| 01:00–03:00 | Giriş ve roller | Müşteri rolünün atanması; admin ve onaylı satıcı rotalarının korunması |
| 03:00–04:00 | Admin kategori sayfası | Kategori ekleme/düzenleme/silme, üst kategori ve doğrulama |
| 04:00–06:00 | Ana sayfa ve kategori | Aktif banner, öne çıkan kategoriler, ürün kartı, 24 ürün/sayfa ve sıralama |
| 06:00–08:00 | Ürün detayı | Görsel alanı, varyant değiştirerek fiyat/stok güncelleme ve sepete ekleme |
| 08:00–09:00 | Test ve teknik doğrulama | Feature testleri, N+1 sorgu kontrol testi ve üretim frontend derlemesi |
| 09:00–10:00 | Kapanış | 2./3. hafta teslimi, kapsam dışı sonraki işler ve GitHub `develop` dalı |

### Demo sırasında önerilen sıra

1. Ana sayfadaki banner ve kategori bağlantılarını gösterin.
2. Kategoriye gidin; sıralamayı “Fiyat: artan” ve “En çok değerlendirilen”
   arasında değiştirin.
3. Bir ürün açıp varyantı değiştirin; sayfa yenilenmeden fiyat ve stok
   bilgisinin değiştiğini gösterin.
4. Uygunsa giriş yapıp ürünü sepete ekleyin. Stok sınırı aşılırsa form
   hatasının sunucu tarafından döndüğünü açıklayın.
5. Yönetici hesabıyla `/admin/categories` ekranını ve kategori CRUD
   doğrulamasını gösterin. Yönetici hesabı sunumdan önce hazır olmalıdır.
6. Terminalde `php artisan test --compact` ve `npm run build` komutlarının
   başarılı sonuçlarını gösterin.

## Muhtemel sorular ve cevapları

**Soru: PDF'de Livewire yazıyor; neden React kullandınız?**
**Cevap:** “Önyüzü React ve Inertia kullanacak şekilde geliştirme kararı
verildi. Varyant değiştirme kabul kriterini Livewire yerine React'in yerel
durum yönetimiyle karşıladım; sunucuya yeni sayfa isteği göndermeden seçilen
varyantın fiyatı ve stoğu güncelleniyor.”

**Soru: Ürün puanı sıralaması nasıl çalışıyor?**
**Cevap:** “Yalnızca yönetici tarafından onaylanmış yorumlar hesaba katılıyor.
Önce yorum sayısı fazla olan ürün gösteriliyor; eşitlikte ortalama puan
kullanılıyor. SQL sıralama alanı kullanıcı girdisinden alınmıyor, izin
listesinden seçiliyor.”

**Soru: Çok satanlar verisi nereden geliyor?**
**Cevap:** “Ödenmiş sipariş kalemleri toplanıyor. İptal ve iade edilen alt
siparişler hesaba katılmıyor. Henüz satış yoksa sistem yapay satış üretmiyor;
boş durum mesajı gösteriyor.”

**Soru: Görsel yüklemesini herkes yapabilir mi?**
**Cevap:** “Hayır. Yönetici veya yalnızca kendi ürününün sahibi olan onaylı
satıcı yükleyebilir. Dosya içeriği, uzantısı, boyutu ve ölçüleri doğrulanır;
Media Library küçük resim ve önizleme türevlerini üretir.”

**Soru: N+1 olmadığını nasıl kontrol ettiniz?**
**Cevap:** “Ürün kartlarının ihtiyaç duyduğu ilişkiler sorguda önceden
yükleniyor. Feature testinde tek ürünlü ve çok ürünlü kategori isteğinin
sorgu sayısı karşılaştırılıyor. Bu proje testi sorgu adedinin ürün başına
artmamasını denetler; sunumda Debugbar ekran görüntüsü iddiasında
bulunmuyorum.”

**Soru: E-posta doğrulamasını gerçek SMTP ile denediniz mi?**
**Cevap:** “Otomatik testler e-posta doğrulama davranışını dış SMTP servisi
çağırmadan sınar. Yerel Mailpit kurulumu bu ortamda yapılmadı; SMTP ile canlı
gönderim doğrulanmış bir teslimat olarak sunulmuyor. README'de Mailpit
kurulum adımları bulunuyor.”

**Soru: Şu an tamamlanmayan alışveriş özellikleri neler?**
**Cevap:** “Bu teslimde ürün kataloğu ve sepete ekleme var. Sepet yönetimi,
checkout, gerçek ödeme, kargo/iade ve satıcı ürün yönetimi PDF'deki sonraki
haftaların işidir; tamamlanmış gibi gösterilmiyor.”

## Doğrulama sonuçları ve sınırlar

- Son doğrulamada tam Laravel test paketi: **66 test geçti, 401 assertion
  geçti**. Kapsam, kod değişikliği oldukça yeniden çalıştırılmalıdır.
- `npm run build` üretim arayüzünü başarıyla derler.
- `vendor/bin/pint --dirty --format agent` değiştirilmiş PHP dosyalarını
  biçimlendirir.
- `composer validate --no-check-publish` Composer dosyalarını doğrular.
- `composer audit` mevcut kilit dosyasında `symfony/mime` için orta seviye bir
  güvenlik tavsiyesi bildirmiştir; bu durum 2./3. hafta uygulamasından önce de
  mevcuttu ve bu teslim kapsamında bağımlılık yükseltmesi yapılmamıştır.
- 2. hafta çalışmaları `816d3ec` commit'iyle; React/Inertia geçişi `49b45c6`
  commit'iyle `develop` dalında bulunmaktadır. 3. hafta kodu ve bu rehber,
  bu teslim kapsamında aynı dala gönderilmektedir.

## Gelecek haftalar için kayıt şablonu

Her yeni haftanın kodu ve testleri tamamlandıktan sonra, bu şablon kopyalanıp
bu dosyanın sonuna eklenmelidir. Henüz yapılmamış haftalar boş/plan olarak
kalmalı; tamamlanmış gibi işaretlenmemelidir.

```markdown
## [N]. hafta — [PDF'deki hafta başlığı]

PDF hedefi:
- ...

Uygulanan adımlar:
1. ...
2. ...

İlgili dosyalar:
- `...`

Kabul kriterleri ve doğrulama:
- Test: ...
- Build/lint: ...
- Canlı demo: ...

Kapsam dışı veya ertelenen işler:
- ...

Sunum konuşması:
> “...”

Commit/push:
- Commit: ...
- Dal: `develop`
```

**Gelecek haftalarda izlenecek sıra:** PDF gereksinimini önce kontrol et;
uygulama ve testleri tamamla; test/build sonuçlarını kaydet; bu dosyaya yeni
hafta bölümünü ekle; README'deki sunum rehberi bağlantısını koru; ardından
kullanıcı talep ettiğinde değişiklikleri `develop` dalına commit edip GitHub'a
push et.
