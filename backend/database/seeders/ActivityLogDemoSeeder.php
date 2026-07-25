<?php

namespace Database\Seeders;

use App\Models\AdminActivityLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalonJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Performance/scenario test data — see DemoDataSeeder. Run this LAST —
 * it references customers/jobs/products that need to already exist. Writes
 * directly to admin_activity_logs rather than through ActivityLogger::log()
 * so each entry can carry its own backdated timestamp and a specific
 * user_id (ActivityLogger reads Auth::id(), which is null in a console
 * context). This is a plausible-looking activity feed for scrolling/paging
 * performance testing, not a literal record of the other seeders' actions.
 */
class ActivityLogDemoSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->all();
        $customers = Customer::inRandomOrder()->limit(30)->get();
        $products = Product::inRandomOrder()->limit(6)->get();
        $jobs = SalonJob::inRandomOrder()->limit(30)->get();

        if (empty($userIds)) {
            return;
        }

        $entries = [];

        foreach ($customers as $customer) {
            $entries[] = ['action' => 'customer.created', 'description' => "Registered customer {$customer->name} ({$customer->phone})", 'subject_type' => Customer::class, 'subject_id' => $customer->id];
        }

        foreach ($jobs as $job) {
            $entries[] = ['action' => 'job.status_updated', 'description' => "Job #{$job->id} marked {$job->status}", 'subject_type' => SalonJob::class, 'subject_id' => $job->id];
        }

        foreach ($products as $product) {
            $entries[] = ['action' => 'product.updated', 'description' => "Updated product \"{$product->name}\"", 'subject_type' => Product::class, 'subject_id' => $product->id];
        }

        foreach (range(1, 20) as $ignored) {
            $entries[] = ['action' => 'staff.shift_added', 'description' => 'Added a shift to the roster', 'subject_type' => null, 'subject_id' => null];
        }

        foreach ($entries as $entry) {
            $daysAgo = fake()->numberBetween(0, 60);
            $ts = Carbon::today()->subDays($daysAgo)->setTime(fake()->numberBetween(8, 20), fake()->numberBetween(0, 59));

            AdminActivityLog::create([
                'user_id' => fake()->randomElement($userIds),
                'action' => $entry['action'],
                'description' => $entry['description'],
                'subject_type' => $entry['subject_type'],
                'subject_id' => $entry['subject_id'],
                'properties' => null,
                'ip_address' => fake()->ipv4(),
            ])->forceFill(['created_at' => $ts, 'updated_at' => $ts])->save();
        }
    }
}
