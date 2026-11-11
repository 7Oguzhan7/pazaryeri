<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CatalogFilterRequest extends FormRequest
{
    private const SORT_OPTIONS = ['newest', 'price_asc', 'price_desc', 'rating'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'marka' => ['nullable', 'string', 'exists:brands,slug'],
            'min' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'max' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'renk' => ['nullable', 'string', 'max:50'],
            'beden' => ['nullable', 'string', 'max:50'],
            'puan' => ['nullable', 'integer', 'between:1,5'],
            'sort' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $minimum = $this->input('min');
            $maximum = $this->input('max');

            if (is_numeric($minimum) && is_numeric($maximum) && (float) $minimum > (float) $maximum) {
                $validator->errors()->add('max', 'En yüksek fiyat, en düşük fiyattan az olamaz.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function catalogFilters(): array
    {
        $validated = $this->validated();

        return [
            'category' => $validated['category'] ?? null,
            'brand' => $validated['marka'] ?? null,
            'min' => $validated['min'] ?? null,
            'max' => $validated['max'] ?? null,
            'color' => $validated['renk'] ?? null,
            'size' => $validated['beden'] ?? null,
            'rating' => $validated['puan'] ?? null,
        ];
    }

    public function catalogSort(): string
    {
        $sort = $this->validated('sort');

        return is_string($sort) && in_array($sort, self::SORT_OPTIONS, true)
            ? $sort
            : 'newest';
    }
}
