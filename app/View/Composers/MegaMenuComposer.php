<?php

namespace App\View\Composers;

use App\Models\Category;
use Illuminate\Contracts\View\View;

class MegaMenuComposer
{
    public function compose(View $view): void
    {
        $view->with(
            'categories',
            Category::query()
                ->whereNull('parent_id')
                ->with('children')
                ->orderBy('sort')
                ->orderBy('name')
                ->get(),
        );
    }
}
