<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Seller;
use App\Models\User;

class ProductPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('seller')
            && $user->seller?->status === Seller::STATUS_APPROVED;
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasRole('seller')
            && (int) $product->seller?->user_id === $user->id
            && $product->seller?->status === Seller::STATUS_APPROVED;
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }

    public function uploadMedia(User $user, Product $product): bool
    {
        return $user->hasRole('admin')
            || $this->update($user, $product);
    }
}
