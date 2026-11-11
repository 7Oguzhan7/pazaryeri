# PazarYeri — 2., 3. ve 4. Hafta Sunum Rehberi

Bu rehber, proje gereksinim PDF'sindeki 2., 3. ve 4. hafta hedeflerini; bu
haftalarda uygulanan işleri, ilgili kod dosyalarını, doğrulama adımlarını ve
öğretmene yapılabilecek canlı sunum akışını bir arada açıklar. Sonraki haftalar
tamamlandıkça aynı dosyaya, en sondaki şablon kullanılarak yeni bir bölüm
eklenecektir. Önceki haftaların kayıtları silinmemelidir.

> **Sunumda doğru kapsamı anlatın:** Bu proje Laravel 13, Inertia.js ve React
> kullanır. PDF'deki Livewire ile varyant güncelleme kabul kriteri, mevcut React
> mimarisinde React durumu ile karşılanmıştır. Dördüncü hafta arama/filtre,
> satıcı başvurusu ve mağaza özelliklerini kapsar; beşinci haftaya bırakılan
> sepet yönetimi ve ödeme özellikleri tamamlanmış gibi sunulmamalıdır.

## İçindekiler

1. [Kısa proje anlatımı](#kısa-proje-anlatımı)
2. [2. hafta — Kimlik doğrulama, roller ve kategoriler](#2-hafta--kimlik-doğrulama-roller-ve-kategoriler)
3. [3. hafta — Ürün kataloğu ve vitrin](#3-hafta--ürün-kataloğu-ve-vitrin)
4. [4. hafta — Arama, filtreler ve satıcı yönetimi](#4-hafta--arama-filtreler-ve-satıcı-yönetimi)
5. [Öğretmene yapılacak demo akışı](#öğretmene-yapılacak-demo-akışı)
6. [Muhtemel sorular ve cevapları](#muhtemel-sorular-ve-cevapları)
7. [Doğrulama sonuçları ve sınırlar](#doğrulama-sonuçları-ve-sınırlar)
8. [Gelecek haftalar için kayıt şablonu](#gelecek-haftalar-için-kayıt-şablonu)

## Kısa proje anlatımı

> “PazarYeri, birden fazla mağazanın ürün listeleyebildiği çok satıcılı bir
> e-ticaret projesidir. Laravel tarafı veritabanı, doğrulama, roller ve iş
> kurallarını yönetir. Kullanıcı arayüzü React, Inertia.js ve Vite ile çalışır.
> İkinci haftada kimlik doğrulama, rol bazlı erişim ve kategori yönetimini;
> üçüncü haftada ürün vitrini, varyantlar, görseller ve sepete eklemeyi;
> dördüncü haftada arama/filtreleri, satıcı başvurusu ve mağaza yönetimini
> geliştirdim.”

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

7. **Sepet müşteri ve misafir alışverişine göre sınırlandırıldı.** Üye
   müşteriler kendi kullanıcı sepetini, misafirler oturum sepetini kullanır.
   Yönetici ve satıcı hesapları alışveriş yapamaz; bu kontrol sunucu tarafında
   da uygulanır. Aynı varyant tekrar eklenirse miktar artırılır. Ürün durumu,
   satıcı onayı ve stok yeniden doğrulanır.
   - `app/Models/User.php`
   - `app/Http/Controllers/Storefront/CartItemController.php`
   - `app/Http/Middleware/HandleInertiaRequests.php`
   - Rota: `POST /cart/items`

8. **Sepet görünür hale getirildi.** Üst menüdeki sepet bağlantısı gerçek
   ürün adedini gösterir. `/sepet` sayfası ürünleri, varyantı, satıcıyı,
   miktarı ve ara toplamı listeler. Ürün ekleme başarılı olduğunda kullanıcı
   doğrudan sepet sayfasına yönlendirilir.
   - `app/Http/Controllers/Storefront/CartPageController.php`
   - `resources/js/Pages/Storefront/Cart/Show.jsx`
   - `resources/js/Components/Layout.jsx`

9. **Satıcı görsel yönetimine panelinden ulaşır.** Onaylı satıcı paneli kendi
   ürünlerini listeler ve her ürünün görsel yükleme alanına bağlantı verir.
   Yükleme yetkisi ayrıca sunucu tarafındaki policy ile kontrol edilir;
   yöneticiler de ürün detay sayfasından görsel yükleyebilir.
   - `app/Http/Controllers/Seller/DashboardController.php`
   - `resources/js/Pages/Seller/Dashboard.jsx`
   - `app/Policies/ProductPolicy.php`

10. **N+1 sorgu riskini azaltmak için ilişkiler önceden yüklenir.** Ürün
   varyantı, medya, satıcı, kategori ve yorum verileri ilgili sorgularda
   eager loading ve aggregate sorgularıyla hazırlanır. Kategori ürün sayısı
   değişince sorgu adedi başına ürün artmamalıdır.
   - Feature testi: `tests/Feature/StorefrontCatalogTest.php`

### PDF kapsamı hakkında

Sepet sayfası ürünleri ve ara toplamı gösterir; sepetten miktar değiştirme,
ürün silme ve misafir sepetini giriş sonrası müşteri sepetiyle birleştirme,
favoriler, adres/kupon, checkout ve ödeme akışları sonraki haftaların
kapsamıdır. Görselleri olmayan seed ürünler arayüzde yer tutucu gösterir; bu,
gerçek ürün görseli değildir.

## 4. hafta — Arama, filtreler ve satıcı yönetimi

PDF'nin bu haftaki hedefleri; katalogda ürün bulmayı kolaylaştırmak, satıcı
başvurularını yönetici onayına bağlamak, satıcının kendi ürünlerini
yönetebilmesini sağlamak ve onaylı satıcıya herkese açık mağaza sayfası
sunmaktır.

### Uygulama adımları

1. **Ürün ve marka araması eklendi.** Ana sayfadaki arama ürün adında ve marka
   adında çalışır. Yazarken arama önerileri ayrı bir JSON rotasından alınır;
   sonuç sayısı sınırlandırılır ve istekler hız sınırlamasına tabidir.
   - `app/Http/Controllers/Storefront/HomeController.php`
   - `app/Http/Controllers/Storefront/SearchSuggestionController.php`
   - `resources/js/Components/Layout.jsx`
   - `resources/js/Pages/Storefront/Home.jsx`
   - Öneri rotası: `GET /arama/oneri`

2. **Katalog filtreleri eklendi.** Kategori, marka, en düşük/en yüksek fiyat,
   renk, beden ve en düşük puan filtreleri ürün kataloğunda kullanılabilir.
   Renk, beden ve fiyat koşulları aynı stok varyantına uygulanır; böylece bir
   varyantın rengiyle başka varyantın beden/fiyatı yanlışlıkla eşleştirilmez.
   Sayısal filtreler ve izin verilen sıralama değerleri sunucuda doğrulanır.
   - `app/Models/Product.php`
   - `app/Actions/Storefront/GetProductFilterOptions.php`
   - `app/Http/Requests/Storefront/CatalogFilterRequest.php`
   - `resources/js/Components/ProductFilters.jsx`
   - `resources/js/Pages/Storefront/Categories/Show.jsx`

3. **Filtre durumu URL'de korunur.** Arama/filtre değerleri sorgu
   parametreleridir; sayfa yenilendiğinde ve sayfalar arasında gezinirken
   seçili değerler korunur. Kategori sıralaması da aynı sorgu parametreleriyle
   birlikte çalışır.
   - `app/Http/Controllers/Storefront/CategoryPageController.php`
   - `app/Http/Controllers/Storefront/HomeController.php`

4. **Satıcı başvuru formu ve başvuru durumu eklendi.** Doğrulanmış kullanıcı
   başvuru formunu gönderir. Vergi numarası ve Türkiye IBAN'ı biçim ve
   sağlama kontrolünden geçer. Başvuru beklerken kullanıcı müşteri rolünde
   kalır; reddedilen başvuru düzeltilerek yeniden gönderilebilir.
   - `app/Http/Controllers/Seller/ApplicationController.php`
   - `app/Http/Requests/Seller/StoreSellerApplicationRequest.php`
   - `resources/js/Pages/Seller/Application.jsx`
   - Rota: `GET /satici-basvurusu`

5. **Yönetici onay/red ekranı eklendi.** Yönetici bekleyen başvuruları
   görüntüler; onaylama işlemi satıcı kaydı oluşturup rolü satıcıya geçirir.
   Red işlemi de başvuruyu ve gerekçeyi kaydeder. Onaydaki veritabanı
   değişiklikleri transaction içinde yapılır.
   - `app/Http/Controllers/Admin/SellerApplicationController.php`
   - `resources/js/Pages/Admin/SellerApplications/Index.jsx`
   - `app/Models/Seller.php`
   - Yönetici rotaları: `/admin/seller-applications`

6. **Satıcının ürün yönetimi eklendi.** Onaylı satıcı yeni ürün oluşturabilir,
   yalnızca kendisine ait ürünü düzenleyebilir veya silebilir; ürünü aktif/pasif
   yapabilir. Varyant SKU'su, fiyat, indirim fiyatı ve stok aynı formdan
   yönetilir. Başka satıcıya ait ürün/varyant kimlikleri sunucu tarafında
   reddedilir. Sipariş geçmişi olan ürün, ilişkili sipariş kayıtları korunarak
   pasife alınır.
   - `app/Http/Controllers/Seller/ProductController.php`
   - `app/Http/Requests/Seller/StoreProductRequest.php`
   - `app/Policies/ProductPolicy.php`
   - `resources/js/Components/SellerProductForm.jsx`
   - `resources/js/Pages/Seller/Products/`
   - Rotalar: `/satici/urunler`

7. **Herkese açık mağaza sayfası eklendi.** `/magaza/{slug}` yalnızca onaylı
   satıcılar için açılır ve o satıcının aktif, stokta bulunan ürünlerini
   gösterir. Ürün detayından satıcının mağazasına gidilebilir; onaysız veya
   bulunmayan mağaza herkese açık katalog olarak sunulmaz.
   - `app/Http/Controllers/Storefront/SellerStoreController.php`
   - `resources/js/Pages/Storefront/Store/Show.jsx`
   - Rota: `GET /magaza/{slug}`

8. **Haftanın davranışları feature testleriyle kapsandı.** Arama/filtre,
   URL parametreleri, başvuru ve yönetici kararı, satıcı ürün sahipliği,
   mağaza görünürlüğü ve stok koşulları otomatik olarak doğrulanır.
   - `tests/Feature/StorefrontSearchAndFiltersTest.php`
   - `tests/Feature/SellerApplicationTest.php`
   - `tests/Feature/SellerProductManagementTest.php`
   - `tests/Feature/PublicSellerStoreTest.php`

### 4. hafta demo akışı

1. Ana sayfadaki arama alanına ürün adı veya marka yazıp öneriyi gösterin.
2. Kategori sayfasında marka/fiyat/renk/beden/puan filtresi uygulayın; URL
   parametrelerinin ve sayfalama/sıralamanın korunduğunu gösterin.
3. Müşteri hesabıyla satıcı başvuru formunu doldurun; başvurunun beklemede
   olduğunu açıklayın.
4. Yönetici hesabıyla `/admin/seller-applications` sayfasında başvuruyu
   onaylayın veya reddedin; onayın kullanıcı rolünü değiştirdiğini belirtin.
5. Onaylı satıcı hesabıyla `/satici/urunler` sayfasında ürün ve stok
   varyantlarını yönetin. Ürün pasifken vitrine çıkmadığını, tekrar aktif
   edildiğinde mağaza sayfasında listelendiğini gösterin.
6. `/magaza/{slug}` sayfasını açıp mağaza ürünlerine gidin.

### 4. haftanın sınırları

Bu hafta ürün kataloğu, başvuru ve satıcı ürün yönetimini kapsar. Sipariş
oluşturma, checkout, ödeme ve kargo iş akışları burada tamamlanmış özellikler
değildir. Görsel yönetimi mevcut ürün detay ekranından çalışır; pasif ürünün
görsel bağlantısı gösterilmez, ürün önce yeniden yayına alınmalıdır.

## Öğretmene yapılacak demo akışı

Önerilen süre: **12 dakika**. Sunumdan önce uygulamayı ve veritabanını
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

### 12 dakikalık konuşma ve gösterim

| Süre | Gösterilecek bölüm | Anlatılacak nokta |
|---|---|---|
| 00:00–01:00 | Ana sayfa | Pazaryeri amacı ve Laravel + React/Inertia mimarisi |
| 01:00–02:30 | Giriş ve roller | Müşteri, yönetici ve onaylı satıcı erişim farkları |
| 02:30–04:00 | Kategori yönetimi | Yönetici kategori ekleme/düzenleme ve doğrulama |
| 04:00–05:00 | Ürün arama | Ürün/marka araması ve otomatik öneriler |
| 05:00–06:30 | Katalog filtreleri | Kategori, marka, fiyat, renk, beden, puan ve URL parametreleri |
| 06:30–07:30 | Ürün detayı ve sepet | Varyant fiyat/stok güncellemesi, görseller ve sepete ekleme |
| 07:30–08:30 | Satıcı başvurusu | Müşteri hesabıyla başvuru ve bekleyen durum |
| 08:30–09:30 | Yönetici onayı | Başvuruyu inceleme, onaylama/reddetme ve rol değişimi |
| 09:30–10:30 | Satıcı paneli | Ürün/varyant/stok yönetimi ve sahiplik sınırları |
| 10:30–11:15 | Mağaza sayfası | Slug ile mağaza adresi ve vitrindeki ürünler |
| 11:15–12:00 | Doğrulama ve kapanış | Test/build sonucu, sonraki hafta kapsamı |

### Demo sırasında önerilen sıra

1. Ana sayfada arama kutusuna ürün veya marka yazıp öneri listesini gösterin.
2. Kategoriye girip fiyat/marka/renk/beden/puan filtresi seçin; URL'deki
   sorgu parametrelerinin korunduğunu gösterin.
3. Bir ürün açıp varyantı değiştirin; fiyat ve stok bilgisinin yenilemesiz
   güncellendiğini ve ürünün sepete eklenebildiğini gösterin.
4. Müşteri hesabıyla satıcı başvurusu yapın. Onayı göstermek için ayrı bir
   tarayıcı profili/oturumunda yönetici hesabıyla bekleyen başvuruyu açın.
5. Onaylı satıcı hesabıyla `/satici/urunler` sayfasında kendi ürününü ve
   varyant stoklarını yönetin; başka satıcı ürünlerinin listelenmediğini
   belirtin.
6. Satıcı mağazasını `/magaza/{slug}` adresinde gösterin.
7. Terminalde `php artisan test --compact` ve `npm run build` sonuçlarını
   gösterin.

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
**Cevap:** “Dördüncü haftada satıcı başvurusu ve satıcı ürün yönetimi de
eklenmiştir. Sepet miktarını değiştirme/silme, misafir sepetini girişte
birleştirme, checkout, gerçek ödeme ve kargo/iade akışları sonraki haftaların
kapsamıdır; tamamlanmış gibi sunulmuyor.”

## Doğrulama sonuçları ve sınırlar

- Son doğrulamada tam Laravel test paketi: **89 test geçti, 639 assertion
  geçti**.
- `npm run build` üretim arayüzünü başarıyla derledi.
- `vendor/bin/pint --dirty --format agent` değiştirilmiş PHP dosyalarını
  biçimlendirdi.
- `git diff --check` temiz sonuçlandı.
- `composer validate --no-check-publish` Composer dosyalarını doğrular.
- `composer audit` mevcut kilit dosyasında `symfony/mime` için orta seviye bir
  güvenlik tavsiyesi bildirmiştir; bu durum 2./3. hafta uygulamasından önce de
  mevcuttu ve bu teslim kapsamında bağımlılık yükseltmesi yapılmamıştır.
- 2. hafta çalışmaları `816d3ec` commit'iyle; React/Inertia geçişi `49b45c6`
  commit'iyle `develop` dalındadır. Bu çalışma ağacındaki 3./4. hafta
  değişiklikleri ve sunum rehberi henüz commit edilip GitHub'a gönderilmedi.

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
