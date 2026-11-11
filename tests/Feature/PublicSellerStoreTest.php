<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicSellerStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_store_shows_only_its_active_in_stock_products(): void
    {
        $seller = $this->createSeller('onayli', 'public-store');
        $visibleProduct = $this->createProduct($seller, 'visible-product', 'Görünür Ürün');
        $inactiveProduct = $this->createProduct($seller, 'inactive-product', 'Pasif Ürün');
        $inactiveProduct->update(['status' => 'pasif']);
        $outOfStockProduct = $this->createProduct($seller, 'out-of-stock', 'Stoksuz Ürün');
        $outOfStockProduct->variants()->update(['stock' => 0]);

        $this->get(route('sellers.show', $seller->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Storefront/Store/Show')
                ->where('store.name', 'Public Store')
                ->where('products.total', 1)
                ->where('products.data.0.slug', $visibleProduct->slug));
    }

    public function test_unapproved_or_unknown_seller_store_is_not_public(): void
    {
        $seller = $this->createSeller('beklemede', 'pending-store');

        $this->get(route('sellers.show', $seller->slug))->assertNotFound();
        $this->get('/magaza/unknown-store')->assertNotFound();
    }

    private function createSeller(string $status, string $slug): Seller
    {
        $user = User::factory()->create();

        return Seller::query()->create([
            'user_id' => $user->id,
            'store_name' => ucwords(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'tax_number' => str_pad((string) $user->id, 10, '0', STR_PAD_LEFT),
            'iban' => 'TR470000000000000000000000',
            'status' => $status,
        ]);
    }

    private function createProduct(Seller $seller, string $slug, string $name): Product
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'electronics'],
            ['name' => 'Elektronik'],
        );
        $product = Product::query()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'status' => 'aktif',
        ]);
        $product->variants()->create([
            'sku' => strtoupper($slug).'-SKU',
            'price' => '100.00',
            'stock' => 5,
        ]);

        return $product;
    }
}
