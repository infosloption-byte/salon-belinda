<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\JobPayment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SalonJob;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * JSON port of Admin\DashboardController. Same queries, JSON shape instead
 * of a Blade view — this is what admin/src/pages/Dashboard.tsx should be
 * pointed at (GET /api/admin/dashboard) in place of its current placeholder
 * stats array.
 */
class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // The full dashboard (revenue, orders, etc.) is admin-only. Staff
        // logins get a lighter payload the React app can use to redirect
        // straight to /jobs instead of rendering stat cards it can't see.
        if (! $user->isAdminRole()) {
            return response()->json(['role' => 'staff', 'redirectTo' => '/jobs']);
        }

        $revenueTrend = Order::query()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', now()->subDays(13))
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $trend = collect(range(0, 13))->map(function ($i) use ($revenueTrend) {
            $day = now()->subDays(13 - $i)->toDateString();
            return ['day' => $day, 'total' => (float) ($revenueTrend[$day]->total ?? 0)];
        })->values();

        $bestSellers = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')
            ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as units_sold'))
            ->groupBy('order_items.product_name')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        $recentActivity = AdminActivityLog::with('user:id,name')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'event' => $log->description,
                'action' => $log->action,
                'user' => $log->user?->name,
                'time' => $log->created_at->toIso8601String(),
            ]);

        // Proactive alerts — same underlying data as the Outstanding
        // Balances and Low Stock reports, just surfaced here so nobody has
        // to open a report to notice. Deliberately count-only (no full job
        // list) since the point is a nudge, not a duplicate report.
        $overdueBalanceCount = SalonJob::where('balance_due', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->where('job_date', '<=', now()->subDays(30)->toDateString())
            ->count();

        $lowStockCount = Product::query()->lowStock()->count();

        $alerts = [];
        if ($overdueBalanceCount > 0) {
            $alerts[] = [
                'type' => 'outstanding_balance',
                'severity' => 'warning',
                'message' => $overdueBalanceCount === 1
                    ? '1 job has an outstanding balance over 30 days old.'
                    : "{$overdueBalanceCount} jobs have an outstanding balance over 30 days old.",
                'count' => $overdueBalanceCount,
            ];
        }
        if ($lowStockCount > 0) {
            $alerts[] = [
                'type' => 'low_stock',
                'severity' => 'warning',
                'message' => $lowStockCount === 1
                    ? '1 product is at or below its reorder point.'
                    : "{$lowStockCount} products are at or below their reorder point.",
                'count' => $lowStockCount,
            ];
        }

        return response()->json([
            'role' => 'admin',
            'alerts' => $alerts,
            'stats' => [
                'todayAppointments' => Appointment::whereDate('date', today())->count(),
                'pendingAppointments' => Appointment::where('status', 'pending')->count(),
                'activeCustomers' => Customer::count(),
                'pendingOrders' => Order::whereIn('status', ['pending', 'processing'])->count(),
                'todayRevenue' => (float) Order::whereDate('created_at', today())->where('payment_status', 'paid')->sum('total'),
                'monthRevenue' => (float) Order::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->where('payment_status', 'paid')
                    ->sum('total'),
                'pendingTestimonials' => Testimonial::where('status', 'pending')->count(),
                'newMessages' => ContactMessage::where('status', 'new')->count(),
                'todayJobs' => SalonJob::whereDate('job_date', today())->where('status', '!=', 'cancelled')->count(),
                'todaySalonCash' => (int) JobPayment::whereDate('paid_at', today())->sum('amount'),
                'outstandingBalance' => (int) SalonJob::where('balance_due', '>', 0)->where('status', '!=', 'cancelled')->sum('balance_due'),
            ],
            'recentAppointments' => Appointment::latest()->limit(5)->get(['id', 'name', 'service_name', 'date', 'time', 'status']),
            'recentOrders' => Order::latest()->limit(5)->get(['id', 'order_number', 'customer_name', 'total', 'status', 'created_at']),
            'revenueTrend' => $trend,
            'bestSellers' => $bestSellers,
            'recentActivity' => $recentActivity,
        ]);
    }
}
