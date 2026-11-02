<?php

namespace App\Actions\Categories;

use App\Models\Category;
use Illuminate\Validation\ValidationException;

class DeleteCategoryAction
{
    public function handle(Category $category): void
    {
        if ($category->children()->exists() || $category->products()->exists()) {
            throw ValidationException::withMessages([
                'category' => __('categories.cannot_delete_in_use'),
            ]);
        }

        $category->delete();
    }
}
