<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\JobItem;
use App\Models\JobPayment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SalonJob;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * JSON port of Admin\ReportController — see routes/api.php
 * /api/admin/reports/*. Same six reports, same date-range defaults and
 * staff-commission visibility rules (staff logins only ever see their own
 * numbers; the staff_id filter is force-set from their account, never
 * trusted from the query string) as the Blade version.
 */
class ReportController extends Controller
{
    public function revenue(Request $request): JsonResponse
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
        })->values();

        $totalRevenue = (float) $shopDaily->sum('total') + (float) $salonDaily->sum('total');

        // Month-over-month style comparison: rather than assuming calendar
        // months (a range doesn't have to be one), compare against the
        // immediately-preceding period of the same length, so "last 30
        // days vs the 30 days before that" works the same as "July vs
        // June" if the user picks calendar-month bounds.
        $periodDays = Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
        $previousTo = Carbon::parse($from)->subDay()->toDateString();
        $previousFrom = Carbon::parse($from)->subDays($periodDays)->toDateString();

        $previousShopTotal = (float) Order::query()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $previousFrom)
            ->whereDate('created_at', '<=', $previousTo)
            ->sum('total');

        $previousSalonTotal = (float) JobPayment::query()
            ->whereDate('paid_at', '>=', $previousFrom)
            ->whereDate('paid_at', '<=', $previousTo)
            ->sum('amount');

        $previousTotalRevenue = $previousShopTotal + $previousSalonTotal;

        // Null (not 0%) when there's nothing to compare against — a jump
        // from zero reads as a division error, not real growth.
        $growthPercent = $previousTotalRevenue > 0
            ? round((($totalRevenue - $previousTotalRevenue) / $previousTotalRevenue) * 100, 1)
            : null;

        return response()->json([
            'combined' => $combined,
            'totalRevenue' => $totalRevenue,
            'totalShopRevenue' => (float) $shopDaily->sum('total'),
            'totalSalonRevenue' => (float) $salonDaily->sum('total'),
            'totalOrders' => (int) $shopDaily->sum('orders_count'),
            'previousTotalRevenue' => $previousTotalRevenue,
            'previousFrom' => $previousFrom,
            'previousTo' => $previousTo,
            'growthPercent' => $growthPercent,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function bestSellers(Request $request): JsonResponse
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

        return response()->json(['bestSellers' => $bestSellers, 'from' => $from, 'to' => $to]);
    }

    public function lowStock(): JsonResponse
    {
        $products = Product::query()
            ->orderBy('stock_count')
            ->lowStock()
            ->get();

        return response()->json(['products' => $products]);
    }

    public function appointments(Request $request): JsonResponse
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

        return response()->json(['byService' => $byService, 'byStatus' => $byStatus, 'from' => $from, 'to' => $to]);
    }

    /**
     * Jobs (walk-ins, appointments-turned-jobs, wedding bookings, etc.) that
     * still have a balance owing — the "who still owes what" list.
     */
    public function outstandingBalances(): JsonResponse
    {
        $jobs = SalonJob::query()
            ->with('customer')
            ->where('balance_due', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->orderBy('job_date')
            ->get();

        return response()->json(['jobs' => $jobs, 'totalOutstanding' => (int) $jobs->sum('balance_due')]);
    }

    /**
     * Bookings by hour of day — useful for staffing decisions (when do we
     * actually need people on the floor). `appointments.time` is a
     * free-text column (predates the booking-engine work — see
     * SALON-OPS-ENHANCEMENTS.md), so parse defensively same as the
     * calendar view's frontend parser and exclude what can't be parsed
     * rather than guessing.
     */
    public function busiestHours(Request $request): JsonResponse
    {
        $from = $request->query('date_from') ?: now()->subDays(29)->toDateString();
        $to = $request->query('date_to') ?: now()->toDateString();

        $times = Appointment::query()
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->pluck('time');

        $counts = array_fill(0, 24, 0);
        $excludedCount = 0;

        foreach ($times as $time) {
            $hour = self::parseHour($time);
            if ($hour === null) {
                $excludedCount++;

                continue;
            }
            $counts[$hour]++;
        }

        $hours = collect($counts)->map(fn ($count, $hour) => [
            'hour' => $hour,
            'label' => self::formatHourLabel($hour),
            'count' => $count,
        ])->values();

        return response()->json([
            'hours' => $hours,
            'excludedCount' => $excludedCount,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Same "free-text time" parsing rules as AppointmentsCalendar.tsx's
     * parseTimeToMinutes(), just returning the hour bucket instead of
     * total minutes — kept in step with that frontend parser rather than
     * trusting a stricter format the data doesn't actually guarantee.
     */
    private static function parseHour(?string $time): ?int
    {
        if (! $time) {
            return null;
        }

        if (! preg_match('/^\s*(\d{1,2}):(\d{2})(?::\d{2})?\s*(AM|PM|am|pm)?\s*$/', $time, $m)) {
            return null;
        }

        $hour = (int) $m[1];
        $minute = (int) $m[2];
        $meridiem = isset($m[3]) ? strtoupper($m[3]) : null;

        if ($meridiem === 'PM' && $hour < 12) {
            $hour += 12;
        }
        if ($meridiem === 'AM' && $hour === 12) {
            $hour = 0;
        }

        if ($hour > 23 || $minute > 59) {
            return null;
        }

        return $hour;
    }

    private static function formatHourLabel(int $hour): string
    {
        $period = $hour < 12 ? 'AM' : 'PM';
        $display = $hour % 12 === 0 ? 12 : $hour % 12;

        return "{$display} {$period}";
    }

    /**
     * Repeat-customer rate: of everyone with a completed visit in the
     * range, what share had already visited before the range started.
     * "Visit" means a non-cancelled job — a cancelled job never happened,
     * so it shouldn't count as a first or repeat visit either way.
     */
    public function retentionRate(Request $request): JsonResponse
    {
        $from = $request->query('date_from') ?: now()->subDays(29)->toDateString();
        $to = $request->query('date_to') ?: now()->toDateString();

        $customerIds = SalonJob::query()
            ->where('status', '!=', 'cancelled')
            ->whereDate('job_date', '>=', $from)
            ->whereDate('job_date', '<=', $to)
            ->distinct()
            ->pluck('customer_id');

        $totalCustomers = $customerIds->count();

        $returningCustomerIds = SalonJob::query()
            ->where('status', '!=', 'cancelled')
            ->whereIn('customer_id', $customerIds)
            ->whereDate('job_date', '<', $from)
            ->distinct()
            ->pluck('customer_id');

        $returningCustomers = $returningCustomerIds->count();
        $newCustomers = $totalCustomers - $returningCustomers;
        $retentionRate = $totalCustomers > 0 ? round(($returningCustomers / $totalCustomers) * 100, 1) : 0.0;

        return response()->json([
            'totalCustomers' => $totalCustomers,
            'returningCustomers' => $returningCustomers,
            'newCustomers' => $newCustomers,
            'retentionRate' => $retentionRate,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function staffCommission(Request $request): JsonResponse
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

        return response()->json([
            'summary' => $summary,
            'detail' => $detail,
            'staffId' => $staffId,
            'staffList' => $isAdmin ? Staff::orderBy('name')->get() : [],
            'from' => $from,
            'to' => $to,
            'isAdmin' => $isAdmin,
        ]);
    }
}
