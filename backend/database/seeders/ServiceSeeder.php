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
                    ['Complete Bridal Package', 'Bridal hair, makeup, saree/gown draping, and touch-up kit for the full ceremony day.', '5–6 hrs', 45000],
                    ['Bridal Trial Session', 'A full rehearsal of your look, styled and photographed ahead of the day.', '2 hrs', 8500],
                    ['Homecoming / Poruwa Look', 'A second look styled for the homecoming or poruwa ceremony.', '3 hrs', 22000],
                    ['Bridal Party Styling (per person)', 'Hair and makeup for bridesmaids, mothers, and family members.', '1.5 hrs', 6500],
                ],
            ],
            [
                'title' => 'Hair',
                'intro' => 'Cutting, colour, and treatments for everyday shine and special-occasion styling.',
                'services' => [
                    ['Cut & Style', 'Precision cut finished with a blow-dry style of your choice.', '45 min', 1800],
                    ['Global Colour', 'Full-head colour using ammonia-friendly professional formulas.', '2 hrs', 6500, 'From'],
                    ['Keratin / Deep Treatment', 'Smoothing and strengthening treatment for damaged or frizzy hair.', '2.5 hrs', 9000, 'From'],
                    ['Occasion Updo', 'Elegant styling for parties, engagements, and photoshoots.', '1 hr', 3500],
                ],
            ],
            [
                'title' => 'Skin & Facial',
                'intro' => 'Facials and skin treatments tailored to your skin type ahead of any occasion.',
                'services' => [
                    ['Classic Facial', 'Cleansing, exfoliation, and mask suited to your skin type.', '1 hr', 3200],
                    ['Bridal Glow Facial Course', 'A 4-session facial course leading up to the wedding for a natural glow.', '1 hr x 4', 14000],
                    ['Brightening Facial', 'Vitamin-C led treatment to even tone and add radiance.', '1 hr 15 min', 4800],
                ],
            ],
            [
                'title' => 'Makeup',
                'intro' => 'Party, photoshoot, and everyday makeup using long-wear, photo-ready products.',
                'services' => [
                    ['Party Makeup', 'Full-face makeup styled for evening events and celebrations.', '1 hr', 4500],
                    ['Photoshoot Makeup', 'HD makeup built for camera, with false lashes included.', '1.5 hrs', 6000],
                    ['Personal Makeup Lesson', 'One-on-one lesson to learn techniques for your own face shape.', '2 hrs', 7500],
                ],
            ],
            [
                'title' => 'Nails',
                'intro' => 'Manicures, pedicures, and nail art finished to last through any event.',
                'services' => [
                    ['Classic Manicure', 'Shape, cuticle care, and polish of your choice.', '40 min', 1500],
                    ['Spa Pedicure', 'Soak, scrub, mask, and polish for tired feet.', '1 hr', 2200],
                    ['Gel Overlay & Art', 'Long-wear gel colour with optional hand-painted detail.', '1 hr 15 min', 3800],
                ],
            ],
            [
                'title' => 'Ladies Special Occasion',
                'intro' => 'Complete look packages for engagements, homecomings, and family celebrations.',
                'services' => [
                    ['Full Occasion Look', 'Hair, makeup, and draping for engagements and family functions.', '2.5 hrs', 9500],
                    ['Saree / Osari Draping', 'Traditional and modern draping styles, pinned to hold all day.', '30 min', 2000],
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
                    ['service_category_id' => $category->id, 'name' => $s[0]],
                    [
                        'description' => $s[1],
                        'duration' => $s[2],
                        'price' => $s[3],
                        'price_prefix' => $s[4] ?? null,
                        'sort_order' => $sIndex,
                    ]
                );
            }
        }
    }
}
