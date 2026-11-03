<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StorefrontCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_active_banners_featured_categories_best_sellers_and_new_arrivals(): void
    {
        $category = $this->createCategory('Elektronik', 'elektronik');
        $seller = $this->createSeller();
        $bestSeller = $this->createProduct($category, $seller, 'best-seller', 'En Çok Satan', '100.00');
        $newArrival = $this->createProduct($category, $seller, 'new-arrival', 'Yeni Ürün', '200.00');
        Banner::query()->create([
            'title' => 'Aktif kampanya',
            'image' => 'banners/missing-image.jpg',
            'url' => '/kategori/elektronik',
            'sort' => 1,
            'is_active' => true,
        ]);
        Banner::query()->create([
            'title' => 'Pasif kampanya',
            'image' => 'banners/inactive.jpg',
            'sort' => 2,
            'is_active' => false,
        ]);
        $this->recordSale($bestSeller, $seller, 4);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Storefront/Home')
                ->has('banners', 1)
                ->where('banners.0.title', 'Aktif kampanya')
                ->has('featuredCategories', 1)
                ->where('featuredCategories.0.slug', 'elektronik')
                ->where('bestSellers.0.slug', 'best-seller')
                ->where('bestSellers.0.soldUnits', 4)
                ->where('newArrivals.0.slug', 'new-arrival'));
    }

    public function test_category_page_paginates_active_products_in_pages_of_24(): void
    {
        $category = $this->createCategory('Elektronik', 'elektronik');
        $seller = $this->createSeller();

        for ($index = 1; $index <= 25; $index++) {
            $this->createProduct(
                $category,
                $seller,
                "product-{$index}",
                "Ürün {$index}",
                number_format($index * 10, 2, '.', ''),
            );
        }

        $response = $this->get('/kategori/elektronik');

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Storefront/Categories/Show')
                ->where('products.per_page', 24)
                ->where('products.total', 25)
                ->has('products.data', 24));
    }

    public function test_category_page_sorts_by_discounted_price_and_approved_rating(): void
    {
        $category = $this->createCategory('Elektronik', 'elektronik');
        $seller = $this->createSeller();
        $expensive = $this->createProduct($category, $seller, 'expensive', 'Pahalı', '500.00');
        $discounted = $this->createProduct($category, $seller, 'discounted', 'İndirimli', '400.00');
        $discounted->variants()->firstOrFail()->update(['discount_price' => '100.00']);
        $bestRated = $this->createProduct($category, $seller, 'best-rated', 'En Yüksek Puanlı', '300.00');
        $this->recordReview($bestRated, $seller, 5, true);
        $this->recordReview($expensive, $seller, 2, true);
        $this->recordReview($expensive, $seller, 4, true, 2);
        $this->recordReview($expensive, $seller, 4, true, 3);
        $this->recordReview($discounted, $seller, 1, false);

        $this->get('/kategori/elektronik?sort=price_asc')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.data.0.slug', 'discounted')
                ->where('products.data.0.price', '100.00')
                ->where('products.data.0.originalPrice', '400.00'));

        $this->get('/kategori/elektronik?sort=price_desc')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.data.0.slug', 'expensive'));

        $this->get('/kategori/elektronik?sort=rating')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.data.0.slug', 'expensive')
                ->where('products.data.0.reviewCount', 3)
                ->where('products.data.0.averageRating', 3.3));

        $this->get('/kategori/elektronik?sort=newest')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.data.0.slug', 'best-rated'));

        $this->get('/kategori/elektronik?sort=price_desc%3Bdrop%20table%20products')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('sort', 'newest')
                ->where('products.data.0.slug', 'best-rated'));
    }

    public function test_category_page_includes_products_from_child_categories(): void
    {
        $parent = $this->createCategory('Elektronik', 'elektronik');
        $child = Category::query()->create([
            'parent_id' => $parent->id,
            'name' => 'Telefonlar',
            'slug' => 'telefonlar',
            'sort' => 1,
        ]);
        $seller = $this->createSeller();
        $product = $this->createProduct($child, $seller, 'child-product', 'Alt Kategori Ürünü', '300.00');

        $this->get('/kategori/elektronik')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.data.0.slug', $product->slug));
    }

    public function test_product_detail_displays_gallery_variants_and_seller_information(): void
    {
        Storage::fake('public');
        $category = $this->createCategory('Elektronik', 'elektronik');
        $seller = $this->createSeller('Teknoloji Dünyası');
        $product = $this->createProduct($category, $seller, 'test-phone', 'Deneme Telefonu', '200.00');
        $product->addMedia(UploadedFile::fake()->image('phone.jpg', 600, 600))->toMediaCollection('products');
        $product->variants()->firstOrFail()->update([
            'color' => 'Siyah',
            'size' => '128 GB',
            'price' => '250.00',
            'discount_price' => '225.00',
            'stock' => 7,
        ]);
        $product->variants()->create([
            'sku' => 'TEST-PHONE-BLUE',
            'color' => 'Mavi',
            'size' => '256 GB',
            'price' => '300.00',
            'stock' => 4,
        ]);

        $this->get('/urun/test-phone')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Storefront/Products/Show')
                ->where('product.name', 'Deneme Telefonu')
                ->where('product.sellerName', 'Teknoloji Dünyası')
                ->has('product.variants', 2)
                ->where('product.variants.0.color', 'Siyah')
                ->where('product.variants.0.discountPrice', '225.00')
                ->where('product.variants.0.stock', 7)
                ->has('product.images', 1));
    }

    public function test_inactive_products_are_not_publicly_visible(): void
    {
        $category = $this->createCategory('Elektronik', 'elektronik');
        $seller = $this->createSeller();
        $product = $this->createProduct($category, $seller, 'hidden-product', 'Gizli Ürün', '100.00');
        $product->update(['status' => 'pasif']);

        $this->get('/urun/hidden-product')->assertNotFound();
        $this->get('/kategori/elektronik')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('products.data', 0));
    }

    public function test_category_product_queries_do_not_grow_per_product(): void
    {
        $category = $this->createCategory('Elektronik', 'elektronik');
        $seller = $this->createSeller();
        $this->createProduct($category, $seller, 'single-product', 'Ürün 1', '100.00');

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get('/kategori/elektronik');
        $singleProductQueryCount = count(DB::getQueryLog());

        DB::flushQueryLog();
        for ($index = 2; $index <= 12; $index++) {
            $this->createProduct(
                $category,
                $seller,
                "product-{$index}",
                "Ürün {$index}",
                number_format($index * 10, 2, '.', ''),
            );
        }

        DB::flushQueryLog();
        $this->get('/kategori/elektronik');
        $multipleProductsQueryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual($singleProductQueryCount + 1, $multipleProductsQueryCount);
    }

    private function createCategory(string $name, string $slug): Category
    {
        return Category::query()->create([
            'name' => $name,
            'slug' => $slug,
            'sort' => 1,
        ]);
    }

    private function createSeller(string $storeName = 'Test Mağazası'): Seller
    {
        $user = User::factory()->create();

        return Seller::query()->create([
            'user_id' => $user->id,
            'store_name' => $storeName,
            'slug' => 'test-magazasi',
            'tax_number' => '1234567890',
            'iban' => 'TR120006200000000000000001',
            'status' => 'onayli',
        ]);
    }

    private function createProduct(
        Category $category,
        Seller $seller,
        string $slug,
        string $name,
        string $price,
    ): Product {
        $product = Product::query()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'description' => 'Ürün açıklaması',
            'status' => 'aktif',
        ]);

        $product->variants()->create([
            'sku' => strtoupper($slug).'-SKU',
            'price' => $price,
            'stock' => 10,
        ]);

        return $product;
    }

    private function recordSale(Product $product, Seller $seller, int $quantity): void
    {
        $customer = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_number' => 'ORDER-'.strtoupper($product->slug),
            'address' => ['city' => 'İstanbul'],
            'subtotal' => '400.00',
            'discount' => '0.00',
            'shipping' => '0.00',
            'total' => '400.00',
            'payment_status' => 'odendi',
        ]);
        $subOrder = SubOrder::query()->create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'sub_order_number' => 'SUB-'.strtoupper($product->slug),
            'status' => 'teslim_edildi',
        ]);
        $variant = $product->variants()->firstOrFail();

        OrderItem::query()->create([
            'sub_order_id' => $subOrder->id,
            'variant_id' => $variant->id,
            'product_name' => $product->name,
            'price' => '100.00',
            'quantity' => $quantity,
        ]);
    }

    private function recordReview(
        Product $product,
        Seller $seller,
        int $rating,
        bool $isApproved,
        int $sequence = 1,
    ): void {
        $customer = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_number' => 'REVIEW-'.strtoupper($product->slug).'-'.$sequence,
            'address' => ['city' => 'İstanbul'],
            'subtotal' => '100.00',
            'discount' => '0.00',
            'shipping' => '0.00',
            'total' => '100.00',
            'payment_status' => 'odendi',
        ]);
        $subOrder = SubOrder::query()->create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'sub_order_number' => 'REVIEW-SUB-'.strtoupper($product->slug).'-'.$sequence,
            'status' => 'teslim_edildi',
        ]);
        $item = OrderItem::query()->create([
            'sub_order_id' => $subOrder->id,
            'variant_id' => $product->variants()->firstOrFail()->id,
            'product_name' => $product->name,
            'price' => '100.00',
            'quantity' => 1,
        ]);

        Review::query()->create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'order_item_id' => $item->id,
            'rating' => $rating,
            'is_approved' => $isApproved,
        ]);
    }
}
