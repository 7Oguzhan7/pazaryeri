<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Seller;
use App\Models\User;

class ProductPolicy
{
    public function uploadMedia(User $user, Product $product): bool
    {
        return $user->hasRole('admin')
            || ($user->hasRole('seller')
                && $product->seller?->user_id === $user->id
                && $product->seller?->status === Seller::STATUS_APPROVED);
    }
}
