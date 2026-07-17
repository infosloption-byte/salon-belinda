<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('payment_status'), fn ($q, $s) => $q->where('payment_status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load('items.product');

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate(['status' => ['required', 'in:pending,processing,completed,cancelled']]);
        $order->update(['status' => $request->status]);

        return back()->with('success', 'Order status updated.');
    }

    /**
     * Manually mark a Cash-on-Delivery or Bank Transfer order as paid once
     * the salon confirms payment was received.
     */
    public function markPaid(Order $order): RedirectResponse
    {
        $order->update([
            'payment_status' => 'paid',
            'transaction_id' => $order->transaction_id ?: 'MANUAL-'.now()->format('YmdHis'),
        ]);

        return back()->with('success', 'Order marked as paid.');
    }
}
