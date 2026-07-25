<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\StaffShift;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Performance/scenario test data — see DemoDataSeeder. One shift per staff
 * member per day across a 6-week window (3 weeks back, this week, 2 weeks
 * ahead), with an occasional day off/leave entry so the schedule isn't
 * perfectly uniform.
 */
class StaffShiftDemoSeeder extends Seeder
{
    public function run(): void
    {
        $staff = Staff::all();
        if ($staff->isEmpty()) {
            return;
        }

        $start = Carbon::today()->subWeeks(3)->startOfWeek();
        $end = Carbon::today()->addWeeks(2)->endOfWeek();

        foreach ($staff as $member) {
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                // Sundays off, roughly one extra random day off per week too.
                if ($date->isSunday() || DemoRandom::boolean(12)) {
                    if (DemoRandom::boolean(30)) {
                        StaffShift::create([
                            'staff_id' => $member->id,
                            'date' => $date->toDateString(),
                            'start_time' => null,
                            'end_time' => null,
                            'type' => 'leave',
                            'notes' => DemoRandom::randomElement(['Day off', 'Annual leave', 'Personal leave']),
                        ]);
                    }

                    continue;
                }

                StaffShift::create([
                    'staff_id' => $member->id,
                    'date' => $date->toDateString(),
                    'start_time' => '09:00',
                    'end_time' => DemoRandom::randomElement(['17:00', '18:00', '19:00']),
                    'type' => 'work',
                    'notes' => null,
                ]);
            }
        }
    }
}
