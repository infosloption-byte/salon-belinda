<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Fills the app with realistic-volume test data covering past, present,
 * and future scenarios — for performance testing and exercising features
 * (reports, low-stock alerts, birthday reminders, points ledger, etc.)
 * against something closer to production data volume than the handful of
 * rows DatabaseSeeder's base seeders create.
 *
 * NOT called from DatabaseSeeder::run() — run it explicitly:
 *
 *   php artisan migrate:fresh --seed          # base data only (as before)
 *   php artisan db:seed --class=Database\\Seeders\\DemoDataSeeder
 *
 * (Run the base DatabaseSeeder first if you haven't — this depends on
 * Staff, Services, and Products already existing.)
 *
 * Order matters:
 *  1. Customers — nothing else here depends on jobs/points existing yet.
 *  2. Staff shifts — independent of everything else.
 *  3. Appointments — independent (no customer_id FK to depend on).
 *  4. Salon jobs (+ items, payments, points) — depends on Customers,
 *     Staff, Services.
 *  5. Stock movements (manual restocks/adjustments) — run BEFORE orders,
 *     so there's stock to sell.
 *  6. Orders (+ items) — depends on Products; draws stock back down via
 *     the same decrementStock() path checkout uses.
 *  7. Contact messages — independent.
 *  8. Activity log — LAST; references rows created by 1, 4, and 6.
 *
 * Note on ledger chronology: stock_movements' `balance_after` is correct
 * for the order operations actually ran in (each movement reflects real
 * stock at that moment), but movement timestamps are backdated somewhat
 * independently of each other afterwards, so a ledger sorted strictly by
 * date won't show a perfectly increasing/decreasing balance_after. Good
 * enough for volume/feature testing; not a source of truth for a
 * point-in-time stock reconciliation.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production') && ! app()->runningUnitTests()) {
            $this->command?->error('DemoDataSeeder generates fake customers, jobs, and orders — refusing to run with APP_ENV=production. Set APP_ENV to local/staging first if this is intentional.');

            return;
        }

        $this->call([
            CustomerDemoSeeder::class,
            StaffShiftDemoSeeder::class,
            AppointmentDemoSeeder::class,
            SalonJobDemoSeeder::class,
            StockMovementDemoSeeder::class,
            OrderDemoSeeder::class,
            ContactMessageDemoSeeder::class,
            ActivityLogDemoSeeder::class,
        ]);

        $this->command?->info('Demo data seeded. Run `php artisan customers:send-milestone-reminders --dry-run` to see the birthday/anniversary reminders it set up for testing.');
    }
}
