<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class CategoryPageController extends Controller
{
    public function __invoke(Request $request, string $slug): Response
    {
        $category = Category::query()
            ->with('children:id,parent_id,name,slug,sort')
            ->where('slug', $slug)
            ->firstOrFail();

        $categoryIds = [$category->id, ...$category->children->pluck('id')->all()];
        $sort = $request->string('sort')->toString();
        $sort = in_array($sort, ['price_asc', 'price_desc', 'newest', 'rating'], true)
            ? $sort
            : 'newest';

        $productsQuery = Product::query()
            ->whereIn('category_id', $categoryIds)
            ->where('status', 'aktif')
            ->whereHas('seller', fn ($query) => $query->where('status', Seller::STATUS_APPROVED))
            ->whereHas('variants', fn ($query) => $query->where('stock', '>', 0))
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
