<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Storefront\GetProductFilterOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CatalogFilterRequest;
use App\Http\Resources\ProductCardResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class CategoryPageController extends Controller
{
    public function __invoke(
        CatalogFilterRequest $request,
        string $slug,
        GetProductFilterOptions $filterOptions,
    ): Response {
        $category = Category::query()
            ->with('children:id,parent_id,name,slug,sort')
            ->where('slug', $slug)
            ->firstOrFail();

        $categoryIds = [$category->id, ...$category->children->pluck('id')->all()];
        $sort = $request->catalogSort();
        $filters = $request->catalogFilters();
        $filters['category'] = null;

        $productsQuery = Product::query()
            ->whereIn('category_id', $categoryIds)
            ->storefrontAvailable()
            ->withStorefrontFilters($filters)
            ->with([
                'variants' => fn ($query) => $query->where('stock', '>', 0),
                'media',
            ])
            ->withAvg(['reviews as average_rating' => fn ($query) => $query->where('is_approved', true)], 'rating')
            ->withCount(['reviews as reviews_count' => fn ($query) => $query->where('is_approved', true)]);

        if (in_array($sort, ['price_asc', 'price_desc'], true)) {
            $price = ProductVariant::query()
                ->selectRaw('MIN(CASE WHEN discount_price IS NOT NULL AND discount_price > 0 AND discount_price < price THEN discount_price ELSE price END)')
                ->whereColumn('product_id', 'products.id')
                ->where('stock', '>', 0);

            $productsQuery->orderBy($price, $sort === 'price_asc' ? 'asc' : 'desc');
        } elseif ($sort === 'rating') {
            $productsQuery
                ->orderByDesc('reviews_count')
                ->orderByDesc('average_rating');
        } else {
            $productsQuery->orderByDesc('products.created_at');
        }

        $productsQuery->orderByDesc('products.id');
        $products = $productsQuery->paginate(24)->withQueryString();

        return Inertia::render('Storefront/Categories/Show', [
            'category' => [
                'name' => $category->name,
                'slug' => $category->slug,
                'children' => $category->children->map(fn (Category $child): array => [
                    'name' => $child->name,
                    'slug' => $child->slug,
                ]),
            ],
            'sort' => $sort,
            'filters' => [
                'category' => null,
                'brand' => $filters['brand'],
                'min' => $filters['min'],
                'max' => $filters['max'],
                'color' => $filters['color'],
                'size' => $filters['size'],
                'rating' => $filters['rating'],
            ],
            'filterOptions' => $filterOptions->handle(),
            'products' => $this->paginatedProducts($products),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function paginatedProducts(LengthAwarePaginator $products): array
    {
        return [
            'data' => ProductCardResource::collection($products->getCollection())->resolve(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'links' => $products->linkCollection(),
        ];
    }
}
