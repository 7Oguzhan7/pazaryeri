<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Banner;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request): Response
    {
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
            ->whereHas('products', fn ($query) => $query
                ->where('status', 'aktif')
                ->whereHas('seller', fn ($sellerQuery) => $sellerQuery
                    ->where('status', Seller::STATUS_APPROVED)))
            ->withCount(['products as active_products_count' => fn ($query) => $query
                ->where('status', 'aktif')
                ->whereHas('seller', fn ($sellerQuery) => $sellerQuery
                    ->where('status', Seller::STATUS_APPROVED))])
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

        return Inertia::render('Storefront/Home', [
            'search' => $request->string('q')->toString(),
            'banners' => $banners,
            'featuredCategories' => $featuredCategories,
            'bestSellers' => ProductCardResource::collection($bestSellers)->resolve(),
            'newArrivals' => ProductCardResource::collection($newArrivals)->resolve(),
        ]);
    }

    private function productCardQuery(): Builder
    {
        return Product::query()
            ->where('products.status', 'aktif')
            ->whereHas('seller', fn ($query) => $query->where('status', Seller::STATUS_APPROVED))
            ->whereHas('variants', fn ($query) => $query->where('stock', '>', 0))
            ->with([
                'variants' => fn ($query) => $query->where('stock', '>', 0),
                'media',
            ])
            ->withAvg(['reviews as average_rating' => fn ($query) => $query->where('is_approved', true)], 'rating')
            ->withCount(['reviews as reviews_count' => fn ($query) => $query->where('is_approved', true)]);
    }
}
