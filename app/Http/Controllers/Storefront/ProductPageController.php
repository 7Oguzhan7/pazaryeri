<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductPageController extends Controller
{
    public function __invoke(Request $request, string $slug): Response
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('status', 'aktif')
            ->whereHas('seller', fn ($query) => $query->where('status', Seller::STATUS_APPROVED))
            ->with([
                'category:id,name,slug',
                'seller:id,user_id,store_name,slug,status',
                'variants' => fn ($query) => $query->orderBy('id'),
                'media',
                'reviews' => fn ($query) => $query
                    ->where('is_approved', true)
                    ->with('user:id,name')
                    ->latest()
                    ->limit(10),
            ])
            ->withAvg(['reviews as average_rating' => fn ($query) => $query->where('is_approved', true)], 'rating')
            ->withCount(['reviews as reviews_count' => fn ($query) => $query->where('is_approved', true)])
            ->firstOrFail();

        return Inertia::render('Storefront/Products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'categoryName' => $product->category->name,
                'categorySlug' => $product->category->slug,
                'sellerName' => $product->seller->store_name,
                'sellerSlug' => $product->seller->slug,
                'averageRating' => round((float) ($product->average_rating ?? 0), 1),
                'reviewCount' => (int) $product->reviews_count,
                'images' => $this->images($product),
                'variants' => $product->variants->map(fn ($variant): array => [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'color' => $variant->color,
                    'size' => $variant->size,
                    'price' => $variant->price,
                    'discountPrice' => $variant->discount_price,
                    'activePrice' => $variant->active_price,
                    'stock' => $variant->stock,
                ])->all(),
                'reviews' => $product->reviews->map(fn ($review): array => [
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'reviewer' => $review->user->name,
                    'createdAt' => $review->created_at->toDateString(),
                ])->all(),
            ],
            'canAddToCart' => $request->user() === null || $request->user()->canShop(),
            'canUploadImages' => $request->user()?->can('uploadMedia', $product) ?? false,
        ]);
    }

    /**
     * @return array<int, array{id: int, url: string, previewUrl: string}>
     */
    private function images(Product $product): array
    {
        return $product->media
            ->filter(fn ($media): bool => $media->collection_name === 'products' && is_file($media->getPath()))
            ->map(function ($media): array {
                $preview = $media->hasGeneratedConversion('preview') && is_file($media->getPath('preview'))
                    ? $media->getUrl('preview')
                    : $media->getUrl();

                $thumbnail = $media->hasGeneratedConversion('thumb') && is_file($media->getPath('thumb'))
                    ? $media->getUrl('thumb')
                    : $media->getUrl();

                return [
                    'id' => $media->id,
                    'url' => $thumbnail,
                    'previewUrl' => $preview,
                ];
            })
            ->values()
            ->all();
    }
}
