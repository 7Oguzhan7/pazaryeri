<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Categories\CreateCategoryAction;
use App\Actions\Categories\DeleteCategoryAction;
use App\Actions\Categories\UpdateCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->with('parent')
            ->withCount(['children', 'products'])
            ->orderBy('sort')
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        $category = new Category;
        $parents = Category::query()
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Categories/Create', [
            'category' => $category,
            'parents' => $parents,
        ]);
    }

    public function store(StoreCategoryRequest $request, CreateCategoryAction $create): RedirectResponse
    {
        $create->handle($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', __('categories.created'));
    }

    public function edit(Category $category): Response
    {
        $parents = Category::query()
            ->whereNull('parent_id')
            ->whereKeyNot($category->getKey())
            ->orderBy('sort')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
            'parents' => $parents,
        ]);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category,
        UpdateCategoryAction $update,
    ): RedirectResponse {
        $update->handle($category, $request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', __('categories.updated'));
    }

    public function destroy(Category $category, DeleteCategoryAction $delete): RedirectResponse
    {
        $delete->handle($category);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', __('categories.deleted'));
    }
}
