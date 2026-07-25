<?php

namespace Database\Seeders;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Performance/scenario test data — NOT part of the default DatabaseSeeder
 * run (see DemoDataSeeder). Generates a realistic spread of customers:
 *  - tags across the fixed vocabulary (Customer::TAGS), including several
 *    with more than one tag and several with none
 *  - a handful with date_of_birth / anniversary_date landing on *today* and
 *    in the next few days, specifically so
 *    `php artisan customers:send-milestone-reminders` has something to send
 *    without waiting for the calendar to line up
 *  - the rest of the birthdays/anniversaries spread across the whole year
 *  - points_balance and points history are NOT set here — SalonJobDemoSeeder
 *    awards points the same way the real app does (job completion), so the
 *    ledger and the cached balance stay consistent with each other
 */
class CustomerDemoSeeder extends Seeder
{
    private const FIRST_NAMES = [
        'Sanduni', 'Dilhani', 'Kavindi', 'Nimasha', 'Shanika', 'Thilini', 'Chathurika', 'Ishara',
        'Piumi', 'Nadeesha', 'Hasini', 'Iresha', 'Dinusha', 'Sachini', 'Anjali', 'Malki',
        'Rashmika', 'Sewwandi', 'Tharushi', 'Vindya', 'Amaya', 'Kalani', 'Oshadi', 'Yasodha',
        'Nuwan', 'Kasun', 'Chamara', 'Ruwan', 'Dinesh', 'Isuru', 'Lahiru', 'Sampath',
        'Tharindu', 'Chathura', 'Nadun', 'Praveen',
    ];

    private const LAST_NAMES = [
        'Perera', 'Fernando', 'Silva', 'Jayasuriya', 'Wickramasinghe', 'Rajapaksa', 'Gunawardena',
        'Bandara', 'Dissanayake', 'Karunaratne', 'Senanayake', 'Weerasinghe', 'Ratnayake',
        'Abeysekera', 'Kodithuwakku', 'Mendis', 'de Silva', 'Wijesinghe', 'Amarasinghe', 'Herath',
    ];

    public function run(): void
    {
        $count = 70;
        $today = Carbon::today();

        for ($i = 0; $i < $count; $i++) {
            $name = self::FIRST_NAMES[array_rand(self::FIRST_NAMES)].' '.self::LAST_NAMES[array_rand(self::LAST_NAMES)];

            // First ~8 customers get a birthday/anniversary within the next
            // week (including today) so the reminder command has same-day
            // matches to send without waiting on the calendar. Everyone else
            // gets a date spread across the whole year, birth years spread
            // across a plausible adult range.
            if ($i < 5) {
                $dob = $today->copy()->addDays($i)->subYears(fake()->numberBetween(20, 45));
            } else {
                $dob = fake()->boolean(75)
                    ? Carbon::createFromDate(fake()->numberBetween(1970, 2004), fake()->numberBetween(1, 12), fake()->numberBetween(1, 28))
                    : null;
            }

            if ($i >= 5 && $i < 8) {
                $anniversary = $today->copy()->addDays($i - 5 + 1)->subYears(fake()->numberBetween(1, 20));
            } else {
                $anniversary = fake()->boolean(40)
                    ? Carbon::createFromDate(fake()->numberBetween(2005, 2025), fake()->numberBetween(1, 12), fake()->numberBetween(1, 28))
                    : null;
            }

            $tags = fake()->randomElements(array_keys(Customer::TAGS), fake()->numberBetween(0, 2));

            Customer::create([
                'name' => $name,
                'phone' => '07'.fake()->numberBetween(1, 9).fake()->numerify('#######'),
                'email' => fake()->boolean(70) ? strtolower(str_replace(' ', '.', $name)).fake()->numberBetween(1, 999).'@example.com' : null,
                'notes' => fake()->boolean(20) ? fake()->randomElement([
                    'Prefers window seating.',
                    'Allergic to certain fragrance oils — check before facials.',
                    'Regular every 6 weeks.',
                    'Referred by a friend.',
                    'Prefers Sanduni for hair services.',
                ]) : null,
                'tags' => $tags,
                'date_of_birth' => $dob,
                'anniversary_date' => $anniversary,
            ]);
        }
    }
}
