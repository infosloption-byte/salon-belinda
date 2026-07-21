<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            ['Nimasha Perera', 'Complete Bridal Package', 5, 'The team made my wedding morning so calm. My hair held up through the whole beach ceremony and photos afterwards. I felt completely like myself, just more polished.', '2026-05-12'],
            ['Ishara Fernando', 'Bridal Trial Session', 5, 'Booked a trial two weeks before the big day and it saved me. We adjusted the eye makeup and I walked in on the day already knowing exactly what to expect.', '2026-04-02'],
            ['Dilhani Wickramasinghe', 'Occasion Updo', 4, 'Lovely updo for my sister\'s homecoming. Only reason it\'s not five stars is I wish I\'d booked more time for the trial beforehand.', '2026-03-20'],
            ['Sanduni Rathnayake', 'Classic Facial', 5, 'My go-to facial before any event. The salon always feels clean, relaxed, and the staff remember exactly what my skin needs each visit.', '2026-02-14'],
            ['Kavindi Jayasuriya', 'Gel Overlay & Art', 5, 'Asked for something bridal-adjacent but subtle for a friend\'s wedding and they nailed it — pun intended. Lasted three full weeks.', '2026-01-30'],
        ];

        foreach ($testimonials as [$name, $service, $rating, $message, $date]) {
            Testimonial::updateOrCreate(
                ['name' => $name, 'service' => $service],
                [
                    'rating' => $rating,
                    'message' => $message,
                    'status' => 'approved',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]
            );
        }
    }
}
