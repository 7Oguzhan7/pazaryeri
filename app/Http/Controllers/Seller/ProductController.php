<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\StoreProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $products = $request->user()->seller
            ->products()
            ->with(['category:id,name', 'variants:id,product_id,stock'])
            ->withCount('variants')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'status' => $product->status,
                'categoryName' => $product->category->name,
                'variantCount' => $product->variants_count,
                'stock' => $product->variants->sum('stock'),
            ]);

        return Inertia::render('Seller/Products/Index', [
            'products' => $products,
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Product::class);

        return Inertia::render('Seller/Products/Create', [
            'product' => [
                'name' => '',
                'categoryId' => '',
                'brandId' => '',
                'description' => '',
                'variants' => [$this->emptyVariant()],
            ],
            ...$this->formOptions(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $product = DB::transaction(function () use ($data, $request): Product {
            $product = $request->user()->seller->products()->create([
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'] ?? null,
                'name' => $data['name'],
                'slug' => $this->uniqueProductSlug($data['name']),
                'description' => $data['description'] ?? null,
                'status' => 'aktif',
            ]);

            foreach ($data['variants'] as $variant) {
                $product->variants()->create($this->variantAttributes($variant));
            }

            return $product;
        });

        return redirect()
            ->route('seller.products.edit', $product)
            ->with('status', 'seller-product-created');
    }

    public function edit(Product $product): Response
    {
        Gate::authorize('update', $product);
        $product->load('variants');

        return Inertia::render('Seller/Products/Edit', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'categoryId' => $product->category_id,
                'brandId' => $product->brand_id ?? '',
                'description' => $product->description ?? '',
                'variants' => $product->variants->map(fn (ProductVariant $variant): array => [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'color' => $variant->color ?? '',
                    'size' => $variant->size ?? '',
                    'price' => $variant->price,
                    'discount_price' => $variant->discount_price ?? '',
                    'stock' => $variant->stock,
                ])->all(),
            ],
            'productStatus' => $product->status,
            ...$this->formOptions(),
        ]);
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $product): void {
            $product->update([
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'] ?? null,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            foreach ($data['variants'] as $variant) {
                $attributes = $this->variantAttributes($variant);

                if (isset($variant['id'])) {
                    $product->variants()->whereKey($variant['id'])->update($attributes);
                } else {
                    $product->variants()->create($attributes);
                }
            }
        });

        return redirect()
            ->route('seller.products.edit', $product)
            ->with('status', 'seller-product-updated');
    }

    public function updateStatus(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);
        $validated = $request->validate([
            'status' => ['required', 'in:aktif,pasif'],
        ]);
        $product->update(['status' => $validated['status']]);

        return back()->with('status', $validated['status'] === 'aktif'
            ? 'seller-product-activated'
            : 'seller-product-deactivated');
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        if ($product->orderItems()->exists()) {
            $product->update(['status' => 'pasif']);

            return redirect()
                ->route('seller.products.index')
                ->with('status', 'seller-product-kept-for-order-history');
        }

        $product->delete();

        return redirect()
            ->route('seller.products.index')
            ->with('status', 'seller-product-deleted');
    }

    /**
     * @return array{categories: array<int, array{id: int, name: string}>, brands: array<int, array{id: int, name: string}>}
     */
    private function formOptions(): array
    {
        return [
            'categories' => Category::query()
                ->with('parent:id,name')
                ->orderBy('sort')
                ->orderBy('name')
                ->get(['id', 'parent_id', 'name'])
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->parent
                        ? "{$category->parent->name} / {$category->name}"
                        : $category->name,
                ])
                ->all(),
            'brands' => Brand::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Brand $brand): array => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                ])
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $variant
     * @return array<string, mixed>
     */
    private function variantAttributes(array $variant): array
    {
        return [
            'sku' => $variant['sku'],
            'color' => $variant['color'] ?? null,
            'size' => $variant['size'] ?? null,
            'price' => $variant['price'],
            'discount_price' => ($variant['discount_price'] ?? '') !== ''
                ? $variant['discount_price']
                : null,
            'stock' => $variant['stock'],
        ];
    }

    /**
     * @return array<string, string|int>
     */
    private function emptyVariant(): array
    {
        return [
            'sku' => '',
            'color' => '',
            'size' => '',
            'price' => '',
            'discount_price' => '',
            'stock' => 0,
        ];
    }

    private function uniqueProductSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'urun';
        $slug = $baseSlug;
        $suffix = 2;

        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
