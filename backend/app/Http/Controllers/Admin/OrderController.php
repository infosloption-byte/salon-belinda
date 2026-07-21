<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ActivityLogger;
use App\Support\InvoiceBranding;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('payment_status'), fn ($q, $s) => $q->where('payment_status', $s))
            ->when($request->query('q'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%");
                });
            })
            ->when($request->query('date_from'), fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->query('date_to'), fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
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
        $previousStatus = $order->status;
        $order->update(['status' => $request->status]);

        if ($previousStatus !== $order->status) {
            ActivityLogger::log(
                'order.status_updated',
                "Changed order {$order->order_number} from \"{$previousStatus}\" to \"{$order->status}\"",
                $order,
                ['from' => $previousStatus, 'to' => $order->status]
            );
        }

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

        ActivityLogger::log('order.marked_paid', "Manually marked order {$order->order_number} as paid", $order);

        return back()->with('success', 'Order marked as paid.');
    }

    /**
     * Open the invoice in the browser (renders inline, e.g. in a new tab)
     * so staff can eyeball it before printing or downloading.
     */
    public function invoicePreview(Order $order): Response
    {
        $order->load('items');

        return Pdf::loadView('admin.orders.invoice', ['order' => $order, 'logo' => InvoiceBranding::logo()])
            ->setPaper('a4')
            ->stream("invoice-{$order->order_number}.pdf");
    }

    /**
     * Force a download of the same invoice.
     */
    public function invoiceDownload(Order $order): Response
    {
        $order->load('items');

        return Pdf::loadView('admin.orders.invoice', ['order' => $order, 'logo' => InvoiceBranding::logo()])
            ->setPaper('a4')
            ->download("invoice-{$order->order_number}.pdf");
    }
}
