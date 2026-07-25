<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerPointsLedger;
use App\Models\JobItem;
use App\Models\JobPayment;
use App\Models\SalonJob;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Performance/scenario test data — see DemoDataSeeder. Builds jobs the same
 * way the real app does (JobItem's price/commission computed by its
 * `saving()` hook, SalonJob::recalculateTotals() for cached totals,
 * Customer::addPoints() for loyalty points) rather than inserting
 * precomputed numbers directly, so every derived figure is internally
 * consistent — a completed job's total_paid, its customer's points_balance,
 * and the points ledger entry for it all agree with each other.
 *
 * Scenarios covered per customer:
 *  - several past jobs (last 90 days), mostly 'completed' and fully paid
 *    (points awarded, matching JobController::updateStatus), a few
 *    'cancelled' with no payment
 *  - roughly a third of customers get a job dated today: 'in_progress'
 *    with a partial deposit payment, or already 'completed'
 *  - roughly a third get 1-2 future jobs: 'scheduled', no payment yet
 */
class SalonJobDemoSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::all();
        $staff = Staff::where('is_active', true)->get();
        $userIds = User::pluck('id')->all();

        if ($services->isEmpty() || $staff->isEmpty()) {
            $this->command?->warn('SalonJobDemoSeeder: skipped — seed Staff/Services first.');

            return;
        }

        Customer::all()->each(function (Customer $customer) use ($services, $staff, $userIds) {
            foreach (range(1, fake()->numberBetween(0, 5)) as $ignored) {
                $this->makePastJob($customer, $services, $staff, $userIds);
            }

            if (fake()->boolean(35)) {
                $this->makeTodayJob($customer, $services, $staff, $userIds);
            }

            if (fake()->boolean(35)) {
                foreach (range(1, fake()->numberBetween(1, 2)) as $ignored) {
                    $this->makeFutureJob($customer, $services, $staff, $userIds);
                }
            }
        });
    }

    private function makePastJob(Customer $customer, $services, $staff, array $userIds): void
    {
        $jobDate = Carbon::today()->subDays(fake()->numberBetween(1, 90));
        $status = fake()->randomElement(['completed', 'completed', 'completed', 'completed', 'cancelled']);

        $job = $this->createJob($customer, $jobDate, $status, $userIds);
        $this->addItems($job, $services, $staff, fake()->numberBetween(1, 3));
        $job->recalculateTotals();

        if ($status === 'completed') {
            $this->addPayments($job, $jobDate, $userIds, full: true);
            $job->recalculateTotals();
            $this->awardPoints($job);
        }

        $this->backdate($job, $jobDate);
    }

    private function makeTodayJob(Customer $customer, $services, $staff, array $userIds): void
    {
        $status = fake()->randomElement(['in_progress', 'in_progress', 'completed']);
        $job = $this->createJob($customer, Carbon::today(), $status, $userIds);
        $this->addItems($job, $services, $staff, fake()->numberBetween(1, 3));
        $job->recalculateTotals();

        if ($status === 'completed') {
            $this->addPayments($job, Carbon::today(), $userIds, full: true);
            $job->recalculateTotals();
            $this->awardPoints($job);
        } elseif (fake()->boolean(50)) {
            // Deposit — partial payment against a job still in progress.
            $this->addPayments($job, Carbon::today(), $userIds, full: false);
            $job->recalculateTotals();
        }
    }

    private function makeFutureJob(Customer $customer, $services, $staff, array $userIds): void
    {
        $jobDate = Carbon::today()->addDays(fake()->numberBetween(1, 30));
        $job = $this->createJob($customer, $jobDate, 'scheduled', $userIds);
        $this->addItems($job, $services, $staff, fake()->numberBetween(1, 2));
        $job->recalculateTotals();
        // No payments yet — booked ahead, nothing collected until the visit.
    }

    private function createJob(Customer $customer, Carbon $jobDate, string $status, array $userIds): SalonJob
    {
        $hasDiscount = fake()->boolean(20);
        $discountType = $hasDiscount ? fake()->randomElement(['percent', 'fixed']) : 'none';

        return SalonJob::create([
            'customer_id' => $customer->id,
            'appointment_id' => null,
            'status' => $status,
            'job_date' => $jobDate->toDateString(),
            'notes' => fake()->boolean(10) ? fake()->randomElement(['Regular visit.', 'Requested extra time.', 'First treatment of this kind.']) : null,
            'created_by' => $userIds ? fake()->randomElement($userIds) : null,
            'discount_type' => $discountType,
            'discount_value' => $discountType === 'none' ? 0 : ($discountType === 'percent' ? fake()->numberBetween(5, 20) : fake()->numberBetween(500, 3000)),
        ]);
    }

    private function addItems(SalonJob $job, $services, $staff, int $count): void
    {
        foreach (range(1, $count) as $ignored) {
            $service = $services->random();
            $member = $staff->random();
            $itemHasDiscount = fake()->boolean(10);
            $discountType = $itemHasDiscount ? fake()->randomElement(['percent', 'fixed']) : 'none';

            JobItem::create([
                'job_id' => $job->id,
                'service_id' => $service->id,
                'service_name' => $service->name,
                'staff_id' => $member->id,
                'base_price' => $service->price,
                'discount_type' => $discountType,
                'discount_value' => $discountType === 'none' ? 0 : ($discountType === 'percent' ? fake()->numberBetween(5, 15) : fake()->numberBetween(200, 1000)),
                'commission_percent' => $member->commission_percent,
                'final_price' => 0, // computed by JobItem::saving()
                'commission_amount' => 0,
            ]);
        }
    }

    private function addPayments(SalonJob $job, Carbon $jobDate, array $userIds, bool $full): void
    {
        $owed = $job->total_after_discount;
        $tip = fake()->boolean(40) ? fake()->numberBetween(100, 1500) : 0;

        if ($full) {
            // Sometimes split into two payments (e.g. deposit at booking,
            // balance on the day) rather than always one lump sum.
            if (fake()->boolean(25) && $owed > 1000) {
                $deposit = (int) round($owed * fake()->randomFloat(2, 0.2, 0.5));
                JobPayment::create([
                    'job_id' => $job->id,
                    'amount' => $deposit,
                    'tip_amount' => 0,
                    'method' => fake()->randomElement(['cash', 'card', 'bank_transfer']),
                    'paid_at' => $jobDate->copy()->subDays(fake()->numberBetween(1, 5)),
                    'recorded_by' => $userIds ? fake()->randomElement($userIds) : null,
                    'note' => 'Deposit',
                ]);
                $owed -= $deposit;
            }

            JobPayment::create([
                'job_id' => $job->id,
                'amount' => max(0, $owed),
                'tip_amount' => $tip,
                'method' => fake()->randomElement(['cash', 'card', 'bank_transfer']),
                'paid_at' => $jobDate->copy()->setTime(fake()->numberBetween(9, 19), fake()->randomElement([0, 15, 30, 45])),
                'recorded_by' => $userIds ? fake()->randomElement($userIds) : null,
                'note' => null,
            ]);
        } else {
            $deposit = (int) round($owed * fake()->randomFloat(2, 0.2, 0.6));
            JobPayment::create([
                'job_id' => $job->id,
                'amount' => $deposit,
                'tip_amount' => 0,
                'method' => fake()->randomElement(['cash', 'card', 'bank_transfer']),
                'paid_at' => Carbon::now(),
                'recorded_by' => $userIds ? fake()->randomElement($userIds) : null,
                'note' => 'Deposit',
            ]);
        }
    }

    /** Mirrors the auto-award in JobController::updateStatus(). */
    private function awardPoints(SalonJob $job): void
    {
        $points = intdiv((int) $job->total_paid, (int) config('loyalty.points_per_currency_unit'));
        if ($points > 0) {
            $job->customer->addPoints($points, "Job #{$job->id} completed", $job->id);
        }
        $job->forceFill(['points_awarded_at' => now()])->save();
    }

    /** Backdate created_at/updated_at so the activity feed and any created_at-ordered lists don't show every past job as "just now". */
    private function backdate(SalonJob $job, Carbon $jobDate): void
    {
        $ts = $jobDate->copy()->setTime(fake()->numberBetween(9, 19), 0);
        $job->items()->update(['created_at' => $ts, 'updated_at' => $ts]);
        $job->payments()->update(['created_at' => $ts, 'updated_at' => $ts]);
        CustomerPointsLedger::where('job_id', $job->id)->update(['created_at' => $ts, 'updated_at' => $ts]);
        $job->forceFill([
            'created_at' => $ts,
            'updated_at' => $ts,
            'points_awarded_at' => $job->points_awarded_at ? $ts : null,
        ])->save();
    }
}
