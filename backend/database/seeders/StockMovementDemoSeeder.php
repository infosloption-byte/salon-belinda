<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Performance/scenario test data — see DemoDataSeeder. Manual restocks
 * (positive) and adjustments (breakage/write-off, negative) across the
 * last 90 days, on top of whatever ProductSeeder's initial stock_count
 * already was. OrderDemoSeeder's sales come afterwards in the run order
 * and will draw this back down — a few products are deliberately left
 * without enough restocking to stay ahead of sales, so the low-stock
 * report has real entries once seeding finishes.
 */
class StockMovementDemoSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $adminId = User::where('is_admin', true)->value('id');

        foreach ($products as $product) {
            foreach (range(1, DemoRandom::numberBetween(2, 4)) as $ignored) {
                $daysAgo = DemoRandom::numberBetween(5, 90);
                $qty = DemoRandom::numberBetween(10, 40);
                $movement = $product->applyMovement('restock', $qty, DemoRandom::randomElement([
                    'Scheduled restock', 'Supplier delivery', 'Restocked ahead of promotion',
                ]), $adminId);
                $this->backdate($movement, Carbon::today()->subDays($daysAgo));
            }

            // Occasional breakage/loss write-off.
            if (DemoRandom::boolean(25)) {
                $daysAgo = DemoRandom::numberBetween(1, 60);
                $qty = -DemoRandom::numberBetween(1, 5);
                $movement = $product->applyMovement('adjustment', $qty, DemoRandom::randomElement([
                    'Damaged in storage', 'Stocktake correction', 'Sample given to customer',
                ]), $adminId);
                $this->backdate($movement, Carbon::today()->subDays($daysAgo));
            }
        }
    }

    private function backdate(StockMovement $movement, Carbon $date): void
    {
        $ts = $date->setTime(DemoRandom::numberBetween(9, 18), 0);
        $movement->forceFill(['created_at' => $ts, 'updated_at' => $ts])->save();
    }
}
