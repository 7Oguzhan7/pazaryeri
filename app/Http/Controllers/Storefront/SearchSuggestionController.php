<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchSuggestionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);
        $search = trim($validated['q']);

        $suggestions = Product::query()
            ->storefrontAvailable()
            ->matchingStorefrontSearch($search)
            ->with('brand:id,name')
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'brand_id', 'name', 'slug'])
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'brand' => $product->brand?->name,
                'slug' => $product->slug,
            ]);

        return response()->json($suggestions);
    }
}
