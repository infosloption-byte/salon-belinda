<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Performance/scenario test data — see DemoDataSeeder. Appointments don't
 * carry a customer_id (public booking form only captures name/phone/email),
 * so these reuse the same name pool as CustomerDemoSeeder without an FK
 * relationship — some deliberately match a seeded customer's name/phone,
 * most don't, mirroring how a real booking form doesn't know who's a
 * returning customer.
 */
class AppointmentDemoSeeder extends Seeder
{
    private const TIMES = ['09:00 AM', '10:00 AM', '11:00 AM', '12:30 PM', '02:00 PM', '03:30 PM', '05:00 PM', '06:30 PM'];

    private const FIRST_NAMES = [
        'Sanduni', 'Dilhani', 'Kavindi', 'Nimasha', 'Shanika', 'Thilini', 'Chathurika', 'Ishara',
        'Piumi', 'Nadeesha', 'Hasini', 'Iresha', 'Nuwan', 'Kasun', 'Chamara', 'Ruwan', 'Dinesh',
        'Isuru', 'Lahiru', 'Sampath',
    ];

    private const LAST_NAMES = [
        'Perera', 'Fernando', 'Silva', 'Jayasuriya', 'Wickramasinghe', 'Bandara', 'Dissanayake',
        'Karunaratne', 'Senanayake', 'Weerasinghe', 'Ratnayake', 'Mendis', 'Wijesinghe', 'Herath',
    ];

    public function run(): void
    {
        $services = Service::all();
        $staff = Staff::all();
        if ($services->isEmpty()) {
            return;
        }

        $today = Carbon::today();

        // Past — 45 days back to yesterday, mostly completed with some
        // cancellations and no-shows so status-based reports have variety.
        for ($d = 45; $d >= 1; $d--) {
            $date = $today->copy()->subDays($d);
            $dailyCount = fake()->numberBetween(0, 4);
            for ($i = 0; $i < $dailyCount; $i++) {
                $this->makeAppointment($date, $services, $staff, fake()->randomElement([
                    'completed', 'completed', 'completed', 'cancelled', 'no_show',
                ]));
            }
        }

        // Today — a realistic single day's book: a few already completed
        // (earlier slots), a couple confirmed/pending for later.
        foreach (fake()->randomElements(self::TIMES, fake()->numberBetween(3, 6)) as $time) {
            $this->makeAppointment($today, $services, $staff, fake()->randomElement(['confirmed', 'confirmed', 'pending', 'completed']), $time);
        }

        // Future — next 21 days, pending/confirmed, with a handful
        // deliberately waitlisted (fully booked slot scenario).
        for ($d = 1; $d <= 21; $d++) {
            $date = $today->copy()->addDays($d);
            $dailyCount = fake()->numberBetween(0, 5);
            for ($i = 0; $i < $dailyCount; $i++) {
                $isWaitlisted = fake()->boolean(10);
                $this->makeAppointment($date, $services, $staff, $isWaitlisted ? 'pending' : fake()->randomElement(['pending', 'confirmed']), null, $isWaitlisted);
            }
        }
    }

    private function makeAppointment(Carbon $date, $services, $staff, string $status, ?string $time = null, bool $isWaitlisted = false): void
    {
        $service = $services->random();
        $name = self::FIRST_NAMES[array_rand(self::FIRST_NAMES)].' '.self::LAST_NAMES[array_rand(self::LAST_NAMES)];

        Appointment::create([
            'service_id' => $service->id,
            'service_name' => $service->name,
            'staff_id' => $staff->isNotEmpty() && fake()->boolean(70) ? $staff->random()->id : null,
            'name' => $name,
            'phone' => '07'.fake()->numberBetween(1, 9).fake()->numerify('#######'),
            'email' => fake()->boolean(60) ? strtolower(str_replace(' ', '.', $name)).fake()->numberBetween(1, 999).'@example.com' : null,
            'date' => $date->toDateString(),
            'time' => $time ?? fake()->randomElement(self::TIMES),
            'notes' => fake()->boolean(15) ? fake()->randomElement([
                'First-time visitor.', 'Please call to confirm.', 'Bringing a reference photo.',
            ]) : null,
            'status' => $status,
            'is_waitlisted' => $isWaitlisted,
        ]);
    }
}
