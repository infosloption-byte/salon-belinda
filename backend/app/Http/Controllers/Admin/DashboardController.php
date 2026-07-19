<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $revenueTrend = Order::query()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', now()->subDays(13))
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        // Fill in the missing days with zero so the trend has a full 14-day shape.
        $trend = collect(range(0, 13))->map(function ($i) use ($revenueTrend) {
            $day = now()->subDays(13 - $i)->toDateString();
            return ['day' => $day, 'total' => (float) ($revenueTrend[$day]->total ?? 0)];
        });

        $bestSellers = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')
            ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as units_sold'))
            ->groupBy('order_items.product_name')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'pendingAppointments' => Appointment::where('status', 'pending')->count(),
            'todayAppointments' => Appointment::whereDate('date', today())->count(),
            'pendingTestimonials' => Testimonial::where('status', 'pending')->count(),
            'newMessages' => ContactMessage::where('status', 'new')->count(),
            'pendingOrders' => Order::whereIn('status', ['pending', 'processing'])->count(),
            'todayRevenue' => Order::whereDate('created_at', today())->where('payment_status', 'paid')->sum('total'),
            'recentAppointments' => Appointment::latest()->limit(5)->get(),
            'recentOrders' => Order::latest()->limit(5)->get(),
            'revenueTrend' => $trend,
            'bestSellers' => $bestSellers,
        ]);
    }
}
