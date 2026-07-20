<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            StaffSeeder::class,
            ServiceSeeder::class,
            GalleryCategorySeeder::class,
            GallerySeeder::class,
            AlbumSeeder::class,
            TestimonialSeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
