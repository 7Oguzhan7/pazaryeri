<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StorefrontSearchAndFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_search_and_autocomplete_match_product_brands(): void
    {
        $category = $this->createCategory('Elektronik', 'elektronik');
        $seller = $this->createSeller('onayli', 'test-magazasi');
        $brand = Brand::query()->create(['name' => 'Acme Audio', 'slug' => 'acme-audio']);
        $product = $this->createProduct($category, $seller, $brand, 'wireless-headphones', 'Kablosuz Kulaklık');
        $hiddenSeller = $this->createSeller('beklemede', 'bekleyen-magaza');
        $this->createProduct($category, $hiddenSeller, $brand, 'hidden-headphones', 'Gizli Kulaklık');

        $this->get('/?q=Acme')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('search', 'Acme')
                ->where('searchResults.total', 1)
                ->where('searchResults.data.0.slug', $product->slug));

        $this->getJson('/arama/oneri?q=Acme')
            ->assertOk()
            ->assertJsonPath('0.name', 'Kablosuz Kulaklık')
            ->assertJsonPath('0.brand', 'Acme Audio')
            ->assertJsonPath('0.slug', $product->slug);
    }

    public function test_search_filters_can_be_shared_as_a_url_and_combine_catalog_criteria(): void
    {
        $parent = $this->createCategory('Elektronik', 'elektronik');
        $phones = $this->createCategory('Telefonlar', 'telefonlar', $parent);
        $seller = $this->createSeller('onayli', 'test-magazasi');
        $brand = Brand::query()->create(['name' => 'Acme', 'slug' => 'acme']);
        $matchingProduct = $this->createProduct($phones, $seller, $brand, 'acme-phone', 'Acme Phone');
        $matchingProduct->variants()->firstOrFail()->update([
            'color' => 'Siyah',
            'size' => 'M',
            'price' => '200.00',
            'discount_price' => '120.00',
        ]);
        $this->recordReview($matchingProduct, $seller, 4);

        $priceMismatch = $this->createProduct($phones, $seller, $brand, 'acme-price-mismatch', 'Acme Phone XL', '300.00', 'Siyah', 'M');
        $colorMismatch = $this->createProduct($phones, $seller, $brand, 'acme-color-mismatch', 'Acme Phone Blue', '120.00', 'Mavi', 'M');
        $otherCategory = $this->createCategory('Ev ve Yaşam', 'ev-yasam');
        $this->createProduct($otherCategory, $seller, $brand, 'acme-other-category', 'Acme Speaker', '120.00', 'Siyah', 'M');

        $url = '/?q=Acme&category=elektronik&marka=acme&min=100&max=150&renk=Siyah&beden=M&puan=4';

        $this->get($url)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('searchResults.total', 1)
                ->where('searchResults.data.0.slug', $matchingProduct->slug)
                ->where('filters.category', 'elektronik')
                ->where('filters.brand', 'acme')
                ->where('filters.min', '100')
                ->where('filters.max', '150')
                ->where('filters.color', 'Siyah')
                ->where('filters.size', 'M')
                ->where('filters.rating', '4'));

        $this->get($url)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('searchResults.data.0.slug', $matchingProduct->slug));

        $this->assertModelExists($priceMismatch);
        $this->assertModelExists($colorMismatch);
    }

    public function test_category_filters_preserve_the_selected_brand_and_price_range(): void
    {
        $category = $this->createCategory('Elektronik', 'elektronik');
        $seller = $this->createSeller('onayli', 'test-magazasi');
        $brand = Brand::query()->create(['name' => 'Acme', 'slug' => 'acme']);
        $matchingProduct = $this->createProduct($category, $seller, $brand, 'acme-budget', 'Acme Budget', '80.00');
        $this->createProduct($category, $seller, $brand, 'acme-premium', 'Acme Premium', '800.00');

        $this->get('/kategori/elektronik?marka=acme&min=50&max=100')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.total', 1)
                ->where('products.data.0.slug', $matchingProduct->slug)
                ->where('filters.brand', 'acme')
                ->where('filters.min', '50')
                ->where('filters.max', '100'));
    }

    public function test_invalid_price_range_is_rejected_with_a_turkish_message(): void
    {
        $this->from('/')->get('/?q=telefon&min=500&max=100')
            ->assertRedirect('/')
            ->assertSessionHasErrors([
                'max' => 'En yüksek fiyat, en düşük fiyattan az olamaz.',
            ]);
    }

    private function createCategory(string $name, string $slug, ?Category $parent = null): Category
    {
        return Category::query()->create([
            'parent_id' => $parent?->id,
            'name' => $name,
            'slug' => $slug,
        ]);
    }

    private function createSeller(string $status, string $slug): Seller
    {
        $user = User::factory()->create();

        return Seller::query()->create([
            'user_id' => $user->id,
            'store_name' => ucfirst($slug),
            'slug' => $slug,
            'tax_number' => str_pad((string) $user->id, 10, '0', STR_PAD_LEFT),
            'iban' => 'TR000000000000000000000000',
            'status' => $status,
        ]);
    }

    private function createProduct(
        Category $category,
        Seller $seller,
        Brand $brand,
        string $slug,
        string $name,
        string $price = '100.00',
        ?string $color = null,
        ?string $size = null,
    ): Product {
        $product = Product::query()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => $name,
            'slug' => $slug,
            'status' => 'aktif',
        ]);
        $product->variants()->create([
            'sku' => strtoupper($slug).'-SKU',
            'color' => $color,
            'size' => $size,
            'price' => $price,
            'stock' => 10,
        ]);

        return $product;
    }

    private function recordReview(Product $product, Seller $seller, int $rating): void
    {
        $customer = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_number' => 'ORDER-'.$product->slug,
            'address' => ['city' => 'İstanbul'],
            'subtotal' => '200.00',
            'discount' => '0.00',
            'shipping' => '0.00',
            'total' => '200.00',
            'payment_status' => 'odendi',
        ]);
        $subOrder = SubOrder::query()->create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'sub_order_number' => 'SUB-'.$product->slug,
            'status' => 'teslim_edildi',
        ]);
        $item = OrderItem::query()->create([
            'sub_order_id' => $subOrder->id,
            'variant_id' => $product->variants()->firstOrFail()->id,
            'product_name' => $product->name,
            'price' => '200.00',
            'quantity' => 1,
        ]);

        Review::query()->create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'order_item_id' => $item->id,
            'rating' => $rating,
            'is_approved' => true,
        ]);
    }
}
