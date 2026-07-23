<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON port of Admin\TestimonialController — see routes/api.php
 * /api/admin/testimonials*.
 */
class TestimonialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $testimonials = Testimonial::query()
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return response()->json(['testimonials' => $testimonials]);
    }

    public function updateStatus(Request $request, Testimonial $testimonial): JsonResponse
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

        return response()->json(['testimonial' => $testimonial->fresh(), 'message' => 'Review updated.']);
    }

    public function destroy(Testimonial $testimonial): JsonResponse
    {
        ActivityLogger::log('testimonial.deleted', "Deleted {$testimonial->name}'s review", $testimonial);
        $testimonial->delete();

        return response()->json(['message' => 'Review deleted.']);
    }
}
