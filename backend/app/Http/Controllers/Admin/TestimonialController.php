<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(Request $request): View
    {
        $testimonials = Testimonial::query()
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function updateStatus(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $request->validate(['status' => ['required', 'in:pending,approved,rejected']]);
        $testimonial->update(['status' => $request->status]);

        return back()->with('success', 'Review updated.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return back()->with('success', 'Review deleted.');
    }
}
