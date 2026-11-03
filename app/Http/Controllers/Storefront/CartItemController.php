<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartItemController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $variant = ProductVariant::query()
                ->whereHas('product', fn ($query) => $query
                    ->where('status', 'aktif')
                    ->whereHas('seller', fn ($sellerQuery) => $sellerQuery
                        ->where('status', Seller::STATUS_APPROVED)))
                ->lockForUpdate()
                ->findOrFail($validated['variant_id']);

            $cart = $request->user()
                ? $request->user()->cart()->firstOrCreate()
                : Cart::query()->firstOrCreate([
                    'session_id' => $request->session()->getId(),
                ]);

            $cartItem = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('variant_id', $variant->id)
                ->lockForUpdate()
                ->first();

            $quantity = ($cartItem?->quantity ?? 0) + $validated['quantity'];

            if ($quantity > $variant->stock) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stokta bu adette ürün bulunmuyor.',
                ]);
            }

            if ($cartItem) {
                $cartItem->update(['quantity' => $quantity]);
            } else {
                $cart->items()->create([
                    'variant_id' => $variant->id,
                    'quantity' => $quantity,
                ]);
            }
        });

        return back()->with('status', 'cart-added');
    }
}
