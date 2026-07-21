<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\JobItem;
use App\Models\JobPayment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SalonJob;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $shopDaily = Order::query()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as orders_count'))
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $salonDaily = JobPayment::query()
            ->whereDate('paid_at', '>=', $from)
            ->whereDate('paid_at', '<=', $to)
            ->select(DB::raw('DATE(paid_at) as day'), DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as payments_count'))
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $days = collect($shopDaily->keys())->merge($salonDaily->keys())->unique()->sort()->values();

        $combined = $days->map(function ($day) use ($shopDaily, $salonDaily) {
            $shop = (float) ($shopDaily[$day]->total ?? 0);
            $salon = (float) ($salonDaily[$day]->total ?? 0);

            return [
                'day' => $day,
                'shop' => $shop,
                'salon' => $salon,
                'total' => $shop + $salon,
                'orders_count' => (int) ($shopDaily[$day]->orders_count ?? 0),
                'payments_count' => (int) ($salonDaily[$day]->payments_count ?? 0),
            ];
        });

        $totalShopRevenue = (float) $shopDaily->sum('total');
        $totalSalonRevenue = (float) $salonDaily->sum('total');
        $totalRevenue = $totalShopRevenue + $totalSalonRevenue;
        $totalOrders = (int) $shopDaily->sum('orders_count');

        return view('admin.reports.revenue', compact(
            'combined', 'totalRevenue', 'totalShopRevenue', 'totalSalonRevenue', 'totalOrders', 'from', 'to'
        ));
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

    /**
     * Jobs (walk-ins, appointments-turned-jobs, wedding bookings, etc.) that
     * still have a balance owing — the "who still owes what" list.
     */
    public function outstandingBalances(): View
    {
        $jobs = SalonJob::query()
            ->with('customer')
            ->where('balance_due', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->orderBy('job_date')
            ->get();

        $totalOutstanding = (int) $jobs->sum('balance_due');

        return view('admin.reports.outstanding-balances', compact('jobs', 'totalOutstanding'));
    }

    /**
     * Staff performance & commission. Admin sees every staff member's
     * totals and can drill into one; a staff login only ever sees their own
     * — the staff_id filter is force-set from their account, never trusted
     * from the query string.
     */
    public function staffCommission(Request $request): View
    {
        $user = Auth::user();
        $isAdmin = $user->isAdminRole();

        if (! $isAdmin && ! $user->staff_id) {
            abort(403, 'Your account is not linked to a staff profile yet — ask an admin to link it.');
        }

        $from = $request->query('date_from') ?: now()->subDays(29)->toDateString();
        $to = $request->query('date_to') ?: now()->toDateString();
        $staffId = $isAdmin ? $request->query('staff_id') : $user->staff_id;

        $summary = JobItem::query()
            ->join('jobs_salon', 'jobs_salon.id', '=', 'job_items.job_id')
            ->join('staff', 'staff.id', '=', 'job_items.staff_id')
            ->where('jobs_salon.status', '!=', 'cancelled')
            ->whereDate('jobs_salon.job_date', '>=', $from)
            ->whereDate('jobs_salon.job_date', '<=', $to)
            ->when($staffId, fn ($q) => $q->where('job_items.staff_id', $staffId))
            ->select(
                'staff.id as staff_id',
                'staff.name',
                'staff.role_title',
                DB::raw('COUNT(*) as services_count'),
                DB::raw('SUM(job_items.final_price) as revenue'),
                DB::raw('SUM(job_items.commission_amount) as commission')
            )
            ->groupBy('staff.id', 'staff.name', 'staff.role_title')
            ->orderByDesc('commission')
            ->get();

        $detail = null;
        if ($staffId) {
            $detail = JobItem::with(['job.customer'])
                ->where('staff_id', $staffId)
                ->whereHas('job', function ($q) use ($from, $to) {
                    $q->where('status', '!=', 'cancelled')
                        ->whereDate('job_date', '>=', $from)
                        ->whereDate('job_date', '<=', $to);
                })
                ->orderByDesc('id')
                ->get();
        }

        $staffList = $isAdmin ? Staff::orderBy('name')->get() : collect();

        return view('admin.reports.staff-commission', compact(
            'summary', 'detail', 'staffId', 'staffList', 'from', 'to', 'isAdmin'
        ));
    }
}
