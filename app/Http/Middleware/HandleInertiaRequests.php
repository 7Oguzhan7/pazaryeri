<?php

namespace App\Http\Middleware;

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
                    ]
                    : null,
            ],
            'categories' => fn (): array => Category::query()
                ->select(['id', 'name', 'sort'])
                ->whereNull('parent_id')
                ->with(['children:id,parent_id,name,sort'])
                ->orderBy('sort')
                ->orderBy('name')
                ->get()
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'children' => $category->children
                        ->map(fn (Category $child): array => [
                            'id' => $child->id,
                            'name' => $child->name,
                        ])
                        ->all(),
                ])
                ->all(),
            'flash' => [
                'status' => fn (): ?string => $request->session()->get('status'),
            ],
        ];
    }
}
