<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\StoreSellerApplicationRequest;
use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function create(Request $request): Response
    {
        $seller = $request->user()->seller;

        return Inertia::render('Seller/Application', [
            'application' => $seller
                ? [
                    'storeName' => $seller->store_name,
                    'status' => $seller->status,
                    'taxNumber' => $seller->tax_number,
                    'iban' => $seller->iban,
                ]
                : null,
        ]);
    }

    public function store(StoreSellerApplicationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $seller = $user->seller;

        if ($seller && $seller->status !== Seller::STATUS_REJECTED) {
            abort(409);
        }

        $storeName = $request->validated('store_name');
        $application = [
            'store_name' => $storeName,
            'slug' => $this->uniqueStoreSlug($storeName, $user->id),
            'tax_number' => $request->validated('tax_number'),
            'iban' => $request->validated('iban'),
            'status' => Seller::STATUS_PENDING,
        ];

        if ($seller) {
            $seller->update($application);
        } else {
            $seller = $user->seller()->create($application);
        }
        $user->setRelation('seller', $seller);

        return redirect()
            ->route('seller.application.create')
            ->with('status', 'seller-application-submitted');
    }

    private function uniqueStoreSlug(string $storeName, int $userId): string
    {
        $baseSlug = Str::slug($storeName) ?: 'magaza';
        $slug = $baseSlug;
        $suffix = 2;

        while (Seller::query()->where('slug', $slug)->where('user_id', '!=', $userId)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
