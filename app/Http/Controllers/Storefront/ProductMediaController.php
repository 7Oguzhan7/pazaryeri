<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductImagesRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;

class ProductMediaController extends Controller
{
    public function store(StoreProductImagesRequest $request, Product $product): RedirectResponse
    {
        foreach ($request->file('images') as $image) {
            $product
                ->addMedia($image)
                ->toMediaCollection('products');
        }

        return to_route('products.show', $product->slug)
            ->with('status', 'product-images-uploaded');
    }
}
