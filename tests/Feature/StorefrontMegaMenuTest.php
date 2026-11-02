<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontMegaMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_categories_from_the_database(): void
    {
        $parent = Category::query()->create([
            'name' => 'Test Elektronik',
            'slug' => 'test-elektronik',
            'sort' => 1,
        ]);
        Category::query()->create([
            'parent_id' => $parent->id,
            'name' => 'Test Telefonlar',
            'slug' => 'test-telefonlar',
            'sort' => 2,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSeeText('Test Elektronik')
            ->assertSeeText('Test Telefonlar');
    }
}
