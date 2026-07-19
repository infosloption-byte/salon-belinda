<?php

namespace Database\Seeders;

use App\Models\GalleryCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GalleryCategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Bridal', 'Hair', 'Makeup', 'Nails', 'Special Occasion'];

        foreach ($names as $i => $name) {
            GalleryCategory::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name), 'sort_order' => $i]
            );
        }
    }
}
