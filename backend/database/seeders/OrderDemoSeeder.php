<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Performance/scenario test data — see DemoDataSeeder. Orders don't have a
 * meaningful "future" state (nothing schedules a shop order ahead of time),
 * so this covers past (60 days back) and present (today) only. Stock is
 * decremented via Product::decrementStock() for every order at "checkout"
 * time — same as OrderController::store() — regardless of what the order's
 * final status ends up being, since a cancelled order doesn't auto-restock
 * in the real app either.
 *
 * Run StockMovementDemoSeeder before this one so products have healthy
 * stock to sell from; a few products still end up low/out of stock by
 * design, to exercise the low-stock report and "Out of stock" UI.
 */
class OrderDemoSeeder extends Seeder
{
    private const CUSTOMER_NAMES = [
        'Sanduni Perera', 'Dilhani Fernando', 'Kavindi Silva', 'Nimasha Bandara', 'Shanika Rajapaksa',
        'Thilini Weerasinghe', 'Chathurika Herath', 'Ishara Mendis', 'Piumi Karunaratne', 'Nadeesha Senanayake',
    ];

    public function run(): void
    {
        $products = Product::all();
        if ($products->isEmpty()) {
            $this->command?->warn('OrderDemoSeeder: skipped — seed Products first.');

            return;
        }

        $deliveryFee = (int) config('shop.delivery_fee');
        $freeThreshold = (int) config('shop.free_delivery_threshold');

        // Past — 60 days back to yesterday.
        for ($d = 60; $d >= 1; $d--) {
            $date = Carbon::today()->subDays($d);
            foreach (range(1, DemoRandom::numberBetween(0, 3)) as $ignored) {
                $this->makeOrder($products, $date, $deliveryFee, $freeThreshold, isToday: false);
            }
        }

        // Present — a handful placed today, still working through the
        // pending/processing pipeline.
        foreach (range(1, DemoRandom::numberBetween(2, 5)) as $ignored) {
            $this->makeOrder($products, Carbon::today(), $deliveryFee, $freeThreshold, isToday: true);
        }
    }

    private function makeOrder($products, Carbon $date, int $deliveryFee, int $freeThreshold, bool $isToday): void
    {
        $name = DemoRandom::randomElement(self::CUSTOMER_NAMES);
        $fulfilment = DemoRandom::randomElement(['delivery', 'delivery', 'pickup']);

        $status = $isToday
            ? DemoRandom::randomElement(['pending', 'processing'])
            : DemoRandom::randomElement(['completed', 'completed', 'completed', 'processing', 'cancelled']);
        $paymentStatus = match (true) {
            $status === 'cancelled' => DemoRandom::randomElement(['pending', 'failed']),
            $status === 'completed' => 'paid',
            default => DemoRandom::randomElement(['pending', 'paid']),
        };

        $lineItems = $products->random(min($products->count(), DemoRandom::numberBetween(1, 4)));
        $subtotal = 0;
        $lines = [];
        foreach ($lineItems as $product) {
            $quantity = DemoRandom::numberBetween(1, 3);
            $lineTotal = $product->price * $quantity;
            $subtotal += $lineTotal;
            $lines[] = ['product' => $product, 'quantity' => $quantity, 'line_total' => $lineTotal];
        }

        $fee = $fulfilment === 'delivery' && $subtotal < $freeThreshold ? $deliveryFee : 0;

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'customer_name' => $name,
            'customer_phone' => '07'.DemoRandom::numberBetween(1, 9).DemoRandom::numerify('#######'),
            'customer_email' => DemoRandom::boolean(80) ? strtolower(str_replace(' ', '.', $name)).DemoRandom::numberBetween(1, 999).'@example.com' : null,
            'fulfilment_method' => $fulfilment,
            'address' => $fulfilment === 'delivery' ? DemoRandom::numerify('###/').DemoRandom::randomElement(['Galle Road', 'Negombo Road', 'High Level Road', 'Kandy Road']).', Colombo' : null,
            'city' => $fulfilment === 'delivery' ? DemoRandom::randomElement(['Colombo', 'Negombo', 'Gampaha', 'Kandy', 'Kalutara']) : null,
            'notes' => DemoRandom::boolean(10) ? 'Please call before delivery.' : null,
            'payment_method' => DemoRandom::randomElement(['cod', 'bank', 'card']),
            'payment_status' => $paymentStatus,
            'transaction_id' => $paymentStatus === 'paid' && DemoRandom::boolean(60) ? strtoupper(DemoRandom::bothify('TXN-########')) : null,
            'subtotal' => $subtotal,
            'delivery_fee' => $fee,
            'total' => $subtotal + $fee,
            'status' => $status,
        ]);

        foreach ($lines as $line) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $line['product']->id,
                'product_name' => $line['product']->name,
                'unit_price' => $line['product']->price,
                'quantity' => $line['quantity'],
                'line_total' => $line['line_total'],
            ]);
            $line['product']->decrementStock($line['quantity'], 'order', $order->id);
        }

        $ts = $isToday ? Carbon::now()->subMinutes(DemoRandom::numberBetween(5, 480)) : $date->copy()->setTime(DemoRandom::numberBetween(8, 21), DemoRandom::randomElement([0, 15, 30, 45]));
        $order->forceFill(['created_at' => $ts, 'updated_at' => $ts])->save();
        $order->items()->update(['created_at' => $ts, 'updated_at' => $ts]);
        StockMovement::where('reference_type', 'order')->where('reference_id', $order->id)
            ->update(['created_at' => $ts, 'updated_at' => $ts]);
    }
}
