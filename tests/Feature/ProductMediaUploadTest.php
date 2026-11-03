<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductMediaUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_product_owner_can_upload_multiple_product_images(): void
    {
        Storage::fake('public');
        $seller = $this->createSeller();
        $product = $this->createProduct($seller);

        $this->actingAs($seller)->post(route('products.media.store', $product), [
            'images' => [
                UploadedFile::fake()->image('front.jpg', 600, 600),
                UploadedFile::fake()->image('side.jpg', 600, 600),
            ],
        ])->assertRedirect(route('products.show', $product->slug));

        $this->assertDatabaseCount('media', 2);
        $media = $product->fresh()->getMedia('products');
        $this->assertCount(2, $media);
        $this->assertTrue($media[0]->hasGeneratedConversion('thumb'));
        $this->assertTrue(Storage::disk('public')->exists($media[0]->getPathRelativeToRoot('thumb')));
    }

    public function test_product_image_upload_rejects_non_images(): void
    {
        Storage::fake('public');
        $seller = $this->createSeller();
        $product = $this->createProduct($seller);

        $this->actingAs($seller)->from(route('products.show', $product->slug))
            ->post(route('products.media.store', $product), [
                'images' => [UploadedFile::fake()->create('not-an-image.txt', 20, 'text/plain')],
            ])
            ->assertRedirect(route('products.show', $product->slug))
            ->assertSessionHasErrors(['images.0' => 'Yüklenen dosyaların tamamı görsel olmalıdır.']);

        $this->assertDatabaseCount('media', 0);
    }

    public function test_product_image_upload_rejects_an_extension_that_does_not_match_the_image(): void
    {
        Storage::fake('public');
        $seller = $this->createSeller();
        $product = $this->createProduct($seller);

        $this->actingAs($seller)->from(route('products.show', $product->slug))
            ->post(route('products.media.store', $product), [
                'images' => [UploadedFile::fake()->image('photo.txt', 600, 600)],
            ])
            ->assertRedirect(route('products.show', $product->slug))
            ->assertSessionHasErrors(['images.0' => 'Görsel dosyasının uzantısı içeriğiyle eşleşmelidir.']);

        $this->assertDatabaseCount('media', 0);
    }

    public function test_only_the_product_owner_or_admin_can_upload_images(): void
    {
        Storage::fake('public');
        $seller = $this->createSeller();
        $otherSeller = $this->createSeller('other-store', 'other-seller@example.test');
        $product = $this->createProduct($seller);

        $this->actingAs($otherSeller)
            ->post(route('products.media.store', $product), [
                'images' => [UploadedFile::fake()->image('unauthorized.jpg')],
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('media', 0);
    }

    public function test_guests_are_redirected_to_login_before_uploading_product_images(): void
    {
        Storage::fake('public');
        $seller = $this->createSeller();
        $product = $this->createProduct($seller);

        $this->post(route('products.media.store', $product), [
            'images' => [UploadedFile::fake()->image('guest.jpg')],
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('media', 0);
    }

    private function createSeller(string $storeName = 'Test Mağazası', string $email = 'seller@example.test'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(Role::findOrCreate('seller', 'web'));
        Seller::query()->create([
            'user_id' => $user->id,
            'store_name' => $storeName,
            'slug' => str_replace(' ', '-', strtolower($storeName)),
            'tax_number' => '1234567890',
            'iban' => 'TR120006200000000000000001',
            'status' => 'onayli',
        ]);

        return $user;
    }

    private function createProduct(User $seller): Product
    {
        $category = Category::query()->create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        return Product::query()->create([
            'seller_id' => $seller->seller->id,
            'category_id' => $category->id,
            'name' => 'Test Ürünü',
            'slug' => 'test-urunu',
            'description' => 'Test açıklaması',
            'status' => 'aktif',
        ]);
    }
}
