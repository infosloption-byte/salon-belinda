<?php

namespace Database\Seeders;

/**
 * Drop-in replacement for the handful of Faker methods the demo seeders
 * use. The seeders originally called the global fake() helper — fine in a
 * local dev environment where fakerphp/faker (a require-dev package) is
 * installed, but this app's production image is built with
 * `composer install --no-dev`, so `\Faker\Factory` doesn't exist there and
 * fake() blows up. Rather than making these seeders depend on a dev-only
 * package (or asking for a --no-dev-violating composer install on a
 * production box just to run a seeder), this reimplements the same method
 * names with plain PHP so `fake()->` could be swapped for `DemoRandom::`
 * everywhere with no other call-site changes.
 */
class DemoRandom
{
    private const FIRST_NAMES = [
        'James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda', 'William', 'Elizabeth',
        'David', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Jessica', 'Thomas', 'Sarah', 'Charles', 'Karen',
    ];

    private const LAST_NAMES = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
        'Hernandez', 'Lopez', 'Wilson', 'Anderson', 'Thomas', 'Taylor',
    ];

    public static function numberBetween(int $min, int $max): int
    {
        if ($min > $max) {
            [$min, $max] = [$max, $min];
        }

        return random_int($min, $max);
    }

    public static function boolean(int $chanceOfTrue = 50): bool
    {
        return random_int(1, 100) <= $chanceOfTrue;
    }

    public static function randomElement(array $array)
    {
        return $array[array_rand($array)];
    }

    /** @return array<int, mixed> */
    public static function randomElements(array $array, int $count): array
    {
        $count = max(0, min($count, count($array)));
        if ($count === 0) {
            return [];
        }

        $keys = array_rand($array, $count);
        $keys = is_array($keys) ? $keys : [$keys];

        return array_values(array_map(fn ($k) => $array[$k], $keys));
    }

    public static function randomFloat(int $decimals, float $min, float $max): float
    {
        $factor = 10 ** $decimals;

        return random_int((int) round($min * $factor), (int) round($max * $factor)) / $factor;
    }

    /** Replaces every '#' with a random digit — same syntax as Faker's numerify(). */
    public static function numerify(string $pattern): string
    {
        return preg_replace_callback('/#/', fn () => (string) random_int(0, 9), $pattern);
    }

    /** Replaces '#' with a digit and '?' with an uppercase letter — same syntax as Faker's bothify(). */
    public static function bothify(string $pattern): string
    {
        $pattern = self::numerify($pattern);

        return preg_replace_callback('/\?/', fn () => chr(random_int(65, 90)), $pattern);
    }

    public static function safeEmail(): string
    {
        return 'user'.random_int(100000, 999999).'@example.com';
    }

    public static function name(): string
    {
        return self::randomElement(self::FIRST_NAMES).' '.self::randomElement(self::LAST_NAMES);
    }

    public static function ipv4(): string
    {
        return self::numberBetween(1, 223).'.'.self::numberBetween(0, 255).'.'.self::numberBetween(0, 255).'.'.self::numberBetween(1, 254);
    }
}
