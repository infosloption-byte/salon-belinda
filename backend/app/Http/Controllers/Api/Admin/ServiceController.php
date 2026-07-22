<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * JSON port of Admin\ServiceController. Same validation/logging/behaviour,
 * JSON responses instead of redirect()->back(). Routes live under
 * /api/admin/services* — see routes/api.php.
 */
class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = ServiceCategory::with('services')->orderBy('sort_order')->get();

        return response()->json(['categories' => $categories]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'intro' => ['nullable', 'string', 'max:500'],
        ]);

        $category = ServiceCategory::create([
            ...$data,
            'slug' => Str::slug($data['title']),
            'sort_order' => ServiceCategory::max('sort_order') + 1,
        ]);
        ActivityLogger::log('service_category.created', "Added service category \"{$category->title}\"", $category);

        return response()->json(['category' => $category, 'message' => 'Category added.'], 201);
    }

    public function destroyCategory(ServiceCategory $category): JsonResponse
    {
        ActivityLogger::log('service_category.deleted', "Deleted service category \"{$category->title}\" and its services", $category);
        $category->delete();

        return response()->json(['message' => 'Category and its services removed.']);
    }

    public function storeService(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'duration' => ['nullable', 'string', 'max:60'],
            'price' => ['required', 'integer', 'min:0'],
            'price_prefix' => ['nullable', 'string', 'max:20'],
        ]);

        $service = Service::create($data + ['sort_order' => 0]);
        ActivityLogger::log('service.created', "Added service \"{$service->name}\"", $service);

        return response()->json(['service' => $service, 'message' => 'Service added.'], 201);
    }

    public function updateService(Request $request, Service $service): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'duration' => ['nullable', 'string', 'max:60'],
            'price' => ['required', 'integer', 'min:0'],
            'price_prefix' => ['nullable', 'string', 'max:20'],
        ]);

        $service->update($data);
        ActivityLogger::log('service.updated', "Updated service \"{$service->name}\"", $service);

        return response()->json(['service' => $service, 'message' => 'Service updated.']);
    }

    public function destroyService(Service $service): JsonResponse
    {
        ActivityLogger::log('service.deleted', "Deleted service \"{$service->name}\"", $service);
        $service->delete();

        return response()->json(['message' => 'Service removed.']);
    }
}
