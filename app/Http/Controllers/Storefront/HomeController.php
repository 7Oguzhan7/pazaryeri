<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Storefront\GetProductFilterOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CatalogFilterRequest;
use App\Http\Resources\ProductCardResource;
use App\Models\Banner;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(
        CatalogFilterRequest $request,
        GetProductFilterOptions $filterOptions,
    ): Response {
        $sales = OrderItem::query()
            ->join('product_variants', 'order_items.variant_id', '=', 'product_variants.id')
            ->join('sub_orders', 'order_items.sub_order_id', '=', 'sub_orders.id')
            ->join('orders', 'sub_orders.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'odendi')
            ->whereNotIn('sub_orders.status', ['iptal', 'iade'])
            ->selectRaw('product_variants.product_id, SUM(order_items.quantity) as sold_units')
            ->groupBy('product_variants.product_id');

        $banners = Banner::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (Banner $banner): array => [
                'id' => $banner->id,
                'title' => $banner->title,
                'url' => $banner->url,
                'imageUrl' => Storage::disk('public')->exists($banner->image)
                    ? Storage::disk('public')->url($banner->image)
                    : null,
            ]);

        $featuredCategories = Category::query()
            ->whereHas('products', fn ($query) => $query->storefrontAvailable())
            ->withCount(['products as active_products_count' => fn ($query) => $query
                ->storefrontAvailable()])
            ->orderByDesc('active_products_count')
            ->orderBy('sort')
            ->limit(6)
            ->get(['id', 'name', 'slug']);

        $bestSellers = $this->productCardQuery()
            ->joinSub($sales, 'product_sales', fn ($join) => $join->on('products.id', '=', 'product_sales.product_id'))
            ->addSelect('product_sales.sold_units')
            ->orderByDesc('product_sales.sold_units')
            ->orderByDesc('products.id')
            ->limit(8)
            ->get();

        $newArrivals = $this->productCardQuery()
            ->orderByDesc('products.created_at')
            ->orderByDesc('products.id')
            ->limit(8)
            ->get();

        $search = trim($request->string('q')->toString());
        $searchResults = null;
        $filters = $request->catalogFilters();
        $sort = $request->catalogSort();

        if ($search !== '') {
            $searchQuery = $this->productCardQuery()
                ->matchingStorefrontSearch($search)
                ->withStorefrontFilters($filters);

            if (in_array($sort, ['price_asc', 'price_desc'], true)) {
                $price = ProductVariant::query()
                    ->selectRaw('MIN(CASE WHEN discount_price IS NOT NULL AND discount_price > 0 AND discount_price < price THEN discount_price ELSE price END)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('stock', '>', 0);

                $searchQuery->orderBy($price, $sort === 'price_asc' ? 'asc' : 'desc');
            } elseif ($sort === 'rating') {
                $searchQuery
                    ->orderByDesc('reviews_count')
                    ->orderByDesc('average_rating');
            } else {
                $searchQuery->orderByDesc('products.created_at');
            }

            $searchResults = $this->paginatedProducts($searchQuery
                ->orderByDesc('products.id')
                ->paginate(24)
                ->withQueryString());
        }

        return Inertia::render('Storefront/Home', [
            'search' => $search,
            'searchResults' => $searchResults,
            'filters' => [
                'category' => $filters['category'],
                'brand' => $filters['brand'],
                'min' => $filters['min'],
                'max' => $filters['max'],
                'color' => $filters['color'],
                'size' => $filters['size'],
                'rating' => $filters['rating'],
            ],
            'sort' => $sort,
            'filterOptions' => $search !== '' ? $filterOptions->handle() : [],
            'banners' => $banners,
            'featuredCategories' => $featuredCategories,
            'bestSellers' => ProductCardResource::collection($bestSellers)->resolve(),
            'newArrivals' => ProductCardResource::collection($newArrivals)->resolve(),
        ]);
    }

    private function productCardQuery(): Builder
    {
        return Product::query()
            ->storefrontAvailable()
            ->with([
                'variants' => fn ($query) => $query->where('stock', '>', 0),
                'media',
            ])
            ->withAvg(['reviews as average_rating' => fn ($query) => $query->where('is_approved', true)], 'rating')
            ->withCount(['reviews as reviews_count' => fn ($query) => $query->where('is_approved', true)]);
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
