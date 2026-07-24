<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'title' => 'Bridal Packages',
                'intro' => 'Full-day bridal artistry, from the first touch of skincare prep to the last pin in your veil.',
                'services' => [
                    // duration_minutes is a best-effort single-sitting estimate used only
                    // for appointment overlap checks — `duration` (free text) is still
                    // what's shown to customers on the public site.
                    ['name' => 'Complete Bridal Package', 'description' => 'Bridal hair, makeup, saree/gown draping, and touch-up kit for the full ceremony day.', 'duration' => '5–6 hrs', 'duration_minutes' => 360, 'price' => 45000],
                    ['name' => 'Bridal Trial Session', 'description' => 'A full rehearsal of your look, styled and photographed ahead of the day.', 'duration' => '2 hrs', 'duration_minutes' => 120, 'price' => 8500],
                    ['name' => 'Homecoming / Poruwa Look', 'description' => 'A second look styled for the homecoming or poruwa ceremony.', 'duration' => '3 hrs', 'duration_minutes' => 180, 'price' => 22000],
                    ['name' => 'Bridal Party Styling (per person)', 'description' => 'Hair and makeup for bridesmaids, mothers, and family members.', 'duration' => '1.5 hrs', 'duration_minutes' => 90, 'price' => 6500],
                ],
            ],
            [
                'title' => 'Hair',
                'intro' => 'Cutting, colour, and treatments for everyday shine and special-occasion styling.',
                'services' => [
                    ['name' => 'Cut & Style', 'description' => 'Precision cut finished with a blow-dry style of your choice.', 'duration' => '45 min', 'duration_minutes' => 45, 'price' => 1800],
                    ['name' => 'Global Colour', 'description' => 'Full-head colour using ammonia-friendly professional formulas.', 'duration' => '2 hrs', 'duration_minutes' => 120, 'price' => 6500, 'price_prefix' => 'From'],
                    ['name' => 'Keratin / Deep Treatment', 'description' => 'Smoothing and strengthening treatment for damaged or frizzy hair.', 'duration' => '2.5 hrs', 'duration_minutes' => 150, 'price' => 9000, 'price_prefix' => 'From'],
                    ['name' => 'Occasion Updo', 'description' => 'Elegant styling for parties, engagements, and photoshoots.', 'duration' => '1 hr', 'duration_minutes' => 60, 'price' => 3500],
                ],
            ],
            [
                'title' => 'Skin & Facial',
                'intro' => 'Facials and skin treatments tailored to your skin type ahead of any occasion.',
                'services' => [
                    ['name' => 'Classic Facial', 'description' => 'Cleansing, exfoliation, and mask suited to your skin type.', 'duration' => '1 hr', 'duration_minutes' => 60, 'price' => 3200],
                    // "1 hr x 4" is 4 sessions booked separately — a single sitting is ~1 hr.
                    ['name' => 'Bridal Glow Facial Course', 'description' => 'A 4-session facial course leading up to the wedding for a natural glow.', 'duration' => '1 hr x 4', 'duration_minutes' => 60, 'price' => 14000],
                    ['name' => 'Brightening Facial', 'description' => 'Vitamin-C led treatment to even tone and add radiance.', 'duration' => '1 hr 15 min', 'duration_minutes' => 75, 'price' => 4800],
                ],
            ],
            [
                'title' => 'Makeup',
                'intro' => 'Party, photoshoot, and everyday makeup using long-wear, photo-ready products.',
                'services' => [
                    ['name' => 'Party Makeup', 'description' => 'Full-face makeup styled for evening events and celebrations.', 'duration' => '1 hr', 'duration_minutes' => 60, 'price' => 4500],
                    ['name' => 'Photoshoot Makeup', 'description' => 'HD makeup built for camera, with false lashes included.', 'duration' => '1.5 hrs', 'duration_minutes' => 90, 'price' => 6000],
                    ['name' => 'Personal Makeup Lesson', 'description' => 'One-on-one lesson to learn techniques for your own face shape.', 'duration' => '2 hrs', 'duration_minutes' => 120, 'price' => 7500],
                ],
            ],
            [
                'title' => 'Nails',
                'intro' => 'Manicures, pedicures, and nail art finished to last through any event.',
                'services' => [
                    ['name' => 'Classic Manicure', 'description' => 'Shape, cuticle care, and polish of your choice.', 'duration' => '40 min', 'duration_minutes' => 40, 'price' => 1500],
                    ['name' => 'Spa Pedicure', 'description' => 'Soak, scrub, mask, and polish for tired feet.', 'duration' => '1 hr', 'duration_minutes' => 60, 'price' => 2200],
                    ['name' => 'Gel Overlay & Art', 'description' => 'Long-wear gel colour with optional hand-painted detail.', 'duration' => '1 hr 15 min', 'duration_minutes' => 75, 'price' => 3800],
                ],
            ],
            [
                'title' => 'Ladies Special Occasion',
                'intro' => 'Complete look packages for engagements, homecomings, and family celebrations.',
                'services' => [
                    ['name' => 'Full Occasion Look', 'description' => 'Hair, makeup, and draping for engagements and family functions.', 'duration' => '2.5 hrs', 'duration_minutes' => 150, 'price' => 9500],
                    ['name' => 'Saree / Osari Draping', 'description' => 'Traditional and modern draping styles, pinned to hold all day.', 'duration' => '30 min', 'duration_minutes' => 30, 'price' => 2000],
                ],
            ],
        ];

        foreach ($categories as $sortIndex => $cat) {
            $category = ServiceCategory::updateOrCreate(
                ['slug' => Str::slug($cat['title'])],
                ['title' => $cat['title'], 'intro' => $cat['intro'], 'sort_order' => $sortIndex]
            );

            foreach ($cat['services'] as $sIndex => $s) {
                Service::updateOrCreate(
                    ['service_category_id' => $category->id, 'name' => $s['name']],
                    [
                        'description' => $s['description'],
                        'duration' => $s['duration'],
                        'duration_minutes' => $s['duration_minutes'] ?? null,
                        'price' => $s['price'],
                        'price_prefix' => $s['price_prefix'] ?? null,
                        'sort_order' => $sIndex,
                    ]
                );
            }
        }
    }
}
