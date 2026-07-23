<?php

namespace App\Http\View\Composers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryMenuComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $menuCategories = Category::topLevel()
            ->active()
            ->withActiveChildren()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $allSubCategories = Category::whereNotNull('parent_id')
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $view->with([
            'menuCategories' => $menuCategories,
            'allSubCategories' => $allSubCategories,
        ]);
    }
}
