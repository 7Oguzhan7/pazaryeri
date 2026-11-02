<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StoreCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_category_list_and_create_form(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Category::query()->create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'sort' => 1,
        ]);

        $this->actingAs($admin)
            ->get('/admin/categories')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Categories/Index')
                ->where('categories.data.0.name', 'Elektronik'));

        $this->get('/admin/categories/create')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Categories/Create'));
    }

    public function test_admin_can_create_a_category_with_a_parent_and_slug(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $parent = Category::query()->create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'sort' => 1,
        ]);

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Telefonlar',
            'slug' => 'telefonlar',
            'parent_id' => $parent->id,
            'sort' => 2,
            'commission_rate' => 8.5,
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Telefonlar',
            'slug' => 'telefonlar',
            'parent_id' => $parent->id,
            'sort' => 2,
            'commission_rate' => 8.5,
        ]);
    }

    public function test_admin_can_create_a_category_with_a_slug_generated_from_its_name(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Telefon & Aksesuar',
            'sort' => 1,
            'commission_rate' => 8,
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Telefon & Aksesuar',
            'slug' => 'telefon-aksesuar',
        ]);
    }

    public function test_category_creation_rejects_a_duplicate_slug_in_turkish(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Category::query()->create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'sort' => 1,
        ]);

        $response = $this->actingAs($admin)->from('/admin/categories/create')->post('/admin/categories', [
            'name' => 'Yeni Elektronik',
            'slug' => 'elektronik',
            'sort' => 2,
        ]);

        $response->assertSessionHasErrors([
            'slug' => 'slug alanı zaten alınmış.',
        ]);
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_category_cannot_be_created_under_an_existing_subcategory(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $parent = Category::query()->create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'sort' => 1,
        ]);
        $child = Category::query()->create([
            'parent_id' => $parent->id,
            'name' => 'Telefonlar',
            'slug' => 'telefonlar',
            'sort' => 2,
        ]);

        $this->actingAs($admin)
            ->from('/admin/categories/create')
            ->post('/admin/categories', [
                'name' => 'Akıllı Telefonlar',
                'parent_id' => $child->id,
                'sort' => 3,
                'commission_rate' => 8,
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseCount('categories', 2);
    }

    public function test_non_admin_cannot_create_a_category(): void
    {
        $this->seed(RoleSeeder::class);
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $this->actingAs($customer)
            ->post('/admin/categories', ['name' => 'Yetkisiz'])
            ->assertForbidden();
        $this->assertDatabaseCount('categories', 0);
    }
}
