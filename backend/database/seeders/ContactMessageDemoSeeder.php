<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Performance/scenario test data — see DemoDataSeeder. Public contact-form
 * submissions across the last 30 days, status weighted so recent ones are
 * mostly 'new' and older ones are 'read'/'replied' — matching how a real
 * inbox actually gets worked through over time.
 */
class ContactMessageDemoSeeder extends Seeder
{
    private const MESSAGES = [
        'Do you have availability for a bridal trial next month?',
        'What products do you recommend for very dry, damaged hair?',
        'Can I book a group appointment for 4 people?',
        'Do you offer home-visit services for bridal makeup?',
        'Is parking available near the salon?',
        'I\'d like to enquire about your keratin treatment pricing.',
        'Can I reschedule my appointment from Saturday to Sunday?',
        'Do you have any current promotions running?',
    ];

    public function run(): void
    {
        foreach (range(1, 35) as $ignored) {
            $daysAgo = fake()->numberBetween(0, 30);
            $status = match (true) {
                $daysAgo <= 2 => fake()->randomElement(['new', 'new', 'read']),
                $daysAgo <= 10 => fake()->randomElement(['read', 'replied']),
                default => 'replied',
            };

            $message = ContactMessage::create([
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'phone' => fake()->boolean(60) ? '07'.fake()->numberBetween(1, 9).fake()->numerify('#######') : null,
                'message' => fake()->randomElement(self::MESSAGES),
                'status' => $status,
            ]);

            $ts = Carbon::today()->subDays($daysAgo)->setTime(fake()->numberBetween(8, 21), fake()->numberBetween(0, 59));
            $message->forceFill(['created_at' => $ts, 'updated_at' => $ts])->save();
        }
    }
}
