<?php

namespace App\Filament\BlockCategories;

use Redberry\PageBuilderPlugin\Abstracts\BaseBlockCategory;

class Products extends BaseBlockCategory
{
    public static function getCategoryName(): string
    {
        return 'Products';
    }
}
