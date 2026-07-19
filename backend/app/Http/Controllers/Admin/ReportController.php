<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('admin.reports.index');
    }

    public function revenue(Request $request): View
    {
        $from = $request->query('date_from') ?: now()->subDays(29)->toDateString();
        $to = $request->query('date_to') ?: now()->toDateString();

        $daily = Order::query()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as orders_count'))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $totalRevenue = $daily->sum('total');
        $totalOrders = $daily->sum('orders_count');

        return view('admin.reports.revenue', compact('daily', 'totalRevenue', 'totalOrders', 'from', 'to'));
    }

    public function bestSellers(Request $request): View
    {
        $from = $request->query('date_from') ?: now()->subDays(89)->toDateString();
        $to = $request->query('date_to') ?: now()->toDateString();

        $bestSellers = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereDate('orders.created_at', '>=', $from)
            ->whereDate('orders.created_at', '<=', $to)
            ->select(
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.line_total) as revenue')
            )
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('units_sold')
            ->limit(20)
            ->get();

        return view('admin.reports.best-sellers', compact('bestSellers', 'from', 'to'));
    }

    public function lowStock(): View
    {
        $products = Product::query()
            ->orderBy('stock_count')
            ->where('stock_count', '<=', 10)
            ->get();

        return view('admin.reports.low-stock', compact('products'));
    }

    public function appointments(Request $request): View
    {
        $from = $request->query('date_from') ?: now()->subDays(29)->toDateString();
        $to = $request->query('date_to') ?: now()->toDateString();

        $byService = Appointment::query()
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->select('service_name', DB::raw('COUNT(*) as total'))
            ->groupBy('service_name')
            ->orderByDesc('total')
            ->get();

        $byStatus = Appointment::query()
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        return view('admin.reports.appointments', compact('byService', 'byStatus', 'from', 'to'));
    }
}
