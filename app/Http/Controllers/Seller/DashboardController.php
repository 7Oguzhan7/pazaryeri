<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $products = $request->user()->seller
            ->products()
            ->with('category:id,name')
            ->orderByDesc('created_at')
            ->get(['id', 'category_id', 'name', 'slug', 'status'])
            ->map(fn ($product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'status' => $product->status,
                'categoryName' => $product->category->name,
            ]);

        return Inertia::render('Seller/Dashboard', [
            'products' => $products,
            'storeUrl' => route('sellers.show', $request->user()->seller->slug),
        ]);
    }
}
