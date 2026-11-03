<?php

namespace App\Http\Resources;

use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $variants = $this->resource->relationLoaded('variants')
            ? $this->resource->variants
            : collect();
        $cheapestVariant = $variants
            ->sortBy(fn (ProductVariant $variant): float => (float) $variant->active_price)
            ->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->imageUrl(),
            'price' => $cheapestVariant?->active_price,
            'originalPrice' => $cheapestVariant
                && (float) $cheapestVariant->active_price < (float) $cheapestVariant->price
                    ? $cheapestVariant->price
                    : null,
            'averageRating' => round((float) ($this->average_rating ?? 0), 1),
            'reviewCount' => (int) ($this->reviews_count ?? 0),
            'soldUnits' => (int) ($this->sold_units ?? 0),
        ];
    }

    private function imageUrl(): ?string
    {
        if (! $this->resource->relationLoaded('media')) {
            return null;
        }

        /** @var Media|null $media */
        $media = $this->resource->media->first(
            fn (Media $item): bool => $item->collection_name === 'products'
                && is_file($item->getPath()),
        );

        if (! $media) {
            return null;
        }

        $conversion = $media->hasGeneratedConversion('thumb') && is_file($media->getPath('thumb'))
            ? 'thumb'
            : '';

        return $media->getUrl($conversion);
    }
}
