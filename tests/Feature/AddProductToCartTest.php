<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AddProductToCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_add_a_product_variant_to_their_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(stock: 5);
        $variant = $product->variants()->firstOrFail();

        $this->actingAs($user)->post(route('cart.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ])->assertRedirect();

        $cart = Cart::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);
    }

    public function test_adding_an_existing_variant_increments_its_cart_quantity(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(stock: 5);
        $variant = $product->variants()->firstOrFail();
        $cart = Cart::query()->create(['user_id' => $user->id]);
        $cart->items()->create(['variant_id' => $variant->id, 'quantity' => 2]);

        $this->actingAs($user)->post(route('cart.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => 4,
        ]);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_customer_cannot_add_more_units_than_available_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(stock: 2);

        $this->actingAs($user)->from('/')->post(route('cart.items.store'), [
            'variant_id' => $product->variants()->firstOrFail()->id,
            'quantity' => 3,
        ])->assertRedirect('/')
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('carts', 0);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_can_add_a_variant_to_a_session_cart(): void
    {
        $product = $this->createProduct(stock: 5);
        $variant = $product->variants()->firstOrFail();

        $this->withSession(['_token' => 'test-token'])
            ->post(route('cart.items.store'), [
                '_token' => 'test-token',
                'variant_id' => $variant->id,
                'quantity' => 1,
            ])->assertRedirect();

        $this->assertDatabaseHas('carts', [
            'user_id' => null,
            'session_id' => session()->getId(),
        ]);
        $this->assertDatabaseHas('cart_items', [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);
    }

    public function test_product_detail_updates_the_selected_variant_without_a_page_reload(): void
    {
        $product = $this->createProduct(stock: 5);
        $product->variants()->create([
            'sku' => 'ALT-SKU',
            'color' => 'Mavi',
            'size' => 'M',
            'price' => '250.00',
            'discount_price' => '200.00',
            'stock' => 3,
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('product.variants.0.activePrice', '100.00')
                ->where('product.variants.1.activePrice', '200.00')
                ->where('product.variants.1.stock', 3));
    }

    private function createProduct(int $stock): Product
    {
        $sellerUser = User::factory()->create();
        $seller = Seller::query()->create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Test Mağazası',
            'slug' => 'test-magazasi',
            'tax_number' => '1234567890',
            'iban' => 'TR120006200000000000000001',
            'status' => 'onayli',
        ]);
        $category = Category::query()->create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);
        $product = Product::query()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Test Ürünü',
            'slug' => 'test-urunu',
            'description' => 'Test açıklaması',
            'status' => 'aktif',
        ]);
        $product->variants()->create([
            'sku' => 'TEST-SKU',
            'price' => '100.00',
            'stock' => $stock,
        ]);

        return $product;
    }
}
