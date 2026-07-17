<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Testimonial;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'pendingAppointments' => Appointment::where('status', 'pending')->count(),
            'todayAppointments' => Appointment::whereDate('date', today())->count(),
            'pendingTestimonials' => Testimonial::where('status', 'pending')->count(),
            'newMessages' => ContactMessage::where('status', 'new')->count(),
            'pendingOrders' => Order::whereIn('status', ['pending', 'processing'])->count(),
            'todayRevenue' => Order::whereDate('created_at', today())->where('payment_status', 'paid')->sum('total'),
            'recentAppointments' => Appointment::latest()->limit(5)->get(),
            'recentOrders' => Order::latest()->limit(5)->get(),
        ]);
    }
}
