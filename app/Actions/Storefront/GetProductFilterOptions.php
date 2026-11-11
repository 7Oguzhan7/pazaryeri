<?php

namespace App\Actions\Storefront;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductVariant;

class GetProductFilterOptions
{
    /**
     * @return array{categories: array<int, array{id: int, name: string, slug: string}>, brands: array<int, array{id: int, name: string, slug: string}>, colors: list<string>, sizes: list<string>}
     */
    public function handle(): array
    {
        return [
            'categories' => Category::query()
                ->whereHas('products', fn ($query) => $query->storefrontAvailable())
                ->orderBy('name')
                ->get(['id', 'name', 'slug'])
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])
                ->all(),
            'brands' => Brand::query()
                ->whereHas('products', fn ($query) => $query->storefrontAvailable())
                ->orderBy('name')
                ->get(['id', 'name', 'slug'])
                ->map(fn (Brand $brand): array => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                ])
                ->all(),
            'colors' => ProductVariant::query()
                ->whereHas('product', fn ($query) => $query->storefrontAvailable())
                ->where('stock', '>', 0)
                ->whereNotNull('color')
                ->where('color', '!=', '')
                ->distinct()
                ->orderBy('color')
                ->pluck('color')
                ->all(),
            'sizes' => ProductVariant::query()
                ->whereHas('product', fn ($query) => $query->storefrontAvailable())
                ->where('stock', '>', 0)
                ->whereNotNull('size')
                ->where('size', '!=', '')
                ->distinct()
                ->orderBy('size')
                ->pluck('size')
                ->all(),
        ];
    }
}
