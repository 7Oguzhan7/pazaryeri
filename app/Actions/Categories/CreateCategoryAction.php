<?php

namespace App\Actions\Categories;

use App\Models\Category;
use Illuminate\Support\Str;

class CreateCategoryAction
{
    /** @param array{name: string, slug: string, parent_id?: int|string|null, sort: int|string, commission_rate: int|float|string} $data */
    public function handle(array $data): Category
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        return Category::query()->create($data);
    }
}
