<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartPageController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $cartQuery = Cart::query()->with(['items.variant.product.seller']);

        if ($request->user()) {
            $cartQuery->where('user_id', $request->user()->id);
        } else {
            $cartQuery->where('session_id', $request->session()->getId());
        }

        $cart = $cartQuery->first();
        $subtotalCents = 0;
        $items = ($cart?->items ?? collect())
            ->map(function (CartItem $item) use (&$subtotalCents): array {
                $variant = $item->variant;
                $product = $variant->product;
                $unitPrice = $this->amountToCents($variant->active_price);
                $lineTotal = $unitPrice * $item->quantity;
                $subtotalCents += $lineTotal;

                return [
                    'id' => $item->id,
                    'productName' => $product->name,
                    'productSlug' => $product->slug,
                    'sellerName' => $product->seller->store_name,
                    'variant' => collect([$variant->color, $variant->size])
                        ->filter()
                        ->implode(' / '),
                    'unitPrice' => $this->formatCents($unitPrice),
                    'quantity' => $item->quantity,
                    'lineTotal' => $this->formatCents($lineTotal),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Storefront/Cart/Show', [
            'items' => $items,
            'subtotal' => $this->formatCents($subtotalCents),
            'cartCount' => array_sum(array_column($items, 'quantity')),
        ]);
    }

    private function amountToCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function formatCents(int $amount): string
    {
        return intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }
}
