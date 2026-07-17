<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = ServiceCategory::with('services')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ServiceCategory $cat) => [
                'id' => $cat->slug,
                'title' => $cat->title,
                'intro' => $cat->intro,
                'services' => $cat->services->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'description' => $s->description,
                    'duration' => $s->duration,
                    'price' => $s->price_label,
                ]),
            ]);

        return response()->json($categories);
    }
}
