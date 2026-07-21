<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\AlbumPhoto;
use Illuminate\Database\Seeder;

class AlbumSeeder extends Seeder
{
    public function run(): void
    {
        $albums = [
            [
                'title' => 'Nadeesha & Kasun',
                'couple_names' => 'Nadeesha & Kasun',
                'event_date' => '2026-02-14',
                'location' => 'Galle Face Beach',
                'category' => 'Beach Wedding',
                'description' => 'A sunset beachside ceremony with soft, wind-friendly waves and a champagne-toned bridal look.',
                'photos' => [
                    ['https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=1200&q=80', 'Golden hour by the shore'],
                    ['https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1200&q=80', 'Bridal portrait, veil detail'],
                    ['https://images.unsplash.com/photo-1522337660859-02fbefca4702?auto=format&fit=crop&w=1200&q=80', 'Soft glam close-up'],
                    ['https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=1200&q=80', 'Final draping check'],
                ],
            ],
            [
                'title' => 'Ishara & Dinuka',
                'couple_names' => 'Ishara & Dinuka',
                'event_date' => '2025-11-08',
                'location' => 'Galle Fort',
                'category' => 'Kandyan',
                'description' => 'A traditional Kandyan wedding inside the historic fort walls, full osari drape and jewellery styling.',
                'photos' => [
                    ['https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1200&q=80', 'Kandyan osari, full length'],
                    ['https://images.unsplash.com/photo-1583001931096-959e9a1a6223?auto=format&fit=crop&w=1200&q=80', 'Traditional jewellery detail'],
                    ['https://images.unsplash.com/photo-1550614000-4895a10e1bfd?auto=format&fit=crop&w=1200&q=80', 'Family portrait'],
                ],
            ],
            [
                'title' => 'Sanduni Homecoming',
                'couple_names' => 'Sanduni & Ravindu',
                'event_date' => '2025-09-20',
                'location' => 'In-salon',
                'category' => 'Homecoming',
                'description' => 'Homecoming saree draping and evening makeup, styled entirely in-salon.',
                'photos' => [
                    ['https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=1200&q=80', 'Saree drape, front view'],
                    ['https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?auto=format&fit=crop&w=1200&q=80', 'Evening makeup finish'],
                    ['https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=1200&q=80', 'Final look, natural light'],
                ],
            ],
        ];

        foreach ($albums as $i => $data) {
            $photos = $data['photos'];
            unset($data['photos']);

            if (Album::where('title', $data['title'])->exists()) {
                continue;
            }

            $album = Album::create([
                ...$data,
                'slug' => Album::makeUniqueSlug($data['title']),
                'cover_image' => $photos[0][0],
                'is_published' => true,
                'sort_order' => $i,
            ]);

            foreach ($photos as $j => [$image, $caption]) {
                AlbumPhoto::create([
                    'album_id' => $album->id,
                    'image' => $image,
                    'caption' => $caption,
                    'sort_order' => $j,
                ]);
            }
        }
    }
}
