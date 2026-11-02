<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UpdateCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_category_name_parent_and_sort_order(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $parent = Category::query()->create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'sort' => 1,
        ]);
        $category = Category::query()->create([
            'name' => 'Telefonlar',
            'slug' => 'telefonlar',
            'sort' => 2,
        ]);

        $this->actingAs($admin)
            ->get("/admin/categories/{$category->id}/edit")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Categories/Edit')
                ->where('category.id', $category->id));

        $response = $this->actingAs($admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Cep Telefonları',
            'slug' => 'cep-telefonlari',
            'parent_id' => $parent->id,
            'sort' => 3,
            'commission_rate' => 9,
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Cep Telefonları',
            'slug' => 'cep-telefonlari',
            'parent_id' => $parent->id,
            'sort' => 3,
            'commission_rate' => 9,
        ]);
    }

    public function test_category_with_children_cannot_be_moved_under_another_category(): void
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
        $anotherParent = Category::query()->create([
            'name' => 'Moda',
            'slug' => 'moda',
            'sort' => 3,
        ]);

        $this->actingAs($admin)
            ->from("/admin/categories/{$category->id}/edit")
            ->put("/admin/categories/{$category->id}", [
                'name' => 'Elektronik',
                'slug' => 'elektronik',
                'parent_id' => $anotherParent->id,
                'sort' => 1,
                'commission_rate' => 10,
            ])
            ->assertSessionHasErrors([
                'parent_id' => 'Alt kategorileri olan bir kategorinin üst kategorisi değiştirilemez.',
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'parent_id' => null,
        ]);
    }

    public function test_category_cannot_be_its_own_parent(): void
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
            ->from("/admin/categories/{$category->id}/edit")
            ->put("/admin/categories/{$category->id}", [
                'name' => 'Elektronik',
                'slug' => 'elektronik',
                'parent_id' => $category->id,
                'sort' => 1,
                'commission_rate' => 10,
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'parent_id' => null,
        ]);
    }
}
