<?php

namespace Database\Seeders;

use App\Models\GalleryItem;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['Bridal', 'Kandyan Bridal Look, Galle', 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=900&q=80'],
            ['Bridal', 'Beachside Wedding, Galle Road', 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=900&q=80'],
            ['Bridal', 'Bridal Trial — Soft Glam', 'https://images.unsplash.com/photo-1522337660859-02fbefca4702?auto=format&fit=crop&w=900&q=80'],
            ['Bridal', 'Homecoming Saree Draping', 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=900&q=80'],
            ['Hair', 'Balayage & Blow-dry', 'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=900&q=80'],
            ['Hair', 'Occasion Updo', 'https://images.unsplash.com/photo-1519699047748-de8e457a634e?auto=format&fit=crop&w=900&q=80'],
            ['Makeup', 'HD Photoshoot Makeup', 'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?auto=format&fit=crop&w=900&q=80'],
            ['Makeup', 'Evening Party Glam', 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=900&q=80'],
            ['Nails', 'Bridal French Gel Set', 'https://images.unsplash.com/photo-1604654894610-df63bc536371?auto=format&fit=crop&w=900&q=80'],
            ['Nails', 'Hand-painted Nail Art', 'https://images.unsplash.com/photo-1610992015732-2449b76344bc?auto=format&fit=crop&w=900&q=80'],
            ['Special Occasion', 'Engagement Day Styling', 'https://images.unsplash.com/photo-1583001931096-959e9a1a6223?auto=format&fit=crop&w=900&q=80'],
            ['Special Occasion', 'Family Function Look', 'https://images.unsplash.com/photo-1550614000-4895a10e1bfd?auto=format&fit=crop&w=900&q=80'],
        ];

        foreach ($items as $i => [$category, $title, $image]) {
            GalleryItem::create([
                'category' => $category,
                'title' => $title,
                'image' => $image,
                'sort_order' => $i,
            ]);
        }
    }
}
