# Demo data seeders

Drop these 9 files into `backend/database/seeders/` (same paths as in this
zip), then:

```bash
cd backend
composer install               # make sure dev deps are installed — fakerphp/faker
                                # is require-dev only, needed for these seeders
php artisan migrate             # if you haven't already applied the Tier 3
                                # customer/product migrations from last time
php artisan db:seed             # base data — staff, services, products, etc.
                                # (skip if you already have this)
php artisan db:seed --class="Database\Seeders\DemoDataSeeder"
```

`DemoDataSeeder` is deliberately **not** wired into the default
`DatabaseSeeder::run()` — it's a separate, explicit opt-in so a normal
`php artisan migrate:fresh --seed` doesn't get flooded with fake data. It
also refuses to run if `APP_ENV=production`.

## What gets created

| Seeder | Coverage |
|---|---|
| `CustomerDemoSeeder` | ~70 customers — tags, points (populated later by jobs), a few birthdays/anniversaries landing in the next few days (so the reminder command has something to send immediately), the rest spread across the year |
| `StaffShiftDemoSeeder` | Rota for every staff member: 3 weeks back, this week, 2 weeks ahead, with days off |
| `AppointmentDemoSeeder` | Past (completed/cancelled/no-show), today (mixed), and next 3 weeks (pending/confirmed, some waitlisted) |
| `SalonJobDemoSeeder` | Past jobs (mostly completed + fully paid + points auto-awarded, some cancelled), today's jobs (in-progress with deposits, or completed), future scheduled jobs — items/commission/totals all computed the same way the real controllers do |
| `StockMovementDemoSeeder` | Manual restocks + occasional breakage/write-off adjustments over the last 90 days, run before orders so there's stock to sell |
| `OrderDemoSeeder` | Past 60 days + a few placed today, decrementing stock via the same path checkout uses — a few products are deliberately left low/out of stock |
| `ContactMessageDemoSeeder` | ~35 contact form submissions, status weighted by age (recent = new, older = replied) |
| `ActivityLogDemoSeeder` | Runs last — backfills a plausible admin activity feed referencing the customers/jobs/products the other seeders created |

## After seeding

- `php artisan customers:send-milestone-reminders --dry-run` — see which
  birthday/anniversary emails would go out today, without actually sending.
- Products → pick any product → the ledger icon shows its full stock
  history (restocks, sales, adjustments).
- Customers → expand any customer → Points tab shows the auto-awarded
  points from their completed jobs.
- Reports (Low Stock, Revenue, etc.) should now have real numbers instead
  of near-empty tables.

## A couple of honest caveats

- **Stock ledger chronology isn't perfectly interleaved.** Each movement's
  `balance_after` is correct for the moment it happened *in the seeder's
  execution order*, but timestamps are backdated somewhat independently
  afterwards — so if you sort the ledger strictly by date, the running
  balance won't always look perfectly monotonic. Fine for volume/feature
  testing; don't treat it as a real point-in-time reconciliation.
- **Orders don't have a "future" state** — there's nothing in this app
  that schedules a shop order ahead of time, so `OrderDemoSeeder` only
  covers past + today.
- Re-running `DemoDataSeeder` **adds more data** rather than replacing it
  (none of these seeders clear tables first). For a clean slate, run
  `php artisan migrate:fresh --seed` first, then re-run `DemoDataSeeder`.
