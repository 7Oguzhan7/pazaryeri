<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'seller_id',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'description',
        'status',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeStorefrontAvailable(Builder $query): Builder
    {
        return $query
            ->where('status', 'aktif')
            ->whereHas('seller', fn (Builder $sellerQuery) => $sellerQuery
                ->where('status', Seller::STATUS_APPROVED))
            ->whereHas('variants', fn (Builder $variantQuery) => $variantQuery->where('stock', '>', 0));
    }

    public function scopeMatchingStorefrontSearch(Builder $query, string $search): Builder
    {
        return $query->where(fn (Builder $searchQuery) => $searchQuery
            ->where('name', 'like', "%{$search}%")
            ->orWhereHas('brand', fn (Builder $brandQuery) => $brandQuery
                ->where('name', 'like', "%{$search}%")));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function scopeWithStorefrontFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['category'])) {
            $query->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery
                ->where('slug', $filters['category'])
                ->orWhereHas('parent', fn (Builder $parentQuery) => $parentQuery
                    ->where('slug', $filters['category'])));
        }

        if (! empty($filters['brand'])) {
            $query->whereHas('brand', fn (Builder $brandQuery) => $brandQuery
                ->where('slug', $filters['brand']));
        }

        if (! empty($filters['rating'])) {
            $query->whereRaw(
                'CAST((SELECT AVG(reviews.rating) FROM reviews WHERE reviews.product_id = products.id AND reviews.is_approved = ?) AS DECIMAL(3, 1)) >= CAST(? AS DECIMAL(3, 1))',
                [true, $filters['rating']],
            );
        }

        $hasVariantFilters = ($filters['min'] ?? null) !== null
            || ($filters['max'] ?? null) !== null
            || ! empty($filters['color'])
            || ! empty($filters['size']);

        if ($hasVariantFilters) {
            $query->whereHas('variants', function (Builder $variantQuery) use ($filters): void {
                $variantQuery->where('stock', '>', 0);

                if (! empty($filters['color'])) {
                    $variantQuery->where('color', $filters['color']);
                }

                if (! empty($filters['size'])) {
                    $variantQuery->where('size', $filters['size']);
                }

                if (($filters['min'] ?? null) !== null) {
                    $variantQuery->whereRaw(
                        'CAST((CASE WHEN discount_price IS NOT NULL AND discount_price > 0 AND discount_price < price THEN discount_price ELSE price END) AS DECIMAL(10, 2)) >= CAST(? AS DECIMAL(10, 2))',
                        [$filters['min']],
                    );
                }

                if (($filters['max'] ?? null) !== null) {
                    $variantQuery->whereRaw(
                        'CAST((CASE WHEN discount_price IS NOT NULL AND discount_price > 0 AND discount_price < price THEN discount_price ELSE price END) AS DECIMAL(10, 2)) <= CAST(? AS DECIMAL(10, 2))',
                        [$filters['max']],
                    );
                }
            });
        }

        return $query;
    }

    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, ProductVariant::class, 'product_id', 'variant_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('products')->useDisk('public');
    }

    public function registerMediaConversions(?SpatieMedia $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 480, 480)
            ->performOnCollections('products')
            ->nonQueued();

        $this->addMediaConversion('preview')
            ->fit(Fit::Contain, 1200, 1200)
            ->performOnCollections('products')
            ->nonQueued();
    }
}
