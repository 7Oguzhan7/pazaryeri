<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Seller;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user
                    ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'isAdmin' => $user->hasRole('admin'),
                        'isApprovedSeller' => $user->hasRole('seller')
                            && $user->seller?->status === Seller::STATUS_APPROVED,
                        'canShop' => $user->canShop(),
                    ]
                    : null,
            ],
            'cartCount' => fn (): int => $this->cartCount($request),
            'categories' => fn (): array => Category::query()
                ->select(['id', 'name', 'slug', 'sort'])
                ->whereNull('parent_id')
                ->with(['children:id,parent_id,name,slug,sort'])
                ->orderBy('sort')
                ->orderBy('name')
                ->get()
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'children' => $category->children
                        ->map(fn (Category $child): array => [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                        ])
                        ->all(),
                ])
                ->all(),
            'flash' => [
                'status' => fn (): ?string => $request->session()->get('status'),
            ],
            'search' => $request->string('q')->toString(),
        ];
    }

    private function cartCount(Request $request): int
    {
        $cartQuery = Cart::query();

        if ($request->user()) {
            $cartQuery->where('user_id', $request->user()->id);
        } else {
            $cartQuery->where('session_id', $request->session()->getId());
        }

        return (int) ($cartQuery->withSum('items', 'quantity')->value('items_sum_quantity') ?? 0);
    }
}
