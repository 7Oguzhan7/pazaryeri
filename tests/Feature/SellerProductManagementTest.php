<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SellerProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_seller_can_create_product_with_variants_and_stock(): void
    {
        $seller = $this->createSeller();
        $category = $this->createCategory();
        $brand = Brand::query()->create(['name' => 'Acme', 'slug' => 'acme']);

        $this->actingAs($seller)->post(route('seller.products.store'), [
            'name' => 'Kablosuz Kulaklık',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'description' => 'Gürültü engelleme özellikli kulaklık.',
            'variants' => [
                [
                    'sku' => 'ACME-BLACK',
                    'color' => 'Siyah',
                    'size' => 'Standart',
                    'price' => '250.00',
                    'discount_price' => '200.00',
                    'stock' => 12,
                ],
                [
                    'sku' => 'ACME-WHITE',
                    'color' => 'Beyaz',
                    'size' => 'Standart',
                    'price' => '250.00',
                    'discount_price' => '',
                    'stock' => 5,
                ],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('products', [
            'seller_id' => $seller->seller->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Kablosuz Kulaklık',
            'slug' => 'kablosuz-kulaklik',
            'status' => 'aktif',
        ]);
        $this->assertDatabaseHas('product_variants', [
            'sku' => 'ACME-BLACK',
            'discount_price' => '200.00',
            'stock' => 12,
        ]);
        $this->assertDatabaseCount('product_variants', 2);
    }

    public function test_seller_can_update_product_details_and_variant_stock(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct($seller);
        $variant = $product->variants()->firstOrFail();

        $this->actingAs($seller)->put(route('seller.products.update', $product), [
            'name' => 'Güncel Ürün Adı',
            'category_id' => $product->category_id,
            'brand_id' => '',
            'description' => 'Güncel ürün açıklaması',
            'variants' => [
                [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'color' => 'Lacivert',
                    'size' => 'L',
                    'price' => '150.00',
                    'discount_price' => '125.00',
                    'stock' => 21,
                ],
                [
                    'sku' => 'NEW-VARIANT-SKU',
                    'color' => 'Gri',
                    'size' => 'M',
                    'price' => '175.00',
                    'discount_price' => '',
                    'stock' => 3,
                ],
            ],
        ])->assertRedirect(route('seller.products.edit', $product));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Güncel Ürün Adı',
            'description' => 'Güncel ürün açıklaması',
        ]);
        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'color' => 'Lacivert',
            'stock' => 21,
            'discount_price' => '125.00',
        ]);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => 'NEW-VARIANT-SKU',
            'stock' => 3,
        ]);
    }

    public function test_seller_can_activate_and_deactivate_their_product(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct($seller);
        $product->update(['status' => 'pasif']);

        $this->actingAs($seller)->patch(route('seller.products.status', $product), [
            'status' => 'aktif',
        ])->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'aktif']);
        $this->get(route('products.show', $product->slug))->assertOk();

        $this->actingAs($seller)->patch(route('seller.products.status', $product), [
            'status' => 'pasif',
        ])->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'pasif']);
        $this->get(route('products.show', $product->slug))->assertNotFound();
    }

    public function test_seller_cannot_edit_or_delete_another_sellers_product(): void
    {
        $seller = $this->createSeller('owner@example.test', 'owner-store');
        $otherSeller = $this->createSeller('other@example.test', 'other-store');
        $product = $this->createProduct($otherSeller);

        $this->actingAs($seller)->get(route('seller.products.edit', $product))->assertForbidden();
        $this->actingAs($seller)->put(route('seller.products.update', $product), [
            'name' => 'Yetkisiz değişiklik',
            'category_id' => $product->category_id,
            'variants' => [],
        ])->assertForbidden();
        $this->actingAs($seller)->delete(route('seller.products.destroy', $product))->assertForbidden();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $product->name,
        ]);
    }

    public function test_seller_can_delete_a_product_without_order_history(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct($seller);

        $this->actingAs($seller)->delete(route('seller.products.destroy', $product))
            ->assertRedirect(route('seller.products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_variants', ['product_id' => $product->id]);
    }

    public function test_product_with_order_history_is_deactivated_instead_of_deleted(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct($seller);
        $this->attachOrderItem($product, $seller->seller);

        $this->actingAs($seller)->delete(route('seller.products.destroy', $product))
            ->assertRedirect(route('seller.products.index'))
            ->assertSessionHas('status', 'seller-product-kept-for-order-history');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'pasif']);
        $this->assertDatabaseHas('order_items', ['variant_id' => $product->variants()->firstOrFail()->id]);
    }

    public function test_seller_product_form_exposes_categories_and_brands(): void
    {
        $seller = $this->createSeller();
        $category = $this->createCategory();
        $brand = Brand::query()->create(['name' => 'Acme', 'slug' => 'acme']);

        $this->actingAs($seller)->get(route('seller.products.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Seller/Products/Create')
                ->where('categories.0.id', $category->id)
                ->where('brands.0.id', $brand->id));
    }

    public function test_product_policy_limits_management_to_approved_owner_sellers(): void
    {
        $owner = $this->createSeller();
        $otherSeller = $this->createSeller('other@example.test', 'other-store');
        $product = $this->createProduct($owner);
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $customer = User::factory()->create();
        $customer->assignRole(Role::findOrCreate('customer', 'web'));
        $pendingSeller = User::factory()->create();
        $pendingSeller->assignRole(Role::findOrCreate('seller', 'web'));
        $pendingSeller->seller()->create([
            'store_name' => 'Bekleyen Mağaza',
            'slug' => 'pending-policy-store',
            'tax_number' => '1234567891',
            'iban' => 'TR470000000000000000000000',
            'status' => Seller::STATUS_PENDING,
        ]);

        $this->assertTrue($owner->can('create', Product::class));
        $this->assertTrue($owner->can('update', $product));
        $this->assertTrue($owner->can('delete', $product));
        $this->assertFalse($otherSeller->can('update', $product));
        $this->assertFalse($pendingSeller->can('create', Product::class));
        $this->assertFalse($customer->can('update', $product));
        $this->assertFalse($admin->can('update', $product));
        $this->assertTrue($admin->can('uploadMedia', $product));
    }

    private function createSeller(string $email = 'seller@example.test', string $slug = 'test-store'): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'email_verified_at' => now(),
        ]);
        $user->assignRole(Role::findOrCreate('seller', 'web'));
        $user->seller()->create([
            'store_name' => ucfirst($slug),
            'slug' => $slug,
            'tax_number' => str_pad((string) $user->id, 10, '0', STR_PAD_LEFT),
            'iban' => 'TR470000000000000000000000',
            'status' => Seller::STATUS_APPROVED,
        ]);

        return $user;
    }

    private function createCategory(): Category
    {
        return $this->createCategoryForSlug('elektronik');
    }

    private function createCategoryForSlug(string $slug): Category
    {
        return Category::query()->create([
            'name' => 'Elektronik',
            'slug' => $slug,
        ]);
    }

    private function createProduct(User $seller): Product
    {
        $category = $this->createCategoryForSlug('elektronik-'.$seller->id);
        $product = Product::query()->create([
            'seller_id' => $seller->seller->id,
            'category_id' => $category->id,
            'name' => 'Test Ürünü',
            'slug' => 'test-urunu-'.$seller->id,
            'description' => 'Test açıklaması',
            'status' => 'aktif',
        ]);
        $product->variants()->create([
            'sku' => 'TEST-SKU-'.$seller->id,
            'price' => '100.00',
            'stock' => 5,
        ]);

        return $product;
    }

    private function attachOrderItem(Product $product, Seller $seller): void
    {
        $customer = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_number' => 'ORDER-'.$product->id,
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
            'sub_order_number' => 'SUB-'.$product->id,
            'status' => 'teslim_edildi',
        ]);

        OrderItem::query()->create([
            'sub_order_id' => $subOrder->id,
            'variant_id' => $product->variants()->firstOrFail()->id,
            'product_name' => $product->name,
            'price' => '100.00',
            'quantity' => 1,
        ]);
    }
}
