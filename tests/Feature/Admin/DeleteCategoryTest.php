<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_an_unused_category(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $category = Category::query()->create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'sort' => 1,
        ]);

        $this->actingAs($admin)
            ->delete("/admin/categories/{$category->id}")
            ->assertRedirect(route('admin.categories.index'));

        $this->assertModelMissing($category);
    }

    public function test_category_with_children_cannot_be_deleted(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $category = Category::query()->create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'sort' => 1,
        ]);
        Category::query()->create([
            'parent_id' => $category->id,
            'name' => 'Telefonlar',
            'slug' => 'telefonlar',
            'sort' => 2,
        ]);

        $this->actingAs($admin)
            ->from('/admin/categories')
            ->delete("/admin/categories/{$category->id}")
            ->assertSessionHasErrors([
                'category' => 'Alt kategorisi veya ürünü bulunan kategori silinemez.',
            ]);

        $this->assertModelExists($category);
    }

    public function test_category_used_by_a_product_cannot_be_deleted(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $sellerUser = User::factory()->create();
        $seller = Seller::query()->create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Örnek Mağaza',
            'slug' => 'ornek-magaza',
            'tax_number' => '1234567890',
            'iban' => 'TR120006200000000000000001',
            'status' => 'onayli',
        ]);
        $category = Category::query()->create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'sort' => 1,
        ]);
        Product::query()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Telefon',
            'slug' => 'telefon',
        ]);

        $this->actingAs($admin)
            ->from('/admin/categories')
            ->delete("/admin/categories/{$category->id}")
            ->assertSessionHasErrors([
                'category' => 'Alt kategorisi veya ürünü bulunan kategori silinemez.',
            ]);

        $this->assertModelExists($category);
    }
}
