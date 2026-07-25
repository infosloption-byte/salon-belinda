# Fix: removed the fakerphp/faker dependency entirely

`fake()` (Faker) is a `require-dev`-only package. Your production image is
almost certainly built with `composer install --no-dev`, so `\Faker\Factory`
doesn't exist there and every `fake()->...` call in the seeders blew up.

Rather than asking you to install dev dependencies on a production box (or
relying on `APP_ENV`/build flags to get this right), I replaced every
`fake()->` call across **all 9** demo seeders with a small dependency-free
helper, `DemoRandom`, that implements the exact same method names
(`numberBetween`, `boolean`, `randomElement`, `randomElements`,
`randomFloat`, `numerify`, `bothify`, `safeEmail`, `name`, `ipv4`) using
only built-in PHP (`random_int`, `array_rand`, etc). No other logic
changed — this was a mechanical `fake()->` → `DemoRandom::` swap, so all
the scenario coverage (past/present/future dates, tags, points, stock
movements, etc.) is exactly what it was before.

This supersedes the previous two fix zips — **this one replaces all 9
demo seeder files plus adds `DemoRandom.php`** (10 files total). You don't
need anything from the earlier zips anymore.

## What to do

Overwrite `backend/database/seeders/` with everything in this zip's
`backend/database/seeders/` folder (10 files), then:

```bash
docker compose exec -e DEMO_SEED_ALLOW_PRODUCTION=true backend \
  php artisan db:seed --class="Database\Seeders\DemoDataSeeder"
```

If it fails partway through again, the seeders aren't wrapped in a
transaction (deliberately — inserting hundreds of rows in one transaction
on a live box isn't great), so whatever ran before the error will already
be in the database. That's fine to leave in place; re-running
`DemoDataSeeder` just adds more on top rather than erroring on duplicates,
since nothing here has a uniqueness constraint against itself.
