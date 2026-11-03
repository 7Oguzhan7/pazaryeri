<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $product instanceof Product
            && ($this->user()?->can('uploadMedia', $product) ?? false);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:8'],
            'images.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'dimensions:max_width=5000,max_height=5000',
                'max:5120',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'images.required' => 'En az bir ürün görseli seçmelisiniz.',
            'images.array' => 'Ürün görselleri geçerli bir listede gönderilmelidir.',
            'images.min' => 'En az bir ürün görseli seçmelisiniz.',
            'images.max' => 'Tek seferde en fazla 8 görsel yükleyebilirsiniz.',
            'images.*.image' => 'Yüklenen dosyaların tamamı görsel olmalıdır.',
            'images.*.mimes' => 'Yalnızca JPG, PNG veya WebP görselleri yükleyebilirsiniz.',
            'images.*.extensions' => 'Görsel dosyasının uzantısı içeriğiyle eşleşmelidir.',
            'images.*.dimensions' => 'Görsel genişliği ve yüksekliği en fazla 5000 piksel olabilir.',
            'images.*.max' => 'Her görsel en fazla 5 MB olabilir.',
        ];
    }
}
