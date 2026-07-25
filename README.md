# Fix: seeders vs. actual customer/points schema, plus a production guard escape hatch

## 1. Schema mismatch (the original bug)

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

## 2. Production guard (what just happened to you)

`DemoDataSeeder` refuses to run when `APP_ENV=production` — which your
box is, since it's a live server. That guard is intentional (fake
customers/orders on a production database by accident would be bad), but
you clearly want this on purpose for load/UAT testing, so **`DemoDataSeeder.php`
now has an explicit opt-in**: set `DEMO_SEED_ALLOW_PRODUCTION=true` for
just that one command, rather than touching `APP_ENV` in your `.env` (which
would also flip debug-mode/error-page behavior — not something you want to
toggle on a live box).

## What to do

Overwrite all three files below in `backend/database/seeders/`:

- `CustomerDemoSeeder.php`
- `SalonJobDemoSeeder.php`
- `DemoDataSeeder.php`

The other 6 seeders (Appointment, StaffShift, StockMovement, Order,
ContactMessage, ActivityLog) are unaffected by either issue.

Then run it with the escape hatch set for just that command:

```bash
docker compose exec -e DEMO_SEED_ALLOW_PRODUCTION=true backend \
  php artisan db:seed --class="Database\Seeders\DemoDataSeeder"
```

(`-e` on `docker compose exec` sets the env var only for that one process
— it doesn't touch your container's actual `.env` file or persist
anywhere.)

