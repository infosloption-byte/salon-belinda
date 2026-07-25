<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerPoint;
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
 * Customer::earnPoints() for loyalty points — called per payment, same as
 * JobController::addPayment) rather than inserting precomputed numbers
 * directly, so every derived figure is internally consistent — a
 * completed job's total_paid, its customer's points_balance, and the
 * points ledger entries for it all agree with each other.
 *
 * Scenarios covered per customer:
 *  - several past jobs (last 90 days), mostly 'completed' and fully paid
 *    (each payment earns points, matching JobController::addPayment), a
 *    few 'cancelled' with no payment
 *  - roughly a third of customers get a job dated today: 'in_progress'
 *    with a partial deposit payment (which still earns points — earning
 *    is driven by payments recorded, not by job status), or already
 *    'completed'
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
            foreach (range(1, DemoRandom::numberBetween(0, 5)) as $ignored) {
                $this->makePastJob($customer, $services, $staff, $userIds);
            }

            if (DemoRandom::boolean(35)) {
                $this->makeTodayJob($customer, $services, $staff, $userIds);
            }

            if (DemoRandom::boolean(35)) {
                foreach (range(1, DemoRandom::numberBetween(1, 2)) as $ignored) {
                    $this->makeFutureJob($customer, $services, $staff, $userIds);
                }
            }
        });
    }

    private function makePastJob(Customer $customer, $services, $staff, array $userIds): void
    {
        $jobDate = Carbon::today()->subDays(DemoRandom::numberBetween(1, 90));
        $status = DemoRandom::randomElement(['completed', 'completed', 'completed', 'completed', 'cancelled']);

        $job = $this->createJob($customer, $jobDate, $status, $userIds);
        $this->addItems($job, $services, $staff, DemoRandom::numberBetween(1, 3));
        $job->recalculateTotals();

        if ($status === 'completed') {
            $this->addPayments($job, $jobDate, $userIds, full: true);
            $job->recalculateTotals();
        }

        $this->backdate($job, $jobDate);
    }

    private function makeTodayJob(Customer $customer, $services, $staff, array $userIds): void
    {
        $status = DemoRandom::randomElement(['in_progress', 'in_progress', 'completed']);
        $job = $this->createJob($customer, Carbon::today(), $status, $userIds);
        $this->addItems($job, $services, $staff, DemoRandom::numberBetween(1, 3));
        $job->recalculateTotals();

        if ($status === 'completed') {
            $this->addPayments($job, Carbon::today(), $userIds, full: true);
            $job->recalculateTotals();
        } elseif (DemoRandom::boolean(50)) {
            // Deposit — partial payment against a job still in progress.
            // Still earns points: earning is driven by payments recorded,
            // not by job status.
            $this->addPayments($job, Carbon::today(), $userIds, full: false);
            $job->recalculateTotals();
        }
    }

    private function makeFutureJob(Customer $customer, $services, $staff, array $userIds): void
    {
        $jobDate = Carbon::today()->addDays(DemoRandom::numberBetween(1, 30));
        $job = $this->createJob($customer, $jobDate, 'scheduled', $userIds);
        $this->addItems($job, $services, $staff, DemoRandom::numberBetween(1, 2));
        $job->recalculateTotals();
        // No payments yet — booked ahead, nothing collected until the visit.
    }

    private function createJob(Customer $customer, Carbon $jobDate, string $status, array $userIds): SalonJob
    {
        $hasDiscount = DemoRandom::boolean(20);
        $discountType = $hasDiscount ? DemoRandom::randomElement(['percent', 'fixed']) : 'none';

        return SalonJob::create([
            'customer_id' => $customer->id,
            'appointment_id' => null,
            'status' => $status,
            'job_date' => $jobDate->toDateString(),
            'notes' => DemoRandom::boolean(10) ? DemoRandom::randomElement(['Regular visit.', 'Requested extra time.', 'First treatment of this kind.']) : null,
            'created_by' => $userIds ? DemoRandom::randomElement($userIds) : null,
            'discount_type' => $discountType,
            'discount_value' => $discountType === 'none' ? 0 : ($discountType === 'percent' ? DemoRandom::numberBetween(5, 20) : DemoRandom::numberBetween(500, 3000)),
        ]);
    }

    private function addItems(SalonJob $job, $services, $staff, int $count): void
    {
        foreach (range(1, $count) as $ignored) {
            $service = $services->random();
            $member = $staff->random();
            $itemHasDiscount = DemoRandom::boolean(10);
            $discountType = $itemHasDiscount ? DemoRandom::randomElement(['percent', 'fixed']) : 'none';

            JobItem::create([
                'job_id' => $job->id,
                'service_id' => $service->id,
                'service_name' => $service->name,
                'staff_id' => $member->id,
                'base_price' => $service->price,
                'discount_type' => $discountType,
                'discount_value' => $discountType === 'none' ? 0 : ($discountType === 'percent' ? DemoRandom::numberBetween(5, 15) : DemoRandom::numberBetween(200, 1000)),
                'commission_percent' => $member->commission_percent,
                'final_price' => 0, // computed by JobItem::saving()
                'commission_amount' => 0,
            ]);
        }
    }

    private function addPayments(SalonJob $job, Carbon $jobDate, array $userIds, bool $full): void
    {
        $owed = $job->total_after_discount;
        $tip = DemoRandom::boolean(40) ? DemoRandom::numberBetween(100, 1500) : 0;

        if ($full) {
            // Sometimes split into two payments (e.g. deposit at booking,
            // balance on the day) rather than always one lump sum.
            if (DemoRandom::boolean(25) && $owed > 1000) {
                $deposit = (int) round($owed * DemoRandom::randomFloat(2, 0.2, 0.5));
                $this->recordPayment($job, $deposit, 0, $jobDate->copy()->subDays(DemoRandom::numberBetween(1, 5)), $userIds, 'Deposit');
                $owed -= $deposit;
            }

            $this->recordPayment($job, max(0, $owed), $tip, $jobDate->copy()->setTime(DemoRandom::numberBetween(9, 19), DemoRandom::randomElement([0, 15, 30, 45])), $userIds, null);
        } else {
            $deposit = (int) round($owed * DemoRandom::randomFloat(2, 0.2, 0.6));
            $this->recordPayment($job, $deposit, 0, Carbon::now(), $userIds, 'Deposit');
        }
    }

    /**
     * Creates the payment AND earns points off it — same two steps
     * JobController::addPayment does (recalculateTotals() is left to the
     * caller, since addPayments() may add more than one payment before
     * the job's totals need refreshing).
     */
    private function recordPayment(SalonJob $job, int $amount, int $tipAmount, Carbon $paidAt, array $userIds, ?string $note): void
    {
        $recordedBy = $userIds ? DemoRandom::randomElement($userIds) : null;

        JobPayment::create([
            'job_id' => $job->id,
            'amount' => $amount,
            'tip_amount' => $tipAmount,
            'method' => DemoRandom::randomElement(['cash', 'card', 'bank_transfer']),
            'paid_at' => $paidAt,
            'recorded_by' => $recordedBy,
            'note' => $note,
        ]);

        // Mirrors JobController::addPayment(): points earned off the
        // amount paid (tip excluded), floor(amount / currency_per_point).
        $points = intdiv($amount, max(1, (int) config('loyalty.currency_per_point')));
        $job->customer?->earnPoints($points, "Payment on job #{$job->id}", 'job', $job->id, $recordedBy);
    }

    /** Backdate created_at/updated_at so the activity feed and any created_at-ordered lists don't show every past job as "just now". */
    private function backdate(SalonJob $job, Carbon $jobDate): void
    {
        $ts = $jobDate->copy()->setTime(DemoRandom::numberBetween(9, 19), 0);
        $job->items()->update(['created_at' => $ts, 'updated_at' => $ts]);
        $job->payments()->update(['created_at' => $ts, 'updated_at' => $ts]);
        CustomerPoint::where('reference_type', 'job')->where('reference_id', $job->id)
            ->update(['created_at' => $ts, 'updated_at' => $ts]);
        $job->forceFill(['created_at' => $ts, 'updated_at' => $ts])->save();
    }
}
