<?php

namespace App\View\Composers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\View\View;

class NavigationComposer
{
    public function compose(View $view): void
    {
        $view->with('navCategories',
            Category::whereNull('parent_id')
                ->with(['children.children'])
                ->orderBy('name')
                ->get()
        );

        $view->with('navBrands',
            Brand::orderBy('name')->take(30)->get()
        );
    }
}
