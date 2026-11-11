<?php

namespace App\Http\Requests\Seller;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $product instanceof Product
            ? $this->user()?->can('update', $product) ?? false
            : $this->user()?->can('create', Product::class) ?? false;
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'description' => ['nullable', 'string', 'max:10000'],
            'variants' => ['required', 'array', 'min:1', 'max:50'],
            'variants.*.id' => [
                'sometimes',
                'integer',
                'distinct',
                Rule::exists('product_variants', 'id')
                    ->where('product_id', $product instanceof Product ? $product->id : 0),
            ],
            'variants.*.sku' => ['required', 'string', 'max:100', 'distinct'],
            'variants.*.color' => ['nullable', 'string', 'max:50'],
            'variants.*.size' => ['nullable', 'string', 'max:50'],
            'variants.*.price' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'variants.*.discount_price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'lt:variants.*.price'],
            'variants.*.stock' => ['required', 'integer', 'min:0', 'max:100000'],
        ];

        foreach ($this->input('variants', []) as $index => $variant) {
            $rules["variants.{$index}.sku"][] = Rule::unique('product_variants', 'sku')
                ->ignore($variant['id'] ?? null);
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Ürün adı zorunludur.',
            'category_id.required' => 'Bir kategori seçin.',
            'variants.required' => 'En az bir ürün seçeneği ekleyin.',
            'variants.min' => 'En az bir ürün seçeneği ekleyin.',
            'variants.*.id.exists' => 'Ürün seçeneği bu ürüne ait değil.',
            'variants.*.sku.required' => 'Her ürün seçeneği için SKU kodu girin.',
            'variants.*.sku.unique' => 'Bu SKU kodu başka bir üründe kullanılıyor.',
            'variants.*.price.required' => 'Ürün seçeneğinin fiyatı zorunludur.',
            'variants.*.price.gt' => 'Fiyat sıfırdan büyük olmalıdır.',
            'variants.*.discount_price.lt' => 'İndirimli fiyat normal fiyattan düşük olmalıdır.',
            'variants.*.stock.required' => 'Stok miktarı zorunludur.',
        ];
    }
}
