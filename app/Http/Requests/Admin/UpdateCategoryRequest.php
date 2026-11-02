<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug(
                filled($this->input('slug'))
                    ? (string) $this->input('slug')
                    : (string) $this->input('name'),
            ),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $category = $this->route('category');
        $parentRules = [
            'nullable',
            'integer',
            Rule::exists('categories', 'id')->whereNull('parent_id'),
        ];

        if ($category instanceof Category) {
            $parentRules[] = Rule::notIn([(string) $category->getKey()]);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($category),
            ],
            'parent_id' => $parentRules,
            'sort' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'commission_rate' => ['required', 'numeric', 'decimal:0,2', 'between:0,100'],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $category = $this->route('category');
                $parentId = $this->input('parent_id');

                if (
                    $category instanceof Category
                    && $category->children()->exists()
                    && filled($parentId)
                    && (int) $parentId !== $category->parent_id
                ) {
                    $validator->errors()->add(
                        'parent_id',
                        __('validation.custom.parent_id.has_children'),
                    );
                }
            },
        ];
    }
}
