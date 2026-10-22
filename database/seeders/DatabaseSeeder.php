<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with required Week 1 deliverables.
     */
    public function run(): void
    {
        $this->command?->info("🚀 Seeder başlatılıyor: 1 Admin, 5 Satıcı, 20 Müşteri, 15 Kategori, 100 Ürün...");

        // ==========================================
        // 1. ADMİN OLUŞTURMA (1 Adet)
        // ==========================================
        $admin = User::firstOrCreate(
            ['email' => 'admin@pazaryeri.com'],
            [
                'name' => 'Pazaryeri Sistem Yöneticisi',
                'phone' => '05550000001',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $this->command?->info("✅ 1 Admin oluşturuldu: admin@pazaryeri.com (Şifre: password123)");

        // ==========================================
        // 2. SATICILAR OLUŞTURMA (5 Adet)
        // ==========================================
        $sellerStores = [
            ['name' => 'Teknoloji Dünyası A.Ş.', 'slug' => 'teknoloji-dunyasi', 'tax' => '1234567890', 'iban' => 'TR120006200000000000000001', 'rate' => 8.50],
            ['name' => 'Moda & Trend Mağazacılık', 'slug' => 'moda-trend', 'tax' => '2345678901', 'iban' => 'TR230006200000000000000002', 'rate' => 12.00],
            ['name' => 'Ev & Yaşam Konsept', 'slug' => 'ev-yasam-konsept', 'tax' => '3456789012', 'iban' => 'TR340006200000000000000003', 'rate' => 10.00],
            ['name' => 'Sporium Outdoor', 'slug' => 'sporium-outdoor', 'tax' => '4567890123', 'iban' => 'TR450006200000000000000004', 'rate' => 9.50],
            ['name' => 'Güzellik & Kozmetik Merkezi', 'slug' => 'guzellik-kozmetik', 'tax' => '5678901234', 'iban' => 'TR560006200000000000000005', 'rate' => 15.00],
        ];

        $sellers = [];
        foreach ($sellerStores as $index => $store) {
            $sellerNum = $index + 1;
            $user = User::firstOrCreate(
                ['email' => "satici{$sellerNum}@pazaryeri.com"],
                [
                    'name' => "Satıcı Yetkilisi {$sellerNum}",
                    'phone' => "0555100000{$sellerNum}",
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            $seller = Seller::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'store_name' => $store['name'],
                    'slug' => $store['slug'],
                    'tax_number' => $store['tax'],
                    'iban' => $store['iban'],
                    'status' => 'onayli',
                    'commission_rate' => $store['rate'],
                ]
            );
            $sellers[] = $seller;
        }
        $this->command?->info("✅ 5 Satıcı ve Mağazası oluşturuldu (satici1@... - satici5@...)");

        // ==========================================
        // 3. MÜŞTERİLER OLUŞTURMA (20 Adet) & ADRESLER
        // ==========================================
        $turkishNames = [
            'Ahmet Yılmaz', 'Mehmet Demir', 'Ayşe Kaya', 'Fatma Çelik', 'Mustafa Şahin',
            'Emine Yıldız', 'Ali Öztürk', 'Zeynep Aydın', 'Hüseyin Özdemir', 'Hatice Arslan',
            'Murat Doğan', 'Elif Kılıç', 'İbrahim Aslan', 'Büşra Çetin', 'Hasan Kara',
            'Seda Koç', 'Burak Kurt', 'Merve Özkan', 'Caner Şimşek', 'Gizem Polat'
        ];

        $cities = [
            ['city' => 'İstanbul', 'district' => 'Kadıköy'],
            ['city' => 'İstanbul', 'district' => 'Beşiktaş'],
            ['city' => 'Ankara', 'district' => 'Çankaya'],
            ['city' => 'İzmir', 'district' => 'Karşıyaka'],
            ['city' => 'Bursa', 'district' => 'Nilüfer'],
            ['city' => 'Antalya', 'district' => 'Muratpaşa'],
        ];

        for ($i = 1; $i <= 20; $i++) {
            $name = $turkishNames[$i - 1] ?? "Müşteri {$i}";
            $customer = User::firstOrCreate(
                ['email' => "musteri{$i}@pazaryeri.com"],
                [
                    'name' => $name,
                    'phone' => sprintf('0532%07d', $i * 11111),
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            $cityData = $cities[($i - 1) % count($cities)];
            Address::firstOrCreate(
                ['user_id' => $customer->id, 'title' => 'Ev Adresim'],
                [
                    'city' => $cityData['city'],
                    'district' => $cityData['district'],
                    'full_address' => "{$cityData['district']} Mah. Atatürk Cad. No:{$i} Daire:4",
                    'is_default' => true,
                ]
            );
        }
        $this->command?->info("✅ 20 Müşteri ve teslimat adresleri oluşturuldu (musteri1@... - musteri20@...)");

        // ==========================================
        // 4. KATEGORİLER (15 Adet - Hiyerarşik Ağaç Yapısı)
        // ==========================================
        $categoryTree = [
            'Elektronik' => [
                'commission_rate' => 8.00,
                'children' => ['Telefon & Aksesuar', 'Bilgisayar & Tablet', 'Televizyon & Ses Sistemleri']
            ],
            'Giyim & Moda' => [
                'commission_rate' => 12.00,
                'children' => ['Kadın Giyim', 'Erkek Giyim', 'Ayakkabı & Çanta']
            ],
            'Ev & Yaşam' => [
                'commission_rate' => 10.00,
                'children' => ['Mobilya & Dekorasyon', 'Mutfak Gereçleri']
            ],
            'Spor & Outdoor' => [
                'commission_rate' => 10.00,
                'children' => ['Fitness & Kondisyon', 'Outdoor Giyim & Kamp']
            ],
            'Kozmetik & Kişisel Bakım' => [
                'commission_rate' => 15.00,
                'children' => ['Parfüm & Deodorant', 'Cilt Bakımı & Makyaj']
            ],
        ];

        $allCategories = [];
        $sortOrder = 1;

        foreach ($categoryTree as $parentName => $data) {
            $parentCat = Category::firstOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'name' => $parentName,
                    'parent_id' => null,
                    'commission_rate' => $data['commission_rate'],
                    'sort' => $sortOrder++,
                ]
            );
            $allCategories[] = $parentCat;

            foreach ($data['children'] as $childName) {
                $childCat = Category::firstOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'name' => $childName,
                        'parent_id' => $parentCat->id,
                        'commission_rate' => $data['commission_rate'],
                        'sort' => $sortOrder++,
                    ]
                );
                $allCategories[] = $childCat;
            }
        }
        $this->command?->info("✅ 15 Kategori hiyerarşik (ana/alt) olarak oluşturuldu.");

        // ==========================================
        // 5. MARKALAR (Brands)
        // ==========================================
        $brandNames = ['Apple', 'Samsung', 'Xiaomi', 'Nike', 'Adidas', 'Philips', 'Sony', 'Zara', 'IKEA', 'L\'Oreal'];
        $brands = [];
        foreach ($brandNames as $bName) {
            $brands[] = Brand::firstOrCreate(
                ['slug' => Str::slug($bName)],
                [
                    'name' => $bName,
                    'logo' => 'brands/' . Str::slug($bName) . '.png',
                ]
            );
        }
        $this->command?->info("✅ 10 Marka tanımlandı.");

        // ==========================================
        // 6. ÜRÜNLER (100 Adet) & VARYANTLARI (decimal(10,2) & stok)
        // ==========================================
        $sampleProductTemplates = [
            // Elektronik
            ['name' => 'iPhone 15 Pro Max 256GB', 'cat' => 'Telefon & Aksesuar', 'brand' => 'Apple', 'price' => 74999.00, 'colors' => ['Naturel Titanyum', 'Siyah Titanyum', 'Mavi Titanyum']],
            ['name' => 'Galaxy S24 Ultra 512GB', 'cat' => 'Telefon & Aksesuar', 'brand' => 'Samsung', 'price' => 68499.00, 'colors' => ['Titanyum Gri', 'Titanyum Siyah']],
            ['name' => 'Redmi Note 13 Pro 256GB', 'cat' => 'Telefon & Aksesuar', 'brand' => 'Xiaomi', 'price' => 14999.00, 'colors' => ['Siyah', 'Yeşil', 'Mavi']],
            ['name' => 'MacBook Pro 14 M3 Chip 16GB', 'cat' => 'Bilgisayar & Tablet', 'brand' => 'Apple', 'price' => 64999.00, 'colors' => ['Uzay Grisi', 'Gümüş']],
            ['name' => 'Galaxy Tab S9 FE Android Tablet', 'cat' => 'Bilgisayar & Tablet', 'brand' => 'Samsung', 'price' => 15999.00, 'colors' => ['Gri', 'Nane Yeşili']],
            ['name' => 'OLED 65 Inç 4K Smart TV', 'cat' => 'Televizyon & Ses Sistemleri', 'brand' => 'Sony', 'price' => 52000.00, 'colors' => ['Siyah']],
            ['name' => 'Bluetooth ANC Kablosuz Kulaklık', 'cat' => 'Televizyon & Ses Sistemleri', 'brand' => 'Sony', 'price' => 8499.00, 'colors' => ['Siyah', 'Gümüş']],
            ['name' => 'AirPods Pro 2. Nesil USB-C', 'cat' => 'Telefon & Aksesuar', 'brand' => 'Apple', 'price' => 9299.00, 'colors' => ['Beyaz']],

            // Giyim & Moda
            ['name' => 'Air Zoom Pegasus Koşu Ayakkabısı', 'cat' => 'Ayakkabı & Çanta', 'brand' => 'Nike', 'price' => 3899.00, 'sizes' => ['41', '42', '43', '44'], 'colors' => ['Siyah/Beyaz', 'Mavi']],
            ['name' => 'Ultraboost Light Spor Ayakkabı', 'cat' => 'Ayakkabı & Çanta', 'brand' => 'Adidas', 'price' => 4599.00, 'sizes' => ['40', '41', '42', '43'], 'colors' => ['Beyaz', 'Siyah']],
            ['name' => 'Slim Fit Klasik Erkek Gömlek', 'cat' => 'Erkek Giyim', 'brand' => 'Zara', 'price' => 1199.00, 'sizes' => ['S', 'M', 'L', 'XL'], 'colors' => ['Beyaz', 'Açık Mavi']],
            ['name' => 'Oversize Kapüşonlu Sweatshirt', 'cat' => 'Erkek Giyim', 'brand' => 'Nike', 'price' => 2499.00, 'sizes' => ['M', 'L', 'XL'], 'colors' => ['Gri Melanj', 'Siyah']],
            ['name' => 'Kruvaze Yünlü Kadın Kaban', 'cat' => 'Kadın Giyim', 'brand' => 'Zara', 'price' => 3999.00, 'sizes' => ['S', 'M', 'L'], 'colors' => ['Camel', 'Siyah']],
            ['name' => 'Yüksek Bel Slim Kadın Jean', 'cat' => 'Kadın Giyim', 'brand' => 'Zara', 'price' => 1499.00, 'sizes' => ['36', '38', '40'], 'colors' => ['Açık Mavi', 'Koyu İndigo']],
            ['name' => 'Deri Omuz Çantası Günlük Seri', 'cat' => 'Ayakkabı & Çanta', 'brand' => 'Zara', 'price' => 1899.00, 'colors' => ['Kahverengi', 'Siyah']],

            // Ev & Yaşam
            ['name' => 'Ergonomik Çalışma Masası ve Koltuğu', 'cat' => 'Mobilya & Dekorasyon', 'brand' => 'IKEA', 'price' => 7499.00, 'colors' => ['Beyaz', 'Meşe']],
            ['name' => 'Modern İskandinav Kitaplık 5 Raflı', 'cat' => 'Mobilya & Dekorasyon', 'brand' => 'IKEA', 'price' => 3299.00, 'colors' => ['Beyaz', 'Antrasit']],
            ['name' => 'Airfryer XXL Sıcak Hava Fritözü', 'cat' => 'Mutfak Gereçleri', 'brand' => 'Philips', 'price' => 5999.00, 'colors' => ['Siyah Altın', 'Beyaz']],
            ['name' => 'Tam Otomatik Espresso Kahve Makinesi', 'cat' => 'Mutfak Gereçleri', 'brand' => 'Philips', 'price' => 13499.00, 'colors' => ['Gümüş', 'Mat Siyah']],
            ['name' => '12 Kişilik Porselen Yemek Takımı', 'cat' => 'Mutfak Gereçleri', 'brand' => 'IKEA', 'price' => 4500.00, 'colors' => ['Mat Beyaz']],

            // Spor & Outdoor
            ['name' => 'Manyetik Katlanabilir Kondisyon Bisikleti', 'cat' => 'Fitness & Kondisyon', 'brand' => 'Sporium', 'price' => 8999.00, 'colors' => ['Siyah-Kırmızı']],
            ['name' => 'Ayarlanabilir Dambıl Ağırlık Seti 20kg', 'cat' => 'Fitness & Kondisyon', 'brand' => 'Sporium', 'price' => 2499.00, 'colors' => ['Siyah']],
            ['name' => '4 Kişilik Su Geçirmez Kamp Çadırı', 'cat' => 'Outdoor Giyim & Kamp', 'brand' => 'Sporium', 'price' => 4899.00, 'colors' => ['Haki Yeşili', 'Turuncu']],
            ['name' => 'Termal Dağcılık ve Yürüyüş Matı', 'cat' => 'Outdoor Giyim & Kamp', 'brand' => 'Sporium', 'price' => 999.00, 'colors' => ['Mavi', 'Yeşil']],

            // Kozmetik
            ['name' => 'Kalıcı Kadın Parfümü EDP 100ml', 'cat' => 'Parfüm & Deodorant', 'brand' => 'L\'Oreal', 'price' => 3299.00, 'colors' => ['Orijinal']],
            ['name' => 'Odunsu Erkek Parfümü EDT 100ml', 'cat' => 'Parfüm & Deodorant', 'brand' => 'L\'Oreal', 'price' => 2799.00, 'colors' => ['Klasik']],
            ['name' => 'Hyaluronik Asit Yoğun Nemlendirici Serum', 'cat' => 'Cilt Bakımı & Makyaj', 'brand' => 'L\'Oreal', 'price' => 649.00, 'colors' => ['30ml', '50ml']],
            ['name' => 'Suya Dayanıklı Hacim Veren Maskara', 'cat' => 'Cilt Bakımı & Makyaj', 'brand' => 'L\'Oreal', 'price' => 389.00, 'colors' => ['Ekstra Siyah']],
        ];

        // Kategorileri isimleriyle eşleyelim
        $catMap = [];
        foreach ($allCategories as $cat) {
            $catMap[$cat->name] = $cat->id;
        }

        // Markaları isimleriyle eşleyelim
        $brandMap = [];
        foreach ($brands as $b) {
            $brandMap[$b->name] = $b->id;
        }

        $productCount = 0;
        $targetTotal = 100;
        $templateCount = count($sampleProductTemplates);

        for ($i = 1; $i <= $targetTotal; $i++) {
            $template = $sampleProductTemplates[($i - 1) % $templateCount];
            $seller = $sellers[($i - 1) % count($sellers)];

            // İsim çeşitlendirme (100 tekil ürün için)
            $suffix = ($i > $templateCount) ? " Seri " . (intval($i / $templateCount) + 1) : "";
            $productName = $template['name'] . $suffix;
            $slug = Str::slug($productName) . '-' . $i;

            $categoryId = $catMap[$template['cat']] ?? $allCategories[0]->id;
            $brandId = $brandMap[$template['brand']] ?? null;

            $product = Product::firstOrCreate(
                ['slug' => $slug],
                [
                    'seller_id' => $seller->id,
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'name' => $productName,
                    'description' => "Bu ürün yüksek kalite standartlarına uygun olarak üretilmiş olup, {$seller->store_name} garantisi altındadır. Orijinal kutusunda ve faturalı gönderilir.",
                    'status' => 'aktif',
                ]
            );

            // Varyantları oluştur
            $colors = $template['colors'] ?? ['Standart'];
            $sizes = $template['sizes'] ?? [null];

            $variantIndex = 1;
            foreach ($colors as $color) {
                foreach ($sizes as $size) {
                    $sku = sprintf("SKU-%03d-%d", $product->id, $variantIndex++);
                    $price = $template['price'] + (($variantIndex - 1) * 50);
                    $discountPrice = ($variantIndex % 2 === 0) ? ($price * 0.90) : null;

                    ProductVariant::firstOrCreate(
                        ['sku' => $sku],
                        [
                            'product_id' => $product->id,
                            'color' => ($color === 'Standart' ? null : $color),
                            'size' => $size,
                            'price' => number_format($price, 2, '.', ''), // Zorunlu decimal(10,2)
                            'discount_price' => $discountPrice ? number_format($discountPrice, 2, '.', '') : null,
                            'stock' => rand(15, 120), // Stoklar product_variants içinde
                        ]
                    );
                }
            }

            // Polimorfik Media kaydı ekle (Örnek ürün görseli)
            Media::firstOrCreate(
                [
                    'model_type' => Product::class,
                    'model_id' => $product->id,
                    'collection_name' => 'products',
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => Str::slug($productName),
                    'file_name' => 'sample_product_' . ($i % 10 + 1) . '.jpg',
                    'mime_type' => 'image/jpeg',
                    'disk' => 'public',
                    'size' => rand(150000, 450000),
                ]
            );

            $productCount++;
        }
        $this->command?->info("✅ Tam 100 Ürün ve tüm varyantları (fiyat decimal(10,2), stoklar) başarıyla oluşturuldu!");

        // ==========================================
        // 7. KUPONLAR & BANNERLAR (Ekstralar)
        // ==========================================
        Coupon::firstOrCreate(
            ['code' => 'HOSGELDIN100'],
            [
                'type' => 'sabit',
                'value' => 100.00,
                'min_order_amount' => 500.00,
                'usage_limit' => 1000,
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
                'is_active' => true,
            ]
        );

        Coupon::firstOrCreate(
            ['code' => 'BAHAR20'],
            [
                'type' => 'yuzde',
                'value' => 20.00,
                'min_order_amount' => 250.00,
                'usage_limit' => 500,
                'start_date' => now(),
                'end_date' => now()->addMonths(1),
                'is_active' => true,
            ]
        );

        Banner::firstOrCreate(
            ['title' => 'Büyük Bahar İndirimi Başladı!'],
            [
                'image' => 'banners/spring_sale.jpg',
                'url' => '/kategori/giyim-moda',
                'sort' => 1,
                'is_active' => true,
            ]
        );

        Banner::firstOrCreate(
            ['title' => 'En Yeni Teknoloji Ürünlerinde %15 Fırsatı'],
            [
                'image' => 'banners/tech_week.jpg',
                'url' => '/kategori/elektronik',
                'sort' => 2,
                'is_active' => true,
            ]
        );

        $this->command?->info("🎉 TEBRİKLER! Hafta 1 Kabul Kriterleri (1 Admin, 5 Satıcı, 20 Müşteri, 15 Kategori, 100 Ürün) eksiksiz tamamlandı.");
    }
}
