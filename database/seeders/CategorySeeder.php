<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $topLevel = [
            ['name' => 'Gold Jewellery',    'icon' => 'heroicon-o-star'],
            ['name' => 'Diamond Jewellery', 'icon' => 'heroicon-o-sparkles'],
            ['name' => 'Silver Jewellery',  'icon' => 'heroicon-o-moon'],
            ['name' => 'Platinum',          'icon' => 'heroicon-o-cube'],
            ['name' => 'Fashion / Imitation','icon' => 'heroicon-o-shopping-bag'],
        ];

        $subCategories = [
            'Gold Jewellery'    => ['Necklaces', 'Earrings', 'Rings', 'Bangles', 'Bracelets', 'Pendants', 'Chains', 'Anklets'],
            'Diamond Jewellery' => ['Solitaire Rings', 'Pendants', 'Earrings', 'Bracelets'],
            'Silver Jewellery'  => ['Rings', 'Earrings', 'Anklets', 'Bracelets'],
            'Platinum'          => ['Rings', 'Pendants', 'Earrings'],
            'Fashion / Imitation' => ['Necklace Sets', 'Earrings', 'Bangles', 'Maang Tikka'],
        ];

        foreach ($topLevel as $i => $cat) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name'       => $cat['name'],
                    'icon'       => $cat['icon'],
                    'sort_order' => $i,
                    'is_active'  => true,
                ]
            );

            foreach ($subCategories[$cat['name']] ?? [] as $j => $sub) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($cat['name'] . '-' . $sub)],
                    [
                        'parent_id'  => $parent->id,
                        'name'       => $sub,
                        'sort_order' => $j,
                        'is_active'  => true,
                    ]
                );
            }
        }
    }
}
