# Fix: seeders vs. actual customer/points schema

Your `customers:send-occasion-reminders` / points implementation ended up
different from the version the seeders were originally written against
(table `customer_points` not `customer_points_ledger`, model `CustomerPoint`
not `CustomerPointsLedger`, `Customer::earnPoints()/redeemPoints()/
adjustPoints()` instead of `addPoints()`, points earned per-payment in
`JobController::addPayment()` rather than on job completion, `tags` as a
keyed array (`'vip' => 'VIP'`) instead of a plain list, and no
`points_awarded_at` column on jobs_salon). That's a better design than my
original, by the way — earning off the actual amount paid rather than off
job status is more correct. The two seeder files that assumed the old
shape needed rewriting to match:

- **`CustomerDemoSeeder.php`** — one-line fix: tags now pull from
  `array_keys(Customer::TAGS)` instead of `Customer::TAGS` directly, since
  `TAGS` is keyed (`'vip' => 'VIP'`) and the `tags` column stores keys.
- **`SalonJobDemoSeeder.php`** — points are now earned inside a new
  `recordPayment()` helper, called for every `JobPayment` created (deposit
  or final), via `Customer::earnPoints()` — mirroring
  `JobController::addPayment()` exactly (floor(amount / currency_per_point),
  tip excluded). Removed the old completion-triggered `awardPoints()` and
  all `points_awarded_at` references. The backdating step now updates
  `customer_points` via `reference_type = 'job'` / `reference_id`, not a
  `job_id` column that doesn't exist on that table.

Just overwrite these two files in `backend/database/seeders/` — the other
7 seeders (Appointment, StaffShift, StockMovement, Order, ContactMessage,
ActivityLog, DemoDataSeeder) didn't touch anything customer/points-related
and are unaffected.

Then, from where you ran the failed attempt:

```bash
docker compose exec backend php artisan db:seed --class="Database\Seeders\DemoDataSeeder"
```

(Your `migrate:fresh --seed` output above only ran the *base*
`DatabaseSeeder` — admin user, staff, services, products, etc. — not
`DemoDataSeeder`. That's expected: `DemoDataSeeder` is deliberately not
wired into the default seed run, so it needs that explicit `--class` call.)
