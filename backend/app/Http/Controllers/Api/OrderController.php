<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\PaymentGatewayStub;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(Request $request, PaymentGatewayStub $gateway): JsonResponse
    {
        $data = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.productId' => ['required', 'integer', 'exists:products,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'fulfilment' => ['required', 'in:delivery,pickup'],
            'payment' => ['required', 'in:cod,bank,card'],
            'customer.fullName' => ['required', 'string', 'max:120'],
            'customer.phone' => ['required', 'string', 'max:30'],
            'customer.email' => ['nullable', 'email', 'max:150'],
            'customer.address' => ['required_if:fulfilment,delivery', 'nullable', 'string', 'max:255'],
            'customer.city' => ['required_if:fulfilment,delivery', 'nullable', 'string', 'max:120'],
            'customer.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = DB::transaction(function () use ($data, $gateway) {
            $products = Product::whereIn('id', collect($data['lines'])->pluck('productId'))
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $lineData = [];

            foreach ($data['lines'] as $line) {
                $product = $products->get($line['productId']);

                if (! $product) {
                    throw ValidationException::withMessages(['lines' => 'One of the selected products no longer exists.']);
                }
                if (! $product->in_stock || $product->stock_count < $line['quantity']) {
                    throw ValidationException::withMessages(['lines' => "\"{$product->name}\" doesn't have enough stock right now."]);
                }

                $lineTotal = $product->price * $line['quantity'];
                $subtotal += $lineTotal;

                $lineData[] = [
                    'product' => $product,
                    'quantity' => $line['quantity'],
                    'unit_price' => $product->price,
                    'line_total' => $lineTotal,
                ];
            }

            $deliveryFee = $data['fulfilment'] === 'pickup' || $subtotal >= config('shop.free_delivery_threshold')
                ? 0
                : config('shop.delivery_fee');

            $total = $subtotal + $deliveryFee;

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_name' => $data['customer']['fullName'],
                'customer_phone' => $data['customer']['phone'],
                'customer_email' => $data['customer']['email'] ?? null,
                'fulfilment_method' => $data['fulfilment'],
                'address' => $data['customer']['address'] ?? null,
                'city' => $data['customer']['city'] ?? null,
                'notes' => $data['customer']['notes'] ?? null,
                'payment_method' => $data['payment'],
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'status' => 'pending',
            ]);

            foreach ($lineData as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'line_total' => $line['line_total'],
                ]);

                $line['product']->decrementStock($line['quantity']);
            }

            // Card payments: no gateway connected yet, so PaymentGatewayStub
            // simulates an immediate successful charge (see that class for
            // how to swap in a real gateway later). COD/Bank stay "pending"
            // until an admin marks them paid from the dashboard.
            if ($data['payment'] === 'card') {
                $result = $gateway->charge($total);

                $order->update([
                    'payment_status' => $result['success'] ? 'paid' : 'failed',
                    'transaction_id' => $result['transaction_id'],
                    'status' => $result['success'] ? 'processing' : 'pending',
                ]);
            }

            return $order->fresh('items');
        });

        return response()->json($order, 201);
    }

    public function show(string $orderNumber): JsonResponse
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();

        return response()->json($order);
    }
}
