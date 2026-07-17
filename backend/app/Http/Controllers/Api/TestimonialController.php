<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Only approved testimonials are public. New submissions start as
     * "pending" until an admin approves them from the dashboard.
     */
    public function index(): JsonResponse
    {
        $testimonials = Testimonial::approved()
            ->latest()
            ->get(['id', 'name', 'service', 'rating', 'message', 'created_at as date']);

        return response()->json([
            'testimonials' => $testimonials,
            'average' => round($testimonials->avg('rating') ?? 0, 1),
            'count' => $testimonials->count(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'service' => ['nullable', 'string', 'max:120'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $testimonial = Testimonial::create($data + ['status' => 'pending']);

        return response()->json([
            'message' => 'Thank you — your review has been submitted and will appear once approved.',
            'testimonial' => $testimonial,
        ], 201);
    }
}
