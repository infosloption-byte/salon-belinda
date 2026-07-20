<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Services\ActivityLogger;
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
        $previousStatus = $testimonial->status;
        $testimonial->update(['status' => $request->status]);

        if ($previousStatus !== $testimonial->status) {
            ActivityLogger::log(
                'testimonial.status_updated',
                "Changed {$testimonial->name}'s review from \"{$previousStatus}\" to \"{$testimonial->status}\"",
                $testimonial,
                ['from' => $previousStatus, 'to' => $testimonial->status]
            );
        }

        return back()->with('success', 'Review updated.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        ActivityLogger::log('testimonial.deleted', "Deleted {$testimonial->name}'s review", $testimonial);
        $testimonial->delete();

        return back()->with('success', 'Review deleted.');
    }
}
