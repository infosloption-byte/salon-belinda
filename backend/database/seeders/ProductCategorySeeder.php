<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Hair Care', 'Skin Care', 'Makeup', 'Bridal Accessories'];

        foreach ($names as $i => $name) {
            ProductCategory::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name), 'sort_order' => $i]
            );
        }
    }
}
