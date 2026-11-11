<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Seller;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class SellerStoreController extends Controller
{
    public function __invoke(string $slug): Response
    {
        $seller = Seller::query()
            ->where('slug', $slug)
            ->where('status', Seller::STATUS_APPROVED)
            ->firstOrFail();

        $products = $seller->products()
            ->storefrontAvailable()
            ->with(['variants' => fn ($query) => $query->where('stock', '>', 0), 'media'])
            ->withAvg(['reviews as average_rating' => fn ($query) => $query->where('is_approved', true)], 'rating')
            ->withCount(['reviews as reviews_count' => fn ($query) => $query->where('is_approved', true)])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(24)
            ->withQueryString();

        return Inertia::render('Storefront/Store/Show', [
            'store' => [
                'name' => $seller->store_name,
                'slug' => $seller->slug,
            ],
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
